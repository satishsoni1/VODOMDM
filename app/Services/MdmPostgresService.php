<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\MdmDevice;
use App\Models\MdmDeviceGps;
use App\Models\MdmDeviceHardware;
use App\Models\MdmDeviceParams;
use App\Models\MdmImportLog;
use App\Models\MdmLocationHistory;
use App\Models\MdmLocationLatest;
use App\Models\MdmPortalDevice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MdmPostgresService
{
    private const CONN         = 'mdm_pgsql';
    private const BATCH_SIZE   = 100;
    private const UPSERT_CHUNK = 25;

    private const DEVICE_TABLE_CANDIDATES        = ['hmdm_device', 'devices', 'device', 'mdm_devices'];
    private const LOCATION_TABLE_CANDIDATES      = ['plugin_deviceinfo_deviceparams_gps', 'hmdm_device_location', 'device_locations', 'device_location', 'hmdm_location', 'locations'];
    private const DEVICE_PARAMS_TABLE_CANDIDATES = ['plugin_deviceinfo_deviceparams_device', 'device_params', 'device_parameters', 'hmdm_device_params'];
    private const BASE_PARAMS_TABLE_CANDIDATES   = ['plugin_deviceinfo_deviceparams', 'device_info_params', 'deviceinfo_params'];
    private const LOCATION_LATEST_TABLE_CANDIDATES  = ['plugin_devicelocations_latest', 'hmdm_device_location_latest', 'device_location_latest'];
    private const LOCATION_HISTORY_TABLE_CANDIDATES = ['plugin_devicelocations_history', 'hmdm_device_location_history', 'device_location_history'];
    private const APP_TABLE_CANDIDATES           = ['hmdm_device_application_setting', 'device_application_settings', 'device_applications', 'device_application', 'hmdm_application_setting'];
    private const GROUP_TABLE_CANDIDATES         = ['hmdm_group', 'groups', 'device_groups', 'group'];
    private const CONFIG_TABLE_CANDIDATES        = ['hmdm_configuration', 'configurations', 'configuration', 'hmdm_config'];

    private ?array    $existingTables    = null;
    private $progressCallback = null;

    // ── Schema scanner ──────────────────────────────────────────────────────
    public function scanTables(): array
    {
        $rows = DB::connection(self::CONN)->select("
            SELECT
                c.relname  AS table_name,
                GREATEST(c.reltuples::bigint, 0) AS row_estimate,
                pg_size_pretty(pg_total_relation_size(c.oid)) AS table_size
            FROM pg_catalog.pg_class c
            JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE n.nspname = 'public'
              AND c.relkind = 'r'
            ORDER BY c.reltuples DESC
        ");

        $knownTables = array_map('strtolower', array_merge(
            self::DEVICE_TABLE_CANDIDATES,
            self::LOCATION_TABLE_CANDIDATES,
            self::APP_TABLE_CANDIDATES,
            self::GROUP_TABLE_CANDIDATES,
            self::CONFIG_TABLE_CANDIDATES,
            self::BASE_PARAMS_TABLE_CANDIDATES,
            self::LOCATION_LATEST_TABLE_CANDIDATES,
            self::LOCATION_HISTORY_TABLE_CANDIDATES,
        ));

        $tables = [];
        foreach ($rows as $row) {
            $cols = DB::connection(self::CONN)->select("
                SELECT column_name, data_type, character_maximum_length, is_nullable
                FROM information_schema.columns
                WHERE table_schema = 'public' AND table_name = ?
                ORDER BY ordinal_position
            ", [$row->table_name]);

            $tables[] = [
                'name'    => $row->table_name,
                'rows'    => (int) $row->row_estimate,
                'size'    => $row->table_size,
                'known'   => in_array(strtolower($row->table_name), $knownTables),
                'role'    => $this->guessRole($row->table_name),
                'columns' => array_map(fn ($c) => [
                    'name'     => $c->column_name,
                    'type'     => $c->data_type . ($c->character_maximum_length ? "({$c->character_maximum_length})" : ''),
                    'nullable' => $c->is_nullable === 'YES',
                ], $cols),
            ];
        }

        return $tables;
    }

    private function guessRole(string $table): string
    {
        $t = strtolower($table);
        if (in_array($t, array_map('strtolower', self::DEVICE_TABLE_CANDIDATES)))        return 'devices';
        if (in_array($t, array_map('strtolower', self::LOCATION_TABLE_CANDIDATES)))      return 'gps';
        if (in_array($t, array_map('strtolower', self::DEVICE_PARAMS_TABLE_CANDIDATES))) return 'device-info';
        if (in_array($t, array_map('strtolower', self::APP_TABLE_CANDIDATES)))           return 'apps';
        if (in_array($t, array_map('strtolower', self::GROUP_TABLE_CANDIDATES)))         return 'groups';
        if (in_array($t, array_map('strtolower', self::CONFIG_TABLE_CANDIDATES)))        return 'configs';
        return '';
    }

    // ── Full sync ──────────────────────────────────────────────────────────
    public function syncAll(int $triggeredBy, string $ip, ?callable $progressCallback = null): MdmImportLog
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        $this->progressCallback = $progressCallback;

        $parentLog = ApiLog::create([
            'type'         => 'mdm_pg_sync',
            'action'       => 'sync_run',
            'status'       => 'running',
            'summary'      => 'MDM PostgreSQL direct sync started',
            'triggered_by' => $triggeredBy,
            'ip_address'   => $ip,
        ]);

        $imported = $updated = $skipped = $matched = $totalFetched = 0;

        try {
            // ── Step 1: Discover schema ──────────────────────────────────────
            $t                = microtime(true);
            $deviceTable      = $this->findDeviceTable();
            $locTable         = $this->findTable(self::LOCATION_TABLE_CANDIDATES);
            $deviceParamTable = $this->findTable(self::DEVICE_PARAMS_TABLE_CANDIDATES);
            $appTable         = $this->findTable(self::APP_TABLE_CANDIDATES);
            $groupTable       = $this->findTable(self::GROUP_TABLE_CANDIDATES);
            $configTable      = $this->findTable(self::CONFIG_TABLE_CANDIDATES);

            ApiLog::step($parentLog->id, 'discover_schema',
                "Device: {$deviceTable} | GPS: " . ($locTable ?? 'none') .
                " | Params: " . ($deviceParamTable ?? 'none') .
                " | Apps: " . ($appTable ?? 'none'), [
                'duration_ms' => (int) ((microtime(true) - $t) * 1000),
            ]);

            if (! $deviceTable) {
                throw new \RuntimeException(
                    'Could not locate a device table in PostgreSQL. Tried: ' .
                    implode(', ', self::DEVICE_TABLE_CANDIDATES)
                );
            }

            // ── Step 2: Load small lookup maps ───────────────────────────────
            $groups  = $groupTable  ? $this->loadLookup($groupTable,  'id', 'name') : [];
            $configs = $configTable ? $this->loadLookup($configTable, 'id', 'name') : [];

            // ── Detect column layout once ────────────────────────────────────
            $locCols   = $locTable         ? $this->getColumns($locTable)         : [];
            $paramCols = $deviceParamTable ? $this->getColumns($deviceParamTable) : [];
            $appCols   = $appTable         ? $this->getColumns($appTable)         : [];

            $locDevCol = $this->firstMatch($locCols, ['device_id', 'deviceid', 'device']);
            $locLatCol = $this->firstMatch($locCols, ['lat', 'latitude']);
            $locLngCol = $this->firstMatch($locCols, ['lon', 'lng', 'longitude']);
            $locTsCol  = $this->firstMatch($locCols, ['ts', 'timestamp', 'device_time', 'created_at', 'time']);

            $paramDevCol = $this->firstMatch($paramCols, ['device_id', 'deviceid', 'device']);
            $paramTsCol  = $this->firstMatch($paramCols, ['ts', 'timestamp', 'createdate', 'created_at', 'time']);

            $appDevCol  = $this->firstMatch($appCols, ['device_id', 'deviceid', 'device']);
            $appNameCol = $this->firstMatch($appCols, ['name', 'app_name', 'application_name', 'pkg', 'package_name']);
            $appVerCol  = $this->firstMatch($appCols, ['installed_version', 'version', 'app_version', 'installed_ver']);

            $locReady   = $locTable         && $locDevCol   && $locLatCol && $locLngCol;
            $paramReady = $deviceParamTable && $paramDevCol;
            $appReady   = $appTable         && $appDevCol   && $appNameCol;

            // ── Step 3: Paginated fetch + upsert ────────────────────────────
            $totalDeviceCount = null;
            try { $totalDeviceCount = (int) DB::connection(self::CONN)->table($deviceTable)->count(); } catch (\Throwable) {}
            $this->reportProgress('devices', 0, $totalDeviceCount, 'running');

            $t      = microtime(true);
            $offset = 0;

            do {
                $rows = DB::connection(self::CONN)
                    ->table($deviceTable)
                    ->orderBy('id')
                    ->offset($offset)
                    ->limit(self::BATCH_SIZE)
                    ->get();

                if ($rows->isEmpty()) break;

                $batchIds = collect($rows)
                    ->map(fn ($r) => ((array) $r)['id'] ?? null)
                    ->filter()->values()->all();

                $batchLocs = ($locReady && $batchIds)
                    ? $this->fetchLocationsForBatch($locTable, $batchIds, $locDevCol, $locLatCol, $locLngCol, $locTsCol)
                    : [];

                $batchParams = ($paramReady && $batchIds)
                    ? $this->fetchDeviceParamsForBatch($deviceParamTable, $batchIds, $paramDevCol, $paramTsCol)
                    : [];

                $batchApps = ($appReady && $batchIds)
                    ? $this->fetchAppsForBatch($appTable, $batchIds, $appDevCol, $appNameCol, $appVerCol)
                    : [];

                // ── Map rows ─────────────────────────────────────────────────
                $batch       = [];   // for mdm_portal_devices
                $mdmDevBatch = [];   // for mdm_devices
                $hwBatch     = [];   // for mdm_device_hardware
                $gpsBatch    = [];   // for mdm_device_gps
                $mdmNumbers  = [];

                foreach ($rows as $raw) {
                    $arr    = (array) $raw;
                    $mdmNum = (string) $this->col($arr, ['number', 'device_number', 'mdm_number', 'id'], '');
                    if ($mdmNum === '') { $skipped++; continue; }

                    try {
                        $devId  = $arr['id'] ?? null;
                        $mapped = $this->mapDevice($arr, $groups, $configs);

                        if ($devId !== null && isset($batchLocs[$devId])) {
                            $gpsRow = $batchLocs[$devId];
                            $mapped['latitude']  = $gpsRow[$locLatCol] ?? null;
                            $mapped['longitude'] = $gpsRow[$locLngCol] ?? null;
                        }

                        if ($devId !== null && isset($batchApps[$devId])) {
                            $mapped['installation_status'] = $batchApps[$devId];
                        } elseif (empty($mapped['installation_status'])) {
                            $mapped['installation_status'] = $this->appsFromInfo($arr);
                        }

                        $mapped['info_json'] = json_encode([
                            'device' => $this->trimRow($arr),
                            'params' => $devId !== null ? $this->trimRow($batchParams[$devId] ?? null) : null,
                            'gps'    => $devId !== null ? $this->trimRow($batchLocs[$devId]   ?? null) : null,
                        ]);

                        $batch[]    = $mapped;
                        $mdmNumbers[] = $mdmNum;

                        // ── New: mdm_devices (normalized) ─────────────────
                        if ($devId !== null) {
                            $mdmDevBatch[] = $this->mapToMdmDevice($devId, $mapped);
                        }

                        // ── New: mdm_device_hardware ──────────────────────
                        if ($devId !== null && isset($batchParams[$devId])) {
                            $p = $batchParams[$devId];
                            $hwBatch[] = [
                                'mdm_device_id'              => $devId,
                                'recorded_at'                => $this->parseTs($this->col($p, ['ts','timestamp','createdate','created_at'])),
                                'battery'                    => $this->extractBattery($p),
                                'total_ram'                  => $this->firstNumericVal($p, ['total_ram','totalram','total_memory']),
                                'available_ram'              => $this->firstNumericVal($p, ['available_ram','availableram','free_memory','available_memory']),
                                'total_internal_storage'     => $this->firstNumericVal($p, ['total_internal_storage','totalinternalstorage','total_storage']),
                                'available_internal_storage' => $this->firstNumericVal($p, ['available_internal_storage','availableinternalstorage','free_storage']),
                                'brand'                      => $this->col($p, ['brand']) ?? null,
                                'manufacturer'               => $this->col($p, ['manufacturer']) ?? null,
                                'android_id'                 => $this->col($p, ['android_id','androidid']) ?? null,
                                'raw_json'                   => json_encode($this->trimRow($p)),
                                'pg_synced_at'               => now(),
                                'created_at'                 => now(),
                                'updated_at'                 => now(),
                            ];
                        }

                        // ── New: mdm_device_gps ───────────────────────────
                        if ($devId !== null && $locReady && isset($batchLocs[$devId])) {
                            $g = $batchLocs[$devId];
                            $lat = isset($g[$locLatCol]) && $g[$locLatCol] ? (float) $g[$locLatCol] : null;
                            $lng = isset($g[$locLngCol]) && $g[$locLngCol] ? (float) $g[$locLngCol] : null;
                            if ($lat && $lng) {
                                $gpsBatch[] = [
                                    'mdm_device_id' => $devId,
                                    'recorded_at'   => $locTsCol ? $this->parseTs($g[$locTsCol] ?? null) : null,
                                    'latitude'      => $lat,
                                    'longitude'     => $lng,
                                    'speed'         => $this->firstNumericVal($g, ['speed']),
                                    'accuracy'      => $this->firstNumericVal($g, ['accuracy','gpsaccuracy']),
                                    'altitude'      => $this->firstNumericVal($g, ['altitude']),
                                    'raw_json'      => json_encode($this->trimRow($g)),
                                    'pg_synced_at'  => now(),
                                    'created_at'    => now(),
                                    'updated_at'    => now(),
                                ];
                            }
                        }
                    } catch (\Throwable $e) {
                        $skipped++;
                        Log::warning('MDM PG map error', ['num' => $mdmNum, 'err' => $e->getMessage()]);
                    }
                }

                // ── Upsert mdm_portal_devices (legacy) ───────────────────────
                if ($batch) {
                    $existing = MdmPortalDevice::whereIn('mdm_number', $mdmNumbers)
                        ->pluck('mdm_number')->flip()->toArray();

                    foreach (array_chunk($batch, self::UPSERT_CHUNK) as $chunk) {
                        MdmPortalDevice::upsert($chunk, ['mdm_number'], $this->upsertColumns());
                    }

                    foreach ($batch as $row) {
                        isset($existing[$row['mdm_number']]) ? $updated++ : $imported++;
                    }
                }

                // ── Upsert mdm_devices (normalized) ──────────────────────────
                if ($mdmDevBatch) {
                    foreach (array_chunk($mdmDevBatch, self::UPSERT_CHUNK) as $chunk) {
                        $this->upsertMdmDeviceChunk($chunk);
                    }
                }

                // ── Upsert mdm_device_hardware ────────────────────────────────
                if ($hwBatch) {
                    foreach (array_chunk($hwBatch, self::UPSERT_CHUNK) as $chunk) {
                        MdmDeviceHardware::upsert($chunk, ['mdm_device_id'], [
                            'recorded_at','battery','total_ram','available_ram',
                            'total_internal_storage','available_internal_storage',
                            'brand','manufacturer','android_id','raw_json','pg_synced_at','updated_at',
                        ]);
                    }
                }

                // ── Upsert mdm_device_gps ─────────────────────────────────────
                if ($gpsBatch) {
                    foreach (array_chunk($gpsBatch, self::UPSERT_CHUNK) as $chunk) {
                        MdmDeviceGps::upsert($chunk, ['mdm_device_id'], [
                            'recorded_at','latitude','longitude','speed','accuracy','altitude',
                            'raw_json','pg_synced_at','updated_at',
                        ]);
                    }
                }

                $rowCount      = count($rows);
                $totalFetched += $rowCount;
                $offset       += self::BATCH_SIZE;

                $this->reportProgress('devices', $totalFetched, $totalDeviceCount, 'running');

                $pagesDone = (int) ($offset / self::BATCH_SIZE);
                if ($pagesDone % 10 === 0) {
                    ApiLog::step($parentLog->id, 'fetch_progress',
                        "Page {$pagesDone}: {$totalFetched} processed — {$imported} new, {$updated} updated", [
                        'records_in' => $totalFetched,
                    ]);
                }

                unset($rows, $batch, $mdmDevBatch, $hwBatch, $gpsBatch,
                      $batchIds, $batchLocs, $batchParams, $batchApps, $mdmNumbers, $existing);
                gc_collect_cycles();

            } while ($rowCount === self::BATCH_SIZE);

            ApiLog::step($parentLog->id, 'sync_devices',
                "{$totalFetched} devices processed — {$imported} new, {$updated} updated, {$skipped} skipped", [
                'duration_ms' => (int) ((microtime(true) - $t) * 1000),
                'records_out' => $imported + $updated,
            ]);
            $this->reportProgress('devices', $totalFetched, $totalFetched, 'done');

            // ── Step 4: Sync additional normalized tables ───────────────────
            $t = microtime(true);
            $bp = $this->syncBaseParams();
            $ll = $this->syncLocationLatest();
            $lh = $this->syncLocationHistory();
            ApiLog::step($parentLog->id, 'sync_plugin_tables',
                "Base params: {$bp} | Location latest: {$ll} | Location history: {$lh} new", [
                'duration_ms' => (int) ((microtime(true) - $t) * 1000),
            ]);

            // ── Step 5: Auto-match IMEI / Serial ───────────────────────────
            $t       = microtime(true);
            $matched = $this->runAutoMatch();
            ApiLog::step($parentLog->id, 'auto_match',
                "Auto-matched {$matched} devices to internal inventory", [
                'duration_ms' => (int) ((microtime(true) - $t) * 1000),
                'records_out' => $matched,
            ]);

            $parentLog->finish('success',
                "PG sync complete: {$totalFetched} fetched, {$imported} new, " .
                "{$updated} updated, {$skipped} skipped, {$matched} matched");

        } catch (\Throwable $e) {
            $short = mb_substr($e->getMessage(), 0, 300);
            $parentLog->finish('failed', "PG sync failed: {$short}", ['error_message' => $e->getMessage()]);
            ApiLog::step($parentLog->id, 'error', $short, ['status' => 'failed']);
            Log::error('MDM PG sync failed', ['error' => $e->getMessage()]);
        }

        return MdmImportLog::create([
            'imported_by'  => $triggeredBy,
            'filename'     => 'pg_direct_sync_' . now()->format('Ymd_His'),
            'total_rows'   => $totalFetched,
            'imported'     => $imported,
            'updated'      => $updated,
            'skipped'      => $skipped,
            'auto_matched' => $matched,
            'status'       => $parentLog->isSuccess() ? 'completed' : 'failed',
            'notes'        => $parentLog->error_message ?? $parentLog->summary,
        ]);
    }

    // ── Sync plugin_deviceinfo_deviceparams → mdm_device_params ─────────────
    private function syncBaseParams(): int
    {
        $table = $this->findTable(self::BASE_PARAMS_TABLE_CANDIDATES);
        if (! $table) return 0;

        $cols   = $this->getColumns($table);
        $devCol = $this->firstMatch($cols, ['deviceid','device_id','device']);
        $tsCol  = $this->firstMatch($cols, ['ts','timestamp','createdate','created_at']);
        if (! $devCol) return 0;

        $allIds = MdmDevice::pluck('id')->all();
        if (empty($allIds)) {
            $this->reportProgress('base_params', 0, 0, 'skipped');
            return 0;
        }

        $total  = count($allIds);
        $synced = 0;
        $this->reportProgress('base_params', 0, $total, 'running');

        foreach (array_chunk($allIds, 500) as $batch) {
            $phs = implode(',', array_fill(0, count($batch), '?'));
            $ord = $tsCol ? "ORDER BY {$devCol}, {$tsCol} DESC" : "ORDER BY {$devCol}";
            try {
                $rows = DB::connection(self::CONN)->select(
                    "SELECT DISTINCT ON ({$devCol}) * FROM {$table} WHERE {$devCol} IN ({$phs}) {$ord}",
                    $batch
                );
            } catch (\Throwable $e) {
                Log::warning('MDM PG base_params sync error', ['err' => $e->getMessage()]);
                continue;
            }

            $records = [];
            foreach ($rows as $row) {
                $arr   = (array) $row;
                $devId = $arr[$devCol] ?? null;
                if (! $devId) continue;
                $records[] = [
                    'mdm_device_id' => $devId,
                    'recorded_at'   => $tsCol ? $this->parseTs($arr[$tsCol] ?? null) : null,
                    'raw_json'      => json_encode($this->trimRow($arr)),
                    'pg_synced_at'  => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            foreach (array_chunk($records, self::UPSERT_CHUNK) as $chunk) {
                MdmDeviceParams::upsert($chunk, ['mdm_device_id'],
                    ['recorded_at','raw_json','pg_synced_at','updated_at']);
            }
            $synced += count($records);
            $this->reportProgress('base_params', $synced, $total, 'running');
            unset($rows, $records);
            gc_collect_cycles();
        }
        $this->reportProgress('base_params', $synced, $synced, 'done');
        return $synced;
    }

    // ── Sync plugin_devicelocations_latest → mdm_location_latest ────────────
    private function syncLocationLatest(): int
    {
        $table = $this->findTable(self::LOCATION_LATEST_TABLE_CANDIDATES);
        if (! $table) return 0;

        $cols   = $this->getColumns($table);
        $devCol = $this->firstMatch($cols, ['deviceid','device_id','device']);
        $latCol = $this->firstMatch($cols, ['lat','latitude']);
        $lngCol = $this->firstMatch($cols, ['lon','lng','longitude']);
        $tsCol  = $this->firstMatch($cols, ['ts','timestamp','createdate','created_at','time']);
        if (! $devCol || ! $latCol || ! $lngCol) return 0;

        $allIds = MdmDevice::pluck('id')->all();
        if (empty($allIds)) {
            $this->reportProgress('location_latest', 0, 0, 'skipped');
            return 0;
        }

        $total  = count($allIds);
        $synced = 0;
        $this->reportProgress('location_latest', 0, $total, 'running');

        foreach (array_chunk($allIds, 500) as $batch) {
            $phs = implode(',', array_fill(0, count($batch), '?'));
            try {
                $rows = DB::connection(self::CONN)->select(
                    "SELECT * FROM {$table} WHERE {$devCol} IN ({$phs})", $batch
                );
            } catch (\Throwable $e) {
                Log::warning('MDM PG location_latest sync error', ['err' => $e->getMessage()]);
                continue;
            }

            $records = [];
            foreach ($rows as $row) {
                $arr   = (array) $row;
                $devId = $arr[$devCol] ?? null;
                if (! $devId) continue;
                $lat = isset($arr[$latCol]) && $arr[$latCol] ? (float) $arr[$latCol] : null;
                $lng = isset($arr[$lngCol]) && $arr[$lngCol] ? (float) $arr[$lngCol] : null;
                $records[] = [
                    'mdm_device_id' => $devId,
                    'recorded_at'   => $tsCol ? $this->parseTs($arr[$tsCol] ?? null) : null,
                    'latitude'      => $lat,
                    'longitude'     => $lng,
                    'speed'         => $this->firstNumericVal($arr, ['speed']),
                    'accuracy'      => $this->firstNumericVal($arr, ['accuracy','gpsaccuracy']),
                    'raw_json'      => json_encode($this->trimRow($arr)),
                    'pg_synced_at'  => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            foreach (array_chunk($records, self::UPSERT_CHUNK) as $chunk) {
                MdmLocationLatest::upsert($chunk, ['mdm_device_id'],
                    ['recorded_at','latitude','longitude','speed','accuracy','raw_json','pg_synced_at','updated_at']);
            }
            $synced += count($records);
            $this->reportProgress('location_latest', $synced, $total, 'running');
            unset($rows, $records);
            gc_collect_cycles();
        }
        $this->reportProgress('location_latest', $synced, $synced, 'done');
        return $synced;
    }

    // ── Sync plugin_devicelocations_history → mdm_location_history (incremental)
    private function syncLocationHistory(): int
    {
        $table = $this->findTable(self::LOCATION_HISTORY_TABLE_CANDIDATES);
        if (! $table) return 0;

        $cols   = $this->getColumns($table);
        $devCol = $this->firstMatch($cols, ['deviceid','device_id','device']);
        $latCol = $this->firstMatch($cols, ['lat','latitude']);
        $lngCol = $this->firstMatch($cols, ['lon','lng','longitude']);
        $tsCol  = $this->firstMatch($cols, ['ts','timestamp','createdate','created_at','time']);
        if (! $devCol || ! $latCol || ! $lngCol) return 0;

        // Only fetch records newer than what we've already synced
        $maxPgId     = MdmLocationHistory::max('pg_id') ?? 0;
        $localDevIds = array_flip(MdmDevice::pluck('id')->all());
        $synced      = 0;
        $rowCount    = 500;
        $this->reportProgress('location_history', 0, null, 'running');

        while ($rowCount === 500) {
            try {
                $rows = DB::connection(self::CONN)->select(
                    "SELECT * FROM {$table} WHERE id > ? ORDER BY id LIMIT 500",
                    [$maxPgId]
                );
            } catch (\Throwable $e) {
                Log::warning('MDM PG location_history sync error', ['err' => $e->getMessage()]);
                break;
            }

            $rowCount = count($rows);
            if (! $rowCount) break;

            $records = [];
            foreach ($rows as $row) {
                $arr   = (array) $row;
                $devId = $arr[$devCol] ?? null;
                $pgId  = $arr['id']    ?? null;
                if (! $devId || ! isset($localDevIds[$devId])) continue;
                $lat = isset($arr[$latCol]) && $arr[$latCol] ? (float) $arr[$latCol] : null;
                $lng = isset($arr[$lngCol]) && $arr[$lngCol] ? (float) $arr[$lngCol] : null;
                if (! $lat || ! $lng) continue;

                $records[] = [
                    'mdm_device_id' => $devId,
                    'pg_id'         => $pgId,
                    'recorded_at'   => $tsCol ? $this->parseTs($arr[$tsCol] ?? null) : null,
                    'latitude'      => $lat,
                    'longitude'     => $lng,
                    'speed'         => $this->firstNumericVal($arr, ['speed']),
                    'accuracy'      => $this->firstNumericVal($arr, ['accuracy','gpsaccuracy']),
                    'raw_json'      => json_encode($this->trimRow($arr)),
                    'pg_synced_at'  => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];

                if ($pgId > $maxPgId) $maxPgId = $pgId;
            }

            foreach (array_chunk($records, self::UPSERT_CHUNK) as $chunk) {
                DB::table('mdm_location_history')->insert($chunk);
            }
            $synced += count($records);
            $this->reportProgress('location_history', $synced, null, 'running');
            unset($rows, $records);
            gc_collect_cycles();
        }
        $this->reportProgress('location_history', $synced, $synced, 'done');
        return $synced;
    }

    // ── Per-batch data fetchers ─────────────────────────────────────────────

    private function fetchLocationsForBatch(
        string $table, array $deviceIds, string $devCol,
        string $latCol, string $lngCol, ?string $tsCol
    ): array {
        try {
            $phs     = implode(',', array_fill(0, count($deviceIds), '?'));
            $orderBy = $tsCol ? "ORDER BY {$devCol}, {$tsCol} DESC" : "ORDER BY {$devCol}";
            $rows    = DB::connection(self::CONN)->select("
                SELECT DISTINCT ON ({$devCol}) *
                FROM {$table}
                WHERE {$devCol} IN ({$phs})
                  AND {$latCol} IS NOT NULL
                  AND {$latCol}::text != '0'
                {$orderBy}
            ", $deviceIds);
            $map = [];
            foreach ($rows as $r) {
                $a = (array) $r;
                $map[$a[$devCol]] = $a;
            }
            return $map;
        } catch (\Throwable $e) {
            Log::warning('MDM PG: location fetch error', ['err' => $e->getMessage()]);
            return [];
        }
    }

    private function fetchDeviceParamsForBatch(
        string $table, array $deviceIds, string $devCol, ?string $tsCol
    ): array {
        try {
            $phs     = implode(',', array_fill(0, count($deviceIds), '?'));
            $orderBy = $tsCol ? "ORDER BY {$devCol}, {$tsCol} DESC" : "ORDER BY {$devCol}";
            $rows    = DB::connection(self::CONN)->select(
                "SELECT DISTINCT ON ({$devCol}) * FROM {$table} WHERE {$devCol} IN ({$phs}) {$orderBy}",
                $deviceIds
            );
            $map = [];
            foreach ($rows as $r) {
                $a = (array) $r;
                $map[$a[$devCol]] = $a;
            }
            return $map;
        } catch (\Throwable $e) {
            Log::warning('MDM PG: device params fetch error', ['err' => $e->getMessage()]);
            return [];
        }
    }

    private function fetchAppsForBatch(
        string $table, array $deviceIds, string $devCol, string $nameCol, ?string $verCol
    ): array {
        try {
            $phs    = implode(',', array_fill(0, count($deviceIds), '?'));
            $select = array_filter([$devCol, $nameCol, $verCol]);
            $rows   = DB::connection(self::CONN)->select(
                "SELECT " . implode(', ', $select) . " FROM {$table} WHERE {$devCol} IN ({$phs})",
                $deviceIds
            );
            $map = [];
            foreach ($rows as $row) {
                $arr  = (array) $row;
                $did  = $arr[$devCol]  ?? null;
                $name = $arr[$nameCol] ?? 'Unknown';
                $ver  = $verCol ? ($arr[$verCol] ?? '') : '';
                if ($did === null) continue;
                $map[$did][] = "- {$name}: installed {$ver}";
            }
            return array_map(fn ($lines) => implode("\n", $lines), $map);
        } catch (\Throwable $e) {
            Log::warning('MDM PG: apps fetch error', ['err' => $e->getMessage()]);
            return [];
        }
    }

    // ── Device-info + GPS report ────────────────────────────────────────────
    public function fetchDeviceReport(int $page = 1, int $perPage = 50, string $search = ''): array
    {
        $deviceTable      = $this->findDeviceTable();
        $gpsTable         = $this->findGpsTable();
        $deviceParamTable = $this->findDeviceParamsTable();

        $gpsCols   = $gpsTable         ? $this->getColumns($gpsTable)         : [];
        $paramCols = $deviceParamTable ? $this->getColumns($deviceParamTable) : [];

        $gpsDevCol  = $this->firstMatch($gpsCols,   ['deviceid', 'device_id', 'device']);
        $gpsTsCol   = $this->firstMatch($gpsCols,   ['ts', 'timestamp', 'createdate', 'created_at', 'time']);
        $gpsLatCol  = $this->firstMatch($gpsCols,   ['lat', 'latitude']);
        $gpsLngCol  = $this->firstMatch($gpsCols,   ['lon', 'lng', 'longitude']);

        $paramDevCol = $this->firstMatch($paramCols, ['deviceid', 'device_id', 'device']);
        $paramTsCol  = $this->firstMatch($paramCols, ['ts', 'timestamp', 'createdate', 'created_at', 'time']);

        $skipCols         = array_filter(['id', $paramDevCol]);
        $displayParamCols = array_values(array_filter($paramCols, fn ($c) => ! in_array($c, $skipCols)));

        $hasGps    = $gpsTable         && $gpsDevCol  && $gpsLatCol && $gpsLngCol;
        $hasParams = $deviceParamTable && $paramDevCol;

        $query = DB::connection(self::CONN)->table($deviceTable ?? 'hmdm_device');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('number',      'ILIKE', "%{$search}%")
                  ->orWhere('imei',        'ILIKE', "%{$search}%")
                  ->orWhere('serial',      'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $total   = $query->count();
        $devices = $query->orderBy('number')->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $ids     = $devices->pluck('id')->filter()->values()->all();

        $gpsMap   = [];
        $paramMap = [];

        if ($hasGps && $ids) {
            $phs  = implode(',', array_fill(0, count($ids), '?'));
            $ord  = $gpsTsCol ? "ORDER BY {$gpsDevCol}, {$gpsTsCol} DESC" : "ORDER BY {$gpsDevCol}";
            $rows = DB::connection(self::CONN)->select(
                "SELECT DISTINCT ON ({$gpsDevCol}) * FROM {$gpsTable} WHERE {$gpsDevCol} IN ({$phs}) {$ord}", $ids
            );
            foreach ($rows as $r) { $a = (array) $r; $gpsMap[$a[$gpsDevCol]] = $a; }
        }

        if ($hasParams && $ids) {
            $phs  = implode(',', array_fill(0, count($ids), '?'));
            $ord  = $paramTsCol ? "ORDER BY {$paramDevCol}, {$paramTsCol} DESC" : "ORDER BY {$paramDevCol}";
            $rows = DB::connection(self::CONN)->select(
                "SELECT DISTINCT ON ({$paramDevCol}) * FROM {$deviceParamTable} WHERE {$paramDevCol} IN ({$phs}) {$ord}", $ids
            );
            foreach ($rows as $r) { $a = (array) $r; $paramMap[$a[$paramDevCol]] = $a; }
        }

        $merged = [];
        foreach ($devices as $dev) {
            $a  = (array) $dev;
            $id = $a['id'] ?? null;
            $merged[] = [
                'device' => $a,
                'gps'    => $id !== null ? ($gpsMap[$id]   ?? null) : null,
                'params' => $id !== null ? ($paramMap[$id] ?? null) : null,
            ];
        }

        return [
            'rows'             => $merged,
            'total'            => $total,
            'page'             => $page,
            'perPage'          => $perPage,
            'deviceTable'      => $deviceTable,
            'gpsTable'         => $gpsTable,
            'deviceParamTable' => $deviceParamTable,
            'gpsLatCol'        => $gpsLatCol,
            'gpsLngCol'        => $gpsLngCol,
            'gpsTsCol'         => $gpsTsCol,
            'paramCols'        => $displayParamCols,
            'hasGps'           => $hasGps,
            'hasParams'        => $hasParams,
            'search'           => $search,
        ];
    }

    // ── Table discovery ─────────────────────────────────────────────────────

    private function findGpsTable(): ?string     { return $this->findTable(self::LOCATION_TABLE_CANDIDATES); }
    private function findDeviceParamsTable(): ?string { return $this->findTable(self::DEVICE_PARAMS_TABLE_CANDIDATES); }

    private function findDeviceTable(): ?string
    {
        $found = $this->findTable(self::DEVICE_TABLE_CANDIDATES);
        if ($found) return $found;
        return $this->findTableByColumns(['imei', 'serial', 'number', 'last_update', 'lastupdate'], 2);
    }

    private function findTable(array $candidates): ?string
    {
        if ($this->existingTables === null) {
            $rows                 = DB::connection(self::CONN)->select(
                "SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'"
            );
            $this->existingTables = array_map(fn ($r) => $r->table_name, $rows);
        }
        $lower = array_map('strtolower', $this->existingTables);
        foreach ($candidates as $c) {
            $idx = array_search(strtolower($c), $lower, true);
            if ($idx !== false) return $this->existingTables[$idx];
        }
        return null;
    }

    private function findTableByColumns(array $cols, int $minMatches): ?string
    {
        $list = "'" . implode("','", array_map('addslashes', $cols)) . "'";
        $rows = DB::connection(self::CONN)->select("
            SELECT table_name, COUNT(*) AS hits
            FROM information_schema.columns
            WHERE table_schema = 'public' AND column_name IN ({$list})
            GROUP BY table_name HAVING COUNT(*) >= ? ORDER BY hits DESC LIMIT 1
        ", [$minMatches]);
        return $rows[0]->table_name ?? null;
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function reportProgress(string $stage, int $done, ?int $total, string $status = 'running'): void
    {
        if ($this->progressCallback) {
            ($this->progressCallback)($stage, $done, $total, $status);
        }
    }

    private function trimRow(?array $row): ?array
    {
        if ($row === null) return null;
        $out = [];
        foreach ($row as $k => $v) {
            if ($v === null) continue;
            if (is_string($v) && strlen($v) > 8192) continue;
            $out[$k] = $v;
        }
        return $out ?: null;
    }

    private function extractBattery(array $row): ?int
    {
        foreach (['battery','battery_level','batterylevel','battery_percentage',
                  'batterycharge','batterychargepercent','batterychargelevel'] as $k) {
            if (array_key_exists($k, $row) && is_numeric($row[$k])) {
                $v = (int) $row[$k];
                if ($v >= 0 && $v <= 100) return $v;
            }
        }
        return null;
    }

    private function firstNumericVal(array $row, array $keys): ?int
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && is_numeric($row[$k])) {
                return (int) $row[$k];
            }
        }
        return null;
    }

    private function loadLookup(string $table, string $keyCol, string $valCol): array
    {
        try {
            $rows = DB::connection(self::CONN)->table($table)->select($keyCol, $valCol)->get();
            $map  = [];
            foreach ($rows as $row) {
                $arr = (array) $row;
                if (isset($arr[$keyCol])) $map[$arr[$keyCol]] = $arr[$valCol] ?? null;
            }
            return $map;
        } catch (\Throwable $e) {
            Log::warning("MDM PG: lookup from {$table} failed", ['err' => $e->getMessage()]);
            return [];
        }
    }

    private function getColumns(string $table): array
    {
        $cols = DB::connection(self::CONN)->select(
            "SELECT column_name FROM information_schema.columns WHERE table_schema='public' AND table_name=?",
            [$table]
        );
        return array_map(fn ($c) => strtolower($c->column_name), $cols);
    }

    private function firstMatch(array $haystack, array $needles): ?string
    {
        foreach ($needles as $n) {
            if (in_array(strtolower($n), $haystack, true)) return $n;
        }
        return null;
    }

    private function col(array $row, array $candidates, mixed $default = null): mixed
    {
        foreach ($candidates as $c) {
            if (array_key_exists($c, $row)) return $row[$c];
            $camel = lcfirst(str_replace('_', '', ucwords($c, '_')));
            if (array_key_exists($camel, $row)) return $row[$camel];
        }
        return $default;
    }

    // ── Device mappers ──────────────────────────────────────────────────────

    /**
     * The devices table itself has no serial/model/androidVersion columns and imei
     * is frequently blank — but the infojson blob (populated by the launcher app)
     * carries these fields nested. Decode it so mapDevice() can fall back to it.
     */
    private function decodeInfoJson(array $d): ?array
    {
        $raw = $d['infojson'] ?? $d['info_json'] ?? null;
        if (empty($raw)) return null;
        if (is_array($raw)) return $raw;
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function mapDevice(array $d, array $groups, array $configs): array
    {
        $info = $this->decodeInfoJson($d);

        $status = 'unknown';
        $sc     = $this->col($d, ['statuscode', 'status_code', 'online', 'status']);
        if ($sc !== null) {
            if (is_bool($sc))
                $status = $sc ? 'on' : 'off';
            elseif (is_int($sc) || ctype_digit((string) $sc))
                $status = ((int) $sc === 1) ? 'on' : 'off';
            elseif (in_array(strtolower((string) $sc), ['on', 'online', 'true', '1']))
                $status = 'on';
            else
                $status = 'off';
        }

        $groupId  = $this->col($d, ['group_id', 'groupid', 'group']);
        $configId = $this->col($d, ['configuration_id', 'configurationid', 'config_id']);
        $perm     = $this->col($d, ['permissions_status', 'permissionsstatus', 'permissionstatus', 'permissions']);
        $number   = (string) $this->col($d, ['number', 'device_number', 'mdm_number', 'id'], '');

        $imei = $this->col($d, ['imei']) ?: ($info['imei'] ?? null);
        $serial = $this->col($d, ['serial', 'serial_number', 'serialnumber']) ?: ($info['serial'] ?? null);
        $model = $this->col($d, ['model']) ?: ($info['model'] ?? null);
        $androidVersion = $this->col($d, ['android_version', 'androidversion', 'androidver']) ?: ($info['androidVersion'] ?? null);
        $mdmMode = $this->col($d, ['mdmmode', 'mdm_mode']);
        if ($mdmMode === null) $mdmMode = $info['mdmMode'] ?? null;
        $kioskMode = $this->col($d, ['kioskmode', 'kiosk_mode']);
        if ($kioskMode === null) $kioskMode = $info['kioskMode'] ?? null;

        return [
            'mdm_number'          => $number,
            'imei'                => $imei ?: null,
            'serial_number'       => $serial ?: null,
            'phone'               => $this->col($d, ['phone', 'phone_number'])                             ?? null,
            'description'         => $this->col($d, ['description'])                                       ?? null,
            'mdm_group'           => $groupId  ? ($groups[(int) $groupId]   ?? null) : null,
            'configuration'       => $configId ? ($configs[(int) $configId] ?? null) : null,
            'launcher_version'    => $this->col($d, ['launcher_version', 'launcherversion'])               ?? null,
            'device_status'       => $status,
            'permission_status'   => is_string($perm) ? $perm : null,
            'installation_status' => null,
            'sync_time'           => $this->parseTs($this->col($d, ['lastupdate', 'last_update', 'sync_time', 'updated_at'])),
            'model'               => $model ?: null,
            'default_launcher'    => $this->col($d, ['launcher', 'default_launcher', 'defaultlauncher'])   ?? null,
            'ip_address'          => $this->col($d, ['ipaddress', 'ip_address', 'ip'])                     ?? null,
            'public_ip'           => $this->col($d, ['publicip', 'public_ip', 'publicipaddress'])          ?? null,
            'mdm_mode'            => (bool) ($mdmMode   ?? false),
            'kiosk_mode'          => (bool) ($kioskMode ?? false),
            'enrollment_date'     => $this->parseTs($this->col($d, ['enrollment_date', 'enrollmentdate', 'enrolled_at', 'createdate', 'created_at'])),
            'android_version'     => (string) ($androidVersion ?? ''),
            'latitude'            => $this->col($d, ['latitude', 'lat'])                                   ?? null,
            'longitude'           => $this->col($d, ['longitude', 'lon', 'lng'])                           ?? null,
            'division'            => $this->col($d, ['division'])                                          ?? null,
            'mdm_status'          => $this->col($d, ['mdm_status', 'mdmstatus'])                           ?? 'Active',
            'synced_at'           => now(),
        ];
    }

    private function mapToMdmDevice(int $devId, array $mapped): array
    {
        return [
            'id'                  => $devId,
            'pg_number'           => $mapped['mdm_number'],
            'imei'                => $mapped['imei'],
            'serial_number'       => $mapped['serial_number'],
            'phone'               => $mapped['phone'],
            'description'         => $mapped['description'],
            'mdm_group'           => $mapped['mdm_group'],
            'configuration'       => $mapped['configuration'],
            'launcher_version'    => $mapped['launcher_version'],
            'mdm_mode'            => $mapped['mdm_mode'],
            'kiosk_mode'          => $mapped['kiosk_mode'],
            'default_launcher'    => $mapped['default_launcher'],
            'device_status'       => $mapped['device_status'],
            'permission_status'   => $mapped['permission_status'],
            'installation_status' => $mapped['installation_status'],
            'sync_time'           => $mapped['sync_time'],
            'mdm_status'          => $mapped['mdm_status'],
            'model'               => $mapped['model'],
            'android_version'     => $mapped['android_version'],
            'ip_address'          => $mapped['ip_address'],
            'public_ip'           => $mapped['public_ip'],
            'latitude'            => $mapped['latitude'],
            'longitude'           => $mapped['longitude'],
            'division'            => $mapped['division'],
            'enrollment_date'     => $mapped['enrollment_date'],
            'pg_synced_at'        => now(),
            'created_at'          => now(),
            'updated_at'          => now(),
        ];
    }

    /**
     * Upsert one chunk of mdm_devices rows keyed on `id`. A device can be
     * re-enrolled in the MDM portal under a brand-new PG id while keeping the
     * same human-readable `pg_number`; when that happens the stale row (old id,
     * same pg_number) is still sitting locally and collides with the incoming
     * insert on the `pg_number` unique index (MySQL can't satisfy the `id` and
     * `pg_number` unique constraints against two different existing rows at
     * once, so ON DUPLICATE KEY UPDATE fails with error 1062 instead of
     * updating). Resolve that by deleting the stale row first, carrying its
     * manual local_device_id/local_employee_id link forward onto the new id.
     */
    private function upsertMdmDeviceChunk(array $chunk): void
    {
        $pgNumberToNewId = array_column($chunk, 'id', 'pg_number');

        $staleRows = MdmDevice::whereIn('pg_number', array_keys($pgNumberToNewId))
            ->whereNotIn('id', array_values($pgNumberToNewId))
            ->get(['id', 'pg_number', 'local_device_id', 'local_employee_id']);

        $carryForward = [];
        foreach ($staleRows as $stale) {
            if ($stale->local_device_id || $stale->local_employee_id) {
                $carryForward[$pgNumberToNewId[$stale->pg_number]] = [
                    'local_device_id'   => $stale->local_device_id,
                    'local_employee_id' => $stale->local_employee_id,
                ];
            }
        }

        if ($staleRows->isNotEmpty()) {
            Log::warning('MDM sync: reassigning pg_number to a new device id', [
                'stale_ids' => $staleRows->pluck('id', 'pg_number')->toArray(),
            ]);
            MdmDevice::whereIn('id', $staleRows->pluck('id'))->delete();
        }

        try {
            MdmDevice::upsert($chunk, ['id'], $this->mdmDeviceUpsertCols());
        } catch (\Throwable $e) {
            Log::warning('MDM sync: mdm_devices chunk upsert failed, skipping chunk', ['err' => $e->getMessage()]);
            return;
        }

        foreach ($carryForward as $newId => $links) {
            MdmDevice::where('id', $newId)
                ->whereNull('local_device_id')
                ->whereNull('local_employee_id')
                ->update($links);
        }
    }

    private function mdmDeviceUpsertCols(): array
    {
        return [
            'pg_number', 'imei', 'serial_number', 'phone', 'description',
            'mdm_group', 'configuration', 'launcher_version', 'mdm_mode', 'kiosk_mode',
            'default_launcher', 'device_status', 'permission_status', 'installation_status',
            'sync_time', 'mdm_status', 'model', 'android_version', 'ip_address', 'public_ip',
            'latitude', 'longitude', 'division', 'enrollment_date', 'pg_synced_at', 'updated_at',
        ];
    }

    private function appsFromInfo(array $d): ?string
    {
        $raw = $this->col($d, ['info', 'device_info', 'info_json', 'applications_status', 'applicationsstatus']);
        if (! $raw) return null;
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($decoded)) return null;
        $appList = $decoded['applicationsStatus'] ?? $decoded['applications'] ?? null;
        if (! is_array($appList)) return null;
        $lines = [];
        foreach ($appList as $app) {
            $name  = $app['name'] ?? $app['pkg'] ?? ($app['packageName'] ?? 'Unknown');
            $inst  = $app['installedVersion'] ?? $app['version']         ?? '';
            $avail = $app['availableVersion'] ?? $app['requiredVersion'] ?? '';
            $lines[] = "- {$name}: installed {$inst}" . ($avail ? ", available {$avail}" : '');
        }
        return $lines ? implode("\n", $lines) : null;
    }

    private function parseTs(mixed $val): ?Carbon
    {
        if ($val === null || $val === '') return null;
        try {
            if (is_numeric($val)) {
                $v = (int) $val;
                return $v > 1_000_000_000_000
                    ? Carbon::createFromTimestampMs($v)
                    : Carbon::createFromTimestamp($v);
            }
            return Carbon::parse($val);
        } catch (\Throwable) {
            return null;
        }
    }

    private function upsertColumns(): array
    {
        return [
            'imei', 'serial_number', 'phone', 'description', 'mdm_group', 'configuration',
            'launcher_version', 'mdm_mode', 'kiosk_mode', 'default_launcher',
            'device_status', 'permission_status', 'installation_status', 'sync_time', 'mdm_status',
            'model', 'android_version', 'ip_address', 'public_ip',
            'latitude', 'longitude', 'division', 'info_json', 'enrollment_date', 'synced_at',
        ];
    }

    private function runAutoMatch(): int
    {
        $matched = 0;
        $this->reportProgress('auto_match', 0, null, 'running');

        // Auto-match mdm_portal_devices
        MdmPortalDevice::whereNull('device_id')->each(function ($mdm) use (&$matched) {
            $device = $this->findDeviceByIdentifiers($mdm->imei, $mdm->serial_number);
            if ($device) {
                $update = ['device_id' => $device->id];
                if (! $mdm->employee_id && $device->current_employee_id) {
                    $update['employee_id'] = $device->current_employee_id;
                }
                $mdm->update($update);
                $matched++;
            }
        });

        // Auto-match mdm_devices
        MdmDevice::whereNull('local_device_id')->each(function ($mdm) {
            $device = $this->findDeviceByIdentifiers($mdm->imei, $mdm->serial_number);
            if ($device) {
                $update = ['local_device_id' => $device->id];
                if (! $mdm->local_employee_id && $device->current_employee_id) {
                    $update['local_employee_id'] = $device->current_employee_id;
                }
                $mdm->update($update);
            }
        });

        $this->reportProgress('auto_match', $matched, $matched, 'done');
        return $matched;
    }

    private function findDeviceByIdentifiers(?string $imei, ?string $serial): ?\App\Models\Device
    {
        $device = null;
        if ($imei) {
            $device = \App\Models\Device::where('imei1', $imei)->orWhere('imei2', $imei)->first();
        }
        if (! $device && $serial) {
            $device = \App\Models\Device::where('serial_number', $serial)->first();
        }
        return $device;
    }
}

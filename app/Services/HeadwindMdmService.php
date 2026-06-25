<?php

namespace App\Services;

use App\Models\ApiLog;
use App\Models\MdmImportLog;
use App\Models\MdmPortalDevice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeadwindMdmService
{
    private string  $baseUrl;
    private string  $login;
    private string  $password;
    private ?string $token    = null;
    private int     $pageSize;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('mdm.server_url'), '/');
        $this->login    = config('mdm.login');
        $this->password = config('mdm.password');
        $this->pageSize = (int) config('mdm.page_size', 100);
    }

    // ── Step 1: Authenticate → get JWT ───────────────────────────────────────
    public function authenticate(): string
    {
        $response = Http::timeout(15)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . '/rest/public/jwt/login', [
                'login'    => $this->login,
                'password' => $this->password,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "MDM auth failed — HTTP {$response->status()}: " . $response->body()
            );
        }

        $token = $response->json('id_token');
        if (! $token) {
            throw new \RuntimeException('MDM auth failed — no id_token in response: ' . $response->body());
        }

        $this->token = $token;
        return $token;
    }

    private function authHeader(): array
    {
        return [
            'Authorization' => 'Bearer ' . ($this->token ?? $this->authenticate()),
            'Content-Type'  => 'application/json',
        ];
    }

    // ── Step 2: Fetch one page of devices ────────────────────────────────────
    public function fetchPage(int $page = 1, ?int $configurationId = null): array
    {
        $this->pageSize = 2;
        $payload = [
            'groupId'         => -1,
            'configurationId' => $configurationId ?? -1,
            'pageNum'         => $page,
            'pageSize'        => $this->pageSize,
            'sortBy'          => null,
            'value'           => null,
        ];
        //var_dump($payload);die;

        $response = Http::timeout(30)
            ->withHeaders($this->authHeader())
            ->post($this->baseUrl . '/rest/private/devices/search', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "MDM device fetch failed — HTTP {$response->status()} page {$page}: " . $response->body()
            );
        }

        $body = $response->json();        // Headwind wraps in { status:"OK", data: { items:[], totalItemsCount:N } }
        
        $items = $body['data']['devices']['items'] ?? [];
        $total = $body['data']['devices']['totalItemsCount'] ?? $body['data']['devices']['totalItemsCount'] ?? count($items);
       
        return ['items' => $items, 'total' => (int) $total];
    }

    // ── Step 3: Paginate — kept for ad-hoc use (sync uses page-by-page streaming) ──
    public function fetchAll(?int $configurationId = null): array
    {
        $page  = 1;
        $all   = [];
        $total = PHP_INT_MAX;

        while (count($all) < $total) {
            $result = $this->fetchPage($page, $configurationId);
            $items  = $result['items'];
            $total  = $result['total'];
            if (empty($items)) break;
            array_push($all, ...$items);
            $page++;
            if (count($items) < $this->pageSize) break;
        }

        return ['devices' => $all, 'total' => $total, 'pages' => $page];
    }

    /** Columns updated on conflict — device_id / employee_id excluded to preserve auto-match links */
    private function upsertUpdateColumns(): array
    {
        return [
            'imei', 'serial_number', 'phone', 'description', 'mdm_group', 'configuration',
            'launcher_version', 'mdm_mode', 'kiosk_mode', 'default_launcher',
            'device_status', 'permission_status', 'installation_status', 'sync_time', 'mdm_status',
            'model', 'android_version', 'ip_address', 'public_ip',
            'latitude', 'longitude', 'division', 'info_json', 'enrollment_date', 'synced_at',
        ];
    }

    // ── Step 4: Map API device object → DB columns ───────────────────────────
    public static function mapDevice(array $d): array
    {
        // Online status: API uses bool `online` or string `status`
        $status = 'unknown';
        if (array_key_exists('online', $d)) {
            $status = $d['online'] ? 'on' : 'off';
        } elseif (isset($d['status'])) {
            $status = in_array(strtolower($d['status']), ['online', 'on']) ? 'on' : 'off';
        }

        // Group / configuration
        $group  = self::firstNameFrom($d['groups']         ?? null);
        $config = self::firstNameFrom($d['configurations'] ?? null);

        // Timestamps (Headwind sends epoch-ms for numeric, date-string otherwise)
        $syncTime   = self::parseTs($d['lastSyncTime']   ?? $d['last_sync_time']   ?? null);
        $enrollDate = self::parseTs($d['enrollmentDate'] ?? $d['enrollment_date']  ?? null);

        // Installation status — flatten app list to multiline text
        $installStatus = null;
        $appList = $d['applicationsStatus'] ?? $d['applications'] ?? null;
        if (is_array($appList) && ! empty($appList)) {
            $lines = [];
            foreach ($appList as $app) {
                $name  = $app['name'] ?? $app['pkg'] ?? ($app['packageName'] ?? 'Unknown');
                $inst  = $app['installedVersion'] ?? $app['version']          ?? '';
                $avail = $app['availableVersion'] ?? $app['requiredVersion']  ?? '';
                $lines[] = "- {$name}: installed {$inst}" . ($avail ? ", available {$avail}" : '');
            }
            $installStatus = implode("\n", $lines);
        }

        return [
            'mdm_number'          => (string) ($d['number'] ?? $d['id'] ?? ''),
            'imei'                => $d['imei']         ?? null,
            'serial_number'       => $d['serialNumber'] ?? $d['serial'] ?? null,
            'phone'               => $d['phone']        ?? null,
            'description'         => $d['description']  ?? null,
            'mdm_group'           => $group,
            'configuration'       => $config,
            'launcher_version'    => $d['launcherVersion'] ?? $d['launcher_version'] ?? null,
            'device_status'       => $status,
            'permission_status'   => is_string($d['permissionsStatus'] ?? null)
                                        ? $d['permissionsStatus'] : null,
            'installation_status' => $installStatus,
            'sync_time'           => $syncTime,
            'model'               => $d['model']    ?? null,
            'default_launcher'    => $d['launcher'] ?? null,
            'ip_address'          => $d['ip']       ?? $d['ip_address'] ?? null,
            'public_ip'           => $d['publicIp'] ?? null,
            'mdm_mode'            => (bool) ($d['mdmMode']   ?? $d['mdm_mode']   ?? false),
            'kiosk_mode'          => (bool) ($d['kioskMode'] ?? $d['kiosk_mode'] ?? false),
            'enrollment_date'     => $enrollDate,
            'android_version'     => (string) ($d['androidVersion'] ?? $d['android_version'] ?? ''),
            'latitude'            => $d['latitude']  ?? null,
            'longitude'           => $d['longitude'] ?? null,
            'division'            => $d['division']  ?? null,
            'mdm_status'          => $d['mdmStatus'] ?? 'Active',
            'synced_at'           => now(),
        ];
    }

    // ── Full sync with step logging (streaming page-by-page, batch upsert) ────
    public function syncWithLog(int $triggeredBy, string $ip): MdmImportLog
    {
        set_time_limit(0);   // 10 k+ devices can take minutes

        $parentLog = ApiLog::create([
            'type'         => 'mdm_sync',
            'action'       => 'sync_run',
            'status'       => 'running',
            'summary'      => 'MDM Server sync started',
            'triggered_by' => $triggeredBy,
            'ip_address'   => $ip,
        ]);

        $imported = $updated = $skipped = $matched = 0;
        $totalFetched  = 0;
        $totalReported = 0;
        $pagesDone     = 0;

        try {
            // Step 1 — Authenticate
            $t     = microtime(true);
            $token = $this->authenticate();
            ApiLog::step($parentLog->id, 'authenticate', 'JWT token obtained', [
                'duration_ms'   => (int)((microtime(true) - $t) * 1000),
                'response_data' => ['token_length' => strlen($token)],
            ]);

            // Steps 2 + 3 — Fetch each page and upsert immediately (no full accumulation)
            $t    = microtime(true);
            $page = 1;

            do {
                $result        = $this->fetchPage($page);
                $items         = $result['items'];
                $totalReported = $result['total'];   // dynamic — trust the API each time

                if (empty($items)) break;

                // Map rows, skip blanks
                $batch      = [];
                $mdmNumbers = [];
                foreach ($items as $raw) {
                    $mdmNum = trim((string) ($raw['number'] ?? $raw['id'] ?? ''));
                    if ($mdmNum === '') { $skipped++; continue; }
                    try {
                        $row              = self::mapDevice($raw);
                        $row['info_json'] = json_encode($raw);   // bypasses model cast in upsert
                        $batch[]          = $row;
                        $mdmNumbers[]     = $mdmNum;
                    } catch (\Throwable $e) {
                        $skipped++;
                        Log::warning('MDM map failed', ['mdm_number' => $mdmNum, 'err' => $e->getMessage()]);
                    }
                }

                if ($batch) {
                    // Determine which are new before upsert (one query per page)
                    $existing = MdmPortalDevice::whereIn('mdm_number', $mdmNumbers)
                        ->pluck('mdm_number')->flip()->toArray();

                    MdmPortalDevice::upsert($batch, ['mdm_number'], $this->upsertUpdateColumns());

                    foreach ($batch as $row) {
                        isset($existing[$row['mdm_number']]) ? $updated++ : $imported++;
                    }
                }

                $totalFetched += count($items);
                $pagesDone++;
                $page++;

                // Log progress every 10 pages
                if ($pagesDone % 10 === 0) {
                    ApiLog::step($parentLog->id, 'fetch_progress',
                        "Page {$pagesDone}: {$totalFetched}/{$totalReported} fetched ({$imported} new, {$updated} updated)", [
                            'records_in' => $totalFetched,
                        ]);
                }

            } while (count($items) >= $this->pageSize && $totalFetched < $totalReported);

            ApiLog::step($parentLog->id, 'fetch_upsert',
                "Fetched {$totalFetched}/{$totalReported} across {$pagesDone} pages — {$imported} new, {$updated} updated, {$skipped} skipped", [
                    'duration_ms' => (int)((microtime(true) - $t) * 1000),
                    'records_in'  => $totalFetched,
                    'records_out' => $imported + $updated,
                ]);

            // Step 4 — Auto-match IMEI / Serial
            $t       = microtime(true);
            $matched = $this->runAutoMatch();
            ApiLog::step($parentLog->id, 'auto_match',
                "Auto-matched {$matched} devices to employees via IMEI/Serial", [
                    'duration_ms' => (int)((microtime(true) - $t) * 1000),
                    'records_out' => $matched,
                ]);

            $parentLog->finish('success',
                "Sync complete: {$totalFetched} fetched, {$imported} new, {$updated} updated, {$skipped} skipped, {$matched} matched", [
                    'records_in'  => $totalFetched,
                    'records_out' => $imported + $updated,
                ]);

        } catch (\Throwable $e) {
            $short = mb_substr($e->getMessage(), 0, 300);
            $parentLog->finish('failed', "Sync failed: {$short}", ['error_message' => $e->getMessage()]);
            ApiLog::step($parentLog->id, 'error', $short, ['status' => 'failed']);
            Log::error('MDM sync failed', ['error' => $e->getMessage()]);
        }

        return MdmImportLog::create([
            'imported_by'  => $triggeredBy,
            'filename'     => 'headwind_api_sync_' . now()->format('Ymd_His'),
            'total_rows'   => $totalFetched,
            'imported'     => $imported,
            'updated'      => $updated,
            'skipped'      => $skipped,
            'auto_matched' => $matched,
            'status'       => $parentLog->isSuccess() ? 'completed' : 'failed',
            'notes'        => $parentLog->error_message ?? $parentLog->summary,
        ]);
    }

    private function runAutoMatch(): int
    {
        $matched = 0;
        MdmPortalDevice::whereNull('device_id')->each(function ($mdm) use (&$matched) {
            $device = null;
            if ($mdm->imei) {
                $device = \App\Models\Device::where('imei1', $mdm->imei)
                    ->orWhere('imei2', $mdm->imei)->first();
            }
            if (! $device && $mdm->serial_number) {
                $device = \App\Models\Device::where('serial_number', $mdm->serial_number)->first();
            }
            if ($device) {
                $update = ['device_id' => $device->id];
                if (! $mdm->employee_id && $device->current_employee_id) {
                    $update['employee_id'] = $device->current_employee_id;
                }
                $mdm->update($update);
                $matched++;
            }
        });
        return $matched;
    }

    private static function firstNameFrom(mixed $val): ?string
    {
        if (! $val) return null;
        if (is_string($val)) return $val;
        if (is_array($val)) {
            $first = $val[0] ?? null;
            if (is_string($first)) return $first;
            return $first['name'] ?? null;
        }
        return null;
    }

    private static function parseTs(mixed $val): ?Carbon
    {
        if (! $val) return null;
        try {
            return is_numeric($val)
                ? Carbon::createFromTimestampMs((int) $val)
                : Carbon::parse($val);
        } catch (\Throwable) {
            return null;
        }
    }
}

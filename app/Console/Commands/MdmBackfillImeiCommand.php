<?php

namespace App\Console\Commands;

use App\Models\MdmDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-off narrow backfill: reads imei/infojson from the PG source (read-only)
 * and UPDATEs only existing mdm_devices rows' imei/serial_number/model/
 * android_version columns. No inserts, no deletes, no location tables touched
 * — safe to run even while another process is syncing the same database.
 */
class MdmBackfillImeiCommand extends Command
{
    protected $signature   = 'mdm:backfill-imei';
    protected $description = 'Backfill mdm_devices imei/serial_number/model/android_version from PG source infojson';

    public function handle(): int
    {
        $scanned = 0;
        $updated = 0;

        DB::connection('mdm_pgsql')->table('devices')
            ->select('id', 'imei', 'infojson')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$scanned, &$updated) {
                $ids = $rows->pluck('id')->all();

                $needsBackfill = MdmDevice::whereIn('id', $ids)
                    ->where(function ($q) {
                        $q->whereNull('imei')->orWhere('imei', '')
                          ->orWhereNull('serial_number')->orWhere('serial_number', '')
                          ->orWhereNull('model')->orWhere('model', '')
                          ->orWhereNull('android_version')->orWhere('android_version', '');
                    })
                    ->pluck('id')->flip();

                foreach ($rows as $r) {
                    $scanned++;
                    if (! isset($needsBackfill[$r->id])) continue;

                    $info = null;
                    if (! empty($r->infojson)) {
                        $decoded = json_decode($r->infojson, true);
                        if (is_array($decoded)) $info = $decoded;
                    }
                    if (! $info) continue;

                    $update = [];
                    $imei = $r->imei ?: ($info['imei'] ?? null);
                    if (! empty($imei)) $update['imei'] = $imei;
                    if (! empty($info['serial'])) $update['serial_number'] = $info['serial'];
                    if (! empty($info['model'])) $update['model'] = $info['model'];
                    if (! empty($info['androidVersion'])) $update['android_version'] = (string) $info['androidVersion'];

                    if ($update) {
                        MdmDevice::where('id', $r->id)->update($update);
                        $updated++;
                    }
                }

                $this->info("scanned={$scanned} updated={$updated}");
            });

        $this->info("DONE scanned={$scanned} updated={$updated}");
        return Command::SUCCESS;
    }
}

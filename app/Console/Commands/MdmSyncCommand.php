<?php

namespace App\Console\Commands;

use App\Services\MdmPostgresService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MdmSyncCommand extends Command
{
    protected $signature   = 'mdm:sync {--user=1 : ID of the user who triggered the sync}';
    protected $description = 'Sync MDM data from PostgreSQL in the background with cache-based progress tracking';

    private array $stageTimers = [];
    private array $progress    = [];

    public function handle(): int
    {
        $userId = (int) $this->option('user');

        $this->progress = [
            'status'     => 'running',
            'started_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
            'stages'     => [
                ['key' => 'devices',          'label' => 'Devices (hmdm_device → mdm_devices + hardware/gps)', 'status' => 'pending', 'done' => 0, 'total' => null, 'rate' => 0, 'eta_sec' => null],
                ['key' => 'base_params',      'label' => 'Base Params (plugin_deviceinfo_deviceparams)',        'status' => 'pending', 'done' => 0, 'total' => null, 'rate' => 0, 'eta_sec' => null],
                ['key' => 'location_latest',  'label' => 'Location Latest (plugin_devicelocations_latest)',     'status' => 'pending', 'done' => 0, 'total' => null, 'rate' => 0, 'eta_sec' => null],
                ['key' => 'location_history', 'label' => 'Location History (incremental sync)',                  'status' => 'pending', 'done' => 0, 'total' => null, 'rate' => 0, 'eta_sec' => null],
                ['key' => 'auto_match',       'label' => 'Auto-Match Devices to Asset Inventory',               'status' => 'pending', 'done' => 0, 'total' => null, 'rate' => 0, 'eta_sec' => null],
            ],
            'result' => null,
            'error'  => null,
        ];

        Cache::put('mdm_sync_progress', $this->progress, 3600);

        try {
            $svc = new MdmPostgresService();
            $log = $svc->syncAll($userId, '127.0.0.1', $this->makeCallback());

            $this->progress['status'] = ($log->status === 'completed') ? 'completed' : 'failed';
            $this->progress['result'] = [
                'imported'     => $log->imported,
                'updated'      => $log->updated,
                'skipped'      => $log->skipped,
                'auto_matched' => $log->auto_matched,
                'total_rows'   => $log->total_rows,
            ];
            if ($log->status !== 'completed') {
                $this->progress['error'] = $log->notes;
            }
        } catch (\Throwable $e) {
            $this->progress['status'] = 'failed';
            $this->progress['error']  = mb_substr($e->getMessage(), 0, 500);
        }

        $this->progress['updated_at'] = now()->timestamp;
        Cache::put('mdm_sync_progress', $this->progress, 7200); // keep for 2 hours after done
        Cache::forget('mdm_sync_running');

        $this->info('MDM sync ' . $this->progress['status']);
        return $this->progress['status'] === 'completed' ? Command::SUCCESS : Command::FAILURE;
    }

    private function makeCallback(): callable
    {
        return function (string $stage, int $done, ?int $total, string $status) {
            foreach ($this->progress['stages'] as &$s) {
                if ($s['key'] !== $stage) continue;

                if ($status === 'running' && !isset($this->stageTimers[$stage])) {
                    $this->stageTimers[$stage] = microtime(true);
                }

                $s['status'] = match ($status) {
                    'done', 'failed', 'skipped' => $status,
                    default                     => 'running',
                };
                $s['done'] = $done;
                if ($total !== null) $s['total'] = $total;
                if ($status === 'done') $s['eta_sec'] = 0;

                if (isset($this->stageTimers[$stage]) && $done > 0) {
                    $elapsed = microtime(true) - $this->stageTimers[$stage];
                    if ($elapsed > 0.1) {
                        $s['rate'] = round($done / $elapsed, 1);
                        if ($s['total'] && $s['rate'] > 0 && $done < $s['total']) {
                            $s['eta_sec'] = (int) ceil(($s['total'] - $done) / $s['rate']);
                        }
                    }
                }
                break;
            }
            unset($s);

            $this->progress['updated_at'] = now()->timestamp;
            Cache::put('mdm_sync_progress', $this->progress, 3600);
        };
    }
}

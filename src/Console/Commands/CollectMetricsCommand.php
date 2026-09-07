<?php

namespace Artur\PelicanAdvancedMetrics\Console\Commands;

use App\Models\Server;
use App\Repositories\Daemon\DaemonServerRepository;
use Artur\PelicanAdvancedMetrics\Models\ServerMetricHistory;
use Artur\PelicanAdvancedMetrics\Services\SourceQueryService;
use Exception;
use Illuminate\Console\Command;

class CollectMetricsCommand extends Command
{
    protected $signature = 'pelican:metrics:collect';

    protected $description = 'Collects snapshot of server metrics into database history';

    public function handle(DaemonServerRepository $daemonRepo): void
    {
        $servers = Server::with('node', 'egg', 'allocation')->get();

        $count = 0;
        foreach ($servers as $server) {
            try {
                // If server is suspended, skip
                if ($server->isSuspended()) {
                    continue;
                }

                $details = $daemonRepo->setServer($server)->getDetails();
                $state = $details['state'] ?? 'offline';

                if ($state !== 'running' && $state !== 'starting') {
                    continue;
                }

                $utilization = $details['utilization'] ?? [];

                $cpu = (float) ($utilization['cpu_absolute'] ?? 0);
                $mem = (int) ($utilization['memory_bytes'] ?? 0);
                $disk = (int) ($utilization['disk_bytes'] ?? 0);
                $netRx = (int) ($utilization['network']['rx_bytes'] ?? 0);
                $netTx = (int) ($utilization['network']['tx_bytes'] ?? 0);

                // Fetch real player count via SourceQuery / GameQuery
                $players = 0;
                try {
                    $queryData = SourceQueryService::queryCached($server);
                    $players = (int) ($queryData['current_players'] ?? 0);
                } catch (\Throwable $e) {
                    $players = (int) cache()->get("servers.{$server->id}.players_count", 0);
                }

                ServerMetricHistory::create([
                    'server_id' => $server->id,
                    'cpu' => $cpu,
                    'memory' => $mem,
                    'disk' => $disk,
                    'network_rx' => $netRx,
                    'network_tx' => $netTx,
                    'players' => $players,
                    'created_at' => now(),
                ]);

                $count++;
            } catch (\Throwable $e) {
                // Ignore single server collection failure (e.g. node unreachable)
            }
        }

        $this->info("Collected metrics for {$count} running servers.");
    }
}

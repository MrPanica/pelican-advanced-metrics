<?php

namespace Artur\PelicanAdvancedMetrics\Http\Controllers;

use App\Models\Server;
use Artur\PelicanAdvancedMetrics\Models\ServerMetricHistory;
use Artur\PelicanAdvancedMetrics\Services\SourceQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class MetricsDataController extends Controller
{
    public function getHistory(Request $request, string|int $serverIdentifier): JsonResponse
    {
        $server = Server::where('id', $serverIdentifier)
            ->orWhere('uuid', $serverIdentifier)
            ->orWhere('uuid', 'like', $serverIdentifier . '%')
            ->firstOrFail();

        $range = $request->get('range', '1h'); // 10s, 1m, 1h, 1d, 1w, 1mo
        $now = now();
        $query = ServerMetricHistory::where('server_id', $server->id);

        switch ($range) {
            case '10s':
                // Check if realtime cached stats exist for this server
                $cachedCpu = (array) cache()->get("servers.{$server->id}.cpu_absolute", []);
                $cachedMem = (array) cache()->get("servers.{$server->id}.memory_bytes", []);
                $cachedDisk = (array) cache()->get("servers.{$server->id}.disk_bytes", []);
                $cachedPlayers = cache()->get("servers.{$server->id}.players_count");
                if ($cachedPlayers === null) {
                    try {
                        $q = SourceQueryService::queryCached($server);
                        $cachedPlayers = (int) ($q['current_players'] ?? 0);
                    } catch (\Throwable $e) {
                        $cachedPlayers = 0;
                    }
                } else {
                    $cachedPlayers = (int) $cachedPlayers;
                }

                if (count($cachedCpu) >= 2) {
                    $points = [];
                    $nowTs = now()->timestamp;
                    $idx = 0;
                    $total = count($cachedCpu);

                    foreach ($cachedCpu as $k => $cpuVal) {
                        $m = $cachedMem[$k] ?? (is_array($cachedMem) && !empty($cachedMem) ? end($cachedMem) : 0);
                        $d = $cachedDisk[$k] ?? (is_array($cachedDisk) && !empty($cachedDisk) ? end($cachedDisk) : 0);
                        $dt = is_numeric($k) && $k > 1000000000
                            ? date('Y-m-d H:i:s', (int) $k)
                            : date('Y-m-d H:i:s', $nowTs - ($total - $idx));
                        $points[] = [
                            'cpu' => (float) $cpuVal,
                            'memory' => (int) $m,
                            'disk' => (int) $d,
                            'network_rx' => 0,
                            'network_tx' => 0,
                            'players' => $cachedPlayers,
                            'tickrate' => 0,
                            'created_at' => $dt,
                        ];
                        $idx++;
                    }
                    $data = collect(array_slice($points, -12));
                } else {
                    $data = $query->orderBy('created_at', 'desc')
                        ->take(12)
                        ->get()
                        ->reverse()
                        ->values();
                }
                break;

            case '1m':
                $data = $query->where('created_at', '>=', $now->copy()->subMinutes(5))
                    ->orderBy('created_at', 'asc')
                    ->get();
                if ($data->isEmpty()) {
                    $data = $query->orderBy('created_at', 'desc')->take(10)->get()->reverse()->values();
                }
                break;

            case '1d':
                $data = $query->where('created_at', '>=', $now->copy()->subDay())
                    ->select(
                        DB::raw('ROUND(AVG(cpu), 2) as cpu'),
                        DB::raw('ROUND(AVG(memory)) as memory'),
                        DB::raw('ROUND(AVG(disk)) as disk'),
                        DB::raw('ROUND(AVG(network_rx)) as network_rx'),
                        DB::raw('ROUND(AVG(network_tx)) as network_tx'),
                        DB::raw('MAX(players) as players'),
                        DB::raw('ROUND(AVG(tickrate), 1) as tickrate'),
                        DB::raw('FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(created_at) / 300) * 300) as bucket_time')
                    )
                    ->groupBy('bucket_time')
                    ->orderBy('bucket_time', 'asc')
                    ->get()
                    ->map(fn($r) => [
                        'cpu' => $r->cpu,
                        'memory' => $r->memory,
                        'disk' => $r->disk,
                        'network_rx' => $r->network_rx,
                        'network_tx' => $r->network_tx,
                        'players' => $r->players,
                        'tickrate' => $r->tickrate,
                        'created_at' => $r->bucket_time,
                    ]);
                break;

            case '1w':
                $data = $query->where('created_at', '>=', $now->copy()->subDays(7))
                    ->select(
                        DB::raw('ROUND(AVG(cpu), 2) as cpu'),
                        DB::raw('ROUND(AVG(memory)) as memory'),
                        DB::raw('ROUND(AVG(disk)) as disk'),
                        DB::raw('ROUND(AVG(network_rx)) as network_rx'),
                        DB::raw('ROUND(AVG(network_tx)) as network_tx'),
                        DB::raw('MAX(players) as players'),
                        DB::raw('ROUND(AVG(tickrate), 1) as tickrate'),
                        DB::raw('FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(created_at) / 1800) * 1800) as bucket_time')
                    )
                    ->groupBy('bucket_time')
                    ->orderBy('bucket_time', 'asc')
                    ->get()
                    ->map(fn($r) => [
                        'cpu' => $r->cpu,
                        'memory' => $r->memory,
                        'disk' => $r->disk,
                        'network_rx' => $r->network_rx,
                        'network_tx' => $r->network_tx,
                        'players' => $r->players,
                        'tickrate' => $r->tickrate,
                        'created_at' => $r->bucket_time,
                    ]);
                break;

            case '1mo':
                $data = $query->where('created_at', '>=', $now->copy()->subDays(30))
                    ->select(
                        DB::raw('ROUND(AVG(cpu), 2) as cpu'),
                        DB::raw('ROUND(AVG(memory)) as memory'),
                        DB::raw('ROUND(AVG(disk)) as disk'),
                        DB::raw('ROUND(AVG(network_rx)) as network_rx'),
                        DB::raw('ROUND(AVG(network_tx)) as network_tx'),
                        DB::raw('MAX(players) as players'),
                        DB::raw('ROUND(AVG(tickrate), 1) as tickrate'),
                        DB::raw('FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(created_at) / 7200) * 7200) as bucket_time')
                    )
                    ->groupBy('bucket_time')
                    ->orderBy('bucket_time', 'asc')
                    ->get()
                    ->map(fn($r) => [
                        'cpu' => $r->cpu,
                        'memory' => $r->memory,
                        'disk' => $r->disk,
                        'network_rx' => $r->network_rx,
                        'network_tx' => $r->network_tx,
                        'players' => $r->players,
                        'tickrate' => $r->tickrate,
                        'created_at' => $r->bucket_time,
                    ]);
                break;

            case '1h':
            default:
                $data = $query->where('created_at', '>=', $now->copy()->subHour())
                    ->orderBy('created_at', 'asc')
                    ->get();
                if ($data->isEmpty()) {
                    $data = $query->orderBy('created_at', 'desc')->take(20)->get()->reverse()->values();
                }
                break;
        }

        return response()->json([
            'range' => $range,
            'server_id' => $server->id,
            'count' => count($data),
            'data' => $data,
        ]);
    }
}

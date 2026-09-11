<?php

namespace Artur\PelicanAdvancedMetrics\Filament\Server\Widgets;

use App\Models\Server;
use Artur\PelicanAdvancedMetrics\Models\ServerMetricHistory;
use Artur\PelicanAdvancedMetrics\Services\SourceQueryService;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ServerPlayersChart extends ChartWidget
{
    protected ?string $pollingInterval = '15s';

    protected ?string $maxHeight = '200px';

    public ?Server $server = null;

    public static function canView(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return $server && !$server->isInConflictState();
    }

    private function getLivePlayerCount(Server $server): int
    {
        $cached = cache()->get("servers.{$server->id}.players_count");
        if ($cached !== null) {
            return (int) $cached;
        }

        try {
            $queryData = SourceQueryService::queryCached($server);
            return (int) ($queryData['current_players'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function getData(): array
    {
        /** @var Server $server */
        $server = $this->server ?? Filament::getTenant();

        $history = ServerMetricHistory::where('server_id', $server->id)
            ->latest('created_at')
            ->take(30)
            ->get()
            ->reverse();

        $data = [];
        $labels = [];

        $user = auth()->user() ?? (function_exists('user') ? user() : null);
        $tz = $user?->timezone ?? 'Europe/Moscow';

        foreach ($history as $record) {
            $data[] = (int) $record->players;
            $labels[] = Carbon::parse($record->created_at)->timezone($tz)->format('H:i');
        }

        $liveCount = $this->getLivePlayerCount($server);

        if (empty($data)) {
            $data = [$liveCount];
            $labels = [now($tz)->format('H:i')];
        } else {
            $data[count($data) - 1] = $liveCount;
        }

        return [
            'datasets' => [
                [
                    'label' => __('advanced-metrics::messages.players_online'),
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.25)',
                    'tension' => 0.3,
                    'fill' => true,
                    'pointRadius' => 0,
                    'radius' => 0,
                    'pointHoverRadius' => 4,
                    'pointHitRadius' => 10,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            elements: {
                point: {
                    radius: 0,
                    hoverRadius: 4,
                    hitRadius: 10
                }
            },
            scales: {
                y: {
                    min: 0,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                },
                x: {
                    display: false,
                }
            },
            plugins: {
                legend: {
                    display: false,
                }
            }
        }
        JS);
    }

    public function getHeading(): string
    {
        /** @var Server $server */
        $server = $this->server ?? Filament::getTenant();
        $players = $this->getLivePlayerCount($server);

        return __('advanced-metrics::messages.players_online') . " - $players";
    }
}

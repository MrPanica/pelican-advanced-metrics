<?php

namespace Artur\PelicanAdvancedMetrics\Filament\Server\Widgets;

use App\Models\Server;
use Artur\PelicanAdvancedMetrics\Models\ServerMetricHistory;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ServerDiskChart extends ChartWidget
{
    protected ?string $pollingInterval = '5s';

    protected ?string $maxHeight = '200px';

    public ?Server $server = null;

    public static function canView(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return $server && !$server->isInConflictState();
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
            $mb = round($record->disk / 1024 / 1024, 2);
            $data[] = $mb;
            $labels[] = Carbon::parse($record->created_at)->timezone($tz)->format('H:i');
        }

        if (empty($data)) {
            $curDisk = (int) collect(cache()->get("servers.{$server->id}.disk_bytes"))->last(default: 0);
            $data = [round($curDisk / 1024 / 1024, 2)];
            $labels = [now($tz)->format('H:i')];
        }

        return [
            'datasets' => [
                [
                    'label' => __('advanced-metrics::messages.chart_disk_mb'),
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.25)',
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
                        callback(value) {
                            return value + ' MB';
                        }
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
        $disk = (int) collect(cache()->get("servers.{$server->id}.disk_bytes"))->last(default: 0);
        $used = $disk > 0 ? convert_bytes_to_readable($disk) : '0 MB';

        return __('advanced-metrics::messages.disk') . " - $used";
    }
}

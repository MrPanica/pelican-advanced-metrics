<?php

namespace Artur\PelicanAdvancedMetrics\Filament\Server\Widgets;

use App\Models\Server;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AdvancedMetricsWidget extends Widget
{
    protected string $view = 'advanced-metrics::filament.widgets.advanced-metrics-widget';

    protected int|string|array $columnSpan = 'full';

    public ?Server $server = null;

    public static function canView(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return $server && !$server->isInConflictState();
    }

    public function mount(): void
    {
        $this->server = Filament::getTenant();
    }
}

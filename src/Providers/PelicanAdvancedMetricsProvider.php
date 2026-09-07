<?php

namespace Artur\PelicanAdvancedMetrics\Providers;

use App\Enums\ConsoleWidgetPosition;
use App\Filament\Server\Pages\Console;
use Artur\PelicanAdvancedMetrics\Console\Commands\CollectMetricsCommand;
use Artur\PelicanAdvancedMetrics\Console\Commands\PruneMetricsCommand;
use Artur\PelicanAdvancedMetrics\Filament\Server\Widgets\ServerDiskChart;
use Artur\PelicanAdvancedMetrics\Filament\Server\Widgets\ServerPlayersChart;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PelicanAdvancedMetricsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/advanced-metrics.php', 'advanced-metrics');

        // Register new charts at Bottom of console page (Disk, Players)
        Console::registerCustomWidgets(ConsoleWidgetPosition::Bottom, [
            ServerDiskChart::class,
            ServerPlayersChart::class,
        ]);

        $this->ensureAssetsPublished();
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'advanced-metrics');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CollectMetricsCommand::class,
                PruneMetricsCommand::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('pelican:metrics:collect')->everyMinute()->withoutOverlapping();
                $schedule->command('pelican:metrics:prune')->daily();
            });
        }

        // Register API route for metrics history accepting id, uuid, or short uuid
        Route::middleware(['web', 'auth'])
            ->prefix('api/client/servers/{serverIdentifier}')
            ->group(function () {
                Route::get('metrics-history', [\Artur\PelicanAdvancedMetrics\Http\Controllers\MetricsDataController::class, 'getHistory']);
            });

        $this->publishes([
            __DIR__ . '/../../resources/css/advanced-metrics.css' => public_path('plugins/pelican-advanced-metrics/css/advanced-metrics.css'),
            __DIR__ . '/../../resources/js/advanced-metrics.js' => public_path('plugins/pelican-advanced-metrics/js/advanced-metrics.js'),
            __DIR__ . '/../../resources/js/chart.umd.min.js' => public_path('plugins/pelican-advanced-metrics/js/chart.umd.min.js'),
        ], 'pelican-advanced-metrics-assets');
    }

    private function ensureAssetsPublished(): void
    {
        $pairs = [
            [__DIR__ . '/../../resources/css/advanced-metrics.css', public_path('plugins/pelican-advanced-metrics/css/advanced-metrics.css')],
            [__DIR__ . '/../../resources/js/advanced-metrics.js', public_path('plugins/pelican-advanced-metrics/js/advanced-metrics.js')],
            [__DIR__ . '/../../resources/js/chart.umd.min.js', public_path('plugins/pelican-advanced-metrics/js/chart.umd.min.js')],
        ];

        foreach ($pairs as [$source, $destination]) {
            if (File::exists($source) && (!File::exists($destination) || File::lastModified($source) > File::lastModified($destination))) {
                $dir = dirname($destination);
                if (!File::isDirectory($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
                File::copy($source, $destination);
            }
        }
    }
}

<?php

namespace Artur\PelicanAdvancedMetrics;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class PelicanAdvancedMetricsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'pelican-advanced-metrics';
    }

    public function register(Panel $panel): void
    {
        if ($panel->getId() === 'server') {
            $version = '1.0.10';

            $panel->renderHook(
                PanelsRenderHook::HEAD_END,
                function () use ($version) {
                    $i18n = trans('advanced-metrics::messages');
                    return new HtmlString(
                        '<link rel="stylesheet" href="/plugins/pelican-advanced-metrics/css/advanced-metrics.css?v=' . $version . '">' . "\n" .
                        '<script>window.PelicanAdvancedMetricsI18n = ' . json_encode($i18n) . ';</script>'
                    );
                }
            );

            $panel->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => new HtmlString(
                    '<script src="/plugins/pelican-advanced-metrics/js/chart.umd.min.js?v=' . $version . '"></script>' . "\n" .
                    '<script src="/plugins/pelican-advanced-metrics/js/advanced-metrics.js?v=' . $version . '" defer></script>'
                )
            );
        }
    }

    public function boot(Panel $panel): void
    {
    }
}

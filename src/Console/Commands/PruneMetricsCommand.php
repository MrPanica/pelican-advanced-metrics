<?php

namespace Artur\PelicanAdvancedMetrics\Console\Commands;

use Artur\PelicanAdvancedMetrics\Models\ServerMetricHistory;
use Illuminate\Console\Command;

class PruneMetricsCommand extends Command
{
    protected $signature = 'pelican:metrics:prune';

    protected $description = 'Prunes metric history older than configured retention period';

    public function handle(): void
    {
        $days = (int) config('advanced-metrics.retention_days', 14);
        if ($days <= 0) {
            $days = 14;
        }

        $threshold = now()->subDays($days);
        $deleted = ServerMetricHistory::where('created_at', '<', $threshold)->delete();

        $this->info("Pruned {$deleted} metric snapshots older than {$days} days.");
    }
}

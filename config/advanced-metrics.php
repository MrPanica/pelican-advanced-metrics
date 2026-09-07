<?php

return [
    'enabled' => env('ADVANCED_METRICS_ENABLED', true),
    'retention_days' => (int) env('ADVANCED_METRICS_RETENTION_DAYS', 14),
    'collect_interval_seconds' => (int) env('ADVANCED_METRICS_INTERVAL', 60),
];

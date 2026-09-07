<?php

namespace Artur\PelicanAdvancedMetrics\Models;

use App\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerMetricHistory extends Model
{
    public $timestamps = false;

    protected $table = 'server_metrics_history';

    protected $fillable = [
        'server_id',
        'cpu',
        'memory',
        'disk',
        'network_rx',
        'network_tx',
        'players',
        'tickrate',
        'created_at',
    ];

    protected $casts = [
        'cpu' => 'float',
        'memory' => 'integer',
        'disk' => 'integer',
        'network_rx' => 'integer',
        'network_tx' => 'integer',
        'players' => 'integer',
        'tickrate' => 'float',
        'created_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}

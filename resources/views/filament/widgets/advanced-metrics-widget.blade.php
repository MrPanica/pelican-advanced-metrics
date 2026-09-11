<x-filament::widget>
    <div id="pelican-advanced-metrics-app" data-server-id="{{ $server->id }}" class="advanced-metrics-container">
        <!-- Range Selector Toolbar -->
        <div class="metrics-header">
            <div class="metrics-title">
                <span class="metrics-pulse-dot"></span>
                <span>{{ __('advanced-metrics::messages.advanced_metrics_title') }}</span>
            </div>
            <div class="metrics-range-selector">
                <button type="button" class="range-btn" data-range="1m">{{ __('advanced-metrics::messages.range_live') }}</button>
                <button type="button" class="range-btn active" data-range="1h">{{ __('advanced-metrics::messages.range_1h') }}</button>
                <button type="button" class="range-btn" data-range="1d">{{ __('advanced-metrics::messages.range_1d') }}</button>
                <button type="button" class="range-btn" data-range="1w">{{ __('advanced-metrics::messages.range_1w') }}</button>
                <button type="button" class="range-btn" data-range="1mo">{{ __('advanced-metrics::messages.range_1mo') }}</button>
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="metrics-grid">
            <!-- CPU Card -->
            <div class="metric-card" data-metric="cpu">
                <div class="metric-card-header">
                    <span>⚡ {{ __('advanced-metrics::messages.chart_cpu_label') }}</span>
                    <button type="button" class="expand-btn" title="{{ __('advanced-metrics::messages.expand_fullscreen') }}">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-cpu">-- %</div>
                <canvas id="chart-cpu" class="metric-mini-chart"></canvas>
            </div>

            <!-- Memory Card -->
            <div class="metric-card" data-metric="memory">
                <div class="metric-card-header">
                    <span>🧠 {{ __('advanced-metrics::messages.chart_memory_label') }}</span>
                    <button type="button" class="expand-btn" title="{{ __('advanced-metrics::messages.expand_fullscreen') }}">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-memory">-- MB</div>
                <canvas id="chart-memory" class="metric-mini-chart"></canvas>
            </div>

            <!-- Network Card -->
            <div class="metric-card" data-metric="network">
                <div class="metric-card-header">
                    <span>🌐 {{ __('advanced-metrics::messages.chart_network_in_out') }}</span>
                    <button type="button" class="expand-btn" title="{{ __('advanced-metrics::messages.expand_fullscreen') }}">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-network">-- / --</div>
                <canvas id="chart-network" class="metric-mini-chart"></canvas>
            </div>

            <!-- Disk Card -->
            <div class="metric-card" data-metric="disk">
                <div class="metric-card-header">
                    <span>💾 {{ __('advanced-metrics::messages.chart_disk_label') }}</span>
                    <button type="button" class="expand-btn" title="{{ __('advanced-metrics::messages.expand_fullscreen') }}">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-disk">-- MB</div>
                <canvas id="chart-disk" class="metric-mini-chart"></canvas>
            </div>

            <!-- Players Card -->
            <div class="metric-card" data-metric="players">
                <div class="metric-card-header">
                    <span>👥 {{ __('advanced-metrics::messages.chart_players_label') }}</span>
                    <button type="button" class="expand-btn" title="{{ __('advanced-metrics::messages.expand_fullscreen') }}">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-players">-- {{ __('advanced-metrics::messages.players_count') }}</div>
                <canvas id="chart-players" class="metric-mini-chart"></canvas>
            </div>

            <!-- Tickrate Card -->
            <div class="metric-card" data-metric="tickrate">
                <div class="metric-card-header">
                    <span>⏱️ {{ __('advanced-metrics::messages.chart_tickrate_label') }}</span>
                    <button type="button" class="expand-btn" title="{{ __('advanced-metrics::messages.expand_fullscreen') }}">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-tickrate">-- tick</div>
                <canvas id="chart-tickrate" class="metric-mini-chart"></canvas>
            </div>
        </div>

        <!-- Fullscreen / Zoom Modal -->
        <div id="metrics-fullscreen-modal" class="metrics-modal">
            <div class="metrics-modal-content">
                <div class="metrics-modal-header">
                    <h3 id="modal-chart-title">{{ __('advanced-metrics::messages.detailed_chart') }}</h3>
                    <div class="metrics-modal-stats" id="modal-stats-summary">
                        <span>{{ __('advanced-metrics::messages.min') }}: <b id="stat-min">--</b></span>
                        <span>{{ __('advanced-metrics::messages.avg') }}: <b id="stat-avg">--</b></span>
                        <span>{{ __('advanced-metrics::messages.max') }}: <b id="stat-max">--</b></span>
                        <span>{{ __('advanced-metrics::messages.current') }}: <b id="stat-cur">--</b></span>
                    </div>
                    <button type="button" id="modal-close-btn" class="modal-close-btn">&times;</button>
                </div>
                <div class="metrics-modal-body">
                    <canvas id="modal-expanded-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</x-filament::widget>

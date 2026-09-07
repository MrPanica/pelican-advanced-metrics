<x-filament::widget>
    <div id="pelican-advanced-metrics-app" data-server-id="{{ $server->id }}" class="advanced-metrics-container">
        <!-- Range Selector Toolbar -->
        <div class="metrics-header">
            <div class="metrics-title">
                <span class="metrics-pulse-dot"></span>
                <span>Расширенные метрики & История</span>
            </div>
            <div class="metrics-range-selector">
                <button type="button" class="range-btn" data-range="1m">1м (Live)</button>
                <button type="button" class="range-btn active" data-range="1h">1 час</button>
                <button type="button" class="range-btn" data-range="1d">1 день</button>
                <button type="button" class="range-btn" data-range="1w">1 нед.</button>
                <button type="button" class="range-btn" data-range="1mo">1 мес.</button>
            </div>
        </div>

        <!-- Metric Cards Grid -->
        <div class="metrics-grid">
            <!-- CPU Card -->
            <div class="metric-card" data-metric="cpu">
                <div class="metric-card-header">
                    <span>⚡ Процессор (CPU)</span>
                    <button type="button" class="expand-btn" title="Развернуть на весь экран">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-cpu">-- %</div>
                <canvas id="chart-cpu" class="metric-mini-chart"></canvas>
            </div>

            <!-- Memory Card -->
            <div class="metric-card" data-metric="memory">
                <div class="metric-card-header">
                    <span>🧠 Память (RAM)</span>
                    <button type="button" class="expand-btn" title="Развернуть на весь экран">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-memory">-- MB</div>
                <canvas id="chart-memory" class="metric-mini-chart"></canvas>
            </div>

            <!-- Network Card -->
            <div class="metric-card" data-metric="network">
                <div class="metric-card-header">
                    <span>🌐 Сеть (In / Out)</span>
                    <button type="button" class="expand-btn" title="Развернуть на весь экран">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-network">-- / --</div>
                <canvas id="chart-network" class="metric-mini-chart"></canvas>
            </div>

            <!-- Disk Card -->
            <div class="metric-card" data-metric="disk">
                <div class="metric-card-header">
                    <span>💾 Диск</span>
                    <button type="button" class="expand-btn" title="Развернуть на весь экран">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-disk">-- MB</div>
                <canvas id="chart-disk" class="metric-mini-chart"></canvas>
            </div>

            <!-- Players Card -->
            <div class="metric-card" data-metric="players">
                <div class="metric-card-header">
                    <span>👥 Игроки онлайн</span>
                    <button type="button" class="expand-btn" title="Развернуть на весь экран">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-players">-- игроков</div>
                <canvas id="chart-players" class="metric-mini-chart"></canvas>
            </div>

            <!-- Tickrate Card -->
            <div class="metric-card" data-metric="tickrate">
                <div class="metric-card-header">
                    <span>⏱️ Tickrate (TF2)</span>
                    <button type="button" class="expand-btn" title="Развернуть на весь экран">⛶</button>
                </div>
                <div class="metric-value" id="metric-val-tickrate">-- tick</div>
                <canvas id="chart-tickrate" class="metric-mini-chart"></canvas>
            </div>
        </div>

        <!-- Fullscreen / Zoom Modal -->
        <div id="metrics-fullscreen-modal" class="metrics-modal">
            <div class="metrics-modal-content">
                <div class="metrics-modal-header">
                    <h3 id="modal-chart-title">Детальный график</h3>
                    <div class="metrics-modal-stats" id="modal-stats-summary">
                        <span>Мин: <b id="stat-min">--</b></span>
                        <span>Среднее: <b id="stat-avg">--</b></span>
                        <span>Макс: <b id="stat-max">--</b></span>
                        <span>Текущее: <b id="stat-cur">--</b></span>
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

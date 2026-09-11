/**
 * Pelican Advanced Metrics - Chart Expand Buttons & Overlay Modal
 * Version 1.0.7
 */
(function () {
    const i18n = window.PelicanAdvancedMetricsI18n || {};
    function __(key, fallback) {
        return (i18n && typeof i18n[key] === 'string') ? i18n[key] : fallback;
    }

    let currentRange = '1h';
    let selectedMetrics = new Set(['cpu']); // multi-select set
    let modalChartInstance = null;
    let cachedHistoryData = [];
    let liveRefreshInterval = null;

    const METRIC_CONFIG = {
        cpu: {
            id: 'cpu',
            label: __('chart_cpu_label', 'Процессор (CPU)'),
            color: '#38bdf8',
            unit: '%',
            yAxisID: 'y_cpu',
            getValue: (d) => parseFloat(d.cpu || 0),
            formatVal: (v) => v.toFixed(1) + ' %'
        },
        players: {
            id: 'players',
            label: __('chart_players_label', 'Игроки онлайн'),
            color: '#f59e0b',
            unit: __('unit_players', 'игр.'),
            yAxisID: 'y_players',
            getValue: (d) => parseInt(d.players || 0),
            formatVal: (v) => Math.round(v) + ' ' + __('unit_players_full', 'чел')
        },
        memory: {
            id: 'memory',
            label: __('chart_memory_label', 'Память (RAM)'),
            color: '#a855f7',
            unit: 'MB',
            yAxisID: 'y_memory',
            getValue: (d) => parseFloat((d.memory / 1024 / 1024).toFixed(1)),
            formatVal: (v) => v >= 1024 ? (v / 1024).toFixed(2) + ' GB' : v.toFixed(1) + ' MB'
        },
        network: {
            id: 'network',
            label: __('chart_network_label', 'Сеть (Трафик)'),
            color: '#06b6d4',
            unit: 'KB/s',
            yAxisID: 'y_network',
            getValue: (d) => parseFloat(((d.network_rx + d.network_tx) / 1024).toFixed(1)),
            formatVal: (v) => v >= 1024 ? (v / 1024).toFixed(2) + ' MB/s' : v.toFixed(1) + ' KB/s'
        },
        disk: {
            id: 'disk',
            label: __('chart_disk_label', 'Диск'),
            color: '#10b981',
            unit: 'MB',
            yAxisID: 'y_disk',
            getValue: (d) => parseFloat((d.disk / 1024 / 1024).toFixed(1)),
            formatVal: (v) => v >= 1024 ? (v / 1024).toFixed(2) + ' GB' : v.toFixed(1) + ' MB'
        }
    };

    function getServerIdentifier() {
        const parts = window.location.pathname.split('/');
        const idx = parts.indexOf('server');
        if (idx !== -1 && parts[idx + 1]) {
            return parts[idx + 1];
        }
        return 'default';
    }

    function detectMetricFromCard(card) {
        const text = (card.textContent || '').toLowerCase();
        if (text.includes('процессор') || text.includes('cpu')) return 'cpu';
        if (text.includes('памят') || text.includes('memory') || text.includes('ram')) return 'memory';
        if (text.includes('сет') || text.includes('network') || text.includes('трафик')) return 'network';
        if (text.includes('диск') || text.includes('disk')) return 'disk';
        if (text.includes('игрок') || text.includes('player')) return 'players';
        return 'cpu';
    }

    // Inject ⛶ expand button directly into .fi-section-header (vertically centered in header)
    function injectChartExpandButtons() {
        const canvases = document.querySelectorAll('canvas');
        canvases.forEach(canvas => {
            if (canvas.id === 'pm-history-chart-canvas' || canvas.closest('#terminal')) return;

            const section = canvas.closest('.fi-section') || canvas.closest('.fi-wi-widget') || canvas.parentElement;
            if (!section || section.querySelector('.pelican-chart-expand-btn')) return;

            const sectionHeader = section.querySelector('.fi-section-header');
            const detectedMetric = detectMetricFromCard(section);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pelican-chart-expand-btn';
            btn.setAttribute('data-metric', detectedMetric);
            btn.setAttribute('title', __('expand_chart_history', 'Развернуть график и историю'));
            btn.innerHTML = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <polyline points="9 21 3 21 3 15"></polyline>
                    <line x1="21" y1="3" x2="14" y2="10"></line>
                    <line x1="3" y1="21" x2="10" y2="14"></line>
                </svg>
            `;

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openMetricsModal(detectedMetric);
            });

            if (sectionHeader) {
                sectionHeader.appendChild(btn);
            } else {
                section.style.position = 'relative';
                section.appendChild(btn);
            }
        });
    }

    function createModalIfMissing() {
        let modal = document.getElementById('pelican-metrics-history-modal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'pelican-metrics-history-modal';
        modal.className = 'pm-modal-backdrop';
        modal.innerHTML = `
            <div class="pm-modal-window">
                <div class="pm-modal-header">
                    <div class="pm-modal-header-left">
                        <span class="pm-modal-icon">📈</span>
                        <h3>${__('metrics_history_title', 'История метрик и сравнительный график')}</h3>
                    </div>
                    <button type="button" class="pm-modal-close" id="pm-modal-close-btn">&times;</button>
                </div>

                <div class="pm-modal-toolbar">
                    <!-- Overlay Metrics Selector (Multi-check) -->
                    <div class="pm-metrics-selector-wrap">
                        <span class="pm-sel-label">${__('show_metrics_overlay', 'Отображать метрики (наложение):')}</span>
                        <div class="pm-metric-checkboxes">
                            <label class="pm-check-label color-cpu">
                                <input type="checkbox" data-metric="cpu" checked>
                                <span class="pm-chk-badge">CPU (%)</span>
                            </label>
                            <label class="pm-check-label color-players">
                                <input type="checkbox" data-metric="players">
                                <span class="pm-chk-badge">${__('players_online', 'Игроки онлайн')}</span>
                            </label>
                            <label class="pm-check-label color-memory">
                                <input type="checkbox" data-metric="memory">
                                <span class="pm-chk-badge">${__('chart_memory_label', 'Память (RAM)')}</span>
                            </label>
                            <label class="pm-check-label color-network">
                                <input type="checkbox" data-metric="network">
                                <span class="pm-chk-badge">${__('network', 'Сеть')}</span>
                            </label>
                            <label class="pm-check-label color-disk">
                                <input type="checkbox" data-metric="disk">
                                <span class="pm-chk-badge">${__('disk', 'Диск')}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Range Selector with 10s -->
                    <div class="pm-range-selector">
                        <button type="button" class="pm-range-btn" data-range="10s">${__('range_10s', '10 сек')}</button>
                        <button type="button" class="pm-range-btn" data-range="1m">${__('range_1m', '1 мин')}</button>
                        <button type="button" class="pm-range-btn active" data-range="1h">${__('range_1h', '1 час')}</button>
                        <button type="button" class="pm-range-btn" data-range="1d">${__('range_1d', '1 день')}</button>
                        <button type="button" class="pm-range-btn" data-range="1w">${__('range_1w', '1 неделя')}</button>
                        <button type="button" class="pm-range-btn" data-range="1mo">${__('range_1mo', '1 месяц')}</button>
                    </div>
                </div>

                <!-- Active Summary stats bar -->
                <div class="pm-stats-bar" id="pm-stats-bar"></div>

                <!-- Chart Canvas Container -->
                <div class="pm-chart-area">
                    <canvas id="pm-history-chart-canvas"></canvas>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Bind Close
        modal.querySelector('#pm-modal-close-btn').addEventListener('click', closeMetricsModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeMetricsModal();
        });

        // Bind Checkboxes
        const checkboxes = modal.querySelectorAll('.pm-metric-checkboxes input[type="checkbox"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const m = cb.getAttribute('data-metric');
                if (cb.checked) {
                    selectedMetrics.add(m);
                } else {
                    if (selectedMetrics.size > 1) {
                        selectedMetrics.delete(m);
                    } else {
                        cb.checked = true; // keep at least 1 checked
                    }
                }
                renderOverlayChart();
            });
        });

        // Bind Range Selector
        const rangeBtns = modal.querySelectorAll('.pm-range-btn');
        rangeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                rangeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentRange = btn.getAttribute('data-range');
                fetchHistoryData();
            });
        });

        return modal;
    }

    function openMetricsModal(initialMetric = 'cpu') {
        const modal = createModalIfMissing();

        if (initialMetric && METRIC_CONFIG[initialMetric]) {
            selectedMetrics.clear();
            selectedMetrics.add(initialMetric);

            modal.querySelectorAll('.pm-metric-checkboxes input[type="checkbox"]').forEach(cb => {
                cb.checked = (cb.getAttribute('data-metric') === initialMetric);
            });
        }

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        fetchHistoryData();

        // Live polling while modal is open
        clearInterval(liveRefreshInterval);
        const pollMs = (currentRange === '10s' || currentRange === '1m') ? 3000 : 5000;
        liveRefreshInterval = setInterval(() => {
            if (modal.classList.contains('show')) {
                fetchHistoryData();
            } else {
                clearInterval(liveRefreshInterval);
            }
        }, pollMs);
    }

    function closeMetricsModal() {
        const modal = document.getElementById('pelican-metrics-history-modal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
        clearInterval(liveRefreshInterval);
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMetricsModal();
        }
    });

    function fetchHistoryData() {
        const serverId = getServerIdentifier();
        const url = `/api/client/servers/${serverId}/metrics-history?range=${currentRange}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res && Array.isArray(res.data)) {
                cachedHistoryData = res.data;
                renderOverlayChart();
            }
        })
        .catch(err => {
            console.error('Error fetching metrics history:', err);
        });
    }

    function renderOverlayChart() {
        const canvas = document.getElementById('pm-history-chart-canvas');
        if (!canvas) return;

        const Chart = window.Chart || (window.Filament && window.Filament.Chart);

        const labels = cachedHistoryData.map(d => {
            const dt = new Date(d.created_at);
            if (currentRange === '1d' || currentRange === '1w' || currentRange === '1mo') {
                return dt.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' ' + dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            return dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        });

        const statsBar = document.getElementById('pm-stats-bar');
        if (statsBar) statsBar.innerHTML = '';

        const datasets = [];

        selectedMetrics.forEach(metricKey => {
            const conf = METRIC_CONFIG[metricKey];
            if (!conf) return;

            const values = cachedHistoryData.map(d => conf.getValue(d));

            datasets.push({
                label: conf.label,
                data: values,
                borderColor: conf.color,
                backgroundColor: conf.color + '20',
                borderWidth: 2,
                fill: selectedMetrics.size === 1,
                tension: 0.3,
                pointRadius: values.length > 40 ? 0 : 3,
                pointHoverRadius: 6,
                yAxisID: conf.yAxisID
            });

            if (statsBar && values.length > 0) {
                const min = Math.min(...values);
                const max = Math.max(...values);
                const cur = values[values.length - 1];

                const statBox = document.createElement('div');
                statBox.className = 'pm-stat-box';
                statBox.style.borderLeft = `3px solid ${conf.color}`;
                statBox.innerHTML = `
                    <span class="label" style="color:${conf.color}">${conf.label}</span>
                    <span class="value">${conf.formatVal(cur)} <small style="color:#64748b">(${__('min', 'мин')}: ${conf.formatVal(min)}, ${__('max', 'макс')}: ${conf.formatVal(max)})</small></span>
                `;
                statsBar.appendChild(statBox);
            }
        });

        if (!Chart) {
            console.warn('Chart.js not loaded yet');
            return;
        }

        if (modalChartInstance) {
            modalChartInstance.destroy();
        }

        // Build independent multi-axis scales for each active metric
        const scales = {
            x: {
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: { color: '#94a3b8', maxTicksLimit: 14 }
            }
        };

        let primaryLeftSet = false;

        if (selectedMetrics.has('cpu')) {
            scales.y_cpu = {
                type: 'linear',
                position: 'left',
                min: 0,
                suggestedMax: 100,
                grid: { color: 'rgba(56, 189, 248, 0.06)' },
                ticks: {
                    color: '#38bdf8',
                    callback: (v) => v + ' %'
                },
                title: { display: true, text: 'CPU (%)', color: '#38bdf8', font: { size: 10, weight: 'bold' } }
            };
            primaryLeftSet = true;
        }

        if (selectedMetrics.has('players')) {
            scales.y_players = {
                type: 'linear',
                position: !primaryLeftSet ? 'left' : 'right',
                min: 0,
                suggestedMax: 20,
                grid: { drawOnChartArea: !primaryLeftSet, color: 'rgba(245, 158, 11, 0.06)' },
                ticks: {
                    color: '#f59e0b',
                    stepSize: 1,
                    precision: 0,
                    callback: (v) => Number.isInteger(v) ? v + ' ' + __('unit_players', 'игр.') : ''
                },
                title: { display: true, text: __('players', 'Игроки'), color: '#f59e0b', font: { size: 10, weight: 'bold' } }
            };
            if (!primaryLeftSet) primaryLeftSet = true;
        }

        if (selectedMetrics.has('memory')) {
            scales.y_memory = {
                type: 'linear',
                position: !primaryLeftSet ? 'left' : 'right',
                min: 0,
                grid: { drawOnChartArea: !primaryLeftSet, color: 'rgba(168, 85, 247, 0.06)' },
                ticks: {
                    color: '#a855f7',
                    callback: (v) => v >= 1024 ? (v / 1024).toFixed(1) + ' GB' : v + ' MB'
                },
                title: { display: true, text: __('memory', 'Память'), color: '#a855f7', font: { size: 10, weight: 'bold' } }
            };
            if (!primaryLeftSet) primaryLeftSet = true;
        }

        if (selectedMetrics.has('disk')) {
            scales.y_disk = {
                type: 'linear',
                position: !primaryLeftSet ? 'left' : 'right',
                min: 0,
                grid: { drawOnChartArea: !primaryLeftSet, color: 'rgba(168, 85, 247, 0.06)' },
                ticks: {
                    color: '#10b981',
                    callback: (v) => v >= 1024 ? (v / 1024).toFixed(1) + ' GB' : v + ' MB'
                },
                title: { display: true, text: __('disk', 'Диск'), color: '#10b981', font: { size: 10, weight: 'bold' } }
            };
            if (!primaryLeftSet) primaryLeftSet = true;
        }

        if (selectedMetrics.has('network')) {
            scales.y_network = {
                type: 'linear',
                position: !primaryLeftSet ? 'left' : 'right',
                min: 0,
                grid: { drawOnChartArea: !primaryLeftSet, color: 'rgba(6, 182, 212, 0.06)' },
                ticks: {
                    color: '#06b6d4',
                    callback: (v) => v >= 1024 ? (v / 1024).toFixed(1) + ' MB/s' : v + ' KB/s'
                },
                title: { display: true, text: __('network', 'Сеть'), color: '#06b6d4', font: { size: 10, weight: 'bold' } }
            };
            if (!primaryLeftSet) primaryLeftSet = true;
        }

        modalChartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: scales,
                plugins: {
                    legend: {
                        display: datasets.length > 1,
                        labels: { color: '#cbd5e1' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const ds = ctx.dataset;
                                const val = ctx.parsed.y;
                                if (ds.yAxisID === 'y_players' || ds.label.includes('Игрок') || ds.label.includes('Player')) {
                                    return ds.label + ': ' + Math.round(val) + ' ' + __('unit_players_full', 'чел');
                                } else if (ds.label.includes('CPU')) {
                                    return ds.label + ': ' + val.toFixed(1) + ' %';
                                } else if (ds.label.includes('Диск') || ds.label.includes('Disk') || ds.label.includes('Память') || ds.label.includes('Memory')) {
                                    return ds.label + ': ' + (val >= 1024 ? (val / 1024).toFixed(2) + ' GB' : val.toFixed(1) + ' MB');
                                } else if (ds.label.includes('Сеть') || ds.label.includes('Network')) {
                                    return ds.label + ': ' + (val >= 1024 ? (val / 1024).toFixed(2) + ' MB/s' : val.toFixed(1) + ' KB/s');
                                }
                                return ds.label + ': ' + val;
                            }
                        }
                    }
                }
            }
        });
    }

    function init() {
        injectChartExpandButtons();

        // Observe DOM for newly rendered/lazy-loaded Filament chart widgets
        try {
            const observer = new MutationObserver(() => {
                injectChartExpandButtons();
            });
            observer.observe(document.body, { childList: true, subtree: true });
        } catch (e) {
            // fallback to interval
        }

        if (window.Livewire) {
            try { window.Livewire.hook('morph.updated', injectChartExpandButtons); } catch (e) {}
        } else {
            document.addEventListener('livewire:init', () => {
                try { window.Livewire.hook('morph.updated', injectChartExpandButtons); } catch (e) {}
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    setInterval(injectChartExpandButtons, 2000);
})();

# 📊 Pelican Advanced Server Metrics

**English** | [Русский](#-русский)

A comprehensive analytics and historical telemetry plugin for **Pelican Panel**, primarily designed and optimized for **Source Engine** dedicated game servers (*Team Fortress 2, Counter-Strike: Source, CS:GO, Garry's Mod, Half-Life 2: Deathmatch, Left 4 Dead 2*, etc.).

---

## ✨ Features (English)

- **Comprehensive Historical Metric Tracking**:
  - **CPU Usage (%)**: Real-time and historical CPU load per server.
  - **Memory (RAM)**: Accurate resident memory utilization trends.
  - **Disk Space (MB / GB)**: Monitored storage growth.
  - **Network Throughput**: Inbound and Outbound traffic rates.
  - **Players Online**: Historical player counts via A2S Source queries.
  - **Server Tickrate**: Dedicated server performance and tick stability (Source/TF2/CS).
- **Flexible Timeframes**:
  - Switch between `1 Minute (Live)`, `1 Hour`, `1 Day`, `1 Week`, and `1 Month` views.
- **Click-to-Expand Modals**:
  - Click on any chart to open a full-featured modal with statistical breakdown: Minimum, Average, Maximum, and Current values.
- **Automated Background Collector & Pruner**:
  - High-efficiency cron task (`pelican:metrics:collect`) collects metric snapshots every minute.
  - Automatic retention cleanup (`pelican:metrics:prune`) purges records older than configured retention (default: 14 days) to keep database size optimal.

---

## 🇷🇺 Описание (Русский)

**Pelican Advanced Server Metrics** — полноценная система аналитики и исторических графиков для панели **Pelican Panel**, в первую очередь ориентированная и оптимизированная для игровых серверов на движке **Source Engine** (*Team Fortress 2, Counter-Strike: Source, CS:GO, Garry's Mod, HL2:DM, L4D2* и др.).

### Основные возможности

- **Исторический мониторинг ключевых метрик**:
  - **Процессор (CPU %)**: динамика нагрузки на процессор.
  - **Оперативная память (RAM)**: мониторинг потребления ОЗУ.
  - **Дисковое пространство (MB / GB)**: отслеживание занятого места.
  - **Сетевой трафик (Inbound / Outbound)**: входящая и исходящая пропускная способность.
  - **Игроки онлайн**: история онлайна на основе A2S-опроса серверов Source.
  - **Тикрейт (Tickrate)**: стабильность тикрейта выделенного игрового сервера.
- **Временные диапазоны**:
  - Переключение периодов: `1 минута (Live)`, `1 час`, `1 день`, `1 неделя`, `1 месяц`.
- **Полноэкранные модальные окна**:
  - Клик по любому графику открывает детальное окно с расчётом Min / Avg / Max / Current значений.
- **Автоматический сбор и очистка**:
  - Фоновый сборщик (`pelican:metrics:collect`) делает снапшоты каждую минуту.
  - Автоматическая очистка (`pelican:metrics:prune`) удаляет записи старше заданного лимита (по умолчанию 14 дней), предотвращая разрастание базы данных.

---

## 🚀 Installation / Установка

```bash
# Clone into Pelican plugins directory
cd /var/www/pelican/plugins
git clone https://github.com/MrPanica/pelican-advanced-metrics.git

# Install and clear cache
cd /var/www/pelican
php artisan p:plugin:install pelican-advanced-metrics
php artisan optimize:clear
```

## 📄 License

MIT License. Developed for gaming communities running Source engine servers on Pelican Panel.

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

## 🚀 Installation & Updates / Установка и обновление

### ⚡ 1-Click Install via URL (Recommended) / Установка по ссылке (Рекомендуется)
#### English:
1. In Pelican Admin Panel navigate to **Plugins** (`/admin/plugins`).
2. Click the **«Import from URL»** or **«Import»** button.
3. Enter the direct `.zip` archive URL:
   ```text
   https://github.com/MrPanica/pelican-advanced-metrics/archive/refs/heads/master.zip
   ```
4. Click **Install**. Pelican Panel will automatically download, extract, run database migrations, and activate the plugin!

#### На русском:
1. В панели управления Pelican перейдите в **Админка ➔ Плагины** (`/admin/plugins`).
2. Нажмите кнопку **«Импорт»** / **«Импорт по URL»** (иконка глобуса).
3. Вставьте прямую ссылку на архив `.zip`:
   ```text
   https://github.com/MrPanica/pelican-advanced-metrics/archive/refs/heads/master.zip
   ```
4. Нажмите **Установить (Install)**. Панель Pelican автоматически скачает, распакует, выполнит миграции таблиц метрик и активирует плагин!

---

### 🔄 Automatic Updates / Автоматические обновления
- **Native Pelican Update Engine / Встроенный механизм обновлений**:
  - The plugin natively supports Pelican's update engine via `update.json`. When a new release is pushed to GitHub, Pelican displays an update notification badge and an **«Update»** button in **Admin ➔ Plugins**.
  - Плагин нативно интегрирован с системой обновлений Pelican через `update.json`. При публикации новой версии на GitHub в панели управления в разделе «Плагины» появится уведомление и кнопка **«Обновить»** для обновления в 1 клик.
- **CLI Update Command / Обновление через консоль**:
  ```bash
  cd /var/www/pelican
  php artisan p:plugin:update pelican-advanced-metrics
  ```

---

### 🌐 Multi-Language Support / Локализация
- **Bilingual Interface (EN / RU)**: Full support for **English (`en`)** and **Russian (`ru`)**.
- **Automatic Panel Locale Sync**: Interface language automatically mirrors Pelican Panel's current system locale (`app()->getLocale()`). No manual switching or browser extensions needed.
- **Автоматическая синхронизация языка**: Язык плагина автоматически подтягивается из текущего языка панели Pelican.

---

### 💻 Manual CLI Installation / Ручная установка через консоль
```bash
# Clone into Pelican plugins directory
cd /var/www/pelican/plugins
git clone https://github.com/MrPanica/pelican-advanced-metrics.git

# Set permissions, install, and clear cache
chown -R www-data:www-data /var/www/pelican/plugins/pelican-advanced-metrics
cd /var/www/pelican
php artisan p:plugin:install pelican-advanced-metrics
php artisan optimize:clear
```

## 📄 License

MIT License. Developed for gaming communities running Source engine servers on Pelican Panel.

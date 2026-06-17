# StreakFlow

Ứng dụng quản lý thói quen hàng ngày kết hợp gamification. Setup task một lần, hệ thống tự tạo task theo lịch mỗi ngày, người dùng mark done/skip rồi chủ động chốt ngày để nhận điểm.

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.2+)
- **Frontend:** Vue 3 + Inertia.js
- **UI:** Tailwind CSS
- **Database:** SQLite (dev) / MySQL (production)
- **Queue:** Laravel Queue (database driver)
- **Cron:** Laravel Task Scheduling

## Cài đặt

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Chạy dev:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Terminal 3 (queue worker)
php artisan queue:work
```

## Cron Jobs

Thêm vào crontab:

```
* * * * * cd /path/to/streakflow && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled commands:
- `00:05` — `streakflow:generate-tasks` — Tạo task instances cho ngày mới
- `00:10` — `streakflow:auto-close` — Tự chốt ngày hôm qua cho users chưa chốt

## Tài khoản demo

| Email | Password | Role |
|-------|----------|------|
| admin@streakflow.test | password | admin |
| user@streakflow.test | password | user |

## Cấu trúc chính

```
app/
├── Actions/
│   ├── CloseDaySummaryAction.php      # Core engine chốt ngày
│   └── GenerateDailyTaskInstancesAction.php
├── Services/
│   ├── GamificationService.php      # HP, XP, Level, Streak logic
│   ├── TaskRecurrenceService.php      # Kiểm tra recurrence
│   └── BadgeService.php
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── TaskTemplateController.php
│   ├── TodayController.php
│   ├── ReportController.php
│   └── AdminController.php
└── Console/Commands/
    ├── GenerateDailyTaskInstancesCommand.php
    └── AutoClosePreviousDaySummariesCommand.php
```

## Gamification

- **HP (0–100):** Tính từ skip quota (25% tổng task). Skip thừa = -HP, skip dư = +HP
- **XP:** Base XP × performance multiplier × streak bonus. Không bao giờ bị trừ
- **Level:** XP cần từ level N → N+1 = `100 × N² + 50 × N`
- **Streak:** ≥75% = +1 streak. 100% = +1 shield. <75% = dùng shield hoặc debt (streak ≥30)

## Routes chính

| Method | URI | Mô tả |
|--------|-----|-------|
| GET | /dashboard | Today view |
| CRUD | /tasks | Task templates |
| PATCH | /today/{id}/done\|skip\|undo | Tương tác task |
| POST | /today/close | Chốt ngày |
| GET | /reports/* | Báo cáo ngày/tuần/tháng/tổng quan |
| GET | /admin/* | Admin panel |

## License

MIT

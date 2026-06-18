# StreakFlow

Ứng dụng quản lý thói quen hàng ngày kết hợp gamification. Setup task một lần, hệ thống tự tạo task theo lịch mỗi ngày, người dùng mark done/skip rồi chủ động chốt ngày để nhận điểm.

## Tech Stack

- **Backend:** Laravel 13 (PHP 8.2+)
- **Frontend:** Vue 3 + Inertia.js
- **UI:** shadcn-vue (reka-ui) + Tailwind CSS + Chart.js
- **Database:** SQLite (dev) / MySQL (production)
- **Queue:** Laravel Queue (database driver)
- **Cron:** Laravel Task Scheduling

## Cài đặt

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
pnpm install
pnpm run build
php artisan serve
```

Chạy dev:

```bash
# Terminal 1
php artisan serve

# Terminal 2
pnpm run dev

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

## Cấu trúc & quy ước

```
routes/
├── web.php          → Inertia pages (GET)
├── web_api.php      → API nội bộ (prefix /web_api) → WebApi controllers
└── api.php          → API khách hàng bên ngoài → Api controllers

app/Http/Controllers/WebApi/   → JSON { success, data, message? }
resources/js/
├── Components/Form/Field.vue  → Input + validate
├── Components/DynamicIcon.vue → Lucide icons
├── composables/useApi.js      → API + sonner toast
├── lang/validation.js         → FE validation messages
└── utils/fieldValidation.js

lang/vi/categories.php | tasks.php | today.php   → i18n BE (không dùng ui.php)
```

**Quy ước FE:** `useApi(route('web_api.xxx'))` · `Field` với `validate: 'required|email'` · `<DynamicIcon name="Heart" />` · auto-import components · mutations qua `/web_api/*`

**pnpm:** Dùng `pnpm install` với `.npmrc` trỏ `registry.npmmirror.com` (tránh timeout IPv6 tới registry.npmjs.org). Sync lockfile: `pnpm import`.

## Gamification

- **HP (0–100):** Tính từ skip quota (25% tổng task). Skip thừa = -HP, skip dư = +HP
- **XP:** Base XP × performance multiplier × streak bonus. Không bao giờ bị trừ
- **Level:** XP cần từ level N → N+1 = `100 × N² + 50 × N`
- **Streak:** ≥75% = +1 streak. 100% = +1 shield. <75% = dùng shield hoặc debt (streak ≥30)

## Routes chính

| Method | URI | Mô tả |
|--------|-----|-------|
| GET | /dashboard | Today view (Inertia) |
| GET | /tasks, /categories | Danh sách (Inertia) |
| POST/PATCH/DELETE | /web_api/* | Mutations qua JSON API (session) |
| POST/PATCH/DELETE | /api/* | API khách hàng (Sanctum token) |
| GET | /reports/* | Báo cáo |
| GET | /admin/* | Admin panel |

## License

MIT

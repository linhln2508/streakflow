# Linh Ta Linh Tinh

App cá nhân đa tính năng — task, thói quen, gamification và mở rộng thêm. Viết tắt: **Linh Tinh**.

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
* * * * * cd /path/to/linh-ta-linh-tinh && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled commands:
- `00:05` — `linhtinh:generate-tasks` — Tạo task instances cho ngày mới
- `00:10` — `linhtinh:auto-close` — Tự chốt ngày hôm qua cho users chưa chốt

## Tài khoản demo

| Email | Password | Role |
|-------|----------|------|
| admin@linhtinh.test | password | admin |
| user@linhtinh.test | password | user |

## Cấu trúc & quy ước

```
routes/
├── web.php          → Inertia pages (GET)
├── web_api.php      → API nội bộ (prefix /web_api) → WebApi controllers
└── api.php          → API khách hàng bên ngoài → Api controllers

app/Http/Controllers/WebApi/   → JSON { success, data, message? }
resources/js/
├── Layouts/
│   ├── AppLayout.vue          → Layout mặc định (navbar + main)
│   ├── AuthLayout.vue         → Auth pages (split-screen)
│   └── Blank.vue              → Layout tối giản
├── Components/
│   ├── Layout/                → PageHeader, PageContainer, PageSection, StatCard, EmptyState
│   ├── Navigation/            → AppNavbar, UserStatsBar, ReportsNav
│   ├── Form/                  → Field + Input/* (schema-driven)
│   └── ui/                    → shadcn primitives
├── constants/navigation.js    → Menu + demo accounts
├── constants/brand.js         → APP_NAME, APP_NAME_SHORT
├── composables/useApi.js
└── Pages/                     → Chỉ nội dung trang (không bọc layout)

lang/vi/categories.php | tasks.php | today.php   → i18n BE (không dùng ui.php)
```

**Quy ước FE:** Layout auto gán trong `app.js` · `PageHeader` + `PageContainer` · `useApi(route('web_api.xxx'))` · `Field` với `validate` · `<DynamicIcon />` · auto-import components

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

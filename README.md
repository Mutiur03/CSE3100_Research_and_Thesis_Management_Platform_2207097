# Research & Thesis Management Platform

A centralized web platform for managing the full academic thesis lifecycle — from proposal submission through supervision, milestones, document versioning, meetings, discussion, external review, and final submission.

Built with **Laravel 12**, **Blade**, **Tailwind CSS**, and **Livewire** (SPA navigation).

## Features

| Module | Description |
|--------|-------------|
| **Authentication** | Registration, login, email verification, password reset |
| **Admin setup** | Secure first-admin bootstrap via emailed setup code |
| **Roles** | Student, Supervisor, Reviewer, Admin |
| **Proposals** | Students draft and submit; supervisors approve/reject/request revision |
| **Theses** | Auto-created from approved proposals |
| **Milestones & tasks** | Supervisor-defined schedule with student completion tracking |
| **Documents** | Upload and version control (chapters, appendices, final thesis) |
| **Meetings** | Supervision/committee/defense scheduling with student RSVP |
| **Discussion** | Threaded comments with @mentions and supervisor private notes |
| **Notifications** | In-app + email with per-category user preferences |
| **External review** | Admin/supervisor assigns reviewers; reviewers submit decisions |
| **Final submission** | Students submit final thesis after uploading a final document |
| **Admin oversight** | User/department management, thesis status control, reviewer assignment |

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (default) or MySQL/PostgreSQL

## Quick start (local showcase)

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
copy .env.example .env   # Windows
php artisan key:generate

# 3. Database
php artisan migrate
php artisan storage:link

# 4. Load demo data (recommended for presentation)
php artisan db:seed --class=DemoSeeder

# 5. Build assets and run
npm run build
composer dev
```

Open **http://127.0.0.1:8000** and sign in with a demo account below.

### Demo accounts

All demo accounts use password: **`password`**

| Role | Email |
|------|-------|
| Admin | `admin@researchhub.test` |
| Supervisor | `supervisor@researchhub.test` |
| Student | `student@researchhub.test` |
| Reviewer | `reviewer@researchhub.test` |
| Student (pending proposal) | `student2@researchhub.test` |

> **Note:** Demo seeder creates the admin directly. The `/setup` flow is only needed on a fresh install without demo data.

### Fresh install (without demo seeder)

1. Set `SETUP_ADMIN_EMAIL=your@email.com` in `.env`
2. Visit `/setup` and request a setup code
3. With `MAIL_MAILER=log`, find the code in `storage/logs/laravel.log`
4. Complete admin registration at `/setup/complete`

## Showcase demo script (~12 min)

1. **Admin** — dashboard stats, departments, users, assign reviewer on thesis
2. **Student** — active thesis, milestones, upload document, submit final thesis
3. **Supervisor** — review pending proposal (student2), manage milestones/meetings, assign reviewer
4. **Reviewer** — open assigned review, submit approve/reject/revision decision
5. **Cross-cutting** — notifications, profile preferences, run `php artisan test`

## Running tests

```bash
php artisan test
```

109+ automated feature tests cover auth, proposals, milestones, documents, meetings, comments, notifications, and thesis reviews.

## Scheduled tasks

Milestone reminder emails run daily at 08:00:

```bash
php artisan milestones:send-reminders
```

In production, configure a cron entry: `* * * * * php artisan schedule:run`

## Project structure

```
app/
├── Enums/           # Status enums (Proposal, Thesis, Review, Milestone, etc.)
├── Http/Controllers/ # Role-based controllers
├── Models/          # Eloquent models
├── Policies/        # Authorization policies
├── Services/        # Business logic (documents, reviews, notifications)
└── Notifications/   # In-app + mail notifications
database/
├── migrations/      # Schema
├── factories/       # Test factories
└── seeders/         # DemoSeeder for showcase
resources/views/     # Blade templates + components
tests/Feature/       # Feature tests
```

## License

MIT

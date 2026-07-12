# Research & Thesis Management Platform

A centralized web platform for managing the academic thesis lifecycle — from proposal submission through supervision, milestones, document versioning, meetings, discussion, and final submission.

Built with **Laravel 12**, **Blade**, **Tailwind CSS**, and **Livewire** (SPA-style navigation via `wire:navigate`).

## Documentation

Full guides live in [`docs/`](docs/README.md):

| Guide | Description |
|-------|-------------|
| [Teacher Presentation](docs/TEACHER_PRESENTATION.md) | What it can do, what’s next, how to demo for your teacher |
| [User Guide](docs/USER_GUIDE.md) | How to use the platform by role |
| [Roles & Permissions](docs/USER_GUIDE_ROLES.md) | Who can do what |
| [System Guide](docs/SYSTEM_GUIDE.md) | Architecture, setup, Laravel internals |
| [Showcase script](docs/USER_GUIDE.md#3-the-thesis-lifecycle) | Demo flow (see User Guide + DemoSeeder below) |
| [Project Blueprint](docs/PROJECT_BLUEPRINT.md) | Vision and implementation status |
| [Module execution](docs/AGENTIC_MODULE_EXECUTION.md) | Build order for remaining modules |

## Quick start (showcase)

```bash
composer install
npm install
copy .env.example .env          # Windows — use cp on macOS/Linux
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan db:seed --class=DemoSeeder
npm run build
composer dev
```

Open **http://127.0.0.1:8000**

### Demo accounts (password: `password`)

| Role | Email |
|------|-------|
| Supervisor | `mutiur@alturavent.com` |
| Student | `mutiur5bb@gmail.com` |
| Student (pending proposal) | `helloworldkuet@gmail.com` |

Create the first admin via `/setup`, then use the accounts above.

Reset demo: `php artisan migrate:fresh --seed --seeder=DemoSeeder`

Details: [docs/setup section in SYSTEM_GUIDE.md](docs/SYSTEM_GUIDE.md#1-quick-start-install--first-login)

## Tests

```bash
composer test
```

**104** automated tests.

## License

MIT

# ResearchHub

ResearchHub is a role-based research and thesis management platform for universities. It brings the full thesis workflow into one place: proposal submission and review, thesis supervision, milestone tracking, document versioning, meetings, discussion, and final submission.

The application is built with Laravel 12, Blade, Livewire, Tailwind CSS 4, and Vite.

## Features

### Students

- Register under a department and choose a supervisor.
- Create, edit, submit, and revise research proposals.
- Track an approved proposal after it becomes a thesis.
- View milestones and update assigned task progress.
- Upload thesis documents and add new file versions without losing their history.
- Discuss work with a supervisor using threaded comments and mentions.
- View scheduled supervision meetings.
- Submit the completed thesis for final review.

### Supervisors

- Review assigned proposals and approve, reject, or request revisions.
- Automatically create a thesis when a proposal is approved.
- Define milestones, dependencies, deadlines, and tasks.
- Monitor thesis progress and student task completion.
- Upload and review versioned thesis documents.
- Schedule in-person or online meetings and record agendas and minutes.
- Keep private supervisor notes in thesis discussions.
- Optionally connect Google Calendar to create and synchronize Google Meet events.

### Administrators

- Complete the secure first-administrator setup flow.
- Create and maintain academic departments.
- Manage user roles, departments, and account status.
- View all theses and update their status.

## Thesis workflow

```text
Student drafts proposal
        ↓
Student submits proposal
        ↓
Supervisor reviews it
        ↓
Approve → thesis is created
Revise  → student updates and resubmits
Reject  → proposal is closed
        ↓
Milestones, tasks, documents, meetings, and discussion
        ↓
Student submits the final thesis
        ↓
Administrator records the final thesis status
```

## Requirements

- PHP 8.2 or later
- Composer
- Node.js 20 or later and npm
- SQLite (default), MySQL, PostgreSQL, or another database supported by Laravel

The relevant PHP extensions for your selected database, file handling, and Laravel installation must also be enabled.

## Installation

1. Clone the repository and enter the project directory.

   ```bash
   git clone <repository-url>
   cd Research_and_Thesis_Management_Platform
   ```

2. Install the PHP dependencies and create the environment file.

   ```bash
   composer install
   ```

   Windows:

   ```powershell
   Copy-Item .env.example .env
   ```

   macOS/Linux:

   ```bash
   cp .env.example .env
   ```

3. Generate the application key.

   ```bash
   php artisan key:generate
   ```

4. Configure the database in `.env`. SQLite is enabled by default. If the database file does not exist, create it:

   Windows:

   ```powershell
   New-Item database/database.sqlite -ItemType File
   ```

   macOS/Linux:

   ```bash
   touch database/database.sqlite
   ```

5. Install and build the frontend assets, then prepare the database and public storage link.

   ```bash
   npm install
   npm run build
   php artisan migrate
   php artisan storage:link
   ```

6. Start the application and Vite development server.

   ```bash
   composer dev
   ```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

> You can also run `composer setup` after creating and configuring `.env` and the database. It installs dependencies, generates the key, runs migrations, creates the storage link, and builds the frontend.

## First administrator setup

Before anyone can use the platform, configure the administrator email in `.env`:

```env
SETUP_ADMIN_EMAIL=admin@university.edu
SETUP_TOKEN_LIFETIME=60
```

Visit `/setup`, request a verification code, and use it to create the first administrator account. With the default local mail configuration (`MAIL_MAILER=log`), the email and setup code are written to `storage/logs/laravel.log` instead of being delivered. Configure an SMTP or supported Laravel mail provider when real email delivery is required.

After setup, the administrator should create departments. Students and supervisors can then register, verify their email addresses, and be activated or managed by an administrator.

## Demo data

To explore a populated thesis workflow, run:

```bash
php artisan db:seed --class=DemoSeeder
```

Then create the first administrator through `/setup`. The seeded accounts all use the password `password`:

| Role | Email | Scenario |
| --- | --- | --- |
| Supervisor | `mutiur@alturavent.com` | Assigned proposals and an active thesis |
| Student | `mutiur5bb@gmail.com` | Active thesis with milestones, documents, and discussion |
| Student | `helloworldkuet@gmail.com` | Submitted proposal awaiting review |

To completely reset a local demo database:

```bash
php artisan migrate:fresh --seed --seeder=DemoSeeder
```

> `migrate:fresh` deletes all tables and their data. Use it only for disposable local environments.

## Google Calendar and Meet integration

Online meetings work without this integration, but automatic Google Calendar events and Meet links require a Composio Google Calendar connection.

Add the following values to `.env`:

```env
COMPOSIO_API_KEY=
COMPOSIO_BASE_URL=https://backend.composio.dev
COMPOSIO_GOOGLE_CALENDAR_AUTH_CONFIG_ID=
```

After these values are configured, a supervisor can open **Profile**, connect Google Calendar, and schedule an online meeting. ResearchHub will create, update, or delete the corresponding calendar event and invite the thesis participants.

## Useful commands

```bash
# Run the application and frontend development server
composer dev

# Build production frontend assets
npm run build

# Run the test suite
composer test

# Format PHP code
./vendor/bin/pint

# Clear cached Laravel configuration
php artisan optimize:clear
```

## Project structure

```text
app/
├── Enums/                 Domain statuses, roles, and categories
├── Http/Controllers/      Admin, authentication, student, and supervisor flows
├── Http/Middleware/       Setup, role, ownership, and account checks
├── Models/                Thesis domain models and relationships
└── Services/              Composio and Google Calendar integration
database/
├── factories/             Test model factories
├── migrations/            Database schema
└── seeders/               Demo data
resources/
├── css/                   Tailwind application styles
├── js/                    Frontend behavior
└── views/                 Blade pages and components
routes/web.php             Web routes and role-protected areas
tests/                     PHPUnit unit and feature tests
```

## Security and access model

- Routes are protected by authentication, verified-email, active-account, role, and ownership middleware.
- Students can access only their own proposals and theses.
- Supervisors can access only work assigned to them.
- Private discussion notes are hidden from students.
- The initial administrator is restricted to the email configured by the deployer.

## License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

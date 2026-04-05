# Library Management System
Created By Saroj Mehta

## Description
Library Management System is a role-based web application for handling library operations from a single platform. It is built for administrators, library staff, and students.

The system helps libraries organize books, manage user accounts, process borrowing requests, issue and return books, track fines, send notifications, and maintain an audit trail of important actions.

## Features

### Core Features

#### Book Management
- Add, edit, search, filter, sort, and categorize books
- Upload book cover images
- Track ISBN, author, publisher, shelf number, condition, total copies, and available copies
- Notify users about newly added books and low inventory
- Prevent direct deletion when a book still has active issues or open requests
- Allow staff to submit deletion requests for admin review instead of deleting directly

#### Student Management
- Manage student records with department, batch, semester, contact, and profile details
- View student summaries, borrowing history, requests, fines, and activity logs
- Activate or deactivate student accounts
- Reset student passwords from the admin panel
- Override student borrowing rules through per-student privilege settings
- Generate downloadable fine receipts for paid student fines

#### Circulation
- Student book request flow with `pending`, `approved`, `rejected`, `cancelled`, `issued`, and `returned` states
- Admin and staff approval/rejection workflows
- Book issue and return workflows for staff and admin
- Due date tracking based on global fine settings or student-specific privilege overrides
- Duplicate active requests and duplicate active borrowings are blocked
- Student can cancel a request while it is still pending

#### Fine and Payment System
- Automatic overdue fine calculation with grace period support
- Global fine rules for per-day fine, max fine amount, issue duration, and penalties
- Extra penalties for fair-condition, damaged, and lost returns
- Mark fines as paid
- Waive fines with a required reason
- Adjust fine amounts manually
- View fine history and bulk process fine actions
- Queue reminder or status emails for pending, paid, and waived fines

#### Notifications System
- Custom database-backed notification system for all roles
- Notifications for request approvals/rejections, issued books, returned books, overdue books, fines, account events, and security events
- Unread count, mark-as-read, mark-all-read, delete-one, and clear-read actions
- Polling-based notification refresh in the role dashboards

### Role-Based Access

#### Admin Capabilities
- Full access to books, users, students, circulation, fines, reports, activity logs, settings, and security management
- Create admin users directly
- Create invited staff and student accounts and send registration invitation emails
- Manage role changes, account status, password resets, and student borrowing privileges
- Configure library branding and fine/borrowing policy
- Review account lockouts and unlock accounts
- View reports for inventory, transactions, fines, users, and overdue items

#### Staff Capabilities
- Access a staff dashboard focused on daily circulation work
- Manage books and categories
- Issue and return books
- Process book requests
- Manage fines, including bulk actions and fine emails
- View student profiles and activate/deactivate student accounts
- Manage personal profile and password settings
- Submit book deletion requests to administrators

#### Student Capabilities
- Access a student dashboard with issued books, requests, fines, and notifications
- Search and filter books
- Submit book requests
- Cancel pending requests
- View current and returned books
- View fine history and outstanding amounts
- Update profile details, password, and profile photo

### Authentication and Access
- Session-based authentication using Laravel's web guard
- Invitation-based onboarding for staff and students
- Current onboarding is invitation-based rather than open public registration
- Staff and student registration is matched against an invited role, email, phone number, and staff/student ID
- Role-based redirects after login
- Role-based route protection using Laravel gates
- Inactive accounts are blocked from signing in and redirected to an inactive-account page
- Admin password resets can force the user to change password on next login
- Password reset and email verification routes are included in the application

### Security Features
- Email verification support through Laravel auth routes
- Invitation identity checks during self-registration
- Registration rate limiting: 5 attempts per minute per email and IP
- Configurable login rate limiting and account lockout
- Signed email unlock links for locked accounts
- Admin account lock monitoring and unlock controls
- CLI account unlock command: `php artisan auth:unlock-account`
- Suspicious activity notifications after repeated failed logins
- Activity logging with IP address, browser, device type, and metadata
- Server-side validation across auth, books, students, requests, fines, and settings
- Forced password change support after admin-initiated password reset

### Technical Features
- Database-backed sessions, cache, and queue by default
- Queue-first email delivery using a dedicated `emails` queue with retries and delay
- Scheduled commands for overdue fine calculation and reminder emails
- AJAX-based tables, filters, stats panels, and notification panels
- Responsive admin, staff, and student layouts
- Light and dark theme toggle in all role portals
- Broadcast notification event class exists, while the shipped UI currently refreshes notifications through API polling

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 12, PHP 8.2 |
| Frontend | Blade templates, Tailwind CSS, custom CSS/JavaScript, Vite |
| UI Assets | Lucide icons, Font Awesome |
| Database | MySQL (recommended), SQLite supported for quick local setup |
| Queue | Database queue |
| Email | SMTP (Gmail or any SMTP-compatible provider) |
| Testing | Pest, PHPUnit |

Note: the current codebase uses Blade + Tailwind CSS + custom CSS/JS. Bootstrap is not installed as a package dependency in this repository.

## Installation Guide

### 1. Clone the repository
```bash
git clone https://github.com/mehtasaroj122/LMS_Real.git
cd LMS_Real
```

### 2. Install backend dependencies
```bash
composer install
```

### 3. Install frontend dependencies
```bash
npm install
```

### 4. Create the environment file
```bash
cp .env.example .env
```

If you are using Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 5. Generate the application key
```bash
php artisan key:generate
```

### 6. Configure `.env`
- Update database settings
- Update mail settings
- Keep `QUEUE_CONNECTION=database`
- Keep `SESSION_DRIVER=database`
- Keep `CACHE_STORE=database` if you want account-lock monitoring to inspect active locks

### 7. Create the public storage symlink
```bash
php artisan storage:link
```

### 8. Run migrations
```bash
php artisan migrate
```

### 9. Optional: seed sample data
```bash
php artisan db:seed
```

The repository includes JSON-backed seeders for users, students, staff, books, requests, issues, and fines. Review the files inside `database/JSON/` before using seeded accounts in a shared environment.

### 10. Build frontend assets
For development:

```bash
npm run dev
```

For a production-style asset build:

```bash
npm run build
```

## Environment Variables

Below are the most important environment values for this project:

### Application
```env
APP_NAME="Library Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

### Database
MySQL example:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_management_system
DB_USERNAME=root
DB_PASSWORD=
```

Quick local default from `.env.example`:

```env
DB_CONNECTION=sqlite
```

### Session, Cache, and Queue
```env
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_QUEUE=emails
MAIL_SEND_DELAY_SECONDS=3
```

### Mail
Gmail SMTP example:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Security
```env
SECURITY_MAX_LOGIN_ATTEMPTS=5
SECURITY_LOCKOUT_DURATION=60
SECURITY_RATE_LIMITING_ENABLED=true
SECURITY_EMAIL_UNLOCK_ENABLED=true
SECURITY_LOG_FAILED_ATTEMPTS=true
SECURITY_LOG_LOCKOUTS=true
SECURITY_LOG_UNLOCKS=true
```

## Running the Project

Start the Laravel development server:

```bash
php artisan serve
```

Start the queue worker for emails and default jobs:

```bash
php artisan queue:work --queue=emails,default
```

Run the Vite development server in another terminal if you are working in development mode:

```bash
npm run dev
```

Optional: run the scheduler locally if you want overdue and fine reminder automation during development:

```bash
php artisan schedule:work
```

Optional: use the built-in combined dev command:

```bash
composer run dev
```

## Application Workflow

### Admin -> Staff -> Student flow
1. Admin sets up departments, users, library rules, branding, and security settings.
2. Admin creates staff and student accounts. Staff and student accounts can be invited and remain inactive until registration is completed.
3. Staff manages day-to-day operations such as catalog updates, issue/return work, request handling, and fine follow-up.
4. Students sign in to search books, submit requests, track issued books, and view fines and notifications.

### Request -> Approval -> Issue -> Return -> Fine flow
1. Student searches the catalog and submits a request for an available book.
2. The request enters `pending` state and staff/admin receive notifications.
3. Staff or admin approves or rejects the request.
4. Once approved, staff or admin issues the book and the request is marked `issued`.
5. On return, the system updates stock, marks the issue as returned, and checks overdue days plus return condition.
6. If needed, a fine record is created or updated based on overdue rules or return penalties.
7. Staff or admin can mark the fine as paid, waive it with a reason, or send reminder emails.

## Screenshots

This repository does not currently ship project screenshots. You can add them here later, for example:

```md
![Admin Dashboard](docs/screenshots/admin-dashboard.png)
![Staff Circulation](docs/screenshots/staff-circulation.png)
![Student Portal](docs/screenshots/student-portal.png)
```

The `docs/` folder already contains system diagrams such as DFDs and ER diagrams that can also support project documentation.

## Folder Structure

```text
LMS_Real/
├── app/
│   ├── Console/              # Artisan commands and scheduled jobs
│   ├── Events/               # Broadcastable application events
│   ├── Helpers/              # Shared helpers such as activity logging
│   ├── Http/
│   │   ├── Controllers/      # Admin, Staff, Student, and Auth controllers
│   │   ├── Middleware/       # Access and password-change middleware
│   │   └── Requests/         # Form request validation classes
│   ├── Jobs/                 # Queued email jobs
│   ├── Mail/                 # Mailables for invitations, reminders, and account mail
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Laravel notification classes
│   ├── Observers/            # Model observers
│   ├── Providers/            # Service providers, gates, and event listeners
│   ├── Services/             # Business logic for auth, students, fines, and requests
│   └── Support/              # Supporting domain utilities
├── config/                   # Auth, mail, queue, security, database, and app config
├── database/
│   ├── migrations/           # Database schema
│   ├── seeders/              # Seeders
│   └── JSON/                 # Sample JSON data used by seeders
├── docs/                     # DFDs, ER diagrams, and documentation assets
├── Implementation Guides/    # Implementation notes and project guides
├── public/
│   ├── admin/                # Admin CSS and JavaScript
│   ├── staff/                # Staff CSS and JavaScript
│   ├── student/              # Student CSS and JavaScript
│   └── shared/               # Shared frontend assets
├── resources/
│   ├── css/                  # Vite-managed styles
│   ├── js/                   # Vite-managed JavaScript bootstrap
│   └── views/                # Blade templates
├── routes/                   # Web, auth, and console routes
├── storage/                  # Logs, cache, sessions, and uploaded files
├── tests/                    # Pest feature tests
├── tools/                    # Utility scripts
└── README.md
```

## Contribution

Contributions are welcome. A simple workflow is:

1. Fork the repository.
2. Create a feature branch.
3. Make your changes.
4. Run the test suite:

```bash
php artisan test
```

5. Open a pull request with a clear description of the change.

## License

This project is distributed under the MIT License, as declared in `composer.json`.

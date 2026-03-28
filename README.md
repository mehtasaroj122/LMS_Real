# 📚 Library Management System (LMS) 
# Created By Saroj Mehta

> A modern, secure, and feature-rich Library Management System built with Laravel, featuring advanced security protocols, comprehensive activity logging, and an intuitive admin dashboard.

[![Laravel](https://img.shields.io/badge/Laravel-11-FF5A3D?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Active-brightgreen?style=flat-square)](#)

---

## ✨ Features

### Core Functionality
- 📖 **Book Management** - Add, update, delete, and search books with advanced filtering
- 👥 **User Management** - Complete user lifecycle management with role-based access control
- 📋 **Borrowing System** - Track book borrowing and returns with due date management
- 📊 **Dashboard** - Real-time statistics and analytics for administrators
- 📱 **Responsive Design** - Mobile-friendly interface built with Tailwind CSS

### Security Features
- 🔒 **Account Lockout System** - Automatic account lockout after failed login attempts
- ⏸️ **Account Inactivity** - Disable inactive accounts based on configurable thresholds
- 📝 **Activity Logging** - Comprehensive audit trail for all user actions
- 🔐 **Role-Based Access Control** - Fine-grained permission system
- 🛡️ **Security Hardening** - XSS protection, CSRF tokens, SQL injection prevention
- 🔑 **Two-Way Authentication** - Enhanced security for administrative functions

### Administrative Features
- 📊 **Admin Dashboard** - Comprehensive data tables with filtering, sorting, and pagination
- 🔔 **Admin Notifications** - Real-time alerts for critical events
- 📧 **Transaction Email Notifications** - Automated email notifications for important events
- 📈 **Advanced Reporting** - Detailed reports on user activity, borrowing trends, and system usage
- 🎯 **User Management Controls** - Unlock accounts, deactivate users, manage permissions

---

## 🛠️ Tech Stack

### Backend
- **Laravel 11** - Modern PHP framework
- **PHP 8.2+** - Server-side language
- **MySQL** - Database management
- **Composer** - Dependency management

### Frontend
- **Blade Templates** - Laravel templating engine
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Next-generation build tool
- **JavaScript (Vanilla/Alpine.js)** - Interactivity

### Additional Tools
- **PHPUnit** - Testing framework
- **PostgreSQL Support** - Alternative database option
- **Queue System** - Background job processing
- **Mail System** - Email notifications

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or PostgreSQL 9.6+
- Node.js 18+ and npm/yarn
- Git

---

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/library-management-system.git
cd Library_Management_System_V4
```

### 2. Install Backend Dependencies
```bash
composer install
```

### 3. Install Frontend Dependencies
```bash
npm install
# or
yarn install
```

### 4. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with:
```env
APP_NAME=LibraryMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_ms
DB_USERNAME=root
DB_PASSWORD=

MAIL_DRIVER=log
MAIL_FROM_ADDRESS=noreply@libraryms.local
```

### 5. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

This will create all necessary tables and populate initial data including default admin user.

### 6. Build Frontend Assets
```bash
npm run build
# For development with hot reload:
npm run dev
```

### 7. Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

---

## 📖 Application Usage

### Default Admin Credentials
After running migrations and seeders:
- **Email**: `admin@libraryms.local`
- **Password**: Check `database/seeders/` for the seeded password

⚠️ **Important**: Change the default password immediately after first login.

### Key User Roles
- **Admin** - Full system access, user management, reports
- **Librarian** - Book management, user registration, borrowing approvals
- **Member** - Browse books, place borrowing requests, manage profile

---

## 📁 Project Structure

```
LibraryMS/
├── app/
│   ├── Console/           # Artisan commands
│   ├── Events/            # Application events
│   ├── Http/              # Controllers, Middleware, Requests
│   ├── Jobs/              # Queued jobs
│   ├── Mail/              # Mailable classes
│   ├── Models/            # Eloquent models
│   ├── Notifications/     # Notification classes
│   ├── Observers/         # Model observers
│   ├── Providers/         # Service providers
│   └── Services/          # Business logic services
├── bootstrap/             # Framework bootstrap files
├── config/                # Application configuration
├── database/
│   ├── factories/         # Model factories for testing
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── Implementation Guides/ # Comprehensive documentation
├── public/                # Web root directory
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── views/             # Blade templates
├── routes/                # Application routes
├── storage/               # Logs, cache, uploads
├── tests/                 # Test files
└── tools/                 # Utility scripts
```

---

## 🔐 Security Features in Detail

### Account Lockout System
- Automatic lockout after 5 failed login attempts (configurable)
- Customizable lockout duration (15, 30, 60 minutes)
- Admin unlock functionality
- Email notifications to locked-out users
- Activity logging of lock/unlock events

### Account Inactivity Feature
- Automatic account deactivation after 90 days of inactivity (configurable)
- Graceful user experience with warning notifications
- Admin control panel for manual activation/deactivation
- Secure reactivation process

### Activity Logging
- Comprehensive audit trail of all user actions
- Tracks login attempts, permission changes, data modifications
- Searchable and filterable logs
- Security event categorization

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test --filter=UserTest
```

### Generate Test Coverage
```bash
php artisan test --coverage
```

---

## 📧 Email Configuration

The system supports multiple mail drivers. Configure in `.env`:

```env
# Log emails to log file (development)
MAIL_DRIVER=log

# Send actual emails
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

# Or use Mailgun, SendGrid, etc.
```

See [MAIL_SYSTEM_DISABLED_GUIDE.md](MAIL_SYSTEM_DISABLED_GUIDE.md) for disabling the mail system.

---

## 📚 Documentation

Comprehensive implementation guides are available in the `Implementation Guides/` directory:

- [Account Lockout System](Implementation%20Guides/ACCOUNT_LOCKOUT_COMPLETE_GUIDE.md)
- [Account Inactivity Feature](Implementation%20Guides/ACCOUNT_INACTIVE_COMPLETE_SUMMARY.md)
- [Activity Logs Documentation](Implementation%20Guides/ACTIVITY_LOGS_DOCUMENTATION.md)
- [Admin Notifications](Implementation%20Guides/ADMIN_NOTIFICATIONS_COMPLETE.md)
- [API Documentation](Implementation%20Guides/ACCOUNT_LOCKOUT_API_DOCUMENTATION.md)
- [Master Documentation Index](Implementation%20Guides/_DOCUMENTATION_MASTER_INDEX.md)

---

## 🎯 Roadmap

- ✅ Core library management features
- ✅ Account security features
- ✅ Activity logging system
- ⏳ Mobile app (React Native)
- ⏳ Advanced analytics dashboard
- ⏳ Integration with external book databases
- ⏳ Multi-language support
- ⏳ REST API expansion

---

## 🤝 Contributing

Contributions are welcome and appreciated! Here's how to get started:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure all tests pass and follow the project's code style guidelines.

---

## 🐛 Troubleshooting

### Common Issues

**Problem**: "Cannot find database"
```bash
# Solution: Ensure database is created and .env is configured
php artisan migrate:fresh --seed
```

**Problem**: "File not found" errors
```bash
# Solution: Set correct file permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

**Problem**: Assets not loading
```bash
# Solution: Rebuild frontend assets
npm run build
```

For more troubleshooting, see [ACCOUNT_LOCKOUT_FAQ_TROUBLESHOOTING.md](Implementation%20Guides/ACCOUNT_LOCKOUT_FAQ_TROUBLESHOOTING.md)

---

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author & Support

**Library Management System V4**

For issues, questions, or suggestions:
- 📧 Email: librarymanagementsystem270@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/library-management-system/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/yourusername/library-management-system/discussions)

---

## 🙏 Acknowledgments

- Laravel community for the amazing framework
- Tailwind CSS for beautiful styling
- All contributors and users

---

**Last Updated**: February 2026  
**Current Version**: 4.0.0  
**Status**: ✅ Production Ready
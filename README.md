# 📚 Library Management System

A professional college-level library management project built to simplify daily library operations such as managing books, handling users, issuing and returning books, tracking fines, and monitoring activity. This system is designed for academic use and also works well as a student portfolio project on GitHub.

## ✨ Description

The **Library Management System** helps libraries organize and automate their core tasks in one place. It provides role-based access for **Admin**, **Staff**, and **Student** users, making it easier to manage books, user records, requests, transactions, and reports through a clean web interface.

## 🚀 Features

- 📖 Add, edit, delete, and manage books
- 🔎 Search and browse available books
- 👤 Manage users, students, and staff accounts
- 📥 Issue books to students
- 📤 Return books and update availability
- 📝 Handle book requests
- 💰 Track overdue fines and payments
- 📊 Admin dashboard with system overview
- 🧾 Activity logs for important actions
- 🔐 Authentication and role-based access control
- 🔔 Notification support for key events
- 🖼️ Profile management for users

## 🛠️ Technologies Used

- **Backend:** PHP, Laravel
- **Frontend:** Blade Templates, HTML, CSS, JavaScript, Tailwind CSS
- **Build Tool:** Vite
- **Database:** MySQL
- **Authentication:** Laravel Breeze
- **Testing:** Pest / PHPUnit
- **Package Managers:** Composer, npm
- **Version Control:** Git, GitHub

## ⚙️ Installation Instructions

### 1. Clone the repository

```bash
git clone https://github.com/your-username/library-management-system.git
cd Library_Management_System_V4
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install frontend dependencies

```bash
npm install
```

### 4. Create the environment file

For Windows:

```bash
copy .env.example .env
```

For macOS/Linux:

```bash
cp .env.example .env
```

### 5. Generate the application key

```bash
php artisan key:generate
```

### 6. Configure the database

Update your `.env` file with your database details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_management_system
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Run migrations and seeders

```bash
php artisan migrate --seed
```

### 8. Start the development servers

Backend server:

```bash
php artisan serve
```

Frontend dev server:

```bash
npm run dev
```

The application will usually run at:

```bash
http://127.0.0.1:8000
```

## ▶️ Usage

1. Open the project in your browser.
2. Log in with a seeded user account or create a new account if enabled.
3. Use the dashboard based on your role.

**Admin:** Manage users, books, reports, settings, and transactions.  
**Staff:** Handle issue/return workflows, book requests, fines, and students.  
**Student:** Search books, request books, view fines, and check issued books.

### Sample Seeded Login

```text
Admin Email: mehtasaroj122@gmail.com
Admin Password: 12345678
```

> After first login, change the default password for security.

## 📁 Project Structure

```text
Library_Management_System_V4/
├── app/                # Controllers, models, services, middleware
├── bootstrap/          # Application bootstrap files
├── config/             # Laravel configuration files
├── database/           # Migrations, seeders, JSON seed data
├── docs/               # Diagrams and project documentation
├── public/             # Public assets and entry point
├── resources/          # Blade views, CSS, JS files
├── routes/             # Web and auth routes
├── storage/            # Logs, cache, uploads
├── tests/              # Feature and unit tests
├── composer.json       # PHP dependencies
├── package.json        # Frontend dependencies
└── README.md           # Project documentation
```

## 🖼️ Screenshots

Screenshots can be added here to show:

- Landing page
- Admin dashboard
- Book management page
- Issue/return module
- Student dashboard

Example:

```md
![Admin Dashboard](screenshots/admin-dashboard.png)
![Book Management](screenshots/book-management.png)
```

## 🔮 Future Enhancements

- 📷 Add actual project screenshots and demo GIFs
- 📄 Generate PDF reports and receipts
- 📱 Improve mobile responsiveness further
- 📚 Add barcode or QR-based book issuing
- 📧 Expand email and notification features
- ☁️ Deploy the system online for live demo access
- 📈 Add more analytics and chart-based reports

## 👨‍💻 Author / Credits

**Saroj Mehta Arya**

- College-level academic project
- Built for learning, practice, and portfolio presentation

## 📄 License

This project is released under the **MIT License** and may also be used for **academic and learning purposes**.

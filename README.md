# 🏥 Smart Hostel Management System

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Industry--Grade-blue?style=for-the-badge)](#)

A comprehensive, role-based enterprise solution designed to digitize and automate the entire lifecycle of hostel operations. From student onboarding and room allocation to complex fee structures and automated notifications, this system provides a robust framework for institutional management.

---

## 🌟 Key Features

### 🔐 Enterprise Security & RBAC
- **Multi-Tenant Ready**: Support for multiple universities/institutions.
- **Granular Permissions**: Built-in Role-Based Access Control (RBAC) using Spatie Laravel-Permission.
- **Secure Auth**: JWT/Sanctum based authentication for secure API and Web sessions.

### 🏠 Housing Operations
- **Inventory Management**: Track buildings, rooms, and individual beds in real-time.
- **Allocation Logic**: Intelligent room mapping with occupancy tracking and vacancy alerts.
- **Asset Tracking**: Management of hostel accessories (furniture, electronics) with checkout logs.

### 💰 Financial Ecosystem
- **Dynamic Fee Engine**: Complex fee heads including Caution Money, Mess Fees, and Facility Charges.
- **Exception Workflow**: Formalized waiver and fee exception requests with multi-level approval.
- **Payment Integration**: Audit-trailed payment modifications and history.

### 📋 Student & Staff Lifecycle
- **Unified Profile**: Comprehensive student records including academic and personal data.
- **Leave Operations**: Digitized leave request and approval workflow.
- **Grievance Redressal**: Integrated feedback and grievance management system.

---

## 🏗 System Architecture

The system is built on a modern MVC architecture using **Laravel 10**, ensuring high performance, scalability, and maintainability.

### Core Modules:
- **Admin/SuperAdmin**: System configuration, institution management, and global reports.
- **Warden Module**: Daily operations, room allocation, and student attendance.
- **Accounts Module**: Fee management, payment verification, and financial reporting.
- **Resident Portal**: Self-service profile, fee tracking, and request submissions.

---

## � Project Structure
## 🚀 Project Structure

```bash
hostel-management-system/
├── app/
│   ├── Http/Controllers/    # Core business logic processing
│   ├── Models/              # Database schema & relationships
│   ├── Services/            # Third-party integrations (AWS, Twilio)
│   └── Providers/           # System service bootstrapping
├── config/                  # Global application configuration
├── database/
│   ├── migrations/          # Version-controlled schema
│   └── seeders/             # Initial system data
├── public/                  # Entry point & static assets
├── resources/
│   ├── views/               # Blade templates / UI components
│   └── js/                  # Frontend logic & styling
├── routes/                  # API & Web route definitions
└── tests/                   # Automated feature & unit tests
```

---

## 🛠 Tech Stack

- **Framework**: [Laravel 10](https://laravel.com/)
- **PHP Version**: 8.1+
- **Database**: MySQL / PostgreSQL
- **Security**: Laravel Sanctum, Spatie Permissions
- **Communication**: Twilio (SMS), Laravel Mail (AWS SES ready)
- **Deployment**: Vite, Composer

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Priyanshu6055/hostal-management-software.git
   cd hostal-management-software
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**
   Update your `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hostel_management
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Migrations & Seeding**
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

6. **Run the Application**
   ```bash
   php artisan serve
   ```

---

## 🤝 Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---
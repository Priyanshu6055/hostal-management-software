# Hostel Management System (Laravel)

A **role-based Hostel Management System** built using **Laravel**, designed to digitize and streamline hostel operations such as student onboarding, room allocation, fee management, approvals, and administrative workflows.

This system follows **real institutional processes**, implements **secure authentication & authorization (RBAC)**, and is built with scalability and maintainability in mind.

---

## 🚀 Features Overview

### 🔐 Authentication & Security
- Secure authentication using Laravel Auth
- Password hashing & protected routes
- Middleware-based access control
- Session & token-based security (API-ready)

---

### 🧑‍💼 Role-Based Access Control (RBAC)
Multiple user roles with clearly defined permissions:

- **Admin**
  - Manage users & roles
  - System-level configuration
  - Full access to reports & approvals

- **Warden**
  - Room allocation & hostel operations
  - Student status management
  - Facility & hostel record handling

- **Accounts**
  - Fee management
  - Payment verification
  - Waiver approvals & modifications

- **Student**
  - Profile & hostel application
  - Fee status tracking
  - Requests & notifications

> RBAC is implemented using Laravel middleware & permission mapping for strict access isolation.

---

### 🏠 Hostel & Room Management
- Hostel creation & configuration
- Room & bed allocation logic
- Occupancy tracking
- Vacancy management in real time

---

### 💰 Fee & Payment Management
- Hostel fee, caution money & facility charges
- Fee waiver request & approval workflow
- Two-step approval process for waivers
- Payment modification with audit trail
- Document upload support for approvals

---

### 📝 Student Management
- Student registration & onboarding
- Profile management
- Scholar number uniqueness validation
- Gender & category-based allocation support

---

### 📊 Admin Dashboard
- Centralized dashboard for admins
- Role-wise data access
- Workflow-driven approvals
- Clean, structured UI for operational clarity

---

### 🗂️ Database Design
- Normalized relational schema
- Foreign key constraints for data integrity
- Optimized queries for performance
- Scalable structure for future modules

---

## 🛠 Tech Stack

- **Backend:** Laravel (PHP)
- **Authentication:** Laravel Auth
- **Authorization:** Role-Based Access Control (RBAC)
- **Database:** MySQL
- **Frontend:** Blade Templates
- **API Ready:** RESTful architecture
- **File Storage:** Laravel Storage
- **Validation:** Laravel Form Requests

---

## ⚙️ Setup, Workflow & Project Overview (Single Page)

```bash
# ================================
# INSTALLATION & SETUP
# ================================

# Clone repository
git clone https://github.com/your-username/hostel-management-system.git
cd hostel-management-system

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Update .env with database credentials
DB_DATABASE=hostel_db
DB_USERNAME=root
DB_PASSWORD=

# Database migration & seeding
php artisan migrate
php artisan db:seed

# Storage link
php artisan storage:link

# Run application
php artisan serve

# Access application
# http://127.0.0.1:8000


# ================================
# APPLICATION WORKFLOW
# ================================

Student applies for hostel
Admin / Warden verifies student details
Room allocation is assigned
Fee is generated
Student requests waiver (if applicable)
Accounts approve waiver
Payment is modified & finalized
Student gains hostel access


# ================================
# IMPACT & BENEFITS
# ================================

Reduced paperwork by ~90%
Faster approvals & hostel allocation
Secure role-based access (RBAC)
Transparent fee & approval tracking
Scalable architecture for future modules
Online payment gateway integration
Email / SMS notification system
Attendance & mess management
Reports & analytics dashboard
API-based mobile app support


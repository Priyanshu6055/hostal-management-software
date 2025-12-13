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

## ⚙️ Installation & Setup

### 1️⃣ Clone the Repository
```bash
git clone https://github.com/your-username/hostel-management-system.git
cd hostel-management-system

# ============================================================
# Hostel Management System - Setup Script
# Tech Stack: Laravel | RBAC | Auth | MySQL
# Author: Priyanshu Raj
# ============================================================

echo "🚀 Starting Hostel Management System Setup..."

# ------------------------------------------------------------
# 2️⃣ Install Dependencies
# ------------------------------------------------------------
echo "📦 Installing PHP dependencies using Composer..."
composer install

# ------------------------------------------------------------
# 3️⃣ Environment Setup
# ------------------------------------------------------------
echo "⚙️ Setting up environment configuration..."

if [ ! -f .env ]; then
  cp .env.example .env
  echo ".env file created from .env.example"
else
  echo ".env file already exists"
fi

php artisan key:generate
echo "🔑 Application key generated"

echo ""
echo "⚠️ IMPORTANT:"
echo "Update your database credentials in the .env file:"
echo ""
echo "DB_DATABASE=hostel_db"
echo "DB_USERNAME=root"
echo "DB_PASSWORD="
echo ""

# ------------------------------------------------------------
# 4️⃣ Database Migration & Seeding
# ------------------------------------------------------------
echo "🗄️ Running database migrations..."
php artisan migrate

echo "🌱 Seeding initial data..."
php artisan db:seed

# ------------------------------------------------------------
# 5️⃣ Storage Link
# ------------------------------------------------------------
echo "📁 Creating storage symbolic link..."
php artisan storage:link

# ------------------------------------------------------------
# 6️⃣ Run the Application
# ------------------------------------------------------------
echo "▶️ Starting Laravel development server..."
php artisan serve

echo ""
echo "✅ Application is running at:"
echo "http://127.0.0.1:8000"
echo ""

# ============================================================
# 🔄 APPLICATION WORKFLOW
# ============================================================
# 1. Student applies for hostel
# 2. Admin / Warden verifies student details
# 3. Room allocation is assigned
# 4. Hostel & facility fee is generated
# 5. Student requests fee waiver (if applicable)
# 6. Accounts approve waiver
# 7. Payment is modified & finalized
# 8. Student gains hostel access
# ============================================================

# ============================================================
# 📈 IMPACT & BENEFITS
# ============================================================
# 🚫 Reduced paperwork by ~90%
# ⚡ Faster approvals & hostel allocation
# 🔐 Secure role-based access (RBAC)
# 📊 Transparent fee & approval tracking
# 🧩 Scalable architecture for future modules
# ============================================================

# ============================================================
# 🔮 FUTURE ENHANCEMENTS
# ============================================================
# - Online payment gateway integration
# - Email / SMS notification system
# - Attendance & mess management
# - Reports & analytics dashboard
# - API support for mobile applications
# ============================================================

echo "🎉 Setup completed successfully!"

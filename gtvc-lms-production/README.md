# Gilgil Technical and Vocational College (GTVC) LMS - Production Package

This repository contains the official, production-ready PHP/MySQL web application for the **Gilgil Technical and Vocational College (GTVC) Learning Management System (LMS)**.

## 🚀 Key Highlights & Production Specs

- **Zero Node.js Dependency**: Runs natively on standard web servers without Node.js, npm, Vite, or TypeScript compilation at runtime.
- **PHP 8.x MVC Architecture**: Clean object-oriented Model-View-Controller codebase using strict typing, PDO prepared statements, and custom session management.
- **MySQL 8.x / MariaDB Ready**: Optimized database schema with full foreign key constraints, indexes, and comprehensive institutional seed data.
- **Standard Shared Hosting Compatible**: Designed for instant deployment on **XAMPP**, **InfinityFree**, **Hostinger**, **cPanel**, or any standard Linux/Windows web server.
- **Role-Aware Portals**: Dedicated server-rendered UI views and workflows for:
  - 🎓 **Students**: Courses, materials, assignments, quizzes, CBET grades, attendance, fee clearance.
  - 👨‍🏫 **Trainers / Lecturers**: Assigned units, gradebook, attendance register, content module builder.
  - 🏛️ **Heads of Department (HOD)**: Department dashboard, student risk monitoring, trainer loads, academic analytics.
  - 💳 **Bursar / Accountants**: Fee structures, student ledgers, M-Pesa verification, term invoices, exam clearance permits.
  - ⚙️ **Administrators**: System settings, user & role management, academic hierarchy, audit logs.

---

## 📂 Directory Structure

```text
gtvc-lms-production/
├── app/
│   ├── config/            # Database and App Configuration
│   ├── controllers/       # Page View & REST API Controllers
│   ├── core/              # MVC Core (Router, View, Controller, Model, Session, Request, FileUpload)
│   ├── middleware/        # Auth & CSRF Protection Middlewares
│   └── models/            # 31 Domain Entity Models (User, Student, CourseOffering, etc.)
│
├── database/
│   ├── schema.sql         # Production Database DDL (MySQL 8.0 / MariaDB)
│   └── seeds.sql          # Seed Data with Test Institutional Accounts
│
├── public/
│   ├── index.php          # Front Controller Entry Point
│   ├── .htaccess          # Apache Rewrite & Security Headers
│   ├── assets/
│   │   ├── css/style.css  # Modern GTVC Design Tokens & Utility CSS
│   │   └── js/app.js      # Vanilla JS Helpers (Modals, Drawers, Tabs, Toasts)
│   └── uploads/           # Public Upload Storage
│
├── views/                 # Server-Rendered PHP Templates
│   ├── layouts/           # Master Layouts & Role-Aware Navigation
│   ├── auth/              # Login & Auth Views
│   ├── student/           # Student Views
│   ├── lecturer/          # Lecturer / Trainer Views
│   ├── hod/               # Head of Department Views
│   ├── accountant/        # Finance / Bursar Views
│   └── admin/             # System Administration Views
│
├── storage/               # Private File Storage & System Logs
│   ├── logs/
│   └── uploads/
│
├── .env.example           # Environment Configuration Template
├── DEPLOYMENT.md          # Comprehensive Deployment Guide (XAMPP, InfinityFree, Hostinger)
└── README.md              # Documentation Overview
```

---

## 🔑 Default Test Credentials

All account passwords in `database/seeds.sql` are set to `password123`.

| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `admin@gilgiltvc.ac.ke` | `password123` |
| **HOD (ICT)** | `hod.ict@gilgiltvc.ac.ke` | `password123` |
| **Trainer / Lecturer** | `jkoech@gilgiltvc.ac.ke` | `password123` |
| **Accountant / Bursar** | `bursar@gilgiltvc.ac.ke` | `password123` |
| **Student** | `student@gilgiltvc.ac.ke` | `password123` |

---

## 📖 Deployment Instructions

For step-by-step setup guides for **XAMPP**, **InfinityFree**, and **Hostinger**, please refer to `DEPLOYMENT.md`.

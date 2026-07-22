# Gilgil TVC Learning Management System

> A modern, secure, and scalable Learning Management System designed for Gilgil Technical and Vocational College (GTVC), supporting students, lecturers/trainers, Heads of Department, accountants, and administrators.

---

## 📚 Overview

The **Gilgil TVC Learning Management System (GTVC LMS)** is a comprehensive web-based platform designed to digitize and streamline academic, learning, assessment, attendance, communication, financial, and administrative workflows within a technical and vocational education environment.

The system is designed around the needs of a TVET institution and supports both traditional academic workflows and competency-based education and training (CBET) processes.

The LMS provides separate role-based experiences for:

* 🎓 Students / Trainees
* 👨‍🏫 Lecturers / Trainers
* 🏛️ Heads of Department (HODs)
* 💳 Accountants / Bursars
* ⚙️ Administrators
* 🔐 Super Administrators

The system has been designed with a strong focus on:

* Usability
* Accessibility
* Security
* Role-based access control
* Mobile responsiveness
* Data isolation
* Maintainability
* Shared-hosting compatibility
* TVET/CBET academic workflows

---

# 🏗️ System Architecture

The project is organized into two major layers.

## 1. Production Application

The production LMS is a standalone:

* PHP 8.x application
* MySQL 8.x / MariaDB application
* Apache-compatible application
* Server-rendered PHP MVC system
* Vanilla JavaScript frontend
* CSS-based design system

The production system does **not** require:

* Node.js
* npm
* Vite
* React
* TypeScript
* Webpack
* Docker
* Redis
* WebSocket servers

This makes the application suitable for:

* XAMPP
* InfinityFree
* Hostinger
* cPanel hosting
* Standard Apache hosting
* Linux servers
* Windows-based local development environments

---

## 2. Development & Documentation Layer

The project also contains a React/Vite development application.

This layer is used as a:

* UI/UX prototype
* Architecture reference
* Living documentation system
* API reference
* Database schema explorer
* PHP MVC code explorer
* Security architecture reference
* Development simulation environment

The React/Vite application is **not required to run the production LMS**.

The production LMS is completely independent and runs using PHP, MySQL/MariaDB, Apache, HTML, CSS, and JavaScript.

---

# ✨ Core Features

## 🎓 Student Portal

Students can access:

* Personal dashboard
* Enrolled courses
* Course modules
* Lessons
* Learning materials
* Video and document resources
* Learning progress
* Assignments
* Assignment submissions
* Online quizzes
* Quiz attempts
* Academic grades
* CBET competency outcomes
* Attendance records
* Practical/workshop hours
* Announcements
* Notifications
* Profile management
* Fee statements
* Payment history
* Exam clearance status

The student experience is designed around minimizing unnecessary steps between the student and their learning activities.

---

## 👨‍🏫 Lecturer / Trainer Portal

Lecturers and trainers can manage:

* Assigned course offerings
* Course modules
* Lessons
* Learning materials
* Assignments
* Student submissions
* Assignment grading
* Feedback
* Online quizzes
* Quiz questions
* Quiz evaluation
* Gradebooks
* Grade publication
* Attendance sessions
* Student attendance
* Practical/workshop tracking
* Student learning progress
* Course announcements

Lecturer access is scoped to authorized course offerings and departmental boundaries.

---

## 🏛️ HOD Portal

Heads of Department can access department-level management tools including:

* Department student rosters
* Department staff
* Course offerings
* Trainer teaching assignments
* Attendance analytics
* Student risk monitoring
* Academic performance
* Department-level reports
* Department announcements

HOD access is restricted to their authorized department scope.

---

## 💳 Accountant / Bursar Portal

Finance staff can manage:

* Fee structures
* Student fee accounts
* Invoices
* Payments
* M-Pesa references
* Bank payments
* Payment verification
* Outstanding balances
* Student financial statements
* Exam clearance
* Clearance overrides

Financial calculations are performed server-side.

Students cannot modify their own:

* Payment verification status
* Fee clearance status
* Outstanding balance
* Financial records

All sensitive financial actions are recorded in the audit trail.

---

## ⚙️ Administrator Portal

Administrators can manage:

* Users
* User accounts
* Roles
* Permissions
* Academic years
* Intakes
* Departments
* Programs
* Program levels
* Units
* Classes/cohorts
* Course offerings
* System settings
* Audit logs

The system supports granular role and permission management.

---

# 📖 Academic Management

The LMS models the institutional academic hierarchy:

```text
Academic Year
    │
    └── Intake
          │
          └── Class / Cohort
                │
                └── Program
                      │
                      └── Program Level
                            │
                            └── Units
                                  │
                                  └── Course Offering
                                        │
                                        ├── Lecturer / Trainer
                                        ├── Modules
                                        ├── Lessons
                                        ├── Assignments
                                        ├── Quizzes
                                        ├── Attendance
                                        └── Grades
```

This structure allows the system to model real institutional academic relationships while maintaining data isolation.

---

# 📚 Learning Management

The learning engine supports:

* Course modules
* Lessons
* Text-based content
* Video resources
* Documents
* External learning resources
* Learning materials
* Student progress tracking
* Lesson completion
* Course completion percentage
* Previous/next lesson navigation

Students can continue learning from their current progress and track their completion status.

---

# 📝 Assessments & Grading

The LMS supports:

### Assignments

* Assignment creation
* Instructions
* Maximum marks
* Release dates
* Due dates
* Late submission policies
* File submissions
* Text submissions
* Submission replacement
* Lecturer grading
* Feedback

### Quizzes

* Multiple-choice questions
* True/false questions
* Short-answer questions
* Question weighting
* Time limits
* Availability windows
* Maximum attempts
* Server-side scoring
* Attempt tracking

### Grading

The gradebook supports:

* Coursework marks
* Examination marks
* Final scores
* Letter grades
* Grade publication
* Student grade isolation
* CBET competency outcomes

Example CBET outcomes include:

* Competent
* Not Yet Competent

All grade access is controlled through server-side authorization.

---

# 🧑‍🏭 Attendance & Practical Tracking

Attendance supports different types of institutional activities:

* Theory classes
* Practical workshops
* Laboratory sessions
* Fieldwork
* Examinations

Attendance statuses include:

* Present
* Late
* Absent
* Excused

The system tracks:

* Arrival times
* Attendance percentages
* Practical hours
* Workshop hours
* Attendance history
* Risk thresholds
* Department-level analytics

Attendance warnings can be generated when students fall below configured institutional thresholds.

---

# 📢 Communication & Notifications

The communication system supports:

## Announcements

Announcements can be targeted by:

* Role
* Department
* Program
* Class/cohort

Announcements support:

* Normal priority
* Important priority
* Urgent priority
* Publication scheduling
* Expiration dates
* Attachments
* Archiving

## Notifications

The notification center supports:

* Assignment notifications
* Grade notifications
* Quiz notifications
* Learning material notifications
* Attendance warnings
* Announcement notifications
* System notifications

Users can:

* View notifications
* Filter notifications
* Mark notifications as read
* Mark notifications as unread
* Mark all notifications as read

---

# 💰 Finance & Fees

The finance system supports:

* Fee structures
* Student fee accounts
* Invoices
* Payment records
* M-Pesa references
* Bank transfers
* Cheques
* Cash records
* Payment verification
* Balance calculations
* Financial statements
* Exam clearance

Fee balances are calculated on the server.

The system prevents unauthorized users from modifying financial records or bypassing examination clearance controls.

---

# 🔐 Security Architecture

Security is a core part of the GTVC LMS architecture.

## Authentication

The system implements:

* Secure password hashing
* Password verification
* Session-based authentication
* Session ID regeneration
* Secure session cookies
* HttpOnly cookies
* SameSite protection
* HTTPS-aware Secure cookies
* Session destruction on logout

---

## RBAC

The application uses server-side role-based access control.

Permissions are represented using granular capability keys such as:

```text
course.create
grade.submit
fee.collect
attendance.mark
announcement.create
user.manage
```

Security decisions are always enforced server-side.

Frontend UI visibility is **not** treated as a security boundary.

---

## CSRF Protection

State-changing requests are protected using CSRF tokens.

Protected operations include:

* POST
* PUT
* PATCH
* DELETE

Token comparison uses secure constant-time comparison mechanisms.

---

## IDOR / BOLA Protection

The system implements resource ownership checks to prevent users from accessing another user's data.

Examples include preventing students from accessing:

* Another student's grades
* Another student's financial records
* Another student's submissions
* Another student's attendance records
* Another student's progress records

---

## Brute-Force Protection

Authentication attempts are rate-limited.

The system tracks failed login attempts and applies temporary lockouts after repeated failures.

---

## SQL Injection Protection

Database operations use PDO prepared statements with bound parameters.

User-controlled input is never directly concatenated into SQL queries.

---

## File Upload Security

Uploaded files are protected using:

* MIME validation
* Extension whitelisting
* File size restrictions
* Randomized filenames
* Double-extension protection
* Path traversal prevention
* Executable file rejection
* Secure file storage
* Authorization checks before downloads

Private files should not be directly accessible through public URLs.

---

## Security Headers

The application supports security headers including:

```text
X-Content-Type-Options
X-Frame-Options
Referrer-Policy
Content-Security-Policy
```

---

## Audit Logging

Security-sensitive and administrative actions are recorded in the audit log.

Examples include:

* Login attempts
* Successful authentication
* Logout
* Unauthorized access
* User management
* Financial transactions
* Payment verification
* Fee clearance changes
* Announcement management
* Security events

---

# 🎨 UI/UX Design System

The LMS uses a centralized design system focused on:

* Clear visual hierarchy
* Consistent spacing
* Predictable navigation
* Minimal cognitive load
* Responsive design
* Mobile-first layouts
* Accessible forms
* Keyboard navigation
* Visible focus states
* Clear feedback
* Loading states
* Empty states
* Error states
* Confirmation dialogs
* Toast notifications

The visual design uses:

* GTVC-inspired teal/green branding
* Academic gold accents
* Neutral slate surfaces
* Centralized CSS custom properties
* Accessible contrast ratios
* Responsive layouts
* Modern typography

The interface is designed to be usable by people with different levels of technical experience.

---

# 🗂️ Production Directory Structure

The production application follows a PHP MVC structure similar to:

```text
gtvc-lms-production/
│
├── app/
│   ├── config/
│   ├── controllers/
│   ├── core/
│   ├── middleware/
│   ├── models/
│   ├── services/
│   └── helpers/
│
├── database/
│   ├── schema.sql
│   └── seeds.sql
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── views/
│   ├── layouts/
│   ├── auth/
│   ├── student/
│   ├── lecturer/
│   ├── hod/
│   ├── accountant/
│   ├── admin/
│   └── shared/
│
├── storage/
│   ├── logs/
│   └── private_uploads/
│
├── README.md
├── DEPLOYMENT.md
└── .env.example
```

---

# 🗄️ Database

The LMS uses a relational MySQL/MariaDB database.

The database schema contains approximately 41 core tables covering:

* Institutional structure
* Academic hierarchy
* RBAC
* Users
* Student profiles
* Staff profiles
* Enrollments
* Course delivery
* Learning materials
* Assessments
* Quizzes
* Grades
* Attendance
* Communication
* Notifications
* Finance
* Payments
* Invoices
* Audit logs

The database is designed for:

* MySQL 8.x
* MariaDB 10.x

The schema can be imported using:

* phpMyAdmin
* MySQL CLI

---

# 💻 Requirements

## Production

Minimum expected environment:

```text
PHP 8.x
MySQL 8.x or MariaDB 10.x
Apache
PDO
PDO_MySQL
PHP Sessions
File Upload Support
.htaccess / mod_rewrite
```

The production LMS does not require Node.js.

---

# 🚀 Installation

## XAMPP

1. Install XAMPP with PHP and MySQL.
2. Start Apache.
3. Start MySQL.
4. Copy the production LMS into the `htdocs` directory.
5. Create a MySQL database using phpMyAdmin.
6. Import:

```text
database/schema.sql
database/seeds.sql
```

7. Configure the database credentials.
8. Configure the application environment.
9. Open the LMS in your browser.

Example:

```text
http://localhost/gtvc-lms-production/public/
```

The exact URL depends on the configured Apache document root.

---

# 🌐 Shared Hosting Deployment

The LMS is designed to support standard shared hosting environments.

Compatible environments may include:

* InfinityFree
* Hostinger
* cPanel hosting
* Apache shared hosting

Typical deployment process:

```text
1. Create MySQL database
2. Create database user
3. Assign database privileges
4. Import schema.sql
5. Import seeds.sql
6. Upload production files
7. Configure database credentials
8. Configure application environment
9. Configure document root / public directory
10. Enable HTTPS
11. Test authentication
12. Test role permissions
```

See:

```text
DEPLOYMENT.md
```

for detailed deployment instructions.

---

# ⚙️ Configuration

Use `.env.example` as a reference for production configuration.

Typical configuration variables include:

```text
APP_ENV
APP_DEBUG
APP_TIMEZONE

DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASS
DB_CHARSET
```

Never commit production credentials to source control.

Production systems should use:

```text
APP_ENV=production
APP_DEBUG=false
```

---

# 🧪 Testing & Verification Status

The project has undergone extensive architectural and static verification across Phases 1–9.

| Component                            | Status             |
| ------------------------------------ | ------------------ |
| React/Vite development application   | Built and verified |
| UI/UX design system                  | Implemented        |
| PHP MVC architecture                 | Implemented        |
| Database schema                      | Implemented        |
| Authentication                       | Implemented        |
| RBAC                                 | Implemented        |
| CSRF protection                      | Implemented        |
| IDOR/BOLA protection                 | Implemented        |
| File upload security                 | Implemented        |
| Academic management                  | Implemented        |
| Learning system                      | Implemented        |
| Assessments                          | Implemented        |
| Grading                              | Implemented        |
| Attendance                           | Implemented        |
| Communication                        | Implemented        |
| Notifications                        | Implemented        |
| Finance                              | Implemented        |
| Administration                       | Implemented        |
| Security hardening                   | Completed          |
| Production PHP conversion            | Completed          |
| Production ZIP package               | Generated          |
| Live PHP runtime validation          | Pending            |
| Live MySQL runtime validation        | Pending            |
| Full XAMPP integration testing       | Pending            |
| Full InfinityFree deployment testing | Pending            |
| Full Hostinger deployment testing    | Pending            |

> **Important:** Static verification and successful frontend compilation do not replace live PHP/MySQL integration testing. Before production use, the system should be deployed to a real PHP/MySQL environment and all major workflows should be tested.

---

# 🧭 Development Roadmap

The major development phases completed so far are:

```text
Phase 1  — Frontend Architecture & Decoupling
Phase 2  — PHP MVC Backend Foundation
Phase 3  — Production Database Architecture
Phase 4  — Authentication & RBAC
Phase 5  — Frontend ↔ PHP API Integration
Phase 6A — Academic Structure & Enrollment
Phase 6B — Learning Content & Course Delivery
Phase 6C — Assessments, Quizzes & Grading
Phase 6D — Attendance & Practical Tracking
Phase 6E — Communication & Notifications
Phase 7  — Administrative & Financial Modules
Phase 8  — Security Hardening & Verification
Phase 9  — Production Conversion & Shared Hosting Deployment
```

The current production architecture is now focused on:

```text
PHP + MySQL + Apache
```

with the React/Vite application retained as the development and documentation reference layer.

---

# 📚 Project Documentation

Additional documentation includes:

```text
README.md
DEPLOYMENT.md
database/schema.sql
database/seeds.sql
```

The development application also provides interactive documentation for:

* Database schema
* Entity relationships
* PHP MVC structure
* REST API routes
* Authentication architecture
* RBAC
* Security controls
* System architecture

---

# 🔒 Production Security Notice

Before deploying the LMS for real institutional use:

* Enable HTTPS.
* Set `APP_DEBUG=false`.
* Use strong database credentials.
* Change all seeded/default passwords.
* Restrict database access.
* Protect private uploaded files.
* Verify PHP and MySQL versions.
* Review `.htaccess` configuration.
* Test all role permissions.
* Test file upload limits.
* Test session behavior.
* Test backup and recovery procedures.
* Perform a final security review.

The application should be treated as **not fully production-validated until it has been executed and tested in a real PHP/MySQL environment**.

---

# 📄 License

This project is developed for the Gilgil Technical and Vocational College Learning Management System.

Add the appropriate institutional or project-specific license before public distribution.

---

# 👨‍💻 Project Status

**Current Status: Production Architecture Complete — Runtime Deployment Validation Pending**

The GTVC LMS has completed its primary architectural and implementation phases.

The next recommended milestone is:

> **Deploy → Test → Fix → Harden → Deploy Again**

The system should first be validated on XAMPP or another PHP/MySQL environment before being deployed to a live shared-hosting environment.

---

## Built for Better Technical & Vocational Education

The goal of the GTVC LMS is to provide a secure, accessible, intuitive, and maintainable digital platform that simplifies learning, teaching, academic administration, student support, and institutional operations.

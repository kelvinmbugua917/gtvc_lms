-- =============================================================================
-- Gilgil Technical and Vocational College (GTVC) LMS
-- Production Initial Seed Data (MySQL 8.0+ / MariaDB 10.5+)
-- =============================================================================

USE `gilgil_lms`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. System Diagnostics Record
INSERT INTO `system_health` (`status`, `db_version`) VALUES ('OK', '1.0.0-gtvc-production');

-- 2. System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `category`, `description`) VALUES
('institution_name', 'Gilgil Technical and Vocational College', 'general', 'Official College Name'),
('institution_code', 'GTVC', 'general', 'Short Abbreviation'),
('institution_email', 'info@gilgiltvc.ac.ke', 'contact', 'Official Email Address'),
('current_academic_year', '2025/2026', 'academic', 'Active Academic Year Name'),
('mpesa_paybill', '400222', 'finance', 'M-Pesa Official Paybill Number'),
('allow_student_self_registration', '0', 'security', 'Disable public self-registration by default')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 3. Academic Years
INSERT INTO `academic_years` (`id`, `name`, `start_date`, `end_date`, `is_current`) VALUES
(1, '2024/2025', '2024-09-01', '2025-08-31', 0),
(2, '2025/2026', '2025-09-01', '2026-08-31', 1);

-- 4. Intakes
INSERT INTO `intakes` (`id`, `academic_year_id`, `name`, `code`, `start_date`, `end_date`, `status`) VALUES
(1, 2, 'January 2025 Intake', 'INT-2025-01', '2025-01-06', '2025-04-30', 'closed'),
(2, 2, 'May 2025 Intake', 'INT-2025-05', '2025-05-05', '2025-08-29', 'active'),
(3, 2, 'September 2025 Intake', 'INT-2025-09', '2025-09-08', '2025-12-19', 'upcoming');

-- 5. Departments
INSERT INTO `departments` (`id`, `code`, `name`, `description`) VALUES
(1, 'COMP', 'ICT and Computer Studies Department', 'Diplomas and Certificates in Information Technology, Software Dev & Cybersecurity'),
(2, 'ELEC', 'Electrical & Electronics Engineering', 'Power Engineering, Telecommunications & CBET Electrical Installation'),
(3, 'MECH', 'Mechanical & Automotive Engineering', 'Automotive Technology, Mechanical Production & Plant Engineering'),
(4, 'BUS', 'Business & Liberal Studies', 'Supply Chain Management, Business Management & Accounting'),
(5, 'HOSP', 'Hospitality & Institutional Management', 'Catering, Food & Beverage Production & Hotel Management');

-- 6. Programs
INSERT INTO `programs` (`id`, `department_id`, `code`, `name`, `award_type`, `duration_months`) VALUES
(1, 1, 'DIT-L6', 'Diploma in Information Technology (CBET Level 6)', 'cbet_level_6', 24),
(2, 1, 'DICT-L5', 'Craft Certificate in IT (CBET Level 5)', 'cbet_level_5', 12),
(3, 2, 'DEEE-L6', 'Diploma in Electrical & Electronics Engineering', 'diploma', 36),
(4, 3, 'DAUT-L6', 'Diploma in Automotive Engineering', 'diploma', 36),
(5, 4, 'DPLM-L6', 'Diploma in Supply Chain Management', 'diploma', 24);

-- 7. Program Levels
INSERT INTO `program_levels` (`id`, `program_id`, `level_number`, `name`) VALUES
(1, 1, 1, 'Level 6 - Module I'),
(2, 1, 2, 'Level 6 - Module II'),
(3, 1, 3, 'Level 6 - Module III');

-- 8. Classes / Cohorts
INSERT INTO `classes` (`id`, `program_id`, `intake_id`, `code`, `name`, `year_of_study`, `status`) VALUES
(1, 1, 2, 'GTVC-DIT-MAY2025', 'Diploma in IT - May 2025 Cohort A', 1, 'active'),
(2, 3, 2, 'GTVC-ELEC-MAY2025', 'Diploma in Electrical - May 2025 Cohort A', 1, 'active');

-- 9. Units / Courses
INSERT INTO `units` (`id`, `department_id`, `code`, `title`, `description`, `credit_hours`, `is_cbet`) VALUES
(1, 1, 'ICT2101', 'Database Management Systems', 'Relational database design, SQL queries, MySQL optimization & ACID transactions', 60, 1),
(2, 1, 'ICT2102', 'Web Application Development', 'Frontend and Backend web development using modern frameworks and standard architectures', 60, 1),
(3, 1, 'ICT2103', 'Object Oriented Programming in Java', 'Core OOP concepts, classes, inheritance, polymorphism, and exception handling', 45, 1),
(4, 2, 'ELEC1101', 'Electrical Principles & Circuit Theory', 'Direct and Alternating Current theory, Kirchhoff laws, and circuit diagnostics', 45, 1);

-- 10. Program Units Mapping
INSERT INTO `program_units` (`program_id`, `unit_id`, `program_level_id`, `is_core`) VALUES
(1, 1, 1, 1),
(1, 2, 1, 1),
(1, 3, 1, 1);

-- 11. Roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'super_admin', 'System Administrator with unrestricted access'),
(2, 'admin', 'Academic Registrar / Departmental Administrator'),
(3, 'lecturer', 'Academic Trainer / Instructor'),
(4, 'student', 'Enrolled Trainee / Student'),
(5, 'accountant', 'Finance Administrator');

-- 12. Permissions
INSERT INTO `permissions` (`id`, `name`, `module`, `description`) VALUES
(1, 'system.manage', 'system', 'Configure system settings and audit logs'),
(2, 'user.manage', 'system', 'Create, update, and manage user accounts'),
(3, 'academic.manage', 'academic', 'Manage departments, programs, and units'),
(4, 'course.create', 'learning', 'Create and publish course modules and lessons'),
(5, 'grade.submit', 'assessment', 'Enter and submit student assignment and exam marks'),
(6, 'fee.collect', 'finance', 'Issue invoices and record fee payments'),
(7, 'attendance.mark', 'attendance', 'Mark daily class and workshop attendance');

-- 13. Role-Permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7),
(2, 2), (2, 3), (2, 7),
(3, 4), (3, 5), (3, 7),
(5, 6);

-- 14. Initial Users
-- Default Passwords hashed with Argon2id / bcrypt placeholder: "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi" (password: "password")
INSERT INTO `users` (`id`, `registration_number`, `national_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `is_active`) VALUES
(1, 'ADMIN001', '22334455', 'System', 'Administrator', 'admin@gilgiltvc.ac.ke', '+254711000111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(2, 'STAFF001', '33445566', 'Dr. Peter', 'Kiprop', 'pkiprop@gilgiltvc.ac.ke', '+254722000222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(3, 'GTVC/DIT/2025/001', '44556677', 'Kevin', 'Mbugua', 'kmbugua@student.gilgiltvc.ac.ke', '+254733000333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(4, 'FIN001', '55667788', 'Mary', 'Wanjiru', 'mwanjiru@gilgiltvc.ac.ke', '+254744000444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- 15. User Roles Mapping
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 3),
(3, 4),
(4, 5);

-- 16. Staff Profiles
INSERT INTO `staff_profiles` (`id`, `user_id`, `staff_number`, `designation`, `qualification`, `employment_type`) VALUES
(1, 2, 'STF/COMP/001', 'Senior Lecturer - ICT', 'MSc. Computer Science', 'full_time');

-- 17. Student Profiles
INSERT INTO `student_profiles` (`id`, `user_id`, `index_number`, `gender`, `date_of_birth`, `address`, `guardian_name`, `guardian_phone`, `cbet_reg_no`) VALUES
(1, 3, 'GTVC/2025/1042', 'male', '2002-05-14', 'P.O. Box 45 Gilgil', 'John Mbugua', '+254720111222', 'CDACC/ICT/6/2025/009');

-- 18. Student Enrollment
INSERT INTO `student_enrollments` (`id`, `student_id`, `class_id`, `program_id`, `intake_id`, `enrollment_date`, `status`) VALUES
(1, 1, 1, 1, 2, '2025-05-05', 'active');

-- 19. Course Offerings
INSERT INTO `course_offerings` (`id`, `unit_id`, `class_id`, `academic_year_id`, `primary_lecturer_id`, `status`) VALUES
(1, 1, 1, 2, 1, 'ongoing'),
(2, 2, 1, 2, 1, 'ongoing');

-- 20. Fee Structure & Student Fee Account
INSERT INTO `fee_structures` (`id`, `program_id`, `academic_year_id`, `intake_id`, `term_semester`, `total_amount`, `description`) VALUES
(1, 1, 2, 2, 1, 16420.00, 'May 2025 Term 1 Tuition & Activity Fees');

INSERT INTO `student_fee_accounts` (`id`, `student_id`, `total_billed`, `total_paid`, `current_balance`, `clearance_status`) VALUES
(1, 1, 16420.00, 10000.00, 6420.00, 'pending');

INSERT INTO `invoices` (`id`, `invoice_number`, `student_id`, `fee_structure_id`, `amount`, `due_date`, `status`) VALUES
(1, 'INV-2025-0001', 1, 1, 16420.00, '2025-05-30', 'partially_paid');

INSERT INTO `payments` (`id`, `transaction_reference`, `student_id`, `invoice_id`, `amount`, `payment_method`, `payment_date`, `status`) VALUES
(1, 'RKT9823412', 1, 1, 10000.00, 'mpesa', '2025-05-10 10:30:00', 'verified');

SET FOREIGN_KEY_CHECKS = 1;

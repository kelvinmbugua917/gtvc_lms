-- =============================================================================
-- Gilgil Technical and Vocational College (GTVC) LMS
-- Production Database Schema Architecture (MySQL 8.0+ / MariaDB 10.5+)
-- Standardized to InnoDB, UTF8MB4, Strict FK Constraints & Normalized Entities
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `gilgil_lms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `gilgil_lms`;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. SYSTEM DIAGNOSTICS & GLOBAL SETTINGS
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `system_health`;

CREATE TABLE `system_health` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `status` VARCHAR(32) NOT NULL DEFAULT 'OK',
    `db_version` VARCHAR(128) NOT NULL,
    `checked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(64) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `category` VARCHAR(32) NOT NULL DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. INSTITUTIONAL & ACADEMIC HIERARCHY
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `course_offerings`;
DROP TABLE IF EXISTS `program_units`;
DROP TABLE IF EXISTS `units`;
DROP TABLE IF EXISTS `classes`;
DROP TABLE IF EXISTS `program_levels`;
DROP TABLE IF EXISTS `programs`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `intakes`;
DROP TABLE IF EXISTS `academic_years`;

CREATE TABLE `academic_years` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(32) NOT NULL UNIQUE, -- e.g. "2024/2025", "2025/2026"
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_current` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `intakes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year_id` INT NOT NULL,
    `name` VARCHAR(64) NOT NULL, -- e.g. "January 2025 Intake", "May 2025 Intake", "September 2025 Intake"
    `code` VARCHAR(32) NOT NULL UNIQUE, -- e.g. "INT-2025-05"
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('upcoming', 'active', 'closed') NOT NULL DEFAULT 'upcoming',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_intakes_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(32) NOT NULL UNIQUE, -- e.g. "COMP", "MECH", "ELEC", "BUS", "HOSP", "AGRIC"
    `name` VARCHAR(128) NOT NULL,
    `description` TEXT NULL,
    `head_of_department_id` BIGINT NULL, -- Handled after users table is created
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `programs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `code` VARCHAR(32) NOT NULL UNIQUE, -- e.g. "DIT", "DICT", "CBET-EE-L6", "CRAFT-AUTO"
    `name` VARCHAR(128) NOT NULL,
    `award_type` ENUM('diploma', 'craft_certificate', 'artisan', 'cbet_level_5', 'cbet_level_6', 'short_course') NOT NULL,
    `duration_months` INT NOT NULL DEFAULT 24,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_programs_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `program_levels` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `level_number` INT NOT NULL, -- 1, 2, 3...
    `name` VARCHAR(64) NOT NULL, -- e.g. "Level 6 - Module I", "Year 1 Semester 1"
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_program_levels_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `classes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `intake_id` INT NOT NULL,
    `code` VARCHAR(64) NOT NULL UNIQUE, -- e.g. "GTVC-DIT-2025A"
    `name` VARCHAR(128) NOT NULL,
    `year_of_study` INT NOT NULL DEFAULT 1,
    `status` ENUM('active', 'completed', 'archived') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_classes_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_classes_intake` FOREIGN KEY (`intake_id`) REFERENCES `intakes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `code` VARCHAR(32) NOT NULL UNIQUE, -- e.g. "ICT2101", "MECH1102"
    `title` VARCHAR(128) NOT NULL,
    `description` TEXT NULL,
    `credit_hours` INT NOT NULL DEFAULT 45,
    `is_cbet` TINYINT(1) NOT NULL DEFAULT 1, -- TVET Competency-Based Education & Training
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_units_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `program_units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `unit_id` INT NOT NULL,
    `program_level_id` INT NULL,
    `is_core` TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY `uk_program_unit` (`program_id`, `unit_id`),
    CONSTRAINT `fk_program_units_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_program_units_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_program_units_level` FOREIGN KEY (`program_level_id`) REFERENCES `program_levels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. IDENTITY, ROLES & PERMISSIONS (RBAC)
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `user_department_assignments`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `roles` (
    `id` TINYINT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(32) NOT NULL UNIQUE, -- "super_admin", "admin", "lecturer", "student", "accountant"
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(64) NOT NULL UNIQUE, -- "course.create", "grade.submit", "fee.collect"
    `module` VARCHAR(32) NOT NULL, -- "academic", "finance", "student", "system"
    `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
    `role_id` TINYINT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `registration_number` VARCHAR(64) NULL UNIQUE, -- e.g. "GTVC/DIT/2025/001" or Staff ID
    `national_id` VARCHAR(32) NULL UNIQUE,
    `first_name` VARCHAR(64) NOT NULL,
    `last_name` VARCHAR(64) NOT NULL,
    `email` VARCHAR(128) NOT NULL UNIQUE,
    `phone` VARCHAR(32) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
    `user_id` BIGINT NOT NULL,
    `role_id` TINYINT NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_department_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL,
    `department_id` INT NOT NULL,
    `is_head_of_department` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_uda_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_uda_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `departments` ADD CONSTRAINT `fk_departments_hod` FOREIGN KEY (`head_of_department_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- -----------------------------------------------------------------------------
-- 4. PROFILES & ENROLLMENT
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `staff_profiles`;
DROP TABLE IF EXISTS `student_enrollments`;
DROP TABLE IF EXISTS `student_profiles`;

CREATE TABLE `student_profiles` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL UNIQUE,
    `index_number` VARCHAR(64) NULL,
    `gender` ENUM('male', 'female', 'other') NOT NULL,
    `date_of_birth` DATE NULL,
    `address` VARCHAR(255) NULL,
    `guardian_name` VARCHAR(128) NULL,
    `guardian_phone` VARCHAR(32) NULL,
    `cbet_reg_no` VARCHAR(64) NULL, -- TVET CDACC Registration
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_enrollments` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `student_id` BIGINT NOT NULL, -- FK to student_profiles
    `class_id` INT NOT NULL,
    `program_id` INT NOT NULL,
    `intake_id` INT NOT NULL,
    `enrollment_date` DATE NOT NULL,
    `status` ENUM('active', 'suspended', 'deferred', 'graduated', 'discontinued') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_enrollment_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollment_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_enrollment_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_enrollment_intake` FOREIGN KEY (`intake_id`) REFERENCES `intakes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `staff_profiles` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL UNIQUE,
    `staff_number` VARCHAR(64) NOT NULL UNIQUE,
    `designation` VARCHAR(128) NOT NULL, -- e.g. "Senior Trainer", "HOD", "Registrar"
    `qualification` VARCHAR(255) NULL,
    `employment_type` ENUM('full_time', 'part_time', 'contract') NOT NULL DEFAULT 'full_time',
    `joining_date` DATE NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. COURSE OFFERINGS & TEACHING ASSIGNMENTS
-- -----------------------------------------------------------------------------

CREATE TABLE `course_offerings` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `unit_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `academic_year_id` INT NOT NULL,
    `primary_lecturer_id` BIGINT NULL, -- FK to staff_profiles
    `status` ENUM('planned', 'ongoing', 'completed') NOT NULL DEFAULT 'ongoing',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_course_offering` (`unit_id`, `class_id`, `academic_year_id`),
    CONSTRAINT `fk_co_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_co_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_co_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_co_lecturer` FOREIGN KEY (`primary_lecturer_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. LEARNING CONTENT & PROGRESS
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `student_lesson_progress`;
DROP TABLE IF EXISTS `learning_materials`;
DROP TABLE IF EXISTS `lessons`;
DROP TABLE IF EXISTS `course_modules`;

CREATE TABLE `course_modules` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `course_offering_id` BIGINT NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `description` TEXT NULL,
    `sequence_order` INT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_module_co` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lessons` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `module_id` BIGINT NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `content_type` ENUM('text', 'video', 'document', 'quiz', 'assignment') NOT NULL DEFAULT 'text',
    `text_content` LONGTEXT NULL,
    `duration_minutes` INT NOT NULL DEFAULT 30,
    `sequence_order` INT NOT NULL DEFAULT 1,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lesson_module` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `learning_materials` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `lesson_id` BIGINT NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(64) NOT NULL, -- "application/pdf", "video/mp4"
    `file_size_bytes` BIGINT NULL,
    `external_url` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lm_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_lesson_progress` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `student_id` BIGINT NOT NULL, -- FK to student_profiles
    `lesson_id` BIGINT NOT NULL,
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `time_spent_seconds` INT NOT NULL DEFAULT 0,
    `completed_at` DATETIME NULL,
    UNIQUE KEY `uk_student_lesson` (`student_id`, `lesson_id`),
    CONSTRAINT `fk_slp_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_slp_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. ASSESSMENTS, QUIZZES, EXAMS & GRADES
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `student_course_grades`;
DROP TABLE IF EXISTS `quiz_responses`;
DROP TABLE IF EXISTS `quiz_attempts`;
DROP TABLE IF EXISTS `quiz_options`;
DROP TABLE IF EXISTS `quiz_questions`;
DROP TABLE IF EXISTS `quizzes`;
DROP TABLE IF EXISTS `assignment_submissions`;
DROP TABLE IF EXISTS `assignments`;

CREATE TABLE `assignments` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `course_offering_id` BIGINT NOT NULL,
    `created_by_staff_id` BIGINT NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `description` TEXT NULL,
    `total_points` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    `due_date` DATETIME NOT NULL,
    `allow_late_submission` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_assign_co` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_assign_staff` FOREIGN KEY (`created_by_staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `assignment_submissions` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` BIGINT NOT NULL,
    `student_id` BIGINT NOT NULL,
    `submission_text` TEXT NULL,
    `file_path` VARCHAR(255) NULL,
    `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `grade_points` DECIMAL(5,2) NULL,
    `feedback` TEXT NULL,
    `graded_by_staff_id` BIGINT NULL,
    `graded_at` DATETIME NULL,
    `status` ENUM('submitted', 'graded', 'resubmission_requested') NOT NULL DEFAULT 'submitted',
    UNIQUE KEY `uk_assign_student` (`assignment_id`, `student_id`),
    CONSTRAINT `fk_sub_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_staff` FOREIGN KEY (`graded_by_staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quizzes` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `course_offering_id` BIGINT NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `description` TEXT NULL,
    `time_limit_minutes` INT NOT NULL DEFAULT 30,
    `passing_score_percent` DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    `max_attempts` INT NOT NULL DEFAULT 3,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_quiz_co` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_questions` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` BIGINT NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('multiple_choice', 'true_false', 'short_answer') NOT NULL DEFAULT 'multiple_choice',
    `points` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    `sequence_order` INT NOT NULL DEFAULT 1,
    CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_options` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `question_id` BIGINT NOT NULL,
    `option_text` TEXT NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT `fk_qo_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_attempts` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` BIGINT NOT NULL,
    `student_id` BIGINT NOT NULL,
    `attempt_number` INT NOT NULL DEFAULT 1,
    `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL,
    `score_achieved` DECIMAL(5,2) NULL,
    `status` ENUM('in_progress', 'submitted', 'graded', 'timed_out') NOT NULL DEFAULT 'in_progress',
    CONSTRAINT `fk_qa_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_qa_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_responses` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` BIGINT NOT NULL,
    `question_id` BIGINT NOT NULL,
    `selected_option_id` BIGINT NULL,
    `text_response` TEXT NULL,
    `is_correct` TINYINT(1) NULL,
    `score_awarded` DECIMAL(5,2) NULL,
    CONSTRAINT `fk_qr_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_qr_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_qr_option` FOREIGN KEY (`selected_option_id`) REFERENCES `quiz_options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_course_grades` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `student_id` BIGINT NOT NULL,
    `course_offering_id` BIGINT NOT NULL,
    `coursework_score` DECIMAL(5,2) NULL DEFAULT 0.00,
    `exam_score` DECIMAL(5,2) NULL DEFAULT 0.00,
    `final_score` DECIMAL(5,2) NULL DEFAULT 0.00,
    `letter_grade` VARCHAR(4) NULL, -- "A", "B", "C", "D", "F", "PASS", "CREDIT", "DISTINCTION"
    `cbet_status` ENUM('competent', 'not_yet_competent', 'pending') NOT NULL DEFAULT 'pending',
    `graded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_student_course_grade` (`student_id`, `course_offering_id`),
    CONSTRAINT `fk_scg_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_scg_co` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. ATTENDANCE TRACKING
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `attendance_records`;
DROP TABLE IF EXISTS `attendance_sessions`;

CREATE TABLE `attendance_sessions` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `course_offering_id` BIGINT NOT NULL,
    `lecturer_id` BIGINT NOT NULL,
    `session_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `session_type` ENUM('theory', 'practical_workshop', 'lab', 'exam') NOT NULL DEFAULT 'theory',
    `topic_covered` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_attses_co` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attses_staff` FOREIGN KEY (`lecturer_id`) REFERENCES `staff_profiles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance_records` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `session_id` BIGINT NOT NULL,
    `student_id` BIGINT NOT NULL,
    `status` ENUM('present', 'absent', 'excused', 'late') NOT NULL DEFAULT 'present',
    `remarks` VARCHAR(255) NULL,
    UNIQUE KEY `uk_attendance_session_student` (`session_id`, `student_id`),
    CONSTRAINT `fk_attr_session` FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_attr_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. ANNOUNCEMENTS & NOTIFICATIONS
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `announcements`;

CREATE TABLE `announcements` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(128) NOT NULL,
    `content` TEXT NOT NULL,
    `author_id` BIGINT NOT NULL, -- FK to users
    `target_role_id` TINYINT NULL,
    `target_department_id` INT NULL,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_anc_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_anc_role` FOREIGN KEY (`target_role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_anc_dept` FOREIGN KEY (`target_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NOT NULL,
    `title` VARCHAR(128) NOT NULL,
    `message` TEXT NOT NULL,
    `type` VARCHAR(32) NOT NULL DEFAULT 'general', -- "assignment", "grade", "fee", "system"
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `link_url` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. FINANCE, FEES & CLEARANCE
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `student_fee_accounts`;
DROP TABLE IF EXISTS `fee_structures`;

CREATE TABLE `fee_structures` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT NOT NULL,
    `academic_year_id` INT NOT NULL,
    `intake_id` INT NOT NULL,
    `term_semester` INT NOT NULL DEFAULT 1,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_fs_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_fs_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_fs_intake` FOREIGN KEY (`intake_id`) REFERENCES `intakes` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student_fee_accounts` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `student_id` BIGINT NOT NULL UNIQUE,
    `total_billed` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `current_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `clearance_status` ENUM('cleared', 'pending', 'blocked_exam') NOT NULL DEFAULT 'pending',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sfa_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `invoices` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(64) NOT NULL UNIQUE, -- e.g. "INV-2025-0089"
    `student_id` BIGINT NOT NULL,
    `fee_structure_id` INT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `due_date` DATE NOT NULL,
    `status` ENUM('unpaid', 'partially_paid', 'paid', 'cancelled') NOT NULL DEFAULT 'unpaid',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_inv_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inv_fs` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `transaction_reference` VARCHAR(64) NOT NULL UNIQUE, -- e.g. M-Pesa Code "SDF98324JK"
    `student_id` BIGINT NOT NULL,
    `invoice_id` BIGINT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` ENUM('mpesa', 'bank_transfer', 'cheque', 'cash') NOT NULL DEFAULT 'mpesa',
    `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `verified_by_user_id` BIGINT NULL,
    `status` ENUM('pending', 'verified', 'rejected') NOT NULL DEFAULT 'verified',
    CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pay_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pay_verifier` FOREIGN KEY (`verified_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. AUDIT TRAIL
-- -----------------------------------------------------------------------------

CREATE TABLE `audit_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT NULL,
    `action` VARCHAR(128) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `details_json` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable Foreign Key Checks
SET FOREIGN_KEY_CHECKS = 1;

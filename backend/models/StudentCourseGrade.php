<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class StudentCourseGrade extends Model
{
    /**
     * Get grades for a course offering
     */
    public static function getGradesByOffering(int $offeringId, bool $publishedOnly = false): array
    {
        $sql = "SELECT scg.id, scg.student_id, scg.course_offering_id, scg.coursework_score,
                       scg.exam_score, scg.total_score, scg.letter_grade, scg.competency_outcome,
                       scg.is_published, scg.published_at, scg.updated_at,
                       u.full_name AS student_name, u.email AS student_email,
                       sp.index_number
                FROM student_course_grades scg
                JOIN users u ON u.id = scg.student_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE scg.course_offering_id = :offering_id";

        if ($publishedOnly) {
            $sql .= " AND scg.is_published = 1";
        }

        $sql .= " ORDER BY u.full_name ASC";

        return self::fetchAll($sql, ['offering_id' => $offeringId]);
    }

    /**
     * Get student's published course grade
     */
    public static function getStudentGradeForOffering(int $offeringId, int $studentId): ?array
    {
        $sql = "SELECT scg.id, scg.student_id, scg.course_offering_id, scg.coursework_score,
                       scg.exam_score, scg.total_score, scg.letter_grade, scg.competency_outcome,
                       scg.is_published, scg.published_at,
                       co.unit_id, u.title AS unit_title, u.code AS unit_code,
                       c.name AS class_name
                FROM student_course_grades scg
                JOIN course_offerings co ON co.id = scg.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = co.class_id
                WHERE scg.course_offering_id = :offering_id AND scg.student_id = :student_id";

        return self::fetchOne($sql, [
            'offering_id' => $offeringId,
            'student_id' => $studentId
        ]);
    }

    /**
     * Get all published grades for a student
     */
    public static function getAllStudentGrades(int $studentId): array
    {
        $sql = "SELECT scg.id, scg.student_id, scg.course_offering_id, scg.coursework_score,
                       scg.exam_score, scg.total_score, scg.letter_grade, scg.competency_outcome,
                       scg.is_published, scg.published_at,
                       u.title AS unit_title, u.code AS unit_code,
                       c.name AS class_name, ay.year_label
                FROM student_course_grades scg
                JOIN course_offerings co ON co.id = scg.course_offering_id
                JOIN units u ON u.id = co.unit_id
                JOIN classes c ON c.id = co.class_id
                JOIN academic_years ay ON ay.id = co.academic_year_id
                WHERE scg.student_id = :student_id AND scg.is_published = 1
                ORDER BY ay.year_label DESC, u.code ASC";

        return self::fetchAll($sql, ['student_id' => $studentId]);
    }

    /**
     * Upsert student grade
     */
    public static function saveStudentGrade(array $data): bool
    {
        $totalScore = (float)($data['coursework_score'] ?? 0) + (float)($data['exam_score'] ?? 0);
        
        // Letter grade & TVET CBET outcome calculation
        $letterGrade = self::calculateLetterGrade($totalScore);
        $competencyOutcome = $totalScore >= 50.0 ? 'Competent' : 'Not Yet Competent';

        $sql = "INSERT INTO student_course_grades 
                (student_id, course_offering_id, coursework_score, exam_score, total_score, letter_grade, competency_outcome, is_published)
                VALUES 
                (:student_id, :course_offering_id, :coursework_score, :exam_score, :total_score, :letter_grade, :competency_outcome, :is_published)
                ON DUPLICATE KEY UPDATE
                    coursework_score = VALUES(coursework_score),
                    exam_score = VALUES(exam_score),
                    total_score = VALUES(total_score),
                    letter_grade = VALUES(letter_grade),
                    competency_outcome = VALUES(competency_outcome),
                    updated_at = CURRENT_TIMESTAMP";

        self::execute($sql, [
            'student_id' => $data['student_id'],
            'course_offering_id' => $data['course_offering_id'],
            'coursework_score' => (float)($data['coursework_score'] ?? 0.0),
            'exam_score' => (float)($data['exam_score'] ?? 0.0),
            'total_score' => $totalScore,
            'letter_grade' => $letterGrade,
            'competency_outcome' => $competencyOutcome,
            'is_published' => isset($data['is_published']) ? (int)$data['is_published'] : 0
        ]);

        return true;
    }

    /**
     * Publish all grades for a course offering
     */
    public static function publishGradesForOffering(int $offeringId): bool
    {
        $sql = "UPDATE student_course_grades
                SET is_published = 1,
                    published_at = CURRENT_TIMESTAMP
                WHERE course_offering_id = :offering_id";

        self::execute($sql, ['offering_id' => $offeringId]);
        return true;
    }

    /**
     * Helper for TVET letter grade calculation
     */
    public static function calculateLetterGrade(float $totalScore): string
    {
        if ($totalScore >= 80.0) return 'A';
        if ($totalScore >= 70.0) return 'B';
        if ($totalScore >= 60.0) return 'C';
        if ($totalScore >= 50.0) return 'D';
        return 'F';
    }
}

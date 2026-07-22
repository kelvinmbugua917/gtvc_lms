<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class QuizQuestion extends Model
{
    /**
     * Get all questions for a quiz (including options if requested)
     */
    public static function getQuestionsByQuiz(int $quizId, bool $includeCorrectAnswers = true): array
    {
        $sql = "SELECT qq.id, qq.quiz_id, qq.question_text, qq.question_type, qq.marks, qq.sequence_order, qq.created_at
                FROM quiz_questions qq
                WHERE qq.quiz_id = :quiz_id
                ORDER BY qq.sequence_order ASC, qq.id ASC";

        $questions = self::fetchAll($sql, ['quiz_id' => $quizId]);

        foreach ($questions as &$q) {
            $q['options'] = QuizOption::getOptionsByQuestion((int)$q['id'], $includeCorrectAnswers);
        }

        return $questions;
    }

    /**
     * Get single question
     */
    public static function getQuestionById(int $id): ?array
    {
        $sql = "SELECT qq.id, qq.quiz_id, qq.question_text, qq.question_type, qq.marks, qq.sequence_order, qq.created_at,
                       q.course_offering_id, co.primary_lecturer_id, p.department_id
                FROM quiz_questions qq
                JOIN quizzes q ON q.id = qq.quiz_id
                JOIN course_offerings co ON co.id = q.course_offering_id
                JOIN classes c ON c.id = co.class_id
                JOIN programs p ON p.id = c.program_id
                WHERE qq.id = :id";

        return self::fetchOne($sql, ['id' => $id]);
    }

    /**
     * Create question
     */
    public static function createQuestion(array $data): int
    {
        $sql = "INSERT INTO quiz_questions (quiz_id, question_text, question_type, marks, sequence_order)
                VALUES (:quiz_id, :question_text, :question_type, :marks, :sequence_order)";

        return self::execute($sql, [
            'quiz_id' => $data['quiz_id'],
            'question_text' => trim($data['question_text']),
            'question_type' => $data['question_type'] ?? 'multiple_choice',
            'marks' => isset($data['marks']) ? (float)$data['marks'] : 1.0,
            'sequence_order' => isset($data['sequence_order']) ? (int)$data['sequence_order'] : 1
        ]);
    }

    /**
     * Update question
     */
    public static function updateQuestion(int $id, array $data): bool
    {
        $sql = "UPDATE quiz_questions 
                SET question_text = :question_text,
                    question_type = :question_type,
                    marks = :marks,
                    sequence_order = :sequence_order
                WHERE id = :id";

        self::execute($sql, [
            'id' => $id,
            'question_text' => trim($data['question_text']),
            'question_type' => $data['question_type'] ?? 'multiple_choice',
            'marks' => (float)($data['marks'] ?? 1.0),
            'sequence_order' => (int)($data['sequence_order'] ?? 1)
        ]);

        return true;
    }

    /**
     * Delete question
     */
    public static function deleteQuestion(int $id): bool
    {
        $sql = "DELETE FROM quiz_questions WHERE id = :id";
        self::execute($sql, ['id' => $id]);
        return true;
    }
}

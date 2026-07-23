<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class QuizResponse extends Model
{
    /**
     * Get responses for an attempt
     */
    public static function getResponsesByAttempt(int $attemptId): array
    {
        $sql = "SELECT qr.id, qr.quiz_attempt_id, qr.question_id, qr.selected_option_id,
                       qr.text_response, qr.marks_awarded, qr.is_correct,
                       qq.question_text, qq.question_type, qq.marks AS question_max_marks,
                       qo.option_text AS selected_option_text
                FROM quiz_responses qr
                JOIN quiz_questions qq ON qq.id = qr.question_id
                LEFT JOIN quiz_options qo ON qo.id = qr.selected_option_id
                WHERE qr.quiz_attempt_id = :attempt_id
                ORDER BY qq.sequence_order ASC";

        return self::fetchAll($sql, ['attempt_id' => $attemptId]);
    }

    /**
     * Record response for a question during quiz attempt
     */
    public static function recordResponse(int $attemptId, int $questionId, ?int $selectedOptionId, ?string $textResponse, float $marksAwarded, int $isCorrect): bool
    {
        // Upsert response
        $sql = "INSERT INTO quiz_responses (quiz_attempt_id, question_id, selected_option_id, text_response, marks_awarded, is_correct)
                VALUES (:attempt_id, :question_id, :selected_option_id, :text_response, :marks_awarded, :is_correct)
                ON DUPLICATE KEY UPDATE
                    selected_option_id = VALUES(selected_option_id),
                    text_response = VALUES(text_response),
                    marks_awarded = VALUES(marks_awarded),
                    is_correct = VALUES(is_correct)";

        self::execute($sql, [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'text_response' => $textResponse,
            'marks_awarded' => $marksAwarded,
            'is_correct' => $isCorrect
        ]);

        return true;
    }
}

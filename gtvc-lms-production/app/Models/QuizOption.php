<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class QuizOption extends Model
{
    /**
     * Get options for a question
     */
    public static function getOptionsByQuestion(int $questionId, bool $includeIsCorrect = true): array
    {
        if ($includeIsCorrect) {
            $sql = "SELECT id, question_id, option_text, is_correct, sequence_order
                    FROM quiz_options
                    WHERE question_id = :question_id
                    ORDER BY sequence_order ASC, id ASC";
        } else {
            // Hide correct answer key from students taking active quiz
            $sql = "SELECT id, question_id, option_text, sequence_order
                    FROM quiz_options
                    WHERE question_id = :question_id
                    ORDER BY sequence_order ASC, id ASC";
        }

        return self::fetchAll($sql, ['question_id' => $questionId]);
    }

    /**
     * Create option
     */
    public static function createOption(array $data): int
    {
        $sql = "INSERT INTO quiz_options (question_id, option_text, is_correct, sequence_order)
                VALUES (:question_id, :option_text, :is_correct, :sequence_order)";

        return self::execute($sql, [
            'question_id' => $data['question_id'],
            'option_text' => trim($data['option_text']),
            'is_correct' => isset($data['is_correct']) ? (int)$data['is_correct'] : 0,
            'sequence_order' => isset($data['sequence_order']) ? (int)$data['sequence_order'] : 1
        ]);
    }

    /**
     * Delete options for question
     */
    public static function deleteOptionsByQuestion(int $questionId): bool
    {
        $sql = "DELETE FROM quiz_options WHERE question_id = :question_id";
        self::execute($sql, ['question_id' => $questionId]);
        return true;
    }
}

import { apiRequest } from "./client";

export interface QuizOptionRecord {
  id: number;
  question_id: number;
  option_text: string;
  is_correct?: number; // Omitted when student is actively taking quiz
  sequence_order: number;
}

export interface QuizQuestionRecord {
  id: number;
  quiz_id: number;
  question_text: string;
  question_type: "multiple_choice" | "true_false" | "short_answer";
  marks: number;
  sequence_order: number;
  created_at?: string;
  options?: QuizOptionRecord[];
}

export interface QuizRecord {
  id: number;
  course_offering_id: number;
  title: string;
  description: string | null;
  instructions: string | null;
  time_limit_minutes: number;
  passing_percentage: number;
  max_attempts: number;
  is_published: number;
  available_from: string | null;
  available_until: string | null;
  created_at: string;
  question_count?: number;
  total_marks?: number;
  attempts_taken?: number;
  questions?: QuizQuestionRecord[];
  my_attempts?: QuizAttemptRecord[];
  unit_title?: string;
  unit_code?: string;
  class_name?: string;
}

export interface QuizAttemptRecord {
  id: number;
  quiz_id: number;
  student_id: number;
  attempt_number: number;
  started_at: string;
  submitted_at: string | null;
  score_achieved: number | null;
  total_possible_marks: number | null;
  percentage_score: number | null;
  is_passed: number | null;
  status: "in_progress" | "submitted" | "expired";
  quiz_title?: string;
  questions?: QuizQuestionRecord[];
  responses?: QuizResponseRecord[];
}

export interface QuizResponseRecord {
  id: number;
  quiz_attempt_id: number;
  question_id: number;
  selected_option_id: number | null;
  text_response: string | null;
  marks_awarded: number;
  is_correct: number;
  question_text?: string;
  question_type?: string;
  question_max_marks?: number;
  selected_option_text?: string;
}

export interface CreateQuizPayload {
  title: string;
  description?: string;
  instructions?: string;
  time_limit_minutes?: number;
  passing_percentage?: number;
  max_attempts?: number;
  is_published?: number;
  available_from?: string;
  available_until?: string;
}

export interface CreateQuestionPayload {
  question_text: string;
  question_type: "multiple_choice" | "true_false" | "short_answer";
  marks: number;
  sequence_order?: number;
  options?: Array<{ option_text: string; is_correct: boolean }>;
}

export async function getQuizzesByOfferingApi(offeringId: number): Promise<QuizRecord[]> {
  try {
    const res = await apiRequest<QuizRecord[]>(`/api/v1/course-offerings/${offeringId}/quizzes`);
    return res.data;
  } catch (err) {
    console.warn("API fallback for quizzes");
    return [
      {
        id: 601,
        course_offering_id: offeringId,
        title: "Module 1 Safety & PPE Checkpoint Quiz",
        description: "10-minute automated knowledge check on workshop safety codes and hazardous material storage.",
        instructions: "Read each question carefully. Select the best answer for multiple choice items.",
        time_limit_minutes: 15,
        passing_percentage: 60,
        max_attempts: 2,
        is_published: 1,
        available_from: new Date(Date.now() - 86400000 * 3).toISOString(),
        available_until: new Date(Date.now() + 86400000 * 10).toISOString(),
        created_at: new Date().toISOString(),
        question_count: 3,
        total_marks: 30,
        attempts_taken: 1
      }
    ];
  }
}

export async function getQuizDetailsApi(id: number): Promise<QuizRecord | null> {
  try {
    const res = await apiRequest<QuizRecord>(`/api/v1/quizzes/${id}`);
    return res.data;
  } catch (err) {
    return null;
  }
}

export async function createQuizApi(offeringId: number, payload: CreateQuizPayload): Promise<{ id: number; message: string }> {
  const res = await apiRequest<{ id: number }>(`/api/v1/course-offerings/${offeringId}/quizzes`, {
    method: "POST",
    body: JSON.stringify(payload)
  });
  return { id: res.data?.id || 0, message: res.message };
}

export async function addQuestionToQuizApi(quizId: number, payload: CreateQuestionPayload): Promise<{ id: number; message: string }> {
  const res = await apiRequest<{ id: number }>(`/api/v1/quizzes/${quizId}/questions`, {
    method: "POST",
    body: JSON.stringify(payload)
  });
  return { id: res.data?.id || 0, message: res.message };
}

export async function startQuizAttemptApi(quizId: number): Promise<QuizAttemptRecord> {
  const res = await apiRequest<QuizAttemptRecord>(`/api/v1/quizzes/${quizId}/start`, {
    method: "POST"
  });
  return res.data;
}

export async function submitQuizAttemptApi(attemptId: number, responses: Array<{ question_id: number; selected_option_id?: number; text_response?: string }>): Promise<QuizAttemptRecord> {
  const res = await apiRequest<QuizAttemptRecord>(`/api/v1/quiz-attempts/${attemptId}/submit`, {
    method: "POST",
    body: JSON.stringify({ responses })
  });
  return res.data;
}

export async function getQuizAttemptDetailsApi(attemptId: number): Promise<QuizAttemptRecord | null> {
  try {
    const res = await apiRequest<QuizAttemptRecord>(`/api/v1/quiz-attempts/${attemptId}`);
    return res.data;
  } catch (err) {
    return null;
  }
}

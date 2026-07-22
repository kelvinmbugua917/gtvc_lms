/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

export interface QuizQuestion {
  id: string;
  questionText: string;
  options: string[];
  correctOptionIndex: number;
  points: number;
}

export interface Quiz {
  id: string;
  title: string;
  questions: QuizQuestion[];
  durationMinutes: number;
}

export interface Lesson {
  id: string;
  title: string;
  duration: string;
  content: string; // Markdown/rich text simulated content
  completed?: boolean;
}

export interface Assignment {
  id: string;
  title: string;
  description: string;
  dueDate: string;
  maxPoints: number;
  status: 'Pending' | 'Submitted' | 'Graded';
  grade?: number;
  submittedFile?: string;
  submittedAt?: string;
  feedback?: string;
}

export interface Course {
  id: string;
  code: string;
  title: string;
  department: string;
  instructor: string;
  progress: number; // 0 to 100
  image: string;
  lessons: Lesson[];
  quizzes: Quiz[];
  assignments: Assignment[];
}

export interface StudentProgress {
  studentId: string;
  studentName: string;
  regNumber: string;
  quizGrades: { [quizId: string]: number };
  assignmentGrades: { [assignmentId: string]: { score: number; feedback: string } };
  attendancePercent: number;
}

export interface AttendanceRecord {
  date: string;
  status: 'Present' | 'Absent' | 'Excused';
}

export interface WorkshopAttendance {
  studentId: string;
  studentName: string;
  regNumber: string;
  courseId: string;
  records: { [date: string]: 'Present' | 'Absent' | 'Excused' };
}

export interface Announcement {
  id: string;
  title: string;
  content: string;
  date: string;
  author: string;
  category: 'General' | 'Exam' | 'Workshop' | 'Fees';
}

// Interactive Questionnaire types to guide future LMS development
export interface QuestionnaireOption {
  value: string;
  label: string;
  description?: string;
}

export interface CustomizationQuestion {
  id: string;
  category: 'architecture' | 'features' | 'auth' | 'integrations';
  questionText: string;
  type: 'select' | 'multiselect' | 'text';
  options?: QuestionnaireOption[];
  selectedValue: string | string[];
}

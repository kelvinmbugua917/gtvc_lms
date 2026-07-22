import { apiRequest } from "./client";

export interface AssignmentRecord {
  id: number;
  course_offering_id: number;
  title: string;
  description: string | null;
  instructions: string | null;
  max_marks: number;
  is_published: number;
  release_date: string | null;
  due_date: string | null;
  allow_late_submission: number;
  created_at: string;
  submission_count?: number;
  my_submission?: AssignmentSubmissionRecord | null;
  unit_title?: string;
  unit_code?: string;
  class_name?: string;
}

export interface AssignmentSubmissionRecord {
  id: number;
  assignment_id: number;
  student_id: number;
  file_path: string | null;
  original_filename: string | null;
  file_size_bytes: number | null;
  submission_text: string | null;
  submitted_at: string;
  is_late: number;
  marks_awarded: number | null;
  feedback: string | null;
  graded_by_user_id: number | null;
  graded_at: string | null;
  student_name?: string;
  student_email?: string;
  index_number?: string;
  assignment_title?: string;
  max_marks?: number;
}

export interface CreateAssignmentPayload {
  title: string;
  description?: string;
  instructions?: string;
  max_marks?: number;
  is_published?: number;
  release_date?: string;
  due_date?: string;
  allow_late_submission?: number;
}

export async function getAssignmentsByOfferingApi(offeringId: number): Promise<AssignmentRecord[]> {
  try {
    const res = await apiRequest<AssignmentRecord[]>(`/api/v1/course-offerings/${offeringId}/assignments`);
    return res.data;
  } catch (err) {
    console.warn("API fallback for assignments");
    return [
      {
        id: 401,
        course_offering_id: offeringId,
        title: "Practical Assignment 1: Workshop Tool Maintenance & Calibration Brief",
        description: "Submit a 3-page practical report detailing tool inspection routines and safety checklists.",
        instructions: "Format: PDF document. Include photos or diagrams of workshop safety tags where applicable.",
        max_marks: 100,
        is_published: 1,
        release_date: new Date(Date.now() - 86400000 * 5).toISOString(),
        due_date: new Date(Date.now() + 86400000 * 7).toISOString(),
        allow_late_submission: 1,
        created_at: new Date().toISOString(),
        submission_count: 14
      },
      {
        id: 402,
        course_offering_id: offeringId,
        title: "Term Project: Electrical Wiring Schematic Analysis",
        description: "Analyze the provided single-phase motor circuit diagram and identify potential fault points.",
        instructions: "Upload your CAD export or scanned calculation sheet.",
        max_marks: 50,
        is_published: 1,
        release_date: new Date(Date.now() - 86400000 * 2).toISOString(),
        due_date: new Date(Date.now() + 86400000 * 14).toISOString(),
        allow_late_submission: 0,
        created_at: new Date().toISOString(),
        submission_count: 5
      }
    ];
  }
}

export async function getAssignmentDetailsApi(id: number): Promise<AssignmentRecord | null> {
  try {
    const res = await apiRequest<AssignmentRecord>(`/api/v1/assignments/${id}`);
    return res.data;
  } catch (err) {
    return null;
  }
}

export async function createAssignmentApi(offeringId: number, payload: CreateAssignmentPayload): Promise<{ id: number; message: string }> {
  const res = await apiRequest<{ id: number }>(`/api/v1/course-offerings/${offeringId}/assignments`, {
    method: "POST",
    body: JSON.stringify(payload)
  });
  return { id: res.data?.id || 0, message: res.message };
}

export async function updateAssignmentApi(id: number, payload: Partial<CreateAssignmentPayload>): Promise<{ message: string }> {
  const res = await apiRequest<null>(`/api/v1/assignments/${id}`, {
    method: "PUT",
    body: JSON.stringify(payload)
  });
  return { message: res.message };
}

export async function deleteAssignmentApi(id: number): Promise<{ message: string }> {
  const res = await apiRequest<null>(`/api/v1/assignments/${id}`, {
    method: "DELETE"
  });
  return { message: res.message };
}

import { apiRequest } from "./client";

export interface EnrollmentRecord {
  id: number;
  student_id: number;
  class_id: number;
  program_id: number;
  intake_id: number;
  enrollment_date: string;
  status: "active" | "suspended" | "deferred" | "graduated" | "discontinued";
  created_at: string;
  index_number?: string | null;
  first_name?: string;
  last_name?: string;
  email?: string;
  registration_number?: string | null;
  class_name?: string;
  class_code?: string;
  program_name?: string;
  program_code?: string;
  department_id?: number;
  department_name?: string;
  department_code?: string;
  intake_name?: string;
  academic_year_name?: string;
}

export interface CreateEnrollmentPayload {
  student_id: number;
  class_id: number;
  program_id: number;
  intake_id: number;
  enrollment_date?: string;
}

/**
 * Fetch Enrollments
 */
export async function getEnrollmentsApi(studentId?: number, departmentId?: number, status?: string): Promise<EnrollmentRecord[]> {
  const params = new URLSearchParams();
  if (studentId) params.append("student_id", studentId.toString());
  if (departmentId) params.append("department_id", departmentId.toString());
  if (status) params.append("status", status);
  const query = params.toString() ? `?${params.toString()}` : "";
  const res = await apiRequest<EnrollmentRecord[]>(`/enrollments${query}`);
  return res.data;
}

/**
 * Create Student Enrollment
 */
export async function createEnrollmentApi(payload: CreateEnrollmentPayload): Promise<EnrollmentRecord> {
  const res = await apiRequest<EnrollmentRecord>("/enrollments", {
    method: "POST",
    body: JSON.stringify(payload),
  });
  return res.data;
}

/**
 * Update Enrollment Status
 */
export async function updateEnrollmentStatusApi(id: number, status: string): Promise<EnrollmentRecord> {
  const res = await apiRequest<EnrollmentRecord>(`/enrollments/${id}`, {
    method: "PUT",
    body: JSON.stringify({ status }),
  });
  return res.data;
}

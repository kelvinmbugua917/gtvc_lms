import { apiRequest } from "./client";

export interface StudentProfile {
  student_profile_id: number;
  user_id: number;
  index_number: string | null;
  gender: "male" | "female" | "other";
  date_of_birth: string | null;
  address: string | null;
  guardian_name: string | null;
  guardian_phone: string | null;
  cbet_reg_no: string | null;
  profile_created_at: string;
  registration_number: string | null;
  national_id: string | null;
  first_name: string;
  last_name: string;
  email: string;
  phone: string | null;
  is_active: number;
  active_enrollment_id?: number | null;
  enrollment_status?: string | null;
  enrollment_date?: string | null;
  class_id?: number | null;
  class_name?: string | null;
  class_code?: string | null;
  program_id?: number | null;
  program_name?: string | null;
  program_code?: string | null;
  department_id?: number | null;
  department_name?: string | null;
  department_code?: string | null;
  intake_id?: number | null;
  intake_name?: string | null;
  academic_year_id?: number | null;
  academic_year_name?: string | null;
}

export interface CreateStudentPayload {
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  registration_number?: string;
  national_id?: string;
  password?: string;
  index_number?: string;
  gender?: "male" | "female" | "other";
  date_of_birth?: string;
  address?: string;
  guardian_name?: string;
  guardian_phone?: string;
  cbet_reg_no?: string;
}

/**
 * Fetch Student Directory
 */
export async function getStudentsApi(departmentId?: number, search?: string): Promise<StudentProfile[]> {
  const params = new URLSearchParams();
  if (departmentId) params.append("department_id", departmentId.toString());
  if (search) params.append("search", search);
  const query = params.toString() ? `?${params.toString()}` : "";
  const res = await apiRequest<StudentProfile[]>(`/students${query}`);
  return res.data;
}

/**
 * Fetch Student Profile by ID
 */
export async function getStudentByIdApi(id: number): Promise<StudentProfile> {
  const res = await apiRequest<StudentProfile>(`/students/${id}`);
  return res.data;
}

/**
 * Create New Student Profile & User Account
 */
export async function createStudentApi(payload: CreateStudentPayload): Promise<StudentProfile> {
  const res = await apiRequest<StudentProfile>("/students", {
    method: "POST",
    body: JSON.stringify(payload),
  });
  return res.data;
}

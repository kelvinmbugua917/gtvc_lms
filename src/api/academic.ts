import { apiRequest } from "./client";

export interface AcademicYear {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  is_current: number;
  created_at: string;
}

export interface Intake {
  id: number;
  academic_year_id: number;
  name: string;
  code: string;
  start_date: string;
  end_date: string;
  status: "upcoming" | "active" | "closed";
  created_at: string;
  academic_year_name?: string;
}

export interface Department {
  id: number;
  code: string;
  name: string;
  description: string | null;
  head_of_department_id: number | null;
  created_at: string;
  hod_first_name?: string;
  hod_last_name?: string;
  hod_email?: string;
}

export interface Program {
  id: number;
  department_id: number;
  code: string;
  name: string;
  award_type: string;
  duration_months: number;
  created_at: string;
  department_name?: string;
  department_code?: string;
}

export interface ClassCohort {
  id: number;
  program_id: number;
  intake_id: number;
  code: string;
  name: string;
  year_of_study: number;
  status: "active" | "completed" | "archived";
  created_at: string;
  program_name?: string;
  program_code?: string;
  department_id?: number;
  department_name?: string;
  intake_name?: string;
  academic_year_name?: string;
}

export interface Unit {
  id: number;
  department_id: number;
  code: string;
  title: string;
  description: string | null;
  credit_hours: number;
  is_cbet: number;
  created_at: string;
  department_name?: string;
  is_core?: number;
  program_level_id?: number;
  program_level_name?: string;
}

export interface CourseOffering {
  id: number;
  unit_id: number;
  class_id: number;
  academic_year_id: number;
  primary_lecturer_id: number | null;
  status: "planned" | "ongoing" | "completed";
  created_at: string;
  unit_code?: string;
  unit_title?: string;
  credit_hours?: number;
  is_cbet?: number;
  department_id?: number;
  department_name?: string;
  class_name?: string;
  class_code?: string;
  program_name?: string;
  program_code?: string;
  academic_year_name?: string;
  staff_number?: string;
  lecturer_first_name?: string;
  lecturer_last_name?: string;
  lecturer_email?: string;
}

/**
 * Fetch Academic Years
 */
export async function getAcademicYearsApi(): Promise<AcademicYear[]> {
  const res = await apiRequest<AcademicYear[]>("/academic-years");
  return res.data;
}

/**
 * Fetch Intakes
 */
export async function getIntakesApi(academicYearId?: number): Promise<Intake[]> {
  const query = academicYearId ? `?academic_year_id=${academicYearId}` : "";
  const res = await apiRequest<Intake[]>(`/intakes${query}`);
  return res.data;
}

/**
 * Fetch Departments
 */
export async function getDepartmentsApi(): Promise<Department[]> {
  const res = await apiRequest<Department[]>("/departments");
  return res.data;
}

/**
 * Fetch Programs
 */
export async function getProgramsApi(departmentId?: number): Promise<Program[]> {
  const query = departmentId ? `?department_id=${departmentId}` : "";
  const res = await apiRequest<Program[]>(`/programs${query}`);
  return res.data;
}

/**
 * Fetch Classes / Cohorts
 */
export async function getClassesApi(programId?: number, intakeId?: number): Promise<ClassCohort[]> {
  const params = new URLSearchParams();
  if (programId) params.append("program_id", programId.toString());
  if (intakeId) params.append("intake_id", intakeId.toString());
  const query = params.toString() ? `?${params.toString()}` : "";
  const res = await apiRequest<ClassCohort[]>(`/classes${query}`);
  return res.data;
}

/**
 * Fetch Units
 */
export async function getUnitsApi(departmentId?: number, programId?: number): Promise<Unit[]> {
  const params = new URLSearchParams();
  if (departmentId) params.append("department_id", departmentId.toString());
  if (programId) params.append("program_id", programId.toString());
  const query = params.toString() ? `?${params.toString()}` : "";
  const res = await apiRequest<Unit[]>(`/units${query}`);
  return res.data;
}

/**
 * Fetch Course Offerings
 */
export async function getCourseOfferingsApi(classId?: number): Promise<CourseOffering[]> {
  const query = classId ? `?class_id=${classId}` : "";
  const res = await apiRequest<CourseOffering[]>(`/course-offerings${query}`);
  return res.data;
}

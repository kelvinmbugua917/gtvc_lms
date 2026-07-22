import { apiRequest } from "./client";
import { AttendanceSessionRecord, StudentAttendanceRecord, AttendanceSummary } from "./attendance";

export interface CourseAttendanceMatrix {
  sessions: AttendanceSessionRecord[];
  students: Array<{
    student_profile_id: number;
    user_id: number;
    first_name: string;
    last_name: string;
    index_number: string;
    registration_number?: string;
    summary: AttendanceSummary;
    session_statuses: Record<number, "present" | "absent" | "late" | "excused">;
  }>;
}

export interface DepartmentAttendanceReport {
  department_id: number;
  total_students: number;
  warning_count: number;
  critical_count: number;
  total_practical_hours: number;
  students: Array<{
    enrollment_id: number;
    student_profile_id: number;
    index_number: string;
    first_name: string;
    last_name: string;
    email: string;
    registration_number?: string;
    class_name: string;
    program_name: string;
    attendance_summary: AttendanceSummary;
  }>;
}

export async function getCourseAttendanceMatrixApi(offeringId: number): Promise<CourseAttendanceMatrix> {
  try {
    const res = await apiRequest<CourseAttendanceMatrix>(`/api/v1/attendance/course/${offeringId}/matrix`);
    return res.data || { sessions: [], students: [] };
  } catch (err) {
    console.warn("API fallback for course attendance matrix", err);
    return {
      sessions: [
        {
          id: 101,
          course_offering_id: offeringId,
          class_id: 1,
          lecturer_id: 2,
          session_date: new Date().toISOString().split("T")[0],
          start_time: "08:30",
          end_time: "11:30",
          session_type: "practical",
          topic: "EFI Engine Oscilloscope Testing",
          facility_equipment: "Automotive Bay 3",
          practical_hours: 3.0,
          theory_hours: 0.0,
          status: "completed"
        }
      ],
      students: [
        {
          student_profile_id: 1,
          user_id: 10,
          first_name: "David",
          last_name: "Ochieng",
          index_number: "GTVC/2025/AUT/001",
          registration_number: "REG-2025-001",
          summary: {
            total_sessions: 10,
            present_count: 9,
            absent_count: 0,
            late_count: 1,
            excused_count: 0,
            attendance_percentage: 95.0,
            warning_level: "normal",
            total_practical_hours: 24.0,
            total_theory_hours: 12.0
          },
          session_statuses: {
            101: "present"
          }
        },
        {
          student_profile_id: 2,
          user_id: 11,
          first_name: "Amina",
          last_name: "Hassan",
          index_number: "GTVC/2025/AUT/002",
          registration_number: "REG-2025-002",
          summary: {
            total_sessions: 10,
            present_count: 6,
            absent_count: 3,
            late_count: 1,
            excused_count: 0,
            attendance_percentage: 65.0,
            warning_level: "warning",
            total_practical_hours: 18.0,
            total_theory_hours: 8.0
          },
          session_statuses: {
            101: "late"
          }
        }
      ]
    };
  }
}

export async function getDepartmentAttendanceReportApi(departmentId?: number): Promise<DepartmentAttendanceReport> {
  try {
    const query = departmentId ? `?department_id=${departmentId}` : "";
    const res = await apiRequest<DepartmentAttendanceReport>(`/api/v1/attendance/department/report${query}`);
    return res.data || {
      department_id: departmentId || 1,
      total_students: 2,
      warning_count: 1,
      critical_count: 0,
      total_practical_hours: 42.0,
      students: []
    };
  } catch (err) {
    console.warn("API fallback for department attendance report", err);
    return {
      department_id: departmentId || 1,
      total_students: 2,
      warning_count: 1,
      critical_count: 0,
      total_practical_hours: 42.0,
      students: [
        {
          enrollment_id: 1,
          student_profile_id: 1,
          index_number: "GTVC/2025/AUT/001",
          first_name: "David",
          last_name: "Ochieng",
          email: "david.ochieng@gtvc.ac.ke",
          registration_number: "REG-2025-001",
          class_name: "Diploma Automotive Tech - Y2S1",
          program_name: "Diploma in Automotive Engineering",
          attendance_summary: {
            total_sessions: 10,
            present_count: 9,
            absent_count: 0,
            late_count: 1,
            excused_count: 0,
            attendance_percentage: 95.0,
            warning_level: "normal",
            total_practical_hours: 24.0,
            total_theory_hours: 12.0
          }
        },
        {
          enrollment_id: 2,
          student_profile_id: 2,
          index_number: "GTVC/2025/AUT/002",
          first_name: "Amina",
          last_name: "Hassan",
          email: "amina.hassan@gtvc.ac.ke",
          registration_number: "REG-2025-002",
          class_name: "Diploma Automotive Tech - Y2S1",
          program_name: "Diploma in Automotive Engineering",
          attendance_summary: {
            total_sessions: 10,
            present_count: 6,
            absent_count: 3,
            late_count: 1,
            excused_count: 0,
            attendance_percentage: 65.0,
            warning_level: "warning",
            total_practical_hours: 18.0,
            total_theory_hours: 8.0
          }
        }
      ]
    };
  }
}

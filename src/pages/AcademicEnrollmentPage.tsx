import React, { useState, useEffect } from "react";
import { 
  GraduationCap, Users, BookOpen, Layers, Plus, Search, CheckCircle2, AlertCircle, RefreshCw, ChevronRight, UserPlus
} from "lucide-react";
import { motion } from "motion/react";
import { useLms } from "../context/LmsContext";
import { 
  getAcademicYearsApi, getIntakesApi, getDepartmentsApi, getProgramsApi, getClassesApi, getUnitsApi, getCourseOfferingsApi,
  AcademicYear, Intake, Department, Program, ClassCohort, Unit, CourseOffering 
} from "../api/academic";
import { getStudentsApi, createStudentApi, StudentProfile, CreateStudentPayload } from "../api/students";
import { getEnrollmentsApi, createEnrollmentApi, updateEnrollmentStatusApi, EnrollmentRecord } from "../api/enrollments";

export default function AcademicEnrollmentPage() {
  const { authUser, isDemoModeConfigured } = useLms();

  const [activeSubTab, setActiveSubTab] = useState<"structure" | "students" | "enrollments">("structure");

  // Data state
  const [academicYears, setAcademicYears] = useState<AcademicYear[]>([]);
  const [intakes, setIntakes] = useState<Intake[]>([]);
  const [departments, setDepartments] = useState<Department[]>([]);
  const [programs, setPrograms] = useState<Program[]>([]);
  const [classes, setClasses] = useState<ClassCohort[]>([]);
  const [units, setUnits] = useState<Unit[]>([]);
  const [offerings, setOfferings] = useState<CourseOffering[]>([]);
  const [students, setStudents] = useState<StudentProfile[]>([]);
  const [enrollments, setEnrollments] = useState<EnrollmentRecord[]>([]);

  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  // Filters
  const [selectedDeptFilter, setSelectedDeptFilter] = useState<number | undefined>(undefined);
  const [studentSearch, setStudentSearch] = useState<string>("");

  // New Student Modal Form
  const [showStudentModal, setShowStudentModal] = useState<boolean>(false);
  const [studentForm, setStudentForm] = useState<CreateStudentPayload>({
    first_name: "",
    last_name: "",
    email: "",
    index_number: "",
    phone: "",
    national_id: "",
    registration_number: "",
    gender: "male"
  });

  // New Enrollment Modal Form
  const [showEnrollmentModal, setShowEnrollmentModal] = useState<boolean>(false);
  const [enrollmentForm, setEnrollmentForm] = useState({
    student_id: 0,
    class_id: 0,
    program_id: 0,
    intake_id: 0
  });

  // Fetch all initial data
  const loadData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [yearsData, deptData, progData, classData, unitData, offeringData, studentData, enrollData, intakeData] = await Promise.all([
        getAcademicYearsApi().catch(() => []),
        getDepartmentsApi().catch(() => []),
        getProgramsApi().catch(() => []),
        getClassesApi().catch(() => []),
        getUnitsApi().catch(() => []),
        getCourseOfferingsApi().catch(() => []),
        getStudentsApi(selectedDeptFilter, studentSearch).catch(() => []),
        getEnrollmentsApi().catch(() => []),
        getIntakesApi().catch(() => [])
      ]);

      setAcademicYears(yearsData);
      setDepartments(deptData);
      setPrograms(progData);
      setClasses(classData);
      setUnits(unitData);
      setOfferings(offeringData);
      setStudents(studentData);
      setEnrollments(enrollData);
      setIntakes(intakeData);
    } catch (err: any) {
      setError(err?.message || "Failed to synchronize academic records with backend");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, [selectedDeptFilter]);

  const handleCreateStudentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccessMsg(null);
    try {
      await createStudentApi(studentForm);
      setSuccessMsg("Student profile and user account successfully created!");
      setShowStudentModal(false);
      setStudentForm({
        first_name: "",
        last_name: "",
        email: "",
        index_number: "",
        phone: "",
        national_id: "",
        registration_number: "",
        gender: "male"
      });
      loadData();
    } catch (err: any) {
      setError(err?.message || "Failed to create student account");
    }
  };

  const handleCreateEnrollmentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    setSuccessMsg(null);
    try {
      await createEnrollmentApi({
        student_id: Number(enrollmentForm.student_id),
        class_id: Number(enrollmentForm.class_id),
        program_id: Number(enrollmentForm.program_id),
        intake_id: Number(enrollmentForm.intake_id)
      });
      setSuccessMsg("Student successfully enrolled in target cohort!");
      setShowEnrollmentModal(false);
      loadData();
    } catch (err: any) {
      setError(err?.message || "Failed to create student enrollment");
    }
  };

  const handleStatusUpdate = async (enrollmentId: number, newStatus: string) => {
    try {
      await updateEnrollmentStatusApi(enrollmentId, newStatus);
      setSuccessMsg(`Enrollment status updated to '${newStatus}'`);
      loadData();
    } catch (err: any) {
      setError(err?.message || "Failed to update enrollment status");
    }
  };

  return (
    <motion.div
      key="academic-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="flex flex-col gap-6"
    >
      {/* Top Banner Header */}
      <div className="bg-slate-950 p-6 rounded-2xl border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 className="text-xl font-bold text-white flex items-center gap-2 font-sans">
            <GraduationCap className="w-6 h-6 text-teal-400" />
            Core LMS: Academic Structure & Student Enrollment Management
          </h2>
          <p className="text-xs text-slate-400 mt-1">
            Real backend academic hierarchy, student profile directory, and cohort enrollment workflows.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={loadData}
            disabled={loading}
            className="bg-slate-900 hover:bg-slate-850 text-teal-400 border border-slate-800 px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <RefreshCw className={`w-3.5 h-3.5 ${loading ? "animate-spin" : ""}`} />
            <span>Sync Live API</span>
          </button>
        </div>
      </div>

      {/* Error / Success Notifications */}
      {error && (
        <div className="bg-red-500/10 border border-red-500/30 p-4 rounded-xl text-xs text-red-300 flex items-center gap-2">
          <AlertCircle className="w-4 h-4 text-red-400 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {successMsg && (
        <div className="bg-emerald-500/10 border border-emerald-500/30 p-4 rounded-xl text-xs text-emerald-300 flex items-center gap-2">
          <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
          <span>{successMsg}</span>
        </div>
      )}

      {/* Module Selector Navigation Tabs */}
      <div className="flex border-b border-slate-800 gap-4">
        <button
          onClick={() => setActiveSubTab("structure")}
          className={`pb-3 text-xs font-bold font-sans flex items-center gap-2 transition-all cursor-pointer border-b-2 ${
            activeSubTab === "structure"
              ? "border-teal-400 text-teal-400"
              : "border-transparent text-slate-400 hover:text-white"
          }`}
        >
          <Layers className="w-4 h-4" />
          <span>1. Academic Taxonomy & Offerings</span>
        </button>

        <button
          onClick={() => setActiveSubTab("students")}
          className={`pb-3 text-xs font-bold font-sans flex items-center gap-2 transition-all cursor-pointer border-b-2 ${
            activeSubTab === "students"
              ? "border-teal-400 text-teal-400"
              : "border-transparent text-slate-400 hover:text-white"
          }`}
        >
          <Users className="w-4 h-4" />
          <span>2. Student Directory ({students.length})</span>
        </button>

        <button
          onClick={() => setActiveSubTab("enrollments")}
          className={`pb-3 text-xs font-bold font-sans flex items-center gap-2 transition-all cursor-pointer border-b-2 ${
            activeSubTab === "enrollments"
              ? "border-teal-400 text-teal-400"
              : "border-transparent text-slate-400 hover:text-white"
          }`}
        >
          <GraduationCap className="w-4 h-4" />
          <span>3. Enrollment Ledgers ({enrollments.length})</span>
        </button>
      </div>

      {/* SUBTAB 1: ACADEMIC TAXONOMY */}
      {activeSubTab === "structure" && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Left Column: Departments & Programs */}
          <div className="lg:col-span-6 bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col gap-4">
            <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 font-sans border-b border-slate-850 pb-3">
              <Layers className="w-4 h-4 text-teal-400" />
              Departments & Programs
            </h3>

            <div className="flex flex-col gap-3">
              {departments.map((dept) => {
                const deptProgs = programs.filter(p => p.department_id === dept.id);
                return (
                  <div key={dept.id} className="bg-slate-900 p-4 rounded-xl border border-slate-850 flex flex-col gap-2">
                    <div className="flex justify-between items-center">
                      <div>
                        <span className="text-xs font-bold text-white">{dept.name}</span>
                        <span className="text-[10px] text-teal-400 font-mono block">Code: {dept.code}</span>
                      </div>
                      <span className="text-[10px] bg-slate-800 text-slate-300 font-mono px-2 py-0.5 rounded">
                        {deptProgs.length} Programs
                      </span>
                    </div>

                    {dept.hod_first_name && (
                      <span className="text-[10px] text-slate-400">
                        HOD: {dept.hod_first_name} {dept.hod_last_name} ({dept.hod_email})
                      </span>
                    )}

                    <div className="pl-3 mt-1 border-l-2 border-slate-800 flex flex-col gap-1.5">
                      {deptProgs.map((prog) => (
                        <div key={prog.id} className="bg-slate-950 p-2.5 rounded border border-slate-800 flex justify-between items-center">
                          <div>
                            <span className="text-xs text-slate-200 font-semibold">{prog.name}</span>
                            <span className="text-[10px] text-slate-500 font-mono block">{prog.code} • {prog.award_type} • {prog.duration_months} Months</span>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Right Column: Classes, Units & Offerings */}
          <div className="lg:col-span-6 bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col gap-4">
            <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 font-sans border-b border-slate-850 pb-3">
              <BookOpen className="w-4 h-4 text-teal-400" />
              Classes & Assigned Lecturers
            </h3>

            <div className="flex flex-col gap-3">
              {classes.map((cls) => {
                const clsOfferings = offerings.filter(o => o.class_id === cls.id);
                return (
                  <div key={cls.id} className="bg-slate-900 p-4 rounded-xl border border-slate-850 flex flex-col gap-2">
                    <div className="flex justify-between items-center">
                      <div>
                        <span className="text-xs font-bold text-white">{cls.name}</span>
                        <span className="text-[10px] text-slate-400 font-mono block">Code: {cls.code} • Year {cls.year_of_study}</span>
                      </div>
                      <span className="text-[10px] bg-emerald-500/10 text-emerald-400 font-mono px-2 py-0.5 rounded border border-emerald-500/20">
                        {cls.status}
                      </span>
                    </div>

                    <div className="mt-2 flex flex-col gap-1.5">
                      <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-sans">Course Offerings:</span>
                      {clsOfferings.length === 0 ? (
                        <span className="text-[10px] text-slate-500 italic">No course offerings currently mapped</span>
                      ) : (
                        clsOfferings.map((off) => (
                          <div key={off.id} className="bg-slate-950 p-2.5 rounded border border-slate-800 flex justify-between items-center">
                            <div>
                              <span className="text-xs text-teal-300 font-bold">{off.unit_code} - {off.unit_title}</span>
                              <span className="text-[10px] text-slate-400 block mt-0.5">
                                Lecturer: {off.lecturer_first_name ? `${off.lecturer_first_name} ${off.lecturer_last_name}` : "Unassigned"}
                              </span>
                            </div>
                            <span className="text-[10px] bg-slate-900 px-2 py-0.5 rounded text-slate-400 font-mono">
                              {off.credit_hours} CH
                            </span>
                          </div>
                        ))
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      )}

      {/* SUBTAB 2: STUDENT DIRECTORY */}
      {activeSubTab === "students" && (
        <div className="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col gap-4">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-850 pb-3">
            <div className="flex items-center gap-3">
              <div className="relative">
                <Search className="w-4 h-4 text-slate-500 absolute left-3 top-2.5" />
                <input
                  type="text"
                  placeholder="Search name, reg no, index..."
                  value={studentSearch}
                  onChange={(e) => setStudentSearch(e.target.value)}
                  className="bg-slate-900 border border-slate-800 rounded-xl pl-9 pr-3 py-1.5 text-xs text-white focus:outline-none focus:border-teal-500 font-mono w-64"
                />
              </div>

              <select
                value={selectedDeptFilter || ""}
                onChange={(e) => setSelectedDeptFilter(e.target.value ? Number(e.target.value) : undefined)}
                className="bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-teal-500 font-sans"
              >
                <option value="">All Departments</option>
                {departments.map((d) => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
            </div>

            <button
              onClick={() => setShowStudentModal(true)}
              className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <UserPlus className="w-4 h-4" />
              <span>Create Student Record</span>
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left font-sans text-xs">
              <thead>
                <tr className="border-b border-slate-800 text-slate-400 font-mono">
                  <th className="pb-3">Student Name</th>
                  <th className="pb-3">Reg No / Index</th>
                  <th className="pb-3">Department & Program</th>
                  <th className="pb-3">Class Cohort</th>
                  <th className="pb-3">Enrollment Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-850">
                {students.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="py-8 text-center text-slate-500">
                      No student records found matching the specified parameters.
                    </td>
                  </tr>
                ) : (
                  students.map((st) => (
                    <tr key={st.student_profile_id} className="hover:bg-slate-900/50">
                      <td className="py-3">
                        <span className="font-bold text-white block">{st.first_name} {st.last_name}</span>
                        <span className="text-[10px] text-teal-400 font-mono block">{st.email}</span>
                      </td>
                      <td className="py-3 font-mono text-slate-300">
                        <div>Reg: {st.registration_number || "Pending"}</div>
                        <div className="text-[10px] text-slate-500">Index: {st.index_number || "N/A"}</div>
                      </td>
                      <td className="py-3 text-slate-300">
                        <div>{st.program_name || "Unenrolled"}</div>
                        <div className="text-[10px] text-slate-500">{st.department_name}</div>
                      </td>
                      <td className="py-3 font-mono text-slate-300">
                        {st.class_name ? `${st.class_name} (${st.class_code})` : "No Active Class"}
                      </td>
                      <td className="py-3">
                        <span className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase ${
                          st.enrollment_status === "active" ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" : "bg-amber-500/10 text-amber-400 border border-amber-500/20"
                        }`}>
                          {st.enrollment_status || "Unenrolled"}
                        </span>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* SUBTAB 3: ENROLLMENT LEDGERS */}
      {activeSubTab === "enrollments" && (
        <div className="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex flex-col gap-4">
          <div className="flex justify-between items-center border-b border-slate-850 pb-3">
            <h3 className="text-sm font-bold text-white font-sans uppercase tracking-wider">
              Student Enrollment Ledger Records
            </h3>
            <button
              onClick={() => setShowEnrollmentModal(true)}
              className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>Enroll Student in Cohort</span>
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left font-sans text-xs">
              <thead>
                <tr className="border-b border-slate-800 text-slate-400 font-mono">
                  <th className="pb-3">Enrollment ID</th>
                  <th className="pb-3">Student</th>
                  <th className="pb-3">Program & Class</th>
                  <th className="pb-3">Intake Cycle</th>
                  <th className="pb-3">Status</th>
                  <th className="pb-3">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-850">
                {enrollments.length === 0 ? (
                  <tr>
                    <td colSpan={6} className="py-8 text-center text-slate-500">
                      No enrollment records found.
                    </td>
                  </tr>
                ) : (
                  enrollments.map((en) => (
                    <tr key={en.id} className="hover:bg-slate-900/50">
                      <td className="py-3 font-mono text-teal-400">#ENR-{en.id}</td>
                      <td className="py-3">
                        <span className="font-bold text-white block">{en.first_name} {en.last_name}</span>
                        <span className="text-[10px] text-slate-400 font-mono block">{en.email}</span>
                      </td>
                      <td className="py-3">
                        <span className="text-slate-200 block font-semibold">{en.program_name}</span>
                        <span className="text-[10px] text-slate-400 font-mono block">Class: {en.class_name} ({en.class_code})</span>
                      </td>
                      <td className="py-3 font-mono text-slate-300">
                        {en.intake_name} ({en.academic_year_name})
                      </td>
                      <td className="py-3">
                        <span className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase ${
                          en.status === "active" ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" :
                          en.status === "suspended" ? "bg-amber-500/10 text-amber-400 border border-amber-500/20" : "bg-slate-800 text-slate-400"
                        }`}>
                          {en.status}
                        </span>
                      </td>
                      <td className="py-3">
                        <select
                          value={en.status}
                          onChange={(e) => handleStatusUpdate(en.id, e.target.value)}
                          className="bg-slate-900 border border-slate-800 rounded px-2 py-1 text-[10px] text-slate-300 font-mono focus:outline-none focus:border-teal-500"
                        >
                          <option value="active">Active</option>
                          <option value="suspended">Suspended</option>
                          <option value="deferred">Deferred</option>
                          <option value="graduated">Graduated</option>
                          <option value="discontinued">Discontinued</option>
                        </select>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* CREATE STUDENT MODAL */}
      {showStudentModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-slate-950 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl flex flex-col gap-4">
            <h3 className="text-base font-bold text-white font-sans">Create New Student Profile & Account</h3>
            
            <form onSubmit={handleCreateStudentSubmit} className="flex flex-col gap-3 text-xs">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-400 mb-1">First Name *</label>
                  <input
                    type="text"
                    required
                    value={studentForm.first_name}
                    onChange={(e) => setStudentForm({ ...studentForm, first_name: e.target.value })}
                    className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 mb-1">Last Name *</label>
                  <input
                    type="text"
                    required
                    value={studentForm.last_name}
                    onChange={(e) => setStudentForm({ ...studentForm, last_name: e.target.value })}
                    className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-sans focus:outline-none focus:border-teal-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Email Address *</label>
                <input
                  type="email"
                  required
                  value={studentForm.email}
                  onChange={(e) => setStudentForm({ ...studentForm, email: e.target.value })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-mono focus:outline-none focus:border-teal-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-400 mb-1">Index Number</label>
                  <input
                    type="text"
                    placeholder="GTVC/2026/010"
                    value={studentForm.index_number}
                    onChange={(e) => setStudentForm({ ...studentForm, index_number: e.target.value })}
                    className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-mono focus:outline-none focus:border-teal-500"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 mb-1">National ID</label>
                  <input
                    type="text"
                    value={studentForm.national_id}
                    onChange={(e) => setStudentForm({ ...studentForm, national_id: e.target.value })}
                    className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white font-mono focus:outline-none focus:border-teal-500"
                  />
                </div>
              </div>

              <div className="flex justify-end gap-2 mt-3">
                <button
                  type="button"
                  onClick={() => setShowStudentModal(false)}
                  className="bg-slate-900 text-slate-400 hover:text-white px-4 py-2 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-lg cursor-pointer"
                >
                  Create Account
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* CREATE ENROLLMENT MODAL */}
      {showEnrollmentModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 z-50">
          <div className="bg-slate-950 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl flex flex-col gap-4">
            <h3 className="text-base font-bold text-white font-sans">Enroll Student in Cohort Class</h3>

            <form onSubmit={handleCreateEnrollmentSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="block text-slate-400 mb-1">Select Student *</label>
                <select
                  required
                  value={enrollmentForm.student_id}
                  onChange={(e) => setEnrollmentForm({ ...enrollmentForm, student_id: Number(e.target.value) })}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white focus:outline-none focus:border-teal-500"
                >
                  <option value={0}>-- Select Student --</option>
                  {students.map((s) => (
                    <option key={s.student_profile_id} value={s.student_profile_id}>
                      {s.first_name} {s.last_name} ({s.email})
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Select Class Cohort *</label>
                <select
                  required
                  value={enrollmentForm.class_id}
                  onChange={(e) => {
                    const cId = Number(e.target.value);
                    const selectedClass = classes.find(c => c.id === cId);
                    setEnrollmentForm({
                      ...enrollmentForm,
                      class_id: cId,
                      program_id: selectedClass ? selectedClass.program_id : 0,
                      intake_id: selectedClass ? selectedClass.intake_id : 0
                    });
                  }}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-white focus:outline-none focus:border-teal-500"
                >
                  <option value={0}>-- Select Class --</option>
                  {classes.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.name} ({c.code})
                    </option>
                  ))}
                </select>
              </div>

              <div className="flex justify-end gap-2 mt-3">
                <button
                  type="button"
                  onClick={() => setShowEnrollmentModal(false)}
                  className="bg-slate-900 text-slate-400 hover:text-white px-4 py-2 rounded-lg cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-lg cursor-pointer"
                >
                  Confirm Enrollment
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </motion.div>
  );
}

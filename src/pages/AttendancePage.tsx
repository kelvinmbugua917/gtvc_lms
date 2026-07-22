import React, { useState, useEffect } from "react";
import { 
  Calendar, CheckCircle, XCircle, Clock, AlertTriangle, ShieldCheck, 
  FileText, Plus, Search, Filter, Wrench, UserCheck, BarChart2,
  BookOpen, Building, Award, Check, RefreshCw, ChevronRight, User, AlertCircle
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { useLms } from "../context/LmsContext";
import { 
  getAttendanceSessionsApi, 
  getAttendanceSessionByIdApi, 
  createAttendanceSessionApi, 
  saveAttendanceRecordsApi, 
  getMyAttendanceApi,
  AttendanceSessionRecord, 
  StudentAttendanceRecord, 
  AttendanceSummary,
  StudentAttendanceHistoryItem 
} from "../api/attendance";
import { 
  getDepartmentAttendanceReportApi, 
  DepartmentAttendanceReport 
} from "../api/attendanceReports";

export default function AttendancePage() {
  const { currentUser, courseOfferings } = useLms();
  const roleNames = currentUser?.roles?.map(r => r.name) || ["student"];
  const isStudent = roleNames.includes("student") && !roleNames.includes("super_admin") && !roleNames.includes("admin");
  const isLecturer = roleNames.includes("lecturer");
  const isHod = roleNames.includes("hod");
  const isAdmin = roleNames.includes("super_admin") || roleNames.includes("admin");

  // Tab view state
  const [activeSubTab, setActiveSubTab] = useState<"my_attendance" | "marking" | "sessions" | "department_report">(
    isStudent ? "my_attendance" : (isHod ? "department_report" : "marking")
  );

  // Student view states
  const [mySummary, setMySummary] = useState<AttendanceSummary | null>(null);
  const [myHistory, setMyHistory] = useState<StudentAttendanceHistoryItem[]>([]);
  const [loadingStudentData, setLoadingStudentData] = useState(false);

  // Lecturer marking view states
  const [sessions, setSessions] = useState<AttendanceSessionRecord[]>([]);
  const [selectedOfferingId, setSelectedOfferingId] = useState<number>(courseOfferings[0]?.id || 1);
  const [selectedSessionId, setSelectedSessionId] = useState<number | null>(null);
  const [activeSession, setActiveSession] = useState<AttendanceSessionRecord | null>(null);
  const [sessionRecords, setSessionRecords] = useState<StudentAttendanceRecord[]>([]);
  const [loadingSession, setLoadingSession] = useState(false);
  const [savingAttendance, setSavingAttendance] = useState(false);
  const [saveSuccessMsg, setSaveSuccessMsg] = useState<string | null>(null);

  // Search & Filters for marking
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("all");

  // Create Session Modal state
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [newSessionData, setNewSessionData] = useState({
    course_offering_id: courseOfferings[0]?.id || 1,
    session_date: new Date().toISOString().split("T")[0],
    start_time: "08:30",
    end_time: "11:30",
    session_type: "practical" as "theory" | "practical" | "workshop" | "laboratory" | "fieldwork" | "examination",
    topic: "",
    facility_equipment: "",
    practical_hours: 3.0,
    theory_hours: 0.0,
    notes: ""
  });
  const [creatingSession, setCreatingSession] = useState(false);

  // Department report states
  const [deptReport, setDeptReport] = useState<DepartmentAttendanceReport | null>(null);
  const [loadingReport, setLoadingReport] = useState(false);
  const [reportSearch, setReportSearch] = useState("");
  const [warningFilter, setWarningFilter] = useState<"all" | "warning" | "critical">("all");

  // Initial Data Fetching
  useEffect(() => {
    if (isStudent || activeSubTab === "my_attendance") {
      fetchStudentAttendance();
    }
  }, [activeSubTab]);

  useEffect(() => {
    fetchSessions();
  }, [selectedOfferingId]);

  useEffect(() => {
    if (activeSubTab === "department_report") {
      fetchDepartmentReport();
    }
  }, [activeSubTab]);

  const fetchStudentAttendance = async () => {
    setLoadingStudentData(true);
    try {
      const data = await getMyAttendanceApi();
      setMySummary(data.summary);
      setMyHistory(data.history);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingStudentData(false);
    }
  };

  const fetchSessions = async () => {
    try {
      const list = await getAttendanceSessionsApi({ course_offering_id: selectedOfferingId });
      setSessions(list);
      if (list.length > 0 && !selectedSessionId) {
        setSelectedSessionId(list[0].id);
        loadSessionDetails(list[0].id);
      }
    } catch (err) {
      console.error(err);
    }
  };

  const loadSessionDetails = async (sessionId: number) => {
    setLoadingSession(true);
    try {
      const session = await getAttendanceSessionByIdApi(sessionId);
      setActiveSession(session);
      setSessionRecords(session.records || []);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingSession(false);
    }
  };

  const fetchDepartmentReport = async () => {
    setLoadingReport(true);
    try {
      const report = await getDepartmentAttendanceReportApi(currentUser?.department_id);
      setDeptReport(report);
    } catch (err) {
      console.error(err);
    } finally {
      setLoadingReport(false);
    }
  };

  // Student status change in lecturer marking grid
  const handleStatusChange = (studentProfileId: number, status: "present" | "absent" | "late" | "excused") => {
    setSessionRecords(prev => prev.map(rec => {
      if (rec.student_profile_id === studentProfileId) {
        return { ...rec, status };
      }
      return rec;
    }));
  };

  const handleRecordFieldChange = (studentProfileId: number, field: keyof StudentAttendanceRecord, value: string) => {
    setSessionRecords(prev => prev.map(rec => {
      if (rec.student_profile_id === studentProfileId) {
        return { ...rec, [field]: value };
      }
      return rec;
    }));
  };

  const handleMarkAllPresent = () => {
    setSessionRecords(prev => prev.map(rec => ({ ...rec, status: "present" })));
  };

  const handleSaveAttendance = async () => {
    if (!selectedSessionId) return;
    setSavingAttendance(true);
    setSaveSuccessMsg(null);
    try {
      await saveAttendanceRecordsApi(selectedSessionId, sessionRecords);
      setSaveSuccessMsg("Attendance records saved successfully!");
      setTimeout(() => setSaveSuccessMsg(null), 4000);
      fetchSessions();
    } catch (err) {
      console.error(err);
    } finally {
      setSavingAttendance(false);
    }
  };

  const handleCreateSession = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newSessionData.topic.trim()) return;
    setCreatingSession(true);
    try {
      const created = await createAttendanceSessionApi(newSessionData);
      setShowCreateModal(false);
      setSelectedSessionId(created.id);
      setActiveSession(created);
      setSessionRecords(created.records || []);
      fetchSessions();
    } catch (err) {
      console.error(err);
    } finally {
      setCreatingSession(false);
    }
  };

  // Filtered session records for marking table
  const filteredRecords = sessionRecords.filter(rec => {
    const nameMatch = `${rec.first_name} ${rec.last_name} ${rec.index_number} ${rec.registration_number}`.toLowerCase().includes(searchTerm.toLowerCase());
    const statusMatch = statusFilter === "all" || rec.status === statusFilter;
    return nameMatch && statusMatch;
  });

  return (
    <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} className="space-y-6 pb-12">
      {/* Header Banner */}
      <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div className="absolute top-0 right-0 w-96 h-96 bg-teal-500/5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none" />
        
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
          <div>
            <div className="flex items-center gap-2 text-teal-400 font-mono text-xs uppercase tracking-wider mb-1 font-bold">
              <Building className="w-3.5 h-3.5" />
              <span>Gilgil TVC LMS • Phase 6D</span>
              <span className="bg-teal-500/10 text-teal-300 border border-teal-500/20 px-2 py-0.5 rounded text-[10px]">
                SUPERVISED WORKSHOP & THEORY
              </span>
            </div>
            <h1 className="text-2xl font-black text-white tracking-tight flex items-center gap-2">
              <Calendar className="w-6 h-6 text-teal-400" />
              Attendance, Practical & Workshop Tracking
            </h1>
            <p className="text-slate-400 text-xs mt-1 max-w-2xl">
              Real-time register for classroom lectures, TVET practical lab clock-ins, equipment usage logs, and department attendance warning alerts.
            </p>
          </div>

          <div className="flex items-center gap-2 bg-slate-950 p-2 rounded-xl border border-slate-800 self-start md:self-auto">
            <span className="text-xs text-slate-400 font-medium px-2">Role context:</span>
            <span className="bg-teal-500/20 text-teal-300 font-bold font-mono text-xs px-2.5 py-1 rounded-lg border border-teal-500/30 uppercase">
              {roleNames[0] || "User"}
            </span>
          </div>
        </div>

        {/* Navigation Sub-Tabs */}
        <div className="flex flex-wrap gap-2 mt-6 pt-4 border-t border-slate-800/80">
          <button
            id="tab-my-attendance"
            onClick={() => setActiveSubTab("my_attendance")}
            className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
              activeSubTab === "my_attendance"
                ? "bg-teal-500 text-slate-950 shadow-md shadow-teal-500/20"
                : "text-slate-400 hover:text-white hover:bg-slate-800"
            }`}
          >
            <UserCheck className="w-4 h-4" />
            <span>My Attendance & TVET Hours</span>
          </button>

          {(isLecturer || isAdmin) && (
            <button
              id="tab-marking"
              onClick={() => setActiveSubTab("marking")}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                activeSubTab === "marking"
                  ? "bg-teal-500 text-slate-950 shadow-md shadow-teal-500/20"
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <CheckCircle className="w-4 h-4" />
              <span>Mark Register & Practicals</span>
            </button>
          )}

          {(isLecturer || isAdmin) && (
            <button
              id="tab-sessions-ledger"
              onClick={() => setActiveSubTab("sessions")}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                activeSubTab === "sessions"
                  ? "bg-teal-500 text-slate-950 shadow-md shadow-teal-500/20"
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <FileText className="w-4 h-4" />
              <span>Session Ledger</span>
            </button>
          )}

          {(isHod || isAdmin) && (
            <button
              id="tab-dept-report"
              onClick={() => setActiveSubTab("department_report")}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer ${
                activeSubTab === "department_report"
                  ? "bg-teal-500 text-slate-950 shadow-md shadow-teal-500/20"
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <BarChart2 className="w-4 h-4" />
              <span>Department Reports & Warnings</span>
            </button>
          )}
        </div>
      </div>

      {/* ========================================================================= */}
      {/* SUB-TAB 1: MY ATTENDANCE & TVET HOURS (STUDENT VIEW)                       */}
      {/* ========================================================================= */}
      {activeSubTab === "my_attendance" && (
        <div className="space-y-6" id="student-attendance-view">
          {loadingStudentData ? (
            <div className="p-12 text-center text-slate-400 flex flex-col items-center gap-3">
              <RefreshCw className="w-6 h-6 animate-spin text-teal-400" />
              <span>Calculating student attendance percentages & practical hours...</span>
            </div>
          ) : mySummary ? (
            <>
              {/* Summary Cards Grid */}
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {/* Overall Attendance % */}
                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl relative overflow-hidden shadow-lg">
                  <div className="flex justify-between items-start">
                    <div>
                      <span className="text-xs text-slate-400 font-medium block">Overall Attendance</span>
                      <span className="text-3xl font-black text-white mt-1 block">
                        {mySummary.attendance_percentage}%
                      </span>
                    </div>
                    <div className={`p-2.5 rounded-xl ${
                      mySummary.warning_level === "normal" ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" :
                      mySummary.warning_level === "warning" ? "bg-amber-500/10 text-amber-400 border border-amber-500/20" :
                      "bg-rose-500/10 text-rose-400 border border-rose-500/20"
                    }`}>
                      {mySummary.warning_level === "normal" ? <CheckCircle className="w-5 h-5" /> : <AlertTriangle className="w-5 h-5" />}
                    </div>
                  </div>
                  <div className="mt-3 flex items-center justify-between text-[11px]">
                    <span className="text-slate-400">Institutional threshold: 75%</span>
                    <span className={`font-bold uppercase font-mono px-2 py-0.5 rounded ${
                      mySummary.warning_level === "normal" ? "bg-emerald-500/20 text-emerald-300" :
                      mySummary.warning_level === "warning" ? "bg-amber-500/20 text-amber-300" :
                      "bg-rose-500/20 text-rose-300"
                    }`}>
                      {mySummary.warning_level}
                    </span>
                  </div>
                </div>

                {/* TVET Practical Hours */}
                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <div className="flex justify-between items-start">
                    <div>
                      <span className="text-xs text-slate-400 font-medium block">TVET Practical Hours</span>
                      <span className="text-3xl font-black text-teal-400 mt-1 block">
                        {mySummary.total_practical_hours} <span className="text-xs text-slate-400 font-normal">hrs</span>
                      </span>
                    </div>
                    <div className="p-2.5 rounded-xl bg-teal-500/10 text-teal-400 border border-teal-500/20">
                      <Wrench className="w-5 h-5" />
                    </div>
                  </div>
                  <span className="text-[11px] text-slate-400 mt-3 block">
                    Supervised workshop & lab clock-in hours
                  </span>
                </div>

                {/* Theory Hours */}
                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <div className="flex justify-between items-start">
                    <div>
                      <span className="text-xs text-slate-400 font-medium block">Theory Classroom Hours</span>
                      <span className="text-3xl font-black text-sky-400 mt-1 block">
                        {mySummary.total_theory_hours} <span className="text-xs text-slate-400 font-normal">hrs</span>
                      </span>
                    </div>
                    <div className="p-2.5 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
                      <BookOpen className="w-5 h-5" />
                    </div>
                  </div>
                  <span className="text-[11px] text-slate-400 mt-3 block">
                    Lecture hall & theory sessions
                  </span>
                </div>

                {/* Sessions Breakdown */}
                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <span className="text-xs text-slate-400 font-medium block mb-2">Sessions Register</span>
                  <div className="grid grid-cols-4 gap-1 text-center font-mono text-xs">
                    <div className="bg-emerald-500/10 p-1.5 rounded border border-emerald-500/20">
                      <span className="text-emerald-400 font-bold block">{mySummary.present_count}</span>
                      <span className="text-[9px] text-slate-400 block uppercase">Pres</span>
                    </div>
                    <div className="bg-amber-500/10 p-1.5 rounded border border-amber-500/20">
                      <span className="text-amber-400 font-bold block">{mySummary.late_count}</span>
                      <span className="text-[9px] text-slate-400 block uppercase">Late</span>
                    </div>
                    <div className="bg-sky-500/10 p-1.5 rounded border border-sky-500/20">
                      <span className="text-sky-400 font-bold block">{mySummary.excused_count}</span>
                      <span className="text-[9px] text-slate-400 block uppercase">Exc</span>
                    </div>
                    <div className="bg-rose-500/10 p-1.5 rounded border border-rose-500/20">
                      <span className="text-rose-400 font-bold block">{mySummary.absent_count}</span>
                      <span className="text-[9px] text-slate-400 block uppercase">Abs</span>
                    </div>
                  </div>
                  <span className="text-[10px] text-slate-500 block text-right mt-2 font-mono">
                    Total: {mySummary.total_sessions} Sessions
                  </span>
                </div>
              </div>

              {/* Attendance Warning Alert Banner (if warning or critical) */}
              {mySummary.warning_level !== "normal" && (
                <div className={`p-4 rounded-xl border flex items-start gap-3 ${
                  mySummary.warning_level === "critical"
                    ? "bg-rose-950/40 border-rose-500/30 text-rose-200"
                    : "bg-amber-950/40 border-amber-500/30 text-amber-200"
                }`}>
                  <AlertTriangle className="w-5 h-5 flex-shrink-0 mt-0.5" />
                  <div>
                    <h4 className="font-bold text-xs uppercase tracking-wider">
                      {mySummary.warning_level === "critical" ? "Critical Attendance Alert (< 60%)" : "Attendance Warning Alert (60% - 74%)"}
                    </h4>
                    <p className="text-xs mt-1 text-slate-300">
                      Your overall attendance is currently at <strong className="font-bold">{mySummary.attendance_percentage}%</strong>. Gilgil TVC policy requires a minimum of 75% attendance to qualify for final TVET exam sit-in and KNEC/CDACC assessments. Please liaise with your lecturer or HOD if you have excused medical or official absence documentation.
                    </p>
                  </div>
                </div>
              )}

              {/* Course-by-Course Attendance Cards */}
              <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                  <BookOpen className="w-4 h-4 text-teal-400" />
                  Course Unit Attendance Breakdown
                </h3>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {mySummary.course_breakdown?.map((course) => (
                    <div key={course.course_offering_id} className="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                      <div className="flex justify-between items-start">
                        <div>
                          <span className="font-mono text-xs font-bold text-teal-400">{course.unit_code}</span>
                          <h4 className="text-sm font-bold text-white mt-0.5">{course.unit_title}</h4>
                        </div>
                        <span className={`text-xs font-mono font-bold px-2 py-0.5 rounded ${
                          course.warning_level === "normal" ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" :
                          course.warning_level === "warning" ? "bg-amber-500/10 text-amber-400 border border-amber-500/20" :
                          "bg-rose-500/10 text-rose-400 border border-rose-500/20"
                        }`}>
                          {course.percentage}%
                        </span>
                      </div>

                      {/* Progress Bar */}
                      <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div 
                          className={`h-full transition-all duration-500 ${
                            course.warning_level === "normal" ? "bg-emerald-500" :
                            course.warning_level === "warning" ? "bg-amber-500" : "bg-rose-500"
                          }`}
                          style={{ width: `${Math.min(course.percentage, 100)}%` }}
                        />
                      </div>

                      <div className="flex justify-between text-[11px] text-slate-400 font-mono">
                        <span>Sessions: {course.present_count} / {course.total_sessions} Present</span>
                        <span className="text-teal-400 font-bold">Practical: {course.practical_hours_completed} hrs</span>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Attendance Session History Log */}
              <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                  <Clock className="w-4 h-4 text-teal-400" />
                  Recent Session Attendance History Log
                </h3>

                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs font-mono">
                    <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                      <tr>
                        <th className="p-3">Date & Time</th>
                        <th className="p-3">Type</th>
                        <th className="p-3">Course & Topic</th>
                        <th className="p-3">Facility / Equipment</th>
                        <th className="p-3">Status</th>
                        <th className="p-3">Lecturer Notes</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800 text-slate-300">
                      {myHistory.map((item) => (
                        <tr key={item.id} className="hover:bg-slate-850/50">
                          <td className="p-3 whitespace-nowrap">
                            <span className="text-white font-bold block">{item.session_date}</span>
                            <span className="text-[10px] text-slate-400 block">{item.start_time} - {item.end_time}</span>
                          </td>
                          <td className="p-3 whitespace-nowrap">
                            <span className={`text-[9px] uppercase px-2 py-0.5 rounded font-bold ${
                              item.session_type === "practical" || item.session_type === "workshop"
                                ? "bg-teal-500/10 text-teal-300 border border-teal-500/20"
                                : "bg-sky-500/10 text-sky-300 border border-sky-500/20"
                            }`}>
                              {item.session_type} ({item.session_type === "practical" ? `${item.practical_hours}h` : `${item.theory_hours}h`})
                            </span>
                          </td>
                          <td className="p-3">
                            <span className="text-teal-400 font-bold block">{item.unit_code} - {item.unit_title}</span>
                            <span className="text-slate-200 font-sans block text-xs mt-0.5">{item.topic}</span>
                          </td>
                          <td className="p-3 text-slate-400 font-sans text-xs max-w-xs">
                            {item.facility_equipment || "Classroom / Hall"}
                          </td>
                          <td className="p-3 whitespace-nowrap">
                            <span className={`text-[10px] uppercase font-bold px-2 py-0.5 rounded ${
                              item.status === "present" ? "bg-emerald-500/20 text-emerald-300" :
                              item.status === "late" ? "bg-amber-500/20 text-amber-300" :
                              item.status === "excused" ? "bg-sky-500/20 text-sky-300" :
                              "bg-rose-500/20 text-rose-300"
                            }`}>
                              {item.status}
                            </span>
                          </td>
                          <td className="p-3 text-slate-400 font-sans text-xs max-w-xs">
                            {item.lecturer_notes || item.excuse_reason || "—"}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          ) : (
            <div className="p-8 text-center text-slate-400">
              <span>No student attendance records available.</span>
            </div>
          )}
        </div>
      )}

      {/* ========================================================================= */}
      {/* SUB-TAB 2: LECTURER MARKING REGISTER & WORKSHOP (LECTURER VIEW)            */}
      {/* ========================================================================= */}
      {activeSubTab === "marking" && (
        <div className="space-y-6" id="lecturer-marking-view">
          {/* Controls Header */}
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full md:w-auto">
              <div className="w-full sm:w-64">
                <label className="text-[11px] text-slate-400 uppercase font-mono block mb-1">Select Course Offering</label>
                <select
                  value={selectedOfferingId}
                  onChange={(e) => setSelectedOfferingId(Number(e.target.value))}
                  className="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3 py-2 font-mono focus:border-teal-500 outline-none"
                >
                  {courseOfferings.map(c => (
                    <option key={c.id} value={c.id}>
                      {c.unit_code} - {c.unit_title} ({c.class_code})
                    </option>
                  ))}
                </select>
              </div>

              {sessions.length > 0 && (
                <div className="w-full sm:w-64">
                  <label className="text-[11px] text-slate-400 uppercase font-mono block mb-1">Select Session</label>
                  <select
                    value={selectedSessionId || ""}
                    onChange={(e) => {
                      const id = Number(e.target.value);
                      setSelectedSessionId(id);
                      loadSessionDetails(id);
                    }}
                    className="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-xl px-3 py-2 font-mono focus:border-teal-500 outline-none"
                  >
                    {sessions.map(s => (
                      <option key={s.id} value={s.id}>
                        {s.session_date} | {s.session_type.toUpperCase()} ({s.start_time}) - {s.topic}
                      </option>
                    ))}
                  </select>
                </div>
              )}
            </div>

            <button
              id="btn-create-session-modal"
              onClick={() => setShowCreateModal(true)}
              className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-xl transition-all shadow-md shadow-teal-500/10 flex items-center gap-2 cursor-pointer self-end md:self-auto"
            >
              <Plus className="w-4 h-4" />
              <span>Create New Attendance Session</span>
            </button>
          </div>

          {/* Active Session & Student Marking Table */}
          {loadingSession ? (
            <div className="p-12 text-center text-slate-400 flex flex-col items-center gap-2">
              <RefreshCw className="w-6 h-6 animate-spin text-teal-400" />
              <span>Loading session roster & records...</span>
            </div>
          ) : activeSession ? (
            <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
              {/* Session Meta Card */}
              <div className="bg-slate-950 border border-slate-800 p-4 rounded-xl flex flex-col md:flex-row justify-between gap-4">
                <div>
                  <div className="flex items-center gap-2">
                    <span className="font-mono text-xs font-bold text-teal-400">{activeSession.unit_code} - {activeSession.unit_title}</span>
                    <span className="text-[10px] bg-slate-800 text-slate-300 font-mono px-2 py-0.5 rounded">{activeSession.class_name}</span>
                    <span className={`text-[10px] uppercase font-bold font-mono px-2 py-0.5 rounded ${
                      activeSession.session_type === "practical" || activeSession.session_type === "workshop"
                        ? "bg-teal-500/10 text-teal-300 border border-teal-500/20"
                        : "bg-sky-500/10 text-sky-300 border border-sky-500/20"
                    }`}>
                      {activeSession.session_type}
                    </span>
                  </div>
                  <h3 className="text-base font-bold text-white mt-1">{activeSession.topic}</h3>
                  {activeSession.facility_equipment && (
                    <p className="text-xs text-slate-400 mt-1 flex items-center gap-1.5 font-mono">
                      <Wrench className="w-3.5 h-3.5 text-teal-400" />
                      <span>Facility / Equipment: {activeSession.facility_equipment}</span>
                    </p>
                  )}
                </div>

                <div className="flex flex-col md:items-end justify-center text-xs font-mono text-slate-400 space-y-1 border-t md:border-t-0 md:border-l border-slate-800 pt-2 md:pt-0 md:pl-4">
                  <div>Date: <strong className="text-white">{activeSession.session_date}</strong></div>
                  <div>Time: <strong className="text-white">{activeSession.start_time} - {activeSession.end_time}</strong></div>
                  <div>Hours: <strong className="text-teal-400">{activeSession.session_type === "practical" ? `${activeSession.practical_hours}h Practical` : `${activeSession.theory_hours}h Theory`}</strong></div>
                </div>
              </div>

              {/* Roster Controls: Search, Mark All Present, Save */}
              <div className="flex flex-col sm:flex-row justify-between items-center gap-3 pt-2">
                <div className="flex items-center gap-2 w-full sm:w-auto">
                  <div className="relative flex-1 sm:w-64">
                    <Search className="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3" />
                    <input
                      type="text"
                      placeholder="Search student or index..."
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                      className="w-full bg-slate-950 border border-slate-800 text-white text-xs pl-8 pr-3 py-2 rounded-xl font-mono focus:border-teal-500 outline-none"
                    />
                  </div>

                  <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="bg-slate-950 border border-slate-800 text-white text-xs px-3 py-2 rounded-xl font-mono focus:border-teal-500 outline-none"
                  >
                    <option value="all">All Statuses</option>
                    <option value="present">Present</option>
                    <option value="late">Late</option>
                    <option value="absent">Absent</option>
                    <option value="excused">Excused</option>
                  </select>
                </div>

                <div className="flex items-center gap-2 w-full sm:w-auto justify-end">
                  <button
                    onClick={handleMarkAllPresent}
                    className="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold px-3 py-2 rounded-xl transition-all flex items-center gap-1.5 cursor-pointer"
                  >
                    <CheckCircle className="w-3.5 h-3.5 text-emerald-400" />
                    <span>Mark All Present</span>
                  </button>

                  <button
                    id="btn-save-attendance-records"
                    onClick={handleSaveAttendance}
                    disabled={savingAttendance}
                    className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs px-5 py-2 rounded-xl transition-all shadow-md shadow-teal-500/10 flex items-center gap-2 cursor-pointer disabled:opacity-50"
                  >
                    {savingAttendance ? <RefreshCw className="w-4 h-4 animate-spin" /> : <ShieldCheck className="w-4 h-4" />}
                    <span>Save Register</span>
                  </button>
                </div>
              </div>

              {saveSuccessMsg && (
                <div className="bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 p-3 rounded-xl text-xs font-mono flex items-center gap-2">
                  <CheckCircle className="w-4 h-4 text-emerald-400" />
                  <span>{saveSuccessMsg}</span>
                </div>
              )}

              {/* Roster Marking Table */}
              <div className="overflow-x-auto border border-slate-800 rounded-xl">
                <table className="w-full text-left text-xs font-mono">
                  <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                      <th className="p-3">Student Details</th>
                      <th className="p-3 text-center">Attendance Status</th>
                      <th className="p-3">Arrival / Notes</th>
                      <th className="p-3">TVET Practical Observation</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800 text-slate-300">
                    {filteredRecords.map((rec) => (
                      <tr key={rec.student_profile_id} className="hover:bg-slate-850/40">
                        <td className="p-3">
                          <span className="text-white font-bold block">{rec.first_name} {rec.last_name}</span>
                          <span className="text-teal-400 font-mono text-[10px] block mt-0.5">{rec.index_number}</span>
                        </td>

                        {/* Status Button Toggle Group */}
                        <td className="p-3 text-center whitespace-nowrap">
                          <div className="inline-flex gap-1 p-1 bg-slate-950 rounded-xl border border-slate-800">
                            <button
                              type="button"
                              onClick={() => handleStatusChange(rec.student_profile_id, "present")}
                              className={`px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer ${
                                rec.status === "present"
                                  ? "bg-emerald-500 text-slate-950 shadow-sm"
                                  : "text-slate-400 hover:text-white"
                              }`}
                            >
                              Present
                            </button>
                            <button
                              type="button"
                              onClick={() => handleStatusChange(rec.student_profile_id, "late")}
                              className={`px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer ${
                                rec.status === "late"
                                  ? "bg-amber-500 text-slate-950 shadow-sm"
                                  : "text-slate-400 hover:text-white"
                              }`}
                            >
                              Late
                            </button>
                            <button
                              type="button"
                              onClick={() => handleStatusChange(rec.student_profile_id, "excused")}
                              className={`px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer ${
                                rec.status === "excused"
                                  ? "bg-sky-500 text-slate-950 shadow-sm"
                                  : "text-slate-400 hover:text-white"
                              }`}
                            >
                              Excused
                            </button>
                            <button
                              type="button"
                              onClick={() => handleStatusChange(rec.student_profile_id, "absent")}
                              className={`px-2.5 py-1 text-[10px] font-bold rounded-lg transition-all cursor-pointer ${
                                rec.status === "absent"
                                  ? "bg-rose-500 text-slate-950 shadow-sm"
                                  : "text-slate-400 hover:text-white"
                              }`}
                            >
                              Absent
                            </button>
                          </div>
                        </td>

                        <td className="p-3 max-w-xs space-y-1">
                          {rec.status === "late" && (
                            <input
                              type="text"
                              placeholder="Arrival time e.g. 08:45"
                              value={rec.arrival_time || ""}
                              onChange={(e) => handleRecordFieldChange(rec.student_profile_id, "arrival_time", e.target.value)}
                              className="w-full bg-slate-950 border border-slate-800 text-white text-[11px] px-2 py-1 rounded font-mono focus:border-teal-500 outline-none"
                            />
                          )}
                          {rec.status === "excused" && (
                            <input
                              type="text"
                              placeholder="Excuse reason / permit #"
                              value={rec.excuse_reason || ""}
                              onChange={(e) => handleRecordFieldChange(rec.student_profile_id, "excuse_reason", e.target.value)}
                              className="w-full bg-slate-950 border border-slate-800 text-white text-[11px] px-2 py-1 rounded font-mono focus:border-teal-500 outline-none"
                            />
                          )}
                          <input
                            type="text"
                            placeholder="Lecturer notes..."
                            value={rec.lecturer_notes || ""}
                            onChange={(e) => handleRecordFieldChange(rec.student_profile_id, "lecturer_notes", e.target.value)}
                            className="w-full bg-slate-950 border border-slate-800 text-slate-300 text-[11px] px-2 py-1 rounded font-sans focus:border-teal-500 outline-none"
                          />
                        </td>

                        <td className="p-3 max-w-xs">
                          <input
                            type="text"
                            placeholder="CBET practical observation..."
                            value={rec.practical_competency_obs || ""}
                            onChange={(e) => handleRecordFieldChange(rec.student_profile_id, "practical_competency_obs", e.target.value)}
                            className="w-full bg-slate-950 border border-slate-800 text-teal-300 text-[11px] px-2 py-1 rounded font-sans focus:border-teal-500 outline-none"
                          />
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <div className="p-12 bg-slate-900 border border-slate-800 rounded-2xl text-center text-slate-400">
              <span>No active attendance sessions for this course offering. Click "Create New Attendance Session" above to start.</span>
            </div>
          )}
        </div>
      )}

      {/* ========================================================================= */}
      {/* SUB-TAB 3: SESSION LEDGER (LECTURER / ADMIN VIEW)                          */}
      {/* ========================================================================= */}
      {activeSubTab === "sessions" && (
        <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4" id="sessions-ledger-view">
          <div className="flex justify-between items-center">
            <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
              <FileText className="w-4 h-4 text-teal-400" />
              Attendance Sessions Master Ledger
            </h3>

            <button
              onClick={() => setShowCreateModal(true)}
              className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 cursor-pointer"
            >
              <Plus className="w-4 h-4" />
              <span>New Session</span>
            </button>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-xs font-mono">
              <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                <tr>
                  <th className="p-3">Session ID</th>
                  <th className="p-3">Date & Time</th>
                  <th className="p-3">Course & Class</th>
                  <th className="p-3">Session Type</th>
                  <th className="p-3">Topic / Facility</th>
                  <th className="p-3">Attendance</th>
                  <th className="p-3">Action</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800 text-slate-300">
                {sessions.map((s) => (
                  <tr key={s.id} className="hover:bg-slate-850/50">
                    <td className="p-3 text-teal-400 font-bold">#{s.id}</td>
                    <td className="p-3 whitespace-nowrap">
                      <span className="text-white font-bold block">{s.session_date}</span>
                      <span className="text-[10px] text-slate-400 block">{s.start_time} - {s.end_time}</span>
                    </td>
                    <td className="p-3">
                      <span className="text-white font-bold block">{s.unit_code} - {s.unit_title}</span>
                      <span className="text-[10px] text-slate-400 block">{s.class_name}</span>
                    </td>
                    <td className="p-3 whitespace-nowrap">
                      <span className={`text-[9px] uppercase px-2 py-0.5 rounded font-bold ${
                        s.session_type === "practical" || s.session_type === "workshop"
                          ? "bg-teal-500/10 text-teal-300 border border-teal-500/20"
                          : "bg-sky-500/10 text-sky-300 border border-sky-500/20"
                      }`}>
                        {s.session_type} ({s.session_type === "practical" ? `${s.practical_hours}h` : `${s.theory_hours}h`})
                      </span>
                    </td>
                    <td className="p-3 max-w-xs">
                      <span className="text-slate-200 font-sans block text-xs">{s.topic}</span>
                      <span className="text-slate-400 text-[10px] block font-mono">{s.facility_equipment || "Classroom"}</span>
                    </td>
                    <td className="p-3 whitespace-nowrap text-teal-400 font-bold">
                      {s.present_count || 0} / {s.total_records || 0} Present
                    </td>
                    <td className="p-3 whitespace-nowrap">
                      <button
                        onClick={() => {
                          setSelectedSessionId(s.id);
                          loadSessionDetails(s.id);
                          setActiveSubTab("marking");
                        }}
                        className="bg-slate-800 hover:bg-slate-700 text-teal-300 px-2.5 py-1 rounded text-[11px] font-bold cursor-pointer"
                      >
                        Open Register
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* SUB-TAB 4: DEPARTMENT REPORTS & WARNINGS (HOD / ADMIN VIEW)                */}
      {/* ========================================================================= */}
      {activeSubTab === "department_report" && (
        <div className="space-y-6" id="department-report-view">
          {loadingReport ? (
            <div className="p-12 text-center text-slate-400 flex flex-col items-center gap-2">
              <RefreshCw className="w-6 h-6 animate-spin text-teal-400" />
              <span>Generating department attendance analytics & warning alerts...</span>
            </div>
          ) : deptReport ? (
            <>
              {/* Department High-level Stats */}
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <span className="text-xs text-slate-400 font-medium block">Total Department Cohorts</span>
                  <span className="text-3xl font-black text-white mt-1 block">{deptReport.total_students}</span>
                  <span className="text-[11px] text-slate-400 mt-2 block">Active TVET Enrolled Students</span>
                </div>

                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <span className="text-xs text-slate-400 font-medium block">Critical Attendance Alerts</span>
                  <span className="text-3xl font-black text-rose-400 mt-1 block">{deptReport.critical_count}</span>
                  <span className="text-[11px] text-rose-400/80 mt-2 block">Below 60% Exam Eligibility</span>
                </div>

                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <span className="text-xs text-slate-400 font-medium block">Warning Alerts</span>
                  <span className="text-3xl font-black text-amber-400 mt-1 block">{deptReport.warning_count}</span>
                  <span className="text-[11px] text-amber-400/80 mt-2 block">Between 60% - 74% Threshold</span>
                </div>

                <div className="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                  <span className="text-xs text-slate-400 font-medium block">Total Practical Hours</span>
                  <span className="text-3xl font-black text-teal-400 mt-1 block">{deptReport.total_practical_hours} <span className="text-xs text-slate-400">hrs</span></span>
                  <span className="text-[11px] text-slate-400 mt-2 block">Logged Supervised Workshop Hours</span>
                </div>
              </div>

              {/* Roster & Warnings Table */}
              <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
                <div className="flex flex-col sm:flex-row justify-between items-center gap-3">
                  <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <BarChart2 className="w-4 h-4 text-teal-400" />
                    Department Student Attendance Summary
                  </h3>

                  <div className="flex items-center gap-2 w-full sm:w-auto">
                    <input
                      type="text"
                      placeholder="Filter student..."
                      value={reportSearch}
                      onChange={(e) => setReportSearch(e.target.value)}
                      className="bg-slate-950 border border-slate-800 text-white text-xs px-3 py-1.5 rounded-xl font-mono focus:border-teal-500 outline-none w-48"
                    />

                    <select
                      value={warningFilter}
                      onChange={(e) => setWarningFilter(e.target.value as any)}
                      className="bg-slate-950 border border-slate-800 text-white text-xs px-3 py-1.5 rounded-xl font-mono focus:border-teal-500 outline-none"
                    >
                      <option value="all">All Levels</option>
                      <option value="critical">Critical (&lt; 60%)</option>
                      <option value="warning">Warning (60-74%)</option>
                      <option value="normal">Normal (&ge; 75%)</option>
                    </select>
                  </div>
                </div>

                <div className="overflow-x-auto">
                  <table className="w-full text-left text-xs font-mono">
                    <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                      <tr>
                        <th className="p-3">Index / Registration</th>
                        <th className="p-3">Student Name</th>
                        <th className="p-3">Class & Program</th>
                        <th className="p-3">Attendance %</th>
                        <th className="p-3">Practical Hours</th>
                        <th className="p-3">Warning Status</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800 text-slate-300">
                      {deptReport.students
                        .filter(s => {
                          const searchMatch = `${s.first_name} ${s.last_name} ${s.index_number}`.toLowerCase().includes(reportSearch.toLowerCase());
                          const warningMatch = warningFilter === "all" || s.attendance_summary.warning_level === warningFilter;
                          return searchMatch && warningMatch;
                        })
                        .map((st) => (
                          <tr key={st.student_profile_id} className="hover:bg-slate-850/50">
                            <td className="p-3 text-teal-400 font-bold">{st.index_number}</td>
                            <td className="p-3 text-white font-bold">{st.first_name} {st.last_name}</td>
                            <td className="p-3">
                              <span className="text-slate-200 block">{st.class_name}</span>
                              <span className="text-[10px] text-slate-400 block">{st.program_name}</span>
                            </td>
                            <td className="p-3 text-white font-bold text-sm">
                              {st.attendance_summary.attendance_percentage}%
                            </td>
                            <td className="p-3 text-teal-400 font-bold">
                              {st.attendance_summary.total_practical_hours} hrs
                            </td>
                            <td className="p-3">
                              <span className={`text-[10px] uppercase font-bold px-2.5 py-1 rounded font-mono ${
                                st.attendance_summary.warning_level === "normal"
                                  ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                                  : st.attendance_summary.warning_level === "warning"
                                  ? "bg-amber-500/20 text-amber-300 border border-amber-500/30"
                                  : "bg-rose-500/20 text-rose-300 border border-rose-500/30"
                              }`}>
                                {st.attendance_summary.warning_level}
                              </span>
                            </td>
                          </tr>
                        ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </>
          ) : (
            <div className="p-8 text-center text-slate-400">
              <span>Department attendance data unavailable.</span>
            </div>
          )}
        </div>
      )}

      {/* ========================================================================= */}
      {/* CREATE SESSION MODAL                                                      */}
      {/* ========================================================================= */}
      <AnimatePresence>
        {showCreateModal && (
          <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <motion.div initial={{ scale: 0.95, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} exit={{ scale: 0.95, opacity: 0 }} className="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-2xl">
              <div className="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 className="text-base font-bold text-white flex items-center gap-2">
                  <Calendar className="w-5 h-5 text-teal-400" />
                  Create New Attendance Session
                </h3>
                <button onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-white p-1">
                  <XCircle className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleCreateSession} className="space-y-4 text-xs font-mono">
                <div>
                  <label className="text-slate-400 uppercase block mb-1">Course Offering</label>
                  <select
                    value={newSessionData.course_offering_id}
                    onChange={(e) => setNewSessionData({ ...newSessionData, course_offering_id: Number(e.target.value) })}
                    className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                  >
                    {courseOfferings.map(c => (
                      <option key={c.id} value={c.id}>
                        {c.unit_code} - {c.unit_title} ({c.class_code})
                      </option>
                    ))}
                  </select>
                </div>

                <div className="grid grid-cols-3 gap-3">
                  <div>
                    <label className="text-slate-400 uppercase block mb-1">Session Date</label>
                    <input
                      type="date"
                      value={newSessionData.session_date}
                      onChange={(e) => setNewSessionData({ ...newSessionData, session_date: e.target.value })}
                      className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                      required
                    />
                  </div>
                  <div>
                    <label className="text-slate-400 uppercase block mb-1">Start Time</label>
                    <input
                      type="text"
                      value={newSessionData.start_time}
                      onChange={(e) => setNewSessionData({ ...newSessionData, start_time: e.target.value })}
                      className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                      placeholder="08:30"
                      required
                    />
                  </div>
                  <div>
                    <label className="text-slate-400 uppercase block mb-1">End Time</label>
                    <input
                      type="text"
                      value={newSessionData.end_time}
                      onChange={(e) => setNewSessionData({ ...newSessionData, end_time: e.target.value })}
                      className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                      placeholder="11:30"
                      required
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="text-slate-400 uppercase block mb-1">Session Type</label>
                    <select
                      value={newSessionData.session_type}
                      onChange={(e) => setNewSessionData({ ...newSessionData, session_type: e.target.value as any })}
                      className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                    >
                      <option value="practical">Practical</option>
                      <option value="workshop">Workshop</option>
                      <option value="laboratory">Laboratory</option>
                      <option value="theory">Theory Lecture</option>
                      <option value="fieldwork">Fieldwork</option>
                      <option value="examination">Examination</option>
                    </select>
                  </div>

                  <div>
                    <label className="text-slate-400 uppercase block mb-1">Supervised Hours</label>
                    <input
                      type="number"
                      step="0.5"
                      value={newSessionData.session_type === "practical" || newSessionData.session_type === "workshop" || newSessionData.session_type === "laboratory" ? newSessionData.practical_hours : newSessionData.theory_hours}
                      onChange={(e) => {
                        const h = parseFloat(e.target.value) || 0;
                        if (newSessionData.session_type === "practical" || newSessionData.session_type === "workshop" || newSessionData.session_type === "laboratory") {
                          setNewSessionData({ ...newSessionData, practical_hours: h, theory_hours: 0 });
                        } else {
                          setNewSessionData({ ...newSessionData, theory_hours: h, practical_hours: 0 });
                        }
                      }}
                      className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="text-slate-400 uppercase block mb-1">Session Topic / Lesson Focus</label>
                  <input
                    type="text"
                    placeholder="e.g. EFI Engine Oscilloscope Diagnostics & Testing"
                    value={newSessionData.topic}
                    onChange={(e) => setNewSessionData({ ...newSessionData, topic: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                    required
                  />
                </div>

                <div>
                  <label className="text-slate-400 uppercase block mb-1">Facility / Equipment Used</label>
                  <input
                    type="text"
                    placeholder="e.g. Automotive Bay 3 - Launch X431 Pro Scanner"
                    value={newSessionData.facility_equipment}
                    onChange={(e) => setNewSessionData({ ...newSessionData, facility_equipment: e.target.value })}
                    className="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2 outline-none focus:border-teal-500"
                  />
                </div>

                <div className="pt-2 flex justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => setShowCreateModal(false)}
                    className="bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold px-4 py-2 rounded-xl"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={creatingSession}
                    className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-5 py-2 rounded-xl flex items-center gap-2 cursor-pointer disabled:opacity-50"
                  >
                    {creatingSession ? <RefreshCw className="w-4 h-4 animate-spin" /> : <CheckCircle className="w-4 h-4" />}
                    <span>Create Session</span>
                  </button>
                </div>
              </form>
            </motion.div>
          </div>
        )}
      </AnimatePresence>
    </motion.div>
  );
}

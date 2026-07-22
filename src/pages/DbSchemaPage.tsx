import React from "react";
import { Database, Info } from "lucide-react";
import { motion } from "motion/react";
import { useLms } from "../context/LmsContext";

export default function DbSchemaPage() {
  const {
    answers,
    dbViewMode,
    setDbViewMode,
    selectedDbTable,
    setSelectedDbTable
  } = useLms();

  return (
    <motion.div 
      key="db-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="grid grid-cols-1 lg:grid-cols-12 gap-6"
    >
      {/* Relational Database Catalog List - 5 columns */}
      <div className="lg:col-span-5 flex flex-col gap-6" id="db-catalog-panel">
        <div className="bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl flex flex-col h-full">
          <div className="border-b border-slate-855 pb-3 mb-4">
            <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
              <Database className="w-4 h-4 text-teal-400" />
              Relational Database Tables
            </h3>
            <p className="text-xs text-slate-400 mt-1">
              Select a normalized table below to inspect its attributes, relationships, or SQL DDL migrations.
            </p>
          </div>

          <div className="flex flex-col gap-3 overflow-y-auto max-h-[550px] pr-1.5">
            {[
              { name: "users", desc: "Core institutional users (Students, Lecturers, Registrars, HODs, Accountants)", fields: 10, key: "PK: id | FK: role_id", category: "security", isImplemented: true },
              { name: "roles", desc: "Role profiles enforcing rigid system authorization segregation", fields: 5, key: "PK: id", category: "security", isImplemented: true },
              { name: "permissions", desc: "Granular access capability flags referenced by authentication models", fields: 5, key: "PK: id", category: "security", isImplemented: true },
              { name: "role_permissions", desc: "Many-to-many lookup table tying specific permissions to roles", fields: 2, key: "Composite PK: (role_id, permission_id)", category: "security", isImplemented: true },
              { name: "departments", desc: "Academic college subdivisions (e.g., Automotive, ICT, Business)", fields: 4, key: "PK: id", category: "academic", isImplemented: true },
              { name: "user_departments", desc: "User department scoping linkages", fields: 3, key: "Composite PK: (user_id, department_id)", category: "security", isImplemented: true },
              { name: "audit_logs", desc: "Administrative operations ledger recording security & grading changes", fields: 6, key: "PK: id | FK: user_id", category: "security", isImplemented: true },
              { name: "login_attempts", desc: "Brute force tracking table for IP & email rate limiting", fields: 5, key: "PK: id", category: "security", isImplemented: true },
              { name: "sessions", desc: "Secure server-side database session persistence", fields: 5, key: "PK: id | FK: user_id", category: "security", isImplemented: true },
              { name: "academic_years", desc: "College years cycle labels (e.g., '2025/2026') and active states", fields: 6, key: "PK: id", category: "academic", isImplemented: true },
              { name: "intakes", desc: "Three annual intakes per year: January, May, and September cohorts", fields: 7, key: "PK: id | FK: academic_year_id", category: "academic", isImplemented: true },
              { name: "programs", desc: "College courses offered with specific TVET modular training levels", fields: 6, key: "PK: id | FK: department_id", category: "academic", isImplemented: true },
              { name: "classes", desc: "Student cohorts tied to program and intake cycle", fields: 8, key: "PK: id | FK: program_id, intake_id", category: "academic", isImplemented: true },
              { name: "units", desc: "Course syllabus units mapped to departments and programs", fields: 7, key: "PK: id | FK: department_id", category: "academic", isImplemented: true },
              { name: "course_offerings", desc: "Active class offerings assigned to primary lecturers", fields: 7, key: "PK: id | FK: unit_id, class_id, lecturer_id", category: "academic", isImplemented: true },
              { name: "course_modules", desc: "Structured learning modules grouped inside course offerings", fields: 6, key: "PK: id | FK: course_offering_id", category: "academic", isImplemented: true },
              { name: "lessons", desc: "Sequential text, video, and document lessons inside course modules", fields: 9, key: "PK: id | FK: module_id", category: "academic", isImplemented: true },
              { name: "learning_materials", desc: "Securely uploaded PDF/office files and external learning URLs", fields: 8, key: "PK: id | FK: lesson_id", category: "academic", isImplemented: true },
              { name: "student_lesson_progress", desc: "Per-student lesson completion timestamps and duration tracker", fields: 6, key: "PK: id | FK: student_id, lesson_id", category: "academic", isImplemented: true },
              { name: "student_profiles", desc: "Student bio profile records, index numbers, and guardian details", fields: 10, key: "PK: id | FK: user_id", category: "academic", isImplemented: true },
              { name: "student_enrollments", desc: "Student cohort enrollment tracking ledger and academic status", fields: 8, key: "PK: id | FK: student_id, class_id", category: "academic", isImplemented: true },
              { name: "staff_profiles", desc: "Lecturer and staff faculty records", fields: 5, key: "PK: id | FK: user_id, department_id", category: "academic", isImplemented: true },
              { name: "attendance_sessions", desc: "Attendance register sessions (Theory, Practical, Workshop, Lab, Exam) with supervised hours", fields: 13, key: "PK: id | FK: course_offering_id, class_id, lecturer_id", category: "activity", isImplemented: true },
              { name: "attendance_records", desc: "Per-student attendance records (Present, Late, Absent, Excused) with TVET practical competency observations", fields: 9, key: "PK: id | FK: attendance_session_id, student_id", category: "activity", isImplemented: true },
              { name: "assignments", desc: "Practical briefs, instructions, release/due dates, and late submission flags", fields: 10, key: "PK: id | FK: course_offering_id", category: "activity", isImplemented: true },
              { name: "assignment_submissions", desc: "Student portfolio uploads, submission text, late status, and awarded marks", fields: 12, key: "PK: id | FK: assignment_id, student_id", category: "activity", isImplemented: true },
              { name: "quizzes", desc: "Online assessment quizzes with time limits, passing %, and availability windows", fields: 11, key: "PK: id | FK: course_offering_id", category: "activity", isImplemented: true },
              { name: "quiz_questions", desc: "Quiz questions with question types and mark weightings", fields: 6, key: "PK: id | FK: quiz_id", category: "activity", isImplemented: true },
              { name: "quiz_options", desc: "Multiple choice options with server-side answer keys", fields: 5, key: "PK: id | FK: question_id", category: "activity", isImplemented: true },
              { name: "quiz_attempts", desc: "Student quiz attempts with server-evaluated score and status", fields: 10, key: "PK: id | FK: quiz_id, student_id", category: "activity", isImplemented: true },
              { name: "quiz_responses", desc: "Itemized student responses to quiz questions with awarded marks", fields: 7, key: "PK: id | FK: quiz_attempt_id, question_id", category: "activity", isImplemented: true },
              { name: "student_course_grades", desc: "Final grade ledger combining coursework, exam, letter grade, and TVET CBET outcome", fields: 11, key: "PK: id | FK: student_id, course_offering_id", category: "activity", isImplemented: true },
              { name: "fee_structures", desc: "Fee structures breakdown by program, academic year, intake, and term", fields: 9, key: "PK: id | FK: program_id, academic_year_id", category: "finance", isImplemented: true },
              { name: "student_fee_accounts", desc: "Student fee accounts balance, billing history, and exam clearance status", fields: 7, key: "PK: id | FK: student_id", category: "finance", isImplemented: true },
              { name: "invoices", desc: "Student invoice ledger with auto-generated invoice numbering and due dates", fields: 8, key: "PK: id | FK: student_id, fee_structure_id", category: "finance", isImplemented: true },
              { name: "payments", desc: "M-Pesa and bank payment receipts tracking and verification workflow", fields: 9, key: "PK: id | FK: student_id, invoice_id", category: "finance", isImplemented: true },
              { name: "system_settings", desc: "Institutional system settings, clearance thresholds, and configurations", fields: 6, key: "PK: id | Unique: setting_key", category: "security", isImplemented: true }
            ].map((table) => {
              const isSelected = selectedDbTable === table.name;
              
              let catBadgeColor = "text-teal-400 bg-teal-500/10";
              if (table.category === "academic") catBadgeColor = "text-sky-400 bg-sky-500/10";
              if (table.category === "activity") catBadgeColor = "text-emerald-400 bg-emerald-500/10";

              return (
                <button 
                  key={table.name} 
                  onClick={() => setSelectedDbTable(table.name)}
                  className={`p-3 rounded-xl border transition-all cursor-pointer text-left w-full ${
                    isSelected 
                      ? "bg-slate-900 border-teal-500 shadow-md shadow-teal-500/5 ring-1 ring-teal-500/25" 
                      : "bg-slate-900/60 border-slate-850 hover:border-slate-700 hover:bg-slate-850/40"
                  }`}
                >
                  <div className="flex justify-between items-center flex-wrap gap-1">
                    <span className={`font-mono text-xs font-bold ${isSelected ? "text-teal-400" : "text-white"}`}>{table.name}</span>
                    <div className="flex items-center gap-1.5">
                      <span className={`text-[9px] uppercase px-1.5 py-0.5 rounded-full font-mono font-bold ${
                        table.isImplemented ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" : "bg-slate-800 text-slate-400 border border-slate-700"
                      }`}>
                        {table.isImplemented ? "REAL SCHEMA" : "PLANNED (PHASE 6+)"}
                      </span>
                      <span className="text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 font-mono">{table.fields} Fields</span>
                    </div>
                  </div>
                  <p className="text-[11px] text-slate-300 mt-1 leading-snug">{table.desc}</p>
                  <span className="text-[9px] text-slate-500 font-mono block mt-1.5">{table.key}</span>
                </button>
              );
            })}
          </div>
        </div>
      </div>

      {/* MySQL Visual Schema Generator - 7 columns */}
      <div className="lg:col-span-7 flex flex-col gap-4" id="db-visual-designer">
        <div className="bg-slate-950 rounded-2xl border border-slate-800 p-6 shadow-xl flex-1 flex flex-col">
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-850 pb-4 mb-4 gap-4">
            <div>
              <h3 className="text-sm font-bold text-white flex items-center gap-1.5">
                {dbViewMode === "erd" ? "Entity Relationship Diagram (ERD)" : "MySQL / MariaDB DDL Schema"}
              </h3>
              <p className="text-xs text-slate-400 mt-1">
                {dbViewMode === "erd" 
                  ? "Showing normalized schema mapping & dependencies. Selected table is highlighted." 
                  : `Strict InnoDB DDL statements for table: ${selectedDbTable}`}
              </p>
            </div>

            {/* View Mode Toggle Control */}
            <div className="flex gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800 self-end md:self-auto">
              <button
                onClick={() => setDbViewMode("erd")}
                className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer ${
                  dbViewMode === "erd" 
                    ? "bg-teal-555 text-white" 
                    : "text-slate-400 hover:text-white"
                }`}
              >
                Visual ERD Map
              </button>
              <button
                onClick={() => setDbViewMode("ddl")}
                className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer ${
                  dbViewMode === "ddl" 
                    ? "bg-teal-555 text-white" 
                    : "text-slate-400 hover:text-white"
                }`}
              >
                SQL DDL Migration
              </button>
            </div>
          </div>

          {/* SCHEMA VIEWPORT */}
          <div className="flex-1 min-h-[450px]">
            {dbViewMode === "erd" ? (
              /* Visual ERD Map Canvas Mock */
              <div className="bg-slate-900 rounded-xl border border-slate-800 p-4 font-mono text-[11px] flex flex-col gap-6 overflow-y-auto max-h-[500px]">
                {/* Section 1: RBAC Security Stack */}
                <div className="border-b border-slate-800/60 pb-4">
                  <span className="text-[10px] text-teal-400 uppercase tracking-wider font-bold mb-3 block">1. RBAC & Granular Scoping Architecture</span>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* ROLES TABLE Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "roles" ? "border-teal-400 ring-2 ring-teal-500/20 scale-[1.01]" : "border-slate-800"}`}>
                      <div className="bg-slate-800/80 px-3 py-1.5 text-xs font-bold text-white border-b border-slate-800 flex justify-between items-center">
                        <span className="flex items-center gap-1">🔑 roles</span>
                        <span className="text-[9px] text-slate-400 bg-slate-900 px-1.5 py-0.2 rounded font-normal font-sans">RBAC</span>
                      </div>
                      <div className="p-2.5 flex flex-col gap-1.5">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT AUTO_INC</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>⚠️ role_name</span>
                          <span>VARCHAR(50)</span>
                        </div>
                        <div className="flex justify-between text-slate-400">
                          <span>description</span>
                          <span>VARCHAR(255)</span>
                        </div>
                      </div>
                    </div>

                    {/* PERMISSIONS TABLE Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "permissions" ? "border-teal-400 ring-2 ring-teal-500/20 scale-[1.01]" : "border-slate-800"}`}>
                      <div className="bg-slate-800/80 px-3 py-1.5 text-xs font-bold text-white border-b border-slate-800 flex justify-between items-center">
                        <span className="flex items-center gap-1">🔑 permissions</span>
                        <span className="text-[9px] text-slate-400 bg-slate-900 px-1.5 py-0.2 rounded font-normal font-sans">Extensible</span>
                      </div>
                      <div className="p-2.5 flex flex-col gap-1.5">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT AUTO_INC</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>⚠️ code</span>
                          <span>VARCHAR(100)</span>
                        </div>
                        <div className="flex justify-between text-slate-400">
                          <span>name</span>
                          <span>VARCHAR(150)</span>
                        </div>
                        <div className="flex justify-between text-slate-500">
                          <span>category</span>
                          <span>VARCHAR(50)</span>
                        </div>
                      </div>
                    </div>

                    {/* ROLE PERMISSIONS JOIN CARD */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "role_permissions" ? "border-teal-400 ring-2 ring-teal-500/20 scale-[1.01]" : "border-slate-800"}`}>
                      <div className="bg-slate-800/80 px-3 py-1.5 text-xs font-bold text-white border-b border-slate-800 flex justify-between items-center">
                        <span className="flex items-center gap-1">🔗 role_permissions</span>
                        <span className="text-[9px] text-slate-400 bg-slate-900 px-1.5 py-0.2 rounded font-normal font-sans">Join Table</span>
                      </div>
                      <div className="p-2.5 flex flex-col gap-1.5">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 role_id</span>
                          <span>INT (FK roles)</span>
                        </div>
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 permission_id</span>
                          <span>INT (FK permissions)</span>
                        </div>
                      </div>
                    </div>

                    {/* USER ASSIGNMENTS SCOPING CARD */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "user_assignments" ? "border-teal-400 ring-2 ring-teal-500/20 scale-[1.01]" : "border-slate-800"}`}>
                      <div className="bg-slate-800/80 px-3 py-1.5 text-xs font-bold text-white border-b border-slate-800 flex justify-between items-center">
                        <span className="flex items-center gap-1">🛡️ user_assignments</span>
                        <span className="text-[9px] text-slate-400 bg-slate-900 px-1.5 py-0.2 rounded font-normal font-sans">Scoping</span>
                      </div>
                      <div className="p-2.5 flex flex-col gap-1.5">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT AUTO_INC</span>
                        </div>
                        <div className="flex justify-between text-slate-350">
                          <span>user_id</span>
                          <span>INT (FK users.id)</span>
                        </div>
                        <div className="flex justify-between text-slate-350">
                          <span>scope_type</span>
                          <span>ENUM('Global', 'Department', ...)</span>
                        </div>
                        <div className="flex justify-between text-slate-500">
                          <span>scope_id</span>
                          <span>INT NULL</span>
                        </div>
                      </div>
                    </div>

                    {/* USERS TABLE Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md md:col-span-2 ${selectedDbTable === "users" ? "border-teal-400 ring-2 ring-teal-500/20 scale-[1.01]" : "border-slate-800"}`}>
                      <div className="bg-slate-800/80 px-3 py-1.5 text-xs font-bold text-white border-b border-slate-800 flex justify-between items-center">
                        <span className="flex items-center gap-1">👤 users</span>
                        <span className="text-[9px] text-slate-400 bg-slate-900 px-1.5 py-0.2 rounded font-normal font-sans">Core Identities</span>
                      </div>
                      <div className="p-2.5 grid grid-cols-1 md:grid-cols-2 gap-2 text-[11px]">
                        <div className="flex flex-col gap-1">
                          <div className="flex justify-between text-teal-400">
                            <span>🔑 id</span>
                            <span>INT AUTO_INC</span>
                          </div>
                          <div className="flex justify-between text-slate-200">
                            <span>⚠️ username</span>
                            <span>VARCHAR(50)</span>
                          </div>
                          <div className="flex justify-between text-slate-400">
                            <span>password_hash</span>
                            <span>VARCHAR(255)</span>
                          </div>
                        </div>
                        <div className="flex flex-col gap-1">
                          <div className="flex justify-between text-slate-200">
                            <span>role_id</span>
                            <span className="text-teal-400">INT (FK roles.id)</span>
                          </div>
                          <div className="flex justify-between text-slate-450">
                            <span>status</span>
                            <span>ENUM('Active', 'Inactive'...)</span>
                          </div>
                          <div className="flex justify-between text-slate-500">
                            <span>full_name</span>
                            <span>VARCHAR(100)</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Section 2: Academic Program Hierarchy */}
                <div className="border-b border-slate-800/60 pb-4">
                  <span className="text-[10px] text-sky-400 uppercase tracking-wider font-bold mb-3 block">2. Academic & TVET Intake Taxonomy</span>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {/* ACADEMIC YEARS Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "academic_years" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                      <div className="bg-slate-800/60 px-2.5 py-1 text-[10px] font-bold text-white border-b border-slate-800 flex justify-between">
                        <span>academic_years</span>
                      </div>
                      <div className="p-2 flex flex-col gap-1 text-[10px]">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>⚠️ year_label</span>
                          <span>VARCHAR(9)</span>
                        </div>
                        <div className="flex justify-between text-slate-450">
                          <span>status</span>
                          <span>ENUM</span>
                        </div>
                      </div>
                    </div>

                    {/* INTAKES Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "intakes" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                      <div className="bg-slate-800/60 px-2.5 py-1 text-[10px] font-bold text-white border-b border-slate-800 flex justify-between">
                        <span>intakes</span>
                      </div>
                      <div className="p-2 flex flex-col gap-1 text-[10px]">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT</span>
                        </div>
                        <div className="flex justify-between text-slate-350">
                          <span>academic_year_id</span>
                          <span className="text-teal-400">FK</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>name</span>
                          <span>VARCHAR</span>
                        </div>
                      </div>
                    </div>

                    {/* DEPARTMENTS CARD */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "departments" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                      <div className="bg-slate-800/60 px-2.5 py-1 text-[10px] font-bold text-white border-b border-slate-800 flex justify-between">
                        <span>departments</span>
                      </div>
                      <div className="p-2 flex flex-col gap-1 text-[10px]">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>code</span>
                          <span>VARCHAR</span>
                        </div>
                        <div className="flex justify-between text-slate-400">
                          <span>name</span>
                          <span>VARCHAR(100)</span>
                        </div>
                      </div>
                    </div>

                    {/* PROGRAMS Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "programs" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                      <div className="bg-slate-800/60 px-2.5 py-1 text-[10px] font-bold text-white border-b border-slate-800 flex justify-between">
                        <span>programs</span>
                      </div>
                      <div className="p-2 flex flex-col gap-1 text-[10px]">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT</span>
                        </div>
                        <div className="flex justify-between text-slate-350">
                          <span>department_id</span>
                          <span className="text-teal-400">FK</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>name</span>
                          <span>VARCHAR</span>
                        </div>
                        <div className="flex justify-between text-slate-450">
                          <span>duration_months</span>
                          <span>TINYINT</span>
                        </div>
                      </div>
                    </div>

                    {/* UNITS Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "units" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                      <div className="bg-slate-800/60 px-2.5 py-1 text-[10px] font-bold text-white border-b border-slate-800 flex justify-between">
                        <span>units</span>
                      </div>
                      <div className="p-2 flex flex-col gap-1 text-[10px]">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT</span>
                        </div>
                        <div className="flex justify-between text-slate-350">
                          <span>program_id</span>
                          <span className="text-teal-400">FK</span>
                        </div>
                        <div className="flex justify-between text-slate-200">
                          <span>title</span>
                          <span>VARCHAR</span>
                        </div>
                      </div>
                    </div>

                    {/* LESSONS Card */}
                    <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "lessons" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                      <div className="bg-slate-800/60 px-2.5 py-1 text-[10px] font-bold text-white border-b border-slate-800 flex justify-between">
                        <span>lessons</span>
                      </div>
                      <div className="p-2 flex flex-col gap-1 text-[10px]">
                        <div className="flex justify-between text-teal-400">
                          <span>🔑 id</span>
                          <span>INT</span>
                        </div>
                        <div className="flex justify-between text-slate-350">
                          <span>unit_id</span>
                          <span className="text-teal-400">FK</span>
                        </div>
                        <div className="flex justify-between text-slate-450">
                          <span>title</span>
                          <span>VARCHAR(255)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Section 3: Vocational Portfolios, Fees & Attendance */}
                <div>
                  <span className="text-[10px] text-emerald-400 uppercase tracking-wider font-bold mb-3 block">3. Portfolios, Attendance & Finance Records</span>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Workshop Attendance table conditional render */}
                    {answers.attendance_type === "separate_workshop" && (
                      <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "workshop_attendance" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                        <div className="bg-emerald-950/80 px-3 py-1.5 text-xs font-bold text-emerald-400 border-b border-slate-800 flex justify-between">
                          <span>workshop_attendance</span>
                          <span className="text-[9px] text-emerald-500 font-normal">Active</span>
                        </div>
                        <div className="p-2.5 flex flex-col gap-1.5">
                          <div className="flex justify-between text-teal-400">
                            <span>🔑 id</span>
                            <span>INT AUTO_INC</span>
                          </div>
                          <div className="flex justify-between text-slate-350">
                            <span>student_id</span>
                            <span className="text-teal-400">INT (FK users.id)</span>
                          </div>
                          <div className="flex justify-between text-slate-350">
                            <span>unit_id</span>
                            <span className="text-teal-400">INT (FK units.id)</span>
                          </div>
                          <div className="flex justify-between text-slate-400">
                            <span>hours_completed</span>
                            <span>TINYINT</span>
                          </div>
                          <div className="flex justify-between text-slate-500">
                            <span>supervisor_id</span>
                            <span>INT (FK users.id)</span>
                          </div>
                        </div>
                      </div>
                    )}

                    {/* Fee records table conditional render */}
                    {answers.fees_integration === "display_only" && (
                      <div className={`bg-slate-950 border rounded-lg overflow-hidden transition-all shadow-md ${selectedDbTable === "fee_records" ? "border-teal-400 ring-2 ring-teal-500/20" : "border-slate-800"}`}>
                        <div className="bg-slate-800 px-3 py-1 text-xs font-bold text-white border-b border-slate-800 flex justify-between">
                          <span>fee_records</span>
                        </div>
                        <div className="p-2.5 flex flex-col gap-1.5">
                          <div className="flex justify-between text-teal-400">
                            <span>🔑 id</span>
                            <span>INT AUTO_INC</span>
                          </div>
                          <div className="flex justify-between text-slate-350">
                            <span>student_id</span>
                            <span className="text-teal-400">INT (FK users.id)</span>
                          </div>
                          <div className="flex justify-between text-slate-400">
                            <span>total_due</span>
                            <span>DECIMAL(10,2)</span>
                          </div>
                          <div className="flex justify-between text-emerald-450">
                            <span>paid_amount</span>
                            <span>DECIMAL(10,2)</span>
                          </div>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            ) : (
              /* SQL DDL Code View with custom highlighted syntax styling */
              <div className="flex flex-col gap-3 h-full">
                <pre className="flex-1 bg-slate-900 p-4 rounded-xl border border-slate-850 text-xs text-slate-300 font-mono overflow-x-auto leading-relaxed max-h-[500px] text-left">
                  <code className="block text-slate-400">
                    {`-- ====================================================================
-- GILGIL TECHNICAL & VOCATIONAL COLLEGE (GILGIL TVC) LMS CORE DB SCHEMA
-- Compatible with MySQL 8.0+ & MariaDB v10.5+ (Standard Shared Hosting)
-- Strict RBAC with Extensible Granular Permissions & Access Scoping
-- ====================================================================\n\n`}
                    
                    <span className="text-amber-400">-- -----------------------------------------------------\n</span>
                    <span className="text-amber-400">-- Table structure for: {selectedDbTable}\n</span>
                    <span className="text-amber-400">-- -----------------------------------------------------\n</span>

                    {selectedDbTable === "users" && (
                      <span>
                        <span className="text-teal-400">CREATE TABLE</span> <span className="text-white">`users`</span> (<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">AUTO_INCREMENT PRIMARY KEY</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `username`</span> <span className="text-sky-400">VARCHAR(50)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `password_hash`</span> <span className="text-sky-400">VARCHAR(255)</span> <span className="text-teal-400">NOT NULL</span>, <span className="text-slate-505">-- Bcrypt hash</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `email`</span> <span className="text-sky-400">VARCHAR(100)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `full_name`</span> <span className="text-sky-400">VARCHAR(100)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `phone`</span> <span className="text-sky-400">VARCHAR(20)</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `role_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `status`</span> <span className="text-sky-400">ENUM</span>(<span className="text-emerald-400">'Active', 'Inactive', 'Suspended'</span>) <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">'Active'</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `created_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `updated_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_users_role`</span> <span className="text-teal-400">FOREIGN KEY</span> (<span className="text-white">`role_id`</span>) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`roles`</span> (<span className="text-white">`id`</span>) <span className="text-teal-400">ON DELETE RESTRICT</span><span className="text-slate-400">{"\n"}</span>
                        ) <span className="text-teal-400">ENGINE</span>=InnoDB <span className="text-teal-400">DEFAULT CHARSET</span>=utf8mb4 <span className="text-teal-400">COLLATE</span>=utf8mb4_unicode_ci;
                      </span>
                    )}

                    {selectedDbTable === "roles" && (
                      <span>
                        <span className="text-teal-400">CREATE TABLE</span> <span className="text-white">`roles`</span> (<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">AUTO_INCREMENT PRIMARY KEY</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `role_name`</span> <span className="text-sky-400">VARCHAR(50)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>, <span className="text-slate-505">-- e.g., 'HOD', 'Registrar', 'Student'</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `description`</span> <span className="text-sky-400">VARCHAR(255)</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `created_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `updated_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP</span><span className="text-slate-400">{"\n"}</span>
                        ) <span className="text-teal-400">ENGINE</span>=InnoDB <span className="text-teal-400">DEFAULT CHARSET</span>=utf8mb4;
                      </span>
                    )}

                    {selectedDbTable === "permissions" && (
                      <span>
                        <span className="text-slate-500">-- EXTENSIBILITY RULE: Add permissions to this table to instantly make them available in RBAC</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-slate-500">-- without modifying table schemas or hard-coding roles.</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">CREATE TABLE</span> <span className="text-white">`permissions`</span> (<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">AUTO_INCREMENT PRIMARY KEY</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `code`</span> <span className="text-sky-400">VARCHAR(100)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>, <span className="text-slate-505">-- e.g., 'approve_syllabus', 'log_workshop_hours'</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `name`</span> <span className="text-sky-400">VARCHAR(150)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `category`</span> <span className="text-sky-400">VARCHAR(50)</span> <span className="text-teal-400">NOT NULL</span>, <span className="text-slate-505">-- e.g., 'Academic', 'Finance', 'Workshop'</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `created_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP</span><span className="text-slate-400">{"\n"}</span>
                        ) <span className="text-teal-400">ENGINE</span>=InnoDB <span className="text-teal-400">DEFAULT CHARSET</span>=utf8mb4;
                      </span>
                    )}

                    {selectedDbTable === "role_permissions" && (
                      <span>
                        <span className="text-teal-400">CREATE TABLE</span> <span className="text-white">`role_permissions`</span> (<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `role_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `permission_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  PRIMARY KEY</span> (`role_id`, `permission_id`),<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_role_perm_role`</span> <span className="text-teal-400">FOREIGN KEY</span> (`role_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`roles`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_role_perm_perm`</span> <span className="text-teal-400">FOREIGN KEY</span> (`permission_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`permissions`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span><span className="text-slate-400">{"\n"}</span>
                        ) <span className="text-teal-400">ENGINE</span>=InnoDB <span className="text-teal-400">DEFAULT CHARSET</span>=utf8mb4;
                      </span>
                    )}

                    {selectedDbTable === "user_assignments" && (
                      <span>
                        <span className="text-slate-550">-- SCOPING DESIGN: restricts access by organizational hierarchy.</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-slate-550">-- An HOD assigned to department 3 cannot modify syllabi in department 4.</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">CREATE TABLE</span> <span className="text-white">`user_assignments`</span> (<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">AUTO_INCREMENT PRIMARY KEY</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `user_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `scope_type`</span> <span className="text-sky-400">ENUM</span>(<span className="text-emerald-400">'Global', 'Department', 'Program', 'Class', 'Course'</span>) <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">'Department'</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `scope_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NULL</span>, <span className="text-slate-505">-- e.g., department_id or unit_id</span><span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `assigned_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP</span>,<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  UNIQUE KEY</span> <span className="text-white">`idx_user_scope`</span> (`user_id`, `scope_type`, `scope_id`),<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_user_assign_user`</span> <span className="text-teal-400">FOREIGN KEY</span> (`user_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`users`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span><span className="text-slate-400">{"\n"}</span>
                        ) <span className="text-teal-400">ENGINE</span>=InnoDB <span className="text-teal-400">DEFAULT CHARSET</span>=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                      </span>
                    )}

                    {!["users", "roles", "permissions", "role_permissions", "user_assignments"].includes(selectedDbTable) && (
                      <span>
                        <span className="text-teal-400">CREATE TABLE</span> <span className="text-white">`{selectedDbTable}`</span> (<span className="text-slate-400">{"\n"}</span>
                        <span className="text-teal-400">  `id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">AUTO_INCREMENT PRIMARY KEY</span>,<span className="text-slate-400">{"\n"}</span>
                        {selectedDbTable === "departments" && (
                          <>
                            <span className="text-teal-400">  `name`</span> <span className="text-sky-400">VARCHAR(100)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `code`</span> <span className="text-sky-400">VARCHAR(15)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `description`</span> <span className="text-sky-400">TEXT</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "programs" && (
                          <>
                            <span className="text-teal-400">  `department_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `name`</span> <span className="text-sky-400">VARCHAR(150)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `code`</span> <span className="text-sky-400">VARCHAR(20)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `level`</span> <span className="text-sky-400">ENUM</span>(<span className="text-emerald-400">'Artisan', 'Certificate', 'Diploma'</span>) <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `duration_months`</span> <span className="text-sky-400">TINYINT UNSIGNED</span> <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">24</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_programs_dept`</span> <span className="text-teal-400">FOREIGN KEY</span> (`department_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`departments`</span> (`id`) <span className="text-teal-400">ON DELETE RESTRICT</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "academic_years" && (
                          <>
                            <span className="text-teal-400">  `year_label`</span> <span className="text-sky-400">VARCHAR(9)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>, <span className="text-slate-505">-- e.g., '2025/2026'</span><span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `status`</span> <span className="text-sky-400">ENUM</span>(<span className="text-emerald-400">'Active', 'Completed', 'Upcoming'</span>) <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">'Upcoming'</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "intakes" && (
                          <>
                            <span className="text-teal-400">  `academic_year_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `name`</span> <span className="text-sky-400">VARCHAR(50)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `code`</span> <span className="text-sky-400">VARCHAR(20)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `start_date`</span> <span className="text-sky-400">DATE</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `end_date`</span> <span className="text-sky-400">DATE</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_intakes_year`</span> <span className="text-teal-400">FOREIGN KEY</span> (`academic_year_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`academic_years`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "units" && (
                          <>
                            <span className="text-teal-400">  `program_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `title`</span> <span className="text-sky-400">VARCHAR(150)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `code`</span> <span className="text-sky-400">VARCHAR(20)</span> <span className="text-teal-400">NOT NULL UNIQUE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `theory_weight`</span> <span className="text-sky-400">TINYINT</span> <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">40</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `practical_weight`</span> <span className="text-sky-400">TINYINT</span> <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">60</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_units_program`</span> <span className="text-teal-400">FOREIGN KEY</span> (`program_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`programs`</span> (`id`) <span className="text-teal-400">ON DELETE RESTRICT</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "lessons" && (
                          <>
                            <span className="text-teal-400">  `unit_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `title`</span> <span className="text-sky-400">VARCHAR(255)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `content`</span> <span className="text-sky-400">LONGTEXT</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `video_url`</span> <span className="text-sky-400">VARCHAR(255)</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_lessons_unit`</span> <span className="text-teal-400">FOREIGN KEY</span> (`unit_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`units`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "workshop_attendance" && (
                          <>
                            <span className="text-teal-400">  `student_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `unit_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `date`</span> <span className="text-sky-400">DATE</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `hours_completed`</span> <span className="text-sky-400">TINYINT</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `supervisor_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_workshop_student`</span> <span className="text-teal-400">FOREIGN KEY</span> (`student_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`users`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_workshop_unit`</span> <span className="text-teal-400">FOREIGN KEY</span> (`unit_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`units`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "assignments" && (
                          <>
                            <span className="text-teal-400">  `unit_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `title`</span> <span className="text-sky-400">VARCHAR(255)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `description`</span> <span className="text-sky-400">TEXT</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `max_points`</span> <span className="text-sky-400">TINYINT</span> <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">100</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_assign_unit`</span> <span className="text-teal-400">FOREIGN KEY</span> (`unit_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`units`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "submissions" && (
                          <>
                            <span className="text-teal-400">  `assignment_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `student_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `file_path`</span> <span className="text-sky-400">VARCHAR(255)</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `grade`</span> <span className="text-sky-400">DECIMAL(5,2)</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_sub_assign`</span> <span className="text-teal-400">FOREIGN KEY</span> (`assignment_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`assignments`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_sub_stud`</span> <span className="text-teal-400">FOREIGN KEY</span> (`student_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`users`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "fee_records" && (
                          <>
                            <span className="text-teal-400">  `student_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `total_due`</span> <span className="text-sky-400">DECIMAL(10,2)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `paid_amount`</span> <span className="text-sky-400">DECIMAL(10,2)</span> <span className="text-teal-400">NOT NULL DEFAULT</span> <span className="text-emerald-400">0.00</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_fees_student`</span> <span className="text-teal-400">FOREIGN KEY</span> (`student_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`users`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        {selectedDbTable === "audit_logs" && (
                          <>
                            <span className="text-teal-400">  `user_id`</span> <span className="text-sky-400">INT UNSIGNED</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `action`</span> <span className="text-sky-400">VARCHAR(100)</span> <span className="text-teal-400">NOT NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  `details`</span> <span className="text-sky-400">TEXT</span> <span className="text-teal-400">NULL</span>,<span className="text-slate-400">{"\n"}</span>
                            <span className="text-teal-400">  CONSTRAINT</span> <span className="text-white">`fk_audit_user`</span> <span className="text-teal-400">FOREIGN KEY</span> (`user_id`) <span className="text-teal-400">REFERENCES</span> <span className="text-white">`users`</span> (`id`) <span className="text-teal-400">ON DELETE CASCADE</span>,<span className="text-slate-400">{"\n"}</span>
                          </>
                        )}
                        <span className="text-teal-400">  `created_at`</span> <span className="text-sky-400">TIMESTAMP</span> <span className="text-teal-400">DEFAULT CURRENT_TIMESTAMP</span><span className="text-slate-400">{"\n"}</span>
                        ) <span className="text-teal-400">ENGINE</span>=InnoDB <span className="text-teal-400">DEFAULT CHARSET</span>=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                      </span>
                    )}
                    <span className="text-slate-400">{"\n"}</span>
                  </code>
                </pre>

                <div className="bg-slate-900 border border-slate-800 rounded-xl p-3 text-[11px] text-slate-400 text-left">
                  <span className="text-teal-400 block font-bold font-mono">🚀 Extensibility Highlight</span>
                  To add a new permission (e.g. <code className="text-white font-mono bg-slate-950 px-1 rounded">view_financial_statistics</code>) in the future, HOD or ICT admins only insert a row into the <code className="text-teal-300 font-mono">permissions</code> table and link it to the target role in <code className="text-teal-300 font-mono">role_permissions</code>. The access is instantly refreshed across all associated users without any database structural migrations!
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </motion.div>
  );
}

import React from "react";
import { ChevronRight, Plus, Activity, DollarSign } from "lucide-react";
import { motion } from "motion/react";
import { useLms } from "../context/LmsContext";

export default function SimulationPage() {
  const {
    answers,
    simRole,
    setSimRole,
    lessonsCompleted,
    setLessonsCompleted,
    assignmentSubmitted,
    setAssignmentSubmitted,
    submittedQuiz,
    setSubmittedQuiz,
    quizScore,
    setQuizScore,
    activeQuizQuestion,
    setActiveQuizQuestion,
    quizAnswers,
    setQuizAnswers,
    workshopStudents,
    handleToggleAttendance
  } = useLms();

  return (
    <motion.div 
      key="sim-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="grid grid-cols-1 lg:grid-cols-12 gap-6"
    >
      {/* Role Switcher sidebar - 3 columns */}
      <div className="lg:col-span-3 bg-slate-950 border border-slate-800 rounded-2xl p-5 flex flex-col gap-4 shadow-xl h-full">
        <h3 className="text-sm font-bold text-white uppercase tracking-wider mb-2 flex items-center gap-2 font-sans">
          <Activity className="w-4 h-4 text-teal-400" />
          UX Role Simulator
        </h3>
        <p className="text-xs text-slate-400 leading-relaxed mb-2">
          Select a role below to simulate the customized interfaces designed for Gilgil TVC's specific trade training requirements.
        </p>

        <div className="flex flex-col gap-2.5">
          <button
            onClick={() => setSimRole("student")}
            className={`text-left px-4 py-3 rounded-xl border flex items-center justify-between transition-all cursor-pointer ${
              simRole === "student" 
                ? "bg-teal-950/40 border-teal-500/80 text-white font-bold" 
                : "bg-slate-900 border-slate-850 text-slate-400 hover:bg-slate-850 hover:text-white"
            }`}
          >
            <span>👨‍🎓 Student Dashboard</span>
            <ChevronRight className="w-4 h-4" />
          </button>
          <button
            onClick={() => setSimRole("lecturer")}
            className={`text-left px-4 py-3 rounded-xl border flex items-center justify-between transition-all cursor-pointer ${
              simRole === "lecturer" 
                ? "bg-teal-950/40 border-teal-500/80 text-white font-bold" 
                : "bg-slate-900 border-slate-850 text-slate-400 hover:bg-slate-850 hover:text-white"
            }`}
          >
            <span>🛠️ Lecturer Dashboard</span>
            <ChevronRight className="w-4 h-4" />
          </button>
          <button
            onClick={() => setSimRole("registrar")}
            className={`text-left px-4 py-3 rounded-xl border flex items-center justify-between transition-all cursor-pointer ${
              simRole === "registrar" 
                ? "bg-teal-950/40 border-teal-500/80 text-white font-bold" 
                : "bg-slate-900 border-slate-850 text-slate-400 hover:bg-slate-850 hover:text-white"
            }`}
          >
            <span>🏛️ Registrar Dashboard</span>
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>

        <div className="bg-slate-900 p-4 rounded-xl border border-slate-850 mt-4 text-[11px] text-slate-400 flex flex-col gap-2 leading-relaxed">
          <strong className="text-white block font-mono">Simulated Cohort</strong>
          <div>🎓 intake: September 2025</div>
          <div>🏢 dept: ICT & Automotive</div>
          <div>📚 active unit: Level 6 Diploma</div>
        </div>
      </div>

      {/* Simulated Display Screen - 9 columns */}
      <div className="lg:col-span-9 bg-slate-950 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col min-h-[550px]" id="ux-sim-frame">
        {/* Simulator header mock */}
        <div className="bg-slate-900 px-5 py-3 border-b border-slate-850 flex justify-between items-center flex-wrap gap-2">
          <div className="flex items-center gap-2">
            <div className="w-3 h-3 rounded-full bg-red-500/80" />
            <div className="w-3 h-3 rounded-full bg-yellow-500/80" />
            <div className="w-3 h-3 rounded-full bg-emerald-500/80" />
            <span className="text-xs text-slate-400 font-mono ml-4 select-none bg-slate-950 px-3 py-1 rounded border border-slate-800">
              https://gilgiltvc.ac.ke/lms/dashboard
            </span>
          </div>
          <span className="text-[10px] bg-teal-500/15 text-teal-400 border border-teal-500/15 px-2.5 py-0.5 rounded-full font-mono">
            Simulation Active
          </span>
        </div>

        {/* SIMULATED ROLE VIEWPORT */}
        <div className="flex-1 p-5 md:p-6 bg-slate-900 overflow-y-auto">
          
          {/* SIMULATION 1: STUDENT INTERFACE */}
          {simRole === "student" && (
            <div className="flex flex-col gap-6">
              {/* Dashboard Welcome header */}
              <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-950 p-5 rounded-2xl border border-slate-800">
                <div>
                  <h4 className="text-lg font-bold text-white">Karibu, Kelvin Mbugua</h4>
                  <p className="text-xs text-slate-400">Reg No: GTVC/ICT/2025/089 • Department: ICT Department</p>
                </div>
                <div className="bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800 flex items-center gap-2">
                  <DollarSign className="w-4 h-4 text-emerald-400" />
                  <div className="text-[11px]">
                    <span className="text-slate-500">Fee Balance: </span>
                    <span className="font-bold text-emerald-400 font-mono">KES 0.00</span>
                  </div>
                </div>
              </div>

              {/* Student Syllabus Course Block */}
              <div>
                <h5 className="text-xs font-bold uppercase text-slate-400 tracking-wider mb-3 font-sans">Enrolled Unit Class</h5>
                <div className="bg-slate-950 rounded-2xl border border-slate-800 overflow-hidden">
                  {/* Banner Info */}
                  <div className="bg-slate-900 p-5 border-b border-slate-800 flex justify-between items-start md:items-center gap-4 flex-col md:flex-row">
                    <div>
                      <span className="text-[10px] bg-teal-500/15 text-teal-400 px-2 py-0.5 rounded font-mono font-bold">ICT LEVEL 6</span>
                      <h4 className="text-base font-bold text-white mt-1">CDACC-ICT06 Web Application Programming</h4>
                      <p className="text-xs text-slate-400">Instructor: Mr. James Mwangi</p>
                    </div>
                    
                    {/* Attendance Meter */}
                    <div className="flex items-center gap-3 bg-slate-950 p-2.5 rounded-xl border border-slate-850">
                      <Activity className="w-5 h-5 text-teal-400" />
                      <div className="text-xs">
                        <span className="text-slate-500 block text-[9px] uppercase font-bold">Workshop attendance</span>
                        <span className="font-bold text-white font-mono">36 / 40 Hours Completed</span>
                      </div>
                    </div>
                  </div>

                  <div className="p-5 grid grid-cols-1 md:grid-cols-12 gap-6">
                    {/* Left Side: Lessons Navigator */}
                    <div className="md:col-span-8 flex flex-col gap-4">
                      <h5 className="text-xs font-bold text-white border-b border-slate-850 pb-2 flex justify-between items-center font-sans">
                        <span>Unit Course Materials</span>
                        <span className="text-slate-500 font-normal">3 lessons available</span>
                      </h5>

                      {/* Lesson list */}
                      <div className="flex flex-col gap-2.5">
                        {[
                          { id: "les1", title: "Lesson 1: Introduction to LAMP Architecture & PDO Databases", duration: "1.5 Hours", desc: "Understand secure data persistence and database routing in standard shared hosts." },
                          { id: "les2", title: "Lesson 2: PHP Session Management & CSRF Gating Protocol", duration: "2 Hours", desc: "Securing student dashboards against cross-site request hijacking." },
                          { id: "les3", title: "Lesson 3: Validating Media Uploads for Vocational Trade Portfolios", duration: "3 Hours", desc: "Sanitizing multi-part files for welding, catering, or plumbing portfolio submission." }
                        ].map((les) => {
                          const completed = lessonsCompleted.includes(les.id);
                          return (
                            <div 
                              key={les.id} 
                              className={`p-4 rounded-xl border transition-all flex justify-between items-start gap-4 ${
                                completed 
                                  ? "bg-slate-900/40 border-teal-555/30" 
                                  : "bg-slate-900/80 border-slate-850"
                              }`}
                            >
                              <div className="flex-1">
                                <div className="flex items-center gap-2">
                                  {completed ? (
                                    <span className="w-2 h-2 rounded-full bg-teal-400 animate-pulse" />
                                  ) : (
                                    <span className="w-2 h-2 rounded-full bg-slate-600" />
                                  )}
                                  <span className="text-xs font-bold text-white">{les.title}</span>
                                </div>
                                <p className="text-[11px] text-slate-400 mt-1 leading-relaxed">{les.desc}</p>
                                <span className="text-[9px] text-slate-500 block mt-1.5 font-mono">Suggested study: {les.duration}</span>
                              </div>

                              <button 
                                onClick={() => {
                                  if (completed) {
                                    setLessonsCompleted(prev => prev.filter(id => id !== les.id));
                                  } else {
                                    setLessonsCompleted(prev => [...prev, les.id]);
                                  }
                                }}
                                className={`px-3 py-1.5 rounded-lg text-[10px] font-bold border transition-all cursor-pointer ${
                                  completed 
                                    ? "bg-teal-555/10 border-teal-555/30 text-teal-400" 
                                    : "bg-slate-850 border-slate-750 text-slate-300 hover:bg-slate-800"
                                }`}
                              >
                                {completed ? "Completed ✅" : "Mark Read"}
                              </button>
                            </div>
                          );
                        })}
                      </div>
                    </div>

                    {/* Right Side: Assessments Widget */}
                    <div className="md:col-span-4 flex flex-col gap-4">
                      <h5 className="text-xs font-bold text-white border-b border-slate-850 pb-2 font-sans">Assessments Tasks</h5>
                      
                      {/* Assignment Submission Card */}
                      <div className="bg-slate-900/50 p-4 rounded-xl border border-slate-850 flex flex-col gap-3">
                        <div>
                          <span className="text-[9px] uppercase font-bold text-teal-400 font-mono">Assignment (60% Weight)</span>
                          <strong className="text-xs text-white block mt-0.5">Automotive Electrical Design Project</strong>
                          <p className="text-[10px] text-slate-400 mt-1 leading-snug">Submit photographs/diagram of complete alternator wiring layout.</p>
                        </div>

                        {assignmentSubmitted ? (
                          <div className="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-2.5 text-center">
                            <span className="text-[10px] text-emerald-400 font-bold">Submission Received! 📂</span>
                            <p className="text-[9px] text-slate-400 mt-0.5 font-sans">Pending Instructor Grading.</p>
                          </div>
                        ) : (
                          <div className="flex flex-col gap-2">
                            <div className="border border-dashed border-slate-700 hover:border-teal-500/50 transition-all rounded-lg p-3 text-center cursor-pointer bg-slate-950">
                              <span className="text-[10px] text-slate-400 block font-mono">Drop picture or schematic file (.PNG / .JPG)</span>
                              <span className="text-[8px] text-slate-500 block mt-1">MIME strictly validated server-side. Max 10MB.</span>
                            </div>
                            <button 
                              onClick={() => setAssignmentSubmitted(true)}
                              className="bg-teal-555 hover:bg-teal-500 text-slate-950 font-bold text-[10px] py-1.5 rounded-lg shadow shadow-teal-500/5 cursor-pointer"
                            >
                              Submit Portfolio
                            </button>
                          </div>
                        )}
                      </div>

                      {/* Interactive Assessment Quiz Block */}
                      <div className="bg-slate-900/50 p-4 rounded-xl border border-slate-850 flex flex-col gap-3">
                        <div>
                          <span className="text-[9px] uppercase font-bold text-teal-400 font-mono">Quiz (40% Weight)</span>
                          <strong className="text-xs text-white block mt-0.5">Secure Prepared Statements Quiz</strong>
                        </div>

                        {submittedQuiz ? (
                          <div className="bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-3 text-center">
                            <span className="text-xs text-emerald-400 font-bold block">Quiz Score: {quizScore}%</span>
                            <span className="text-[9px] text-slate-400 block mt-0.5 font-sans">Recorded directly in Secure SQL Ledgers</span>
                            <button 
                              onClick={() => {
                                setSubmittedQuiz(false);
                                setQuizAnswers({});
                                setActiveQuizQuestion(0);
                              }}
                              className="text-teal-400 hover:underline text-[9px] font-bold mt-2 cursor-pointer"
                            >
                              Retake Quiz
                            </button>
                          </div>
                        ) : (
                          <div className="flex flex-col gap-2.5">
                            {/* Question text block */}
                            <div className="text-[11px] text-slate-350 leading-relaxed bg-slate-950 p-2.5 rounded border border-slate-800">
                              {activeQuizQuestion === 0 
                                ? "Q1: Which method guarantees raw inputs are never evaluated directly as SQL commands by MariaDB?"
                                : "Q2: What PHP session flag protects cookies from being read by cross-site javascript scripts?"}
                            </div>
                            
                            {/* Options selector */}
                            <div className="flex flex-col gap-1.5">
                              {(activeQuizQuestion === 0 
                                ? [
                                  { index: 0, text: "A. htmlspecialchars()" },
                                  { index: 1, text: "B. Bound PDO Prepared Statements" },
                                  { index: 2, text: "C. md5() encryption" }
                                ] 
                                : [
                                  { index: 0, text: "A. HttpOnly Cookie flag" },
                                  { index: 1, text: "B. Session Timeout" },
                                  { index: 2, text: "C. CSRF Token" }
                                ]
                              ).map((opt) => {
                                const selected = quizAnswers[activeQuizQuestion] === opt.index;
                                return (
                                  <button
                                    key={opt.index}
                                    onClick={() => setQuizAnswers(prev => ({ ...prev, [activeQuizQuestion]: opt.index }))}
                                    className={`text-left p-2 rounded text-[10px] border transition-all cursor-pointer ${
                                      selected 
                                        ? "bg-teal-950/20 border-teal-550 text-teal-400 font-semibold" 
                                        : "bg-slate-950 border-slate-800 hover:bg-slate-900 text-slate-400"
                                    }`}
                                  >
                                    {opt.text}
                                  </button>
                                );
                              })}
                            </div>

                            {/* Quiz Controls */}
                            <div className="flex justify-between items-center mt-1">
                              {activeQuizQuestion === 0 ? (
                                <button 
                                  onClick={() => setActiveQuizQuestion(1)}
                                  disabled={quizAnswers[0] === undefined}
                                  className="bg-slate-800 hover:bg-slate-750 text-white font-bold text-[10px] px-3.5 py-1.5 rounded disabled:opacity-40 cursor-pointer ml-auto"
                                >
                                  Next Question
                                </button>
                              ) : (
                                <div className="flex justify-between w-full">
                                  <button 
                                    onClick={() => setActiveQuizQuestion(0)}
                                    className="text-slate-400 hover:text-white text-[9px] cursor-pointer"
                                  >
                                    Back
                                  </button>
                                  <button 
                                    onClick={() => {
                                      let score = 0;
                                      if (quizAnswers[0] === 1) score += 50;
                                      if (quizAnswers[1] === 0) score += 50;
                                      setQuizScore(score);
                                      setSubmittedQuiz(true);
                                    }}
                                    disabled={quizAnswers[1] === undefined}
                                    className="bg-teal-555 hover:bg-teal-500 text-slate-950 font-bold text-[10px] px-3.5 py-1.5 rounded disabled:opacity-40 cursor-pointer"
                                  >
                                    Submit Quiz
                                  </button>
                                </div>
                              )}
                            </div>
                          </div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}

              {/* SIMULATION 2: LECTURER INTERFACE */}
              {simRole === "lecturer" && (
                <div className="flex flex-col gap-6">
                  <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-950 p-5 rounded-2xl border border-slate-800">
                    <div>
                      <h4 className="text-lg font-bold text-white">Karibu, Instructor Mwangi</h4>
                      <p className="text-xs text-slate-400">Mechanical & Automotive Engineering Faculty</p>
                    </div>
                    <span className="text-xs bg-teal-500/10 text-teal-400 px-3 py-1 rounded-full border border-teal-500/10 font-mono select-none">
                      HOD Authorization Approved
                    </span>
                  </div>

                  {/* Workshop Attendance Ledger Simulator */}
                  <div className="bg-slate-950 rounded-2xl border border-slate-800 p-5 flex flex-col gap-4">
                    <div>
                      <h5 className="text-sm font-bold text-white flex items-center gap-2 font-sans">
                        <Activity className="w-4 h-4 text-emerald-400" />
                        Vocational Trade Workshop Session Ledger
                      </h5>
                      <p className="text-xs text-slate-400 mt-1">
                        Gilgil TVC students require 40 hours of supervised workshop trades training for external certification.
                      </p>
                    </div>

                    {/* List of class students */}
                    <div className="flex flex-col gap-2.5 mt-2">
                      {workshopStudents.map((student) => (
                        <div key={student.id} className="bg-slate-900 p-4 rounded-xl border border-slate-850 flex justify-between items-center flex-wrap gap-4">
                          <div>
                            <span className="text-xs font-bold text-white block">{student.name}</span>
                            <span className="text-[10px] text-slate-400 font-mono block">{student.reg}</span>
                          </div>

                          <div className="flex items-center gap-6">
                            {/* Completed hours progress bar */}
                            <div className="text-xs text-right min-w-[120px]">
                              <span className="text-slate-500 text-[10px] block font-sans">Workshop Completion</span>
                              <div className="flex items-center gap-2.5 mt-1">
                                <div className="w-24 bg-slate-800 h-2 rounded-full overflow-hidden">
                                  <div 
                                    className={`h-full ${student.status === "Warning" ? "bg-amber-500" : "bg-emerald-500"}`}
                                    style={{ width: `${Math.min(100, (student.workshopHours / student.requiredHours) * 100)}%` }}
                                  />
                                </div>
                                <span className="font-mono font-bold text-white text-[10px]">{student.workshopHours}/{student.requiredHours}h</span>
                              </div>
                            </div>

                            {/* Status Tag */}
                            <span className={`px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase select-none ${
                              student.status === "Warning" ? "bg-amber-500/10 text-amber-400 border border-amber-500/20" :
                              student.status === "Compliant" ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" :
                              "bg-slate-850 text-slate-400"
                            }`}>
                              {student.status}
                            </span>

                            {/* Quick Log Session button */}
                            <button
                              onClick={() => handleToggleAttendance(student.id)}
                              className="bg-slate-800 hover:bg-slate-750 text-white hover:text-teal-400 font-bold text-[10px] px-3 py-1.5 rounded transition-all cursor-pointer"
                            >
                              {student.workshopHours >= student.requiredHours ? "Deduct 2h" : "Add +2 Hours"}
                            </button>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              )}

              {/* SIMULATION 3: REGISTRAR INTERFACE */}
              {simRole === "registrar" && (
                <div className="flex flex-col gap-6">
                  <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-950 p-5 rounded-2xl border border-slate-800">
                    <div>
                      <h4 className="text-lg font-bold text-white">Karibu, Academic Registrar Office</h4>
                      <p className="text-xs text-slate-400">Gilgil TVC Cohort Admissions & Intake Scheduler</p>
                    </div>
                    <span className="text-xs bg-slate-900 border border-slate-800 px-3 py-1 rounded font-mono text-slate-300">
                      Active Term: Term II 2026
                    </span>
                  </div>

                  {/* Cohort Taxonomy Planner */}
                  <div className="bg-slate-950 rounded-2xl border border-slate-800 p-5 flex flex-col gap-4">
                    <div className="border-b border-slate-850 pb-3 flex justify-between items-center flex-wrap gap-2">
                      <h5 className="text-sm font-bold text-white font-sans">Current Cohort Taxonomy Planner</h5>
                      <button className="bg-teal-555 text-slate-950 font-bold text-[10px] px-3 py-1.5 rounded-lg flex items-center gap-1 cursor-pointer">
                        <Plus className="w-3.5 h-3.5" />
                        Create New Cycle
                      </button>
                    </div>

                    {/* Hierarchical Cohort Display */}
                    <div className="flex flex-col gap-4 font-mono text-xs">
                      {/* Academic Year Node */}
                      <div className="bg-slate-900/60 p-4 rounded-xl border border-slate-850 flex flex-col gap-3">
                        <div className="flex justify-between items-center">
                          <span className="text-teal-400 font-bold">📂 Academic Year: 2025/2026</span>
                          <span className="text-[9px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/10 select-none font-sans">Current Cycle</span>
                        </div>

                        {/* Intakes Child Node */}
                        <div className="pl-4 flex flex-col gap-3 border-l border-slate-800">
                          <div>
                            <span className="text-white font-semibold">📁 Intake Cycle: September 2025 Intake</span>
                            <span className="text-[10px] text-slate-500 ml-2">Started: 01-Sep-2025</span>
                          </div>

                          {/* Programs Node */}
                          <div className="pl-4 flex flex-col gap-2 border-l border-slate-800">
                            <div className="bg-slate-950 p-2.5 rounded border border-slate-850 flex justify-between items-center flex-wrap gap-2">
                              <div>
                                <span className="text-slate-200 block font-bold">🛠️ Certificate in Plumbing (Level 5)</span>
                                <span className="text-[10px] text-slate-400 font-sans mt-0.5 block">Level/Year: Year 1 • Term/Module: Term II</span>
                              </div>
                              <span className="text-[10px] bg-slate-900 px-2 py-0.5 rounded border border-slate-800 text-slate-400 font-mono">18 Students Enrolled</span>
                            </div>

                            <div className="bg-slate-950 p-2.5 rounded border border-slate-850 flex justify-between items-center flex-wrap gap-2">
                              <div>
                                <span className="text-slate-200 block font-bold">💻 Diploma in ICT (Level 6)</span>
                                <span className="text-[10px] text-slate-400 font-sans mt-0.5 block">Level/Year: Year 1 • Term/Module: Term II</span>
                              </div>
                              <span className="text-[10px] bg-slate-900 px-2 py-0.5 rounded border border-slate-800 text-slate-400 font-mono">42 Students Enrolled</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              )}

        </div>
      </div>
    </motion.div>
  );
}

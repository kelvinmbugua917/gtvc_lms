import React, { useState, useEffect } from "react";
import { 
  FileCheck, Clock, CheckCircle2, AlertCircle, Plus, Upload, Download, 
  Send, HelpCircle, Award, CheckSquare, Eye, Edit3, Trash2, Calendar, Shield
} from "lucide-react";
import { motion, AnimatePresence } from "motion/react";
import { useLms } from "../context/LmsContext";
import { 
  getAssignmentsByOfferingApi, createAssignmentApi, AssignmentRecord 
} from "../api/assignments";
import { 
  getSubmissionsByAssignmentApi, submitAssignmentApi, gradeSubmissionApi, getSubmissionDownloadUrl 
} from "../api/submissions";
import { 
  getQuizzesByOfferingApi, createQuizApi, addQuestionToQuizApi, startQuizAttemptApi, 
  submitQuizAttemptApi, QuizRecord, QuizAttemptRecord, QuizQuestionRecord 
} from "../api/quizzes";
import { 
  getCourseGradesApi, saveGradeApi, publishCourseGradesApi, getMyStudentGradesApi, StudentCourseGradeRecord 
} from "../api/grades";

export default function AssessmentsPage() {
  const { authUser } = useLms();
  const [activeSubTab, setActiveSubTab] = useState<"assignments" | "quizzes" | "gradebook">("assignments");
  const [selectedOfferingId, setSelectedOfferingId] = useState<number>(1);

  // Assignments State
  const [assignments, setAssignments] = useState<AssignmentRecord[]>([]);
  const [selectedAssignment, setSelectedAssignment] = useState<AssignmentRecord | null>(null);
  const [submissions, setSubmissions] = useState<any[]>([]);
  const [showCreateAssignmentModal, setShowCreateAssignmentModal] = useState(false);
  const [showSubmissionModal, setShowSubmissionModal] = useState(false);
  const [showGradingModal, setShowGradingModal] = useState<any | null>(null);

  // New Assignment Form State
  const [newAssignTitle, setNewAssignTitle] = useState("");
  const [newAssignDesc, setNewAssignDesc] = useState("");
  const [newAssignInstructions, setNewAssignInstructions] = useState("");
  const [newAssignMaxMarks, setNewAssignMaxMarks] = useState(100);
  const [newAssignDueDate, setNewAssignDueDate] = useState("");

  // Student Submission Form State
  const [submitText, setSubmitText] = useState("");
  const [submitFile, setSubmitFile] = useState<File | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Lecturer Grading Form State
  const [gradeMarks, setGradeMarks] = useState<number>(0);
  const [gradeFeedback, setGradeFeedback] = useState("");

  // Quizzes State
  const [quizzes, setQuizzes] = useState<QuizRecord[]>([]);
  const [selectedQuiz, setSelectedQuiz] = useState<QuizRecord | null>(null);
  const [activeQuizAttempt, setActiveQuizAttempt] = useState<QuizAttemptRecord | null>(null);
  const [studentAnswers, setStudentAnswers] = useState<Record<number, { selected_option_id?: number; text_response?: string }>>({});
  const [quizTimerSeconds, setQuizTimerSeconds] = useState<number>(0);
  const [quizResult, setQuizResult] = useState<QuizAttemptRecord | null>(null);
  const [showCreateQuizModal, setShowCreateQuizModal] = useState(false);
  const [showAddQuestionModal, setShowAddQuestionModal] = useState<number | null>(null);

  // New Quiz Form State
  const [newQuizTitle, setNewQuizTitle] = useState("");
  const [newQuizTimeLimit, setNewQuizTimeLimit] = useState(15);
  const [newQuizPassingPct, setNewQuizPassingPct] = useState(50);
  const [newQuizMaxAttempts, setNewQuizMaxAttempts] = useState(1);

  // New Question Form State
  const [newQText, setNewQText] = useState("");
  const [newQType, setNewQType] = useState<"multiple_choice" | "true_false">("multiple_choice");
  const [newQMarks, setNewQMarks] = useState(10);
  const [newQOptionA, setNewQOptionA] = useState("");
  const [newQOptionB, setNewQOptionB] = useState("");
  const [newQOptionC, setNewQOptionC] = useState("");
  const [newQOptionD, setNewQOptionD] = useState("");
  const [newQCorrectOpt, setNewQCorrectOpt] = useState<number>(0);

  // Gradebook State
  const [courseGrades, setCourseGrades] = useState<StudentCourseGradeRecord[]>([]);
  const [myGrades, setMyGrades] = useState<StudentCourseGradeRecord[]>([]);
  const [editingGrade, setEditingGrade] = useState<{ student_id: number; student_name: string; coursework: number; exam: number } | null>(null);

  const isStudent = authUser?.roles.some(r => r.name === "student") && !authUser?.roles.some(r => ["admin", "super_admin"].includes(r.name));
  const isLecturerOrAdmin = authUser?.roles.some(r => ["lecturer", "hod", "admin", "super_admin"].includes(r.name));

  // Load data on selection change
  useEffect(() => {
    loadAssignments();
    loadQuizzes();
    loadGrades();
  }, [selectedOfferingId, activeSubTab]);

  const loadAssignments = async () => {
    const list = await getAssignmentsByOfferingApi(selectedOfferingId);
    setAssignments(list);
  };

  const loadQuizzes = async () => {
    const list = await getQuizzesByOfferingApi(selectedOfferingId);
    setQuizzes(list);
  };

  const loadGrades = async () => {
    if (isStudent) {
      const my = await getMyStudentGradesApi();
      setMyGrades(my);
    }
    const courseList = await getCourseGradesApi(selectedOfferingId);
    setCourseGrades(courseList);
  };

  // Handle Assignment Creation
  const handleCreateAssignment = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newAssignTitle) return;

    try {
      await createAssignmentApi(selectedOfferingId, {
        title: newAssignTitle,
        description: newAssignDesc,
        instructions: newAssignInstructions,
        max_marks: newAssignMaxMarks,
        due_date: newAssignDueDate ? new Date(newAssignDueDate).toISOString() : undefined,
        is_published: 1
      });
      setShowCreateAssignmentModal(false);
      setNewAssignTitle("");
      setNewAssignDesc("");
      setNewAssignInstructions("");
      loadAssignments();
    } catch (err: any) {
      alert("Error creating assignment: " + err.message);
    }
  };

  // Handle Student Submission
  const handleStudentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedAssignment) return;

    setIsSubmitting(true);
    try {
      const formData = new FormData();
      if (submitText) formData.append("submission_text", submitText);
      if (submitFile) formData.append("file", submitFile);

      const res = await submitAssignmentApi(selectedAssignment.id, formData);
      alert(res.message);
      setShowSubmissionModal(false);
      setSubmitText("");
      setSubmitFile(null);
      loadAssignments();
    } catch (err: any) {
      alert("Submission Error: " + err.message);
    } finally {
      setIsSubmitting(false);
    }
  };

  // Handle Lecturer Submission Viewing
  const handleViewSubmissions = async (assignment: AssignmentRecord) => {
    setSelectedAssignment(assignment);
    const list = await getSubmissionsByAssignmentApi(assignment.id);
    setSubmissions(list);
  };

  // Handle Lecturer Grading
  const handleGradeSubmissionSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!showGradingModal) return;

    try {
      await gradeSubmissionApi(showGradingModal.id, gradeMarks, gradeFeedback);
      setShowGradingModal(null);
      if (selectedAssignment) {
        handleViewSubmissions(selectedAssignment);
      }
    } catch (err: any) {
      alert("Grading Error: " + err.message);
    }
  };

  // Handle Quiz Creation
  const handleCreateQuiz = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newQuizTitle) return;

    try {
      await createQuizApi(selectedOfferingId, {
        title: newQuizTitle,
        time_limit_minutes: newQuizTimeLimit,
        passing_percentage: newQuizPassingPct,
        max_attempts: newQuizMaxAttempts,
        is_published: 1
      });
      setShowCreateQuizModal(false);
      setNewQuizTitle("");
      loadQuizzes();
    } catch (err: any) {
      alert("Quiz Creation Error: " + err.message);
    }
  };

  // Handle Question Addition
  const handleAddQuestion = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!showAddQuestionModal || !newQText) return;

    try {
      const options = [
        { option_text: newQOptionA, is_correct: newQCorrectOpt === 0 },
        { option_text: newQOptionB, is_correct: newQCorrectOpt === 1 },
        { option_text: newQOptionC, is_correct: newQCorrectOpt === 2 },
        { option_text: newQOptionD, is_correct: newQCorrectOpt === 3 },
      ].filter(o => o.option_text.trim().length > 0);

      await addQuestionToQuizApi(showAddQuestionModal, {
        question_text: newQText,
        question_type: newQType,
        marks: newQMarks,
        options
      });

      setShowAddQuestionModal(null);
      setNewQText("");
      setNewQOptionA("");
      setNewQOptionB("");
      setNewQOptionC("");
      setNewQOptionD("");
      loadQuizzes();
    } catch (err: any) {
      alert("Error adding question: " + err.message);
    }
  };

  // Handle Starting Quiz Attempt
  const handleStartQuiz = async (quiz: QuizRecord) => {
    try {
      const attempt = await startQuizAttemptApi(quiz.id);
      setActiveQuizAttempt(attempt);
      setSelectedQuiz(quiz);
      setQuizTimerSeconds(quiz.time_limit_minutes * 60);
      setStudentAnswers({});
      setQuizResult(null);
    } catch (err: any) {
      alert("Cannot Start Quiz: " + err.message);
    }
  };

  // Timer countdown hook
  useEffect(() => {
    if (!activeQuizAttempt || quizTimerSeconds <= 0) return;
    const interval = setInterval(() => {
      setQuizTimerSeconds(prev => {
        if (prev <= 1) {
          clearInterval(interval);
          handleFinalizeQuizSubmit();
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
    return () => clearInterval(interval);
  }, [activeQuizAttempt, quizTimerSeconds]);

  // Submit Quiz Attempt
  const handleFinalizeQuizSubmit = async () => {
    if (!activeQuizAttempt) return;

    try {
      const formattedAnswers = Object.entries(studentAnswers).map(([qId, val]) => ({
        question_id: Number(qId),
        selected_option_id: (val as any)?.selected_option_id,
        text_response: (val as any)?.text_response
      }));

      const res = await submitQuizAttemptApi(activeQuizAttempt.id, formattedAnswers);
      setQuizResult(res);
      setActiveQuizAttempt(null);
      loadQuizzes();
    } catch (err: any) {
      alert("Error submitting quiz attempt: " + err.message);
    }
  };

  // Handle Save Grade
  const handleSaveGradeSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingGrade) return;

    try {
      await saveGradeApi(selectedOfferingId, editingGrade.student_id, editingGrade.coursework, editingGrade.exam);
      setEditingGrade(null);
      loadGrades();
    } catch (err: any) {
      alert("Grade Save Error: " + err.message);
    }
  };

  // Handle Publish All Grades
  const handlePublishGrades = async () => {
    if (!confirm("Are you sure you want to publish final grades for all students in this course offering?")) return;
    try {
      await publishCourseGradesApi(selectedOfferingId);
      alert("All grades published successfully!");
      loadGrades();
    } catch (err: any) {
      alert("Error publishing grades: " + err.message);
    }
  };

  return (
    <motion.div 
      key="assessments-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="flex flex-col gap-6"
    >
      {/* Top Banner & Control Bar */}
      <div className="bg-slate-950 rounded-2xl border border-slate-800 p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <div className="flex items-center gap-2">
            <h2 className="text-lg font-bold text-white flex items-center gap-2">
              <FileCheck className="w-5 h-5 text-teal-400" />
              Assessments, Quizzes & Gradebook
            </h2>
            <span className="text-xs bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-mono font-semibold">
              PHASE 6C PRODUCTION
            </span>
          </div>
          <p className="text-xs text-slate-400 mt-1">
            Complete TVET CBET assessment engine: Assignments, secure file uploads, online auto-scored quizzes, and competency gradebook.
          </p>
        </div>

        {/* Offering Selector & Sub-Nav Tabs */}
        <div className="flex flex-wrap items-center gap-3">
          <div className="flex items-center gap-2 bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-xs">
            <span className="text-slate-400 font-medium">Offering:</span>
            <select 
              value={selectedOfferingId}
              onChange={(e) => setSelectedOfferingId(Number(e.target.value))}
              className="bg-transparent text-teal-300 font-bold focus:outline-none cursor-pointer"
            >
              <option value={1} className="bg-slate-900 text-white">AUT-101: Cert Automotive Workshop (Jan 2026)</option>
              <option value={2} className="bg-slate-900 text-white">ELE-102: Cert Electrical Wiring (Jan 2026)</option>
            </select>
          </div>

          <div className="flex gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800">
            <button
              onClick={() => { setActiveSubTab("assignments"); setSelectedAssignment(null); }}
              className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer ${
                activeSubTab === "assignments" ? "bg-teal-500 text-slate-950 font-bold" : "text-slate-400 hover:text-white"
              }`}
            >
              Assignments
            </button>
            <button
              onClick={() => { setActiveSubTab("quizzes"); setActiveQuizAttempt(null); }}
              className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer ${
                activeSubTab === "quizzes" ? "bg-teal-500 text-slate-950 font-bold" : "text-slate-400 hover:text-white"
              }`}
            >
              Online Quizzes
            </button>
            <button
              onClick={() => setActiveSubTab("gradebook")}
              className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer ${
                activeSubTab === "gradebook" ? "bg-teal-500 text-slate-950 font-bold" : "text-slate-400 hover:text-white"
              }`}
            >
              Course Gradebook
            </button>
          </div>
        </div>
      </div>

      {/* SUB-TAB 1: ASSIGNMENTS */}
      {activeSubTab === "assignments" && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Left Column: List of Assignments */}
          <div className={`${selectedAssignment ? "lg:col-span-5" : "lg:col-span-12"} flex flex-col gap-4`}>
            <div className="bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl">
              <div className="flex justify-between items-center border-b border-slate-850 pb-3 mb-4">
                <h3 className="text-sm font-bold text-white flex items-center gap-2">
                  <FileCheck className="w-4 h-4 text-teal-400" />
                  Course Assignments ({assignments.length})
                </h3>

                {isLecturerOrAdmin && (
                  <button
                    onClick={() => setShowCreateAssignmentModal(true)}
                    className="flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold rounded-xl text-xs transition-all cursor-pointer shadow-md shadow-teal-500/10"
                  >
                    <Plus className="w-3.5 h-3.5" />
                    New Assignment
                  </button>
                )}
              </div>

              <div className="flex flex-col gap-3">
                {assignments.map((assign) => {
                  const isSelected = selectedAssignment?.id === assign.id;
                  const isDueDatePassed = assign.due_date ? new Date() > new Date(assign.due_date) : false;

                  return (
                    <div 
                      key={assign.id}
                      onClick={() => handleViewSubmissions(assign)}
                      className={`p-4 rounded-xl border transition-all cursor-pointer ${
                        isSelected 
                          ? "bg-slate-900 border-teal-500 ring-1 ring-teal-500/20 shadow-md" 
                          : "bg-slate-900/60 border-slate-850 hover:border-slate-700 hover:bg-slate-850/40"
                      }`}
                    >
                      <div className="flex justify-between items-start gap-2">
                        <span className="font-bold text-white text-sm">{assign.title}</span>
                        <span className="text-[10px] font-mono bg-teal-500/15 text-teal-300 border border-teal-500/30 px-2 py-0.5 rounded font-bold">
                          {assign.max_marks} Marks
                        </span>
                      </div>

                      <p className="text-xs text-slate-400 mt-1 line-clamp-2">{assign.description}</p>

                      <div className="flex items-center justify-between mt-3 pt-2 border-t border-slate-800/60 text-[11px]">
                        <span className="flex items-center gap-1 text-slate-400 font-mono">
                          <Calendar className="w-3 h-3 text-slate-500" />
                          Due: {assign.due_date ? new Date(assign.due_date).toLocaleDateString() : "No deadline"}
                        </span>

                        <span className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold ${
                          isDueDatePassed ? "bg-amber-500/10 text-amber-400" : "bg-emerald-500/10 text-emerald-400"
                        }`}>
                          {isDueDatePassed ? "Closed / Late" : "Open"}
                        </span>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>

          {/* Right Column: Selected Assignment Details / Submissions Panel */}
          {selectedAssignment && (
            <div className="lg:col-span-7 flex flex-col gap-4">
              <div className="bg-slate-950 rounded-2xl border border-slate-800 p-6 shadow-xl">
                <div className="border-b border-slate-850 pb-4 mb-4">
                  <div className="flex justify-between items-start gap-2">
                    <h3 className="text-base font-bold text-white">{selectedAssignment.title}</h3>
                    <button 
                      onClick={() => setSelectedAssignment(null)}
                      className="text-slate-400 hover:text-white text-xs font-mono"
                    >
                      ✕ Close
                    </button>
                  </div>
                  <p className="text-xs text-slate-300 mt-1">{selectedAssignment.description}</p>

                  {selectedAssignment.instructions && (
                    <div className="mt-3 bg-slate-900/80 p-3 rounded-xl border border-slate-800 text-xs text-slate-300">
                      <span className="font-bold text-teal-400 block mb-1">Instructions:</span>
                      {selectedAssignment.instructions}
                    </div>
                  )}
                </div>

                {/* STUDENT VIEW: SUBMISSION ACTION */}
                {isStudent && (
                  <div className="bg-slate-900 p-4 rounded-xl border border-slate-800 mb-4">
                    <h4 className="text-xs font-bold text-white uppercase tracking-wider mb-2 flex items-center gap-1.5">
                      <Upload className="w-3.5 h-3.5 text-teal-400" />
                      My Submission Status
                    </h4>

                    {selectedAssignment.my_submission ? (
                      <div className="bg-slate-950 p-3 rounded-lg border border-slate-800 flex flex-col gap-2 text-xs">
                        <div className="flex justify-between items-center">
                          <span className="text-emerald-400 font-bold flex items-center gap-1">
                            <CheckCircle2 className="w-3.5 h-3.5" />
                            Submitted on {new Date(selectedAssignment.my_submission.submitted_at).toLocaleString()}
                          </span>
                          {selectedAssignment.my_submission.is_late === 1 && (
                            <span className="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded font-mono">
                              LATE SUBMISSION
                            </span>
                          )}
                        </div>

                        {selectedAssignment.my_submission.original_filename && (
                          <div className="flex items-center justify-between bg-slate-900 p-2 rounded border border-slate-850 text-slate-300">
                            <span className="font-mono text-xs truncate max-w-[250px]">
                              📄 {selectedAssignment.my_submission.original_filename}
                            </span>
                            <a 
                              href={getSubmissionDownloadUrl(selectedAssignment.my_submission.id)}
                              target="_blank"
                              rel="noreferrer"
                              className="text-teal-400 hover:underline text-xs flex items-center gap-1 font-bold"
                            >
                              <Download className="w-3 h-3" />
                              Download
                            </a>
                          </div>
                        )}

                        {selectedAssignment.my_submission.marks_awarded !== null ? (
                          <div className="mt-2 bg-emerald-500/10 p-2.5 rounded border border-emerald-500/20 text-xs text-emerald-300">
                            <span className="font-bold block">Grade Awarded: {selectedAssignment.my_submission.marks_awarded} / {selectedAssignment.max_marks} Marks</span>
                            {selectedAssignment.my_submission.feedback && (
                              <p className="text-slate-300 mt-1 italic">"{selectedAssignment.my_submission.feedback}"</p>
                            )}
                          </div>
                        ) : (
                          <span className="text-slate-400 italic text-[11px]">Pending lecturer grading</span>
                        )}

                        <button
                          onClick={() => setShowSubmissionModal(true)}
                          className="mt-2 text-xs text-teal-400 hover:underline font-bold self-start"
                        >
                          Replace Submission
                        </button>
                      </div>
                    ) : (
                      <div className="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-800 rounded-xl text-center">
                        <Upload className="w-8 h-8 text-slate-500 mb-2" />
                        <span className="text-xs font-bold text-slate-200">No submission uploaded yet</span>
                        <p className="text-[11px] text-slate-400 mt-0.5">Upload your document or enter text response before deadline.</p>
                        <button
                          onClick={() => setShowSubmissionModal(true)}
                          className="mt-3 px-4 py-2 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold rounded-xl text-xs transition-all cursor-pointer"
                        >
                          Submit Assignment Now
                        </button>
                      </div>
                    )}
                  </div>
                )}

                {/* LECTURER VIEW: SUBMISSIONS LIST */}
                {isLecturerOrAdmin && (
                  <div>
                    <h4 className="text-xs font-bold text-white uppercase tracking-wider mb-3 flex items-center justify-between">
                      <span>Student Submissions ({submissions.length})</span>
                      <span className="text-[10px] text-slate-400 font-mono font-normal">MIME Validated & IDOR Protected</span>
                    </h4>

                    <div className="flex flex-col gap-2 max-h-[400px] overflow-y-auto pr-1">
                      {submissions.map((sub) => (
                        <div key={sub.id} className="bg-slate-900 p-3 rounded-xl border border-slate-800 flex justify-between items-center gap-2">
                          <div>
                            <div className="flex items-center gap-2">
                              <span className="font-bold text-white text-xs">{sub.student_name}</span>
                              <span className="text-[10px] font-mono text-slate-400">{sub.index_number}</span>
                              {sub.is_late === 1 && (
                                <span className="text-[9px] bg-amber-500/20 text-amber-300 px-1 py-0.2 rounded font-mono font-bold">
                                  LATE
                                </span>
                              )}
                            </div>
                            <span className="text-[10px] text-slate-400 font-mono">
                              Submitted: {new Date(sub.submitted_at).toLocaleString()}
                            </span>
                          </div>

                          <div className="flex items-center gap-2">
                            {sub.file_path && (
                              <a
                                href={getSubmissionDownloadUrl(sub.id)}
                                target="_blank"
                                rel="noreferrer"
                                className="p-1.5 bg-slate-800 hover:bg-slate-700 text-teal-400 rounded-lg text-xs font-mono transition-all flex items-center gap-1"
                              >
                                <Download className="w-3.5 h-3.5" />
                                File
                              </a>
                            )}

                            <button
                              onClick={() => {
                                setShowGradingModal(sub);
                                setGradeMarks(sub.marks_awarded || 0);
                                setGradeFeedback(sub.feedback || "");
                              }}
                              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer ${
                                sub.marks_awarded !== null 
                                  ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" 
                                  : "bg-teal-500 text-slate-950 hover:bg-teal-400"
                              }`}
                            >
                              {sub.marks_awarded !== null ? `${sub.marks_awarded} / ${selectedAssignment.max_marks}` : "Grade"}
                            </button>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      )}

      {/* SUB-TAB 2: ONLINE QUIZZES */}
      {activeSubTab === "quizzes" && (
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
          {/* Active Quiz Taking Interface Overlay */}
          {activeQuizAttempt ? (
            <div className="lg:col-span-12 bg-slate-950 rounded-2xl border border-teal-500/40 p-6 shadow-2xl flex flex-col gap-6">
              <div className="flex justify-between items-center border-b border-slate-800 pb-4">
                <div>
                  <h3 className="text-base font-bold text-white flex items-center gap-2">
                    <CheckSquare className="w-5 h-5 text-teal-400" />
                    {selectedQuiz?.title}
                  </h3>
                  <span className="text-xs text-slate-400">Attempt #{activeQuizAttempt.attempt_number} • Passing Score: {selectedQuiz?.passing_percentage}%</span>
                </div>

                {/* Live Countdown Timer */}
                <div className="flex items-center gap-2 bg-slate-900 border border-teal-500/30 px-4 py-2 rounded-xl text-teal-300 font-mono font-bold text-sm">
                  <Clock className="w-4 h-4 text-teal-400 animate-pulse" />
                  <span>
                    {Math.floor(quizTimerSeconds / 60)}:{(quizTimerSeconds % 60).toString().padStart(2, "0")}
                  </span>
                </div>
              </div>

              {/* Questions List */}
              <div className="flex flex-col gap-6">
                {activeQuizAttempt.questions?.map((q, idx) => (
                  <div key={q.id} className="bg-slate-900 p-5 rounded-xl border border-slate-800 flex flex-col gap-3">
                    <div className="flex justify-between items-start">
                      <span className="font-bold text-white text-sm">
                        Q{idx + 1}. {q.question_text}
                      </span>
                      <span className="text-[10px] font-mono bg-slate-800 px-2 py-0.5 rounded text-slate-400">
                        {q.marks} Marks
                      </span>
                    </div>

                    {/* Options list */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                      {q.options?.map((opt) => {
                        const isSelected = studentAnswers[q.id]?.selected_option_id === opt.id;

                        return (
                          <button
                            key={opt.id}
                            onClick={() => {
                              setStudentAnswers(prev => ({
                                ...prev,
                                [q.id]: { selected_option_id: opt.id }
                              }));
                            }}
                            className={`p-3 rounded-xl border text-left transition-all cursor-pointer flex items-center gap-2 text-xs ${
                              isSelected
                                ? "bg-teal-500/20 border-teal-400 text-teal-200 font-bold"
                                : "bg-slate-950/60 border-slate-800 text-slate-300 hover:bg-slate-850"
                            }`}
                          >
                            <span className={`w-4 h-4 rounded-full border flex items-center justify-center text-[10px] font-bold ${
                              isSelected ? "border-teal-400 bg-teal-500 text-slate-950" : "border-slate-600 text-slate-400"
                            }`}>
                              {isSelected ? "✓" : ""}
                            </span>
                            <span>{opt.option_text}</span>
                          </button>
                        );
                      })}
                    </div>
                  </div>
                ))}
              </div>

              <div className="flex justify-end pt-4 border-t border-slate-800">
                <button
                  onClick={handleFinalizeQuizSubmit}
                  className="px-6 py-2.5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold rounded-xl text-xs transition-all shadow-lg shadow-teal-500/20 cursor-pointer flex items-center gap-2"
                >
                  <Send className="w-4 h-4" />
                  Finalize & Submit Quiz
                </button>
              </div>
            </div>
          ) : (
            /* Quiz Catalog & Management */
            <div className="lg:col-span-12 flex flex-col gap-4">
              {quizResult && (
                <div className="bg-emerald-500/10 border border-emerald-500/30 p-5 rounded-2xl flex justify-between items-center">
                  <div>
                    <h4 className="text-sm font-bold text-emerald-300 flex items-center gap-2">
                      <CheckCircle2 className="w-5 h-5 text-emerald-400" />
                      Quiz Attempt Submitted Successfully
                    </h4>
                    <span className="text-xs text-slate-300 mt-1 block">
                      Score: {quizResult.score_achieved} / {quizResult.total_possible_marks} ({quizResult.percentage_score}%) — 
                      <span className={`font-bold ml-1 ${quizResult.is_passed ? "text-emerald-400" : "text-amber-400"}`}>
                        {quizResult.is_passed ? "PASSED" : "NEEDS REVISION"}
                      </span>
                    </span>
                  </div>
                  <button onClick={() => setQuizResult(null)} className="text-slate-400 hover:text-white text-xs font-mono">
                    Dismiss
                  </button>
                </div>
              )}

              <div className="bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl">
                <div className="flex justify-between items-center border-b border-slate-850 pb-3 mb-4">
                  <h3 className="text-sm font-bold text-white flex items-center gap-2">
                    <CheckSquare className="w-4 h-4 text-teal-400" />
                    Online Quizzes & Assessments ({quizzes.length})
                  </h3>

                  {isLecturerOrAdmin && (
                    <button
                      onClick={() => setShowCreateQuizModal(true)}
                      className="flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold rounded-xl text-xs transition-all cursor-pointer shadow-md shadow-teal-500/10"
                    >
                      <Plus className="w-3.5 h-3.5" />
                      Create Quiz
                    </button>
                  )}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {quizzes.map((quiz) => (
                    <div key={quiz.id} className="bg-slate-900 p-5 rounded-xl border border-slate-850 flex flex-col justify-between gap-4">
                      <div>
                        <div className="flex justify-between items-start gap-2">
                          <h4 className="font-bold text-white text-sm">{quiz.title}</h4>
                          <span className="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300 font-mono font-bold">
                            {quiz.time_limit_minutes} Mins
                          </span>
                        </div>
                        <p className="text-xs text-slate-400 mt-1">{quiz.description}</p>
                      </div>

                      <div className="flex items-center justify-between pt-3 border-t border-slate-800/60 text-[11px] text-slate-400">
                        <span>Questions: {quiz.question_count || 0}</span>
                        <span>Pass: {quiz.passing_percentage}%</span>
                      </div>

                      <div className="flex justify-between items-center">
                        {isStudent && (
                          <button
                            onClick={() => handleStartQuiz(quiz)}
                            className="w-full py-2 bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold rounded-xl text-xs transition-all cursor-pointer shadow-md shadow-teal-500/10"
                          >
                            Start Quiz Attempt
                          </button>
                        )}

                        {isLecturerOrAdmin && (
                          <button
                            onClick={() => setShowAddQuestionModal(quiz.id)}
                            className="w-full py-2 bg-slate-800 hover:bg-slate-700 text-teal-300 border border-slate-700 font-bold rounded-xl text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
                          >
                            <Plus className="w-3.5 h-3.5" />
                            Add Question ({quiz.question_count || 0})
                          </button>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>
      )}

      {/* SUB-TAB 3: COURSE GRADEBOOK & STUDENT RESULTS */}
      {activeSubTab === "gradebook" && (
        <div className="bg-slate-950 rounded-2xl border border-slate-800 p-6 shadow-xl flex flex-col gap-6">
          <div className="flex justify-between items-center border-b border-slate-850 pb-4">
            <div>
              <h3 className="text-base font-bold text-white flex items-center gap-2">
                <Award className="w-5 h-5 text-teal-400" />
                {isStudent ? "My Academic Grade Results" : "Course Gradebook Ledger"}
              </h3>
              <p className="text-xs text-slate-400 mt-1">
                {isStudent 
                  ? "Official published coursework & examination grade records with TVET CBET competency status." 
                  : "Lecturer gradebook: Enter coursework/exam scores and publish final TVET results."}
              </p>
            </div>

            {isLecturerOrAdmin && (
              <button
                onClick={handlePublishGrades}
                className="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl text-xs transition-all shadow-md shadow-emerald-500/10 cursor-pointer flex items-center gap-1.5"
              >
                <Award className="w-4 h-4" />
                Publish Course Grades
              </button>
            )}
          </div>

          {/* STUDENT GRADES VIEW */}
          {isStudent && (
            <div className="flex flex-col gap-3">
              {myGrades.map((g) => (
                <div key={g.id} className="bg-slate-900 p-4 rounded-xl border border-slate-800 flex justify-between items-center gap-4">
                  <div>
                    <span className="font-bold text-white text-sm">{g.unit_code}: {g.unit_title}</span>
                    <span className="text-xs text-slate-400 block mt-0.5">{g.class_name} ({g.year_label})</span>
                  </div>

                  <div className="flex items-center gap-4 text-right">
                    <div>
                      <span className="text-[10px] text-slate-400 uppercase font-mono block">Coursework + Exam</span>
                      <span className="text-xs font-bold text-slate-200">{g.coursework_score} + {g.exam_score} = {g.total_score}%</span>
                    </div>

                    <div className="bg-slate-950 px-3 py-1.5 rounded-lg border border-slate-800">
                      <span className="text-[10px] text-slate-400 uppercase font-mono block">Grade</span>
                      <span className="text-sm font-extrabold text-teal-400">{g.letter_grade}</span>
                    </div>

                    <div className={`px-3 py-1.5 rounded-lg border font-mono text-xs font-bold ${
                      g.competency_outcome === "Competent" 
                        ? "bg-emerald-500/20 text-emerald-300 border-emerald-500/30" 
                        : "bg-amber-500/20 text-amber-300 border-amber-500/30"
                    }`}>
                      {g.competency_outcome}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* LECTURER GRADEBOOK MATRIX */}
          {isLecturerOrAdmin && (
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="border-b border-slate-800 text-slate-400 font-mono text-[10px] uppercase">
                    <th className="p-3">Index No</th>
                    <th className="p-3">Student Name</th>
                    <th className="p-3">Coursework (40%)</th>
                    <th className="p-3">Exam (60%)</th>
                    <th className="p-3">Total Score</th>
                    <th className="p-3">Letter Grade</th>
                    <th className="p-3">CBET Outcome</th>
                    <th className="p-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-850 text-slate-300">
                  {courseGrades.map((grade) => (
                    <tr key={grade.student_id} className="hover:bg-slate-900/50 transition-colors">
                      <td className="p-3 font-mono text-slate-400">{grade.index_number}</td>
                      <td className="p-3 font-bold text-white">{grade.student_name}</td>
                      <td className="p-3 font-mono text-slate-200">{grade.coursework_score}</td>
                      <td className="p-3 font-mono text-slate-200">{grade.exam_score}</td>
                      <td className="p-3 font-mono font-bold text-teal-300">{grade.total_score}%</td>
                      <td className="p-3 font-bold text-white">{grade.letter_grade}</td>
                      <td className="p-3">
                        <span className={`px-2 py-0.5 rounded text-[10px] font-mono font-bold ${
                          grade.competency_outcome === "Competent" 
                            ? "bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" 
                            : "bg-amber-500/20 text-amber-300 border border-amber-500/30"
                        }`}>
                          {grade.competency_outcome}
                        </span>
                      </td>
                      <td className="p-3 text-right">
                        <button
                          onClick={() => setEditingGrade({
                            student_id: grade.student_id,
                            student_name: grade.student_name || "Student",
                            coursework: grade.coursework_score,
                            exam: grade.exam_score
                          })}
                          className="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-teal-300 rounded font-semibold text-xs transition-all cursor-pointer"
                        >
                          Edit Grade
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}

      {/* CREATE ASSIGNMENT MODAL */}
      {showCreateAssignmentModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl">
            <h3 className="text-sm font-bold text-white mb-4">Create Course Assignment</h3>
            <form onSubmit={handleCreateAssignment} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="text-slate-400 block mb-1">Title *</label>
                <input 
                  type="text"
                  required
                  value={newAssignTitle}
                  onChange={(e) => setNewAssignTitle(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                  placeholder="e.g. Practical Assignment 1: Safety Check"
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Description</label>
                <textarea 
                  rows={2}
                  value={newAssignDesc}
                  onChange={(e) => setNewAssignDesc(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Max Marks</label>
                <input 
                  type="number"
                  value={newAssignMaxMarks}
                  onChange={(e) => setNewAssignMaxMarks(Number(e.target.value))}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>

              <div className="flex justify-end gap-2 mt-4">
                <button
                  type="button"
                  onClick={() => setShowCreateAssignmentModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-teal-500 text-slate-950 rounded-xl font-bold"
                >
                  Create
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* STUDENT SUBMISSION MODAL */}
      {showSubmissionModal && selectedAssignment && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl">
            <h3 className="text-sm font-bold text-white mb-2">Submit Assignment</h3>
            <p className="text-xs text-slate-400 mb-4">{selectedAssignment.title}</p>

            <form onSubmit={handleStudentSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="text-slate-400 block mb-1">Text Response / Notes</label>
                <textarea 
                  rows={3}
                  value={submitText}
                  onChange={(e) => setSubmitText(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                  placeholder="Enter text notes or calculation summaries..."
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">File Attachment (PDF, DOCX, ZIP, PNG)</label>
                <input 
                  type="file"
                  onChange={(e) => setSubmitFile(e.target.files?.[0] || null)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-slate-300"
                />
              </div>

              <div className="flex justify-end gap-2 mt-4">
                <button
                  type="button"
                  onClick={() => setShowSubmissionModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-4 py-2 bg-teal-500 text-slate-950 rounded-xl font-bold"
                >
                  {isSubmitting ? "Uploading..." : "Upload & Submit"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* LECTURER GRADING MODAL */}
      {showGradingModal && selectedAssignment && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <h3 className="text-sm font-bold text-white mb-1">Grade Submission</h3>
            <span className="text-xs text-teal-400 font-bold block mb-4">{showGradingModal.student_name}</span>

            <form onSubmit={handleGradeSubmissionSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="text-slate-400 block mb-1">Marks Awarded (Max: {selectedAssignment.max_marks}) *</label>
                <input 
                  type="number"
                  required
                  min={0}
                  max={selectedAssignment.max_marks}
                  value={gradeMarks}
                  onChange={(e) => setGradeMarks(Number(e.target.value))}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white font-mono font-bold"
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Feedback Comments</label>
                <textarea 
                  rows={3}
                  value={gradeFeedback}
                  onChange={(e) => setGradeFeedback(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                  placeholder="Written evaluation feedback..."
                />
              </div>

              <div className="flex justify-end gap-2 mt-4">
                <button
                  type="button"
                  onClick={() => setShowGradingModal(null)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-teal-500 text-slate-950 rounded-xl font-bold"
                >
                  Save Grade
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* CREATE QUIZ MODAL */}
      {showCreateQuizModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl">
            <h3 className="text-sm font-bold text-white mb-4">Create Online Quiz</h3>
            <form onSubmit={handleCreateQuiz} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="text-slate-400 block mb-1">Quiz Title *</label>
                <input 
                  type="text"
                  required
                  value={newQuizTitle}
                  onChange={(e) => setNewQuizTitle(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-slate-400 block mb-1">Time Limit (Mins)</label>
                  <input 
                    type="number"
                    value={newQuizTimeLimit}
                    onChange={(e) => setNewQuizTimeLimit(Number(e.target.value))}
                    className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                  />
                </div>
                <div>
                  <label className="text-slate-400 block mb-1">Passing %</label>
                  <input 
                    type="number"
                    value={newQuizPassingPct}
                    onChange={(e) => setNewQuizPassingPct(Number(e.target.value))}
                    className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                  />
                </div>
              </div>

              <div className="flex justify-end gap-2 mt-4">
                <button
                  type="button"
                  onClick={() => setShowCreateQuizModal(false)}
                  className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 bg-teal-500 text-slate-950 rounded-xl font-bold"
                >
                  Create Quiz
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ADD QUESTION MODAL */}
      {showAddQuestionModal && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl">
            <h3 className="text-sm font-bold text-white mb-4">Add Question to Quiz</h3>
            <form onSubmit={handleAddQuestion} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="text-slate-400 block mb-1">Question Text *</label>
                <input 
                  type="text"
                  required
                  value={newQText}
                  onChange={(e) => setNewQText(e.target.value)}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-slate-400 block mb-1">Option A</label>
                  <input type="text" value={newQOptionA} onChange={(e) => setNewQOptionA(e.target.value)} className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-white" />
                </div>
                <div>
                  <label className="text-slate-400 block mb-1">Option B</label>
                  <input type="text" value={newQOptionB} onChange={(e) => setNewQOptionB(e.target.value)} className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-white" />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="text-slate-400 block mb-1">Option C</label>
                  <input type="text" value={newQOptionC} onChange={(e) => setNewQOptionC(e.target.value)} className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-white" />
                </div>
                <div>
                  <label className="text-slate-400 block mb-1">Option D</label>
                  <input type="text" value={newQOptionD} onChange={(e) => setNewQOptionD(e.target.value)} className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-white" />
                </div>
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Correct Option Key</label>
                <select value={newQCorrectOpt} onChange={(e) => setNewQCorrectOpt(Number(e.target.value))} className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-teal-300 font-bold">
                  <option value={0}>Option A</option>
                  <option value={1}>Option B</option>
                  <option value={2}>Option C</option>
                  <option value={3}>Option D</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 mt-4">
                <button type="button" onClick={() => setShowAddQuestionModal(null)} className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 bg-teal-500 text-slate-950 rounded-xl font-bold">
                  Add Question
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* EDIT GRADE MODAL */}
      {editingGrade && (
        <div className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl">
            <h3 className="text-sm font-bold text-white mb-1">Edit Student Grade</h3>
            <span className="text-xs text-teal-400 font-bold block mb-4">{editingGrade.student_name}</span>

            <form onSubmit={handleSaveGradeSubmit} className="flex flex-col gap-3 text-xs">
              <div>
                <label className="text-slate-400 block mb-1">Coursework Score (Out of 40)</label>
                <input 
                  type="number"
                  min={0}
                  max={40}
                  value={editingGrade.coursework}
                  onChange={(e) => setEditingGrade({ ...editingGrade, coursework: Number(e.target.value) })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white font-mono font-bold"
                />
              </div>

              <div>
                <label className="text-slate-400 block mb-1">Examination Score (Out of 60)</label>
                <input 
                  type="number"
                  min={0}
                  max={60}
                  value={editingGrade.exam}
                  onChange={(e) => setEditingGrade({ ...editingGrade, exam: Number(e.target.value) })}
                  className="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-white font-mono font-bold"
                />
              </div>

              <div className="flex justify-end gap-2 mt-4">
                <button type="button" onClick={() => setEditingGrade(null)} className="px-4 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold">
                  Cancel
                </button>
                <button type="submit" className="px-4 py-2 bg-teal-500 text-slate-950 rounded-xl font-bold">
                  Save Scores
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </motion.div>
  );
}

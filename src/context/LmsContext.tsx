import React, { createContext, useContext, useState, useEffect } from "react";
import { loginApi, logoutApi, getMeApi } from "../api/auth";
import { ApiError, onSessionExpired } from "../api/client";

export interface QuestionOption {
  value: string;
  label: string;
  desc: string;
}

export interface Question {
  id: string;
  category: string;
  title: string;
  questionText: string;
  description: string;
  type: 'select' | 'multiselect' | 'text';
  options?: QuestionOption[];
  placeholder?: string;
}

export interface AuthRole {
  id: number;
  name: string;
  description: string;
}

export interface AuthDepartment {
  id: number;
  code: string;
  name: string;
  is_head_of_department: number;
}

export interface AuthUser {
  id: number;
  email: string;
  first_name: string;
  last_name: string;
  registration_number: string;
  roles: AuthRole[];
  permissions: string[];
  departments: AuthDepartment[];
  profile?: any;
  is_demo_profile?: boolean;
}

export const QUESTIONS: Question[] = [
  {
    id: "roles",
    category: "User Roles & Permissions",
    title: "1. User Roles & Access Control",
    questionText: "Which user roles must the LMS support at launch?",
    description: "These roles will dictate dashboard views, access permissions, and menu items.",
    type: "multiselect",
    options: [
      { value: "super_admin", label: "Super Administrator", desc: "Full system access, backup management, global settings." },
      { value: "registrar", label: "Registrar", desc: "Manages student admissions, course enrollments, class lists, and academic periods." },
      { value: "hod", label: "Head of Department (HOD)", desc: "Approves learning materials, view department-wide student/lecturer statistics." },
      { value: "lecturer", label: "Lecturer / Instructor", desc: "Uploads lessons, creates quizzes, grades assignments, records workshop attendance." },
      { value: "student", label: "Student", desc: "Views learning materials, attempts quizzes, submits assignments, views progress." },
      { value: "accountant", label: "Accountant / Finance Officer", desc: "Tracks fee structures, records manual invoice updates, views payment reports." },
      { value: "ict_admin", label: "ICT Administrator", desc: "Manages server resources, handles integration configurations, views audit logs." }
    ]
  },
  {
    id: "admission",
    category: "Student Management",
    title: "2. Student Admission & Registration Method",
    questionText: "How should students be admitted and registered onto the LMS?",
    description: "Determines the security boundaries for user onboarding.",
    type: "select",
    options: [
      { value: "admin_only", label: "Registrar / Administrator Only", desc: "Direct manual entry and CSV bulk upload by the Registrar. Highly secure, no public registration." },
      { value: "self_with_approval", label: "Self-Registration with Admin Approval", desc: "Students register online using their registration number, but accounts remain locked until a Registrar approves." },
      { value: "self_auto", label: "Self-Registration via Academic Email Domain", desc: "Students with @gilgiltvc.ac.ke email register and auto-activate their accounts." }
    ]
  },
  {
    id: "attendance_type",
    category: "Attendance Tracking",
    title: "3. Vocational Workshop Attendance",
    questionText: "Does Gilgil TVC require specialized attendance tracking for practical workshops?",
    description: "Gilgil TVC is heavily TVET-focused (e.g., plumbing, welding, automotive) where practical workshop hours are mandatory for national exams (KNEC/CDACC).",
    type: "select",
    options: [
      { value: "separate_workshop", label: "Separate Theory & Practical (Workshop) Attendance", desc: "Enable distinct tracking of practical workshop hours with threshold warnings (e.g., student flagged if workshop attendance < 75%)." },
      { value: "simple_attendance", label: "Standard Session Attendance Only", desc: "Simple present/absent tracking per class unit, no specialized workshop ledger." }
    ]
  },
  {
    id: "lessons_format",
    category: "Online Learning Materials",
    title: "4. Learning Materials & Video hosting",
    questionText: "What format should the LMS support for online learning lessons?",
    description: "Ensures low-cost storage and matches the capabilities of standard shared hosting.",
    type: "multiselect",
    options: [
      { value: "rich_text", label: "Rich Text & Markdown Editor", desc: "Highly compact lessons stored directly in the database (consumes zero disk space)." },
      { value: "pdf_docs", label: "PDF & Word Document Uploads", desc: "Lecturers upload reference sheets (validated server-side with strict mime checks, stored in public/uploads/)." },
      { value: "embedded_videos", label: "Embedded Video Links (YouTube/Vimeo)", desc: "Low-cost video hosting by embedding YouTube/Vimeo. Zero load on shared hosting servers." },
      { value: "mp4_upload", label: "Direct MP4 Video Upload", desc: "Caution: Directly uploading large videos will quickly exhaust shared hosting storage and bandwidth." }
    ]
  },
  {
    id: "assignment_submissions",
    category: "Assignments",
    title: "5. Practical Work & Assignment Submissions",
    questionText: "What submission types should be supported for vocational assignments?",
    description: "Allows lecturers to grade practical hands-on demonstrations.",
    type: "multiselect",
    options: [
      { value: "text_answers", label: "Rich-Text Online Submissions", desc: "Students type their responses directly into a rich text box." },
      { value: "pdf_doc_uploads", label: "PDF / Office Documents", desc: "Standard files like project reports, lab write-ups." },
      { value: "image_uploads", label: "Image / Photo Uploads (e.g., JPG, PNG)", desc: "Crucial for vocational tasks—students upload photographs of their physical workshop projects (e.g., plumbing joint, dress model, wiring panel)." },
      { value: "video_demo_uploads", label: "Video Demonstration Uploads (under 20MB)", desc: "Students upload short clips demonstrating a practical skill (welding, styling, cooking)." }
    ]
  },
  {
    id: "grading_scale",
    category: "Grading and Assessments",
    title: "6. Assessment Weights & Grading Policy",
    questionText: "What is the primary assessment and grading scheme for Gilgil TVC?",
    description: "Determines how the gradebook handles weights for vocational qualifications.",
    type: "select",
    options: [
      { value: "tvet_split", label: "TVET Dual Weighting (60% Practical + 40% Theory)", desc: "Ideal for Levels 4-6 TVET programs. Tracks practical workshop assignments with higher weight." },
      { value: "knec_standard", label: "KNEC Exam Syllabus Format (30% Coursework + 70% Final Exam)", desc: "Traditional academic weighting pattern." },
      { value: "cbet_competency", label: "CBET Mastery Based (Competent vs. Not Yet Competent)", desc: "Direct CDACC TVET assessment criteria, no percentage grade." }
    ]
  },
  {
    id: "fees_integration",
    category: "Finance & Fee Structure",
    title: "7. Student Fee Clearance & Ledger Integration",
    questionText: "How should the LMS interact with student fee payment balances?",
    description: "Prevents students with huge fee arrears from taking exams, while maintaining server simplicity.",
    type: "select",
    options: [
      { value: "display_only", label: "Read-Only Fee Status Display (Manual Updates by Accountant)", desc: "Accountants update balances manually or upload CSV billing statements. Safest & simplest for PHP hosting." },
      { value: "mpesa_direct", label: "Direct M-Pesa C2B / Paybill Webhook Listener", desc: "System auto-reconciles M-Pesa transactions via a secure PHP webhook endpoint." },
      { value: "no_fees", label: "Disable Fee Tracking on LMS", desc: "Keep financial accounts strictly off the LMS platform." }
    ]
  },
  {
    id: "security_tier",
    category: "Security & Hosting Hardening",
    title: "8. Platform Security & Defensive Features",
    questionText: "Which security hardening mechanisms must be active on the PHP backend?",
    description: "Defends against standard OWASP web vulnerabilities.",
    type: "multiselect",
    options: [
      { value: "brute_force", label: "Brute-Force Rate Limiting", desc: "Locks accounts temporarily after 5 invalid password attempts." },
      { value: "audit_logs", label: "Comprehensive Security Audit Logs", desc: "Logs all logins, grade edits, and admin updates to the `audit_logs` table." },
      { value: "csrf_strict", label: "Strict CSRF & SameSite Session Cookies", desc: "Enforces HttpOnly, SameSite=Lax session cookies with token validation." },
      { value: "input_sanitize", label: "MIME File Validation & SQL Prepared Statements", desc: "Zero raw SQL queries; strict image/PDF magic-byte validation." }
    ]
  },
  {
    id: "php_version",
    category: "Server Infrastructure",
    title: "9. PHP Server Environment",
    questionText: "What PHP runtime environment is deployed on Gilgil TVC's servers?",
    description: "Informs language syntax compatibility and PDO driver setup.",
    type: "select",
    options: [
      { value: "php82_plus", label: "PHP 8.1 / 8.2+ (Modern Typed OOP Architecture)", desc: "Utilizes strict types, enum matching, and native PDO prepared statements." },
      { value: "php74", label: "PHP 7.4 Legacy Server Compatibility", desc: "Fallbacks for older CPanel Linux hosting servers." }
    ]
  }
];

export interface Announcement {
  id: string;
  title: string;
  content: string;
  date: string;
  author: string;
  category: 'General' | 'Exam' | 'Workshop' | 'Fees';
}

export interface ChatMessage {
  sender: 'user' | 'architect';
  text: string;
  time: string;
}

export interface WorkshopStudent {
  id: string;
  name: string;
  reg: string;
  theory: number;
  workshopHours: number;
  requiredHours: number;
  status: 'Normal' | 'Warning' | 'Compliant';
}

interface LmsContextType {
  activeTab: string;
  setActiveTab: (tab: string) => void;
  answers: Record<string, string | string[]>;
  setAnswers: React.Dispatch<React.SetStateAction<Record<string, string | string[]>>>;
  currentQuestionIndex: number;
  setCurrentQuestionIndex: React.Dispatch<React.SetStateAction<number>>;
  textInput: string;
  setTextInput: (val: string) => void;
  customAnnouncements: Announcement[];
  setCustomAnnouncements: React.Dispatch<React.SetStateAction<Announcement[]>>;
  chatLog: ChatMessage[];
  setChatLog: React.Dispatch<React.SetStateAction<ChatMessage[]>>;
  chatInput: string;
  setChatInput: (val: string) => void;
  simRole: "student" | "lecturer" | "registrar";
  setSimRole: (role: "student" | "lecturer" | "registrar") => void;
  simSelectedCourse: string;
  setSimSelectedCourse: (c: string) => void;
  lessonsCompleted: string[];
  setLessonsCompleted: React.Dispatch<React.SetStateAction<string[]>>;
  quizScore: number | null;
  setQuizScore: React.Dispatch<React.SetStateAction<number | null>>;
  assignmentSubmitted: boolean;
  setAssignmentSubmitted: (val: boolean) => void;
  activeQuizQuestion: number;
  setActiveQuizQuestion: (val: number) => void;
  quizAnswers: Record<number, number>;
  setQuizAnswers: React.Dispatch<React.SetStateAction<Record<number, number>>>;
  submittedQuiz: boolean;
  setSubmittedQuiz: (val: boolean) => void;
  dbViewMode: "erd" | "ddl";
  setDbViewMode: (val: "erd" | "ddl") => void;
  selectedDbTable: string;
  setSelectedDbTable: (tbl: string) => void;
  selectedPhpFile: string;
  setSelectedPhpFile: (file: string) => void;
  selectedApiRoute: string;
  setSelectedApiRoute: (route: string) => void;
  workshopStudents: WorkshopStudent[];
  setWorkshopStudents: React.Dispatch<React.SetStateAction<WorkshopStudent[]>>;
  
  // Auth & Environment State
  authUser: AuthUser | null;
  authLoading: boolean;
  authError: string | null;
  isDemoSession: boolean;
  isDemoModeConfigured: boolean;
  setIsDemoModeConfigured: (val: boolean) => void;
  sessionExpiredNotice: string | null;
  clearSessionExpiredNotice: () => void;
  loginUser: (email: string, pass: string) => Promise<boolean>;
  logoutUser: () => Promise<void>;
  fetchMeUser: () => Promise<void>;

  handleChatSubmit: (e: React.FormEvent) => void;
  handleSelectOption: (val: string) => void;
  handleNextQuestion: () => void;
  handlePrevQuestion: () => void;
  isOptionSelected: (id: string, value: string) => boolean;
  handleToggleAttendance: (id: string) => void;
}

const LmsContext = createContext<LmsContextType | undefined>(undefined);

// Seed user catalog for interactive demonstration fallback
const DEMO_USERS: Record<string, AuthUser> = {
  "admin@gilgiltvc.ac.ke": {
    id: 1,
    email: "admin@gilgiltvc.ac.ke",
    first_name: "System",
    last_name: "Administrator",
    registration_number: "ADMIN001",
    roles: [{ id: 1, name: "super_admin", description: "System Administrator with unrestricted access" }],
    permissions: [
      "system.manage", "user.manage", "academic.manage", 
      "course.create", "grade.submit", "fee.collect", "attendance.mark"
    ],
    departments: [{ id: 1, code: "COMP", name: "ICT and Computer Studies", is_head_of_department: 1 }]
  },
  "pkiprop@gilgiltvc.ac.ke": {
    id: 2,
    email: "pkiprop@gilgiltvc.ac.ke",
    first_name: "Dr. Peter",
    last_name: "Kiprop",
    registration_number: "STAFF001",
    roles: [{ id: 3, name: "lecturer", description: "Academic Trainer / Instructor" }],
    permissions: ["course.create", "grade.submit", "attendance.mark"],
    departments: [{ id: 1, code: "COMP", name: "ICT and Computer Studies", is_head_of_department: 1 }]
  },
  "kmbugua@student.gilgiltvc.ac.ke": {
    id: 3,
    email: "kmbugua@student.gilgiltvc.ac.ke",
    first_name: "Kevin",
    last_name: "Mbugua",
    registration_number: "GTVC/DIT/2025/001",
    roles: [{ id: 4, name: "student", description: "Enrolled Trainee / Student" }],
    permissions: ["course.view", "assignment.submit", "grade.view", "attendance.view"],
    departments: [{ id: 1, code: "COMP", name: "ICT and Computer Studies", is_head_of_department: 0 }]
  },
  "mwanjiru@gilgiltvc.ac.ke": {
    id: 4,
    email: "mwanjiru@gilgiltvc.ac.ke",
    first_name: "Mary",
    last_name: "Wanjiru",
    registration_number: "FIN001",
    roles: [{ id: 5, name: "accountant", description: "Finance Administrator" }],
    permissions: ["fee.collect", "fee.view"],
    departments: []
  }
};

export function LmsProvider({ children }: { children: React.ReactNode }) {
  const [activeTab, setActiveTab] = useState("interview");
  const [answers, setAnswers] = useState<Record<string, string | string[]>>({
    roles: ["super_admin", "registrar", "hod", "lecturer", "student", "accountant"],
    admission: "admin_only",
    attendance_type: "separate_workshop",
    lessons_format: ["rich_text", "pdf_docs", "embedded_videos"],
    assignment_submissions: ["text_answers", "pdf_doc_uploads", "image_uploads"],
    grading_scale: "tvet_split",
    fees_integration: "display_only",
    security_tier: ["brute_force", "audit_logs", "csrf_strict"],
    php_version: "php82_plus"
  });

  const [currentQuestionIndex, setCurrentQuestionIndex] = useState<number>(0);
  const [textInput, setTextInput] = useState<string>("");
  const [customAnnouncements, setCustomAnnouncements] = useState<Announcement[]>([
    { id: "1", title: "Registration for Term II CDACC Exams", content: "All ICT Level 6 and Automotive Engineering students are reminded to submit their assessment booklets to the department registrar before Friday.", date: "July 21, 2026", author: "Registrar Office", category: "Exam" },
    { id: "2", title: "Mandatory Workshop Safety Guidelines", content: "No student will be allowed inside the Welding & Fabrication or Automotive workshops without heavy leather work boots and protective overalls.", date: "July 20, 2026", author: "HOD Automotive", category: "Workshop" }
  ]);

  const [chatLog, setChatLog] = useState<ChatMessage[]>([
    { sender: "user", text: "Hi, I need an LMS for Gilgil Technical and Vocational College. We operate three intakes (Jan, May, Sep), have vocational departments like Automotive, ICT, Business, Electrical, Building, and Hospitality, and need standard low-cost shared hosting with MySQL and PHP.", time: "02:58" },
    { sender: "architect", text: "Greetings! I've loaded Gilgil TVC's academic profile (Jan/May/Sep cycles, TVET Levels 4-6, and departments). Let's systematically refine the architecture. I've compiled an interactive dashboard so you can visualize the Software Requirements Specification (SRS), MySQL tables, and secure PHP file structures live as we talk. Let's start with User Roles. Which roles should we support in the initial launch?", time: "02:59" }
  ]);
  const [chatInput, setChatInput] = useState("");

  // Auth & Mode Configuration state
  const [authUser, setAuthUser] = useState<AuthUser | null>(null);
  const [authLoading, setAuthLoading] = useState<boolean>(false);
  const [authError, setAuthError] = useState<string | null>(null);
  const [isDemoSession, setIsDemoSession] = useState<boolean>(false);
  const [isDemoModeConfigured, setIsDemoModeConfigured] = useState<boolean>(
    import.meta.env.VITE_DEMO_MODE === "true"
  );
  const [sessionExpiredNotice, setSessionExpiredNotice] = useState<string | null>(null);

  const clearSessionExpiredNotice = () => setSessionExpiredNotice(null);

  // Register session expiration handler for 401 API responses
  useEffect(() => {
    onSessionExpired(() => {
      setAuthUser(null);
      setIsDemoSession(false);
      setSessionExpiredNotice("Your active session has expired or was invalidated by the PHP backend. Please log in again.");
    });
  }, []);

  // On mount or mode toggle, check authentication status
  useEffect(() => {
    fetchMeUser();
  }, [isDemoModeConfigured]);

  const loginUser = async (email: string, pass: string): Promise<boolean> => {
    setAuthLoading(true);
    setAuthError(null);
    setSessionExpiredNotice(null);

    // 1. LIVE BACKEND MODE
    if (!isDemoModeConfigured) {
      try {
        const user = await loginApi({ email, password: pass });
        setAuthUser(user);
        setIsDemoSession(false); // Live verified backend session
        setAuthLoading(false);
        return true;
      } catch (err: any) {
        const msg = err instanceof ApiError 
          ? err.message 
          : "Authentication failed. Unable to communicate with live PHP/MySQL backend.";
        setAuthError(msg);
        setAuthUser(null);
        setIsDemoSession(false);
        setAuthLoading(false);
        return false;
      }
    }

    // 2. DEMO / SIMULATION MODE
    const matchedUser = DEMO_USERS[email.toLowerCase()];
    if (matchedUser && pass === "password") {
      setAuthUser({ ...matchedUser, is_demo_profile: true });
      setIsDemoSession(true); // Marked as Simulated Demo Session
      setAuthLoading(false);
      return true;
    }

    setAuthError("Demo Mode Authentication Failed. Use presets: admin@gilgiltvc.ac.ke, pkiprop@gilgiltvc.ac.ke, kmbugua@student.gilgiltvc.ac.ke with password 'password'.");
    setAuthLoading(false);
    return false;
  };

  const logoutUser = async (): Promise<void> => {
    setAuthLoading(true);
    if (!isDemoModeConfigured) {
      try {
        await logoutApi();
      } catch {
        // Silent catch for logout
      }
    }
    setAuthUser(null);
    setIsDemoSession(isDemoModeConfigured);
    setAuthLoading(false);
  };

  const fetchMeUser = async (): Promise<void> => {
    setAuthLoading(true);
    setAuthError(null);

    // Live Mode: query real PHP session
    if (!isDemoModeConfigured) {
      try {
        const user = await getMeApi();
        setAuthUser(user);
        setIsDemoSession(false);
      } catch {
        // Unauthenticated or backend offline: do NOT fall back silently in live mode
        setAuthUser(null);
        setIsDemoSession(false);
      } finally {
        setAuthLoading(false);
      }
      return;
    }

    // Demo Mode: default reference profile for developer inspection
    setAuthUser({ ...DEMO_USERS["admin@gilgiltvc.ac.ke"], is_demo_profile: true });
    setIsDemoSession(true);
    setAuthLoading(false);
  };

  const handleChatSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!chatInput.trim()) return;

    const userMsg = chatInput;
    setChatLog(prev => [...prev, { sender: "user", text: userMsg, time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }]);
    setChatInput("");

    setTimeout(() => {
      let responseText = "";
      if (userMsg.toLowerCase().includes("role") || userMsg.toLowerCase().includes("user")) {
        responseText = "Understood. The system will support Super Admin, Registrar, HOD, Lecturer, Student, and Accountant as standard. I've adjusted the DB tables and role models accordingly. You can see these under the 'Relational DB Schema' and 'User Simulation' tabs.";
      } else if (userMsg.toLowerCase().includes("fees") || userMsg.toLowerCase().includes("payment")) {
        responseText = "Got it. We will include a dedicated 'Fees Status Display' dashboard block. For safety and low-cost deployment, we will start with manual Accountant balance updates, with clean hooks to hook into M-Pesa's C2B API or KCB IPG banks later. Check out the DB updates on 'fee_records'!";
        setAnswers(prev => ({ ...prev, fees_integration: "display_only" }));
      } else if (userMsg.toLowerCase().includes("attendance") || userMsg.toLowerCase().includes("workshop")) {
        responseText = "Excellent point. Given Gilgil TVC's focus on technical trades, tracking theory attendance separate from mandatory hands-on Workshop Hours is essential. I have activated the specialized `workshop_attendance` tracking logic. You can simulate it in the 'User Simulation' tab!";
        setAnswers(prev => ({ ...prev, attendance_type: "separate_workshop" }));
      } else if (userMsg.toLowerCase().includes("upload") || userMsg.toLowerCase().includes("file") || userMsg.toLowerCase().includes("video")) {
        responseText = "Perfect choice. To ensure shared hosting longevity, we will restrict video content to embedded links (YouTube, Vimeo) while supporting secure, MIME-validated image uploads (for students to snap photos of their practical plumbing/electrical joints). We will enforce strict file validation in PHP to block any executable scripts (no .php, .sh upload vulnerability).";
      } else {
        responseText = "Understood! I've logged this requirement in our Living SRS file. Your instruction directly guides the modular PHP controllers. Please inspect the visual architecture diagrams on the tabs above to confirm.";
      }

      setChatLog(prev => [...prev, { sender: "architect", text: responseText, time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }]);
    }, 1000);
  };

  const handleSelectOption = (value: string) => {
    const currentQuestion = QUESTIONS[currentQuestionIndex];
    if (currentQuestion.type === "select") {
      setAnswers(prev => ({ ...prev, [currentQuestion.id]: value }));
    } else if (currentQuestion.type === "multiselect") {
      const currentSelection = (answers[currentQuestion.id] as string[]) || [];
      if (currentSelection.includes(value)) {
        setAnswers(prev => ({
          ...prev,
          [currentQuestion.id]: currentSelection.filter(item => item !== value)
        }));
      } else {
        setAnswers(prev => ({
          ...prev,
          [currentQuestion.id]: [...currentSelection, value]
        }));
      }
    }
  };

  const handleNextQuestion = () => {
    if (currentQuestionIndex < QUESTIONS.length - 1) {
      setCurrentQuestionIndex(prev => prev + 1);
    }
  };

  const handlePrevQuestion = () => {
    if (currentQuestionIndex > 0) {
      setCurrentQuestionIndex(prev => prev - 1);
    }
  };

  const isOptionSelected = (id: string, value: string) => {
    const val = answers[id];
    if (Array.isArray(val)) {
      return val.includes(value);
    }
    return val === value;
  };

  const [simRole, setSimRole] = useState<"student" | "lecturer" | "registrar">("student");
  const [simSelectedCourse, setSimSelectedCourse] = useState<string>("ICT6");
  const [lessonsCompleted, setLessonsCompleted] = useState<string[]>(["les1"]);
  const [quizScore, setQuizScore] = useState<number | null>(null);
  const [assignmentSubmitted, setAssignmentSubmitted] = useState<boolean>(false);
  const [activeQuizQuestion, setActiveQuizQuestion] = useState<number>(0);
  const [quizAnswers, setQuizAnswers] = useState<Record<number, number>>({});
  const [submittedQuiz, setSubmittedQuiz] = useState<boolean>(false);
  const [dbViewMode, setDbViewMode] = useState<"erd" | "ddl">("erd");
  const [selectedDbTable, setSelectedDbTable] = useState<string>("users");
  const [selectedPhpFile, setSelectedPhpFile] = useState<string>("config/database.php");
  const [selectedApiRoute, setSelectedApiRoute] = useState<string>("/api/v1/auth/login");

  const [workshopStudents, setWorkshopStudents] = useState<WorkshopStudent[]>([
    { id: "S1", name: "Kelvin Mbugua", reg: "GTVC/ICT/2025/089", theory: 92, workshopHours: 36, requiredHours: 40, status: "Normal" },
    { id: "S2", name: "Mercy Wanjiku", reg: "GTVC/MECH/2025/112", theory: 88, workshopHours: 28, requiredHours: 40, status: "Warning" },
    { id: "S3", name: "Brian Kiprotich", reg: "GTVC/ELECT/2025/045", theory: 75, workshopHours: 42, requiredHours: 40, status: "Compliant" },
    { id: "S4", name: "Faith Chemutai", reg: "GTVC/HOSP/2025/201", theory: 95, workshopHours: 38, requiredHours: 40, status: "Compliant" }
  ]);

  const handleToggleAttendance = (studentId: string) => {
    setWorkshopStudents(prev => prev.map(s => {
      if (s.id === studentId) {
        const added = s.workshopHours >= s.requiredHours ? -2 : 2;
        return {
          ...s,
          workshopHours: Math.max(0, s.workshopHours + added),
          status: s.workshopHours + added < 30 ? "Warning" : "Compliant"
        };
      }
      return s;
    }));
  };

  return (
    <LmsContext.Provider value={{
      activeTab, setActiveTab,
      answers, setAnswers,
      currentQuestionIndex, setCurrentQuestionIndex,
      textInput, setTextInput,
      customAnnouncements, setCustomAnnouncements,
      chatLog, setChatLog,
      chatInput, setChatInput,
      simRole, setSimRole,
      simSelectedCourse, setSimSelectedCourse,
      lessonsCompleted, setLessonsCompleted,
      quizScore, setQuizScore,
      assignmentSubmitted, setAssignmentSubmitted,
      activeQuizQuestion, setActiveQuizQuestion,
      quizAnswers, setQuizAnswers,
      submittedQuiz, setSubmittedQuiz,
      dbViewMode, setDbViewMode,
      selectedDbTable, setSelectedDbTable,
      selectedPhpFile, setSelectedPhpFile,
      selectedApiRoute, setSelectedApiRoute,
      workshopStudents, setWorkshopStudents,
      authUser,
      authLoading,
      authError,
      isDemoSession,
      isDemoModeConfigured,
      setIsDemoModeConfigured,
      sessionExpiredNotice,
      clearSessionExpiredNotice,
      loginUser,
      logoutUser,
      fetchMeUser,
      handleChatSubmit,
      handleSelectOption,
      handleNextQuestion,
      handlePrevQuestion,
      isOptionSelected,
      handleToggleAttendance
    }}>
      {children}
    </LmsContext.Provider>
  );
}

export function useLms() {
  const context = useContext(LmsContext);
  if (!context) {
    throw new Error("useLms must be used within an LmsProvider");
  }
  return context;
}

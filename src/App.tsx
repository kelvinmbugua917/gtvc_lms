import React, { useState } from "react";
import { 
  Database, FolderTree, ShieldCheck, HelpCircle, Activity, FileText, Award, BookOpen, FileCheck, Calendar, Megaphone
} from "lucide-react";
import { AnimatePresence } from "motion/react";
import { LmsProvider, useLms } from "./context/LmsContext";

// Import Page Components
import InterviewWizardPage from "./pages/InterviewWizardPage";
import LivingSrsPage from "./pages/LivingSrsPage";
import DbSchemaPage from "./pages/DbSchemaPage";
import PhpCodeStructurePage from "./pages/PhpCodeStructurePage";
import ApiMapPage from "./pages/ApiMapPage";
import SimulationPage from "./pages/SimulationPage";
import AcademicEnrollmentPage from "./pages/AcademicEnrollmentPage";
import LearningContentPage from "./pages/LearningContentPage";
import AssessmentsPage from "./pages/AssessmentsPage";
import AttendancePage from "./pages/AttendancePage";
import CommunicationPage from "./pages/CommunicationPage";

function LmsApp() {
  const { 
    activeTab, 
    setActiveTab, 
    authUser, 
    isDemoSession, 
    isDemoModeConfigured,
    sessionExpiredNotice,
    clearSessionExpiredNotice
  } = useLms();

  return (
    <div className="min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans selection:bg-teal-500 selection:text-slate-900" id="main-root">
      {/* Institutional Top Navigation */}
      <header className="border-b border-slate-800 bg-slate-950 px-6 py-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 z-10" id="app-header">
        <div className="flex items-center gap-3">
          <div className="bg-gradient-to-tr from-teal-500 to-emerald-400 p-2.5 rounded-xl shadow-lg shadow-teal-500/15 flex items-center justify-center">
            <Award className="w-6 h-6 text-slate-950" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                Gilgil TVC <span className="text-teal-400 font-normal text-sm px-2 py-0.5 bg-slate-800 rounded-md border border-slate-700">LMS Blueprint</span>
              </h1>
              <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded ${
                !isDemoModeConfigured 
                  ? "bg-teal-500/15 text-teal-300 border border-teal-500/30" 
                  : "bg-amber-500/15 text-amber-300 border border-amber-500/30"
              }`}>
                {!isDemoModeConfigured ? "🟢 LIVE BACKEND MODE" : "🟡 DEMO / SIMULATION MODE"}
              </span>
            </div>
            <p className="text-xs text-slate-400">Technical & Vocational College Learning Management System Engine</p>
          </div>
        </div>

        {/* User Auth Badge & Navigation Tabs */}
        <div className="flex flex-col md:flex-row items-start md:items-center gap-3 w-full md:w-auto">
          {authUser && (
            <div className="flex items-center gap-2 bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800 text-xs">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
              <span className="text-slate-200 font-bold">{authUser.first_name} {authUser.last_name}</span>
              <span className="text-[10px] bg-teal-500/20 text-teal-300 px-1.5 py-0.5 rounded font-mono font-bold uppercase">
                {authUser.roles[0]?.name || "User"}
              </span>
            </div>
          )}

          <nav className="flex flex-wrap gap-1 bg-slate-900 p-1 rounded-xl border border-slate-800 w-full md:w-auto" id="main-nav">
            <button 
              id="tab-btn-interview"
              onClick={() => setActiveTab("interview")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "interview" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <HelpCircle className="w-3.5 h-3.5" />
              <span>Interview Wizard</span>
            </button>
            <button 
              id="tab-btn-srs"
              onClick={() => setActiveTab("srs")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "srs" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <FileText className="w-3.5 h-3.5" />
              <span>Living SRS</span>
            </button>
            <button 
              id="tab-btn-db"
              onClick={() => setActiveTab("db")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "db" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <Database className="w-3.5 h-3.5" />
              <span>MySQL Schema</span>
            </button>
            <button 
              id="tab-btn-files"
              onClick={() => setActiveTab("files")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "files" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <FolderTree className="w-3.5 h-3.5" />
              <span>PHP Structure</span>
            </button>
            <button 
              id="tab-btn-api"
              onClick={() => setActiveTab("api")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "api" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <ShieldCheck className="w-3.5 h-3.5" />
              <span>API Inspector</span>
            </button>
            <button 
              id="tab-btn-academic"
              onClick={() => setActiveTab("academic")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "academic" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <Award className="w-3.5 h-3.5" />
              <span>Academic & Cohorts</span>
            </button>
            <button 
              id="tab-btn-learning"
              onClick={() => setActiveTab("learning")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "learning" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <BookOpen className="w-3.5 h-3.5" />
              <span>Learning & Delivery</span>
            </button>
            <button 
              id="tab-btn-assessments"
              onClick={() => setActiveTab("assessments")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "assessments" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <FileCheck className="w-3.5 h-3.5" />
              <span>Assessments & Grading</span>
            </button>
            <button 
              id="tab-btn-attendance"
              onClick={() => setActiveTab("attendance")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "attendance" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <Calendar className="w-3.5 h-3.5" />
              <span>Attendance & Workshop</span>
            </button>
            <button 
              id="tab-btn-communication"
              onClick={() => setActiveTab("communication")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "communication" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <Megaphone className="w-3.5 h-3.5" />
              <span>Communication & Notices</span>
            </button>
            <button 
              id="tab-btn-sim"
              onClick={() => setActiveTab("simulation")}
              className={`flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg transition-all cursor-pointer ${
                activeTab === "simulation" 
                  ? "bg-teal-500 text-slate-950 font-bold shadow-md shadow-teal-500/10" 
                  : "text-slate-400 hover:text-white hover:bg-slate-800"
              }`}
            >
              <Activity className="w-3.5 h-3.5" />
              <span>UX Simulator</span>
            </button>
          </nav>
        </div>
      </header>

      {/* Global Session Expired Warning Banner */}
      {sessionExpiredNotice && (
        <div className="bg-red-500/15 border-b border-red-500/30 px-6 py-2.5 flex justify-between items-center text-xs text-red-200">
          <span className="font-semibold">{sessionExpiredNotice}</span>
          <button 
            onClick={clearSessionExpiredNotice} 
            className="bg-red-500/20 hover:bg-red-500/30 text-red-100 px-2.5 py-1 rounded text-[11px] font-mono cursor-pointer"
          >
            Dismiss
          </button>
        </div>
      )}

      {/* Main Body Layout */}
      <main className="flex-1 overflow-y-auto p-4 md:p-6" id="app-main-content">
        <AnimatePresence mode="wait">
          {activeTab === "interview" && <InterviewWizardPage onViewSrs={() => setActiveTab("srs")} />}
          {activeTab === "srs" && <LivingSrsPage />}
          {activeTab === "db" && <DbSchemaPage />}
          {activeTab === "files" && <PhpCodeStructurePage />}
          {activeTab === "api" && <ApiMapPage />}
          {activeTab === "academic" && <AcademicEnrollmentPage />}
          {activeTab === "learning" && <LearningContentPage />}
          {activeTab === "assessments" && <AssessmentsPage />}
          {activeTab === "attendance" && <AttendancePage />}
          {activeTab === "communication" && <CommunicationPage />}
          {activeTab === "simulation" && <SimulationPage />}
        </AnimatePresence>
      </main>

      {/* Interactive Footer */}
      <footer className="bg-slate-950 border-t border-slate-800 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500" id="app-footer">
        <div>
          <span>© 2026 Gilgil Technical and Vocational College (Gilgil TVC) LMS Engine.</span>
        </div>
        <div className="flex gap-4">
          <span className="flex items-center gap-1 text-slate-400 select-none">
            <ShieldCheck className="w-4 h-4 text-teal-400" />
            Security Audit: Compliant
          </span>
          <span className="flex items-center gap-1 text-slate-400 font-mono select-none">
            PHP v8.2+ • MySQL v8.0 • Apache 2.4
          </span>
        </div>
      </footer>
    </div>
  );
}

export default function App() {
  return (
    <LmsProvider>
      <LmsApp />
    </LmsProvider>
  );
}

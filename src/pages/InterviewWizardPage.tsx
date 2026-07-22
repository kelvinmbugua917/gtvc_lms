import React from "react";
import { HelpCircle, ChevronLeft, ChevronRight, CheckCircle2, Check, Send, ShieldCheck } from "lucide-react";
import { motion } from "motion/react";
import { useLms, QUESTIONS } from "../context/LmsContext";

export default function InterviewWizardPage() {
  const {
    currentQuestionIndex,
    answers,
    chatLog,
    chatInput,
    setChatInput,
    handleChatSubmit,
    handleSelectOption,
    handleNextQuestion,
    handlePrevQuestion,
    isOptionSelected,
    setActiveTab
  } = useLms();

  const currentQuestion = QUESTIONS[currentQuestionIndex];

  return (
    <motion.div 
      key="interview-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="grid grid-cols-1 lg:grid-cols-12 gap-6"
    >
      {/* Interview Q&A Wizard - 7 columns */}
      <div className="lg:col-span-7 flex flex-col gap-6" id="interview-wizard-panel">
        <div className="bg-slate-950 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
          {/* Progress Header */}
          <div className="bg-slate-900 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <span className="text-xs font-semibold uppercase tracking-wider text-teal-400 font-mono">Interactive Specifications Engine</span>
            <span className="text-xs text-slate-400 font-mono bg-slate-800 px-2 py-1 rounded-md border border-slate-700">
              Step {currentQuestionIndex + 1} of {QUESTIONS.length}
            </span>
          </div>
          
          {/* Progress bar */}
          <div className="w-full bg-slate-800 h-1">
            <div 
              className="bg-gradient-to-r from-teal-500 to-emerald-400 h-1 transition-all duration-300"
              style={{ width: `${((currentQuestionIndex + 1) / QUESTIONS.length) * 100}%` }}
            />
          </div>

          {/* Question Card */}
          <div className="p-6 md:p-8 flex flex-col gap-6">
            <div className="flex items-center gap-2">
              <span className="px-2.5 py-1 text-xs font-medium rounded bg-teal-500/10 text-teal-400 border border-teal-500/20">
                {currentQuestion.category}
              </span>
            </div>

            <div>
              <h2 className="text-2xl font-bold text-white tracking-tight leading-snug">
                {currentQuestion.questionText}
              </h2>
              <p className="text-slate-400 mt-2 text-sm leading-relaxed">
                {currentQuestion.description}
              </p>
            </div>

            {/* Dynamic Option Selector */}
            <div className="grid grid-cols-1 gap-3.5 my-2">
              {currentQuestion.options?.map((opt) => {
                const selected = isOptionSelected(currentQuestion.id, opt.value);
                return (
                  <button
                    key={opt.value}
                    onClick={() => handleSelectOption(opt.value)}
                    className={`text-left p-4 rounded-xl border transition-all flex justify-between items-start gap-4 cursor-pointer group ${
                      selected 
                        ? "bg-teal-950/40 border-teal-500/80 shadow-md shadow-teal-500/5 text-white" 
                        : "bg-slate-900 hover:bg-slate-850 border-slate-800 hover:border-slate-700 text-slate-350"
                    }`}
                  >
                    <div className="flex-1">
                      <span className={`text-sm font-semibold block transition-colors ${selected ? "text-teal-400" : "text-white"}`}>
                        {opt.label}
                      </span>
                      <span className="text-xs text-slate-400 block mt-1 leading-relaxed">
                        {opt.desc}
                      </span>
                    </div>
                    <div className={`mt-0.5 rounded-full p-0.5 w-5 h-5 flex items-center justify-center border transition-all ${
                      selected 
                        ? "bg-teal-555 border-teal-400 text-white" 
                        : "border-slate-700 text-transparent group-hover:border-slate-500"
                    }`}>
                      <Check className="w-3.5 h-3.5 stroke-[3]" />
                    </div>
                  </button>
                );
              })}
            </div>

            {/* Wizard Buttons */}
            <div className="flex justify-between items-center border-t border-slate-800 pt-6 mt-4">
              <button
                onClick={handlePrevQuestion}
                disabled={currentQuestionIndex === 0}
                className="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-800 text-sm font-semibold text-slate-400 hover:text-white hover:bg-slate-900 disabled:opacity-40 disabled:hover:bg-transparent transition-all cursor-pointer"
              >
                <ChevronLeft className="w-4 h-4" />
                Back
              </button>

              <div className="flex gap-2">
                <button
                  onClick={() => {
                    // Direct SRS tab jump to review results
                    setActiveTab("srs");
                  }}
                  className="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-all"
                >
                  Review SRS Doc
                </button>
                
                {currentQuestionIndex < QUESTIONS.length - 1 ? (
                  <button
                    onClick={handleNextQuestion}
                    className="flex items-center gap-2 bg-teal-555 hover:bg-teal-500 text-slate-950 px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-teal-500/10 transition-all cursor-pointer"
                  >
                    Next Requirement
                    <ChevronRight className="w-4 h-4" />
                  </button>
                ) : (
                  <button
                    onClick={() => setActiveTab("srs")}
                    className="flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-450 hover:to-teal-450 text-slate-950 px-5 py-2.5 rounded-xl text-sm font-black shadow-lg shadow-emerald-500/15 transition-all cursor-pointer"
                  >
                    <CheckCircle2 className="w-4 h-4" />
                    Generate Full SRS
                  </button>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Live Architecture Status card */}
        <div className="bg-slate-950 p-5 rounded-2xl border border-slate-800 flex items-start gap-4">
          <div className="bg-teal-500/10 text-teal-400 p-2.5 rounded-xl border border-teal-500/10">
            <ShieldCheck className="w-5 h-5" />
          </div>
          <div>
            <h3 className="text-sm font-bold text-white">Dynamic Architectural Compliance</h3>
            <p className="text-xs text-slate-400 mt-1 leading-relaxed">
              As you modify options, your requirements automatically update the visual <span className="text-teal-400 font-mono">MySQL database models</span>, generate compliant <span className="text-teal-400 font-mono">secure PDO statements</span>, and map the precise controller folder hierarchies.
            </p>
          </div>
        </div>
      </div>

      {/* Chat Interview Companion - 5 columns */}
      <div className="lg:col-span-5 flex flex-col gap-6" id="chat-companion-panel">
        <div className="bg-slate-950 rounded-2xl border border-slate-800 shadow-xl overflow-hidden flex flex-col h-[580px]">
          {/* Chat Header */}
          <div className="bg-slate-900 px-5 py-3.5 border-b border-slate-800 flex items-center justify-between">
            <div className="flex items-center gap-2.5">
              <div className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" />
              <div>
                <span className="text-sm font-bold text-white block">Senior Software Architect</span>
                <span className="text-[10px] text-slate-400 font-mono">Live SRS Compiler Mode</span>
              </div>
            </div>
            <span className="text-[10px] bg-slate-800 border border-slate-700 text-slate-400 font-mono px-2 py-0.5 rounded">
              LAMP Expert
            </span>
          </div>

          {/* Chat Logs */}
          <div className="flex-1 overflow-y-auto p-4 flex flex-col gap-4">
            {chatLog.map((log, i) => (
              <div 
                key={i} 
                className={`max-w-[85%] flex flex-col gap-1 ${log.sender === "user" ? "self-end items-end" : "self-start items-start"}`}
              >
                <div className={`p-3.5 rounded-2xl text-xs leading-relaxed ${
                  log.sender === "user" 
                    ? "bg-teal-555 text-slate-950 font-medium rounded-tr-none" 
                    : "bg-slate-900 text-slate-300 border border-slate-800 rounded-tl-none"
                }`}>
                  {log.text}
                </div>
                <span className="text-[9px] text-slate-500 font-mono px-1">{log.time}</span>
              </div>
            ))}
          </div>

          {/* Chat Input */}
          <form onSubmit={handleChatSubmit} className="p-3 border-t border-slate-800 bg-slate-900 flex gap-2">
            <input
              type="text"
              value={chatInput}
              onChange={(e) => setChatInput(e.target.value)}
              placeholder="Discuss details (e.g. 'add exam rules', 'M-Pesa integration')..."
              className="flex-1 bg-slate-950 border border-slate-800 text-slate-100 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:border-teal-555 placeholder-slate-500"
            />
            <button
              type="submit"
              className="bg-teal-555 hover:bg-teal-500 text-slate-950 p-2.5 rounded-xl flex items-center justify-center shadow shadow-teal-500/10 cursor-pointer"
            >
              <Send className="w-4 h-4" />
            </button>
          </form>
        </div>

        {/* College Profile Sidebar Quick stats */}
        <div className="bg-slate-950 border border-slate-800 rounded-2xl p-5 flex flex-col gap-4">
          <div className="border-b border-slate-855 pb-3 flex justify-between items-center">
            <span className="text-xs font-bold uppercase text-slate-300 tracking-wider font-sans">Institution Profile</span>
            <span className="text-[10px] bg-slate-900 border border-slate-800 px-2 py-0.5 rounded text-teal-400 font-mono">Gilgil TVC</span>
          </div>
          
          <div className="grid grid-cols-2 gap-3.5 text-xs">
            <div className="bg-slate-900/60 p-3 rounded-xl border border-slate-850">
              <span className="text-slate-500 block">Academic cycles</span>
              <span className="font-semibold text-white mt-1 block">3 Intakes / Year</span>
            </div>
            <div className="bg-slate-900/60 p-3 rounded-xl border border-slate-850">
              <span className="text-slate-500 block">TVET Levels</span>
              <span className="font-semibold text-white mt-1 block">Level 4, 5 & 6</span>
            </div>
            <div className="bg-slate-900/60 p-3 rounded-xl border border-slate-850">
              <span className="text-slate-500 block">Departments</span>
              <span className="font-semibold text-white mt-1 block">6 Specialties</span>
            </div>
            <div className="bg-slate-900/60 p-3 rounded-xl border border-slate-850">
              <span className="text-slate-500 block">Primary Stack</span>
              <span className="font-semibold text-teal-400 font-mono mt-1 block">LAMP (PHP 8.2+)</span>
            </div>
          </div>
        </div>
      </div>
    </motion.div>
  );
}

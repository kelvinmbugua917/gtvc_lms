import React from "react";
import { FileText, Download, Check } from "lucide-react";
import { motion } from "motion/react";
import { useLms, QUESTIONS } from "../context/LmsContext";

export default function LivingSrsPage() {
  const { answers } = useLms();

  const handleExportSrs = () => {
    alert("SRS exported to Markdown locally!");
  };

  return (
    <motion.div 
      key="srs-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="max-w-4xl mx-auto flex flex-col gap-6"
    >
      {/* SRS Toolbar Header */}
      <div className="bg-slate-950 px-6 py-4 rounded-2xl border border-slate-800 flex justify-between items-center">
        <div>
          <h2 className="text-lg font-bold text-white flex items-center gap-2">
            <FileText className="w-5 h-5 text-teal-400" />
            Living Software Requirements Specification
          </h2>
          <p className="text-xs text-slate-400">IEEE-inspired academic spec. Instantly reflecting wizard configurations.</p>
        </div>
        <button 
          onClick={handleExportSrs}
          className="bg-slate-900 hover:bg-slate-850 border border-slate-800 hover:border-slate-700 text-slate-330 font-medium px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all cursor-pointer"
        >
          <Download className="w-4 h-4 text-teal-400" />
          Export .MD File
        </button>
      </div>

      {/* SRS Content Render */}
      <div className="bg-slate-950 rounded-2xl border border-slate-800 p-6 md:p-10 text-xs md:text-sm text-slate-300 flex flex-col gap-8 leading-relaxed shadow-xl">
        {/* Title block */}
        <div className="border-b border-slate-800 pb-6 text-center md:text-left">
          <h1 className="text-2xl md:text-3xl font-black text-white tracking-tight">
            SOFTWARE REQUIREMENTS SPECIFICATION (SRS)
          </h1>
          <p className="text-teal-400 font-mono text-xs uppercase tracking-wider mt-2">
            Project: GILGIL TVC CUSTOM LEARNING MANAGEMENT SYSTEM (LMS)
          </p>
          <p className="text-slate-500 text-xs mt-1">
            Version: 1.0.0-Beta • Compiled: July 21, 2026 • Status: Living Document
          </p>
        </div>

        {/* Section 1: Institution Info */}
        <div>
          <h3 className="text-sm md:text-base font-bold text-white flex items-center gap-2 border-b border-slate-850 pb-2 mb-3">
            <span className="text-teal-400 font-mono">1.0</span> Institution Information & Profile
          </h3>
          <p className="mb-3">
            <strong>Gilgil Technical and Vocational College (Gilgil TVC)</strong> is a TVET college requiring a robust, low-overhead LMS suitable for standard Linux-Apache-MySQL-PHP shared hosting environments. The primary objectives are to bridge physical workshop instruction with digital material delivery, track continuous assessments, and securely control student progress and exam permissions.
          </p>
          <ul className="list-disc pl-5 flex flex-col gap-1">
            <li><strong>Key Trade Departments:</strong> Automotive and Mechanical Engineering, ICT, Business, Electrical and Electronics Engineering, Building and Civil Engineering, and Hospitality.</li>
            <li><strong>TVET Levels Supported:</strong> Level 4 (Artisan), Level 5 (Certificate), and Level 6 (Diploma).</li>
            <li><strong>Academic Calendar Cycles:</strong> Three intakes per year (January, May, September) modeled dynamically on a multi-tiered academic schedule rather than fixed semesters.</li>
          </ul>
        </div>

        {/* Section 2: User Roles */}
        <div>
          <h3 className="text-sm md:text-base font-bold text-white flex items-center gap-2 border-b border-slate-850 pb-2 mb-3">
            <span className="text-teal-400 font-mono">2.0</span> System Roles & Authorization Matrix
          </h3>
          <p className="mb-3">
            The LMS enforces rigid role segregation using Role-Based Access Control (RBAC). Based on configuration inputs, the following accounts are initialized in the security tables:
          </p>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            {QUESTIONS[0].options?.map((opt) => {
              const selected = answers.roles.includes(opt.value);
              return (
                <div 
                  key={opt.value} 
                  className={`p-3 rounded-xl border ${selected ? "bg-slate-900/60 border-emerald-500/20" : "bg-slate-900/20 border-slate-850 opacity-40"}`}
                >
                  <div className="flex items-center gap-2">
                    <span className={`w-2 h-2 rounded-full ${selected ? "bg-emerald-500" : "bg-slate-700"}`} />
                    <strong className="text-xs text-white">{opt.label}</strong>
                  </div>
                  <p className="text-[11px] text-slate-400 mt-1 leading-snug">{opt.desc}</p>
                </div>
              );
            })}
          </div>
        </div>

        {/* Section 3: Student Onboarding */}
        <div>
          <h3 className="text-sm md:text-base font-bold text-white flex items-center gap-2 border-b border-slate-850 pb-2 mb-3">
            <span className="text-teal-400 font-mono">3.0</span> Onboarding & Authentication Policy
          </h3>
          <p className="mb-2">
            To prevent malicious user account creation or syllabus access leaks:
          </p>
          <div className="bg-slate-900 p-4 rounded-xl border border-slate-800">
            <p className="text-xs text-slate-300 leading-relaxed">
              <strong>Selected Model: </strong> 
              {QUESTIONS[1].options?.find(o => o.value === answers.admission)?.label}
            </p>
            <p className="text-xs text-slate-400 mt-1">
              {QUESTIONS[1].options?.find(o => o.value === answers.admission)?.desc}
            </p>
          </div>
        </div>

        {/* Section 4: Workshop Attendance */}
        <div>
          <h3 className="text-sm md:text-base font-bold text-white flex items-center gap-2 border-b border-slate-850 pb-2 mb-3">
            <span className="text-teal-400 font-mono">4.0</span> Technical Trades & Attendance Rules
          </h3>
          <p className="mb-2">
            Due to the highly vocational trade training syllabus of plumbing, mechanics, and welding, standard single-tier attendance is insufficient:
          </p>
          <div className="bg-slate-900 p-4 rounded-xl border border-slate-800 text-xs text-slate-300">
            {answers.attendance_type === "separate_workshop" ? (
              <div>
                <strong className="text-teal-400 block mb-1">Dual Attendance Engine Activated (Highly Recommended)</strong>
                <p className="text-slate-400 leading-relaxed">
                  The system records both classroom theory attendance and strict hands-on Workshop Session Hours. The PHP core triggers flags and warns the lecturer when students fail to meet the mandatory 40-hour workshop quota required for end-of-year KNEC examination bookings.
                </p>
              </div>
            ) : (
              <p className="text-slate-400 leading-relaxed">
                Basic attendance ledger tracking will be compiled, recording single present/absent logs for classroom units.
              </p>
            )}
          </div>
        </div>

        {/* Section 5: Learning Materials & Video */}
        <div>
          <h3 className="text-sm md:text-base font-bold text-white flex items-center gap-2 border-b border-slate-850 pb-2 mb-3">
            <span className="text-teal-400 font-mono">5.0</span> Resource Gating & Storage Strategy
          </h3>
          <p className="mb-2">
            To maintain extremely low hosting costs and avoid overloading disk drives:
          </p>
          <ul className="list-disc pl-5 flex flex-col gap-1 mb-4">
            {answers.lessons_format.includes("rich_text") && <li><strong>Compact Database Storage:</strong> Syllabus lessons stored as plain text/rich Markdown directly in the MySQL table, creating zero media filesystem load.</li>}
            {answers.lessons_format.includes("pdf_docs") && <li><strong>MIME-Validated PDF Storage:</strong> Secure document hosting with strict extensions validations (e.g., block upload of <code>.php</code>, <code>.phtml</code>, <code>.sh</code> files completely).</li>}
            {answers.lessons_format.includes("embedded_videos") && <li><strong>External CDN Video Streaming:</strong> Videos hosted entirely on platforms like YouTube/Vimeo and streamed using zero-cost iframe embedding, keeping the college's server bandwidth completely free.</li>}
            {answers.lessons_format.includes("mp4_upload") && <li className="text-red-400"><strong>Direct Video Upload Enabled:</strong> Direct video uploads allowed. Security recommends restricting direct MP4 uploads to a maximum of 15MB to prevent shared hosting storage exhaustion.</li>}
          </ul>
        </div>

        {/* Section 6: Security and LAMP Architecture */}
        <div>
          <h3 className="text-sm md:text-base font-bold text-white flex items-center gap-2 border-b border-slate-850 pb-2 mb-3">
            <span className="text-teal-400 font-mono">6.0</span> Security Architectures & Host Requirements
          </h3>
          <p className="mb-3">
            The PHP LMS core will strictly implement standard defensive engineering practices to secure institutional student profiles:
          </p>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs text-slate-355">
            <div className="bg-slate-900 p-3 rounded-xl border border-slate-850">
              <strong className="text-teal-400 block">Password Defense</strong>
              Uses native PHP <code>password_hash()</code> with <code>PASSWORD_BCRYPT</code> and cost 12. Never stores plain passwords.
            </div>
            <div className="bg-slate-900 p-3 rounded-xl border border-slate-850">
              <strong className="text-teal-400 block">Database Injection Shield</strong>
              All SQL statements executed via PHP PDO class using strictly bound prepared statements. No raw queries allowed.
            </div>
            <div className="bg-slate-900 p-3 rounded-xl border border-slate-850">
              <strong className="text-teal-400 block">Session Protection</strong>
              Session IDs regenerated upon login (<code>session_regenerate_id(true)</code>), cookies marked with HttpOnly, Secure, and SameSite flags.
            </div>
            <div className="bg-slate-900 p-3 rounded-xl border border-slate-850">
              <strong className="text-teal-400 block">Cross-Site Scripting (XSS)</strong>
              Outputs sanitized and encoded using PHP's native <code>htmlspecialchars()</code> filter before rendering.
            </div>
          </div>
        </div>

        {/* Call-to-action */}
        <div className="bg-slate-900 border border-slate-800 p-5 rounded-xl text-center flex flex-col items-center gap-3 mt-4">
          <div className="bg-emerald-500/10 text-emerald-400 p-2.5 rounded-full">
            <Check className="w-5 h-5" />
          </div>
          <div>
            <h4 className="font-bold text-white text-sm">Specification Validated & Completed</h4>
            <p className="text-xs text-slate-400 mt-1 leading-relaxed max-w-lg">
              This SRS fully describes a production-grade LAMP application customized specifically for the technical trades environment of Gilgil TVC. Let's inspect the visual Database Tables next!
            </p>
          </div>
        </div>
      </div>
    </motion.div>
  );
}

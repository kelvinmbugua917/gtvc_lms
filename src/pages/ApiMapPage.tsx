import React, { useState } from "react";
import { ShieldCheck, Activity, Key, LogOut, UserCheck, Lock, CheckCircle2, XCircle } from "lucide-react";
import { motion } from "motion/react";
import { useLms } from "../context/LmsContext";

export default function ApiMapPage() {
  const {
    selectedApiRoute,
    setSelectedApiRoute,
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
    fetchMeUser
  } = useLms();

  const [emailInput, setEmailInput] = useState("admin@gilgiltvc.ac.ke");
  const [passwordInput, setPasswordInput] = useState("password");
  const [lastApiResponse, setLastApiResponse] = useState<any>(null);

  const handlePresetSelect = (presetEmail: string) => {
    setEmailInput(presetEmail);
    setPasswordInput("password");
  };

  const handleLoginSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const success = await loginUser(emailInput, passwordInput);
    if (success) {
      setLastApiResponse({
        status: 200,
        message: "Authentication successful",
        endpoint: "POST /api/v1/auth/login",
        timestamp: new Date().toISOString(),
        user: authUser
      });
    } else {
      setLastApiResponse({
        status: 401,
        message: "Invalid credentials or account locked",
        endpoint: "POST /api/v1/auth/login",
        timestamp: new Date().toISOString(),
        error: authError
      });
    }
  };

  const handleFetchMe = async () => {
    await fetchMeUser();
    setLastApiResponse({
      status: authUser ? 200 : 401,
      message: authUser ? "Authenticated profile retrieved" : "Unauthenticated session",
      endpoint: "GET /api/v1/auth/me",
      timestamp: new Date().toISOString(),
      user: authUser
    });
  };

  const handleLogoutSubmit = async () => {
    await logoutUser();
    setLastApiResponse({
      status: 200,
      message: "Successfully logged out",
      endpoint: "POST /api/v1/auth/logout",
      timestamp: new Date().toISOString(),
      user: null
    });
  };

  return (
    <motion.div 
      key="api-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="grid grid-cols-1 lg:grid-cols-12 gap-6"
    >
      <div className="lg:col-span-12 bg-slate-950 px-6 py-4 rounded-2xl border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h2 className="text-lg font-bold text-white flex items-center gap-2 font-sans">
            <ShieldCheck className="w-5 h-5 text-teal-400" />
            Secure REST Backend API Routes & Authentication Engine
          </h2>
          <p className="text-xs text-slate-400">Strictly gated routing endpoints for modern SPA and client integrations. Test real authentication & RBAC session queries below.</p>
        </div>

        {/* Live vs Demo Mode Configuration Switcher */}
        <div className="flex items-center gap-2 bg-slate-900 p-1.5 rounded-xl border border-slate-800">
          <span className="text-[11px] font-mono text-slate-400 px-2">Environment Mode:</span>
          <button
            onClick={() => setIsDemoModeConfigured(false)}
            className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer font-mono ${
              !isDemoModeConfigured
                ? "bg-teal-500 text-slate-950 shadow-md shadow-teal-500/20"
                : "text-slate-400 hover:text-white"
            }`}
          >
            🟢 Live Backend Mode
          </button>
          <button
            onClick={() => setIsDemoModeConfigured(true)}
            className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer font-mono ${
              isDemoModeConfigured
                ? "bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20"
                : "text-slate-400 hover:text-white"
            }`}
          >
            🟡 Demo / Simulation Mode
          </button>
        </div>
      </div>

      {/* Session Expired Banner Notice */}
      {sessionExpiredNotice && (
        <div className="lg:col-span-12 bg-red-500/10 border border-red-500/30 p-4 rounded-2xl flex items-center justify-between text-xs text-red-300">
          <div className="flex items-center gap-2">
            <XCircle className="w-5 h-5 text-red-400 shrink-0" />
            <span className="font-medium">{sessionExpiredNotice}</span>
          </div>
          <button
            onClick={clearSessionExpiredNotice}
            className="bg-red-500/20 hover:bg-red-500/30 text-red-200 px-3 py-1 rounded-lg font-mono text-[11px] transition-all cursor-pointer"
          >
            Dismiss
          </button>
        </div>
      )}

      {/* Interactive Live Auth Testing Console */}
      <div className="lg:col-span-12 bg-slate-950 rounded-2xl border border-teal-500/30 p-5 shadow-2xl flex flex-col gap-5">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-4">
          <div>
            <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 font-sans">
              <Key className="w-4 h-4 text-teal-400" />
              Live Authentication & RBAC Session Tester
            </h3>
            <p className="text-xs text-slate-400 mt-0.5">Test real backend login workflows, tokenless HttpOnly session cookies, and RBAC permission checks.</p>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <span className="text-[11px] text-slate-400 font-mono">Preset Accounts:</span>
            <button
              onClick={() => handlePresetSelect("admin@gilgiltvc.ac.ke")}
              className="text-[11px] bg-slate-900 hover:bg-slate-800 border border-slate-700 text-teal-300 px-2.5 py-1 rounded font-mono transition-all cursor-pointer"
            >
              Super Admin
            </button>
            <button
              onClick={() => handlePresetSelect("pkiprop@gilgiltvc.ac.ke")}
              className="text-[11px] bg-slate-900 hover:bg-slate-800 border border-slate-700 text-teal-300 px-2.5 py-1 rounded font-mono transition-all cursor-pointer"
            >
              Lecturer
            </button>
            <button
              onClick={() => handlePresetSelect("kmbugua@student.gilgiltvc.ac.ke")}
              className="text-[11px] bg-slate-900 hover:bg-slate-800 border border-slate-700 text-teal-300 px-2.5 py-1 rounded font-mono transition-all cursor-pointer"
            >
              Student
            </button>
            <button
              onClick={() => handlePresetSelect("mwanjiru@gilgiltvc.ac.ke")}
              className="text-[11px] bg-slate-900 hover:bg-slate-800 border border-slate-700 text-teal-300 px-2.5 py-1 rounded font-mono transition-all cursor-pointer"
            >
              Accountant
            </button>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
          {/* Form Controls */}
          <div className="md:col-span-5 flex flex-col gap-4">
            <form onSubmit={handleLoginSubmit} className="flex flex-col gap-3">
              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Account Email / Reg No</label>
                <input
                  type="email"
                  value={emailInput}
                  onChange={(e) => setEmailInput(e.target.value)}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-teal-500"
                  placeholder="user@gilgiltvc.ac.ke"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                <input
                  type="password"
                  value={passwordInput}
                  onChange={(e) => setPasswordInput(e.target.value)}
                  className="w-full bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white font-mono focus:outline-none focus:border-teal-500"
                  placeholder="••••••••"
                  required
                />
              </div>

              {authError && (
                <div className="text-[11px] text-red-400 bg-red-500/10 border border-red-500/20 p-2.5 rounded-lg flex items-center gap-2">
                  <XCircle className="w-4 h-4 shrink-0" />
                  <span>{authError}</span>
                </div>
              )}

              <div className="flex flex-wrap items-center gap-2 mt-1">
                <button
                  type="submit"
                  disabled={authLoading}
                  className="bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                >
                  <Lock className="w-3.5 h-3.5" />
                  <span>Login (POST /auth/login)</span>
                </button>

                <button
                  type="button"
                  onClick={handleFetchMe}
                  className="bg-slate-850 hover:bg-slate-800 border border-slate-700 text-slate-200 font-semibold px-3 py-2 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                >
                  <UserCheck className="w-3.5 h-3.5 text-emerald-400" />
                  <span>GET /auth/me</span>
                </button>

                <button
                  type="button"
                  onClick={handleLogoutSubmit}
                  className="bg-slate-850 hover:bg-slate-800 border border-slate-700 text-red-400 font-semibold px-3 py-2 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                >
                  <LogOut className="w-3.5 h-3.5" />
                  <span>Logout</span>
                </button>
              </div>
            </form>
          </div>

          {/* Current Auth Session Display */}
          <div className="md:col-span-7 bg-slate-900 rounded-xl border border-slate-800 p-4 flex flex-col gap-3">
            <div className="flex items-center justify-between border-b border-slate-800 pb-2">
              <span className="text-xs font-bold text-white uppercase tracking-wider font-sans">
                Active Session State
              </span>
              <div className="flex items-center gap-2">
                <span className={`text-[10px] font-mono px-2 py-0.5 rounded font-bold ${
                  isDemoSession ? "bg-amber-500/10 text-amber-400 border border-amber-500/20" : "bg-cyan-500/10 text-cyan-400 border border-cyan-500/20"
                }`}>
                  {isDemoSession ? "DEMO / SIMULATION MODE" : "LIVE PRODUCTION BACKEND"}
                </span>
                <span className={`text-[10px] font-mono px-2 py-0.5 rounded font-bold ${
                  authUser ? "bg-emerald-500/10 text-emerald-400 border border-emerald-500/20" : "bg-red-500/10 text-red-400 border border-red-500/20"
                }`}>
                  {authUser ? "AUTHENTICATED" : "UNAUTHENTICATED"}
                </span>
              </div>
            </div>

            {authUser ? (
              <div className="flex flex-col gap-2.5 text-xs">
                <div className="flex items-center justify-between">
                  <span className="text-slate-400">User Identity:</span>
                  <span className="text-white font-bold">{authUser.first_name} {authUser.last_name} ({authUser.registration_number})</span>
                </div>
                <div className="flex items-center justify-between">
                  <span className="text-slate-400">Email Address:</span>
                  <span className="text-teal-300 font-mono">{authUser.email}</span>
                </div>
                <div>
                  <span className="text-slate-400 block mb-1">Assigned Roles:</span>
                  <div className="flex flex-wrap gap-1">
                    {authUser.roles.map((r, i) => (
                      <span key={i} className="bg-teal-500/10 text-teal-300 border border-teal-500/20 font-mono text-[10px] px-2 py-0.5 rounded">
                        {r.name}
                      </span>
                    ))}
                  </div>
                </div>
                <div>
                  <span className="text-slate-400 block mb-1">Granted Permissions (RBAC):</span>
                  <div className="flex flex-wrap gap-1 max-h-20 overflow-y-auto pr-1">
                    {authUser.permissions.map((p, i) => (
                      <span key={i} className="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono text-[9px] px-1.5 py-0.5 rounded flex items-center gap-1">
                        <CheckCircle2 className="w-2.5 h-2.5" />
                        {p}
                      </span>
                    ))}
                  </div>
                </div>
              </div>
            ) : (
              <div className="text-xs text-slate-500 p-4 text-center">
                No active authenticated session. Select a preset user account above and click "Login" to simulate a live secure backend authentication exchange.
              </div>
            )}
          </div>
        </div>

        {/* Live Response Payload Inspector */}
        {lastApiResponse && (
          <div className="border-t border-slate-800 pt-3">
            <div className="flex items-center justify-between mb-2">
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">
                Latest Response Payload ({lastApiResponse.endpoint}):
              </span>
              <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded ${
                lastApiResponse.status === 200 ? "bg-emerald-500/10 text-emerald-400" : "bg-red-500/10 text-red-400"
              }`}>
                HTTP {lastApiResponse.status}
              </span>
            </div>
            <pre className="bg-slate-900 p-3 rounded-lg border border-slate-800 font-mono text-[10px] text-teal-300 overflow-x-auto max-h-40">
              {JSON.stringify(lastApiResponse, null, 2)}
            </pre>
          </div>
        )}
      </div>

      {/* Endpoint Catalog Table (7 Columns) */}
      <div className="lg:col-span-7 bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl flex flex-col gap-4">
        <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider font-sans">Complete API Route Inventory</h3>
        <div className="overflow-x-auto">
          <table className="w-full text-left font-mono text-xs">
            <thead>
              <tr className="border-b border-slate-800 text-slate-400">
                <th className="pb-3 w-2/12">Method</th>
                <th className="pb-3 w-5/12 text-slate-200">Endpoint Routing</th>
                <th className="pb-3 w-3/12">Status</th>
                <th className="pb-3 w-2/12">Role</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-850">
              {[
                { method: "GET", route: "/api/v1/auth/csrf", role: "Public", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/auth/login", role: "Public", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/auth/logout", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/auth/me", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/health", role: "Public", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/academic-years", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/intakes", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/departments", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/programs", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/classes", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/units", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/course-offerings", role: "Authenticated", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/students", role: "Registrar / HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/students", role: "Registrar / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/students/{id}", role: "Self / Staff", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/enrollments", role: "Self / Staff", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/enrollments", role: "Registrar / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "PUT", route: "/api/v1/enrollments/{id}", role: "Registrar / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/course-offerings/{id}/modules", role: "Enrolled Student / Lecturer", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/course-offerings/{id}/modules", role: "Lecturer / HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/modules/{id}/lessons", role: "Enrolled Student / Lecturer", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/modules/{id}/lessons", role: "Lecturer / HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/lessons/{id}/materials", role: "Enrolled Student / Lecturer", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/lessons/{id}/materials", role: "Lecturer / HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/materials/{id}/download", role: "Enrolled Student / Lecturer", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/lessons/{id}/progress", role: "Self Student", status: "REAL (IMPLEMENTED)", live: true },
                { method: "PUT", route: "/api/v1/lessons/{id}/progress", role: "Self Student", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/course-offerings/{id}/progress-overview", role: "Lecturer / HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/assignments/{id}/submissions", role: "Enrolled Student", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/course-offerings/{id}/quizzes", role: "Enrolled Student / Lecturer", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/attendance/sessions", role: "Lecturer / HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/attendance/sessions", role: "Lecturer / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/attendance/sessions/{id}/records", role: "Lecturer / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/attendance/me", role: "Student", status: "REAL (IMPLEMENTED)", live: true },
                { method: "GET", route: "/api/v1/attendance/department/report", role: "HOD / Admin", status: "REAL (IMPLEMENTED)", live: true },
                { method: "POST", route: "/api/v1/billing/update-ledger", role: "Accountant", status: "PLANNED (PHASE 6E)", live: false }
              ].map((endpoint, i) => (
                <tr 
                  key={i} 
                  onClick={() => setSelectedApiRoute(endpoint.route)}
                  className={`hover:bg-slate-900/50 cursor-pointer transition-all ${
                    selectedApiRoute === endpoint.route ? "bg-slate-905 border-l-2 border-teal-500" : ""
                  }`}
                >
                  <td className="py-4 pl-2 font-black">
                    <span className={`px-2 py-0.5 rounded text-[10px] ${
                      endpoint.method === "GET" ? "bg-emerald-500/10 text-emerald-400" :
                      endpoint.method === "POST" ? "bg-teal-500/10 text-teal-400" : "bg-amber-500/10 text-amber-400"
                    }`}>
                      {endpoint.method}
                    </span>
                  </td>
                  <td className="py-4 text-white font-semibold">{endpoint.route}</td>
                  <td className="py-4">
                    <span className={`px-2 py-0.5 rounded text-[9px] font-bold ${
                      endpoint.live 
                        ? "bg-teal-500/15 text-teal-300 border border-teal-500/20" 
                        : "bg-slate-800 text-slate-400 border border-slate-700"
                    }`}>
                      {endpoint.status}
                    </span>
                  </td>
                  <td className="py-4 text-slate-350">{endpoint.role}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Endpoint Inspector Sidebar (5 Columns) */}
      <div className="lg:col-span-5 bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl flex flex-col gap-4">
        <div className="border-b border-slate-855 pb-3">
          <h3 className="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 font-sans">
            <Activity className="w-4 h-4 text-teal-400" />
            REST Route Inspector
          </h3>
          <p className="text-xs text-slate-400 mt-1">Detailed secure design details for developers and security auditors.</p>
        </div>

        {/* API Info Cards depending on selected Route */}
        {selectedApiRoute === "/api/v1/auth/login" && (
          <div className="flex flex-col gap-4 text-left">
            <div className="flex items-center gap-2">
              <span className="bg-teal-500/10 text-teal-400 font-bold font-mono px-2 py-0.5 rounded text-xs">POST</span>
              <span className="text-white font-mono text-xs font-semibold">/api/v1/auth/login</span>
            </div>
            <div className="text-xs text-slate-300 leading-relaxed bg-slate-900 p-3 rounded-lg border border-slate-850">
              Handles user login. To mitigate brute-force attempts, the backend implements basic lockouts on repeated fails and enforces password_verify() hashing.
            </div>
            <div>
              <h4 className="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 font-sans">Request Body Payload:</h4>
              <pre className="bg-slate-900 p-3 rounded-lg border border-slate-850 font-mono text-[10px] text-teal-300 leading-normal">
{`{
  "email": "string (email)",
  "password": "string (Raw password)"
}`}
              </pre>
            </div>
            <div>
              <h4 className="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 font-sans">Defensive SQL Logic (Prepared PDO):</h4>
              <pre className="bg-slate-900 p-3 rounded-lg border border-slate-850 font-mono text-[10px] text-slate-300 leading-normal overflow-x-auto">
{`SELECT * FROM users WHERE email = :email LIMIT 1;`}
              </pre>
            </div>
          </div>
        )}

        {selectedApiRoute === "/api/v1/auth/logout" && (
          <div className="flex flex-col gap-4 text-left">
            <div className="flex items-center gap-2">
              <span className="bg-teal-500/10 text-teal-400 font-bold font-mono px-2 py-0.5 rounded text-xs">POST</span>
              <span className="text-white font-mono text-xs font-semibold">/api/v1/auth/logout</span>
            </div>
            <div className="text-xs text-slate-300 leading-relaxed bg-slate-900 p-3 rounded-lg border border-slate-850">
              Terminates session instantly, invalidates active cookie storage, and records logout timestamp in audit_logs table.
            </div>
          </div>
        )}

        {selectedApiRoute === "/api/v1/auth/me" && (
          <div className="flex flex-col gap-4 text-left">
            <div className="flex items-center gap-2">
              <span className="bg-emerald-500/10 text-emerald-400 font-bold font-mono px-2 py-0.5 rounded text-xs">GET</span>
              <span className="text-white font-mono text-xs font-semibold">/api/v1/auth/me</span>
            </div>
            <div className="text-xs text-slate-300 leading-relaxed bg-slate-900 p-3 rounded-lg border border-slate-850">
              Retrieves profile details, role assignments, department mappings, and granular permission array of current authenticated user.
            </div>
          </div>
        )}

        {!["/api/v1/auth/login", "/api/v1/auth/logout", "/api/v1/auth/me"].includes(selectedApiRoute) && (
          <div className="p-8 text-center text-slate-500 text-xs">
            Select an API route above to inspect detailed schemas, headers, payloads, and defensive backend logic.
          </div>
        )}
      </div>
    </motion.div>
  );
}

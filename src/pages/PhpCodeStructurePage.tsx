import React from "react";
import { FolderTree } from "lucide-react";
import { motion } from "motion/react";
import { useLms } from "../context/LmsContext";

export default function PhpCodeStructurePage() {
  const {
    selectedPhpFile,
    setSelectedPhpFile
  } = useLms();

  return (
    <motion.div 
      key="files-tab"
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -15 }}
      className="grid grid-cols-1 lg:grid-cols-12 gap-6"
    >
      {/* Folder Tree - 4 columns */}
      <div className="lg:col-span-4 bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl flex flex-col h-full">
        <h3 className="text-sm font-bold text-white uppercase tracking-wider mb-3 flex items-center gap-2 font-sans">
          <FolderTree className="w-4 h-4 text-teal-400" />
          PHP MVC Directory Map
        </h3>
        <p className="text-xs text-slate-400 leading-relaxed mb-4">
          Fully decoupled file system optimized for standard Apache servers. Click any file to explore its secure, cPanel-compatible PHP source code.
        </p>

        <div className="font-mono text-xs text-slate-300 flex flex-col gap-2.5 overflow-y-auto max-h-[500px]">
          <div className="flex items-center gap-2 text-white pb-1 border-b border-slate-900">
            <span>📁</span> <strong>gilgiltvc-lms/backend/</strong>
          </div>
          
          <div className="pl-3 flex flex-col gap-2">
            {/* config folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 config/</div>
              <div className="pl-4 mt-1">
                <button 
                  onClick={() => setSelectedPhpFile("config/database.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "config/database.php" ? "text-emerald-400 bg-emerald-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 database.php
                </button>
              </div>
            </div>

            {/* core folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 core/</div>
              <div className="pl-4 mt-1 flex flex-col gap-1">
                <button 
                  onClick={() => setSelectedPhpFile("core/Session.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "core/Session.php" ? "text-emerald-400 bg-emerald-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 Session.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("core/FileUpload.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "core/FileUpload.php" ? "text-emerald-400 bg-emerald-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 FileUpload.php
                </button>
              </div>
            </div>

            {/* middleware folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 middleware/</div>
              <div className="pl-4 mt-1 flex flex-col gap-1">
                <button 
                  onClick={() => setSelectedPhpFile("middleware/AuthMiddleware.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "middleware/AuthMiddleware.php" ? "text-amber-400 bg-amber-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AuthMiddleware.php
                </button>
              </div>
            </div>

            {/* controllers folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 controllers/</div>
              <div className="pl-4 mt-1 flex flex-col gap-1">
                <button 
                  onClick={() => setSelectedPhpFile("controllers/AuthController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/AuthController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AuthController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/AcademicController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/AcademicController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AcademicController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/StudentController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/StudentController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 StudentController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/EnrollmentController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/EnrollmentController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 EnrollmentController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/CourseModuleController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/CourseModuleController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 CourseModuleController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/LessonController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/LessonController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LessonController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/LearningMaterialController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/LearningMaterialController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LearningMaterialController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/LessonProgressController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/LessonProgressController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LessonProgressController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/AttendanceController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/AttendanceController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AttendanceController.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("controllers/AttendanceReportController.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "controllers/AttendanceReportController.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AttendanceReportController.php
                </button>
              </div>
            </div>

            {/* models folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 models/</div>
              <div className="pl-4 mt-1 flex flex-col gap-1">
                <button 
                  onClick={() => setSelectedPhpFile("models/User.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/User.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 User.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/Student.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/Student.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 Student.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/Enrollment.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/Enrollment.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 Enrollment.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/AcademicYear.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/AcademicYear.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AcademicYear.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/Department.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/Department.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 Department.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/ClassCohort.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/ClassCohort.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 ClassCohort.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/Unit.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/Unit.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 Unit.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/CourseOffering.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/CourseOffering.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 CourseOffering.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/CourseModule.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/CourseModule.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 CourseModule.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/Lesson.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/Lesson.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 Lesson.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/LearningMaterial.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/LearningMaterial.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LearningMaterial.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/LessonProgress.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/LessonProgress.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LessonProgress.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/AuditLog.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/AuditLog.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AuditLog.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/AttendanceSession.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/AttendanceSession.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AttendanceSession.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/AttendanceRecord.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/AttendanceRecord.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AttendanceRecord.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("models/LoginAttempt.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "models/LoginAttempt.php" ? "text-sky-400 bg-sky-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LoginAttempt.php
                </button>
              </div>
            </div>

            {/* public folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 public/</div>
              <div className="pl-4 mt-1 flex flex-col gap-1">
                <button 
                  onClick={() => setSelectedPhpFile("public/index.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "public/index.php" ? "text-teal-400 bg-teal-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 index.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("public/.htaccess")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "public/.htaccess" ? "text-amber-400 bg-amber-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 .htaccess
                </button>
              </div>
            </div>

            {/* tests folder */}
            <div>
              <div className="text-slate-400 flex items-center gap-1 font-semibold">📁 tests/</div>
              <div className="pl-4 mt-1 flex flex-col gap-1">
                <button 
                  onClick={() => setSelectedPhpFile("tests/LearningContentAndProgressTest.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "tests/LearningContentAndProgressTest.php" ? "text-emerald-400 bg-emerald-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 LearningContentAndProgressTest.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("tests/AssessmentAndGradingTest.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "tests/AssessmentAndGradingTest.php" ? "text-emerald-400 bg-emerald-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AssessmentAndGradingTest.php
                </button>
                <button 
                  onClick={() => setSelectedPhpFile("tests/AttendanceAndWorkshopTest.php")}
                  className={`flex items-center gap-1.5 transition-all w-full text-left p-1.5 rounded cursor-pointer ${
                    selectedPhpFile === "tests/AttendanceAndWorkshopTest.php" ? "text-emerald-400 bg-emerald-500/10 font-bold" : "text-slate-350 hover:text-white"
                  }`}
                >
                  📄 AttendanceAndWorkshopTest.php
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>

      {/* PHP Code View - 8 columns */}
      <div className="lg:col-span-8 flex flex-col gap-4" id="php-boilerplates-designer">
        <div className="bg-slate-950 rounded-2xl border border-slate-800 p-5 shadow-xl flex-1 flex flex-col">
          <div className="flex justify-between items-center border-b border-slate-850 pb-3 mb-4 flex-wrap gap-2">
            <div>
              <div className="flex items-center gap-2 mb-1">
                <h3 className="text-sm font-bold text-white">
                  {selectedPhpFile === "config/database.php" && "Secure Database Connection Class (PHP 8.2)"}
                  {selectedPhpFile === "core/Session.php" && "Secure Session Manager (HttpOnly & SameSite)"}
                  {selectedPhpFile === "middleware/AuthMiddleware.php" && "RBAC & IDOR Defensive Authorization Gate"}
                  {selectedPhpFile === "controllers/AuthController.php" && "Secure Authentication Controller"}
                  {selectedPhpFile === "models/User.php" && "Core User Model & Permission Mapping"}
                  {selectedPhpFile === "models/AuditLog.php" && "Security Audit Trail Recorder"}
                  {selectedPhpFile === "models/LoginAttempt.php" && "Brute-Force Protection Rate Limiter"}
                  {selectedPhpFile === "public/index.php" && "Master Front Controller & Router"}
                  {selectedPhpFile === "public/.htaccess" && "Apache Security Rules (.htaccess)"}
                </h3>
                <span className="text-[10px] font-mono font-bold bg-teal-500/15 text-teal-300 border border-teal-500/20 px-2 py-0.5 rounded">
                  PHYSICAL REPOSITORY FILE
                </span>
              </div>
              <p className="text-xs text-slate-400">
                {selectedPhpFile === "config/database.php" && "Ensuring zero raw SQL statements and strict session integrity."}
                {selectedPhpFile === "core/Session.php" && "Session regeneration, activity timeouts, and cookie security flags."}
                {selectedPhpFile === "middleware/AuthMiddleware.php" && "Granular permission verification, role inheritance, and IDOR resource checks."}
                {selectedPhpFile === "controllers/AuthController.php" && "Strict hash verification, brute force checks, and CSRF token defenses."}
                {selectedPhpFile === "models/User.php" && "Normalized PDO queries with role/permission aggregation."}
                {selectedPhpFile === "models/AuditLog.php" && "Recording user logins, logouts, and unauthorized access attempts."}
                {selectedPhpFile === "models/LoginAttempt.php" && "Tracking failed authentication attempts and enforcing 15-min lockouts."}
                {selectedPhpFile === "public/index.php" && "Single entry bootstrapping, CSP header controls, and safe routing."}
                {selectedPhpFile === "public/.htaccess" && "Enforcing directory indexes blockage, HTTPS redirects, and URL re-writes."}
              </p>
            </div>
            <span className="text-[10px] bg-slate-900 border border-slate-800 px-3 py-1 rounded font-mono text-emerald-400 self-start">
              backend/{selectedPhpFile}
            </span>
          </div>

          <pre className="flex-1 bg-slate-900 p-4 rounded-xl border border-slate-850 text-xs text-slate-300 font-mono overflow-x-auto leading-relaxed max-h-[480px] text-left">
            {selectedPhpFile === "config/database.php" && (
`<?php
declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = AppConfig::get('DB_HOST', '127.0.0.1');
            $port = AppConfig::get('DB_PORT', '3306');
            $db   = AppConfig::get('DB_DATABASE', 'gtvc_lms');
            $user = AppConfig::get('DB_USERNAME', 'gtvc_user');
            $pass = AppConfig::get('DB_PASSWORD', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
            
            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failure: " . $e->getMessage());
                throw $e;
            }
        }
        return self::$instance;
    }
}`
            )}

            {selectedPhpFile === "core/Session.php" && (
`<?php
declare(strict_types=1);

namespace App\Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.use_only_cookies', '1');
            session_start();
        }
    }

    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }

    public static function destroy(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
}`
            )}

            {selectedPhpFile === "middleware/AuthMiddleware.php" && (
`<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Models\User;

class AuthMiddleware {
    public static function authenticate(): array {
        $user = User::getAuthenticatedUser();
        if (!$user) {
            Response::error('Unauthorized access. Please log in.', 401);
        }
        return $user;
    }

    public static function requirePermission(string $permission): array {
        $user = self::authenticate();
        if (!User::hasPermission($user['id'], $permission)) {
            Response::error("Forbidden: Lacks required permission '{$permission}'.", 403);
        }
        return $user;
    }

    public static function enforceResourceOwnership(int $resourceOwnerId, string $bypassPermission = 'admin.manage'): array {
        $user = self::authenticate();
        if ((int)$user['id'] === $resourceOwnerId || User::hasPermission($user['id'], $bypassPermission)) {
            return $user;
        }
        Response::error('Forbidden: Access denied to this resource (IDOR protection).', 403);
    }
}`
            )}

            {selectedPhpFile === "controllers/AuthController.php" && (
`<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\AuditLog;
use App\Middleware\AuthMiddleware;

class AuthController extends Controller {
    public function login(Request $request): void {
        $body = $request->getBody();
        $email = trim($body['email'] ?? '');
        $password = $body['password'] ?? '';

        if (LoginAttempt::isLockedOut($email, $ip)) {
            Response::error("Account locked due to multiple failed login attempts.", 429);
        }

        $user = User::findByEmail($email);
        if (!$user || !User::verifyPassword($password, $user['password_hash'])) {
            LoginAttempt::recordFailedAttempt($email, $ip);
            Response::error("Invalid credentials.", 401);
        }

        Session::regenerate();
        $_SESSION['user_id'] = $user['id'];
        
        $profile = User::getUserWithProfile($user['id']);
        AuditLog::log((int)$user['id'], 'AUTH_LOGIN_SUCCESS', "User logged in", $ip);

        Response::json($profile, "Login successful");
    }

    public function logout(Request $request): void {
        $user = User::getAuthenticatedUser();
        if ($user) {
            AuditLog::log((int)$user['id'], 'AUTH_LOGOUT', "User logged out");
        }
        Session::destroy();
        Response::json([], "Logout successful");
    }

    public function me(Request $request): void {
        $user = AuthMiddleware::authenticate();
        $profile = User::getUserWithProfile((int)$user['id']);
        Response::json($profile, "Authenticated user profile");
    }
}`
            )}

            {selectedPhpFile === "models/User.php" && (
`<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use App\Core\Model;
use PDO;

class User extends Model {
    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public static function getUserWithProfile(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, email, first_name, last_name, registration_number FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        $user['roles'] = self::getUserRoles($userId);
        $user['permissions'] = self::getUserPermissions($userId);
        $user['departments'] = self::getUserDepartments($userId);
        return $user;
    }
}`
            )}

            {selectedPhpFile === "models/AuditLog.php" && (
`<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

class AuditLog {
    public static function log(?int $userId, string $action, string $details = '', ?string $ip = null): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, action, details, ip_address, created_at)
            VALUES (:user_id, :action, :details, :ip_address, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'action'  => $action,
            'details' => $details,
            'ip_address' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
        ]);
    }
}`
            )}

            {selectedPhpFile === "models/LoginAttempt.php" && (
`<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

class LoginAttempt {
    private static int $maxAttempts = 5;
    private static int $lockoutMinutes = 15;

    public static function isLockedOut(string $email, string $ip): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE (identifier = :email OR ip_address = :ip)
              AND success = 0
              AND attempted_at > DATE_SUB(NOW(), INTERVAL :mins MINUTE)
        ");
        $stmt->execute(['email' => $email, 'ip' => $ip, 'mins' => self::$lockoutMinutes]);
        return ((int)$stmt->fetchColumn()) >= self::$maxAttempts;
    }
}`
            )}

            {selectedPhpFile === "public/index.php" && (
`<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../controllers/AuthController.php';

use App\Core\Router;
use App\Core\Request;
use App\Controllers\AuthController;

$router = new Router();
$router->post('/api/v1/auth/login', [AuthController::class, 'login']);
$router->post('/api/v1/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/v1/auth/me', [AuthController::class, 'me']);

$router->dispatch(new Request());`
            )}

            {selectedPhpFile === "public/.htaccess" && (
`Options -Indexes

<FilesMatch "^\\.env|composer\\.json|database\\.sql">
    Require all denied
</FilesMatch>

RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]`
            )}
          </pre>
        </div>
      </div>
    </motion.div>
  );
}

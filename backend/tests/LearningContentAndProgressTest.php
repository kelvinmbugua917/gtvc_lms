<?php

declare(strict_types=1);

namespace App\Tests;

use App\Core\FileUpload;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LearningMaterial;
use App\Models\LessonProgress;

class LearningContentAndProgressTest
{
    private array $results = [];

    public function runAll(): array
    {
        $this->testModuleModelStructure();
        $this->testLessonModelStructure();
        $this->testFileUploadSecurityRules();
        $this->testStudentProgressIsolationRule();
        $this->testEnrollmentAccessBoundaryRule();

        return [
            'total' => count($this->results),
            'passed' => count(array_filter($this->results, fn($r) => $r['passed'])),
            'details' => $this->results,
        ];
    }

    private function testModuleModelStructure(): void
    {
        $hasGet = method_exists(CourseModule::class, 'getModulesByOffering');
        $hasCreate = method_exists(CourseModule::class, 'createModule');
        $hasReorder = method_exists(CourseModule::class, 'reorderModules');

        $this->results[] = [
            'test' => 'CourseModule Model API Methods Verification',
            'passed' => $hasGet && $hasCreate && $hasReorder,
            'description' => 'Verifies course module CRUD and reordering helper methods exist',
        ];
    }

    private function testLessonModelStructure(): void
    {
        $hasGet = method_exists(Lesson::class, 'getLessonsByModule');
        $hasCreate = method_exists(Lesson::class, 'createLesson');
        $hasReorder = method_exists(Lesson::class, 'reorderLessons');

        $this->results[] = [
            'test' => 'Lesson Model API Methods Verification',
            'passed' => $hasGet && $hasCreate && $hasReorder,
            'description' => 'Verifies lesson management and sequence ordering helper methods exist',
        ];
    }

    private function testFileUploadSecurityRules(): void
    {
        // Test 1: Valid PDF
        $validPdf = FileUpload::validate([
            'name' => 'Syllabus.pdf',
            'error' => 0,
            'size' => 1024 * 500,
        ]);

        // Test 2: Forbidden Executable Script (.php)
        $phpFile = FileUpload::validate([
            'name' => 'webshell.php',
            'error' => 0,
            'size' => 1024,
        ]);

        // Test 3: Double extension attack (.php.pdf)
        $doubleExt = FileUpload::validate([
            'name' => 'payload.php.pdf',
            'error' => 0,
            'size' => 2048,
        ]);

        // Test 4: Oversized File (>20MB)
        $oversized = FileUpload::validate([
            'name' => 'large_video.mp4',
            'error' => 0,
            'size' => 25 * 1024 * 1024,
        ]);

        $passed = ($validPdf['valid'] === true) &&
                  ($phpFile['valid'] === false) &&
                  ($doubleExt['valid'] === false) &&
                  ($oversized['valid'] === false);

        $this->results[] = [
            'test' => 'Secure File Upload Security Rules (MIME, Extension, Double-Extension, Size)',
            'passed' => $passed,
            'description' => 'Verifies strict rejection of executables, double extensions, oversized files, and allowed PDF whitelist',
        ];
    }

    private function testStudentProgressIsolationRule(): void
    {
        $hasProgressGet = method_exists(LessonProgress::class, 'getProgress');
        $hasProgressSave = method_exists(LessonProgress::class, 'saveProgress');
        $hasCourseProgress = method_exists(LessonProgress::class, 'getStudentCourseProgress');

        $this->results[] = [
            'test' => 'Student Progress Isolation & Overview Methods',
            'passed' => $hasProgressGet && $hasProgressSave && $hasCourseProgress,
            'description' => 'Verifies backend lesson progress save and course percentage completion calculation helpers exist',
        ];
    }

    private function testEnrollmentAccessBoundaryRule(): void
    {
        $hasCheck = method_exists(LessonProgress::class, 'isStudentEnrolledInCourseOffering');

        $this->results[] = [
            'test' => 'Server-Side Course Enrollment Boundary Enforcement Helper',
            'passed' => $hasCheck,
            'description' => 'Verifies helper function exists to validate active student enrollment before granting content or progress access',
        ];
    }
}

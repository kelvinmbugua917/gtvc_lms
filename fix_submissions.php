<?php
$content = file_get_contents('/app/applet/gtvc-lms-production/app/Models/AssignmentSubmission.php');
$content = preg_replace('/^\s*\/\*\*.*?ensureSubmissionColumns.*?^    }/ms', '', $content);
$content = preg_replace('/self::ensureSubmissionColumns\(\);/m', '', $content);
// Also remove ensureAssignmentColumns from Assignment.php
$assignContent = file_get_contents('/app/applet/gtvc-lms-production/app/Models/Assignment.php');
$assignContent = preg_replace('/^\s*\/\*\*.*?ensureAssignmentColumns.*?^    }/ms', '', $assignContent);
$assignContent = preg_replace('/self::ensureAssignmentColumns\(\);/m', '', $assignContent);
file_put_contents('/app/applet/gtvc-lms-production/app/Models/AssignmentSubmission.php', $content);
file_put_contents('/app/applet/gtvc-lms-production/app/Models/Assignment.php', $assignContent);

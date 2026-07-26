const fs = require('fs');

function fixFile(file, regex1, regex2) {
    let content = fs.readFileSync(file, 'utf8');
    
    // Attempt 1: remove function block using simpler regex
    content = content.replace(/\s*\/\*\*\s*\*\s*Ensure\b[\s\S]*?public static function\s+ensure[\s\S]*?\}\s*\}\s*catch\s*\([^\)]+\)\s*\{\s*\/\/[^\n]*\s*\}\s*\}/g, '');

    content = content.replace(regex2, '');
    
    fs.writeFileSync(file, content);
}

fixFile('/app/applet/gtvc-lms-production/app/Models/AssignmentSubmission.php', null, /self::ensureSubmissionColumns\(\);/g);
fixFile('/app/applet/gtvc-lms-production/app/Models/Assignment.php', null, /self::ensureAssignmentColumns\(\);/g);

console.log("Done");

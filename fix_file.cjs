const fs = require('fs');

let content = fs.readFileSync('/app/applet/gtvc-lms-production/app/Models/AssignmentSubmission.php', 'utf8');

// Find the start of class and the first method
const match = content.match(/class\s+AssignmentSubmission\s+extends\s+Model\s*\{/);
if (match) {
    const classStart = match.index + match[0].length;
    const methodMatch = content.match(/\/\*\*\s*\*\s*Get submission by ID/);
    if (methodMatch) {
        const replaceEnd = methodMatch.index;
        content = content.substring(0, classStart) + "\n" + content.substring(replaceEnd);
    }
}
fs.writeFileSync('/app/applet/gtvc-lms-production/app/Models/AssignmentSubmission.php', content);

// Now for Assignment.php
let content2 = fs.readFileSync('/app/applet/gtvc-lms-production/app/Models/Assignment.php', 'utf8');
const match2 = content2.match(/class\s+Assignment\s+extends\s+Model\s*\{/);
if (match2) {
    const classStart2 = match2.index + match2[0].length;
    const methodMatch2 = content2.match(/\/\*\*\s*\*\s*Get all assignments/);
    if (methodMatch2) {
        const replaceEnd2 = methodMatch2.index;
        content2 = content2.substring(0, classStart2) + "\n" + content2.substring(replaceEnd2);
    }
}
fs.writeFileSync('/app/applet/gtvc-lms-production/app/Models/Assignment.php', content2);
console.log("Done fixing");

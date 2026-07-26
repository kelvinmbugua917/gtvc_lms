<?php
use App\Models\Department;
use App\Models\ClassCohort;
use App\Models\AcademicYear;
use App\Core\Model;

$departments = Department::getAllDepartments();
$programs = Department::getAllPrograms();
$classes = ClassCohort::getAllClasses();
$intakes = AcademicYear::getAllIntakes();

$db = Model::getDb();

// Fetch program counts per department
$programCounts = [];
try {
    $pStmt = $db->query("SELECT department_id, COUNT(*) as cnt FROM programs GROUP BY department_id");
    if ($pStmt) {
        foreach ($pStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $programCounts[$row['department_id']] = (int)$row['cnt'];
        }
    }
} catch (\Throwable $e) {}

// Fetch HOD candidates
$hodCandidates = [];
try {
    $hodStmt = $db->query("
        SELECT DISTINCT u.id, u.first_name, u.last_name, u.email
        FROM users u
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id
        WHERE r.name IN ('hod', 'lecturer', 'trainer', 'admin', 'super_admin') 
           OR u.id IN (SELECT DISTINCT head_of_department_id FROM departments WHERE head_of_department_id IS NOT NULL)
        ORDER BY u.first_name ASC, u.last_name ASC
    ");
    if ($hodStmt) {
        $hodCandidates = $hodStmt->fetchAll(\PDO::FETCH_ASSOC);
    }
} catch (\Throwable $e) {}

$activeTab = $_GET['tab'] ?? 'departments';
?>

<div style="margin-bottom: 1rem; display: flex; gap: 0.5rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem;">
    <a href="?tab=departments" class="btn btn-sm <?= $activeTab === 'departments' ? 'btn-primary' : 'btn-secondary' ?>">
        🏢 Academic Departments (<?= count($departments) ?>)
    </a>
    <a href="?tab=programs" class="btn btn-sm <?= $activeTab === 'programs' ? 'btn-primary' : 'btn-secondary' ?>">
        📚 Courses & Programs (<?= count($programs) ?>)
    </a>
    <a href="?tab=classes" class="btn btn-sm <?= $activeTab === 'classes' ? 'btn-primary' : 'btn-secondary' ?>">
        🎓 Classes & Cohorts (<?= count($classes) ?>)
    </a>
</div>

<?php if ($activeTab === 'departments'): ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Academic Departments</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Manage institute academic departments and assign HOD leadership.</p>
        </div>
        <button class="btn btn-sm btn-primary" onclick="openAddDeptModal()">+ Add Department</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Dept Code</th>
                    <th>Department Name</th>
                    <th>Head of Department (HOD)</th>
                    <th>Programs Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($departments)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">
                            No academic departments found. Click "+ Add Department" above to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($departments as $dept): ?>
                        <?php
                            $hodName = (!empty($dept['hod_first_name']) || !empty($dept['hod_last_name']))
                                ? trim($dept['hod_first_name'] . ' ' . $dept['hod_last_name'])
                                : 'Not Assigned';
                            $pCount = $programCounts[$dept['id']] ?? 0;
                        ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($dept['code']) ?></strong></td>
                            <td><?= \App\Core\View::e($dept['name']) ?></td>
                            <td><?= \App\Core\View::e($hodName) ?></td>
                            <td><span class="badge badge-info"><?= $pCount ?> Program<?= $pCount === 1 ? '' : 's' ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick='openEditDeptModal(<?= (int)$dept["id"] ?>, <?= json_encode($dept["code"]) ?>, <?= json_encode($dept["name"]) ?>, <?= (int)($dept["head_of_department_id"] ?? 0) ?>)'>
                                    Edit Dept
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($activeTab === 'programs'): ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Academic Programs & Courses</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Manage academic diploma, craft, artisan, and short courses offered by departments.</p>
        </div>
        <button class="btn btn-sm btn-primary" onclick="openAddProgramModal()">+ Add Program / Course</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Program Code</th>
                    <th>Program Title</th>
                    <th>Department</th>
                    <th>Award Level</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($programs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: #64748b;">
                            No academic programs found. Click "+ Add Program / Course" to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($programs as $prog): ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($prog['code']) ?></strong></td>
                            <td><?= \App\Core\View::e($prog['name']) ?></td>
                            <td><span class="badge badge-secondary"><?= \App\Core\View::e($prog['department_code'] ?? 'GENERAL') ?></span> <?= \App\Core\View::e($prog['department_name'] ?? '') ?></td>
                            <td><span class="badge badge-info"><?= strtoupper(str_replace('_', ' ', $prog['award_type'])) ?></span></td>
                            <td><?= (int)$prog['duration_months'] ?> Months</td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick='openEditProgramModal(<?= (int)$prog["id"] ?>, <?= (int)$prog["department_id"] ?>, <?= json_encode($prog["code"]) ?>, <?= json_encode($prog["name"]) ?>, <?= json_encode($prog["award_type"]) ?>, <?= (int)$prog["duration_months"] ?>)'>
                                    Edit Course
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($activeTab === 'classes'): ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Student Class Groups & Cohorts</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Classes tied to academic programs and intakes where students are enrolled.</p>
        </div>
        <button class="btn btn-sm btn-primary" onclick="openAddClassModal()">+ Add Class Group</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Class Code</th>
                    <th>Class / Cohort Name</th>
                    <th>Parent Program</th>
                    <th>Intake Semester</th>
                    <th>Year of Study</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #64748b;">
                            No class groups found. Click "+ Add Class Group" to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $c): ?>
                        <tr>
                            <td><strong><?= \App\Core\View::e($c['code']) ?></strong></td>
                            <td><?= \App\Core\View::e($c['name']) ?></td>
                            <td><?= \App\Core\View::e($c['program_name'] ?? $c['program_code'] ?? 'N/A') ?></td>
                            <td><?= \App\Core\View::e($c['intake_name'] ?? 'N/A') ?></td>
                            <td>Year <?= (int)$c['year_of_study'] ?></td>
                            <td>
                                <span class="badge <?= $c['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= strtoupper($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick='openEditClassModal(<?= (int)$c["id"] ?>, <?= (int)$c["program_id"] ?>, <?= (int)$c["intake_id"] ?>, <?= json_encode($c["code"]) ?>, <?= json_encode($c["name"]) ?>, <?= (int)$c["year_of_study"] ?>, <?= json_encode($c["status"]) ?>)'>
                                    Edit Class
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Add / Edit Department Modal -->
<div class="modal-backdrop" id="addDepartmentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px; background: white; margin: auto;">
        <div class="card-header">
            <h3 class="card-title" id="deptModalTitle">Add Academic Department</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('addDepartmentModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/departments') ?>" method="POST" style="padding: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" id="deptIdInput" name="id" value="">

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Department Code *</label>
                <input type="text" id="deptCodeInput" name="code" class="form-control" placeholder="e.g. MECH" required>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Department Name *</label>
                <input type="text" id="deptNameInput" name="name" class="form-control" placeholder="e.g. Mechanical Engineering" required>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Head of Department (HOD)</label>
                <select id="deptHodSelect" name="hod_id" class="form-control">
                    <option value="">Select HOD (Optional)...</option>
                    <?php foreach ($hodCandidates as $hod): ?>
                        <option value="<?= (int)$hod['id'] ?>">
                            <?= \App\Core\View::e($hod['first_name'] . ' ' . $hod['last_name'] . ' (' . $hod['email'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDepartmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Add / Edit Program Modal -->
<div class="modal-backdrop" id="programModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px; background: white; margin: auto;">
        <div class="card-header">
            <h3 class="card-title" id="programModalTitle">Add Academic Program</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('programModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/programs') ?>" method="POST" style="padding: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" id="progIdInput" name="id" value="">

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Parent Department *</label>
                <select id="progDeptSelect" name="department_id" class="form-control" required>
                    <option value="">Select Department...</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= (int)$d['id'] ?>"><?= \App\Core\View::e($d['name'] . ' (' . $d['code'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Program Code *</label>
                <input type="text" id="progCodeInput" name="code" class="form-control" placeholder="e.g. DIT" required>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Program Name *</label>
                <input type="text" id="progNameInput" name="name" class="form-control" placeholder="e.g. Diploma in Information Technology" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Award Type</label>
                    <select id="progAwardSelect" name="award_type" class="form-control">
                        <option value="diploma">Diploma</option>
                        <option value="craft_certificate">Craft Certificate</option>
                        <option value="artisan">Artisan Certificate</option>
                        <option value="cbet_level_6">CBET Level 6</option>
                        <option value="cbet_level_5">CBET Level 5</option>
                        <option value="short_course">Short Course</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration (Months)</label>
                    <input type="number" id="progDurationInput" name="duration_months" class="form-control" value="24" required>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('programModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Course / Program</button>
            </div>
        </form>
    </div>
</div>

<!-- Add / Edit Class Modal -->
<div class="modal-backdrop" id="classModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px; background: white; margin: auto;">
        <div class="card-header">
            <h3 class="card-title" id="classModalTitle">Add Class Group / Cohort</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('classModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/classes') ?>" method="POST" style="padding: 1rem;">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">
            <input type="hidden" id="classIdInput" name="id" value="">

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Parent Program *</label>
                <select id="classProgramSelect" name="program_id" class="form-control" required>
                    <option value="">Select Program...</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= \App\Core\View::e($p['name'] . ' (' . $p['code'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Intake Semester *</label>
                <select id="classIntakeSelect" name="intake_id" class="form-control" required>
                    <option value="">Select Intake...</option>
                    <?php foreach ($intakes as $i): ?>
                        <option value="<?= (int)$i['id'] ?>"><?= \App\Core\View::e($i['name'] . ' (' . $i['code'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Class Code *</label>
                <input type="text" id="classCodeInput" name="code" class="form-control" placeholder="e.g. GTVC-DIT-2025A" required>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label class="form-label">Class / Cohort Name *</label>
                <input type="text" id="classNameInput" name="name" class="form-control" placeholder="e.g. Diploma in IT - Jan 2025 Intake" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <div class="form-group">
                    <label class="form-label">Year of Study</label>
                    <input type="number" id="classYearInput" name="year_of_study" class="form-control" value="1" min="1" max="5">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="classStatusSelect" name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('classModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Class Group</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddDeptModal() {
    document.getElementById('deptModalTitle').innerText = "Add Academic Department";
    document.getElementById('deptIdInput').value = "";
    document.getElementById('deptCodeInput').value = "";
    document.getElementById('deptNameInput').value = "";
    document.getElementById('deptHodSelect').value = "";
    openModal('addDepartmentModal');
}

function openEditDeptModal(id, code, name, hodId) {
    document.getElementById('deptModalTitle').innerText = "Edit Academic Department";
    document.getElementById('deptIdInput').value = id;
    document.getElementById('deptCodeInput').value = code;
    document.getElementById('deptNameInput').value = name;
    document.getElementById('deptHodSelect').value = hodId > 0 ? hodId : "";
    openModal('addDepartmentModal');
}

function openAddProgramModal() {
    document.getElementById('programModalTitle').innerText = "Add Academic Program / Course";
    document.getElementById('progIdInput').value = "";
    document.getElementById('progDeptSelect').value = "";
    document.getElementById('progCodeInput').value = "";
    document.getElementById('progNameInput').value = "";
    document.getElementById('progAwardSelect').value = "diploma";
    document.getElementById('progDurationInput').value = "24";
    openModal('programModal');
}

function openEditProgramModal(id, deptId, code, name, award, duration) {
    document.getElementById('programModalTitle').innerText = "Edit Academic Program / Course";
    document.getElementById('progIdInput').value = id;
    document.getElementById('progDeptSelect').value = deptId;
    document.getElementById('progCodeInput').value = code;
    document.getElementById('progNameInput').value = name;
    document.getElementById('progAwardSelect').value = award;
    document.getElementById('progDurationInput').value = duration;
    openModal('programModal');
}

function openAddClassModal() {
    document.getElementById('classModalTitle').innerText = "Add Class Group / Cohort";
    document.getElementById('classIdInput').value = "";
    document.getElementById('classProgramSelect').value = "";
    document.getElementById('classIntakeSelect').value = "";
    document.getElementById('classCodeInput').value = "";
    document.getElementById('classNameInput').value = "";
    document.getElementById('classYearInput').value = "1";
    document.getElementById('classStatusSelect').value = "active";
    openModal('classModal');
}

function openEditClassModal(id, progId, intakeId, code, name, year, status) {
    document.getElementById('classModalTitle').innerText = "Edit Class Group / Cohort";
    document.getElementById('classIdInput').value = id;
    document.getElementById('classProgramSelect').value = progId;
    document.getElementById('classIntakeSelect').value = intakeId;
    document.getElementById('classCodeInput').value = code;
    document.getElementById('classNameInput').value = name;
    document.getElementById('classYearInput').value = year;
    document.getElementById('classStatusSelect').value = status;
    openModal('classModal');
}
</script>

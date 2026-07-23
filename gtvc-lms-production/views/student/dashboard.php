<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
    <div class="stat-card">
        <div>
            <div class="stat-label">Enrolled Units</div>
            <div class="stat-value">5</div>
        </div>
        <div class="stat-icon" style="background: #ccfbf1; color: #0f766e;">📚</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Pending Assignments</div>
            <div class="stat-value">2</div>
        </div>
        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">📝</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Attendance Rate</div>
            <div class="stat-value">92%</div>
        </div>
        <div class="stat-icon" style="background: #d1fae5; color: #047857;">⏱️</div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-label">Exam Clearance</div>
            <div class="stat-value" style="font-size: 1.125rem; color: #047857;">CLEARED</div>
        </div>
        <div class="stat-icon" style="background: #e0f2fe; color: #0369a1;">🛡️</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3" style="margin-top: 1.5rem;">
    <!-- Active Course Offerings -->
    <div class="card lg:grid-cols-2" style="grid-column: span 2;">
        <div class="card-header">
            <h3 class="card-title">Current Semester Course Offerings</h3>
            <a href="<?= \App\Core\View::url('/student/courses') ?>" class="btn btn-sm btn-secondary">View All</a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Unit Code & Name</th>
                        <th>Trainer</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>ICT 201: System Analysis & Design</strong><br>
                            <small class="text-muted">Diploma in ICT - Term 2</small>
                        </td>
                        <td>Eng. John Koech</td>
                        <td>
                            <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                <div style="width: 75%; height: 100%; background: #0d9488;"></div>
                            </div>
                            <small>75%</small>
                        </td>
                        <td><span class="badge badge-success">ACTIVE</span></td>
                    </tr>
                    <tr>
                        <td>
                            <strong>EE 104: Electrical Workshop Practice</strong><br>
                            <small class="text-muted">Craft Certificate in Electrical Eng</small>
                        </td>
                        <td>Tr. Mary Mwangi</td>
                        <td>
                            <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                <div style="width: 88%; height: 100%; background: #0d9488;"></div>
                            </div>
                            <small>88%</small>
                        </td>
                        <td><span class="badge badge-success">ACTIVE</span></td>
                    </tr>
                    <tr>
                        <td>
                            <strong>ME 302: Hydraulics & Pneumatics</strong><br>
                            <small class="text-muted">Diploma in Mechanical Eng</small>
                        </td>
                        <td>Dr. David Ochieng</td>
                        <td>
                            <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                <div style="width: 60%; height: 100%; background: #0d9488;"></div>
                            </div>
                            <small>60%</small>
                        </td>
                        <td><span class="badge badge-success">ACTIVE</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Sidebar Notices -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Bulletins</h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="padding-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0;">
                <span class="badge badge-warning" style="margin-bottom: 0.375rem;">ACADEMIC NOTICE</span>
                <h4 style="font-size: 0.875rem; font-weight: 700;">Term 2 Mid-Semester CAT Timetable</h4>
                <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem;">CAT exams will commence on Monday, July 27th. Ensure exam cards are printed.</p>
            </div>

            <div style="padding-bottom: 0.75rem;">
                <span class="badge badge-info" style="margin-bottom: 0.375rem;">FINANCE DEPT</span>
                <h4 style="font-size: 0.875rem; font-weight: 700;">Fee Payment Clearance Deadline</h4>
                <p style="font-size: 0.775rem; color: #64748b; margin-top: 0.25rem;">All students must have a zero balance or approved fee payment plan by Friday.</p>
            </div>
        </div>
    </div>
</div>

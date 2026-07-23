<div class="card">
    <div class="card-header">
        <h3 class="card-title">Approved Program Fee Structures</h3>
        <button class="btn btn-sm btn-primary" onclick="openModal('feeStructureModal')">+ Create Fee Structure</button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Program Title</th>
                    <th>Academic Year</th>
                    <th>Term</th>
                    <th>Total Fee (KES)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Diploma in Information Communication Technology (DICT)</td>
                    <td>2025/2026</td>
                    <td>Term 2</td>
                    <td><strong>KES 22,500.00</strong></td>
                    <td><button class="btn btn-sm btn-secondary" onclick="openModal('feeStructureModal')">Edit Items</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Fee Structure Modal -->
<div class="modal-backdrop" id="feeStructureModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 100;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <h3 class="card-title">Create / Edit Program Fee Structure</h3>
            <button class="btn btn-sm btn-secondary" onclick="closeModal('feeStructureModal')">✕</button>
        </div>

        <form action="<?= \App\Core\View::url('/api/v1/finance/fee-structures') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\View::e($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Academic Program</label>
                <select name="program_id" class="form-control" required>
                    <option value="1">Diploma in ICT (DICT)</option>
                    <option value="2">Diploma in Electrical Engineering</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Academic Year & Term</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <input type="text" name="academic_year" class="form-control" value="2025/2026" required>
                    <select name="term" class="form-control">
                        <option value="Term 1">Term 1</option>
                        <option value="Term 2" selected>Term 2</option>
                        <option value="Term 3">Term 3</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tuition & Operations Fee (KES)</label>
                <input type="number" name="tuition_fee" class="form-control" placeholder="22500" required>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('feeStructureModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Fee Structure</button>
            </div>
        </form>
    </div>
</div>

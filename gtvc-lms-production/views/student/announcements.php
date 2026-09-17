<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title" style="margin: 0;">Institutional Notices & Targeted Announcements</h3>
            <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">Official circulars, exam notifications, and campus news bulletins.</p>
        </div>
        <form method="GET" action="" style="display: flex; align-items: center; gap: 0.5rem; margin: 0; flex-wrap: wrap;">
            <input type="text" name="search" value="<?= \App\Core\View::e($search ?? '') ?>" placeholder="Search notices..." class="form-control" style="width: 200px; padding: 0.375rem 0.75rem; font-size: 0.875rem;">
            <select name="per_page" onchange="this.form.submit()" class="form-control" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                <option value="5" <?= ($perPage ?? 10) === 5 ? 'selected' : '' ?>>5 / page</option>
                <option value="10" <?= ($perPage ?? 10) === 10 ? 'selected' : '' ?>>10 / page</option>
                <option value="20" <?= ($perPage ?? 10) === 20 ? 'selected' : '' ?>>20 / page</option>
            </select>
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <?php if (!empty($search)): ?>
                <a href="?" class="btn btn-sm btn-secondary" title="Clear filters">✕</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="display: flex; flex-direction: column; gap: 1rem; padding: 1.25rem;">
        <?php if (!empty($announcements)): ?>
            <?php foreach ($announcements as $ann): ?>
                <?php
                    $priority = strtolower($ann['priority'] ?? 'normal');
                    $badgeClass = match($priority) {
                        'urgent' => 'badge-danger',
                        'important' => 'badge-warning',
                        default => 'badge-info'
                    };
                ?>
                <div class="card" style="border: 1px solid #e2e8f0; margin-bottom: 0; padding: 1.25rem; background: #fafafa; border-radius: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                        <span class="badge <?= $badgeClass ?>" style="text-transform: uppercase;">
                            <?= \App\Core\View::e($ann['target_role'] ?? 'ALL') ?> &bull; <?= \App\Core\View::e($priority) ?>
                        </span>
                        <small class="text-muted"><?= date('Y-m-d H:i', strtotime($ann['created_at'] ?? 'now')) ?></small>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">
                        <?= \App\Core\View::e($ann['title']) ?>
                    </h3>
                    <p style="font-size: 0.9rem; color: #475569; margin: 0; line-height: 1.6;">
                        <?= nl2br(\App\Core\View::e($ann['content'])) ?>
                    </p>
                    <?php if (!empty($ann['attachment_name'])): ?>
                        <div style="margin-top: 0.75rem; font-size: 0.85rem;">
                            📎 <strong>Attachment:</strong> <?= \App\Core\View::e($ann['attachment_name']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="border: 1px solid #cbd5e1; margin-bottom: 0; padding: 1.25rem; border-radius: 8px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span class="badge badge-warning">ACADEMIC BOARD</span>
                    <small class="text-muted">2026-07-20</small>
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">Term 2 Examination Registration & Timetable Release</h3>
                <p style="font-size: 0.9rem; color: #475569; margin-top: 0.5rem; line-height: 1.6;">
                    All students in Diploma and Craft Certificate programs are notified that end-of-term examinations will commence on August 10th. Ensure all fee balances are fully settled to receive your digital exam hall permit.
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?= isset($paginator) ? $paginator->render() : '' ?>
</div>

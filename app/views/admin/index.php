<?php
$reports = $reports ?? [];
$stats = $stats ?? [];
$closeRequests = $closeRequests ?? [];
?>

<div class="container py-5">
  <span class="eyebrow">
    <i data-lucide="shield-check"></i>
    Admin workspace
  </span>

  <h1 class="fw-black mt-3">Admin Panel</h1>

  <div class="my-3">
    <a href="?route=admin-analytics" class="btn btn-brand rounded-pill px-4"><i data-lucide="bar-chart-3"></i> View Analytics Dashboard</a>
  </div>

  <div class="stats row g-3 my-4">
    <div class="col-md-3">
      <div class="stat">
        <i data-lucide="clipboard-list"></i>
        <b><?= $stats['total'] ?? 0 ?></b>
        <span>Total</span>
      </div>
    </div>

    <div class="col-md-3">
      <div class="stat">
        <i data-lucide="map-pin"></i>
        <b><?= $stats['missing'] ?? 0 ?></b>
        <span>Missing</span>
      </div>
    </div>

    <div class="col-md-3">
      <div class="stat">
        <i data-lucide="scan-search"></i>
        <b><?= $stats['found'] ?? 0 ?></b>
        <span>Found</span>
      </div>
    </div>

    <div class="col-md-3">
      <div class="stat">
        <i data-lucide="party-popper"></i>
        <b><?= $stats['reunited'] ?? 0 ?></b>
        <span>Reunited</span>
      </div>
    </div>
  </div>



  <?php if (!empty($closeRequests)): ?>
    <div class="dashboard-table table-responsive mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="fw-black mb-1">Pending Reunited / Close Requests</h4>
          <p class="text-muted mb-0">Verify proof photos and contact details before approving.</p>
        </div>
      </div>

      <table class="table align-middle">
        <thead>
          <tr>
            <th>Animal</th>
            <th>Request</th>
            <th>Submitted By</th>
            <th>Proof</th>
            <th>Note</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($closeRequests as $cr): ?>
            <tr>
              <td class="fw-bold"><?= htmlspecialchars($cr['animal_name'] ?: 'Unknown') ?></td>
              <td><span class="tag <?= htmlspecialchars($cr['result_status']) ?>"><?= htmlspecialchars($cr['result_status']) ?></span></td>
              <td><?= htmlspecialchars($cr['name']) ?><br><small class="text-muted"><?= htmlspecialchars($cr['contact']) ?></small></td>
              <td>
                <?php if (!empty($cr['proof_photo'])): ?>
                  <a href="uploads/<?= htmlspecialchars($cr['proof_photo']) ?>" target="_blank" class="btn btn-sm btn-dark admin-action-btn">View Photo</a>
                <?php else: ?>
                  <span class="text-muted">No photo</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($cr['note']) ?></td>
              <td>
                <div class="d-flex gap-2">
                  <form method="post" action="?route=admin-close-request" onsubmit="return confirm('Approve this request and update the report status?');">
                    <input type="hidden" name="id" value="<?= $cr['id'] ?>">
                    <input type="hidden" name="action_type" value="approve">
                    <button class="btn btn-sm btn-success admin-action-btn" type="submit">Approve</button>
                  </form>
                  <form method="post" action="?route=admin-close-request" onsubmit="return confirm('Reject this request?');">
                    <input type="hidden" name="id" value="<?= $cr['id'] ?>">
                    <input type="hidden" name="action_type" value="reject">
                    <button class="btn btn-sm btn-danger admin-action-btn" type="submit">Reject</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <div class="dashboard-table table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Animal</th>
          <th>Owner</th>
          <th>Type</th>
          <th>Status</th>
          <th>Approved</th>
          <th>Update</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
          <tr>
            <td class="fw-bold"><?= htmlspecialchars($r['animal_name'] ?: 'Unknown') ?></td>
            <td><?= htmlspecialchars($r['user_name']) ?></td>
            <td>
              <span class="tag <?= htmlspecialchars($r['report_type']) ?>">
                <?= htmlspecialchars($r['report_type']) ?>
              </span>
            </td>
            <td>
              <span class="tag <?= htmlspecialchars($r['status']) ?>">
                <?= htmlspecialchars($r['status']) ?>
              </span>
            </td>
            <td><?= $r['is_approved'] ? 'Yes' : 'No' ?></td>
            <td>
              <form method="post" action="?route=admin-status" class="d-flex gap-2">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">

                <select name="status" class="form-select form-select-sm">
                  <option value="active" <?= $r['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="reunited" <?= $r['status'] === 'reunited' ? 'selected' : '' ?>>Reunited</option>
                  <option value="closed" <?= $r['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>

                <select name="approved" class="form-select form-select-sm">
                  <option value="1" <?= $r['is_approved'] ? 'selected' : '' ?>>Approve</option>
                  <option value="0" <?= !$r['is_approved'] ? 'selected' : '' ?>>Pending</option>
                </select>

                <button class="btn btn-sm btn-brand admin-action-btn" type="submit">Save</button>
              </form>
            </td>
            <td>
              <form method="post" action="?route=admin-delete" onsubmit="return confirm('Delete this report permanently?');">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-sm btn-danger btn-delete admin-action-btn" type="submit">
                  <i data-lucide="trash-2"></i> Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

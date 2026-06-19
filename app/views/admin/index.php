<?php
$reports = $reports ?? [];
$stats = $stats ?? [];
?>

<div class="container py-5">
  <span class="eyebrow">
    <i data-lucide="shield-check"></i>
    Admin workspace
  </span>

  <h1 class="fw-black mt-3">Admin Panel</h1>

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
                  <option value="active">Active</option>
                  <option value="reunited">Reunited</option>
                  <option value="closed">Closed</option>
                </select>

                <select name="approved" class="form-select form-select-sm">
                  <option value="1">Approve</option>
                  <option value="0">Pending</option>
                </select>

                <button class="btn btn-sm btn-brand" type="submit">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

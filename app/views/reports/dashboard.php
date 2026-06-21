<?php
$reports = $reports ?? [];
$notifications = $notifications ?? [];
?>
<div class="container py-5">
  <div class="section-head">
    <div>
      <span class="eyebrow"><i data-lucide="layout-dashboard"></i> User panel</span>
      <h1 class="fw-black mt-3">My Dashboard</h1>
      <p class="text-muted">Track your submitted reports and latest community updates.</p>
    </div>
    <a class="btn btn-brand rounded-pill px-4" href="?route=report-create"><i data-lucide="circle-plus"></i> New Report</a>
  </div>

  <div class="row g-4 mt-2">
    <div class="col-lg-8">
      <div class="table-responsive dashboard-table">
        <table class="table align-middle">
          <thead><tr><th>Animal</th><th>Type</th><th>Status</th><th>Approved</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($reports as $r): ?>
              <tr>
                <td class="fw-bold"><?= htmlspecialchars($r['animal_name'] ?: 'Unknown') ?></td>
                <td><span class="tag <?= htmlspecialchars($r['report_type']) ?>"><?= ucfirst(htmlspecialchars($r['report_type'])) ?></span></td>
                <td><span class="tag <?= htmlspecialchars($r['status']) ?>"><?= ucfirst(htmlspecialchars($r['status'])) ?></span></td>
                <td><?= $r['is_approved'] ? 'Yes' : 'Pending' ?></td>
                <td><a href="?route=report-show&id=<?= htmlspecialchars($r['id']) ?>" class="btn btn-sm btn-dark rounded-pill"><i data-lucide="eye"></i> View</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="dashboard-side-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="fw-black mb-0"><i data-lucide="bell"></i> Latest Alerts</h4>
          <a href="?route=notifications">View all</a>
        </div>
        <?php foreach($notifications as $n): ?>
          <a class="mini-notif <?= !$n['is_read'] ? 'unread' : '' ?>" href="<?= !empty($n['report_id']) ? '?route=report-show&id=' . htmlspecialchars($n['report_id']) : '#' ?>">
            <b><?= htmlspecialchars($n['title']) ?></b>
            <span><?= htmlspecialchars($n['message']) ?></span>
          </a>
        <?php endforeach; ?>
        <?php if(empty($notifications)): ?><p class="text-muted mb-0">No notifications yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$analytics = $analytics ?? [];
$reports = $analytics['reports'] ?? [];
$successRate = $reports['success_rate'] ?? 0;
$monthly = array_reverse($analytics['monthly'] ?? []);
$maxMonthly = max(array_map(fn($r) => (int)($r['total'] ?? 0), $monthly ?: [['total'=>1]]));
$areas = $analytics['areas'] ?? [];
$maxArea = max(array_map(fn($r) => (int)($r['total'] ?? 0), $areas ?: [['total'=>1]]));
function admin_avatar($photo, $name) {
  $photo = trim($photo ?? '');
  if ($photo !== '') {
    if (!preg_match('/^https?:\/\//i', $photo) && strpos($photo, 'uploads/') !== 0) $photo = 'uploads/' . $photo;
    return '<img src="' . htmlspecialchars($photo) . '" alt="' . htmlspecialchars($name) . '">';
  }
  return htmlspecialchars(strtoupper(substr($name ?: 'U', 0, 1)));
}
?>
<div class="container py-5 admin-analytics-page">
  <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
    <div>
      <span class="eyebrow"><i data-lucide="bar-chart-3"></i> Analytics Dashboard</span>
      <h1 class="fw-black mt-3 mb-1">PawJect Insights</h1>
      <p class="text-muted mb-0">Admin overview for reports, sightings, community activity, and moderation.</p>
    </div>
    <a href="?route=admin" class="btn btn-light rounded-pill px-4"><i data-lucide="arrow-left"></i> Back to Admin</a>
  </div>

  <div class="stats row g-3 my-4">
    <div class="col-md-3"><div class="stat"><i data-lucide="clipboard-list"></i><b><?= (int)($reports['total'] ?? 0) ?></b><span>Total Reports</span></div></div>
    <div class="col-md-3"><div class="stat"><i data-lucide="activity"></i><b><?= (int)($reports['active'] ?? 0) ?></b><span>Active Cases</span></div></div>
    <div class="col-md-3"><div class="stat"><i data-lucide="heart"></i><b><?= (int)($reports['reunited'] ?? 0) ?></b><span>Reunited Cases</span></div></div>
    <div class="col-md-3"><div class="stat"><i data-lucide="percent"></i><b><?= htmlspecialchars($successRate) ?>%</b><span>Success Rate</span></div></div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="analytics-card h-100">
        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
          <div><h3 class="fw-black mb-1">Monthly Reports</h3><p class="text-muted mb-0">Report submissions grouped by month.</p></div>
          <span class="mini-badge"><i data-lucide="calendar-days"></i> Last 6 months</span>
        </div>
        <?php if(empty($monthly)): ?>
          <div class="empty-state compact-empty"><div class="empty-state-icon"><i data-lucide="bar-chart-3"></i></div><h5>No monthly data yet</h5><p>Reports will appear here once submitted.</p></div>
        <?php else: ?>
          <div class="monthly-bars">
            <?php foreach($monthly as $row): $height = max(18, ((int)$row['total'] / max(1,$maxMonthly)) * 170); ?>
              <div class="monthly-bar-item">
                <div class="monthly-bar-value"><?= (int)$row['total'] ?></div>
                <div class="monthly-bar"><i style="height:<?= $height ?>px"></i></div>
                <small><?= htmlspecialchars($row['month']) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="analytics-card h-100">
        <h3 class="fw-black">This Month</h3>
        <div class="metric-pill"><span>New Reports</span><b><?= (int)($analytics['thisMonth']['reports'] ?? 0) ?></b></div>
        <div class="metric-pill"><span>New Sightings</span><b><?= (int)($analytics['thisMonth']['sightings'] ?? 0) ?></b></div>
        <div class="metric-pill"><span>New Comments</span><b><?= (int)($analytics['thisMonth']['comments'] ?? 0) ?></b></div>
        <div class="metric-pill"><span>Followers</span><b><?= (int)($analytics['followers']['total'] ?? 0) ?></b></div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="analytics-card h-100">
        <h3 class="fw-black">Missing Area Statistics</h3>
        <p class="text-muted">Top locations with the highest number of reports.</p>
        <?php foreach($areas as $row): $width = max(8, ((int)$row['total'] / max(1,$maxArea)) * 100); ?>
          <div class="heat-row">
            <span><?= htmlspecialchars($row['location']) ?></span>
            <div><i style="width:<?= $width ?>%"></i></div>
            <b><?= (int)$row['total'] ?></b>
          </div>
        <?php endforeach; ?>
        <?php if(empty($areas)): ?><div class="empty-state compact-empty"><div class="empty-state-icon"><i data-lucide="map-pin-off"></i></div><h5>No location data yet</h5><p>Area statistics will appear here after reports are submitted.</p></div><?php endif; ?>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="analytics-card h-100">
        <h3 class="fw-black">Top Contributors</h3>
        <p class="text-muted">Community members ranked by reputation activity.</p>
        <?php foreach(($analytics['contributors'] ?? []) as $index => $u): ?>
          <div class="leaderboard-row">
            <span class="leader-rank">#<?= $index + 1 ?></span>
            <span class="leader-avatar"><?= admin_avatar($u['profile_photo'] ?? '', $u['name'] ?? 'User') ?></span>
            <div>
              <b><?= htmlspecialchars($u['name'] ?? 'User') ?></b>
              <small><?= (int)($u['reports'] ?? 0) ?> reports · <?= (int)($u['verified_sightings'] ?? 0) ?> verified sightings</small>
            </div>
            <strong><?= (int)($u['points'] ?? 0) ?> pts</strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-lg-7">
      <div class="analytics-card h-100">
        <h3 class="fw-black">Most Active Cases</h3>
        <p class="text-muted">Cases with the most comments, sightings, and followers.</p>
        <?php foreach(($analytics['activeCases'] ?? []) as $case): ?>
          <a class="active-case-row" href="?route=report-show&id=<?= (int)$case['id'] ?>">
            <div><b><?= htmlspecialchars($case['animal_name'] ?: 'Unknown pet') ?></b><small><?= htmlspecialchars($case['location'] ?: 'No location') ?></small></div>
            <span><?= (int)$case['comments'] ?> comments</span>
            <span><?= (int)$case['sightings'] ?> sightings</span>
            <span><?= (int)$case['followers'] ?> followers</span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="analytics-card h-100">
        <h3 class="fw-black">Report Types</h3>
        <?php foreach($analytics['byType'] ?? [] as $row): ?>
          <div class="metric-pill"><span><?= ucfirst(htmlspecialchars($row['report_type'])) ?></span><b><?= (int)$row['total'] ?></b></div>
        <?php endforeach; ?>
        <hr>
        <div class="metric-pill"><span>Total Sightings</span><b><?= (int)($analytics['sightings']['total'] ?? 0) ?></b></div>
        <div class="metric-pill"><span>Verified Sightings</span><b><?= (int)($analytics['sightings']['verified'] ?? 0) ?></b></div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="analytics-card moderation-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="fw-black mb-0">Reported Comments</h3><span class="mini-badge"><?= count($analytics['reportedComments'] ?? []) ?> items</span></div>
        <?php if(empty($analytics['reportedComments'])): ?>
          <div class="empty-state compact-empty"><div class="empty-state-icon"><i data-lucide="shield-check"></i></div><h5>No reported comments</h5><p>Reported comments for moderation will appear here.</p></div>
        <?php endif; ?>
        <?php foreach(($analytics['reportedComments'] ?? []) as $c): ?>
          <a class="moderation-row" href="?route=report-show&id=<?= (int)$c['report_id'] ?>#comments">
            <b><?= htmlspecialchars($c['user_name'] ?: 'Unknown user') ?></b>
            <p><?= htmlspecialchars(strlen($c['comment'] ?? '') > 90 ? substr($c['comment'], 0, 90) . '...' : ($c['comment'] ?? '')) ?></p>
            <small>Reason: <?= htmlspecialchars($c['report_reason'] ?: 'Not specified') ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="analytics-card moderation-card h-100">
        <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="fw-black mb-0">Pending Sightings</h3><span class="mini-badge"><?= count($analytics['pendingSightings'] ?? []) ?> items</span></div>
        <?php if(empty($analytics['pendingSightings'])): ?>
          <div class="empty-state compact-empty"><div class="empty-state-icon"><i data-lucide="badge-check"></i></div><h5>No pending sightings</h5><p>Unverified sighting reports will appear here.</p></div>
        <?php endif; ?>
        <?php foreach(($analytics['pendingSightings'] ?? []) as $s): ?>
          <a class="moderation-row" href="?route=report-show&id=<?= (int)$s['report_id'] ?>#sightings">
            <b><?= htmlspecialchars($s['animal_name'] ?: 'Unknown pet') ?></b>
            <p><?= htmlspecialchars($s['location'] ?: 'No location') ?></p>
            <small>Submitted by <?= htmlspecialchars($s['name'] ?: 'Anonymous') ?> · <?= htmlspecialchars($s['created_at']) ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

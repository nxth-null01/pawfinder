<?php $analytics = $analytics ?? []; $reports = $analytics['reports'] ?? []; ?>
<div class="container py-5">
  <span class="eyebrow"><i data-lucide="bar-chart-3"></i> Analytics Dashboard</span>
  <h1 class="fw-black mt-3">PawJect Insights</h1>
  <p class="text-muted">A simple overview of platform activity and community engagement.</p>

  <div class="stats row g-3 my-4">
    <div class="col-md-3"><div class="stat"><i data-lucide="clipboard-list"></i><b><?= $reports['total'] ?? 0 ?></b><span>Total Reports</span></div></div>
    <div class="col-md-3"><div class="stat"><i data-lucide="clock-3"></i><b><?= $analytics['pending']['total'] ?? 0 ?></b><span>Pending</span></div></div>
    <div class="col-md-3"><div class="stat"><i data-lucide="message-circle"></i><b><?= $analytics['comments']['total'] ?? 0 ?></b><span>Comments</span></div></div>
    <div class="col-md-3"><div class="stat"><i data-lucide="map-pin-check"></i><b><?= $analytics['sightings']['total'] ?? 0 ?></b><span>Sightings</span></div></div>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="analytics-card">
        <h3 class="fw-black">Recent Reports by Day</h3>
        <?php foreach(array_reverse($analytics['recent'] ?? []) as $row): $width = min(100, max(8, ((int)$row['total']) * 18)); ?>
          <div class="bar-row"><span><?= htmlspecialchars($row['day']) ?></span><div><i style="width:<?= $width ?>%"></i></div><b><?= $row['total'] ?></b></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="analytics-card">
        <h3 class="fw-black">Report Types</h3>
        <?php foreach($analytics['byType'] ?? [] as $row): ?>
          <div class="metric-pill"><span><?= ucfirst(htmlspecialchars($row['report_type'])) ?></span><b><?= $row['total'] ?></b></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

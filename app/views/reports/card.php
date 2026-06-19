<?php
$hasPhoto = !empty($r['photo']);
$reportType = $r['report_type'] ?? 'missing';
$status = $r['status'] ?? 'active';
$animalName = $r['animal_name'] ?: 'Unknown';
?>

<div class="col-lg-4 col-md-6">
  <article class="pet-card h-100">
    <div class="pet-media">
      <?php if ($hasPhoto): ?>
        <img
          src="uploads/<?= htmlspecialchars($r['photo']) ?>"
          class="pet-img"
          alt="<?= htmlspecialchars($animalName) ?> photo"
          onerror="this.closest('.pet-media').innerHTML='<div class=&quot;pet-placeholder&quot;><i data-lucide=&quot;paw-print&quot;></i><span>No photo available</span></div>'; lucide.createIcons();"
        >
      <?php else: ?>
        <div class="pet-placeholder">
          <i data-lucide="paw-print"></i>
          <span>No photo available</span>
        </div>
      <?php endif; ?>
    </div>

    <div class="pet-body">
      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <span class="tag <?= htmlspecialchars($reportType) ?>">
          <i data-lucide="<?= $reportType === 'missing' ? 'map-pin' : 'scan-search' ?>"></i>
          <?= ucfirst(htmlspecialchars($reportType)) ?>
        </span>

        <span class="tag <?= htmlspecialchars($status) ?>">
          <i data-lucide="activity"></i>
          <?= ucfirst(htmlspecialchars($status)) ?>
        </span>
      </div>

      <h4 class="pet-title">
        <?= htmlspecialchars($animalName) ?>
      </h4>

      <p class="pet-meta">
        <?= htmlspecialchars($r['species']) ?> • <?= htmlspecialchars($r['breed']) ?> • <?= htmlspecialchars($r['color']) ?>
      </p>

      <p class="pet-location">
        <i data-lucide="map-pinned"></i>
        <?= htmlspecialchars($r['location']) ?>
      </p>

      <a class="btn btn-sm btn-dark rounded-pill px-3" href="?route=report-show&id=<?= $r['id'] ?>">
        <i data-lucide="eye"></i>
        View Details
      </a>
    </div>
  </article>
</div>

<?php $stories = $stories ?? []; ?>
<div class="container py-5">
  <div class="section-title-center mb-4">
    <span class="mini-paw">🐾</span>
    <div>
      <div class="section-kicker text-center">Success Stories</div>
      <h1 class="fw-black text-center mb-0">Pets Reunited With Their People</h1>
      <p class="text-muted text-center mt-2">A warm collection of resolved PawJect cases.</p>
    </div>
    <span class="mini-paw">🐾</span>
  </div>

  <div class="row g-4">
    <?php foreach($stories as $s): ?>
      <div class="col-lg-4 col-md-6">
        <article class="success-card h-100">
          <img src="uploads/<?= htmlspecialchars($s['photo'] ?: 'placeholder.svg') ?>" alt="<?= htmlspecialchars($s['animal_name'] ?: 'Pet') ?>">
          <div class="success-body">
            <span class="tag reunited"><i data-lucide="heart"></i> Reunited</span>
            <h3><?= htmlspecialchars($s['animal_name'] ?: 'Unknown Pet') ?></h3>
            <p><?= htmlspecialchars($s['species'] . ' • ' . $s['location']) ?></p>
            <a href="?route=report-show&id=<?= htmlspecialchars($s['id']) ?>" class="btn btn-brand rounded-pill px-4">View Story</a>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if(empty($stories)): ?>
    <div class="empty-state"><i data-lucide="heart"></i><h3>No reunited stories yet</h3><p>Once a report is marked reunited, it will shine here.</p></div>
  <?php endif; ?>
</div>

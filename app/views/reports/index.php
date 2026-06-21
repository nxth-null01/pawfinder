<?php
$reports = $reports ?? [];
?>
<div class="container py-5">
  <span class="eyebrow">
    <i data-lucide="search"></i>
    Browse reports
  </span>

  <div class="section-head mt-3">
    <div>
      <h1 class="fw-black mb-1">Lost & Found Animals</h1>
      <p class="text-muted mb-0">Search missing pets, found animals, and active community reports.</p>
    </div>
  </div>

  <form class="filter-card search-filter mt-4" method="GET" action="">
    <input type="hidden" name="route" value="reports">

    <div class="search-field search-field-main">
      <label class="visually-hidden" for="q">Search</label>
      <input
        id="q"
        name="q"
        class="form-control form-control-lg"
        placeholder="Search breed, color, location..."
        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
      >
    </div>

    <div class="search-field">
      <label class="visually-hidden" for="type">Type</label>
      <select id="type" name="type" class="form-select form-select-lg">
        <option value="">All Types</option>
        <option value="missing" <?= ($_GET['type'] ?? '') === 'missing' ? 'selected' : '' ?>>Missing</option>
        <option value="found" <?= ($_GET['type'] ?? '') === 'found' ? 'selected' : '' ?>>Found/Stray</option>
      </select>
    </div>

    <div class="search-field">
      <label class="visually-hidden" for="status">Status</label>
      <select id="status" name="status" class="form-select form-select-lg">
        <option value="">All Status</option>
        <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
        <option value="reunited" <?= ($_GET['status'] ?? '') === 'reunited' ? 'selected' : '' ?>>Reunited</option>
      </select>
    </div>

    <div class="search-field search-field-button">
      <button class="btn btn-brand btn-search w-100" type="submit">
        <i data-lucide="search"></i>
      </button>
    </div>
  </form>

  <div class="row g-4 mt-2">
    <?php foreach ($reports as $r): ?>
      <?php include __DIR__ . '/card.php'; ?>
    <?php endforeach; ?>

    <?php if (empty($reports)): ?>
      <div class="col-12">
        <div class="empty-state">
          <i data-lucide="dog"></i>
          <h4>No reports found</h4>
          <p>Try another keyword, location, or report type.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

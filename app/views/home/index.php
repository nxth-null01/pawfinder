<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="eyebrow"><i data-lucide="heart-handshake"></i> Community Lost & Found Pet System</span>
        <h1 class="display-3 fw-black mt-3">Bring missing pets back home faster.</h1>
        <p class="lead text-muted my-4">Report missing pets, found animals, and sightings with a warm modern platform for communities, rescuers, and shelters.</p>
        <div class="d-flex gap-2 flex-wrap">
          <a href="?route=report-create" class="btn btn-brand btn-lg rounded-pill px-4"><i data-lucide="circle-plus"></i> Report Animal</a>
          <a href="?route=reports" class="btn btn-outline-dark btn-lg rounded-pill px-4"><i data-lucide="search"></i> Browse Reports</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="hero-card">
          <div class="hero-icon"><i data-lucide="paw-print"></i></div>
          <div class="hero-mini">
            <h3 class="fw-black mb-2">Lost pet near your area?</h3>
            <p class="mb-0">Post details, photo, location, and contact info in minutes.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="stats row g-3 mt-5">
      <div class="col-md-3"><div class="stat"><i data-lucide="clipboard-list"></i><b><?= $stats['total']??0 ?></b><span>Total Reports</span></div></div>
      <div class="col-md-3"><div class="stat"><i data-lucide="map-pin"></i><b><?= $stats['missing']??0 ?></b><span>Missing</span></div></div>
      <div class="col-md-3"><div class="stat"><i data-lucide="scan-search"></i><b><?= $stats['found']??0 ?></b><span>Found</span></div></div>
      <div class="col-md-3"><div class="stat"><i data-lucide="party-popper"></i><b><?= $stats['reunited']??0 ?></b><span>Reunited</span></div></div>
    </div>
  </div>
</section>
<section class="container mt-5">
  <div class="section-head mb-3">
    <div><h2 class="fw-black">Recent Reports</h2><p class="text-muted mb-0">Latest missing and found animals.</p></div>
    <a href="?route=reports" class="btn btn-light rounded-pill px-4"><i data-lucide="arrow-right"></i> View all</a>
  </div>
  <div class="row g-4"><?php foreach(array_slice($reports,0,3) as $r): include __DIR__.'/../reports/card.php'; endforeach; ?></div>
</section>

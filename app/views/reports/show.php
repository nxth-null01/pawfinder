<div class="container py-5">
    <div class="row g-4 align-items-start">
        <div class="col-lg-6">
            <img class="detail-img" src="uploads/<?= $report['photo'] ?: 'placeholder.svg' ?>" alt="Animal photo">
        </div>

        <div class="col-lg-6">
            <span class="tag <?= $report['report_type'] ?>">
                <i data-lucide="<?= $report['report_type'] === 'missing' ? 'map-pin' : 'scan-search' ?>"></i>
                <?= ucfirst($report['report_type']) ?>
            </span>

            <h1 class="fw-black mt-3"><?= htmlspecialchars($report['animal_name'] ?: 'Unknown') ?></h1>
            <p class="lead text-muted"><?= htmlspecialchars($report['species'] . ' • ' . $report['breed'] . ' • ' . $report['color']) ?></p>

            <div class="info-box">
                <p><i data-lucide="map-pinned"></i> <b>Location:</b> <?= htmlspecialchars($report['location']) ?></p>
                <p><i data-lucide="calendar-days"></i> <b>Date:</b> <?= htmlspecialchars($report['last_seen_date']) ?></p>
                <p><i data-lucide="user-round"></i> <b>Posted by:</b> <?= htmlspecialchars($report['user_name']) ?></p>
                <p><i data-lucide="mail"></i> <b>Email:</b> <?= htmlspecialchars($report['email']) ?></p>

                <?php if (!empty($report['owner_contact'])): ?>
                    <p class="mb-0"><i data-lucide="phone"></i> <b>Other Contact:</b> <?= htmlspecialchars($report['owner_contact']) ?></p>
                <?php endif; ?>
            </div>

            <p class="mt-3"><?= nl2br(htmlspecialchars($report['description'])) ?></p>
        </div>
    </div>

    <hr class="my-5">

    <span class="eyebrow"><i data-lucide="map-plus"></i> Help update location</span>
    <h3 class="fw-black mt-3">Report a Sighting</h3>

    <form method="post" action="?route=sighting-store" enctype="multipart/form-data" class="row g-3 sighting-form">
        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">

        <div class="col-md-4">
            <input name="name" class="form-control" placeholder="Your name" required>
        </div>

        <div class="col-md-4">
            <input name="contact" class="form-control" placeholder="Contact" required>
        </div>

        <div class="col-md-4">
            <input name="location" class="form-control" placeholder="Where seen" required>
        </div>

        <div class="col-md-12">
            <label>Upload photo if you saw the pet</label>
            <input type="file" name="sighting_photo" class="form-control" accept="image/*">
        </div>

        <div class="col-12">
            <textarea name="note" class="form-control" placeholder="Details" rows="3"></textarea>
        </div>

        <div>
            <button class="btn btn-dark rounded-pill px-4">
                <i data-lucide="send"></i> Submit Sighting
            </button>
        </div>
    </form>

    <div class="mt-4">
        <?php foreach ($sightings as $s): ?>
            <div class="sighting">
                <div class="sighting-content">
                    <?php if (!empty($s['photo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($s['photo']) ?>" alt="Sighting photo" class="sighting-photo">
                    <?php endif; ?>

                    <div>
                        <b><i data-lucide="map-pin"></i> <?= htmlspecialchars($s['location']) ?></b>
                        <p class="mb-0"><?= htmlspecialchars($s['note']) ?></p>
                        <small class="text-muted">By <?= htmlspecialchars($s['name']) ?> • <?= $s['created_at'] ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

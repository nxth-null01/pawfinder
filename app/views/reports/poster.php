<?php
$posterTitle = strtoupper(($report['report_type'] ?? 'missing') === 'missing' ? 'Missing Pet' : 'Found Pet');
$statusLabel = ucfirst($report['status'] ?? 'active');
$photo = $report['photo'] ?: 'placeholder.svg';
?>
<div class="container py-5 poster-page">
    <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-4 no-print">
        <a class="btn btn-light rounded-pill px-4" href="?route=report-show&id=<?= htmlspecialchars($report['id']) ?>"><i data-lucide="arrow-left"></i> Back to Report</a>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-brand rounded-pill px-4" type="button" onclick="window.print()"><i data-lucide="printer"></i> Print / Save PDF</button>
            <button class="btn btn-dark rounded-pill px-4" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>'); this.innerText='Copied link';"><i data-lucide="copy"></i> Copy Link</button>
        </div>
    </div>

    <section class="pet-poster-card mx-auto">
        <div class="poster-alert-band">
            <span><?= htmlspecialchars($posterTitle) ?></span>
        </div>

        <div class="poster-body">
            <div class="poster-photo-wrap">
                <img src="uploads/<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($report['animal_name'] ?: 'Pet photo') ?>">
            </div>
            <div class="poster-details">
                <span class="tag <?= htmlspecialchars($report['status'] ?? 'active') ?>"><i data-lucide="activity"></i> <?= htmlspecialchars($statusLabel) ?></span>
                <h1><?= htmlspecialchars($report['animal_name'] ?: 'Unknown Pet') ?></h1>
                <p class="poster-subtitle"><?= htmlspecialchars(trim(($report['species'] ?? '') . ' • ' . ($report['breed'] ?? '') . ' • ' . ($report['color'] ?? ''), ' •')) ?></p>

                <div class="poster-info-grid">
                    <div><small>Last Seen</small><b><?= htmlspecialchars($lastSeenText) ?></b></div>
                    <div><small>Date</small><b><?= htmlspecialchars($report['last_seen_date'] ?? 'Unknown') ?></b></div>
                    <div><small>Location</small><b><?= htmlspecialchars($report['location'] ?? 'Unknown') ?></b></div>
                    <div><small>Reward</small><b><?= !empty($report['reward_amount']) ? '₱' . number_format((float)$report['reward_amount'], 0) : 'None' ?></b></div>
                </div>

                <?php if(!empty($report['description'])): ?>
                    <div class="poster-description">
                        <small>Description</small>
                        <p><?= nl2br(htmlspecialchars($report['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="poster-contact-box">
                    <div>
                        <small>Contact</small>
                        <b><?= htmlspecialchars($report['owner_contact'] ?: $report['email']) ?></b>
                    </div>
                    <div>
                        <small>Posted by</small>
                        <b><?= htmlspecialchars($report['user_name']) ?></b>
                    </div>
                </div>
            </div>
        </div>

        <div class="poster-footer">
            <div>
                <b>PawJect Community Alert</b>
                <span>Please share responsibly and contact the owner if you have useful information.</span>
            </div>
            <small><?= htmlspecialchars($shareUrl) ?></small>
        </div>
    </section>
</div>

<?php
$report = $report ?? [];
$sightings = $sightings ?? [];
$closeRequests = $closeRequests ?? [];
$comments = $comments ?? [];
$timeline = $timeline ?? [];
$approved = !empty($report['is_approved']);
$hasSighting = count($sightings) > 0;
$isReunited = ($report['status'] ?? '') === 'reunited';
?>
<div class="container py-5">
    <div class="row g-4 align-items-start">
        <div class="col-lg-6">
            <img class="detail-img" src="uploads/<?= htmlspecialchars($report['photo'] ?: 'placeholder.svg') ?>" alt="Animal photo">
        </div>

        <div class="col-lg-6">
            <span class="tag <?= htmlspecialchars($report['report_type']) ?>">
                <i data-lucide="<?= $report['report_type'] === 'missing' ? 'map-pin' : 'scan-search' ?>"></i>
                <?= ucfirst(htmlspecialchars($report['report_type'])) ?>
            </span>

            <h1 class="fw-black mt-3"><?= htmlspecialchars($report['animal_name'] ?: 'Unknown') ?></h1>
            <p class="lead text-muted"><?= htmlspecialchars(($report['species'] ?? '') . ' • ' . ($report['breed'] ?? '') . ' • ' . ($report['color'] ?? '')) ?></p>

            <div class="progress-tracker mb-4">
                <div class="progress-step active"><span><i data-lucide="clipboard-plus"></i></span><b>Submitted</b></div>
                <div class="progress-line <?= $approved ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $approved ? 'active' : '' ?>"><span><i data-lucide="badge-check"></i></span><b>Approved</b></div>
                <div class="progress-line <?= $hasSighting ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $hasSighting ? 'active' : '' ?>"><span><i data-lucide="map-pin-check"></i></span><b>Sighting</b></div>
                <div class="progress-line <?= $isReunited ? 'active' : '' ?>"></div>
                <div class="progress-step <?= $isReunited ? 'active' : '' ?>"><span><i data-lucide="heart"></i></span><b>Reunited</b></div>
            </div>

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

    <div class="row g-4">
        <div class="col-lg-6">
            <span class="eyebrow"><i data-lucide="activity"></i> Activity Timeline</span>
            <h3 class="fw-black mt-3">Case Updates</h3>
            <div class="timeline-card mt-3">
                <?php if(empty($timeline)): ?>
                    <div class="timeline-item"><span><i data-lucide="clipboard-plus"></i></span><div><b>Report Created</b><p>No additional updates yet.</p></div></div>
                <?php endif; ?>
                <?php foreach($timeline as $item): ?>
                    <div class="timeline-item">
                        <span><i data-lucide="<?= htmlspecialchars($item['icon'] ?: 'activity') ?>"></i></span>
                        <div>
                            <b><?= htmlspecialchars($item['title']) ?></b>
                            <?php if(!empty($item['details'])): ?><p><?= htmlspecialchars($item['details']) ?></p><?php endif; ?>
                            <small class="text-muted"><?= htmlspecialchars($item['created_at']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-6" id="comments">
            <span class="eyebrow"><i data-lucide="message-circle"></i> Community Comments</span>
            <h3 class="fw-black mt-3">Comments</h3>

            <?php if(!empty($_SESSION['user'])): ?>
                <form method="post" action="?route=comment-store" class="comment-box mt-3">
                    <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['id']) ?>">
                    <textarea name="comment" class="form-control" rows="3" placeholder="Write a helpful update or question..." required></textarea>
                    <button class="btn btn-brand rounded-pill px-4 mt-2"><i data-lucide="send"></i> Post Comment</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning rounded-4 mt-3">Please login to comment on this report.</div>
            <?php endif; ?>

            <div class="comment-list mt-3">
                <?php foreach($comments as $c): ?>
                    <div class="comment-item">
                        <div class="comment-avatar"><?= strtoupper(substr($c['name'] ?: 'U', 0, 1)) ?></div>
                        <div>
                            <b><?= htmlspecialchars($c['name']) ?></b>
                            <p><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($c['created_at']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($comments)): ?><p class="text-muted">No comments yet. Be the first to help.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <span class="eyebrow"><i data-lucide="badge-check"></i> Case verification</span>
    <h3 class="fw-black mt-3">Request Reunited / Close Case</h3>
    <p class="text-muted">Use this if the pet was returned to the owner, claimed, or the case should be closed.</p>

    <form method="post" action="?route=close-request-store" enctype="multipart/form-data" class="row g-3 sighting-form close-request-form">
        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['id']) ?>">
        <div class="col-md-4"><input name="name" class="form-control" placeholder="Your name" required></div>
        <div class="col-md-4"><input name="contact" class="form-control" placeholder="Contact number / Messenger / Email" required></div>
        <div class="col-md-4">
            <select name="result_status" class="form-select" required>
                <option value="reunited">Reunited / Claimed</option>
                <option value="closed">Close case only</option>
            </select>
        </div>
        <div class="col-md-12"><label>Proof photo</label><input type="file" name="proof_photo" class="form-control" accept="image/*"></div>
        <div class="col-12"><textarea name="note" class="form-control" placeholder="Explain what happened." rows="3" required></textarea></div>
        <div><button class="btn btn-brand rounded-pill px-4"><i data-lucide="shield-check"></i> Submit Verification</button></div>
    </form>

    <?php if (!empty($closeRequests)): ?>
        <div class="mt-4">
            <h5 class="fw-bold">Verification Requests</h5>
            <?php foreach ($closeRequests as $cr): ?>
                <div class="sighting">
                    <div class="sighting-content">
                        <?php if (!empty($cr['proof_photo'])): ?><img src="uploads/<?= htmlspecialchars($cr['proof_photo']) ?>" alt="Proof photo" class="sighting-photo"><?php endif; ?>
                        <div>
                            <b><?= ucfirst(htmlspecialchars($cr['result_status'])) ?> request</b>
                            <span class="tag <?= htmlspecialchars($cr['request_status']) ?>"><?= htmlspecialchars($cr['request_status']) ?></span>
                            <p class="mb-0"><?= htmlspecialchars($cr['note']) ?></p>
                            <small class="text-muted">By <?= htmlspecialchars($cr['name']) ?> • <?= htmlspecialchars($cr['contact']) ?> • <?= htmlspecialchars($cr['created_at']) ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <hr class="my-5">

    <span class="eyebrow"><i data-lucide="map-plus"></i> Help update location</span>
    <h3 class="fw-black mt-3">Report a Sighting</h3>

    <form method="post" action="?route=sighting-store" enctype="multipart/form-data" class="row g-3 sighting-form">
        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['id']) ?>">
        <div class="col-md-4"><input name="name" class="form-control" placeholder="Your name" required></div>
        <div class="col-md-4"><input name="contact" class="form-control" placeholder="Contact" required></div>
        <div class="col-md-4"><input name="location" class="form-control" placeholder="Where seen" required></div>
        <div class="col-md-12"><label>Upload photo if you saw the pet</label><input type="file" name="sighting_photo" class="form-control" accept="image/*"></div>
        <div class="col-12"><textarea name="note" class="form-control" placeholder="Details" rows="3"></textarea></div>
        <div><button class="btn btn-dark rounded-pill px-4"><i data-lucide="send"></i> Submit Sighting</button></div>
    </form>

    <div class="mt-4">
        <?php foreach ($sightings as $s): ?>
            <div class="sighting"><div class="sighting-content">
                <?php if (!empty($s['photo'])): ?><img src="uploads/<?= htmlspecialchars($s['photo']) ?>" alt="Sighting photo" class="sighting-photo"><?php endif; ?>
                <div><b><i data-lucide="map-pin"></i> <?= htmlspecialchars($s['location']) ?></b><p class="mb-0"><?= htmlspecialchars($s['note']) ?></p><small class="text-muted">By <?= htmlspecialchars($s['name']) ?> • <?= htmlspecialchars($s['created_at']) ?></small></div>
            </div></div>
        <?php endforeach; ?>
    </div>
</div>

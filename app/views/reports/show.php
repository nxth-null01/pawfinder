<?php
$report = $report ?? [];
$sightings = $sightings ?? [];
$closeRequests = $closeRequests ?? [];
$comments = $comments ?? [];
$timeline = $timeline ?? [];
$similarReports = $similarReports ?? [];
$isFollowing = $isFollowing ?? false;
$trustScore = $trustScore ?? ['total' => 0, 'verified' => 0, 'score' => 0];
$commentSort = $commentSort ?? ($_GET['comment_sort'] ?? 'newest');
$approved = !empty($report['is_approved']);
$hasSighting = count($sightings) > 0;
$isReunited = ($report['status'] ?? '') === 'reunited';
$lastSeenText = 'Unknown';
if (!empty($report['last_seen_date'])) {
    $days = (new DateTime($report['last_seen_date']))->diff(new DateTime('today'))->days;
    $lastSeenText = $days == 0 ? 'Today' : $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}
$shareUrl = 'http' . (!empty($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$commentsByParent = [];
foreach ($comments as $comment) {
    $parentKey = empty($comment['parent_id']) ? 0 : (int)$comment['parent_id'];
    $commentsByParent[$parentKey][] = $comment;
}
function paw_comment_avatar($comment) {
    $photo = trim($comment['profile_photo'] ?? '');
    $name = $comment['user_name'] ?? $comment['name'] ?? 'User';
    if ($photo !== '') {
        if (!preg_match('/^https?:\/\//i', $photo) && strpos($photo, 'uploads/') !== 0) {
            $photo = 'uploads/' . $photo;
        }
        return '<img src="' . htmlspecialchars($photo) . '" alt="' . htmlspecialchars($name) . '">';
    }
    return htmlspecialchars(strtoupper(substr($name ?: 'U', 0, 1)));
}

function paw_render_comments($parentId, $commentsByParent, $reportId, $reportOwnerId, $level = 0) {
    if (empty($commentsByParent[$parentId])) return;
    foreach ($commentsByParent[$parentId] as $c):
        $indent = min($level * 18, 72);
        $displayName = $c['user_name'] ?: ($c['name'] ?? 'User');
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);
        $isAdmin = (($_SESSION['user']['role'] ?? '') === 'admin');
        $isOwner = $currentUserId && ((int)($c['user_id'] ?? 0) === $currentUserId);
        $canEdit = $isOwner || $isAdmin;
        $canDelete = $isOwner || $isAdmin;
        $canPin = $currentUserId && ((int)$reportOwnerId === $currentUserId || $isAdmin) && empty($c['is_deleted']);
        $isDeleted = !empty($c['is_deleted']);
        $isSighting = ($c['type'] ?? '') === 'sighting';
        $commentBadge = 'Community Member';
        $commentBadgeIcon = 'paw-print';
        if (($c['user_role'] ?? '') === 'admin') {
            $commentBadge = 'Moderator';
            $commentBadgeIcon = 'shield-check';
        } elseif ((int)($c['user_id'] ?? 0) === (int)$reportOwnerId) {
            $commentBadge = 'Report Owner';
            $commentBadgeIcon = 'crown';
        } elseif ((int)($c['user_verified_sightings'] ?? 0) > 0) {
            $commentBadge = 'Trusted Reporter';
            $commentBadgeIcon = 'badge-check';
        } elseif ((int)($c['user_helpful_count'] ?? 0) >= 5) {
            $commentBadge = 'Helpful Neighbor';
            $commentBadgeIcon = 'heart-handshake';
        } elseif ((int)($c['user_report_count'] ?? 0) > 0) {
            $commentBadge = 'Pet Advocate';
            $commentBadgeIcon = 'shield-check';
        }
?>
        <div class="comment-thread" style="margin-left: <?= $indent ?>px">
            <div class="comment-item improved-comment <?= !empty($c['is_pinned']) ? 'pinned-comment' : '' ?> <?= $isDeleted ? 'deleted-comment' : '' ?>">
                <div class="comment-avatar"><?= $isDeleted ? '<i data-lucide="user-x"></i>' : paw_comment_avatar($c) ?></div>
                <div class="comment-body w-100">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <b class="comment-name"><?= $isDeleted ? 'Deleted Comment' : htmlspecialchars($displayName) ?></b>
                                <?php if (!$isDeleted): ?><span class="mini-badge user-badge"><i data-lucide="<?= htmlspecialchars($commentBadgeIcon) ?>"></i> <?= htmlspecialchars($commentBadge) ?></span><?php endif; ?>
                                <?php if (!empty($c['is_pinned'])): ?><span class="mini-badge"><i data-lucide="pin"></i> Pinned by owner</span><?php endif; ?>
                                <?php if ($isSighting && !$isDeleted): ?><span class="mini-badge sighting-badge"><i data-lucide="map-pin-check"></i> Sighting Update</span><?php endif; ?>
                                <?php if (!empty($c['is_reported']) && $isAdmin): ?><span class="mini-badge report-badge"><i data-lucide="flag"></i> Reported</span><?php endif; ?>
                            </div>
                            <small class="text-muted d-block">
                                <?= htmlspecialchars($c['created_at']) ?>
                                <?php if(!$isDeleted): ?> • <?= (int)((($c['user_report_count'] ?? 0) * 10) + (($c['user_helpful_count'] ?? 0) * 5) + (($c['user_verified_sightings'] ?? 0) * 20)) ?> pts<?php endif; ?>
                                <?php if(!$isDeleted && !empty($c['edited_at'])): ?> • Edited<?php endif; ?>
                            </small>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <?php if (!$isDeleted && !empty($c['helpful_count'])): ?>
                                <small class="helpful-count">❤ <?= (int)$c['helpful_count'] ?></small>
                            <?php endif; ?>
                            <?php if (!empty($_SESSION['user']) && (!$isDeleted || $canPin)): ?>
                                <div class="dropdown comment-menu">
                                    <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <?php if (!$isDeleted && $canEdit): ?>
                                            <li><button class="dropdown-item" type="button" data-bs-toggle="collapse" data-bs-target="#edit-comment-<?= (int)$c['id'] ?>"><i data-lucide="edit-3"></i> Edit</button></li>
                                        <?php endif; ?>
                                        <?php if ($canPin): ?>
                                            <li>
                                                <form method="post" action="?route=comment-pin">
                                                    <input type="hidden" name="report_id" value="<?= htmlspecialchars($reportId) ?>">
                                                    <input type="hidden" name="comment_id" value="<?= htmlspecialchars($c['id']) ?>">
                                                    <input type="hidden" name="pin" value="<?= !empty($c['is_pinned']) ? 0 : 1 ?>">
                                                    <button class="dropdown-item" type="submit"><i data-lucide="pin"></i> <?= !empty($c['is_pinned']) ? 'Unpin' : 'Pin comment' ?></button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (!$isDeleted && !$isOwner): ?>
                                            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#report-comment-<?= (int)$c['id'] ?>"><i data-lucide="flag"></i> Report</button></li>
                                        <?php endif; ?>
                                        <?php if (!$isDeleted && $canDelete): ?>
                                            <li><button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-comment-<?= (int)$c['id'] ?>"><i data-lucide="trash-2"></i> Delete</button></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($isDeleted): ?>
                        <p class="comment-text text-muted fst-italic">This comment was deleted.</p>
                    <?php else: ?>
                        <?php if ($isSighting && !empty($c['sighting_location'])): ?>
                            <div class="sighting-chip"><i data-lucide="map-pin"></i> <?= htmlspecialchars($c['sighting_location']) ?></div>
                        <?php endif; ?>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                    <?php endif; ?>

                    <?php if (!$isDeleted && $canEdit): ?>
                        <div class="collapse mt-2" id="edit-comment-<?= (int)$c['id'] ?>">
                            <form method="post" action="?route=comment-update" class="reply-box edit-comment-box">
                                <input type="hidden" name="report_id" value="<?= htmlspecialchars($reportId) ?>">
                                <input type="hidden" name="comment_id" value="<?= htmlspecialchars($c['id']) ?>">
                                <textarea name="comment" class="form-control" rows="2" required><?= htmlspecialchars($c['comment']) ?></textarea>
                                <div class="d-flex gap-2 mt-2">
                                    <button class="btn btn-brand btn-sm rounded-pill px-3"><i data-lucide="save"></i> Save</button>
                                    <button class="btn btn-light btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#edit-comment-<?= (int)$c['id'] ?>">Cancel</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="comment-actions mt-2 d-flex gap-2 flex-wrap">
                        <?php if(!empty($_SESSION['user']) && !$isDeleted): ?>
                            <form method="post" action="?route=comment-helpful" class="d-inline">
                                <input type="hidden" name="report_id" value="<?= htmlspecialchars($reportId) ?>">
                                <input type="hidden" name="comment_id" value="<?= htmlspecialchars($c['id']) ?>">
                                <button class="btn btn-sm btn-light rounded-pill" type="submit"><i data-lucide="heart"></i> Helpful</button>
                            </form>
                        <?php endif; ?>
                        <?php if(!empty($_SESSION['user'])): ?>
                            <button class="btn btn-sm btn-light rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#reply-<?= (int)$c['id'] ?>"><i data-lucide="reply"></i> Reply</button>
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($_SESSION['user'])): ?>
                        <div class="collapse mt-2" id="reply-<?= (int)$c['id'] ?>">
                            <form method="post" action="?route=comment-store" class="reply-box">
                                <input type="hidden" name="report_id" value="<?= htmlspecialchars($reportId) ?>">
                                <input type="hidden" name="parent_id" value="<?= htmlspecialchars($c['id']) ?>">
                                <textarea name="comment" class="form-control" rows="2" placeholder="Reply to <?= htmlspecialchars($displayName) ?>..." required></textarea>
                                <button class="btn btn-brand btn-sm rounded-pill px-3 mt-2"><i data-lucide="send"></i> Reply</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$isDeleted && !$isOwner): ?>
                <div class="modal fade" id="report-comment-<?= (int)$c['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="post" action="?route=comment-report" class="modal-content confirm-modal">
                            <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i data-lucide="flag"></i> Report comment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="report_id" value="<?= htmlspecialchars($reportId) ?>">
                                <input type="hidden" name="comment_id" value="<?= htmlspecialchars($c['id']) ?>">
                                <textarea name="reason" class="form-control" rows="3" placeholder="Reason, e.g. spam, fake sighting, offensive comment..." required></textarea>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand rounded-pill">Submit Report</button></div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$isDeleted && $canDelete): ?>
                <div class="modal fade" id="delete-comment-<?= (int)$c['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="post" action="?route=comment-delete" class="modal-content confirm-modal">
                            <div class="modal-header border-0"><h5 class="modal-title fw-bold"><i data-lucide="trash-2"></i> Delete comment?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <input type="hidden" name="report_id" value="<?= htmlspecialchars($reportId) ?>">
                                <input type="hidden" name="comment_id" value="<?= htmlspecialchars($c['id']) ?>">
                                <p class="mb-0 text-muted">This will show as “This comment was deleted.” Replies will stay visible.</p>
                            </div>
                            <div class="modal-footer border-0"><button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger rounded-pill">Delete</button></div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <?php paw_render_comments((int)$c['id'], $commentsByParent, $reportId, $reportOwnerId, $level + 1); ?>
        </div>
<?php
    endforeach;
}
?>
<div class="container py-5">
    <div class="row g-4 align-items-start report-overview-grid">
        <div class="col-lg-6">
            <img class="detail-img" src="uploads/<?= htmlspecialchars($report['photo'] ?: 'placeholder.svg') ?>" alt="Animal photo">
        </div>

        <div class="col-lg-6">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="tag <?= htmlspecialchars($report['report_type']) ?>">
                    <i data-lucide="<?= $report['report_type'] === 'missing' ? 'map-pin' : 'scan-search' ?>"></i>
                    <?= ucfirst(htmlspecialchars($report['report_type'])) ?>
                </span>
                <span class="tag <?= htmlspecialchars($report['status']) ?>"><i data-lucide="activity"></i> <?= ucfirst(htmlspecialchars($report['status'])) ?></span>
                <?php if (!empty($report['reward_amount']) && $report['reward_amount'] > 0): ?>
                    <span class="tag active"><i data-lucide="gift"></i> ₱<?= number_format((float)$report['reward_amount'], 2) ?> Reward</span>
                <?php endif; ?>
            </div>

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
                <p><i data-lucide="calendar-days"></i> <b>Date:</b> <?= htmlspecialchars($report['last_seen_date']) ?> <span class="text-muted">(<?= htmlspecialchars($lastSeenText) ?>)</span></p>
                <p><i data-lucide="user-round"></i> <b>Posted by:</b> <?= htmlspecialchars($report['user_name']) ?> <?php if(($trustScore['total'] ?? 0) > 0): ?><span class="tag active ms-1">⭐ Trusted <?= (int)$trustScore['score'] ?>%</span><?php endif; ?></p>
                <p><i data-lucide="mail"></i> <b>Email:</b> <?= htmlspecialchars($report['email']) ?></p>
                <?php if (!empty($report['owner_contact'])): ?>
                    <p><i data-lucide="phone"></i> <b>Other Contact:</b> <?= htmlspecialchars($report['owner_contact']) ?></p>
                <?php endif; ?>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <?php if(!empty($_SESSION['user'])): ?>
                        <form method="post" action="?route=follow-toggle">
                            <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['id']) ?>">
                            <button class="btn btn-sm btn-brand rounded-pill px-3"><i data-lucide="bell"></i> <?= $isFollowing ? 'Following' : 'Follow Case' ?></button>
                        </form>
                    <?php endif; ?>
                    <a class="btn btn-sm btn-dark rounded-pill px-3" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"><i data-lucide="share-2"></i> Share</a>
                    <a class="btn btn-sm btn-light rounded-pill px-3" target="_blank" href="?route=report-poster&id=<?= htmlspecialchars($report['id']) ?>"><i data-lucide="file-down"></i> Generate Poster</a>
                    <button class="btn btn-sm btn-light rounded-pill px-3" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>'); this.innerText='Copied link';"><i data-lucide="copy"></i> Copy Link</button>
                </div>
            </div>

            <p class="mt-3"><?= nl2br(htmlspecialchars($report['description'])) ?></p>

            <div class="sticky-pet-card mt-4">
                <div class="sticky-pet-card-top">
                    <img src="uploads/<?= htmlspecialchars($report['photo'] ?: 'placeholder.svg') ?>" alt="<?= htmlspecialchars($report['animal_name'] ?: 'Pet') ?>">
                    <div>
                        <small class="text-muted">Quick case info</small>
                        <h5><?= htmlspecialchars($report['animal_name'] ?: 'Unknown Pet') ?></h5>
                    </div>
                </div>
                <div class="sticky-pet-grid">
                    <div><span>Status</span><b><?= ucfirst(htmlspecialchars($report['status'])) ?></b></div>
                    <div><span>Type</span><b><?= ucfirst(htmlspecialchars($report['report_type'])) ?></b></div>
                    <div><span>Last seen</span><b><?= htmlspecialchars($lastSeenText) ?></b></div>
                    <div><span>Reward</span><b><?= !empty($report['reward_amount']) ? '₱' . number_format((float)$report['reward_amount'], 0) : 'None' ?></b></div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-3">
                    <a class="btn btn-sm btn-brand rounded-pill px-3" href="#comments"><i data-lucide="message-circle"></i> Help Case</a>
                    <a class="btn btn-sm btn-light rounded-pill px-3" target="_blank" href="?route=report-poster&id=<?= htmlspecialchars($report['id']) ?>"><i data-lucide="file-down"></i> Poster</a>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <div class="row g-4">
        <div class="col-lg-6">
            <span class="eyebrow"><i data-lucide="activity"></i> Activity Timeline</span>
            <h3 class="fw-black mt-3">Case Updates</h3>
            <div class="timeline-card mt-3">
                <?php if(empty($timeline)): ?>
                    <div class="empty-state compact-empty">
                        <div class="empty-state-icon"><i data-lucide="activity"></i></div>
                        <h5>No major updates yet</h5>
                        <p>Meaningful updates such as new sightings, verified sightings, reunited requests, and closed cases will appear here.</p>
                    </div>
                <?php endif; ?>
                <?php foreach($timeline as $item): ?>
                    <div class="timeline-item">
                        <span><i data-lucide="<?= htmlspecialchars($item['icon'] ?: 'activity') ?>"></i></span>
                        <div>
                            <b><?= htmlspecialchars(str_replace('Sighting Added', 'Sighting Reported', $item['title'])) ?></b>
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
                    <input type="hidden" name="comment_type" value="comment">
                    <textarea name="comment" class="form-control" rows="3" placeholder="Write a helpful update or question..." required></textarea>
                    <button class="btn btn-brand rounded-pill px-4 mt-2"><i data-lucide="send"></i> Post Comment</button>

                    <div class="quick-case-actions mt-3">
                        <button type="button" class="quick-case-btn sighting-action" data-bs-toggle="modal" data-bs-target="#sightingModal">
                            <i data-lucide="eye"></i>
                            <span>Report Sighting</span>
                        </button>
                        <button type="button" class="quick-case-btn reunited-action" data-bs-toggle="modal" data-bs-target="#reunitedModal">
                            <i data-lucide="heart"></i>
                            <span>Request Reunited</span>
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning rounded-4 mt-3">Please login to comment, report a sighting, or request reunited/closed case.</div>
            <?php endif; ?>

            <div class="comment-sort-bar mt-3">
                <span>Sort by:</span>
                <a class="<?= $commentSort === 'newest' ? 'active' : '' ?>" href="?route=report-show&id=<?= htmlspecialchars($report['id']) ?>&comment_sort=newest#comments">Newest</a>
                <a class="<?= $commentSort === 'oldest' ? 'active' : '' ?>" href="?route=report-show&id=<?= htmlspecialchars($report['id']) ?>&comment_sort=oldest#comments">Oldest</a>
                <a class="<?= $commentSort === 'helpful' ? 'active' : '' ?>" href="?route=report-show&id=<?= htmlspecialchars($report['id']) ?>&comment_sort=helpful#comments">Most Helpful</a>
            </div>

            <div class="comment-list mt-3">
                <?php paw_render_comments(0, $commentsByParent, $report['id'], $report['user_id']); ?>
                <?php if(empty($comments)): ?>
                    <div class="empty-state compact-empty comment-empty-state">
                        <div class="empty-state-icon"><i data-lucide="message-circle-heart"></i></div>
                        <h5>Be the first to help this case</h5>
                        <p>Ask a question, share a useful detail, or report a possible sighting.</p>
                        <?php if(!empty($_SESSION['user'])): ?>
                            <button type="button" class="btn btn-sm btn-brand rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#sightingModal"><i data-lucide="eye"></i> Report Sighting</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(!empty($_SESSION['user'])): ?>
        <div class="modal fade" id="sightingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="post" action="?route=sighting-store" enctype="multipart/form-data" class="modal-content case-action-modal">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <span class="eyebrow"><i data-lucide="eye"></i> Help update location</span>
                            <h5 class="modal-title fw-black mt-2">Report a Sighting</h5>
                            <p class="text-muted mb-0">Use this if you saw the pet or have a possible location update.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['id']) ?>">
                        <div class="row g-3">
                            <div class="col-md-4"><input name="name" class="form-control" placeholder="Your name" required></div>
                            <div class="col-md-4"><input name="contact" class="form-control" placeholder="Contact" required></div>
                            <div class="col-md-4"><input name="location" class="form-control" placeholder="Where seen" required></div>
                            <div class="col-12"><label class="form-label fw-bold">Upload photo if you saw the pet</label><input type="file" name="sighting_photo" class="form-control" accept="image/*"></div>
                            <div class="col-12"><textarea name="note" class="form-control" placeholder="Details" rows="3"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-dark rounded-pill px-4"><i data-lucide="send"></i> Submit Sighting</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="reunitedModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="post" action="?route=close-request-store" enctype="multipart/form-data" class="modal-content case-action-modal">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <span class="eyebrow"><i data-lucide="badge-check"></i> Case verification</span>
                            <h5 class="modal-title fw-black mt-2">Request Reunited / Close Case</h5>
                            <p class="text-muted mb-0">Use this if the pet was returned, claimed, or the case should be closed.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="report_id" value="<?= htmlspecialchars($report['id']) ?>">
                        <div class="row g-3">
                            <div class="col-md-4"><input name="name" class="form-control" placeholder="Your name" required></div>
                            <div class="col-md-4"><input name="contact" class="form-control" placeholder="Contact number / Messenger / Email" required></div>
                            <div class="col-md-4"><select name="result_status" class="form-select" required><option value="reunited">Reunited / Claimed</option><option value="closed">Close case only</option></select></div>
                            <div class="col-12"><label class="form-label fw-bold">Proof photo</label><input type="file" name="proof_photo" class="form-control" accept="image/*"></div>
                            <div class="col-12"><textarea name="note" class="form-control" placeholder="Explain what happened." rows="3" required></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-brand rounded-pill px-4"><i data-lucide="shield-check"></i> Submit Verification</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($similarReports)): ?>
        <hr class="my-5">
        <span class="eyebrow"><i data-lucide="scan-search"></i> Similar Reports</span>
        <h3 class="fw-black mt-3">Related Reports Nearby / Similar Pet</h3>
        <div class="row g-3 mt-1">
            <?php foreach($similarReports as $r): ?>
                <div class="col-md-4">
                    <div class="comment-item h-100">
                        <div class="comment-avatar"><i data-lucide="paw-print"></i></div>
                        <div><b><?= htmlspecialchars($r['animal_name'] ?: 'Unknown') ?></b><p><?= htmlspecialchars($r['species'] . ' • ' . $r['color']) ?></p><small class="text-muted"><?= htmlspecialchars($r['location']) ?></small><br><a class="btn btn-sm btn-dark rounded-pill mt-2" href="?route=report-show&id=<?= htmlspecialchars($r['id']) ?>">View</a></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

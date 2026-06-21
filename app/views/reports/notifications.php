<?php $notifications = $notifications ?? []; ?>
<div class="container py-5">
  <div class="section-head mb-4">
    <div>
      <span class="eyebrow"><i data-lucide="bell"></i> Notifications</span>
      <h1 class="fw-black mt-3">Your Alerts</h1>
      <p class="text-muted">Updates from comments, sightings, admin actions, and case verification.</p>
    </div>
    <a href="?route=notifications-read" class="btn btn-brand rounded-pill px-4"><i data-lucide="bell-check"></i> Mark all as read</a>
  </div>

  <div class="notification-page-list">
    <?php foreach($notifications as $n): ?>
      <a class="notification-row <?= !$n['is_read'] ? 'unread' : '' ?>" href="<?= !empty($n['report_id']) ? '?route=report-show&id=' . htmlspecialchars($n['report_id']) : '#' ?>">
        <div class="notification-dot"><i data-lucide="bell"></i></div>
        <div>
          <b><?= htmlspecialchars($n['title']) ?></b>
          <p><?= htmlspecialchars($n['message']) ?></p>
          <small><?= htmlspecialchars($n['created_at']) ?></small>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if(empty($notifications)): ?>
      <div class="empty-state"><i data-lucide="bell-off"></i><h3>No notifications yet</h3><p>Updates will appear here when someone interacts with your reports.</p></div>
    <?php endif; ?>
  </div>
</div>

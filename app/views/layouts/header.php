<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PawJect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="icon" type="image/svg+xml" href="assets/images/pawject.svg">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top glass-nav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="?route=home">
      <span class="brand-mark"><i data-lucide="paw-print"></i></span>
      <span>PawJect</span>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li><a class="nav-link" href="?route=about"><i data-lucide="info"></i> About</a></li>
        <li><a class="nav-link" href="?route=reports"><i data-lucide="search"></i> Lost & Found</a></li>
        <li><a class="nav-link" href="?route=report-create"><i data-lucide="circle-plus"></i> Report</a></li>

        <?php if(!empty($_SESSION['user'])): ?>
          <?php
            $unread = Report::unreadNotifications($_SESSION['user']['id']);
            $latestNotifications = Report::notifications($_SESSION['user']['id'], 5);
            $currentUserName = $_SESSION['user']['name'] ?? 'User';
            $currentUserPhoto = $_SESSION['user']['profile_photo'] ?? '';
            $firstName = strtok($currentUserName, ' ') ?: 'User';
            $initial = strtoupper(substr($currentUserName, 0, 1));
            $navReputation = Report::userReputation($_SESSION['user']['id']);
          ?>

          <li class="nav-item dropdown notif-nav-item">
            <a class="nav-link notification-bell dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
              <i data-lucide="bell"></i>
              <?php if($unread > 0): ?><span class="notif-badge bell-badge"><?= $unread ?></span><?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end notif-dropdown">
              <div class="notif-dropdown-head">
                <strong>Notifications</strong>
                <a href="?route=notifications-read">Mark all read</a>
              </div>

              <?php if(!empty($latestNotifications)): ?>
                <?php foreach($latestNotifications as $n): ?>
                  <a class="notif-preview <?= empty($n['is_read']) ? 'unread' : '' ?>" href="<?= !empty($n['report_id']) ? '?route=report-show&id=' . (int)$n['report_id'] : '?route=notifications' ?>">
                    <span><i data-lucide="bell-ring"></i></span>
                    <div>
                      <b><?= htmlspecialchars($n['title']) ?></b>
                      <small><?= htmlspecialchars($n['message']) ?></small>
                      <em><?= htmlspecialchars($n['created_at'] ?? '') ?></em>
                    </div>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="notif-empty-mini">
                  <i data-lucide="bell-off"></i>
                  <span>No notifications yet</span>
                </div>
              <?php endif; ?>

              <a class="notif-view-all" href="?route=notifications">View all notifications</a>
            </div>
          </li>

          <li class="nav-item dropdown">
            <a class="profile-nav dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="profile-nav-avatar">
                <?php if(!empty($currentUserPhoto)): ?>
                  <img src="<?= htmlspecialchars($currentUserPhoto) ?>" alt="<?= htmlspecialchars($firstName) ?>">
                <?php else: ?>
                  <?= htmlspecialchars($initial) ?>
                <?php endif; ?>
              </span>
              <span><?= htmlspecialchars($firstName) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end profile-dropdown">
              <li class="profile-dropdown-summary">
                <strong><?= htmlspecialchars($currentUserName) ?></strong>
                <small><i data-lucide="<?= htmlspecialchars($navReputation['icon'] ?? 'paw-print') ?>"></i> <?= htmlspecialchars($navReputation['badge'] ?? 'Community Member') ?> · <?= (int)($navReputation['points'] ?? 0) ?> pts</small>
              </li>
              <li><a class="dropdown-item" href="?route=profile"><i data-lucide="settings"></i> Profile Settings</a></li>
              <li><a class="dropdown-item" href="?route=dashboard"><i data-lucide="clipboard-list"></i> My Reports</a></li>
              <li><a class="dropdown-item" href="?route=notifications"><i data-lucide="bell"></i> Notifications</a></li>
              <li><a class="dropdown-item" href="?route=success-stories"><i data-lucide="heart"></i> Success Stories</a></li>
              <?php if($_SESSION['user']['role']==='admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?route=admin"><i data-lucide="shield-check"></i> Admin Panel</a></li>
                <li><a class="dropdown-item" href="?route=admin-analytics"><i data-lucide="bar-chart-3"></i> Analytics</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="?route=logout"><i data-lucide="log-out"></i> Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li><a class="btn btn-brand rounded-pill px-4" href="?route=login"><i data-lucide="user-round"></i> Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php if (!empty($_SESSION['flash'])): ?>
  <?php
    $flash = is_array($_SESSION['flash'])
      ? $_SESSION['flash']
      : ['type' => 'info', 'icon' => 'bell', 'title' => 'Notice', 'message' => $_SESSION['flash']];

    $flashType = htmlspecialchars($flash['type'] ?? 'info');
    $flashIcon = htmlspecialchars($flash['icon'] ?? 'bell');
    $flashTitle = htmlspecialchars($flash['title'] ?? 'Notice');
    $flashMessage = htmlspecialchars($flash['message'] ?? '');
    unset($_SESSION['flash']);
  ?>
  <div class="toast-wrap">
    <div class="app-toast <?= $flashType ?>" role="alert">
      <span class="toast-icon"><i data-lucide="<?= $flashIcon ?>"></i></span>
      <span>
        <strong><?= $flashTitle ?></strong>
        <small><?= $flashMessage ?></small>
      </span>
      <button class="toast-close" type="button" aria-label="Close notification">
        <i data-lucide="x"></i>
      </button>
    </div>
  </div>
<?php endif; ?>

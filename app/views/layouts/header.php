<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PawFinder</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top glass-nav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="?route=home">
      <span class="brand-mark"><i data-lucide="paw-print"></i></span>
      <span>PawFinder</span>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li><a class="nav-link" href="?route=reports"><i data-lucide="search"></i> Lost & Found</a></li>
        <li><a class="nav-link" href="?route=report-create"><i data-lucide="circle-plus"></i> Report</a></li>
        <?php if(!empty($_SESSION['user'])): ?>
          <li><a class="nav-link" href="?route=dashboard"><i data-lucide="layout-dashboard"></i> Dashboard</a></li>
          <?php if($_SESSION['user']['role']==='admin'): ?><li><a class="nav-link" href="?route=admin"><i data-lucide="shield-check"></i> Admin</a></li><?php endif; ?>
          <li><a class="btn btn-dark rounded-pill px-3" href="?route=logout"><i data-lucide="log-out"></i> Logout</a></li>
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

<?php
$user = $user ?? ($_SESSION['user'] ?? []);
$initials = '';
if (!empty($user['name'])) {
  $parts = preg_split('/\s+/', trim($user['name']));
  $initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[count($parts)-1] ?? '', 0, 1));
}
$initials = $initials ?: 'U';
$profilePhoto = $user['profile_photo'] ?? '';
$avatarHtml = !empty($profilePhoto)
  ? '<img src="' . htmlspecialchars($profilePhoto) . '" alt="' . htmlspecialchars($user['name'] ?? 'User') . '">'
  : htmlspecialchars($initials);
?>

<section class="profile-hero about-paw-bg">
  <div class="container">
    <div class="profile-cover">
      <span class="profile-floating-paw paw-one">🐾</span>
      <span class="profile-floating-paw paw-two">🐾</span>

      <div class="profile-avatar-big">
        <?= $avatarHtml ?>
      </div>

      <div>
        <span class="eyebrow"><i data-lucide="settings"></i> Profile Settings</span>
        <h1 class="fw-black mt-2 mb-1"><?= htmlspecialchars($user['name'] ?? 'User') ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($user['email'] ?? '') ?></p>
      </div>
    </div>
  </div>
</section>

<section class="container profile-settings-section">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="profile-side-card">
        <div class="profile-avatar-medium"><?= $avatarHtml ?></div>
        <h3 class="fw-black mb-1"><?= htmlspecialchars($user['name'] ?? 'User') ?></h3>
        <p class="text-muted mb-3"><?= htmlspecialchars($user['role'] ?? 'user') ?> account</p>

        <div class="profile-menu">
          <a href="#account-info" data-profile-tab="account-info" class="active"><i data-lucide="user-round"></i> Account Information</a>
          <a href="#password-settings" data-profile-tab="password-settings"><i data-lucide="lock-keyhole"></i> Change Password</a>
          <a href="?route=dashboard"><i data-lucide="clipboard-list"></i> My Reports</a>
          <a href="?route=notifications"><i data-lucide="bell"></i> Notifications</a>
          <a href="?route=logout" class="danger"><i data-lucide="log-out"></i> Logout</a>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div id="account-info" class="settings-card">
        <div class="settings-card-head">
          <div>
            <span class="section-kicker">Account</span>
            <h2 class="fw-black mb-0">Edit Profile</h2>
          </div>
          <span class="settings-icon"><i data-lucide="badge-check"></i></span>
        </div>

        <form action="?route=profile-update" method="POST" enctype="multipart/form-data" class="row g-3 mt-3 confirm-before-submit" data-confirm-title="Save profile changes?" data-confirm-message="Your name and email will be updated." data-confirm-button="Save Changes">

          <div class="col-12">
            <label class="form-label">Profile Photo</label>
            <div class="profile-photo-uploader">
              <div class="profile-photo-preview"><?= $avatarHtml ?></div>
              <div class="flex-grow-1">
                <input type="file" name="profile_photo" class="form-control" accept="image/png,image/jpeg,image/webp">
                <small class="text-muted">Optional. Upload JPG, PNG, or WEBP up to 3MB.</small>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <div class="input-icon">
              <i data-lucide="user-round"></i>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Email Address</label>
            <div class="input-icon">
              <i data-lucide="mail"></i>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-brand rounded-pill px-4">
              <i data-lucide="save"></i> Save Changes
            </button>
          </div>
        </form>
      </div>

      <div id="password-settings" class="settings-card mt-4">
        <div class="settings-card-head">
          <div>
            <span class="section-kicker">Security</span>
            <h2 class="fw-black mb-0">Change Password</h2>
          </div>
          <span class="settings-icon"><i data-lucide="shield-check"></i></span>
        </div>

        <form action="?route=password-update" method="POST" class="row g-3 mt-3 confirm-before-submit" data-confirm-title="Update password?" data-confirm-message="Make sure your new password is correct before continuing." data-confirm-button="Update Password">
          <div class="col-12">
            <label class="form-label">Current Password</label>
            <div class="input-icon password-field">
              <i data-lucide="key-round"></i>
              <input type="password" name="current_password" class="form-control" required>
              <button type="button" class="toggle-password" aria-label="Show password"><i data-lucide="eye"></i></button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">New Password</label>
            <div class="input-icon password-field">
              <i data-lucide="lock"></i>
              <input type="password" name="new_password" class="form-control" minlength="6" required>
              <button type="button" class="toggle-password" aria-label="Show password"><i data-lucide="eye"></i></button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Confirm New Password</label>
            <div class="input-icon password-field">
              <i data-lucide="lock-keyhole"></i>
              <input type="password" name="confirm_password" class="form-control" minlength="6" required>
              <button type="button" class="toggle-password" aria-label="Show password"><i data-lucide="eye"></i></button>
            </div>
          </div>

          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-dark rounded-pill px-4">
              <i data-lucide="shield-check"></i> Update Password
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>


<div class="modal fade" id="confirmActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content confirm-modal">
      <div class="modal-header border-0 pb-0">
        <div>
          <span class="section-kicker">Confirmation</span>
          <h5 class="modal-title fw-black" id="confirmActionTitle">Are you sure?</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted mb-0" id="confirmActionMessage">Please confirm this action.</p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-brand rounded-pill px-4" id="confirmActionButton">Confirm</button>
      </div>
    </div>
  </div>
</div>

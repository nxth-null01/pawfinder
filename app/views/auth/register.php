<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-icon"><i data-lucide="paw-print"></i></div>
        <h1 class="fw-black">Create account</h1>
        <p class="text-muted">Join the PawFinder community.</p>

        <form method="post" action="?route=register-post">
            <input name="name" class="form-control mb-3" placeholder="Full name" required>
            <input name="email" type="email" class="form-control mb-3" placeholder="Email" required>

            <div class="password-field mb-3">
                <input name="password" type="password" class="form-control" placeholder="Password" required>
                <button type="button" class="password-toggle" aria-label="Show password">
                    <i data-lucide="eye"></i>
                </button>
            </div>

            <button class="btn btn-brand w-100 rounded-pill py-3">
                <i data-lucide="user-plus"></i> Register
            </button>
        </form>
    </div>
</div>

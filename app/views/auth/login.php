<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-icon"><i data-lucide="user-round"></i></div>
        <h1 class="fw-black">Welcome back</h1>
        <p class="text-muted">Login to manage your animal reports.</p>

        <form method="post" action="?route=login-post">
            <input name="email" type="email" class="form-control mb-3" placeholder="Email" required>

            <div class="password-field mb-3">
                <input name="password" type="password" class="form-control" placeholder="Password" required>
                <button type="button" class="password-toggle" aria-label="Show password">
                    <i data-lucide="eye"></i>
                </button>
            </div>

            <button class="btn btn-brand w-100 rounded-pill py-3">
                <i data-lucide="log-in"></i> Login
            </button>
        </form>

        <p class="mt-3 mb-0">No account? <a href="?route=register">Create one</a></p>
    </div>
</div>

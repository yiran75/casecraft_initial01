<div class="col-12 col-md-8 col-lg-6">
    <div class="auth-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="background-color: rgba(13, 148, 136, 0.15); color: #14b8a6; font-size: 2rem;">
                <i class="bi bi-person-plus"></i>
            </div>
            <h3 class="fw-bold mb-1 text-white">Create Account</h3>
            <p class="text-secondary small">CaseCraft Personnel Registration</p>
        </div>

        <?php if (!empty($flashes['error'])): ?>
            <div class="alert alert-danger d-flex align-items-center py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= e($flashes['error']) ?></div>
            </div>
        <?php endif; ?>

        <form action="/signup" method="POST" autocomplete="off">
            <?= \App\Helpers\Csrf::field() ?>

            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="Officer / Specialist Name" value="<?= e($_POST['full_name'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="user@casecraft.local" value="<?= e($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password (min 8 chars)</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="8" placeholder="••••••••">
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="••••••••">
                </div>
            </div>

            <div class="mb-4">
                <label for="role" class="form-label">Requested Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="investigator" <?= (($_POST['role'] ?? '') === 'investigator') ? 'selected' : '' ?>>Investigator (Case Management, Evidence, Notes)</option>
                    <option value="reviewer" <?= (($_POST['role'] ?? '') === 'reviewer') ? 'selected' : '' ?>>Read-only Reviewer (Case Inspection & Reports)</option>
                </select>
                <div class="form-text text-secondary" style="font-size: 0.8rem;">
                    * Administrator roles can only be granted by existing administrators.
                </div>
            </div>

            <button type="submit" class="btn btn-teal w-100 py-2 mb-3">
                <i class="bi bi-person-check me-1"></i> Register Account
            </button>

            <div class="text-center text-secondary small">
                Already registered? <a href="/login" class="text-teal text-decoration-none" style="color: #14b8a6;">Sign in here</a>
            </div>
        </form>
    </div>
</div>

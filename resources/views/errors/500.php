<div class="col-12 col-md-8 col-lg-6 text-center">
    <div class="auth-card p-4 p-md-5">
        <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3 text-danger" style="background-color: rgba(239, 68, 68, 0.15); font-size: 2.5rem;">
            <i class="bi bi-exclamation-octagon"></i>
        </div>
        <h2 class="fw-bold text-white mb-2"><?= e($title ?? '500 - Server Error') ?></h2>
        <p class="text-secondary mb-4"><?= e($message ?? 'An unexpected error occurred while processing the request.') ?></p>
        
        <?php if (!empty($exception) && ($appConfig['debug'] ?? false)): ?>
            <div class="text-start bg-dark p-3 rounded mb-4 overflow-auto" style="max-height: 180px; font-size: 0.8rem; color: #fca5a5;">
                <strong>Debug Info:</strong> <?= e($exception->getMessage()) ?><br>
                <small class="text-secondary"><?= e($exception->getFile()) ?>:<?= $exception->getLine() ?></small>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-center gap-2">
            <a href="/dashboard" class="btn btn-teal"><i class="bi bi-arrow-clockwise me-1"></i> Return to Safety</a>
            <a href="/" class="btn btn-outline-secondary"><i class="bi bi-house me-1"></i> Home</a>
        </div>
    </div>
</div>

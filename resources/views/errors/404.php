<div class="col-12 col-md-8 col-lg-5 text-center">
    <div class="auth-card p-4 p-md-5">
        <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3 text-info" style="background-color: rgba(14, 165, 233, 0.15); font-size: 2.5rem;">
            <i class="bi bi-compass"></i>
        </div>
        <h2 class="fw-bold text-white mb-2"><?= e($title ?? '404 - Not Found') ?></h2>
        <p class="text-secondary mb-4"><?= e($message ?? 'The requested case file or page was not found on this system.') ?></p>
        <div class="d-flex justify-content-center gap-2">
            <a href="/dashboard" class="btn btn-teal"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
            <a href="/" class="btn btn-outline-secondary"><i class="bi bi-house me-1"></i> Home</a>
        </div>
    </div>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(\App\Helpers\Csrf::getToken()) ?>">
    <title><?= e($title ?? 'CaseCraft - Cyber Forensics') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Cyberpunk Forensics Theme Stylesheet -->
    <link rel="stylesheet" href="/assets/css/cyber-theme.css">
    <!-- Google Identity Services (GIS) Official Client SDK -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <!-- Top Academic Demo Banner -->
    <header class="py-2 text-center" style="background-color: rgba(3, 7, 18, 0.95); border-bottom: 1px solid rgba(0, 245, 212, 0.2);">
        <div class="d-flex align-items-center justify-content-center gap-2">
            <span class="badge border border-info text-info small px-3 py-1" style="background: rgba(0, 245, 212, 0.1);">
                <i class="bi bi-shield-lock me-1"></i> CID FORENSIC INVESTIGATION DOCKET // RESTRICTED ACCESS
            </span>
            <span class="badge border border-info text-info small d-none d-md-inline-block" style="background: rgba(0, 245, 212, 0.1);">
                <i class="bi bi-cpu me-1"></i> QUANTUM ENCRYPTED NODE // 4096-BIT TLS
            </span>
        </div>
    </header>

    <main class="container py-4 flex-grow-1 d-flex align-items-center justify-content-center">
        <?= $content ?>
    </main>

    <footer class="py-3 text-center text-secondary border-top" style="font-size: 0.85rem; border-color: rgba(0, 245, 212, 0.15) !important; background: rgba(3, 7, 18, 0.9);">
        &copy; <?= date('Y') ?> CaseCraft &bull; Cyber Investigation & Forensic Intelligence Command.
    </footer>

    <!-- Audio FX Pill Toggle -->
    <div class="audio-toggle-pill" id="audioToggleBtn" title="Toggle Tactical Cyber SFX">
        <i class="bi bi-volume-up me-1" id="audioIcon"></i> <span id="audioStatusText">SFX: ON</span>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cyber Audio & Biometrics Scripts -->
    <script src="/assets/js/cyber-audio.js"></script>
    <script src="/assets/js/biometrics.js"></script>

    <script>
    const audioBtn = document.getElementById('audioToggleBtn');
    const audioIcon = document.getElementById('audioIcon');
    const audioStatusText = document.getElementById('audioStatusText');

    if (audioBtn) {
        audioBtn.addEventListener('click', () => {
            const isMuted = window.cyberSound.toggleMute();
            if (isMuted) {
                audioIcon.className = 'bi bi-volume-mute me-1';
                audioStatusText.textContent = 'SFX: OFF';
                audioBtn.style.opacity = '0.5';
            } else {
                audioIcon.className = 'bi bi-volume-up me-1';
                audioStatusText.textContent = 'SFX: ON';
                audioBtn.style.opacity = '1';
                window.cyberSound.click();
            }
        });
    }
    </script>
</body>
</html>

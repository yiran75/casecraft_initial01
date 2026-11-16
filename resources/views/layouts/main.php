<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'CaseCraft - Forensic Intelligence') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- Cyber Theme Stylesheet -->
    <link rel="stylesheet" href="/assets/css/cyber-theme.css">
    <style>
        #sidebar {
            min-width: 270px;
            max-width: 270px;
            background: rgba(3, 7, 18, 0.95);
            border-right: 1px solid var(--cyber-border);
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(16px);
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: rgba(7, 13, 30, 0.9);
            border-bottom: 1px solid var(--cyber-border);
        }
        #sidebar ul.components {
            padding: 15px 0;
            list-style: none;
            margin: 0;
        }
        #sidebar ul li a {
            padding: 12px 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-family: var(--font-mono);
        }
        #sidebar ul li a:hover, #sidebar ul li.active a {
            color: #00f5d4;
            background: rgba(0, 245, 212, 0.08);
            border-left-color: #00f5d4;
            text-shadow: 0 0 10px rgba(0, 245, 212, 0.5);
        }
        #sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.15rem;
        }
        #content {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .top-navbar {
            background-color: rgba(7, 13, 30, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--cyber-border);
            padding: 12px 24px;
        }
        .academic-banner {
            background-color: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
            border-bottom: 1px solid rgba(245, 158, 11, 0.25);
            font-size: 0.8rem;
            padding: 5px 16px;
            text-align: center;
            font-family: var(--font-mono);
        }
        .badge-role {
            font-size: 0.72rem;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            font-family: var(--font-mono);
            letter-spacing: 0.5px;
        }
        .role-admin { background-color: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5; }
        .role-investigator { background-color: rgba(0, 245, 212, 0.15); border: 1px solid #00f5d4; color: #00f5d4; }
        .role-reviewer { background-color: rgba(59, 130, 246, 0.2); border: 1px solid #3b82f6; color: #93c5fd; }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: -270px;
                position: fixed;
                top: 0; bottom: 0;
                z-index: 1040;
            }
            #sidebar.active { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Academic Demo Disclaimer -->
    <div class="academic-banner d-flex align-items-center justify-content-between px-3">
        <div>
            <i class="bi bi-shield-exclamation me-1 text-warning"></i> <?= e($appConfig['academic_notice'] ?? 'Academic Demo – Fictional Data Only. Not an official law-enforcement system.') ?>
        </div>
        <div class="d-none d-md-block text-cyber-glow">
            <span class="spinner-grow spinner-grow-sm text-info me-1" style="width: 8px; height: 8px;"></span> SECURE HUD TERMINAL
        </div>
    </div>

    <div class="wrapper d-flex">
        <!-- Sidebar Navigation -->
        <nav id="sidebar">
            <div class="sidebar-header d-flex align-items-center justify-content-between">
                <a href="/dashboard" class="text-white text-decoration-none fw-bold fs-5 d-flex align-items-center">
                    <i class="bi bi-shield-shaded me-2 text-cyber-glow"></i> CaseCraft <span class="badge border border-info text-info ms-2" style="font-size: 0.65rem;">HUD</span>
                </a>
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarCollapseClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <ul class="components flex-grow-1">
                <li>
                    <a href="/dashboard"><i class="bi bi-speedometer2"></i> Command Center</a>
                </li>
                <li>
                    <a href="/cases"><i class="bi bi-briefcase"></i> Case Registry</a>
                </li>
                <?php if ($currentUser['role'] !== 'reviewer'): ?>
                <li>
                    <a href="/cases/create"><i class="bi bi-plus-circle"></i> Open Inquiry</a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="/reports"><i class="bi bi-file-earmark-bar-graph"></i> Case Dossiers</a>
                </li>

                <?php if ($currentUser['role'] === 'admin'): ?>
                <li class="mt-3 px-3 pb-1 text-uppercase text-secondary font-monospace" style="font-size: 0.7rem; letter-spacing: 1px;">
                    Administration
                </li>
                <li>
                    <a href="/admin/users"><i class="bi bi-people"></i> Personnel Matrix</a>
                </li>
                <li>
                    <a href="/admin/logs"><i class="bi bi-shield-check"></i> Security Audit Log</a>
                </li>
                <?php endif; ?>

                <li class="mt-3 px-3 pb-1 text-uppercase text-secondary font-monospace" style="font-size: 0.7rem; letter-spacing: 1px;">
                    Security
                </li>
                <li>
                    <a href="/profile"><i class="bi bi-person-gear"></i> Agent Profile</a>
                </li>
            </ul>

            <!-- Officer Active Status HUD Footer -->
            <div class="p-3 border-top" style="border-color: var(--cyber-border) !important; background: rgba(3, 7, 18, 0.9);">
                <div class="d-flex align-items-center mb-2">
                    <?php if (!empty($currentUser['avatar_url'])): ?>
                        <img src="<?= e($currentUser['avatar_url']) ?>" alt="Officer Avatar" class="rounded-circle me-2 border border-info" style="width: 38px; height: 38px; object-fit: cover; box-shadow: 0 0 10px rgba(0, 245, 212, 0.5);">
                    <?php else: ?>
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-dark me-2 font-monospace fw-bold" style="width: 38px; height: 38px; background: #00f5d4; box-shadow: 0 0 10px #00f5d4;">
                            <?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-truncate small text-white"><?= e($currentUser['full_name'] ?? 'User') ?></div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge-role role-<?= e($currentUser['role'] ?? 'investigator') ?>"><?= e($currentUser['role'] ?? 'Staff') ?></span>
                            <?php if (!empty($currentUser['auth_method'])): ?>
                                <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 0.6rem;">
                                    <?= e($currentUser['auth_method']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <a href="/logout" class="btn btn-sm btn-outline-danger w-100 mt-2 font-monospace">
                    <i class="bi bi-power me-1"></i> Terminate Session
                </a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="top-navbar d-flex align-items-center justify-content-between">
                <button type="button" id="sidebarCollapse" class="btn btn-outline-cyber d-lg-none">
                    <i class="bi bi-list"></i>
                </button>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge border border-success text-success small font-monospace d-none d-sm-inline-flex align-items-center">
                        <span class="spinner-grow spinner-grow-sm me-1" style="width: 6px; height: 6px;"></span>
                        FIREWALL: ACTIVE // ZERO BREACH
                    </span>
                    <span class="text-secondary small font-monospace d-none d-md-inline-block">
                        IP: <span class="text-white"><?= e($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></span>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-secondary small font-monospace">
                        Signed: <strong class="text-cyber-glow"><?= e($currentUser['email'] ?? '') ?></strong>
                    </span>
                    <a href="/logout" class="btn btn-outline-danger btn-sm py-1 px-2" title="Sign Out">
                        <i class="bi bi-power"></i>
                    </a>
                </div>
            </nav>

            <!-- Alerts & Main Views -->
            <div class="container-fluid p-4 flex-grow-1">
                <?php if (!empty($flashes['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2" role="alert" style="background: rgba(0, 245, 212, 0.1); border: 1px solid rgba(0, 245, 212, 0.4); color: #00f5d4;">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <div class="small font-monospace"><?= e($flashes['success']) ?></div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($flashes['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2" role="alert" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5;">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <div class="small font-monospace"><?= e($flashes['error']) ?></div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>

            <footer class="p-3 text-center text-secondary small border-top font-monospace" style="border-color: var(--cyber-border) !important; background: rgba(3, 7, 18, 0.95);">
                &copy; <?= date('Y') ?> CaseCraft &bull; Cyber-Intelligence & Forensic Investigation Dossier Platform.
            </footer>
        </div>
    </div>

    <!-- Audio FX Pill Toggle -->
    <div class="audio-toggle-pill" id="audioToggleBtn" title="Toggle Tactical Cyber SFX">
        <i class="bi bi-volume-up me-1" id="audioIcon"></i> <span id="audioStatusText">SFX: ON</span>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cyber Audio Script -->
    <script src="/assets/js/cyber-audio.js"></script>

    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        const sidebarCollapseClose = document.getElementById('sidebarCollapseClose');

        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
        if (sidebarCollapseClose) {
            sidebarCollapseClose.addEventListener('click', () => {
                sidebar.classList.remove('active');
            });
        }

        // SFX Audio button listener
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

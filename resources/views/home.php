<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --navy-primary: #0f172a;
            --navy-secondary: #1e293b;
            --navy-card: #1e293b;
            --teal-accent: #0d9488;
            --teal-light: #14b8a6;
            --teal-hover: #0f766e;
            --slate-muted: #94a3b8;
        }
        body {
            background-color: var(--navy-primary);
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            overflow-x: hidden;
        }
        .navbar {
            background-color: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #334155;
        }
        .hero-section {
            padding: 90px 0 60px 0;
            background: radial-gradient(circle at 50% 20%, rgba(13, 148, 136, 0.15) 0%, rgba(15, 23, 42, 1) 70%);
        }
        .academic-pill {
            background-color: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.35);
            font-size: 0.85rem;
            padding: 6px 16px;
            border-radius: 50rem;
            display: inline-flex;
            align-items: center;
        }
        .btn-teal {
            background-color: var(--teal-accent);
            color: #ffffff;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-teal:hover {
            background-color: var(--teal-hover);
            color: #ffffff;
            transform: translateY(-2px);
        }
        .btn-outline-custom {
            border: 1px solid #475569;
            color: #e2e8f0;
            font-weight: 500;
            padding: 10px 24px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-outline-custom:hover {
            background-color: #334155;
            color: #ffffff;
            border-color: #64748b;
        }
        .feature-card {
            background-color: var(--navy-card);
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 28px;
            height: 100%;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            border-color: var(--teal-accent);
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background-color: rgba(13, 148, 136, 0.15);
            color: var(--teal-light);
            margin-bottom: 20px;
        }
        footer {
            background-color: #020617;
            border-top: 1px solid #1e293b;
        }
    </style>
</head>
<body>

    <!-- Academic Demo Visibly Displayed Banner -->
    <div class="py-2 text-center" style="background-color: #020617; border-bottom: 1px solid #1e293b;">
        <span class="academic-pill">
            <i class="bi bi-shield-exclamation me-2"></i> Academic Demo – Fictional Data Only. Not an official law-enforcement system.
        </span>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center text-white fw-bold fs-4" href="/">
                <i class="bi bi-folder2-open text-teal me-2" style="color: var(--teal-light);"></i> CaseCraft
            </a>
            <button class="navbar-toggler border-secondary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-center gap-2 mt-3 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <span class="text-secondary small me-2">Signed in as <strong><?= e($user['full_name']) ?></strong></span>
                        </li>
                        <li class="nav-item">
                            <a href="/dashboard" class="btn btn-teal btn-sm"><i class="bi bi-speedometer2 me-1"></i> Go to Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a href="/logout" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="/login" class="btn btn-outline-custom btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i> Sign In</a>
                        </li>
                        <li class="nav-item">
                            <a href="/signup" class="btn btn-teal btn-sm"><i class="bi bi-person-plus me-1"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <span class="badge bg-secondary mb-3 px-3 py-2 text-uppercase tracking-wide" style="font-size: 0.75rem; letter-spacing: 1px;">
                        Centralized Case Tracking & Evidence Integrity
                    </span>
                    <h1 class="display-4 fw-bold text-white mb-3">
                        Investigation Case Management, <span style="color: var(--teal-light);">Structured & Secure</span>.
                    </h1>
                    <p class="lead text-secondary mb-4 px-md-5">
                        Replace scattered notes and disconnected files with organized case records. Assign investigators, record witness statements, track evidence chain-of-custody metadata, and monitor case timelines in one centralized platform.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <?php if ($isLoggedIn): ?>
                            <a href="/dashboard" class="btn btn-teal btn-lg"><i class="bi bi-speedometer2 me-2"></i> Open Dashboard</a>
                        <?php else: ?>
                            <a href="/login" class="btn btn-teal btn-lg"><i class="bi bi-box-arrow-in-right me-2"></i> Access Portal</a>
                            <a href="/signup" class="btn btn-outline-custom btn-lg"><i class="bi bi-person-plus me-2"></i> Investigator Registration</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" style="background-color: #0b1120;">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold text-white mb-2">Built for Authorized Personnel</h2>
                <p class="text-secondary">Designed with strict access controls, full audit logging, and evidence integrity</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-briefcase"></i></div>
                        <h5 class="fw-bold text-white mb-2">Structured Case Records</h5>
                        <p class="text-secondary small mb-0">Track cases by unique identifiers, classification categories, priority matrix, and status lifecycles from initial intake to review.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-people"></i></div>
                        <h5 class="fw-bold text-white mb-2">Team Assignment Matrix</h5>
                        <p class="text-secondary small mb-0">Assign lead investigators and forensic specialists to specific cases with role-enforced boundaries and permissions.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-file-earmark-lock"></i></div>
                        <h5 class="fw-bold text-white mb-2">Secure Evidence Metadata</h5>
                        <p class="text-secondary small mb-0">Store digital and physical evidence metadata with strict file allowlists (PDF, JPG, PNG) and authenticated download endpoints.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-chat-left-quote"></i></div>
                        <h5 class="fw-bold text-white mb-2">Witness Statements & Notes</h5>
                        <p class="text-secondary small mb-0">Log chronological witness interviews, field logs, and investigation progress notes tied permanently to case files.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-clock-history"></i></div>
                        <h5 class="fw-bold text-white mb-2">Immutable Activity Timelines</h5>
                        <p class="text-secondary small mb-0">Automatic tracking of case status changes, assignments, uploads, and edits for comprehensive accountability.</p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="bi bi-printer"></i></div>
                        <h5 class="fw-bold text-white mb-2">Reports & CSV Export</h5>
                        <p class="text-secondary small mb-0">Generate print-ready formatted case summary dossiers or export filtered case registries to CSV for analysis.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Safety & Ethical Notice -->
    <section class="py-5">
        <div class="container py-3">
            <div class="p-4 rounded-3 border border-secondary-subtle" style="background-color: var(--navy-secondary); border-color: #334155 !important;">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-2 text-warning"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <h5 class="fw-bold text-white mb-1">Academic Prototype & Ethical Scope</h5>
                        <p class="text-secondary small mb-0">
                            CaseCraft is an academic demonstrator using fictional sample data only. It does not predict criminality, make automated legal determinations, or employ facial recognition or covert surveillance. Human review is required for all data relationships.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 text-center text-secondary small">
        <div class="container">
            <p class="mb-1">&copy; <?= date('Y') ?> CaseCraft – Investigation Case Management Web Application.</p>
            <p class="mb-0 text-muted" style="font-size: 0.8rem;">Developed for Academic Demonstration. Palette: Deep Navy, Slate, and Restrained Teal.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

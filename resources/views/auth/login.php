<div class="col-12 col-md-9 col-lg-6">
    <div class="auth-card p-4 p-md-5">
        <!-- Tactical Terminal Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="background: rgba(0, 245, 212, 0.1); border: 1px solid rgba(0, 245, 212, 0.4); box-shadow: 0 0 20px rgba(0, 245, 212, 0.25);">
                <i class="bi bi-shield-lock text-cyber-glow" style="font-size: 2.2rem;"></i>
            </div>
            <h3 class="fw-bold text-white mb-1 tracking-wide">CASECRAFT <span class="text-cyber-glow">CID COMMAND</span></h3>
            <p class="text-secondary small font-monospace mb-0">
                <span class="text-success">&bull;</span> POLICE & FORENSIC INVESTIGATION DOCKET &bull; ZERO-TRUST CLEARANCE <span class="terminal-cursor"></span>
            </p>
        </div>

        <?php if (!empty($flashes['error'])): ?>
            <div class="alert alert-danger d-flex align-items-center py-2" role="alert" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div class="small"><?= e($flashes['error']) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashes['success'])): ?>
            <div class="alert alert-success d-flex align-items-center py-2" role="alert" style="background: rgba(0, 245, 212, 0.1); border: 1px solid rgba(0, 245, 212, 0.4); color: #00f5d4;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="small"><?= e($flashes['success']) ?></div>
            </div>
        <?php endif; ?>

        <!-- Clearance Navigation Tabs -->
        <ul class="nav nav-pills mb-4 nav-justified" id="authTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active font-monospace" id="login-tab" data-bs-toggle="pill" data-bs-target="#login-panel" type="button" role="tab">
                    <i class="bi bi-key me-1"></i> Officer Login (পাসকোড লগইন)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link font-monospace" id="register-tab" data-bs-toggle="pill" data-bs-target="#register-panel" type="button" role="tab">
                    <i class="bi bi-person-plus me-1"></i> First-Time Setup (নতুন পাসকোড সেট)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="authTabContent">
            <!-- Tab 1: Returning Officer Login -->
            <div class="tab-pane fade show active" id="login-panel" role="tabpanel">
                <!-- Standard Passcode Form -->
                <form action="/login" method="POST" autocomplete="off">
                    <?= \App\Helpers\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary small font-monospace">OFFICER IDENTIFIER / EMAIL (অফিসার ইমেইল)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-person-badge"></i></span>
                            <input type="email" class="form-control font-monospace" id="email" name="email" required placeholder="officer@cid.gov.bd" value="<?= e($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-secondary small font-monospace">SECURITY PASSCODE (ব্যক্তিগত গোপন পাসকোড)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control font-monospace" id="password" name="password" required placeholder="••••••••">
                        </div>
                        <div class="form-text text-secondary font-monospace" style="font-size: 0.72rem;">
                            প্রথমবার লগইন করছেন? পাশের <a href="javascript:void(0)" onclick="document.getElementById('register-tab').click()" class="text-cyber-glow">"First-Time Setup"</a> ট্যাবে নিজের পাসকোড নির্ধারণ করুন।
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cyber w-100 py-2 mb-3 fw-bold font-monospace">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Authenticate & Unlock Case Docket
                    </button>
                </form>

                <div class="d-flex align-items-center my-3">
                    <hr class="flex-grow-1 border-secondary" style="opacity: 0.3;">
                    <span class="px-2 text-secondary small font-monospace">OR SIGN IN WITH GOOGLE</span>
                    <hr class="flex-grow-1 border-secondary" style="opacity: 0.3;">
                </div>

                <!-- Google Sign-In Trigger -->
                <div class="mb-3">
                    <?php if (!empty($isGoogleConfigured)): ?>
                        <div id="g_id_signin_btn" class="mb-2 d-flex justify-content-center"></div>
                        <a href="/auth/google/redirect" class="btn btn-google w-100 py-2 font-monospace shadow-sm">
                            <svg width="18" height="18" viewBox="0 0 18 18">
                                <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z"/>
                                <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                                <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.59.102-1.167.282-1.707V4.961H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.039l3.007-2.332z"/>
                                <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z"/>
                            </svg>
                            <span class="fw-bold">Sign in with Google Account</span>
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-google w-100 py-2 shadow-sm font-monospace position-relative" data-bs-toggle="modal" data-bs-target="#googleModal">
                            <svg width="18" height="18" viewBox="0 0 18 18">
                                <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z"/>
                                <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                                <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.59.102-1.167.282-1.707V4.961H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.039l3.007-2.332z"/>
                                <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z"/>
                            </svg>
                            <span class="fw-bold">Sign in with Google Account</span>
                            <span class="position-absolute end-0 top-50 translate-middle-y me-2 badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-check"></i> READY
                            </span>
                        </button>
                    <?php endif; ?>
                    <div id="googleAuthStatus" class="font-monospace small text-center mt-2"></div>
                </div>

                <!-- Biometric Sensor Trigger -->
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-cyber w-100 py-2 d-flex align-items-center justify-content-center gap-2 font-monospace" data-bs-toggle="modal" data-bs-target="#biometricModal">
                        <i class="bi bi-fingerprint fs-5"></i>
                        <i class="bi bi-person-bounding-box fs-5"></i>
                        <span>Biometric Clearances (Fingerprint / Face ID)</span>
                    </button>
                </div>
            </div>

            <!-- Tab 2: First-Time Officer Setup -->
            <div class="tab-pane fade" id="register-panel" role="tabpanel">
                <div class="p-3 rounded-3 mb-3" style="background: rgba(0, 245, 212, 0.05); border: 1px solid rgba(0, 245, 212, 0.25);">
                    <small class="text-white d-block fw-bold"><i class="bi bi-info-circle text-cyber-glow me-1"></i> প্রথমবার তদন্তকারী কর্মকর্তা প্রোফাইল তৈরি:</small>
                    <small class="text-secondary font-monospace">আপনার অফিশিয়াল পরিচয় ও একটি গোপন পাসকোড সেট করুন। এই পাসকোডটি আপনার ব্যক্তিগত তদন্ত ডকেটকে অন্য যে কারো অনুপ্রবেশ থেকে সুরক্ষিত রাখবে।</small>
                </div>

                <form action="/auth/first-time-setup" method="POST" autocomplete="off">
                    <?= \App\Helpers\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="setup_full_name" class="form-label text-light small font-monospace">OFFICER FULL NAME & RANK (নাম ও পদবী) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control font-monospace" id="setup_full_name" name="full_name" required placeholder="e.g. Inspector M. Rahman (CID)">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label for="setup_email" class="form-label text-light small font-monospace">OFFICIAL / GOOGLE EMAIL (ইমেইল) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control font-monospace" id="setup_email" name="email" required placeholder="officer@cid.gov.bd">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="setup_badge" class="form-label text-light small font-monospace">BADGE / ID NO (ব্যাজ নম্বর)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-shield"></i></span>
                                <input type="text" class="form-control font-monospace" id="setup_badge" name="badge_id" placeholder="CID-741">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="setup_passcode" class="form-label text-light small font-monospace">CHOOSE SECRET PASSCODE (পছন্দমতো পাসকোড সেট করুন) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control font-monospace" id="setup_passcode" name="passcode" required placeholder="৪-৮ ডিজিটের পিন বা পাসকোড" minlength="4">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="setup_passcode_confirm" class="form-label text-light small font-monospace">CONFIRM PASSCODE (পাসকোড পুনরায় লিখুন) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-check2-circle"></i></span>
                            <input type="password" class="form-control font-monospace" id="setup_passcode_confirm" name="passcode_confirm" required placeholder="Confirm passcode" minlength="4">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-cyber w-100 py-2 font-monospace fw-bold">
                        <i class="bi bi-shield-lock-fill me-1"></i> Set Passcode & Initialize Officer Docket
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ================= GOOGLE IDENTITY & API GATEWAY MODAL ================= -->
<div class="modal fade" id="googleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content card-custom p-4">
            <div class="modal-header border-0 pb-0 justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <svg width="26" height="26" viewBox="0 0 18 18">
                        <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z"/>
                        <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                        <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.59.102-1.167.282-1.707V4.961H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.039l3.007-2.332z"/>
                        <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z"/>
                    </svg>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0">Google Identity Gateway // Security Clearance</h5>
                        <small class="text-secondary font-monospace" style="font-size: 0.75rem;">OAuth 2.0 &bull; OpenID Connect &bull; Cryptographic Token Verification</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body py-4">
                <ul class="nav nav-pills mb-4 nav-justified" id="googleGatewayTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active font-monospace" id="fast-login-tab" data-bs-toggle="pill" data-bs-target="#fast-login" type="button" role="tab">
                            <i class="bi bi-person-check me-1"></i> Officer Google Sign-In
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link font-monospace" id="api-setup-tab" data-bs-toggle="pill" data-bs-target="#api-setup" type="button" role="tab">
                            <i class="bi bi-gear-fill me-1"></i> Google Cloud API Config
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="googleGatewayTabContent">
                    <!-- Tab 1: Instant Officer Google Sign-In -->
                    <div class="tab-pane fade show active" id="fast-login" role="tabpanel">
                        <?php if (!empty($isGoogleConfigured)): ?>
                            <div class="p-3 mb-4 rounded-3 text-center" style="background: rgba(0, 245, 212, 0.08); border: 1px solid rgba(0, 245, 212, 0.3);">
                                <i class="bi bi-patch-check-fill text-success fs-3 d-block mb-1"></i>
                                <strong class="text-white">Google Cloud API Client Active & Verified</strong>
                                <p class="text-secondary small font-monospace mb-3">Your system is connected to Google Cloud. Click below to authenticate via Google's official OAuth server:</p>
                                <a href="/auth/google/redirect" class="btn btn-google py-2 px-4 font-monospace shadow">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Launch Google OAuth 2.0 Authentication
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Custom Officer Gmail Entry -->
                        <form action="/auth/google" method="POST">
                            <?= \App\Helpers\Csrf::field() ?>
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label text-light small font-monospace">YOUR GOOGLE / GMAIL ACCOUNT *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-google"></i></span>
                                        <input type="email" name="google_email" class="form-control font-monospace" required placeholder="investigator.rahman@gmail.com">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label text-light small font-monospace">OFFICER RANK & NAME *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-person-badge"></i></span>
                                        <input type="text" name="google_name" class="form-control font-monospace" required placeholder="Insp. M. Rahman">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-google w-100 py-2 font-monospace fw-bold">
                                <i class="bi bi-shield-check text-success me-1"></i> Establish Private Officer Workspace
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: Google Cloud Console API Setup -->
                    <div class="tab-pane fade" id="api-setup" role="tabpanel">
                        <div class="p-3 rounded-3 mb-3" style="background: rgba(3, 7, 18, 0.85); border: 1px solid rgba(0, 245, 212, 0.2);">
                            <h6 class="text-white fw-bold mb-2"><i class="bi bi-info-circle text-info me-1"></i> How to get real Google Cloud OAuth credentials:</h6>
                            <ol class="text-secondary small ps-3 mb-3 font-monospace" style="line-height: 1.6;">
                                <li>Open <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-cyber-glow">Google Cloud Console &rarr; Credentials</a></li>
                                <li>Click <strong>+ CREATE CREDENTIALS &rarr; OAuth client ID</strong></li>
                                <li>Application type: <strong>Web application</strong></li>
                                <li>Set <strong>Authorized JavaScript origins:</strong> <code class="text-warning">http://localhost:8000</code></li>
                                <li>Set <strong>Authorized redirect URIs:</strong> <code class="text-warning"><?= e($googleRedirectUri) ?></code></li>
                                <li>Copy Client ID & Client Secret and paste below:</li>
                            </ol>
                        </div>

                        <form action="/auth/google/setup" method="POST">
                            <?= \App\Helpers\Csrf::field() ?>
                            <div class="mb-3">
                                <label class="form-label text-light small font-monospace">GOOGLE CLIENT ID *</label>
                                <input type="text" name="client_id" class="form-control font-monospace" value="<?= e($googleClientId) ?>" required placeholder="e.g. 1048576928-xxxxxx.apps.googleusercontent.com">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light small font-monospace">GOOGLE CLIENT SECRET *</label>
                                <input type="password" name="client_secret" class="form-control font-monospace" placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx">
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-light small font-monospace">REDIRECT URI (AUTO-DETECTED)</label>
                                <input type="text" name="redirect_uri" class="form-control font-monospace bg-dark text-secondary" readonly value="<?= e($googleRedirectUri) ?>">
                            </div>

                            <button type="submit" class="btn btn-cyber w-100 py-2 font-monospace fw-bold">
                                <i class="bi bi-save me-1"></i> Save & Activate Google Cloud Credentials
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= BIOMETRIC SCANNER MODAL ================= -->
<div class="modal fade" id="biometricModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom text-center p-4">
            <div class="modal-header border-0 pb-0 justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cpu text-cyber-glow fs-4"></i>
                    <h5 class="modal-title text-white fw-bold">Biometric Cryptographic Gateway</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body py-4">
                <p class="text-secondary small font-monospace mb-4">Select verification sensor and place your finger or align face with HUD scanner:</p>

                <!-- Officer Account Selector for Biometrics -->
                <div class="mb-4 text-start">
                    <label class="form-label text-secondary small font-monospace">SELECT OFFICER PROFILE</label>
                    <select id="biometricOfficerEmail" class="form-select font-monospace">
                        <option value="officer@cid.gov.bd">Superintendent Marcus Vance</option>
                        <option value="investigator@cid.gov.bd">Inspector Elena Rostova</option>
                    </select>
                </div>

                <!-- Biometric Sensor Box with Laser & Face Mesh Animation -->
                <div class="biometric-scanner-box my-3" id="biometricBox">
                    <div class="biometric-laser" id="biometricLaser"></div>
                    <div class="face-id-mesh" id="faceMeshOverlay"></div>
                    <i class="bi bi-fingerprint text-cyber-glow" id="sensorIcon" style="font-size: 4rem;"></i>
                </div>

                <!-- Status Feedback -->
                <div id="biometricStatusText" class="font-monospace small my-3 text-secondary">
                    <i class="bi bi-shield-check me-1"></i> Sensor Ready. Press button below to scan.
                </div>

                <!-- Dual Biometric Buttons -->
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button type="button" class="btn btn-cyber btn-sm px-3" onclick="triggerScan('fingerprint')">
                        <i class="bi bi-fingerprint me-1"></i> Scan Fingerprint
                    </button>
                    <button type="button" class="btn btn-outline-cyber btn-sm px-3" onclick="triggerScan('face')">
                        <i class="bi bi-person-bounding-box me-1"></i> Verify Face ID
                    </button>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0 justify-content-center">
                <small class="text-secondary font-monospace" style="font-size: 0.75rem;">
                    WebAuthn FIDO2 / Windows Hello & Touch ID Interface Activated
                </small>
            </div>
        </div>
    </div>
</div>

<script>
function triggerScan(type) {
    const email = document.getElementById('biometricOfficerEmail').value;
    const sensorIcon = document.getElementById('sensorIcon');
    
    if (type === 'face') {
        sensorIcon.className = 'bi bi-person-bounding-box text-cyber-glow';
    } else {
        sensorIcon.className = 'bi bi-fingerprint text-cyber-glow';
    }

    window.biometricAuth.startScan(type, email);
}

// Google Identity Services (GIS) Callback Handler
function handleGoogleCredentialResponse(response) {
    const statusDiv = document.getElementById('googleAuthStatus');
    if (statusDiv) {
        statusDiv.innerHTML = '<span class="text-cyber-glow font-monospace small"><i class="bi bi-arrow-repeat spin"></i> Cryptographically verifying Google token...</span>';
    }

    if (window.cyberSound) window.cyberSound.accessGranted();

    fetch('/auth/google/token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ credential: response.credential })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '/dashboard';
        } else {
            alert('Google Verification Error: ' + (data.error || 'Access denied'));
            if (statusDiv) statusDiv.innerHTML = '';
        }
    })
    .catch(err => {
        console.error('Google token verification error:', err);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/auth/google/token';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'credential';
        input.value = response.credential;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });
}

// Initialize Google Identity Services if client ID is set
window.addEventListener('load', function() {
    <?php if (!empty($isGoogleConfigured)): ?>
    if (window.google && window.google.accounts && window.google.accounts.id) {
        google.accounts.id.initialize({
            client_id: "<?= e($googleClientId) ?>",
            callback: handleGoogleCredentialResponse,
            auto_select: false
        });
        const btnContainer = document.getElementById("g_id_signin_btn");
        if (btnContainer) {
            google.accounts.id.renderButton(
                btnContainer,
                { theme: "filled_blue", size: "large", width: "100%", shape: "rectangular", text: "signin_with" }
            );
        }
        google.accounts.id.prompt();
    }
    <?php endif; ?>
});
</script>

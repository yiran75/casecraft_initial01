<div class="col-12 col-md-8 col-lg-5">
    <div class="auth-card p-4 p-md-5">
        <!-- Terminal Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="background: rgba(0, 245, 212, 0.1); border: 1px solid rgba(0, 245, 212, 0.4); box-shadow: 0 0 20px rgba(0, 245, 212, 0.25);">
                <i class="bi bi-key-fill text-cyber-glow" style="font-size: 2.2rem;"></i>
            </div>
            <h3 class="fw-bold text-white mb-1 tracking-wide">SET SECURITY <span class="text-cyber-glow">PASSCODE</span></h3>
            <p class="text-secondary small font-monospace mb-0">
                <span class="text-success">&bull;</span> OFFICER ACCESS INITIALIZATION &bull; CONFIDENTIAL <span class="terminal-cursor"></span>
            </p>
        </div>

        <?php if (!empty($flashes['error'])): ?>
            <div class="alert alert-danger d-flex align-items-center py-2" role="alert" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div class="small"><?= e($flashes['error']) ?></div>
            </div>
        <?php endif; ?>

        <div class="p-3 rounded-3 mb-4" style="background: rgba(3, 7, 18, 0.7); border: 1px solid rgba(0, 245, 212, 0.2);">
            <div class="small text-secondary font-monospace" style="line-height: 1.6;">
                <strong class="text-white d-block mb-1"><i class="bi bi-shield-lock-fill text-cyber-glow me-1"></i> অফিসার গোপনীয়তা নির্দেশনা:</strong>
                তদন্তকারী কর্মকর্তা হিসেবে আপনার কেস ডকেটের সম্পূর্ণ গোপনীয়তা নিশ্চিত করতে আপনার নিজস্ব একটি গোপন পাসকোড (৪-৮ ডিজিটের পিন বা পাসওয়ার্ড) নির্ধারণ করুন। <strong>পরবর্তীতে প্রতিবার লগইনের সময় এই পাসকোডটি আবশ্যক হবে।</strong>
            </div>
        </div>

        <form action="/auth/setup-passcode" method="POST" autocomplete="off">
            <?= \App\Helpers\Csrf::field() ?>

            <div class="mb-3">
                <label for="passcode" class="form-label text-light small font-monospace">CHOOSE SECRET PASSCODE / পিন বা পাসকোড *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control font-monospace" id="passcode" name="passcode" required placeholder="e.g. 4829 or secret passcode" minlength="4" maxlength="32">
                </div>
                <div class="form-text text-secondary" style="font-size: 0.72rem;">Minimum 4 characters or digits. Only you will know this passcode.</div>
            </div>

            <div class="mb-4">
                <label for="passcode_confirm" class="form-label text-light small font-monospace">CONFIRM PASSCODE / পাসকোড নিশ্চিতকরণ *</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-check2-circle"></i></span>
                    <input type="password" class="form-control font-monospace" id="passcode_confirm" name="passcode_confirm" required placeholder="Re-type your passcode" minlength="4" maxlength="32">
                </div>
            </div>

            <button type="submit" class="btn btn-cyber w-100 py-2 mb-3 fw-bold font-monospace">
                <i class="bi bi-shield-check me-1"></i> Save Passcode & Unlock Terminal
            </button>

            <div class="text-center text-secondary small font-monospace">
                CID Investigation Management Command Node &bull; 4096-bit TLS
            </div>
        </form>
    </div>
</div>

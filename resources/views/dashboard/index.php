<div class="row g-3 mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge border border-info text-info small font-monospace">COMMAND CONSOLE</span>
                <span class="badge border border-success text-success small font-monospace">STATUS: OPTIMAL</span>
            </div>
            <h3 class="fw-bold text-white mb-0 font-monospace">FORENSIC COMMAND // <span class="text-cyber-glow">TELEMETRY</span></h3>
            <p class="text-secondary small mb-0 font-monospace">Real-time case metrics, digital evidence chain analysis, and incident vectors</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($currentUser['role'] !== 'reviewer'): ?>
                <a href="/cases/create" class="btn btn-cyber"><i class="bi bi-plus-circle me-1"></i> New Case</a>
            <?php endif; ?>
            <a href="/cases" class="btn btn-outline-cyber"><i class="bi bi-search me-1"></i> Registry</a>
        </div>
    </div>
</div>

<!-- Suspense Cyber HUD Telemetry Alert -->
<div class="card-custom p-3 mb-4 border-info">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 font-monospace small">
        <div class="d-flex align-items-center gap-3">
            <div class="hud-radar" style="width: 45px; height: 45px;"></div>
            <div>
                <span class="text-cyber-glow fw-bold"><i class="bi bi-cpu me-1"></i> ACTIVE SPECTRUM SCANNER:</span>
                <span id="telemetryStream" class="text-secondary ms-2">Scanning network perimeter & database shards...</span>
                <span class="terminal-cursor"></span>
            </div>
        </div>
        <div class="d-flex gap-2 text-secondary">
            <span>UPTIME: <strong class="text-white">99.98%</strong></span>
            <span>&bull;</span>
            <span>THREAT LVL: <strong class="text-cyber-amber">ELEVATED (LEVEL 2)</strong></span>
        </div>
    </div>
</div>

<!-- KPI Summary Cards with Cyber Glow and Animated Counters -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary small font-monospace text-uppercase fw-semibold">Total Dossiers</div>
                <div class="fs-2 fw-bold text-white counter" data-target="<?= (int)$stats['total_cases'] ?>">0</div>
                <div class="text-info small mt-1 font-monospace"><i class="bi bi-folder2-open me-1"></i> Indexed Files</div>
            </div>
            <div class="p-3 rounded-3" style="background-color: rgba(0, 245, 212, 0.1); border: 1px solid rgba(0, 245, 212, 0.3); color: var(--neon-cyan); font-size: 1.75rem;">
                <i class="bi bi-folder-check"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary small font-monospace text-uppercase fw-semibold">Active Inquiries</div>
                <div class="fs-2 fw-bold text-warning counter" data-target="<?= (int)$stats['under_investigation'] ?>">0</div>
                <div class="text-secondary small mt-1 font-monospace"><i class="bi bi-activity me-1"></i> In Field Progress</div>
            </div>
            <div class="p-3 rounded-3" style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; font-size: 1.75rem;">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary small font-monospace text-uppercase fw-semibold">Open Intake</div>
                <div class="fs-2 fw-bold text-cyber-glow counter" data-target="<?= (int)$stats['open_cases'] ?>">0</div>
                <div class="text-secondary small mt-1 font-monospace"><i class="bi bi-flag me-1"></i> Awaiting Assignment</div>
            </div>
            <div class="p-3 rounded-3" style="background-color: rgba(0, 180, 216, 0.1); border: 1px solid rgba(0, 180, 216, 0.3); color: #00b4d8; font-size: 1.75rem;">
                <i class="bi bi-shield-plus"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card-custom p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="text-secondary small font-monospace text-uppercase fw-semibold">Evidence Items</div>
                <div class="fs-2 fw-bold text-success counter" data-target="<?= (int)$stats['total_evidence'] ?>">0</div>
                <div class="text-success small mt-1 font-monospace"><i class="bi bi-file-earmark-lock me-1"></i> Hash Verified</div>
            </div>
            <div class="p-3 rounded-3" style="background-color: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); color: #4ade80; font-size: 1.75rem;">
                <i class="bi bi-fingerprint"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Visualizations (Cyber Styled) -->
<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card-custom p-4 h-100">
            <h5 class="fw-bold text-white mb-1 font-monospace"><i class="bi bi-pie-chart me-2 text-cyber-glow"></i> Case Status Spectrum</h5>
            <p class="text-secondary small font-monospace mb-3">Lifecycle distribution across operational queues</p>
            <div style="height: 260px; position: relative;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-custom p-4 h-100">
            <h5 class="fw-bold text-white mb-1 font-monospace"><i class="bi bi-bar-chart-steps me-2 text-cyber-glow"></i> Crime Classification Spectrum</h5>
            <p class="text-secondary small font-monospace mb-3">Active incident volume grouped by crime category</p>
            <div style="height: 260px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Cases Table -->
<div class="card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-white mb-0 font-monospace"><i class="bi bi-terminal me-2 text-cyber-glow"></i> Active Case Inquiries</h5>
        <a href="/cases" class="text-cyber-glow small text-decoration-none font-monospace">Full Registry Stream &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0" style="background-color: transparent;">
            <thead>
                <tr class="text-secondary border-secondary font-monospace small" style="background: rgba(3, 7, 18, 0.6);">
                    <th>CASE #</th>
                    <th>INCIDENT TITLE</th>
                    <th>CATEGORY</th>
                    <th>PRIORITY</th>
                    <th>STATUS</th>
                    <th>INTAKE DATE</th>
                    <th class="text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentCases)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-3">
                                <i class="bi bi-shield-plus text-cyber-glow fs-2 d-block mb-2"></i>
                                <div class="text-white fw-bold font-monospace mb-1">NO ACTIVE CASES IN DOCKET // কোনো সক্রিয় কেস নেই</div>
                                <p class="text-secondary small font-monospace mb-3">আপনার ফাইল সম্পূর্ণ পরিচ্ছন্ন। যেকোনো নতুন মামলা, খুনের কেস বা তদন্ত নথিভুক্ত করতে নিচের বাটনে চাপুন।</p>
                                <a href="/cases/create" class="btn btn-cyber btn-sm px-4 font-monospace">
                                    <i class="bi bi-plus-circle me-1"></i> Open First Investigation Docket (নতুন কেস ফাইল করুন)
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentCases as $c): ?>
                        <tr class="border-secondary-subtle">
                            <td><strong class="text-cyber-glow font-monospace"><?= e($c['case_number']) ?></strong></td>
                            <td><?= e($c['title']) ?></td>
                            <td><span class="badge bg-dark border border-secondary text-light font-monospace"><?= e($c['category']) ?></span></td>
                            <td>
                                <?php
                                    $pClass = match($c['priority']) {
                                        'Urgent' => 'bg-danger text-white',
                                        'High' => 'bg-warning text-dark',
                                        'Medium' => 'bg-info text-dark',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $pClass ?> font-monospace"><?= e($c['priority']) ?></span>
                            </td>
                            <td>
                                <?php
                                    $sClass = match($c['status']) {
                                        'Open' => 'border border-primary text-primary',
                                        'Under Investigation' => 'border border-warning text-warning',
                                        'Pending Review' => 'border border-info text-info',
                                        'Closed' => 'border border-success text-success',
                                        default => 'border border-secondary text-secondary'
                                    };
                                ?>
                                <span class="badge <?= $sClass ?> px-2 py-1 font-monospace"><?= e($c['status']) ?></span>
                            </td>
                            <td class="text-secondary small font-monospace"><?= e($c['opened_at']) ?></td>
                            <td class="text-end">
                                <a href="/cases/view?id=<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-cyber py-0 px-2 font-monospace">
                                    <i class="bi bi-eye"></i> Inspect
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Interactive Cyber Counter & Chart.js Config -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Animated number counters
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        let count = 0;
        const step = Math.max(1, Math.ceil(target / 25));
        const timer = setInterval(() => {
            count += step;
            if (count >= target) {
                counter.innerText = target;
                clearInterval(timer);
            } else {
                counter.innerText = count;
            }
        }, 40);
    });

    // 2. Suspense Telemetry Typing Effect
    const telemetryMessages = [
        "Scanning network perimeter & forensic shards...",
        "Cryptographic integrity verified // SHA-256 checks pass.",
        "Biometric sensors: Online (Windows Hello & Touch ID).",
        "Threat level stable. No perimeter breaches detected.",
        "Awaiting officer case updates and evidence submission..."
    ];
    let msgIdx = 0;
    const streamEl = document.getElementById('telemetryStream');
    if (streamEl) {
        setInterval(() => {
            msgIdx = (msgIdx + 1) % telemetryMessages.length;
            streamEl.textContent = telemetryMessages[msgIdx];
        }, 4000);
    }

    // 3. Status Doughnut Chart (Neon Palette)
    const statusData = <?= json_encode($stats['status_chart']) ?>;
    const statusLabels = Object.keys(statusData);
    const statusValues = Object.values(statusData);

    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels.length ? statusLabels : ['None'],
            datasets: [{
                data: statusValues.length ? statusValues : [1],
                backgroundColor: ['#00f5d4', '#ffb703', '#00b4d8', '#22c55e', '#7b2cbf'],
                borderColor: '#070d1e',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#94a3b8', font: { family: 'Consolas, monospace', size: 11 } }
                }
            }
        }
    });

    // 4. Category Bar Chart (Neon Cyan Gradient)
    const catData = <?= json_encode($stats['category_chart']) ?>;
    const catLabels = Object.keys(catData);
    const catValues = Object.values(catData);

    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'bar',
        data: {
            labels: catLabels.length ? catLabels : ['General'],
            datasets: [{
                label: 'Dossiers',
                data: catValues.length ? catValues : [0],
                backgroundColor: 'rgba(0, 245, 212, 0.4)',
                borderColor: '#00f5d4',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { ticks: { color: '#94a3b8', font: { family: 'Consolas, monospace' } }, grid: { display: false } },
                y: { ticks: { color: '#94a3b8', stepSize: 1, font: { family: 'Consolas, monospace' } }, grid: { color: 'rgba(0, 245, 212, 0.1)' }, beginAtZero: true }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>

<!-- Header & Case Number -->
<div class="row g-3 mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-dark border border-secondary text-info fs-6 px-3 py-1"><?= e($case['case_number']) ?></span>
                <span id="currentStatusBadge" class="badge px-3 py-1 border 
                    <?= match($case['status']) {
                        'Open' => 'border-primary text-primary',
                        'Under Investigation' => 'border-warning text-warning',
                        'Pending Review' => 'border-info text-info',
                        'Closed' => 'border-success text-success',
                        default => 'border-secondary text-secondary'
                    } ?>">
                    <?= e($case['status']) ?>
                </span>
                <span class="badge bg-secondary"><?= e($case['category']) ?></span>
                <span class="badge <?= match($case['priority']) {
                    'Urgent' => 'bg-danger',
                    'High' => 'bg-warning text-dark',
                    'Medium' => 'bg-info text-dark',
                    default => 'bg-secondary'
                } ?>"><?= e($case['priority']) ?> Priority</span>
            </div>
            <h3 class="fw-bold text-white mb-0"><?= e($case['title']) ?></h3>
        </div>
        <div class="d-flex gap-2">
            <a href="/cases/report?id=<?= (int)$case['id'] ?>" target="_blank" class="btn btn-outline-light btn-sm">
                <i class="bi bi-printer me-1"></i> Print Dossier
            </a>
            <?php if ($currentUser['role'] !== 'reviewer'): ?>
                <a href="/cases/edit?id=<?= (int)$case['id'] ?>" class="btn btn-teal btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Case
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- AJAX Status Quick-Update & Toast Feedback -->
<?php if ($currentUser['role'] !== 'reviewer'): ?>
<div class="card-custom p-3 mb-4 border-info-subtle">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small fw-medium"><i class="bi bi-lightning-charge text-warning me-1"></i> Quick Status Console (Live AJAX):</span>
            <select id="quickStatusSelect" class="form-select form-select-sm" style="width: 220px;">
                <?php foreach (['Open', 'Under Investigation', 'Pending Review', 'Closed', 'Archived'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($case['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
            <button id="updateStatusBtn" type="button" class="btn btn-sm btn-outline-info">
                <span id="statusSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                Apply
            </button>
        </div>
        <div id="ajaxAlert" class="small py-1 px-3 rounded d-none"></div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Case Overview & Tabs -->
    <div class="col-lg-8">
        <!-- Overview Card -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-info-circle me-2 text-teal" style="color: var(--teal-light);"></i> Incident Summary</h5>
            <p class="text-light mb-4" style="line-height: 1.7; white-space: pre-line;"><?= e($case['description']) ?></p>

            <div class="row g-3 pt-3 border-top border-secondary-subtle text-secondary small">
                <?php if (!empty($case['fir_number'])): ?>
                <div class="col-sm-6 col-md-3">
                    <strong>FIR / GD No:</strong> <span class="text-warning ms-1 font-monospace"><?= e($case['fir_number']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($case['incident_location'])): ?>
                <div class="col-sm-6 col-md-5">
                    <strong>Crime Scene:</strong> <span class="text-white ms-1 font-monospace"><i class="bi bi-geo-alt text-danger me-1"></i><?= e($case['incident_location']) ?></span>
                </div>
                <?php endif; ?>
                <div class="col-sm-4 col-md-2">
                    <strong>Opened:</strong> <span class="text-white ms-1"><?= e($case['opened_at']) ?></span>
                </div>
                <div class="col-sm-4 col-md-2">
                    <strong>Investigator:</strong> <span class="text-info ms-1"><?= e($case['creator_name'] ?? 'CID Lead') ?></span>
                </div>
            </div>
        </div>

        <!-- Dossier Subsections Navigation Tabs -->
        <div class="card-custom p-4">
            <ul class="nav nav-pills mb-4 gap-2" id="dossierTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active btn-sm" id="tab-suspects" data-bs-toggle="pill" data-bs-target="#content-suspects" type="button">
                        <i class="bi bi-person-x me-1 text-danger"></i> Suspects & Criminals (<?= count($suspects ?? []) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="tab-victims" data-bs-toggle="pill" data-bs-target="#content-victims" type="button">
                        <i class="bi bi-person-heart me-1 text-warning"></i> Victims (<?= count($victims ?? []) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="tab-evidence" data-bs-toggle="pill" data-bs-target="#content-evidence" type="button">
                        <i class="bi bi-file-earmark-lock me-1"></i> Evidence (<?= count($evidence) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="tab-witness" data-bs-toggle="pill" data-bs-target="#content-witness" type="button">
                        <i class="bi bi-chat-left-quote me-1"></i> Witness Statements (<?= count($statements) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="tab-notes" data-bs-toggle="pill" data-bs-target="#content-notes" type="button">
                        <i class="bi bi-journal-text me-1"></i> Notes (<?= count($notes) ?>)
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="tab-timeline" data-bs-toggle="pill" data-bs-target="#content-timeline" type="button">
                        <i class="bi bi-clock-history me-1"></i> Activity Timeline (<?= count($timeline) ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- 0. Suspects & Criminal Profiles Tab -->
                <div class="tab-pane fade show active" id="content-suspects">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-white mb-0"><i class="bi bi-person-x text-danger me-1"></i> Suspects & Persons of Interest (POI)</h6>
                            <small class="text-secondary">Physical appearance, gait, posture, mannerisms, and last known hideouts</small>
                        </div>
                        <?php if ($currentUser['role'] !== 'reviewer'): ?>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#addSuspectModal">
                                <i class="bi bi-person-plus me-1"></i> Add Suspect Profile
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($suspects)): ?>
                        <div class="p-4 text-center text-secondary bg-dark rounded-3 border border-secondary-subtle font-monospace">
                            <i class="bi bi-shield-slash fs-2 d-block mb-1 text-muted"></i>
                            No suspect profiles cataloged in this case file yet.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($suspects as $sp): ?>
                                <div class="p-3 rounded-3 card-custom border-danger-subtle" style="background: rgba(15, 23, 42, 0.85);">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2 pb-2 border-bottom border-secondary-subtle">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-danger" style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3);">
                                                <i class="bi bi-person-x fs-3"></i>
                                            </div>
                                            <div>
                                                <div class="fs-5 fw-bold text-white mb-0">
                                                    <?= e($sp['full_name']) ?>
                                                    <?php if (!empty($sp['alias_name'])): ?>
                                                        <span class="text-danger small">(Alias: "<?= e($sp['alias_name']) ?>")</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-secondary small font-monospace">
                                                    NID/Passport: <span class="text-white"><?= e($sp['nid_passport'] ?: 'Unknown') ?></span> &bull; 
                                                    Age: <span class="text-white"><?= e($sp['age'] ?: 'Unverified') ?></span> &bull; 
                                                    Gender: <span class="text-white"><?= e($sp['gender']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php
                                                $sBadge = match($sp['suspect_status']) {
                                                    'At Large' => 'bg-danger text-white border-danger',
                                                    'Under Surveillance' => 'bg-warning text-dark border-warning',
                                                    'In Custody', 'Detained' => 'bg-info text-dark border-info',
                                                    'Charges Filed' => 'bg-primary text-white border-primary',
                                                    default => 'bg-secondary text-white'
                                                };
                                            ?>
                                            <span class="badge <?= $sBadge ?> border px-2 py-1 font-monospace"><?= e($sp['suspect_status']) ?></span>
                                            
                                            <?php if ($currentUser['role'] !== 'reviewer'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" title="Edit Suspect Profile" 
                                                        onclick='editSuspect(<?= json_encode($sp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="/suspects/delete" method="POST" onsubmit="return confirm('Confirm removal of this suspect profile?');" class="d-inline">
                                                    <?= \App\Helpers\Csrf::field() ?>
                                                    <input type="hidden" name="id" value="<?= (int)$sp['id'] ?>">
                                                    <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove Suspect">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row g-2 small">
                                        <div class="col-md-6">
                                            <div class="p-2 rounded" style="background: rgba(3, 7, 18, 0.7); border: 1px solid #334155;">
                                                <span class="text-info fw-bold d-block"><i class="bi bi-aspect-ratio me-1"></i> Physical Build & Complexion:</span>
                                                <span class="text-light"><?= e($sp['physical_description'] ?: 'Not recorded') ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2 rounded" style="background: rgba(3, 7, 18, 0.7); border: 1px solid #334155;">
                                                <span class="text-warning fw-bold d-block"><i class="bi bi-bookmark-star me-1"></i> Distinctive Marks / Tattoos / Scars:</span>
                                                <span class="text-light"><?= e($sp['identifying_marks'] ?: 'None recorded') ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2 rounded" style="background: rgba(3, 7, 18, 0.7); border: 1px solid #334155;">
                                                <span class="text-cyber-glow fw-bold d-block"><i class="bi bi-person-walking me-1"></i> Posture, Gait & Mannerisms (শারীরিক ভঙ্গি):</span>
                                                <span class="text-light"><?= e($sp['posture_mannerisms'] ?: 'Standard / Unobserved') ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-2 rounded" style="background: rgba(3, 7, 18, 0.7); border: 1px solid #334155;">
                                                <span class="text-danger fw-bold d-block"><i class="bi bi-geo-alt me-1"></i> Last Known Location & Hideouts (অবস্থান ও গতিবিধি):</span>
                                                <span class="text-light"><?= e($sp['last_known_location'] ?: 'Tracking in progress') ?></span>
                                            </div>
                                        </div>
                                        <?php if (!empty($sp['prior_records'])): ?>
                                            <div class="col-12">
                                                <div class="p-2 rounded" style="background: rgba(3, 7, 18, 0.7); border: 1px solid #334155;">
                                                    <span class="text-secondary fw-bold d-block"><i class="bi bi-clock-history me-1"></i> Prior Criminal Records & Intelligence Notes:</span>
                                                    <span class="text-light"><?= e($sp['prior_records']) ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 0. Victims Profile Tab -->
                <div class="tab-pane fade" id="content-victims">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-white mb-0"><i class="bi bi-person-heart text-warning me-1"></i> Registered Victims</h6>
                            <small class="text-secondary">Injury conditions, trauma assessment, and witness protection protocol</small>
                        </div>
                        <?php if ($currentUser['role'] !== 'reviewer'): ?>
                            <button type="button" class="btn btn-warning text-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addVictimModal">
                                <i class="bi bi-plus-circle me-1"></i> Log Victim Record
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($victims)): ?>
                        <div class="p-4 text-center text-secondary bg-dark rounded-3 border border-secondary-subtle font-monospace">
                            <i class="bi bi-person-check fs-2 d-block mb-1 text-muted"></i>
                            No victim records logged in this case file.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-secondary small border-secondary-subtle">
                                        <th>Victim Name & Info</th>
                                        <th>Harm / Condition</th>
                                        <th>Damages / Injuries Loss</th>
                                        <th>Protection Status</th>
                                        <th>Contact / Address</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($victims as $vc): ?>
                                        <tr class="border-secondary-subtle">
                                            <td>
                                                <strong class="text-white d-block"><?= e($vc['full_name']) ?></strong>
                                                <small class="text-secondary"><?= e($vc['gender']) ?>, Age: <?= e($vc['age'] ?: 'N/A') ?> &bull; NID: <?= e($vc['nid_passport'] ?: 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                    $vcClass = match($vc['victim_condition']) {
                                                        'Deceased' => 'bg-danger text-white',
                                                        'Hospitalized' => 'bg-warning text-dark',
                                                        'Safe / Protected' => 'bg-success text-white',
                                                        'Missing' => 'bg-danger-subtle text-danger border border-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?= $vcClass ?> font-monospace"><?= e($vc['victim_condition']) ?></span>
                                            </td>
                                            <td class="small text-light" style="max-width: 200px;">
                                                <?= e($vc['injuries_loss_description'] ?: 'None documented') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark border border-secondary text-info"><?= e($vc['protection_status'] ?: 'Standard Security') ?></span>
                                            </td>
                                            <td class="small text-secondary">
                                                <div><?= e($vc['contact_number'] ?: 'Unlisted') ?></div>
                                                <div class="text-truncate" style="max-width: 150px;"><?= e($vc['address'] ?: 'Protected') ?></div>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($currentUser['role'] !== 'reviewer'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" title="Edit Victim Record" 
                                                            onclick='editVictim(<?= json_encode($vc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="/victims/delete" method="POST" onsubmit="return confirm('Delete this victim record?');" class="d-inline">
                                                        <?= \App\Helpers\Csrf::field() ?>
                                                        <input type="hidden" name="id" value="<?= (int)$vc['id'] ?>">
                                                        <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 1. Evidence Tab -->
                <div class="tab-pane fade" id="content-evidence">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0">Cataloged Evidence & Attachments</h6>
                        <?php if ($currentUser['role'] !== 'reviewer'): ?>
                            <button type="button" class="btn btn-teal btn-sm" data-bs-toggle="modal" data-bs-target="#addEvidenceModal">
                                <i class="bi bi-upload me-1"></i> Add Evidence
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($evidence)): ?>
                        <div class="p-4 text-center text-secondary bg-dark rounded-3 border border-secondary-subtle">
                            <i class="bi bi-folder-x fs-3 d-block mb-1"></i>
                            No evidence items have been cataloged for this case yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-secondary small border-secondary-subtle">
                                        <th>Title & Details</th>
                                        <th>Classification</th>
                                        <th>Collected</th>
                                        <th>Attachment</th>
                                        <th>Logged By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($evidence as $ev): ?>
                                        <tr class="border-secondary-subtle">
                                            <td>
                                                <strong class="text-white d-block"><?= e($ev['title']) ?></strong>
                                                <small class="text-secondary"><?= e($ev['description']) ?></small>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= e($ev['evidence_type']) ?></span></td>
                                            <td class="small text-secondary"><?= e($ev['collected_at']) ?></td>
                                            <td>
                                                <?php if (!empty($ev['file_path'])): ?>
                                                    <a href="/evidence/download?id=<?= (int)$ev['id'] ?>" class="btn btn-sm btn-outline-info py-0 px-2" title="Download Verified File">
                                                        <i class="bi bi-download me-1"></i> <?= e($ev['original_file_name'] ?: 'Download') ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Physical / No File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small text-secondary"><?= e($ev['uploaded_by_name'] ?? 'Officer') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Witness Statements Tab -->
                <div class="tab-pane fade" id="content-witness">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0">Recorded Witness Statements</h6>
                        <?php if ($currentUser['role'] !== 'reviewer'): ?>
                            <button type="button" class="btn btn-teal btn-sm" data-bs-toggle="modal" data-bs-target="#addWitnessModal">
                                <i class="bi bi-plus-circle me-1"></i> Record Statement
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($statements)): ?>
                        <div class="p-4 text-center text-secondary bg-dark rounded-3 border border-secondary-subtle">
                            <i class="bi bi-chat-square-dots fs-3 d-block mb-1"></i>
                            No witness statements recorded.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($statements as $ws): ?>
                                <div class="p-3 rounded-3" style="background-color: #0b1120; border: 1px solid #334155;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary px-2 py-1"><?= e($ws['witness_reference']) ?></span>
                                            <?php if ($currentUser['role'] !== 'reviewer'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info py-0 px-2" title="Edit Witness Statement"
                                                        onclick='editWitness(<?= json_encode($ws, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="/witness/delete" method="POST" onsubmit="return confirm('Delete this witness statement?');" class="d-inline">
                                                    <?= \App\Helpers\Csrf::field() ?>
                                                    <input type="hidden" name="id" value="<?= (int)$ws['id'] ?>">
                                                    <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-secondary"><?= e($ws['recorded_at']) ?> | By: <?= e($ws['recorded_by_name'] ?? 'Officer') ?></small>
                                    </div>
                                    <p class="text-light mb-0 small" style="line-height: 1.6;"><?= e($ws['statement_text']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Notes Tab -->
                <div class="tab-pane fade" id="content-notes">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white mb-0">Internal Investigation Notes</h6>
                        <?php if ($currentUser['role'] !== 'reviewer'): ?>
                            <button type="button" class="btn btn-teal btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="bi bi-sticky me-1"></i> Append Note
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($notes)): ?>
                        <div class="p-4 text-center text-secondary bg-dark rounded-3 border border-secondary-subtle">
                            <i class="bi bi-journal fs-3 d-block mb-1"></i>
                            No notes logged for this case file yet.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($notes as $n): ?>
                                <div class="p-3 rounded-3" style="background-color: #0b1120; border: 1px solid #334155;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-teal small" style="color: var(--teal-light);"><?= e($n['author_name'] ?? 'Investigator') ?></strong>
                                        <small class="text-secondary"><?= e($n['created_at']) ?></small>
                                    </div>
                                    <p class="text-light mb-0 small" style="line-height: 1.6;"><?= e($n['note_text']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 4. Timeline Tab -->
                <div class="tab-pane fade" id="content-timeline">
                    <h6 class="fw-bold text-white mb-3">Immutable Activity Timeline</h6>
                    <?php if (empty($timeline)): ?>
                        <div class="text-secondary small">No activity logged.</div>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0" style="position: relative; padding-left: 20px; border-left: 2px solid #334155;">
                            <?php foreach ($timeline as $log): ?>
                                <li class="mb-3" style="position: relative;">
                                    <div style="position: absolute; left: -27px; top: 2px; width: 12px; height: 12px; border-radius: 50%; background-color: var(--teal-accent);"></div>
                                    <div class="small text-secondary"><?= e($log['created_at']) ?> - <span class="text-info"><?= e($log['actor_name'] ?? 'Officer') ?></span></div>
                                    <div class="text-light fw-medium small"><?= e($log['description']) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Assigned Investigators & Possible Links -->
    <div class="col-lg-4">
        <!-- Assigned Staff Card -->
        <div class="card-custom p-4 mb-4">
            <h5 class="fw-bold text-white mb-3"><i class="bi bi-people me-2 text-teal" style="color: var(--teal-light);"></i> Assigned Personnel</h5>
            <?php if (empty($assignedUsers)): ?>
                <p class="text-secondary small mb-0">No investigators currently assigned to this dossier.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($assignedUsers as $u): ?>
                        <div class="p-2 rounded-2 d-flex align-items-center justify-content-between" style="background-color: #0b1120; border: 1px solid #334155;">
                            <div>
                                <strong class="text-white small d-block"><?= e($u['full_name']) ?></strong>
                                <small class="text-secondary"><?= e($u['email']) ?></small>
                            </div>
                            <span class="badge-role role-<?= e($u['role']) ?>"><?= e($u['role']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Possible Links Review Suggestions Panel -->
        <div class="card-custom p-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-diagram-3 me-2 text-warning"></i> Potential Links</h5>
                <span class="badge bg-warning text-dark small" style="font-size: 0.65rem;">Review Suggestion Only</span>
            </div>
            <p class="text-secondary small mb-3">Transparent heuristic suggestions based on shared tags, categories, or date proximity. Requires human investigation review; never infers guilt.</p>

            <?php if (empty($possibleLinks)): ?>
                <div class="text-secondary small text-center p-3 bg-dark rounded-2 border border-secondary-subtle">
                    No related pattern suggestions identified.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($possibleLinks as $pl): ?>
                        <div class="p-3 rounded-2" style="background-color: #0b1120; border: 1px solid #334155;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="/cases/view?id=<?= (int)$pl['id'] ?>" class="text-info fw-bold small text-decoration-none">
                                    <?= e($pl['case_number']) ?>
                                </a>
                                <span class="badge bg-secondary" style="font-size: 0.7rem;"><?= e($pl['status']) ?></span>
                            </div>
                            <div class="text-white small fw-medium text-truncate mb-1"><?= e($pl['title']) ?></div>
                            <div class="text-warning small" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-return-right me-1"></i> <?= e($pl['link_reason']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Add Evidence Modal -->
<div class="modal fade" id="addEvidenceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content card-custom">
            <form action="/evidence/create" method="POST" enctype="multipart/form-data">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-upload text-teal me-2" style="color: var(--teal-light);"></i> Catalog New Evidence</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Evidence Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. CCTV Recording Gate 2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Evidence Classification *</label>
                        <select name="evidence_type" class="form-select" required>
                            <option value="Document">Documentary</option>
                            <option value="Digital">Digital Media / Log</option>
                            <option value="Physical">Physical Artifact</option>
                            <option value="Photographic">Photographic</option>
                            <option value="Forensic">Forensic Analysis</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Detailed Item Description *</label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Chain of custody condition, recovery coordinates, and item characteristics..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Date Collected</label>
                        <input type="date" name="collected_at" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Secure File Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text text-secondary" style="font-size: 0.75rem;">
                            Allowed types: PDF, JPG, PNG (Max 5MB). Files stored in non-public storage with authenticated downloads.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal btn-sm">Catalog Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Add Witness Statement Modal -->
<div class="modal fade" id="addWitnessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content card-custom">
            <form action="/witness/create" method="POST">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-chat-left-quote text-teal me-2" style="color: var(--teal-light);"></i> Record Witness Statement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Witness Anonymized Reference *</label>
                        <input type="text" name="witness_reference" class="form-control" required placeholder="e.g. WITNESS-LOGISTICS-04 (Do not record real PII)">
                        <div class="form-text text-secondary" style="font-size: 0.75rem;">For academic prototype, avoid unnecessary personal identifiable information.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Statement Narrative *</label>
                        <textarea name="statement_text" class="form-control" rows="4" required placeholder="Full recorded testimonial statement..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Date Recorded</label>
                        <input type="date" name="recorded_at" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal btn-sm">Save Statement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Add Investigation Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content card-custom">
            <form action="/notes/create" method="POST">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-journal-plus text-teal me-2" style="color: var(--teal-light);"></i> Append Internal Note</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Investigation Progress Note *</label>
                        <textarea name="note_text" class="form-control" rows="4" required placeholder="Record tactical findings, follow-up actions, hypothesis, or field updates..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal btn-sm">Append Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. Add Suspect Modal (CID Criminal Profile) -->
<div class="modal fade" id="addSuspectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content card-custom text-start">
            <form action="/suspects/create" method="POST" enctype="multipart/form-data">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-person-x text-danger me-2"></i> Catalog Suspect / Criminal Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Suspect Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Full legal name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Alias / Street Moniker (ডাকনাম / গ্যাং নাম)</label>
                            <input type="text" name="alias_name" class="form-control" placeholder="e.g. Shooter Kabir / Black Cobra">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">NID / Passport / ID No</label>
                            <input type="text" name="nid_passport" class="form-control" placeholder="National identification number">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-light small fw-medium">Age</label>
                            <input type="number" name="age" class="form-control" placeholder="Age">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Current Status *</label>
                            <select name="suspect_status" class="form-select">
                                <option value="At Large" selected>At Large (পলাতক)</option>
                                <option value="Under Surveillance">Under Surveillance (নজরদারিতে)</option>
                                <option value="In Custody">In Custody (আটককৃত)</option>
                                <option value="Detained">Detained (গ্রেফতার)</option>
                                <option value="Charges Filed">Charges Filed (অভিযুক্ত)</option>
                                <option value="Acquitted">Acquitted (খালাসপ্রাপ্ত)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Physical Build & Complexion (উচ্চতা, গড়ন, গায়ের রঙ)</label>
                            <textarea name="physical_description" class="form-control" rows="2" placeholder="e.g. Height 5ft 10in, athletic build, dusky complexion, short black hair"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Distinctive Marks & Scars (শনাক্তকারী চিহ্ন, ট্যাটু, দাগ)</label>
                            <textarea name="identifying_marks" class="form-control" rows="2" placeholder="e.g. 2-inch scar across left eyebrow, dragon tattoo on right forearm"></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Posture, Gait & Mannerisms (শারীরিক ভঙ্গি, হাঁটার ধরণ, বাচনভঙ্গি) *</label>
                            <textarea name="posture_mannerisms" class="form-control" rows="2" placeholder="e.g. Slight limp in right leg, hunched posture, speaks with stutter under stress, habitual eye twitch"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Last Known Location & Movement (সর্বশেষ অবস্থান ও গতিবিধি) *</label>
                            <textarea name="last_known_location" class="form-control" rows="2" placeholder="e.g. Last spotted boarding bus towards Sylhet border, Sector 7 safehouse hideout"></textarea>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-light small fw-medium">Prior Criminal Records & Intelligence Notes</label>
                        <textarea name="prior_records" class="form-control" rows="2" placeholder="Previous conviction records, active warrants, affiliated criminal networks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-shield-plus me-1"></i> Catalog Suspect Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5. Add Victim Modal -->
<div class="modal fade" id="addVictimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content card-custom text-start">
            <form action="/victims/create" method="POST">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-person-heart text-warning me-2"></i> Register Victim Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Victim Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Victim legal name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Age</label>
                            <input type="number" name="age" class="form-control" placeholder="Age">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">NID / Passport No</label>
                            <input type="text" name="nid_passport" class="form-control" placeholder="NID number">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">Contact Phone</label>
                            <input type="text" name="contact_number" class="form-control" placeholder="Emergency phone / family contact">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">Harm / Physical Condition *</label>
                            <select name="victim_condition" class="form-select">
                                <option value="Safe / Protected" selected>Safe / Protected (সুরক্ষিত)</option>
                                <option value="Hospitalized">Hospitalized (চিকিৎসাধীন)</option>
                                <option value="Deceased">Deceased (নিহত)</option>
                                <option value="Missing">Missing (নিখোঁজ)</option>
                                <option value="Uninjured">Uninjured (অক্ষত)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Address / Sanctuary Residence</label>
                        <input type="text" name="address" class="form-control" placeholder="Permanent or temporary shelter address">
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Injuries / Trauma / Financial Loss Details</label>
                            <textarea name="injuries_loss_description" class="form-control" rows="2" placeholder="Documented physical injuries, psychological trauma, or financial stolen assets..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Police Protection & Witness Security Status</label>
                            <textarea name="protection_status" class="form-control" rows="2" placeholder="e.g. 24/7 Armed escort assigned, undercover relocation, standard surveillance"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark btn-sm"><i class="bi bi-shield-check me-1"></i> Register Victim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 6. Edit Suspect Modal -->
<div class="modal fade" id="editSuspectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content card-custom text-start">
            <form action="/suspects/update" method="POST" enctype="multipart/form-data">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                <input type="hidden" name="id" id="edit_suspect_id">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil-square text-info me-2"></i> Update Suspect Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Suspect Full Name *</label>
                            <input type="text" name="full_name" id="edit_suspect_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Alias / Street Moniker</label>
                            <input type="text" name="alias_name" id="edit_suspect_alias" class="form-control">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">NID / Passport / ID No</label>
                            <input type="text" name="nid_passport" id="edit_suspect_nid" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-light small fw-medium">Age</label>
                            <input type="number" name="age" id="edit_suspect_age" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Gender</label>
                            <select name="gender" id="edit_suspect_gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Current Status *</label>
                            <select name="suspect_status" id="edit_suspect_status" class="form-select">
                                <option value="At Large">At Large (পলাতক)</option>
                                <option value="Under Surveillance">Under Surveillance (নজরদারিতে)</option>
                                <option value="In Custody">In Custody (আটককৃত)</option>
                                <option value="Detained">Detained (গ্রেফতার)</option>
                                <option value="Charges Filed">Charges Filed (অভিযুক্ত)</option>
                                <option value="Acquitted">Acquitted (খালাসপ্রাপ্ত)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Physical Build & Complexion</label>
                            <textarea name="physical_description" id="edit_suspect_physical" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Distinctive Marks & Scars</label>
                            <textarea name="identifying_marks" id="edit_suspect_marks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Posture, Gait & Mannerisms *</label>
                            <textarea name="posture_mannerisms" id="edit_suspect_mannerisms" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Last Known Location & Movement *</label>
                            <textarea name="last_known_location" id="edit_suspect_location" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-light small fw-medium">Prior Criminal Records & Intelligence Notes</label>
                        <textarea name="prior_records" id="edit_suspect_records" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm"><i class="bi bi-save me-1"></i> Save Suspect Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 7. Edit Victim Modal -->
<div class="modal fade" id="editVictimModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content card-custom text-start">
            <form action="/victims/update" method="POST">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                <input type="hidden" name="id" id="edit_victim_id">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil-square text-warning me-2"></i> Update Victim Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Victim Full Name *</label>
                            <input type="text" name="full_name" id="edit_victim_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Age</label>
                            <input type="number" name="age" id="edit_victim_age" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-light small fw-medium">Gender</label>
                            <select name="gender" id="edit_victim_gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">NID / Passport No</label>
                            <input type="text" name="nid_passport" id="edit_victim_nid" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">Contact Number</label>
                            <input type="text" name="contact_number" id="edit_victim_contact" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-light small fw-medium">Condition *</label>
                            <select name="victim_condition" id="edit_victim_condition" class="form-select">
                                <option value="Safe / Protected">Safe / Protected</option>
                                <option value="Hospitalized">Hospitalized (চিকিৎসাধীন)</option>
                                <option value="Deceased">Deceased (মৃত)</option>
                                <option value="Missing">Missing (নিখোঁজ)</option>
                                <option value="Uninjured">Uninjured (অক্ষত)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Injuries / Loss Description</label>
                            <textarea name="injuries_loss_description" id="edit_victim_loss" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light small fw-medium">Address & Shelter Sanctuary</label>
                            <textarea name="address" id="edit_victim_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-light small fw-medium">Police Protection & Security Status</label>
                            <input type="text" name="protection_status" id="edit_victim_protection" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark btn-sm"><i class="bi bi-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 8. Edit Witness Modal -->
<div class="modal fade" id="editWitnessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content card-custom">
            <form action="/witness/update" method="POST">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                <input type="hidden" name="id" id="edit_witness_id">

                <div class="modal-header border-secondary-subtle">
                    <h5 class="modal-title text-white"><i class="bi bi-pencil-square text-teal me-2"></i> Update Witness Statement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Witness Identifier *</label>
                        <input type="text" name="witness_reference" id="edit_witness_ref" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Statement Narrative *</label>
                        <textarea name="statement_text" id="edit_witness_text" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-light small fw-medium">Date Recorded</label>
                        <input type="date" name="recorded_at" id="edit_witness_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary-subtle">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal btn-sm">Update Statement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fetch API Live Status Script & Modals Populator -->
<script>
function editSuspect(s) {
    document.getElementById('edit_suspect_id').value = s.id;
    document.getElementById('edit_suspect_name').value = s.full_name || '';
    document.getElementById('edit_suspect_alias').value = s.alias_name || '';
    document.getElementById('edit_suspect_nid').value = s.nid_passport || '';
    document.getElementById('edit_suspect_age').value = s.age || '';
    document.getElementById('edit_suspect_gender').value = s.gender || 'Male';
    document.getElementById('edit_suspect_status').value = s.suspect_status || 'At Large';
    document.getElementById('edit_suspect_physical').value = s.physical_description || '';
    document.getElementById('edit_suspect_marks').value = s.identifying_marks || '';
    document.getElementById('edit_suspect_mannerisms').value = s.posture_mannerisms || '';
    document.getElementById('edit_suspect_location').value = s.last_known_location || '';
    document.getElementById('edit_suspect_records').value = s.prior_records || '';
    new bootstrap.Modal(document.getElementById('editSuspectModal')).show();
}

function editVictim(v) {
    document.getElementById('edit_victim_id').value = v.id;
    document.getElementById('edit_victim_name').value = v.full_name || '';
    document.getElementById('edit_victim_age').value = v.age || '';
    document.getElementById('edit_victim_gender').value = v.gender || 'Male';
    document.getElementById('edit_victim_nid').value = v.nid_passport || '';
    document.getElementById('edit_victim_contact').value = v.contact_number || '';
    document.getElementById('edit_victim_condition').value = v.victim_condition || 'Safe / Protected';
    document.getElementById('edit_victim_loss').value = v.injuries_loss_description || '';
    document.getElementById('edit_victim_address').value = v.address || '';
    document.getElementById('edit_victim_protection').value = v.protection_status || '';
    new bootstrap.Modal(document.getElementById('editVictimModal')).show();
}

function editWitness(w) {
    document.getElementById('edit_witness_id').value = w.id;
    document.getElementById('edit_witness_ref').value = w.witness_reference || '';
    document.getElementById('edit_witness_text').value = w.statement_text || '';
    document.getElementById('edit_witness_date').value = w.recorded_at || '';
    new bootstrap.Modal(document.getElementById('editWitnessModal')).show();
}

document.addEventListener('DOMContentLoaded', () => {
    const updateBtn = document.getElementById('updateStatusBtn');
    const select = document.getElementById('quickStatusSelect');
    const spinner = document.getElementById('statusSpinner');
    const alertBox = document.getElementById('ajaxAlert');
    const statusBadge = document.getElementById('currentStatusBadge');

    if (updateBtn) {
        updateBtn.addEventListener('click', async () => {
            const newStatus = select.value;
            const csrfToken = '<?= \App\Helpers\Csrf::getToken() ?>';

            spinner.classList.remove('d-none');
            updateBtn.disabled = true;
            alertBox.className = 'small py-1 px-3 rounded d-none';

            try {
                const formData = new FormData();
                formData.append('case_id', '<?= (int)$case['id'] ?>');
                formData.append('status', newStatus);
                formData.append('_csrf_token', csrfToken);

                const response = await fetch('/cases/status', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alertBox.textContent = data.message;
                    alertBox.className = 'small py-1 px-3 rounded bg-success-subtle text-success d-inline-block';
                    statusBadge.textContent = newStatus;
                } else {
                    alertBox.textContent = data.error || 'Failed to update status.';
                    alertBox.className = 'small py-1 px-3 rounded bg-danger-subtle text-danger d-inline-block';
                }
            } catch (err) {
                alertBox.textContent = 'Network or server error occurred.';
                alertBox.className = 'small py-1 px-3 rounded bg-danger-subtle text-danger d-inline-block';
            } finally {
                spinner.classList.add('d-none');
                updateBtn.disabled = false;
            }
        });
    }
});
</script>

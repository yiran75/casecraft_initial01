<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            padding: 30px;
        }
        .report-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .report-badge {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;
        }
        .academic-notice {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 6px 12px;
            font-size: 11px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .section-title {
            background-color: #f8fafc;
            border-left: 4px solid #0d9488;
            padding: 6px 10px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #0f172a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>

    <!-- Controls (Hidden on print) -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-light border rounded">
        <div>
            <strong>CaseCraft Investigation Dossier</strong> &bull; Print Preview
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">Close Window</button>
        </div>
    </div>

    <!-- Academic Disclaimer Banner -->
    <div class="academic-notice">
        <strong>Academic Demonstration – Fictional Data Only.</strong> Not an official police or government record. Fictional sample data generated for course review.
    </div>

    <!-- Header -->
    <div class="report-header d-flex justify-content-between align-items-end">
        <div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;">CASECRAFT INVESTIGATION DOSSIER</h3>
            <div class="text-muted">Investigation Case Management System &bull; Confidential Personnel Record</div>
        </div>
        <div class="text-end">
            <h4 class="fw-bold mb-0 text-primary"><?= e($case['case_number']) ?></h4>
            <small class="text-muted">Generated: <?= date('Y-m-d H:i:s') ?></small>
        </div>
    </div>

    <!-- Summary Details Table -->
    <div class="section-title">1. Case Parameters & Intake Data</div>
    <table>
        <tr>
            <th style="width: 20%;">Case Title</th>
            <td colspan="3"><strong><?= e($case['title']) ?></strong></td>
        </tr>
        <tr>
            <th>Classification</th>
            <td style="width: 30%;"><?= e($case['category']) ?></td>
            <th style="width: 20%;">Lifecycle Status</th>
            <td style="width: 30%;"><span class="report-badge"><?= e($case['status']) ?></span></td>
        </tr>
        <tr>
            <th>Priority Level</th>
            <td><?= e($case['priority']) ?></td>
            <th>Date Opened</th>
            <td><?= e($case['opened_at']) ?></td>
        </tr>
        <tr>
            <th>Created By</th>
            <td><?= e($case['creator_name'] ?? 'System') ?></td>
            <th>Date Closed</th>
            <td><?= e($case['closed_at'] ?? 'Pending / Active') ?></td>
        </tr>
    </table>

    <div class="section-title">2. Incident Narrative & Objective Scope</div>
    <div class="p-3 bg-light border rounded mb-3" style="white-space: pre-line; line-height: 1.6;">
        <?= e($case['description']) ?>
    </div>

    <!-- Assigned Investigators -->
    <div class="section-title">3. Assigned Investigation Personnel</div>
    <table>
        <thead>
            <tr>
                <th>Officer / Specialist</th>
                <th>Email Address</th>
                <th>Role</th>
                <th>Assigned Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($assignedUsers)): ?>
                <tr><td colspan="4" class="text-center text-muted">No investigators formally assigned.</td></tr>
            <?php else: ?>
                <?php foreach ($assignedUsers as $u): ?>
                    <tr>
                        <td><strong><?= e($u['full_name']) ?></strong></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= ucfirst(e($u['role'])) ?></td>
                        <td><?= e($u['assigned_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Suspects Profile Section -->
    <div class="section-title">4. Suspect Profiles & Criminal Intelligence (CID)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Suspect Name & Alias</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 25%;">Physical Build, Posture & Gait</th>
                <th style="width: 20%;">Identifying Marks / Scars</th>
                <th style="width: 20%;">Last Known Location & Hideout</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($suspects)): ?>
                <tr><td colspan="5" class="text-center text-muted">No suspects formally cataloged in dossier.</td></tr>
            <?php else: ?>
                <?php foreach ($suspects as $sp): ?>
                    <tr>
                        <td>
                            <strong><?= e($sp['full_name']) ?></strong>
                            <?php if (!empty($sp['alias_name'])): ?><br><small class="text-danger">"<?= e($sp['alias_name']) ?>"</small><?php endif; ?>
                            <br><small class="text-muted">Age: <?= e($sp['age'] ?: 'N/A') ?>, <?= e($sp['gender']) ?></small>
                        </td>
                        <td><span class="report-badge"><?= e($sp['suspect_status']) ?></span></td>
                        <td>
                            <strong>Build:</strong> <?= e($sp['physical_description'] ?: 'Unrecorded') ?><br>
                            <strong>Gait/Posture:</strong> <?= e($sp['posture_mannerisms'] ?: 'Unrecorded') ?>
                        </td>
                        <td><?= e($sp['identifying_marks'] ?: 'None noted') ?></td>
                        <td><?= e($sp['last_known_location'] ?: 'Tracking in progress') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Victims Profile Section -->
    <div class="section-title">5. Registered Victims & Protective Custody</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Victim Identity</th>
                <th style="width: 15%;">Condition</th>
                <th style="width: 35%;">Trauma / Harm / Losses Documented</th>
                <th style="width: 25%;">Protection & Security Protocol</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($victims)): ?>
                <tr><td colspan="4" class="text-center text-muted">No victims registered for this incident.</td></tr>
            <?php else: ?>
                <?php foreach ($victims as $vc): ?>
                    <tr>
                        <td>
                            <strong><?= e($vc['full_name']) ?></strong><br>
                            <small class="text-muted"><?= e($vc['gender']) ?>, Age: <?= e($vc['age'] ?: 'N/A') ?> | NID: <?= e($vc['nid_passport'] ?: 'N/A') ?></small>
                        </td>
                        <td><span class="report-badge"><?= e($vc['victim_condition']) ?></span></td>
                        <td><?= e($vc['injuries_loss_description'] ?: 'None recorded') ?></td>
                        <td><?= e($vc['protection_status'] ?: 'Standard') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Evidence Inventory -->
    <div class="section-title">6. Evidence Inventory & Chain of Custody</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Item Title</th>
                <th style="width: 15%;">Classification</th>
                <th>Description / Custody Details</th>
                <th style="width: 15%;">Date Logged</th>
                <th style="width: 15%;">Cataloged By</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($evidence)): ?>
                <tr><td colspan="5" class="text-center text-muted">No physical or digital evidence registered.</td></tr>
            <?php else: ?>
                <?php foreach ($evidence as $ev): ?>
                    <tr>
                        <td><strong><?= e($ev['title']) ?></strong></td>
                        <td><?= e($ev['evidence_type']) ?></td>
                        <td><?= e($ev['description']) ?></td>
                        <td><?= e($ev['collected_at']) ?></td>
                        <td><?= e($ev['uploaded_by_name'] ?? 'Officer') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Witness Statements -->
    <div class="section-title">5. Recorded Witness Statements</div>
    <?php if (empty($statements)): ?>
        <p class="text-muted small">No formal statements registered.</p>
    <?php else: ?>
        <?php foreach ($statements as $ws): ?>
            <div class="mb-2 p-2 border rounded" style="background-color: #fafafa;">
                <div class="d-flex justify-content-between font-monospace text-muted mb-1" style="font-size: 11px;">
                    <strong>Reference: <?= e($ws['witness_reference']) ?></strong>
                    <span>Recorded: <?= e($ws['recorded_at']) ?> (By: <?= e($ws['recorded_by_name'] ?? 'Staff') ?>)</span>
                </div>
                <div style="line-height: 1.5; font-size: 12px;"><?= e($ws['statement_text']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Internal Notes -->
    <div class="section-title">6. Chronological Tactical Notes</div>
    <?php if (empty($notes)): ?>
        <p class="text-muted small">No internal tactical notes logged.</p>
    <?php else: ?>
        <?php foreach ($notes as $n): ?>
            <div class="mb-2 p-2 border-start border-3 border-teal ps-2" style="border-left-color: #0d9488 !important;">
                <small class="text-muted"><?= e($n['created_at']) ?> &bull; <?= e($n['author_name'] ?? 'Investigator') ?>:</small>
                <div style="font-size: 12px;"><?= e($n['note_text']) ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Sign-off Block -->
    <div class="mt-5 pt-4 border-top">
        <div class="row">
            <div class="col-6">
                <div class="text-muted small mb-4">Investigator Sign-off:</div>
                <div style="border-bottom: 1px solid #94a3b8; width: 80%; height: 30px;"></div>
                <small class="text-muted">Lead Officer Signature / Date</small>
            </div>
            <div class="col-6">
                <div class="text-muted small mb-4">Supervisor Approval:</div>
                <div style="border-bottom: 1px solid #94a3b8; width: 80%; height: 30px;"></div>
                <small class="text-muted">Reviewing Official Signature / Date</small>
            </div>
        </div>
    </div>

</body>
</html>

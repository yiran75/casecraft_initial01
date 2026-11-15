<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card-custom p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary-subtle">
                <div>
                    <h3 class="fw-bold text-white mb-1"><i class="bi bi-folder-plus text-teal me-2" style="color: var(--teal-light);"></i> Create Investigation Case</h3>
                    <p class="text-secondary small mb-0">Initialize structured case file with assigned investigators and metadata</p>
                </div>
                <span class="badge bg-dark border border-secondary text-info fs-6 px-3 py-2">
                    <?= e($suggestedNumber) ?>
                </span>
            </div>

            <form action="/cases/create" method="POST">
                <?= \App\Helpers\Csrf::field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="title" class="form-label text-light fw-medium">Case Title / মামলা বা তদন্তের শিরোনাম *</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Sector-7 Mystery Homicide Investigation" value="<?= e($_POST['title'] ?? '') ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="category" class="form-label text-light fw-medium">Crime Classification / অপরাধের ধরণ *</label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">-- Select Crime Type --</option>
                            <option value="Homicide / Murder (হত্যাকাণ্ড / খুন)" selected>🚨 Homicide / Murder (হত্যাকাণ্ড / খুন)</option>
                            <option value="Attempted Murder & Grievous Hurt (হত্যাচেষ্টা)">Attempted Murder & Grievous Hurt (হত্যাচেষ্টা)</option>
                            <option value="Armed Robbery & Dacoity (ডাকাতি ও দস্যুতা)">Armed Robbery & Dacoity (ডাকাতি ও দস্যুতা)</option>
                            <option value="Kidnapping & Extortion (অপহরণ ও মুক্তিপণ)">Kidnapping & Extortion (অপহরণ ও মুক্তিপণ)</option>
                            <option value="Cybercrime & Digital Fraud (সাইবার ক্রাইম)">Cybercrime & Digital Fraud (সাইবার ক্রাইম)</option>
                            <option value="Financial Scam & Embezzlement (অর্থ আত্মসাৎ)">Financial Scam & Embezzlement (অর্থ আত্মসাৎ)</option>
                            <option value="Narcotics & Drug Trafficking (মাদক চোরাচালান)">Narcotics & Drug Trafficking (মাদক চোরাচালান)</option>
                            <option value="Terrorism & Sabotage (নাশকতা ও সন্ত্রাস)">Terrorism & Sabotage (নাশকতা ও সন্ত্রাস)</option>
                            <option value="Theft & Burglary (চুরি ও ছিনতাই)">Theft & Burglary (চুরি ও ছিনতাই)</option>
                            <option value="Unnatural Death & Inquest (অপমৃত্যু / ইউডি)">Unnatural Death & Inquest (অপমৃত্যু / ইউডি)</option>
                            <option value="Other Serious Crime (অন্যান্য অপরাধ)">Other Serious Crime (অন্যান্য অপরাধ)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="fir_number" class="form-label text-light fw-medium">Police Station FIR / GD No. (থানার মামলা/জিডি নং)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-file-text"></i></span>
                            <input type="text" class="form-control font-monospace" id="fir_number" name="fir_number" placeholder="e.g. FIR-14/2026 or GD-842" value="<?= e($_POST['fir_number'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="incident_location" class="form-label text-light fw-medium">Crime Scene / Incident Location (ঘটনাস্থল ও থানা)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" class="form-control font-monospace" id="incident_location" name="incident_location" placeholder="e.g. Plot 24, Road 7, Banani / Uttara Thana" value="<?= e($_POST['incident_location'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label text-light fw-medium">Initial Incident Summary & Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Detailed objective synopsis of incident, location, initial discovery parameters, and scope..."><?= e($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="priority" class="form-label text-light fw-medium">Priority Level</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="Low">Low Priority</option>
                            <option value="Medium" selected>Medium Priority</option>
                            <option value="High">High Priority</option>
                            <option value="Urgent">Urgent / Critical</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label text-light fw-medium">Initial Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Open" selected>Open</option>
                            <option value="Under Investigation">Under Investigation</option>
                            <option value="Pending Review">Pending Review</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="opened_at" class="form-label text-light fw-medium">Intake Date</label>
                        <input type="date" class="form-control" id="opened_at" name="opened_at" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <!-- Assigned Investigators Selection -->
                <div class="mb-4">
                    <label class="form-label text-light fw-medium mb-1">
                        <i class="bi bi-people me-1"></i> Assign Authorized Investigators
                    </label>
                    <p class="text-secondary small mb-2">Select one or more active investigators to assign responsibility to this dossier:</p>
                    <div class="row g-2 p-3 rounded-3" style="background-color: #0b1120; border: 1px solid #334155;">
                        <?php foreach ($allStaff as $staff): ?>
                            <?php if ($staff['is_active']): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="investigators[]" value="<?= (int)$staff['id'] ?>" id="user_<?= (int)$staff['id'] ?>" <?= ($staff['id'] == $currentUser['id']) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-light small" for="user_<?= (int)$staff['id'] ?>">
                                            <strong><?= e($staff['full_name']) ?></strong> 
                                            <span class="text-secondary">(<?= ucfirst(e($staff['role'])) ?>)</span>
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top border-secondary-subtle">
                    <a href="/cases" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-teal px-4">
                        <i class="bi bi-check-circle me-1"></i> Initialize Case File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

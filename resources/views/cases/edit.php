<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card-custom p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary-subtle">
                <div>
                    <h3 class="fw-bold text-white mb-1"><i class="bi bi-pencil-square text-teal me-2" style="color: var(--teal-light);"></i> Edit Case File</h3>
                    <p class="text-secondary small mb-0">Modify parameters for Case: <strong><?= e($case['case_number']) ?></strong></p>
                </div>
                <a href="/cases/view?id=<?= (int)$case['id'] ?>" class="btn btn-sm btn-outline-info">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dossier
                </a>
            </div>

            <form action="/cases/edit" method="POST">
                <?= \App\Helpers\Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int)$case['id'] ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="title" class="form-label text-light fw-medium">Case Title / মামলা বা তদন্তের শিরোনাম *</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?= e($case['title']) ?>">
                    </div>
                    <div class="col-md-5">
                        <label for="category" class="form-label text-light fw-medium">Crime Classification / অপরাধের ধরণ *</label>
                        <select class="form-select" id="category" name="category" required>
                            <?php 
                            $categories = [
                                'Homicide / Murder (হত্যাকাণ্ড / খুন)',
                                'Attempted Murder & Grievous Hurt (হত্যাচেষ্টা)',
                                'Armed Robbery & Dacoity (ডাকাতি ও দস্যুতা)',
                                'Kidnapping & Extortion ( অপহরণ ও মুক্তিপণ)',
                                'Cybercrime & Digital Fraud (সাইবার ক্রাইম)',
                                'Financial Scam & Embezzlement (অর্থ আত্মসাৎ)',
                                'Narcotics & Drug Trafficking (মাদক চোরাচালান)',
                                'Terrorism & Sabotage (নাশকতা ও সন্ত্রাস)',
                                'Theft & Burglary (চুরি ও ছিনতাই)',
                                'Unnatural Death & Inquest (অপমৃত্যু / ইউডি)',
                                'Other Serious Crime (অন্যান্য অপরাধ)'
                            ];
                            foreach ($categories as $cat): 
                            ?>
                                <option value="<?= e($cat) ?>" <?= ($case['category'] === $cat || str_contains($case['category'], explode(' ', $cat)[0])) ? 'selected' : '' ?>><?= e($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="fir_number" class="form-label text-light fw-medium">Police Station FIR / GD No. (থানার মামলা/জিডি নং)</label>
                        <input type="text" class="form-control font-monospace" id="fir_number" name="fir_number" value="<?= e($case['fir_number'] ?? '') ?>" placeholder="e.g. FIR-14/2026">
                    </div>
                    <div class="col-md-6">
                        <label for="incident_location" class="form-label text-light fw-medium">Crime Scene / Incident Location (ঘটনাস্থল ও থানা)</label>
                        <input type="text" class="form-control font-monospace" id="incident_location" name="incident_location" value="<?= e($case['incident_location'] ?? '') ?>" placeholder="e.g. Uttara Sector 7 / Central Depot">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label text-light fw-medium">Incident Summary *</label>
                    <textarea class="form-control" id="description" name="description" rows="5" required><?= e($case['description']) ?></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="priority" class="form-label text-light fw-medium">Priority Level</label>
                        <select class="form-select" id="priority" name="priority">
                            <?php foreach (['Low', 'Medium', 'High', 'Urgent'] as $pr): ?>
                                <option value="<?= $pr ?>" <?= ($case['priority'] === $pr) ? 'selected' : '' ?>><?= $pr ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label text-light fw-medium">Lifecycle Status</label>
                        <select class="form-select" id="status" name="status">
                            <?php foreach (['Open', 'Under Investigation', 'Pending Review', 'Closed', 'Archived'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($case['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Assigned Investigators Selection -->
                <div class="mb-4">
                    <label class="form-label text-light fw-medium mb-1">
                        <i class="bi bi-people me-1"></i> Update Assigned Investigators
                    </label>
                    <div class="row g-2 p-3 rounded-3" style="background-color: #0b1120; border: 1px solid #334155;">
                        <?php foreach ($allStaff as $staff): ?>
                            <?php if ($staff['is_active']): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="investigators[]" value="<?= (int)$staff['id'] ?>" id="edit_user_<?= (int)$staff['id'] ?>" <?= in_array((int)$staff['id'], $assignedIds, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label text-light small" for="edit_user_<?= (int)$staff['id'] ?>">
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
                    <a href="/cases/view?id=<?= (int)$case['id'] ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-teal px-4">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

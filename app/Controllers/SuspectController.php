<?php
namespace App\Controllers;

use App\Models\Suspect;
use App\Models\CaseModel;
use App\Services\AuthService;
use App\Services\ActivityLogger;
use App\Services\FileUploadService;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Suspect Controller
 */
class SuspectController
{
    public function store(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Unauthorized: Reviewers cannot add suspect records.');
            header('Location: /cases');
            exit;
        }

        $caseId = (int)($_POST['case_id'] ?? 0);
        $case = CaseModel::findById($caseId);
        if (!$case) {
            Session::flash('error', 'Case file not found.');
            header('Location: /cases');
            exit;
        }

        // Strict officer check: only case creator or assigned officer
        if ($currentUser['role'] !== 'admin' && (int)$case['created_by'] !== (int)$currentUser['id']) {
            Session::flash('error', 'Security violation: You can only modify your own classified case files.');
            header('Location: /cases');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        if (empty($fullName)) {
            Session::flash('error', 'Suspect full name is required.');
            header('Location: /cases/view?id=' . $caseId . '#suspects');
            exit;
        }

        // Optional photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploadService::uploadEvidenceFile($_FILES['photo']);
            if ($upload['success']) {
                $photoPath = $upload['file_path'];
            }
        }

        $suspectId = Suspect::create([
            'case_id' => $caseId,
            'full_name' => $fullName,
            'alias_name' => trim($_POST['alias_name'] ?? ''),
            'nid_passport' => trim($_POST['nid_passport'] ?? ''),
            'age' => $_POST['age'] ?? null,
            'gender' => $_POST['gender'] ?? 'Male',
            'suspect_status' => $_POST['suspect_status'] ?? 'At Large',
            'physical_description' => trim($_POST['physical_description'] ?? ''),
            'identifying_marks' => trim($_POST['identifying_marks'] ?? ''),
            'posture_mannerisms' => trim($_POST['posture_mannerisms'] ?? ''),
            'last_known_location' => trim($_POST['last_known_location'] ?? ''),
            'photo_path' => $photoPath,
            'prior_records' => trim($_POST['prior_records'] ?? ''),
            'created_by' => (int)$currentUser['id']
        ]);

        ActivityLogger::log($caseId, 'SUSPECT_CATALOGED', "Suspect profile created: '{$fullName}' (Status: {$_POST['suspect_status']}).");

        Session::flash('success', "Suspect '{$fullName}' successfully cataloged in confidential CID file.");
        header('Location: /cases/view?id=' . $caseId . '#suspects');
        exit;
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Unauthorized: Reviewers cannot edit suspect records.');
            header('Location: /cases');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $caseId = (int)($_POST['case_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');

        if (empty($fullName)) {
            Session::flash('error', 'Suspect full name is required.');
            header('Location: /cases/view?id=' . $caseId . '#suspects');
            exit;
        }

        $photoPath = null;
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = FileUploadService::uploadEvidenceFile($_FILES['photo']);
            if ($upload['success']) {
                $photoPath = $upload['file_path'];
            }
        }

        $data = [
            'full_name' => $fullName,
            'alias_name' => trim($_POST['alias_name'] ?? ''),
            'nid_passport' => trim($_POST['nid_passport'] ?? ''),
            'age' => $_POST['age'] ?? null,
            'gender' => $_POST['gender'] ?? 'Male',
            'suspect_status' => $_POST['suspect_status'] ?? 'At Large',
            'physical_description' => trim($_POST['physical_description'] ?? ''),
            'identifying_marks' => trim($_POST['identifying_marks'] ?? ''),
            'posture_mannerisms' => trim($_POST['posture_mannerisms'] ?? ''),
            'last_known_location' => trim($_POST['last_known_location'] ?? ''),
            'prior_records' => trim($_POST['prior_records'] ?? '')
        ];
        if ($photoPath) {
            $data['photo_path'] = $photoPath;
        }

        Suspect::update($id, $data);
        ActivityLogger::log($caseId, 'SUSPECT_UPDATED', "Updated suspect profile: '{$fullName}' (Status: {$_POST['suspect_status']}).");

        Session::flash('success', "Suspect profile updated successfully.");
        header('Location: /cases/view?id=' . $caseId . '#suspects');
        exit;
    }

    public function delete(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        $id = (int)($_POST['id'] ?? 0);
        $caseId = (int)($_POST['case_id'] ?? 0);

        $suspect = Suspect::findById($id);
        if ($suspect) {
            Suspect::delete($id);
            ActivityLogger::log($caseId, 'SUSPECT_REMOVED', "Removed suspect record #{$id} ({$suspect['full_name']}).");
            Session::flash('info', 'Suspect record removed.');
        }

        header('Location: /cases/view?id=' . $caseId . '#suspects');
        exit;
    }
}

<?php
namespace App\Controllers;

use App\Models\Victim;
use App\Models\CaseModel;
use App\Services\AuthService;
use App\Services\ActivityLogger;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Victim Controller
 */
class VictimController
{
    public function store(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Unauthorized.');
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

        if ($currentUser['role'] !== 'admin' && (int)$case['created_by'] !== (int)$currentUser['id']) {
            Session::flash('error', 'Security violation: Confidential case files can only be altered by the investigating officer.');
            header('Location: /cases');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        if (empty($fullName)) {
            Session::flash('error', 'Victim name is required.');
            header('Location: /cases/view?id=' . $caseId . '#victims');
            exit;
        }

        Victim::create([
            'case_id' => $caseId,
            'full_name' => $fullName,
            'nid_passport' => trim($_POST['nid_passport'] ?? ''),
            'age' => $_POST['age'] ?? null,
            'gender' => $_POST['gender'] ?? 'Male',
            'contact_number' => trim($_POST['contact_number'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'victim_condition' => $_POST['victim_condition'] ?? 'Safe / Protected',
            'injuries_loss_description' => trim($_POST['injuries_loss_description'] ?? ''),
            'protection_status' => trim($_POST['protection_status'] ?? ''),
            'created_by' => (int)$currentUser['id']
        ]);

        ActivityLogger::log($caseId, 'VICTIM_REGISTERED', "Registered victim record: '{$fullName}' (Condition: {$_POST['victim_condition']}).");

        Session::flash('success', "Victim record for '{$fullName}' logged.");
        header('Location: /cases/view?id=' . $caseId . '#victims');
        exit;
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Unauthorized.');
            header('Location: /cases');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $caseId = (int)($_POST['case_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');

        if (empty($fullName)) {
            Session::flash('error', 'Victim name is required.');
            header('Location: /cases/view?id=' . $caseId . '#victims');
            exit;
        }

        Victim::update($id, [
            'full_name' => $fullName,
            'nid_passport' => trim($_POST['nid_passport'] ?? ''),
            'age' => $_POST['age'] ?? null,
            'gender' => $_POST['gender'] ?? 'Male',
            'contact_number' => trim($_POST['contact_number'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'victim_condition' => $_POST['victim_condition'] ?? 'Safe / Protected',
            'injuries_loss_description' => trim($_POST['injuries_loss_description'] ?? ''),
            'protection_status' => trim($_POST['protection_status'] ?? '')
        ]);

        ActivityLogger::log($caseId, 'VICTIM_UPDATED', "Updated victim record: '{$fullName}' (Condition: {$_POST['victim_condition']}).");
        Session::flash('success', "Victim record updated successfully.");
        header('Location: /cases/view?id=' . $caseId . '#victims');
        exit;
    }

    public function delete(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        $caseId = (int)($_POST['case_id'] ?? 0);

        Victim::delete($id);
        ActivityLogger::log($caseId, 'VICTIM_REMOVED', "Removed victim record #{$id}.");
        Session::flash('info', 'Victim record deleted.');

        header('Location: /cases/view?id=' . $caseId . '#victims');
        exit;
    }
}

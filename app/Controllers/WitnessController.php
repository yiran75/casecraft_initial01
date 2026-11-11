<?php
namespace App\Controllers;

use App\Models\WitnessStatement;
use App\Models\CaseModel;
use App\Services\AuthService;
use App\Services\ActivityLogger;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Witness Statement Controller
 */
class WitnessController
{
    public function store(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Reviewers cannot record witness statements.');
            header('Location: /cases');
            exit;
        }

        $caseId = (int)($_POST['case_id'] ?? 0);
        $case = CaseModel::findById($caseId);
        if (!$case) {
            Session::flash('error', 'Case not found.');
            header('Location: /cases');
            exit;
        }

        $witnessReference = trim($_POST['witness_reference'] ?? '');
        $statementText = trim($_POST['statement_text'] ?? '');
        $recordedAt = $_POST['recorded_at'] ?? date('Y-m-d');

        if (empty($witnessReference) || empty($statementText)) {
            Session::flash('error', 'Witness reference identifier and statement narrative are required.');
            header('Location: /cases/view?id=' . $caseId);
            exit;
        }

        WitnessStatement::create([
            'case_id' => $caseId,
            'witness_reference' => $witnessReference,
            'statement_text' => $statementText,
            'recorded_at' => $recordedAt,
            'recorded_by' => (int)$currentUser['id']
        ]);

        ActivityLogger::log($caseId, 'STATEMENT_RECORDED', "Recorded formal witness statement for identifier '{$witnessReference}'.");

        Session::flash('success', "Witness statement for '{$witnessReference}' recorded.");
        header('Location: /cases/view?id=' . $caseId . '#witnesses');
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
        $witnessReference = trim($_POST['witness_reference'] ?? '');
        $statementText = trim($_POST['statement_text'] ?? '');
        $recordedAt = $_POST['recorded_at'] ?? date('Y-m-d');

        if (empty($witnessReference) || empty($statementText)) {
            Session::flash('error', 'Witness reference and statement text are required.');
            header('Location: /cases/view?id=' . $caseId . '#witnesses');
            exit;
        }

        WitnessStatement::update($id, [
            'witness_reference' => $witnessReference,
            'statement_text' => $statementText,
            'recorded_at' => $recordedAt
        ]);

        ActivityLogger::log($caseId, 'WITNESS_UPDATED', "Witness statement updated for '{$witnessReference}'.");
        Session::flash('success', 'Witness statement updated successfully.');
        header('Location: /cases/view?id=' . $caseId . '#witnesses');
        exit;
    }

    public function delete(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $id = (int)($_POST['id'] ?? 0);
        $caseId = (int)($_POST['case_id'] ?? 0);

        WitnessStatement::delete($id);
        ActivityLogger::log($caseId, 'WITNESS_REMOVED', "Witness statement #{$id} deleted.");
        Session::flash('info', 'Witness statement deleted.');
        header('Location: /cases/view?id=' . $caseId . '#witnesses');
        exit;
    }
}

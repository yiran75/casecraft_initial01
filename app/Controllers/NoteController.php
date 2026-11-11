<?php
namespace App\Controllers;

use App\Models\InvestigationNote;
use App\Models\CaseModel;
use App\Services\AuthService;
use App\Services\ActivityLogger;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Investigation Notes Controller
 */
class NoteController
{
    public function store(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Reviewers cannot append notes.');
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

        $noteText = trim($_POST['note_text'] ?? '');

        if (empty($noteText)) {
            Session::flash('error', 'Note content cannot be empty.');
            header('Location: /cases/view?id=' . $caseId);
            exit;
        }

        InvestigationNote::create([
            'case_id' => $caseId,
            'note_text' => $noteText,
            'created_by' => (int)$currentUser['id']
        ]);

        ActivityLogger::log($caseId, 'NOTE_ADDED', "Added new internal investigation note by {$currentUser['full_name']}.");

        Session::flash('success', "Note added to case file.");
        header('Location: /cases/view?id=' . $caseId . '#notes');
        exit;
    }
}

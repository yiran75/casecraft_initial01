<?php
namespace App\Controllers;

use App\Models\Evidence;
use App\Models\CaseModel;
use App\Services\AuthService;
use App\Services\FileUploadService;
use App\Services\ActivityLogger;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Evidence Controller
 * Handles evidence logging and secure authenticated download streaming
 */
class EvidenceController
{
    /**
     * Store evidence record with optional file attachment
     */
    public function store(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Reviewers cannot upload evidence.');
            header('Location: /cases');
            exit;
        }

        $caseId = (int)($_POST['case_id'] ?? 0);
        $case = CaseModel::findById($caseId);

        if (!$case) {
            Session::flash('error', 'Related case not found.');
            header('Location: /cases');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $evidenceType = $_POST['evidence_type'] ?? 'Document';
        $description = trim($_POST['description'] ?? '');
        $collectedAt = $_POST['collected_at'] ?? date('Y-m-d');

        if (empty($title) || empty($description)) {
            Session::flash('error', 'Evidence title and description are required.');
            header('Location: /cases/view?id=' . $caseId);
            exit;
        }

        // Handle secure file upload
        $uploadResult = ['success' => true, 'file_path' => null, 'original_name' => null, 'size' => null, 'mime' => null];
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = FileUploadService::uploadEvidenceFile($_FILES['attachment']);
            if (!$uploadResult['success']) {
                Session::flash('error', $uploadResult['error']);
                header('Location: /cases/view?id=' . $caseId);
                exit;
            }
        }

        Evidence::create([
            'case_id' => $caseId,
            'title' => $title,
            'evidence_type' => $evidenceType,
            'description' => $description,
            'collected_at' => $collectedAt,
            'file_path' => $uploadResult['file_path'],
            'original_file_name' => $uploadResult['original_name'],
            'file_size' => $uploadResult['size'],
            'mime_type' => $uploadResult['mime'],
            'uploaded_by' => (int)$currentUser['id']
        ]);

        ActivityLogger::log($caseId, 'EVIDENCE_ADDED', "Added new evidence item: '{$title}' ({$evidenceType}).");

        Session::flash('success', "Evidence '{$title}' successfully cataloged.");
        header('Location: /cases/view?id=' . $caseId . '#evidence');
        exit;
    }

    /**
     * Authenticated download endpoint (Never expose raw storage directory)
     */
    public function download(): void
    {
        AuthMiddleware::handle();

        $evidenceId = (int)($_GET['id'] ?? 0);
        $evidence = Evidence::findById($evidenceId);

        if (!$evidence || empty($evidence['file_path'])) {
            http_response_code(404);
            die("Error: Evidence record or associated file does not exist.");
        }

        FileUploadService::streamDownload(
            $evidence['file_path'],
            $evidence['original_file_name'] ?: 'evidence_file',
            $evidence['mime_type'] ?: 'application/octet-stream'
        );
    }
}

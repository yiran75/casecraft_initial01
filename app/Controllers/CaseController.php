<?php
namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\CaseAssignment;
use App\Models\Evidence;
use App\Models\WitnessStatement;
use App\Models\InvestigationNote;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuthService;
use App\Services\ActivityLogger;
use App\Helpers\View;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Case Controller
 * Manages full lifecycle of investigation case files
 */
class CaseController
{
    /**
     * List cases with search, multi-filters, sorting, and pagination
     */
    public function index(): void
    {
        AuthMiddleware::handle();

        $currentUser = AuthService::user();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'category' => trim($_GET['category'] ?? ''),
            'priority' => trim($_GET['priority'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to' => trim($_GET['date_to'] ?? ''),
            'investigator_id' => trim($_GET['investigator_id'] ?? '')
        ];

        // If investigator or reviewer, only show assigned or self-created cases unless admin
        $assignedUserId = ($currentUser['role'] !== 'admin') ? (int)$currentUser['id'] : null;

        $result = CaseModel::getFilteredCases($filters, $page, $perPage, $assignedUserId);
        $allStaff = User::getAllStaff();

        View::render('cases/index', [
            'title' => 'Case Registry - CaseCraft',
            'cases' => $result['cases'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['total_pages'],
            'filters' => $filters,
            'allStaff' => $allStaff,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    /**
     * Display create case form
     */
    public function create(): void
    {
        AuthMiddleware::handle();
        $currentUser = AuthService::user();

        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Read-only Reviewers are not authorized to create cases.');
            header('Location: /cases');
            exit;
        }

        $allStaff = User::getAllStaff();
        $suggestedNumber = CaseModel::generateCaseNumber();

        View::render('cases/create', [
            'title' => 'Create Investigation Case - CaseCraft',
            'suggestedNumber' => $suggestedNumber,
            'allStaff' => $allStaff,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    /**
     * Process case creation
     */
    public function store(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Unauthorized action.');
            header('Location: /cases');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $firNumber = trim($_POST['fir_number'] ?? '');
        $incidentLocation = trim($_POST['incident_location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $priority = $_POST['priority'] ?? 'Medium';
        $status = $_POST['status'] ?? 'Open';
        $openedAt = $_POST['opened_at'] ?? date('Y-m-d');
        $assignedInvestigators = $_POST['investigators'] ?? [];

        if (empty($title) || empty($description) || empty($category)) {
            Session::flash('error', 'Title, description, and classification category are required.');
            header('Location: /cases/create');
            exit;
        }

        $caseNumber = CaseModel::generateCaseNumber();

        $caseId = CaseModel::create([
            'case_number' => $caseNumber,
            'fir_number' => $firNumber,
            'incident_location' => $incidentLocation,
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'priority' => $priority,
            'status' => $status,
            'opened_at' => $openedAt,
            'created_by' => (int)$currentUser['id']
        ]);

        // Assign selected investigators
        if (!empty($assignedInvestigators) && is_array($assignedInvestigators)) {
            CaseAssignment::sync($caseId, $assignedInvestigators, (int)$currentUser['id']);
        }

        // Log action
        ActivityLogger::log($caseId, 'CASE_CREATED', "Case {$caseNumber} created with initial status '{$status}' and priority '{$priority}'.");

        Session::flash('success', "Case {$caseNumber} successfully established.");
        header('Location: /cases/view?id=' . $caseId);
        exit;
    }

    /**
     * View detailed case dossier
     */
    public function show(): void
    {
        AuthMiddleware::handle();
        $caseId = (int)($_GET['id'] ?? 0);
        $currentUser = AuthService::user();

        $case = CaseModel::findById($caseId);
        if (!$case) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Case Not Found', 'message' => 'The requested case file does not exist.'], 'layouts/main');
            exit;
        }

        // Strict Officer Data Isolation: No other officer can view this case unless creator or assigned
        if ($currentUser['role'] !== 'admin' && (int)$case['created_by'] !== (int)$currentUser['id'] && !CaseAssignment::isUserAssigned($caseId, (int)$currentUser['id'])) {
            http_response_code(403);
            View::render('errors/403', [
                'title' => 'Classified File Restricted',
                'message' => 'SECURITY RESTRICTION: This case dossier is classified and restricted exclusively to the assigned officer. You are not authorized to inspect this record.'
            ], 'layouts/main');
            exit;
        }

        $assignedUsers = CaseAssignment::getAssignedUsers($caseId);
        $evidence = Evidence::getByCaseId($caseId);
        $statements = WitnessStatement::getByCaseId($caseId);
        $notes = InvestigationNote::getByCaseId($caseId);
        $timeline = ActivityLog::getByCaseId($caseId);
        $possibleLinks = CaseModel::getPossibleLinks($caseId);
        $suspects = \App\Models\Suspect::getByCaseId($caseId);
        $victims = \App\Models\Victim::getByCaseId($caseId);
        $allStaff = User::getAllStaff();

        View::render('cases/show', [
            'title' => "Case {$case['case_number']} - CaseCraft",
            'case' => $case,
            'assignedUsers' => $assignedUsers,
            'evidence' => $evidence,
            'statements' => $statements,
            'notes' => $notes,
            'timeline' => $timeline,
            'possibleLinks' => $possibleLinks,
            'suspects' => $suspects,
            'victims' => $victims,
            'allStaff' => $allStaff,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    /**
     * Edit case details
     */
    public function edit(): void
    {
        AuthMiddleware::handle();
        $currentUser = AuthService::user();

        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Read-only Reviewers cannot edit case files.');
            header('Location: /cases');
            exit;
        }

        $caseId = (int)($_GET['id'] ?? 0);
        $case = CaseModel::findById($caseId);

        if (!$case) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Case Not Found'], 'layouts/main');
            exit;
        }

        $assignedUsers = CaseAssignment::getAssignedUsers($caseId);
        $assignedIds = array_column($assignedUsers, 'id');
        $allStaff = User::getAllStaff();

        View::render('cases/edit', [
            'title' => "Edit Case {$case['case_number']} - CaseCraft",
            'case' => $case,
            'assignedIds' => $assignedIds,
            'allStaff' => $allStaff,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    /**
     * Process case update
     */
    public function update(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            Session::flash('error', 'Unauthorized action.');
            header('Location: /cases');
            exit;
        }

        $caseId = (int)($_POST['id'] ?? 0);
        $case = CaseModel::findById($caseId);

        if (!$case) {
            Session::flash('error', 'Case file not found.');
            header('Location: /cases');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $firNumber = trim($_POST['fir_number'] ?? '');
        $incidentLocation = trim($_POST['incident_location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $priority = $_POST['priority'] ?? 'Medium';
        $status = $_POST['status'] ?? 'Open';
        $assignedInvestigators = $_POST['investigators'] ?? [];

        CaseModel::update($caseId, [
            'title' => $title,
            'fir_number' => $firNumber,
            'incident_location' => $incidentLocation,
            'description' => $description,
            'category' => $category,
            'priority' => $priority,
            'status' => $status
        ]);

        // Sync assignees
        if (is_array($assignedInvestigators)) {
            CaseAssignment::sync($caseId, $assignedInvestigators, (int)$currentUser['id']);
        }

        // Activity log
        ActivityLogger::log($caseId, 'CASE_UPDATED', "Case details updated by {$currentUser['full_name']}. Status: {$status}.");

        Session::flash('success', "Case {$case['case_number']} updated successfully.");
        header('Location: /cases/view?id=' . $caseId);
        exit;
    }

    /**
     * Purge demo seed cases for clean operational use
     */
    public function purgeDemoCases(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $pdo = \App\Models\Database::getConnection();
        // Delete seeded cases (id <= 5)
        $pdo->exec("DELETE FROM cases WHERE id <= 5");

        Session::flash('success', 'Fictional demo cases purged. Database is now clean for real departmental use.');
        header('Location: /cases');
        exit;
    }

    /**
     * Fetch API / AJAX status update endpoint with JSON response
     */
    public function updateStatusAjax(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        if ($currentUser['role'] === 'reviewer') {
            View::json(['success' => false, 'error' => 'Permission denied: Reviewers cannot change status.'], 403);
        }

        $caseId = (int)($_POST['case_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');

        $validStatuses = ['Open', 'Under Investigation', 'Pending Review', 'Closed', 'Archived'];
        if (!in_array($newStatus, $validStatuses, true)) {
            View::json(['success' => false, 'error' => 'Invalid case status specified.'], 422);
        }

        $case = CaseModel::findById($caseId);
        if (!$case) {
            View::json(['success' => false, 'error' => 'Case file not found.'], 404);
        }

        $oldStatus = $case['status'];
        CaseModel::updateStatus($caseId, $newStatus);

        ActivityLogger::log($caseId, 'STATUS_UPDATED', "Case status modified from '{$oldStatus}' to '{$newStatus}' via live console.");

        View::json([
            'success' => true,
            'message' => "Case status successfully updated to '{$newStatus}'.",
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }
}

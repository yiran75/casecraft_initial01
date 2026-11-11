<?php
namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\CaseAssignment;
use App\Models\Evidence;
use App\Models\WitnessStatement;
use App\Models\InvestigationNote;
use App\Models\ActivityLog;
use App\Services\AuthService;
use App\Helpers\View;
use App\Middleware\AuthMiddleware;

/**
 * Report & Export Controller
 * Generates print-ready case dossiers and CSV case list exports
 */
class ReportController
{
    /**
     * Report index page
     */
    public function index(): void
    {
        AuthMiddleware::handle();
        $currentUser = AuthService::user();

        $assignedUserId = ($currentUser['role'] !== 'admin') ? (int)$currentUser['id'] : null;
        $result = CaseModel::getFilteredCases([], 1, 100, $assignedUserId);

        View::render('reports/index', [
            'title' => 'Case Reports & Exports - CaseCraft',
            'cases' => $result['cases'],
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    /**
     * Printable case summary dossier
     */
    public function printReport(): void
    {
        AuthMiddleware::handle();

        $caseId = (int)($_GET['id'] ?? 0);
        $case = CaseModel::findById($caseId);

        if (!$case) {
            http_response_code(404);
            die("Case file not found.");
        }

        $currentUser = AuthService::user();

        // Strict Officer Data Isolation: No other officer can view/print this case
        if ($currentUser['role'] !== 'admin' && (int)$case['created_by'] !== (int)$currentUser['id'] && !\App\Models\CaseAssignment::isUserAssigned($caseId, (int)$currentUser['id'])) {
            http_response_code(403);
            die("SECURITY RESTRICTION: Confidential CID Case File. Access restricted exclusively to the assigned case officer.");
        }

        $assignedUsers = CaseAssignment::getAssignedUsers($caseId);
        $evidence = Evidence::getByCaseId($caseId);
        $statements = WitnessStatement::getByCaseId($caseId);
        $notes = InvestigationNote::getByCaseId($caseId);
        $timeline = ActivityLog::getByCaseId($caseId);
        $suspects = \App\Models\Suspect::getByCaseId($caseId);
        $victims = \App\Models\Victim::getByCaseId($caseId);

        View::render('reports/print', [
            'title' => "Case Dossier Report - {$case['case_number']}",
            'case' => $case,
            'assignedUsers' => $assignedUsers,
            'evidence' => $evidence,
            'statements' => $statements,
            'notes' => $notes,
            'timeline' => $timeline,
            'suspects' => $suspects,
            'victims' => $victims,
            'currentUser' => $currentUser
        ], null); // Self-contained printable layout
    }

    /**
     * Export filtered cases as CSV file
     */
    public function exportCsv(): void
    {
        AuthMiddleware::handle();
        $currentUser = AuthService::user();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'category' => trim($_GET['category'] ?? '')
        ];

        $assignedUserId = ($currentUser['role'] !== 'admin') ? (int)$currentUser['id'] : null;
        $cases = CaseModel::getForCsvExport($filters, $assignedUserId);

        $filename = 'casecraft_export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Header row
        fputcsv($output, ['Case Number', 'Title', 'Category', 'Priority', 'Status', 'Opened Date', 'Closed Date', 'Created By', 'Created At']);

        foreach ($cases as $c) {
            fputcsv($output, [
                $c['case_number'],
                $c['title'],
                $c['category'],
                $c['priority'],
                $c['status'],
                $c['opened_at'],
                $c['closed_at'] ?? 'N/A',
                $c['creator_name'] ?? 'System',
                $c['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}

<?php
namespace App\Models;

use PDO;

/**
 * Case Model
 * Handles CRUD, multi-filtering, search, pagination, and dashboard statistics
 */
class CaseModel
{
    /**
     * Generate unique case number: e.g. CC-2026-006
     */
    public static function generateCaseNumber(): string
    {
        $pdo = Database::getConnection();
        $year = date('Y');
        $stmt = $pdo->query("SELECT MAX(id) as max_id FROM cases");
        $row = $stmt->fetch();
        $nextId = ($row['max_id'] ?? 0) + 1;
        return sprintf("CC-%s-%03d", $year, $nextId);
    }

    /**
     * Create a new case
     */
    public static function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO cases (case_number, fir_number, incident_location, title, description, category, priority, status, opened_at, created_by, created_at)
            VALUES (:case_number, :fir_number, :incident_location, :title, :description, :category, :priority, :status, :opened_at, :created_by, NOW())
        ');

        $stmt->execute([
            'case_number' => $data['case_number'],
            'fir_number' => $data['fir_number'] ?? null,
            'incident_location' => $data['incident_location'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'Medium',
            'status' => $data['status'] ?? 'Open',
            'opened_at' => $data['opened_at'] ?? date('Y-m-d'),
            'created_by' => $data['created_by']
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Find case by ID with creator details
     */
    public static function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT c.*, u.full_name AS creator_name, u.email AS creator_email 
            FROM cases c
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.id = :id
            LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $case = $stmt->fetch();
        return $case ?: null;
    }

    /**
     * Update case details
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $closedAt = ($data['status'] === 'Closed') ? (date('Y-m-d')) : null;

        $stmt = $pdo->prepare('
            UPDATE cases 
            SET title = :title,
                fir_number = :fir_number,
                incident_location = :incident_location,
                description = :description,
                category = :category,
                priority = :priority,
                status = :status,
                closed_at = :closed_at,
                updated_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'fir_number' => $data['fir_number'] ?? null,
            'incident_location' => $data['incident_location'] ?? null,
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'closed_at' => $closedAt
        ]);
    }

    /**
     * Update status only (e.g. for AJAX calls)
     */
    public static function updateStatus(int $id, string $status): bool
    {
        $pdo = Database::getConnection();
        $closedAt = ($status === 'Closed') ? date('Y-m-d') : null;

        $stmt = $pdo->prepare('
            UPDATE cases 
            SET status = :status,
                closed_at = :closed_at,
                updated_at = NOW()
            WHERE id = :id
        ');

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'closed_at' => $closedAt
        ]);
    }

    /**
     * Search, Filter, Sort, and Paginate cases
     */
    public static function getFilteredCases(array $filters = [], int $page = 1, int $perPage = 10, ?int $assignedUserId = null): array
    {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        // Role restriction: if non-admin and assignedUserId provided, filter by assignment or created_by
        if ($assignedUserId !== null) {
            $conditions[] = '(c.created_by = :user_id OR EXISTS (
                SELECT 1 FROM case_assignments ca WHERE ca.case_id = c.id AND ca.user_id = :user_id_assign
            ))';
            $params['user_id'] = $assignedUserId;
            $params['user_id_assign'] = $assignedUserId;
        }

        // Search term (case number, title, description)
        if (!empty($filters['search'])) {
            $conditions[] = '(c.case_number LIKE :search OR c.title LIKE :search OR c.description LIKE :search)';
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        // Status filter
        if (!empty($filters['status'])) {
            $conditions[] = 'c.status = :status';
            $params['status'] = $filters['status'];
        }

        // Category filter
        if (!empty($filters['category'])) {
            $conditions[] = 'c.category = :category';
            $params['category'] = $filters['category'];
        }

        // Priority filter
        if (!empty($filters['priority'])) {
            $conditions[] = 'c.priority = :priority';
            $params['priority'] = $filters['priority'];
        }

        // Date range filters
        if (!empty($filters['date_from'])) {
            $conditions[] = 'c.opened_at >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'c.opened_at <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        // Assigned investigator filter
        if (!empty($filters['investigator_id'])) {
            $conditions[] = 'EXISTS (SELECT 1 FROM case_assignments ca WHERE ca.case_id = c.id AND ca.user_id = :investigator_id)';
            $params['investigator_id'] = (int)$filters['investigator_id'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Count total matching records for pagination
        $countSql = "SELECT COUNT(*) FROM cases c {$whereClause}";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = (int)$countStmt->fetchColumn();

        // Pagination calculation
        $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $perPage) : 1;
        $offset = max(0, ($page - 1) * $perPage);

        // Fetch records
        $sql = "
            SELECT c.*, u.full_name AS creator_name,
            (SELECT COUNT(*) FROM evidence e WHERE e.case_id = c.id) AS evidence_count,
            (SELECT COUNT(*) FROM witness_statements ws WHERE ws.case_id = c.id) AS witness_count
            FROM cases c
            LEFT JOIN users u ON c.created_by = u.id
            {$whereClause}
            ORDER BY c.created_at DESC
            LIMIT :offset, :per_page
        ";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'cases' => $stmt->fetchAll(),
            'total' => $totalRecords,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Get all cases matching filters for CSV export (no pagination limit)
     */
    public static function getForCsvExport(array $filters = [], ?int $assignedUserId = null): array
    {
        $pdo = Database::getConnection();
        $conditions = [];
        $params = [];

        if ($assignedUserId !== null) {
            $conditions[] = '(c.created_by = :user_id OR EXISTS (
                SELECT 1 FROM case_assignments ca WHERE ca.case_id = c.id AND ca.user_id = :user_id_assign
            ))';
            $params['user_id'] = $assignedUserId;
            $params['user_id_assign'] = $assignedUserId;
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(c.case_number LIKE :search OR c.title LIKE :search)';
            $params['search'] = '%' . trim($filters['search']) . '%';
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'c.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $conditions[] = 'c.category = :category';
            $params['category'] = $filters['category'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT c.case_number, c.title, c.category, c.priority, c.status, c.opened_at, c.closed_at, u.full_name AS creator_name, c.created_at
            FROM cases c
            LEFT JOIN users u ON c.created_by = u.id
            {$whereClause}
            ORDER BY c.id DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Dashboard KPI statistics and chart aggregations
     */
    public static function getDashboardStatistics(?int $userId = null, string $role = 'admin'): array
    {
        $pdo = Database::getConnection();
        $userFilter = '';
        $params = [];

        if ($role !== 'admin' && $userId !== null) {
            $userFilter = ' WHERE (c.created_by = :u1 OR EXISTS (SELECT 1 FROM case_assignments ca WHERE ca.case_id = c.id AND ca.user_id = :u2))';
            $params = ['u1' => $userId, 'u2' => $userId];
        }

        // Summary counts
        $totalCasesStmt = $pdo->prepare("SELECT COUNT(*) FROM cases c {$userFilter}");
        $totalCasesStmt->execute($params);
        $totalCases = (int)$totalCasesStmt->fetchColumn();

        $openCasesStmt = $pdo->prepare("SELECT COUNT(*) FROM cases c " . ($userFilter ? "{$userFilter} AND c.status = 'Open'" : "WHERE c.status = 'Open'"));
        $openCasesStmt->execute($params);
        $openCases = (int)$openCasesStmt->fetchColumn();

        $underInvestigationStmt = $pdo->prepare("SELECT COUNT(*) FROM cases c " . ($userFilter ? "{$userFilter} AND c.status = 'Under Investigation'" : "WHERE c.status = 'Under Investigation'"));
        $underInvestigationStmt->execute($params);
        $underInvestigation = (int)$underInvestigationStmt->fetchColumn();

        $closedCasesStmt = $pdo->prepare("SELECT COUNT(*) FROM cases c " . ($userFilter ? "{$userFilter} AND c.status = 'Closed'" : "WHERE c.status = 'Closed'"));
        $closedCasesStmt->execute($params);
        $closedCases = (int)$closedCasesStmt->fetchColumn();

        $evidenceCountStmt = $pdo->query("SELECT COUNT(*) FROM evidence");
        $totalEvidence = (int)$evidenceCountStmt->fetchColumn();

        // Chart Data: Status Breakdown
        $statusStmt = $pdo->prepare("SELECT c.status, COUNT(*) AS count FROM cases c {$userFilter} GROUP BY c.status");
        $statusStmt->execute($params);
        $statusDistribution = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Chart Data: Category Breakdown
        $catStmt = $pdo->prepare("SELECT c.category, COUNT(*) AS count FROM cases c {$userFilter} GROUP BY c.category");
        $catStmt->execute($params);
        $categoryDistribution = $catStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Chart Data: Priority Breakdown
        $priStmt = $pdo->prepare("SELECT c.priority, COUNT(*) AS count FROM cases c {$userFilter} GROUP BY c.priority");
        $priStmt->execute($params);
        $priorityDistribution = $priStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'total_cases' => $totalCases,
            'open_cases' => $openCases,
            'under_investigation' => $underInvestigation,
            'closed_cases' => $closedCases,
            'total_evidence' => $totalEvidence,
            'status_chart' => $statusDistribution,
            'category_chart' => $categoryDistribution,
            'priority_chart' => $priorityDistribution
        ];
    }

    /**
     * Get recent cases for dashboard table
     */
    public static function getRecent(int $limit = 5, ?int $userId = null, string $role = 'admin'): array
    {
        $pdo = Database::getConnection();
        $userFilter = '';
        $params = [];

        if ($role !== 'admin' && $userId !== null) {
            $userFilter = ' WHERE (c.created_by = :u1 OR EXISTS (SELECT 1 FROM case_assignments ca WHERE ca.case_id = c.id AND ca.user_id = :u2))';
            $params = ['u1' => $userId, 'u2' => $userId];
        }

        $sql = "
            SELECT c.*, u.full_name AS creator_name
            FROM cases c
            LEFT JOIN users u ON c.created_by = u.id
            {$userFilter}
            ORDER BY c.created_at DESC
            LIMIT {$limit}
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * "Possible Links" Panel - Transparent review suggestions
     * Clearly explains shared category or timeline proximity. Does not infer guilt.
     */
    public static function getPossibleLinks(int $caseId): array
    {
        $pdo = Database::getConnection();
        $current = self::findById($caseId);
        if (!$current) {
            return [];
        }

        $category = $current['category'];
        $openedAt = $current['opened_at'];

        $stmt = $pdo->prepare('
            SELECT id, case_number, title, category, priority, status, opened_at,
                   CASE 
                       WHEN category = :cat1 THEN CONCAT("Shared Category: ", :cat2)
                       ELSE "Proximity Date Range (Opened within 14 days)"
                   END AS link_reason
            FROM cases
            WHERE id != :case_id 
              AND (category = :cat3 OR (opened_at BETWEEN DATE_SUB(:open_dt1, INTERVAL 14 DAY) AND DATE_ADD(:open_dt2, INTERVAL 14 DAY)))
            LIMIT 5
        ');

        $stmt->execute([
            'cat1' => $category,
            'cat2' => $category,
            'case_id' => $caseId,
            'cat3' => $category,
            'open_dt1' => $openedAt,
            'open_dt2' => $openedAt
        ]);

        return $stmt->fetchAll();
    }
}

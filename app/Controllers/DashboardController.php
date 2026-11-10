<?php
namespace App\Controllers;

use App\Models\CaseModel;
use App\Services\AuthService;
use App\Helpers\View;
use App\Middleware\AuthMiddleware;

/**
 * Dashboard Controller
 * Aggregates summary cards, Chart.js statistics, and recent cases
 */
class DashboardController
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $user = AuthService::user();
        $userId = (int)$user['id'];
        $role = $user['role'];

        $stats = CaseModel::getDashboardStatistics($userId, $role);
        $recentCases = CaseModel::getRecent(5, $userId, $role);

        View::render('dashboard/index', [
            'title' => 'Investigation Dashboard - CaseCraft',
            'stats' => $stats,
            'recentCases' => $recentCases,
            'currentUser' => $user
        ], 'layouts/main');
    }
}

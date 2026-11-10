<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Services\AuthService;
use App\Helpers\View;
use App\Helpers\Session;
use App\Middleware\RoleMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Admin Controller
 * Manages personnel directory, access authorizations, and global activity audit
 */
class AdminController
{
    public function users(): void
    {
        RoleMiddleware::handle('admin');

        $users = User::getAll();
        $currentUser = AuthService::user();

        View::render('admin/users', [
            'title' => 'Personnel Management - CaseCraft Admin',
            'users' => $users,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    public function updateUserRole(): void
    {
        RoleMiddleware::handle('admin');
        CsrfMiddleware::handle();

        $userId = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'investigator';
        $isActive = (int)($_POST['is_active'] ?? 1);

        if (!in_array($role, ['admin', 'investigator', 'reviewer'], true)) {
            Session::flash('error', 'Invalid role selection.');
            header('Location: /admin/users');
            exit;
        }

        // Prevent admin from deactivating or demoting themselves
        $currentUser = AuthService::user();
        if ($userId === (int)$currentUser['id'] && ($role !== 'admin' || $isActive === 0)) {
            Session::flash('error', 'You cannot demote or deactivate your own administrator account.');
            header('Location: /admin/users');
            exit;
        }

        User::updateRoleAndStatus($userId, $role, $isActive);

        Session::flash('success', 'User authorization updated successfully.');
        header('Location: /admin/users');
        exit;
    }

    public function logs(): void
    {
        RoleMiddleware::handle('admin');

        $logs = ActivityLog::getAllSystemLogs(100);
        $currentUser = AuthService::user();

        View::render('admin/logs', [
            'title' => 'System Audit Logs - CaseCraft Admin',
            'logs' => $logs,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }
}

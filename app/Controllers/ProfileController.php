<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Helpers\View;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

/**
 * Profile Controller
 */
class ProfileController
{
    public function index(): void
    {
        AuthMiddleware::handle();
        $currentUser = AuthService::user();
        $user = User::findById((int)$currentUser['id']);

        View::render('profile/index', [
            'title' => 'Officer Profile - CaseCraft',
            'user' => $user,
            'currentUser' => $currentUser
        ], 'layouts/main');
    }

    public function updateProfile(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        $userId = (int)$currentUser['id'];

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));

        if (empty($fullName) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Please provide a valid name and email address.');
            header('Location: /profile');
            exit;
        }

        // Check duplicate email
        $existing = User::findByEmail($email);
        if ($existing && (int)$existing['id'] !== $userId) {
            Session::flash('error', 'Email address is already in use by another account.');
            header('Location: /profile');
            exit;
        }

        User::updateProfile($userId, $fullName, $email);

        // Update session user info
        $updatedUser = $currentUser;
        $updatedUser['full_name'] = $fullName;
        $updatedUser['email'] = $email;
        Session::set('user', $updatedUser);

        Session::flash('success', 'Profile information updated successfully.');
        header('Location: /profile');
        exit;
    }

    public function changePassword(): void
    {
        AuthMiddleware::handle();
        CsrfMiddleware::handle();

        $currentUser = AuthService::user();
        $userId = (int)$currentUser['id'];
        $user = User::findByEmail($currentUser['email']);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password_hash'])) {
            Session::flash('error', 'Current password is incorrect.');
            header('Location: /profile');
            exit;
        }

        if (strlen($newPassword) < 8) {
            Session::flash('error', 'New password must be at least 8 characters long.');
            header('Location: /profile');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New password and confirmation do not match.');
            header('Location: /profile');
            exit;
        }

        User::updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));

        Session::flash('success', 'Security password successfully changed.');
        header('Location: /profile');
        exit;
    }
}

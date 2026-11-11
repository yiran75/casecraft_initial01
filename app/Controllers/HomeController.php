<?php
namespace App\Controllers;

use App\Helpers\View;
use App\Services\AuthService;

/**
 * Public Landing Page Controller
 */
class HomeController
{
    public function index(): void
    {
        View::render('home', [
            'title' => 'CaseCraft - Investigation Case Management',
            'isLoggedIn' => AuthService::check(),
            'user' => AuthService::user()
        ], null); // Self-contained full landing layout
    }
}

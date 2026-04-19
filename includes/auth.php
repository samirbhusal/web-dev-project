<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect to login if not logged in
function requireLogin(string $requiredRole = '') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /FuelTrackpro/pages/login.php');
        exit;
    }
    // check if valid role
    if ($requiredRole && $_SESSION['role'] !== $requiredRole && $_SESSION['role'] !== 'admin') {
        header('Location: /FuelTrackpro/pages/login.php');
        exit;
    }
}

// Admin-only pages
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: /FuelTrackpro/pages/employee/dashboard.php');
        exit;
    }
}

// Safe HTML output
function e(string $val): string {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
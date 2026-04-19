<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'FuelTrack Pro';
$userName = $userName ?? 'Employee';
$userEmail = $userEmail ?? '';

if (!isset($initials)) {
    $parts = explode(' ', $userName);
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/css/style.css">
</head>

<body>

    <?php if (empty($hideNav)): ?>
        <!-- Top Header -->
        <header class="top-header" id="topHeader">
            <div class="header-left">
                <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                <span class="header-title">FuelTrack Pro</span>
            </div>
            <div class="header-right">
                <button class="profile-btn" id="profileBtn" aria-label="Profile menu">
                    <span class="profile-avatar"><?php echo htmlspecialchars($initials); ?></span>
                </button>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-user-info">
                        <span class="dropdown-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="dropdown-email"><?php echo htmlspecialchars($userEmail); ?></span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo BASE_PATH; ?>/pages/employee/change_password.php" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        Change Password
                    </a>
                    <a href="<?php echo BASE_PATH; ?>/pages/logout.php" class="dropdown-item dropdown-item-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        Log Out
                    </a>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
        <?php require_once __DIR__ . '/employee_sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Content  -->
    <div class="dashboard-content" id="dashboardContent" <?php echo !empty($hideNav) ? 'style="padding-top: 2rem; margin-left: 0; display:flex; justify-content:center;"' : ''; ?>>
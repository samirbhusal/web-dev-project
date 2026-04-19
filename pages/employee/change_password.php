<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/app.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Employee';
$userEmail = $_SESSION['email'] ?? '';

$error = '';
$success = '';

/* Handle form submission  */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPw = $_POST['current_password'] ?? '';
    $newPw = $_POST['new_password'] ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    // Verify current password
    $escapedEmail = mysqli_real_escape_string($dbc, $userEmail);
    $escapedCurrent = mysqli_real_escape_string($dbc, $currentPw);
    $row = mysqli_fetch_assoc(mysqli_query(
        $dbc,
        "SELECT user_id FROM users WHERE user_id='$userId' AND password='$escapedCurrent' AND status='active'"
    ));

    if (!$row) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPw) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($newPw !== $confirmPw) {
        $error = 'New passwords do not match.';
    } else {
        $escapedNew = mysqli_real_escape_string($dbc, $newPw);
        mysqli_query($dbc, "UPDATE users SET password='$escapedNew' WHERE user_id='$userId'");
        $success = 'Password updated successfully!';
    }
}

/* User initials */
$parts = explode(' ', $userName);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1)
    $initials .= strtoupper(substr(end($parts), 0, 1));
$pageTitle = 'Change Password | FuelTrack Pro';
$hideNav   = true;
require_once __DIR__ . '/../../includes/employee_header.php';
?>
<div class="page-wrapper">
    <div class="card" style="max-width: 540px;">

        <h1>Change Password</h1>
        <p class="subtitle centered">Enter your current password and choose a new one.</p>

        <?php if ($error): ?>
            <div class="alert error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo e($success); ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <div class="password-wrapper">
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                        required>
                    <button type="button" class="password-toggle" aria-label="Show password"
                        onclick="togglePw('current_password', this)">
                        <!-- eye-off (default, password hidden) -->
                        <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                        <!-- eye-on (password visible) -->
                        <svg class="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="display:none">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" autocomplete="new-password" required>
                    <button type="button" class="password-toggle" aria-label="Show password"
                        onclick="togglePw('new_password', this)">
                        <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                        <svg class="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="display:none">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
                <small class="field-note">Must be at least 6 characters.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
                        required>
                    <button type="button" class="password-toggle" aria-label="Show password"
                        onclick="togglePw('confirm_password', this)">
                        <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                        <svg class="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" style="display:none">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="primary-btn">Update Password</button>
        </form>

        <div class="small-links">
            <a href="<?php echo BASE_PATH; ?>/pages/employee/dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</div>
</div>

<script>
    function togglePw(fieldId, btn) {
        const input = document.getElementById(fieldId);
        const isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        btn.querySelector('.eye-on').style.display = isHidden ? 'block' : 'none';
        btn.querySelector('.eye-off').style.display = isHidden ? 'none' : 'block';
    }

</script>
<?php require_once __DIR__ . '/../../includes/employee_footer.php'; ?>
<?php
require_once '../includes/db.php';
require_once '../config/app.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = mysqli_real_escape_string($dbc, $_POST['email'] ?? '');
    $newPw     = $_POST['new_password']      ?? '';
    $confirmPw = $_POST['confirm_password']  ?? '';

    if (strlen($newPw) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($newPw !== $confirmPw) {
        $error = 'Passwords do not match.';
    } else {
        $result = mysqli_query($dbc, "SELECT user_id FROM users WHERE email='$email' AND status='active'");
        if (mysqli_num_rows($result) > 0) {
            $safePw = mysqli_real_escape_string($dbc, $newPw);
            mysqli_query($dbc, "UPDATE users SET password='$safePw' WHERE email='$email'");
            $success = 'Password updated! You can now log in.';
        } else {
            $error = 'No active account found with that email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — FuelTrack Pro</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/css/style.css">
</head>
<body>

<div class="page-wrapper" style="align-items: center; min-height: 100vh;">
    <div class="card">

        <h1>Reset Password</h1>
        <p class="subtitle centered">Enter your email and choose a new password.</p>

        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="password-toggle" aria-label="Show password"
                            onclick="togglePw('new_password', this)">
                        <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        <svg class="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <small class="field-note">Must be at least 6 characters.</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="password-toggle" aria-label="Show password"
                            onclick="togglePw('confirm_password', this)">
                        <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        <svg class="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="primary-btn">Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="small-links">
            <a href="login.php">← Back to Login</a>
        </div>

    </div>
</div>

<script>
function togglePw(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('.eye-on').style.display  = isHidden ? 'block' : 'none';
    btn.querySelector('.eye-off').style.display = isHidden ? 'none'  : 'block';
}
</script>

</body>
</html>
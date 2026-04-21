<?php
session_start();

// If already logged in redirect
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
  } else {
    header('Location: employee/dashboard.php');
    exit;
  }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once '../includes/db.php';

  $email = $_POST['email'];
  $password = $_POST['password'];
  $role = $_POST['role'];

  // Check email and password in users table
  $query = "SELECT * FROM users WHERE email='$email' AND password='$password' AND status='active'";
  $result = mysqli_query($dbc, $query);

  if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    // Save to session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Redirect based on role
    if ($user['role'] === 'admin') {
      header('Location: admin/dashboard.php');
      exit;
    } else {
      header('Location: employee/dashboard.php');
      exit;
    }
  } else {
    $error = 'Invalid email or password.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — FuelTrack Pro</title>
  <?php require_once '../config/app.php'; ?>
  <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/css/style.css">
</head>

<body>

  <div class="page-wrapper" style="align-items: center; min-height: 100vh;">
    <div class="card">

      <h1>FuelTrack Pro</h1>
      <p class="subtitle centered">Sign in to your account</p>

      <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">

        <!-- Role toggle -->
        <!-- <div class="role-toggle">
                    <input type="radio" name="role" id="emp" value="employee" checked>
                    <label for="emp">Employee</label>
                    <input type="radio" name="role" id="adm" value="admin">
                    <label for="adm">Admin</label>
                </div> -->

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="" required>
            <button type="button" class="password-toggle" aria-label="Show password"
              onclick="togglePw('password', this)">
              <svg class="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path
                  d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
              <svg class="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" style="display:none">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="primary-btn">Sign In</button>
      </form>

      <div class="small-links">
        <a href="forgot_password.php">Forgot password?</a>
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

</body>

</html>
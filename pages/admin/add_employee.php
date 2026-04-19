<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name        = trim($_POST['name'] ?? '');
  $email       = trim($_POST['email'] ?? '');
  $role        = trim($_POST['role'] ?? '');
  $hourly_rate = trim($_POST['hourly_rate'] ?? '0');
  $password    = $_POST['password'] ?? '';

  // Backward compatibility: UI/API payloads may still send supervisor.
  if ($role === 'supervisor') {
    $role = 'manager';
  }

  $allowedRoles = ['admin', 'manager', 'cashier'];
  if (!in_array($role, $allowedRoles, true)) {
    $error = 'Invalid role selected. Allowed roles: admin, manager, cashier.';
  } else {
    $check = mysqli_prepare($dbc, 'SELECT user_id FROM users WHERE email = ?');
    mysqli_stmt_bind_param($check, 's', $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
      $error = 'An employee with this email already exists.';
    } else {
      $insert = mysqli_prepare($dbc, 'INSERT INTO users (name, email, role, hourly_rate, password, status) VALUES (?, ?, ?, ?, ?, "active")');
      mysqli_stmt_bind_param($insert, 'sssds', $name, $email, $role, $hourly_rate, $password);

      if (mysqli_stmt_execute($insert)) {
        $success = 'Employee added successfully!';
      } else {
        $error = 'Failed to add employee: ' . mysqli_error($dbc);
      }

      mysqli_stmt_close($insert);
    }

    mysqli_stmt_close($check);
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Add Employee - FuelTrack Pro</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f0f4f8;
    }

    .topbar {
      background: #065A82;
      color: #fff;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
    }

    .topbar a {
      color: #fca5a5;
      text-decoration: none;
      font-size: 13px;
    }

    .wrapper {
      display: flex;
    }

    .sidebar {
      width: 160px;
      background: #1A3A5C;
      min-height: calc(100vh - 40px);
      padding-top: 10px;
    }

    .sidebar a {
      display: block;
      padding: 9px 14px;
      color: #C8DFF2;
      text-decoration: none;
      font-size: 13px;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: #065A82;
      color: #fff;
    }

    .sidebar .grp {
      padding: 10px 14px 3px;
      font-size: 11px;
      color: #64748B;
      text-transform: uppercase;
    }

    .main {
      flex: 1;
      padding: 20px;
    }

    h2 {
      color: #1A3A5C;
      margin-bottom: 16px;
    }

    .error {
      color: red;
      font-size: 13px;
      margin-bottom: 10px;
    }

    .success {
      color: green;
      font-size: 13px;
      margin-bottom: 10px;
    }

    .form-box {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 20px;
      max-width: 400px;
    }

    label {
      display: block;
      font-size: 13px;
      margin-top: 10px;
      margin-bottom: 3px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="number"],
    select {
      width: 100%;
      padding: 7px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 13px;
      box-sizing: border-box;
    }

    .actions {
      margin-top: 16px;
      display: flex;
      gap: 10px;
    }

    input[type="submit"] {
      padding: 8px 18px;
      background: #065A82;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
    }

    a.cancel {
      padding: 8px 18px;
      background: #eee;
      color: #333;
      border-radius: 4px;
      text-decoration: none;
      font-size: 13px;
    }
  </style>
</head>

<body>

  <div class="topbar">
    <strong>FuelTrack Pro — Admin</strong>
    <span><a href="../logout.php">Logout</a></span>
  </div>

  <div class="wrapper">
    <div class="sidebar">
      <div class="grp">Main</div>
      <a href="dashboard.php">Dashboard</a>
      <div class="grp">Employees</div>
      <a href="employees.php">All Employees</a>
      <a href="add_employee.php" class="active">Add Employee</a>
      <div class="grp">Attendance</div>
      <a href="time_entries.php">Time Entries</a>
      <div class="grp">Payroll</div>
      <a href="payroll.php">Generate Payroll</a>
    </div>

    <div class="main">
      <h2>Add Employee</h2>

      <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
      <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

      <div class="form-box">
        <form method="POST">

          <label>Full Name</label>
          <input type="text" name="name" required />

          <label>Email</label>
          <input type="email" name="email" required />

          <label>Role</label>
          <select name="role" required>
            <option value="">-- Select --</option>
            <option value="cashier">Cashier</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>

          <label>Hourly Rate ($)</label>
          <input type="number" name="hourly_rate" step="0.01" min="0" required />

          <label>Password</label>
          <input type="password" name="password" required />

          <div class="actions">
            <input type="submit" value="Add Employee" />
            <a href="employees.php" class="cancel">Cancel</a>
          </div>

        </form>
      </div>

    </div>
  </div>

</body>

</html>
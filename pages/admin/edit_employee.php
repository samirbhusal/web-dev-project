<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

$error   = '';
$success = '';

// Get employee ID from URL
$id     = $_GET['id'];
$result = mysqli_query($dbc, "SELECT * FROM employees WHERE employee_id='$id'");
$emp    = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'];
    $email       = $_POST['email'];
    $role        = $_POST['role'];
    $hourly_rate = $_POST['hourly_rate'];
    $status      = $_POST['status'];
    $password    = $_POST['password'];

    if (!empty($password)) {
        mysqli_query($dbc, "UPDATE employees SET name='$name', email='$email', role='$role',
            hourly_rate='$hourly_rate', status='$status', password='$password'
            WHERE employee_id='$id'");
    } else {
        mysqli_query($dbc, "UPDATE employees SET name='$name', email='$email', role='$role',
            hourly_rate='$hourly_rate', status='$status'
            WHERE employee_id='$id'");
    }

    $success = 'Employee updated successfully!';

    // Refresh data
    $result = mysqli_query($dbc, "SELECT * FROM users WHERE user_id='$id'");
    $emp    = mysqli_fetch_assoc($result);
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Employee — FuelTrack Pro</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f0f4f8; }
    .topbar { background: #065A82; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; }
    .topbar a { color: #fca5a5; text-decoration: none; font-size: 13px; }
    .wrapper { display: flex; }
    .sidebar { width: 160px; background: #1A3A5C; min-height: calc(100vh - 40px); padding-top: 10px; }
    .sidebar a { display: block; padding: 9px 14px; color: #C8DFF2; text-decoration: none; font-size: 13px; }
    .sidebar a:hover, .sidebar a.active { background: #065A82; color: #fff; }
    .sidebar .grp { padding: 10px 14px 3px; font-size: 11px; color: #64748B; text-transform: uppercase; }
    .main { flex: 1; padding: 20px; }
    h2 { color: #1A3A5C; margin-bottom: 16px; }
    .success { color: green; font-size: 13px; margin-bottom: 10px; }
    .form-box { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; max-width: 400px; }
    label { display: block; font-size: 13px; margin-top: 10px; margin-bottom: 3px; }
    input[type="text"], input[type="email"], input[type="password"], input[type="number"], select {
      width: 100%; padding: 7px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px; box-sizing: border-box;
    }
    .hint { font-size: 11px; color: #888; margin-top: 3px; }
    .actions { margin-top: 16px; display: flex; gap: 10px; }
    input[type="submit"] { padding: 8px 18px; background: #065A82; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
    a.cancel { padding: 8px 18px; background: #eee; color: #333; border-radius: 4px; text-decoration: none; font-size: 13px; }
  </style>
</head>
<body>

<div class="topbar">
  <strong>FuelTrack Pro — Admin</strong>
  <span>Welcome, <?= $_SESSION['name'] ?> | <a href="../logout.php">Logout</a></span>
</div>

<div class="wrapper">
  <div class="sidebar">
    <div class="grp">Main</div>
    <a href="dashboard.php">Dashboard</a>
    <div class="grp">Employees</div>
    <a href="employees.php" class="active">All Employees</a>
    <a href="add_employee.php">Add Employee</a>
    <div class="grp">Attendance</div>
    <a href="time_entries.php">Time Entries</a>
    <div class="grp">Payroll</div>
    <a href="payroll.php">Generate Payroll</a>
  </div>

  <div class="main">
    <h2>Edit Employee</h2>
    <br/><br/>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>

    <div class="form-box">
      <form method="POST">

        <label>Full Name</label>
        <input type="text" name="name" value="<?= $emp['name'] ?>" required/>

        <label>Email</label>
        <input type="email" name="email" value="<?= $emp['email'] ?>" required/>

        <label>Role</label>
        <select name="role" required>
          <option value="cashier"    <?= $emp['role'] === 'cashier'    ? 'selected' : '' ?>>Cashier</option>
          <option value="supervisor" <?= $emp['role'] === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
          <option value="admin"      <?= $emp['role'] === 'admin'      ? 'selected' : '' ?>>Admin</option>
        </select>

        <label>Hourly Rate ($)</label>
        <input type="number" name="hourly_rate" step="0.01" value="<?= $emp['hourly_rate'] ?>" required/>

        <label>Status</label>
        <select name="status">
          <option value="active"   <?= $emp['status'] === 'active'   ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $emp['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>

        <label>New Password</label>
        <input type="password" name="password"/>
        

        <div class="actions">
          <input type="submit" value="Save Changes"/>
          <a href="employees.php" class="cancel">Cancel</a>
        </div>

      </form>
    </div>

  </div>
</div>

</body>
</html>
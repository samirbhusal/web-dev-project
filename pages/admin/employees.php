<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

// Handle delete
if (isset($_POST['delete'])) {
    $id = $_POST['employee_id'];
    mysqli_query($dbc, "DELETE FROM users WHERE user_id='$id'");
}

// Get all employees
$result = mysqli_query($dbc, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>All Employees — FuelTrack Pro</title>
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
    .btn { display: inline-block; padding: 8px 16px; background: #065A82; color: #fff; border-radius: 4px; text-decoration: none; font-size: 13px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #ddd; }
    th { background: #e8f1fb; padding: 9px 12px; text-align: left; font-size: 12px; }
    td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #eee; }
    .btn-sm { padding: 4px 10px; font-size: 12px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-edit { background: #e8f1fb; color: #065A82; }
    .btn-delete { background: #fee; color: #c00; }
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
    <a href="employees.php" class="active">All Employees</a>
    <a href="add_employee.php">Add Employee</a>
    <div class="grp">Attendance</div>
    <a href="time_entries.php">Time Entries</a>
    <div class="grp">Payroll</div>
    <a href="payroll.php">Generate Payroll</a>

  </div>

  <div class="main">
    <h2>All Employees</h2>
  

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Hourly Rate</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= $row['name'] ?></td>
          <td><?= $row['email'] ?></td>
          <td><?= ucfirst($row['role']) ?></td>
          <td>$<?= number_format($row['hourly_rate'], 2) ?></td>
          <td><?= ucfirst($row['status']) ?></td>
          <td>
            <a href="edit_employee.php?id=<?= $row['user_id'] ?>" class="btn-sm btn-edit">Edit</a>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="employee_id" value="<?= $row['user_id'] ?>"/>
              <button type="submit" name="delete" class="btn-sm btn-delete"
                onclick="return confirm('Delete this employee?')">Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </div>
</div>

</body>
</html>
<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

// Get all time entries with employee name
$result = mysqli_query($dbc, "
    SELECT te.*, e.name
    FROM time_entries te
    JOIN users e ON te.user_id = e.user_id
    ORDER BY te.clock_in DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Time Entries - FuelTrack Pro</title>
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
    table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #ddd; }
    th { background: #e8f1fb; padding: 9px 12px; text-align: left; font-size: 12px; }
    td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #eee; }
    .empty { text-align: center; padding: 20px; color: #666; }
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
    <a href="add_employee.php">Add Employee</a>
    <div class="grp">Attendance</div>
    <a href="time_entries.php" class="active">Time Entries</a>
    <div class="grp">Payroll</div>
    <a href="payroll.php">Generate Payroll</a>
  </div>

  <div class="main">
    <h2>Time Entries</h2>

    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Employee</th>
          <th>Clock In</th>
          <th>Clock Out</th>
          <th>Total Hours</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (mysqli_num_rows($result) === 0): ?>
          <tr><td colspan="6" class="empty">No time entries found.</td></tr>
        <?php else: ?>
          <?php $i = 1; while ($row = mysqli_fetch_assoc($result)): ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['clock_in'] ?></td>
            <td><?= $row['clock_out'] ? $row['clock_out'] : '—' ?></td>
            <td><?= $row['total_hours'] ? $row['total_hours'] . ' hrs' : '—' ?></td>
            <td><?= ucfirst($row['status']) ?></td>
          </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>

  </div>
</div>

</body>
</html>
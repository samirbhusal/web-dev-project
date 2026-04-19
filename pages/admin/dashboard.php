<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

// Clocked in count
$result = mysqli_query($dbc, "SELECT COUNT(*) as total FROM time_entries WHERE status='open'");
$clocked_in = mysqli_fetch_assoc($result)['total'];

// Total active employees
$result = mysqli_query($dbc, "SELECT COUNT(*) as total FROM users WHERE status='active' and role != 'admin'");
$total_emp = mysqli_fetch_assoc($result)['total'];

// Today's shifts
$result = mysqli_query($dbc, "SELECT COUNT(*) as total FROM time_entries WHERE DATE(clock_in) = CURDATE()");
$today_shifts = mysqli_fetch_assoc($result)['total'];

// Live attendance list
$live = mysqli_query($dbc, "
    SELECT e.name, e.role, te.clock_in
    FROM time_entries te
    JOIN users e ON te.user_id = e.user_id
    WHERE te.status = 'open'
");
?>
<!DOCTYPE html>
<html>

<head>
  <title>Admin Dashboard — FuelTrack Pro</title>
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

    .stats {
      display: flex;
      gap: 14px;
      margin-bottom: 18px;
    }

    .stat {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 14px 18px;
      flex: 1;
    }

    .stat .num {
      font-size: 26px;
      font-weight: bold;
      color: #1A3A5C;
    }

    .stat .lbl {
      font-size: 12px;
      color: #666;
    }

    .btn {
      display: inline-block;
      padding: 8px 16px;
      background: #065A82;
      color: #fff;
      border-radius: 4px;
      text-decoration: none;
      font-size: 13px;
      margin-bottom: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border: 1px solid #ddd;
    }

    th {
      background: #e8f1fb;
      padding: 9px 12px;
      text-align: left;
      font-size: 12px;
    }

    td {
      padding: 9px 12px;
      font-size: 13px;
      border-bottom: 1px solid #eee;
    }

    .empty {
      text-align: center;
      padding: 20px;
      color: #666;
      font-size: 13px;
    }
  </style>
</head>

<body>

  <div class="topbar">
    <strong>FuelTrack Pro - Admin</strong>
    <span><a href="../logout.php">Logout</a></span>
  </div>

  <div class="wrapper">
    <div class="sidebar">
      <div class="grp">Main</div>
      <a href="dashboard.php" class="active">Dashboard</a>
      <div class="grp">Employees</div>
      <a href="employees.php">All Employees</a>
      <a href="add_employee.php">Add Employee</a>
      <div class="grp">Attendance</div>
      <a href="time_entries.php">Time Entries</a>
      <div class="grp">Payroll</div>
      <a href="payroll.php">Generate Payroll</a>
    </div>

    <div class="main">
      <h2>Admin Dashboard</h2>

      <div class="stats">
        <div class="stat">
          <div class="num"><?= $clocked_in ?></div>
          <div class="lbl">Clocked In Now</div>
        </div>
        <div class="stat">
          <div class="num"><?= $total_emp ?></div>
          <div class="lbl">Active Employees</div>
        </div>
        <div class="stat">
          <div class="num"><?= $today_shifts ?></div>
          <div class="lbl">Shifts Today</div>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Role</th>
            <th>Clocked In At</th>
          </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($live) === 0): ?>
            <tr>
              <td colspan="3" class="empty">No employees currently clocked in.</td>
            </tr>
            <?php else: ?>
              <?php while ($row = mysqli_fetch_assoc($live)): ?>
              <tr>
                <td><?= $row['name'] ?></td>
                <td><?= ucfirst($row['role']) ?></td>
                <td><?= $row['clock_in'] ?></td>
              </tr>
              <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
      </table>

    </div>
  </div>

</body>

</html>
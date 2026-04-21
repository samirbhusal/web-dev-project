<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireAdmin();

$results      = [];
$error        = '';
$period_start = '';
$period_end   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $period_start = $_POST['period_start'];
  $period_end   = $_POST['period_end'];

  if (empty($period_start) || empty($period_end)) {
    $error = 'Please select both dates.';
  } else {
    $emp_result = mysqli_query($dbc, "SELECT * FROM users WHERE status='active' AND role != 'admin'");

    while ($emp = mysqli_fetch_assoc($emp_result)) {
      $hrs_result = mysqli_query($dbc, "
                SELECT SUM(total_hours) as total
                FROM time_entries
                WHERE user_id='{$emp['user_id']}'
                AND status='closed'
                AND DATE(clock_in) BETWEEN '$period_start' AND '$period_end'
            ");
      $hrs         = mysqli_fetch_assoc($hrs_result);
      $total_hours = round($hrs['total'], 2);

      if ($total_hours > 0) {
        $regular_hrs  = min($total_hours, 40);
        $overtime_hrs = max($total_hours - 40, 0);
        $gross_pay    = round(($regular_hrs * $emp['hourly_rate']) + ($overtime_hrs * $emp['hourly_rate'] * 1.5), 2);
        $deductions   = round($gross_pay * 0.10, 2);
        $net_pay      = round($gross_pay - $deductions, 2);

        $results[] = [
          'name'         => $emp['name'],
          'hourly_rate'  => $emp['hourly_rate'],
          'total_hours'  => $total_hours,
          'regular_hrs'  => $regular_hrs,
          'overtime_hrs' => $overtime_hrs,
          'gross_pay'    => $gross_pay,
          'deductions'   => $deductions,
          'net_pay'      => $net_pay,
        ];
      }
    }

    if (empty($results)) {
      $error = 'No closed time entries found for this period.';
    }
  }
}
?>
<!DOCTYPE html>
<html>

<head>
  <title>Payroll — FuelTrack Pro</title>
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

    .form-box {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 16px;
      margin-bottom: 20px;
      display: flex;
      gap: 12px;
      align-items: flex-end;
      flex-wrap: wrap;
    }

    label {
      display: block;
      font-size: 13px;
      margin-bottom: 3px;
    }

    input[type="date"] {
      padding: 7px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 13px;
    }

    input[type="submit"] {
      padding: 8px 16px;
      background: #065A82;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
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

    .total-row td {
      font-weight: bold;
      background: #e8f1fb;
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
      <a href="dashboard.php">Dashboard</a>
      <div class="grp">Employees</div>
      <a href="employees.php">All Employees</a>
      <a href="add_employee.php">Add Employee</a>
      <div class="grp">Attendance</div>
      <a href="time_entries.php">Time Entries</a>
      <div class="grp">Payroll</div>
      <a href="payroll.php" class="active">Generate Payroll</a>
    </div>

    <div class="main">
      <h2>Generate Payroll</h2>

      <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

      <div class="form-box">
        <form method="POST">
          <div>
            <label>Period Start</label>
            <input type="date" name="period_start" value="<?= $period_start ?>" required />
          </div>
          <div>
            <label>Period End</label>
            <input type="date" name="period_end" value="<?= $period_end ?>" required />
          </div>
          <div>
            <label>&nbsp;</label>
            <input type="submit" value="Calculate" />
          </div>
        </form>
      </div>

      <?php if (!empty($results)): ?>
        <table>
          <thead>
            <tr>
              <th>Employee</th>
              <th>Rate/hr</th>
              <th>Total Hrs</th>
              <!-- <th>Regular Hrs</th> -->
              <!-- <th>OT Hrs</th> -->
              <th>Gross Pay</th>
              <!-- <th>Deductions (10%)</th> -->
              <!-- <th>Net Pay</th> -->
            </tr>
          </thead>
          <tbody>
            <?php
            $grand_gross = 0;
            $grand_net   = 0;
            foreach ($results as $row):
              $grand_gross += $row['gross_pay'];
              $grand_net   += $row['net_pay'];
            ?>
              <tr>
                <td><?= $row['name'] ?></td>
                <td>$<?= $row['hourly_rate'] ?></td>
                <td><?= $row['total_hours'] ?></td>
                <!-- <td><?= $row['regular_hrs'] ?></td> -->
                <!-- <td><?= $row['overtime_hrs'] ?></td> -->
                <td>$<?= $row['gross_pay'] ?></td>
                <!-- <td>$<?= $row['deductions'] ?></td> -->
                <!-- <td><strong>$<?= $row['net_pay'] ?></strong></td> -->
              </tr>
            <?php endforeach; ?>
            <tr class="total-row">
              <td colspan="3">Total</td>
              <td>$<?= $grand_gross ?></td>
              <!-- <td></td> -->
              <!-- <td>$<?= $grand_net ?></td> -->
            </tr>
          </tbody>
        </table>
      <?php endif; ?>

    </div>
  </div>

</body>

</html>
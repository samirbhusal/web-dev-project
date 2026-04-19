<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/app.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Employee';
$userEmail = $_SESSION['email'] ?? '';

// Fetch the user's hourly rate from the database
$userQuery = mysqli_query($dbc, "SELECT hourly_rate FROM users WHERE user_id='$userId'");
$userResult = mysqli_fetch_assoc($userQuery);
$hourlyRate = $userResult['hourly_rate'] ?? 0.00;

// Get the earliest year the user has an entry, defaulting to current year
$yearQuery = mysqli_query($dbc, "SELECT MIN(YEAR(clock_in)) as min_year FROM time_entries WHERE user_id = '$userId'");
$yearRow = mysqli_fetch_assoc($yearQuery);
$minYear = $yearRow['min_year'] ? (int) $yearRow['min_year'] : (int) date('Y');
$currentYear = (int) date('Y');

$selectedYear  = $_POST['period_year'] ?? date('Y');
$selectedMonth = $_POST['period_month'] ?? date('m');

// Calculate total hours worked in the period
$hrsQuery = mysqli_query($dbc, "
    SELECT SUM(total_hours) as total
    FROM time_entries
    WHERE user_id='$userId'
    AND status='closed'
    AND YEAR(clock_in) = '$selectedYear'
    AND MONTH(clock_in) = '$selectedMonth'
");
$total_hours = round(mysqli_fetch_assoc($hrsQuery)['total'] ?? 0, 2);

// Calculate pay (Matching simple Hour * Rate logic)
$gross_pay = round($total_hours * $hourlyRate, 2);
$net_pay = round($gross_pay, 2);

// User initials
$parts = explode(' ', $userName);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1)
    $initials .= strtoupper(substr(end($parts), 0, 1));
$pageTitle = 'My Payslip — FuelTrack Pro';
require_once __DIR__ . '/../../includes/employee_header.php';
?>
<div class="page-wrapper">
    <div class="card" style="max-width: 900px;">

        <h1>My Payslip</h1>
        <p class="subtitle centered">View your earnings for <?php echo date('F Y', strtotime("$selectedYear-$selectedMonth-01")); ?></p>

        <form class="period-form" method="POST">
            <div class="form-group" style="flex: 1;">
                <label>Select Year</label>
                <select name="period_year" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; outline: none;">
                    <?php for ($y = $currentYear; $y >= $minYear; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $selectedYear ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Select Month</label>
                <select name="period_month" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #d1d5db; outline: none;">
                    <?php 
                    for ($m = 1; $m <= 12; $m++): 
                        $monthNum = str_pad($m, 2, '0', STR_PAD_LEFT);
                        $monthName = date('F', mktime(0, 0, 0, $m, 1));
                    ?>
                        <option value="<?php echo $monthNum; ?>" <?php echo $monthNum === $selectedMonth ? 'selected' : ''; ?>>
                            <?php echo $monthName; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="primary-btn">Calculate Payslip</button>
        </form>

        <div class="payslip-summary">
            <div class="summary-item">
                <span class="summary-value"><?php echo number_format($hourlyRate, 2); ?></span>
                <span class="summary-label">Hourly Rate ($)</span>
            </div>
            <div class="summary-item">
                <span class="summary-value"><?php echo number_format($total_hours, 2); ?></span>
                <span class="summary-label">Total Hours</span>
            </div>
            <div class="summary-item">
                <span class="summary-value"><?php echo number_format($gross_pay, 2); ?></span>
                <span class="summary-label">Gross Pay ($)</span>
            </div>
        </div>

    </div>
</div>
<?php require_once __DIR__ . '/../../includes/employee_footer.php'; ?>
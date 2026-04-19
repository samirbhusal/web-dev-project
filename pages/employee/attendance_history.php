<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/app.php';

requireLogin();

$userId    = (int) $_SESSION['user_id'];
$userName  = $_SESSION['name']  ?? 'Employee';
$userEmail = $_SESSION['email'] ?? '';

// Get the earliest year the user has an entry, defaulting to current year
$yearQuery = mysqli_query($dbc, "SELECT MIN(YEAR(clock_in)) as min_year FROM time_entries WHERE user_id = '$userId'");
$yearRow = mysqli_fetch_assoc($yearQuery);
$minYear = $yearRow['min_year'] ? (int) $yearRow['min_year'] : (int) date('Y');
$currentYear = (int) date('Y');

$selectedYear  = $_POST['period_year'] ?? date('Y');
$selectedMonth = $_POST['period_month'] ?? date('m');

// Fetch time_entries for the selected month and year
$result  = mysqli_query($dbc,
    "SELECT * FROM time_entries
     WHERE user_id = '$userId'
     AND YEAR(clock_in) = '$selectedYear'
     AND MONTH(clock_in) = '$selectedMonth'
     ORDER BY clock_in DESC"
);
$records = [];
while ($row = mysqli_fetch_assoc($result)) {
    $records[] = $row;
}

// Summary stats
$totalDays    = count($records);
$completeDays = 0;
$totalMinutes = 0;
$lateDays     = 0;

foreach ($records as $r) {
    if ($r['clock_out'] !== null) {
        $completeDays++;
        $totalMinutes += round($r['total_hours'] * 60);
    }
    if (!empty($r['late_reason'])) $lateDays++;
}

$totalHoursAll = intdiv($totalMinutes, 60);
$totalMinsRem  = $totalMinutes % 60;

// User initials
$parts    = explode(' ', $userName);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
$pageTitle = 'Attendance History — FuelTrack Pro';
require_once __DIR__ . '/../../includes/employee_header.php';
?>
<div class="page-wrapper">
        <div class="card attendance-history-card">

            <h1>Attendance History</h1>
            <p class="subtitle centered">Viewing records for <?php echo date('F Y', strtotime("$selectedYear-$selectedMonth-01")); ?></p>

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
                <button type="submit" class="primary-btn">Filter</button>
            </form>

            <?php if (empty($records)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <p>No attendance records yet.</p>
                </div>
            <?php else: ?>

                <!-- Summary cards -->
                <div class="history-summary">
                    <div class="summary-item">
                        <span class="summary-value"><?php echo $totalDays; ?></span>
                        <span class="summary-label">Total Days</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-value"><?php echo $completeDays; ?></span>
                        <span class="summary-label">Complete Shifts</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-value"><?php echo $totalHoursAll . 'h ' . $totalMinsRem . 'm'; ?></span>
                        <span class="summary-label">Total Hours</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-value"><?php echo $lateDays; ?></span>
                        <span class="summary-label">Late Check-ins</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Hours Worked</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $r):
                                $checkInDt  = new DateTime($r['clock_in']);
                                $date       = $checkInDt->format('M j, Y');
                                $checkIn    = $checkInDt->format('g:i A');
                                $checkOut   = '—';
                                $worked     = '—';
                                $isComplete = $r['clock_out'] !== null;

                                if ($isComplete) {
                                    $checkOutDt   = new DateTime($r['clock_out']);
                                    $checkOut     = $checkOutDt->format('g:i A');
                                    $mins         = round($r['total_hours'] * 60);
                                    $worked       = intdiv($mins, 60) . 'h ' . ($mins % 60) . 'm';
                                }
                            ?>
                            <tr>
                                <td data-label="Date">
                                    <span class="date-cell"><?php echo e($date); ?></span>
                                </td>
                                <td data-label="Check In">
                                    <?php echo e($checkIn); ?>
                                    <?php if (!empty($r['late_reason'])): ?>
                                        <span class="late-indicator" title="<?php echo e($r['late_reason']); ?>">Late</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Check Out"><?php echo e($checkOut); ?></td>
                                <td data-label="Hours Worked">
                                    <span class="worked-hours"><?php echo e($worked); ?></span>
                                </td>
                                <td data-label="Status">
                                    <?php if ($isComplete): ?>
                                        <span class="status-pill status-pill-complete">Complete</span>
                                    <?php else: ?>
                                        <span class="status-pill status-pill-active">In Progress</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>
<?php require_once __DIR__ . '/../../includes/employee_footer.php'; ?>
<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/app.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['name'] ?? 'Employee';
$userEmail = $_SESSION['email'] ?? '';

/* ── Shift config */
define('SHIFT_START_HOUR', 9);
define('SHIFT_START_MINUTE', 0);
define('GRACE_MINUTES', 15);

/* ── Handle Clock In */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'checkin') {
        // Check not already clocked in
        $check = mysqli_query($dbc, "SELECT entry_id FROM time_entries WHERE user_id='$userId' AND status='open' LIMIT 1");
        if (mysqli_num_rows($check) === 0) {
            $lateReason = trim($_POST['late_reason'] ?? '');
            $lateReasonSafe = mysqli_real_escape_string($dbc, $lateReason);
            $hasLateReasonColumn = mysqli_num_rows(mysqli_query($dbc, "SHOW COLUMNS FROM time_entries LIKE 'late_reason'")) > 0;

            if ($hasLateReasonColumn) {
                mysqli_query($dbc, "INSERT INTO time_entries (user_id, clock_in, late_reason, status)
                                    VALUES ('$userId', NOW(), '$lateReasonSafe', 'open')");
            } else {
                mysqli_query($dbc, "INSERT INTO time_entries (user_id, clock_in, status)
                                    VALUES ('$userId', NOW(), 'open')");
            }
        }
        header('Location: dashboard.php?success=checkin');
        exit;
    }

    if ($_POST['action'] === 'checkout') {
        $open = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT * FROM time_entries WHERE user_id='$userId' AND status='open' LIMIT 1"));
        if ($open) {
            $entryId = $open['entry_id'];
            $totalHours = round((time() - strtotime($open['clock_in'])) / 3600, 2);
            mysqli_query($dbc, "UPDATE time_entries SET clock_out=NOW(), total_hours='$totalHours', status='closed' WHERE entry_id='$entryId'");
        }
        header('Location: dashboard.php?success=checkout');
        exit;
    }
}

/* ── Load attendance state */
$openAttendance = mysqli_fetch_assoc(mysqli_query(
    $dbc,
    "SELECT * FROM time_entries
     WHERE user_id='$userId' AND status='open'
     ORDER BY clock_in DESC
     LIMIT 1"
));

$todayAttendance = mysqli_fetch_assoc(mysqli_query(
    $dbc,
    "SELECT * FROM time_entries
     WHERE user_id='$userId' AND DATE(clock_in) = CURDATE()
     ORDER BY clock_in DESC
     LIMIT 1"
));

$attendance = $openAttendance ?: $todayAttendance;
$hasCheckedIn = $attendance !== null;
$hasCheckedOut = $todayAttendance !== null && $todayAttendance['clock_out'] !== null;

/* ── Late check-in logic */
$now = new DateTime();
$deadline = new DateTime();
$deadline->setTime(SHIFT_START_HOUR, SHIFT_START_MINUTE + GRACE_MINUTES);
$isLate = ($now > $deadline);
$deadlineFmt = $deadline->format('g:i A');

$userIP = $_SERVER['REMOTE_ADDR'];
$isOnNetwork = in_array($userIP, ALLOWED_NETWORKS);

/* ── Format display times */
$checkInTime = $hasCheckedIn ? date('g:i A', strtotime($attendance['clock_in'])) : null;
$checkOutTime = $hasCheckedOut ? date('g:i A', strtotime($attendance['clock_out'])) : null;

/* ── User initials for avatar */
$parts = explode(' ', $userName);
$initials = strtoupper(substr($parts[0], 0, 1));
if (count($parts) > 1)
    $initials .= strtoupper(substr(end($parts), 0, 1));

$success = $_GET['success'] ?? '';
require_once __DIR__ . '/../../includes/employee_header.php';
?>
<div class="page-wrapper">
    <div class="card attendance-card">

        <!-- Greeting + Clock -->
        <div class="dashboard-greeting">
            <h1>Welcome, <?php echo e($userName); ?></h1>
            <p class="subtitle centered"><?php echo date('l, F j, Y'); ?></p>
            <div class="live-clock" id="liveClock"><?php echo date('h:i:s A'); ?></div>
        </div>

        <!-- Success alert -->
        <?php if ($success === 'checkin'): ?>
            <div class="alert success">You have successfully checked in!</div>
        <?php elseif ($success === 'checkout'): ?>
            <div class="alert success">You have successfully checked out. See you next time!</div>
        <?php endif; ?>

        <!-- Action area -->
        <div class="action-area">

            <?php if (!$isOnNetwork && !$hasCheckedIn): ?>
                <!-- Not on network error -->
                <div class="alert error" style="margin-bottom: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        style="vertical-align: middle; margin-bottom: 4px; margin-right: 6px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <circle cx="12" cy="11" r="3" />
                    </svg>
                    <strong>Access Denied:</strong> You must be connected to the company's network to Check In.
                </div>

            <?php elseif (!$hasCheckedIn): ?>
                <!-- CHECK-IN FORM -->
                <form class="attendance-form" method="post">
                    <input type="hidden" name="action" value="checkin">

                    <?php if ($isLate): ?>
                        <div class="reason-field show" id="reasonField">
                            <div class="late-notice">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                <span>You are checking in after <?php echo e($deadlineFmt); ?>. A reason is
                                    required.</span>
                            </div>
                            <div class="checkin-row">
                                <div class="form-group">
                                    <label for="lateReason">Reason:</label>
                                    <textarea id="lateReason" name="late_reason" rows="2"
                                        placeholder="Reason for late check-in..." required></textarea>
                                    <small class="field-error" id="reasonError"></small>
                                </div>
                                <button type="submit" class="action-btn checkin-btn">Check In</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <button type="submit" class="action-btn checkin-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            Check In
                        </button>
                    <?php endif; ?>
                </form>

            <?php elseif ($hasCheckedIn && !$hasCheckedOut): ?>
                <!-- CHECK-OUT FORM -->
                <form class="attendance-form" method="post">
                    <input type="hidden" name="action" value="checkout">
                    <div class="reason-field show">
                        <?php if (!empty($attendance['late_reason'] ?? '')): ?>
                            <div class="late-notice">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                </svg>
                                <span>You checked in late. Reason: <?php echo e($attendance['late_reason'] ?? ''); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="checkin-row">
                            <div class="form-group">
                                <label>Checked in at:</label>
                                <textarea readonly><?php echo e($checkInTime); ?></textarea>
                            </div>
                            <button type="submit" class="action-btn checkout-btn">Check Out</button>
                        </div>
                    </div>
                </form>

            <?php else: ?>
                <!-- SHIFT COMPLETE -->
                <div class="shift-complete-msg">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#067647" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <p>Your shift is complete for today. See you tomorrow!</p>
                </div>
            <?php endif; ?>

        </div><!-- /action-area -->

        <!-- Today's Activity Log -->
        <?php if ($hasCheckedIn): ?>
            <div class="attendance-log">
                <h3>Today's Activity</h3>
                <div class="log-entries">
                    <div class="log-entry">
                        <div class="log-icon checkin-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                        <div class="log-details">
                            <span class="log-label">Checked In</span>
                            <span class="log-time"><?php echo e($checkInTime); ?></span>
                        </div>
                    </div>

                    <?php if (!empty($attendance['late_reason'] ?? '')): ?>
                        <div class="log-entry">
                            <div class="log-icon late-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                </svg>
                            </div>
                            <div class="log-details">
                                <span class="log-label">Late Reason</span>
                                <span class="log-reason"><?php echo e($attendance['late_reason'] ?? ''); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($hasCheckedOut): ?>
                        <div class="log-entry">
                            <div class="log-icon checkout-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                            </div>
                            <div class="log-details">
                                <span class="log-label">Checked Out</span>
                                <span class="log-time"><?php echo e($checkOutTime); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
</div>

<script>
    // Live clock
    function tick() {
        const el = document.getElementById('liveClock');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }
    setInterval(tick, 1000);
</script>
<?php require_once __DIR__ . '/../../includes/employee_footer.php'; ?>
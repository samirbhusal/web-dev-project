<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <a href="<?php echo BASE_PATH; ?>/pages/employee/dashboard.php"
            class="sidebar-item <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
            </svg>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo BASE_PATH; ?>/pages/employee/attendance_history.php"
            class="sidebar-item <?php echo $currentPage === 'attendance_history.php' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            <span>Attendance History</span>
        </a>
        <a href="<?php echo BASE_PATH; ?>/pages/employee/payslip.php"
            class="sidebar-item <?php echo $currentPage === 'payslip.php' ? 'active' : ''; ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="2" ry="2" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
            <span>My Payslip</span>
        </a>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    // Hamburger + sidebar menu logic
    const hamburger = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        hamburger.classList.toggle('active');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    }

    if (hamburger) hamburger.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);

    // Profile dropdown logic
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileBtn) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => profileDropdown.classList.remove('open'));
    }
</script>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Backdrop -->
    <div class="mobile-drawer-backdrop" id="admin-menu-backdrop" onclick="toggleAdminMenu()"></div>
    
    <!-- Mobile Drawer -->
    <aside class="mobile-drawer" id="admin-mobile-menu">
        <div class="mobile-drawer-header">
            <div class="mobile-drawer-brand">
                <div class="sidebar-logo-icon" style="width:36px;height:36px;font-size:0.9rem;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <span style="font-weight:700;color:var(--green-700);font-size:0.95rem;">Admin Menu</span>
            </div>
            <button class="mobile-drawer-close" onclick="toggleAdminMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mobile-drawer-nav">
            <div class="mobile-drawer-label">Main</div>
            <a href="dashboard.php" class="mobile-drawer-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            
            <div class="mobile-drawer-label">Management</div>
            <a href="view_students.php" class="mobile-drawer-link <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['view_students.php', 'manage_students.php'])) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Students
            </a>
            <a href="manage_sections.php" class="mobile-drawer-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_sections.php') ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i> Sections
            </a>
            
            <div class="mobile-drawer-label">Attendance</div>
            <a href="manual_attendance.php" class="mobile-drawer-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manual_attendance.php') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-check"></i> Manual Entry
            </a>
            <a href="attendance_reports_sections.php" class="mobile-drawer-link <?php echo (basename($_SERVER['PHP_SELF']) == 'attendance_reports_sections.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            
            <hr class="mobile-drawer-divider">
            <div class="mobile-drawer-label">Quick Actions</div>
            <a href="../scan_attendance.php" class="mobile-drawer-link" target="_blank">
                <i class="fas fa-qrcode"></i> QR Scanner
            </a>
            <a href="../index.php" class="mobile-drawer-link" target="_blank">
                <i class="fas fa-globe"></i> View Site
            </a>
            <a href="logout.php" class="mobile-drawer-link mobile-drawer-link-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>

    <script>
        function toggleAdminMenu() {
            document.getElementById('admin-mobile-menu').classList.toggle('active');
            document.getElementById('admin-menu-backdrop').classList.toggle('active');
        }
    </script>

    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

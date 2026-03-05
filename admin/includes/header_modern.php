<?php
// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#059669">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin - ASJ AttendEase</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bento Glass Design System -->
    <link rel="stylesheet" href="../css/bento-glass.css">
    
    <!-- Modern Design CSS (legacy compat) -->
    <link rel="stylesheet" href="../css/modern-design.css">
    
    <!-- Admin Bento Layout CSS -->
    <link rel="stylesheet" href="../css/admin-bento.css">
    
    <!-- Chart.js for dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="bg-mesh">
    <!-- Decorative Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="admin-layout">
        <!-- Desktop Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="sidebar-logo-text">
                        <span class="sidebar-logo-title">AttendEase</span>
                        <span class="sidebar-logo-sub">Admin Panel</span>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="sidebar-nav-label">Main</div>
                <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <div class="sidebar-link-icon"><i class="fas fa-home"></i></div>
                    <span>Dashboard</span>
                </a>
                
                <div class="sidebar-nav-label">Management</div>
                <a href="view_students.php" class="sidebar-link <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['view_students.php', 'manage_students.php'])) ? 'active' : ''; ?>">
                    <div class="sidebar-link-icon"><i class="fas fa-users"></i></div>
                    <span>Students</span>
                </a>
                <a href="manage_sections.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manage_sections.php') ? 'active' : ''; ?>">
                    <div class="sidebar-link-icon"><i class="fas fa-layer-group"></i></div>
                    <span>Sections</span>
                </a>
                
                <div class="sidebar-nav-label">Attendance</div>
                <a href="manual_attendance.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'manual_attendance.php') ? 'active' : ''; ?>">
                    <div class="sidebar-link-icon"><i class="fas fa-clipboard-check"></i></div>
                    <span>Manual Entry</span>
                </a>
                <a href="attendance_reports_sections.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'attendance_reports_sections.php') ? 'active' : ''; ?>">
                    <div class="sidebar-link-icon"><i class="fas fa-chart-bar"></i></div>
                    <span>Reports</span>
                </a>
                
                <div class="sidebar-nav-label">Quick Actions</div>
                <a href="../scan_attendance.php" class="sidebar-link" target="_blank">
                    <div class="sidebar-link-icon"><i class="fas fa-qrcode"></i></div>
                    <span>QR Scanner</span>
                </a>
                <a href="../index.php" class="sidebar-link" target="_blank">
                    <div class="sidebar-link-icon"><i class="fas fa-globe"></i></div>
                    <span>View Site</span>
                </a>
                <a href="logout.php" class="sidebar-link sidebar-link-danger">
                    <div class="sidebar-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <span>Logout</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-profile">
                    <div class="sidebar-avatar">
                        <?php echo isset($currentAdmin) ? strtoupper(substr($currentAdmin['username'], 0, 1)) : 'A'; ?>
                    </div>
                    <div class="sidebar-profile-info">
                        <span class="sidebar-profile-name"><?php echo isset($currentAdmin) ? sanitizeOutput($currentAdmin['username']) : 'Admin'; ?></span>
                        <span class="sidebar-profile-role"><?php echo isset($currentAdmin) ? sanitizeOutput($currentAdmin['role']) : 'Administrator'; ?></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="topbar-menu-btn" onclick="toggleAdminMenu()" aria-label="Toggle menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="topbar-title-group">
                        <h1 class="topbar-title">
                            <i class="fas fa-<?php echo isset($pageIcon) ? $pageIcon : 'home'; ?>"></i>
                            <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
                        </h1>
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-avatar">
                        <?php echo isset($currentAdmin) ? strtoupper(substr($currentAdmin['username'], 0, 1)) : 'A'; ?>
                    </div>
                </div>
            </header>

            <!-- Content Wrapper -->
            <div class="content-wrapper">

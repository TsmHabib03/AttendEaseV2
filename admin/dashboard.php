<?php
require_once 'config.php';
requireAdmin();

$currentAdmin = getCurrentAdmin();
$pageTitle = 'Dashboard';
$pageIcon = 'home';

/**
 * Fetch all dashboard data in optimized queries
 * Returns comprehensive dashboard statistics
 */
function getDashboardData($pdo) {
    $data = [];
    
    try {
        // 1. STAT CARDS DATA
        // Total students
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
        $data['totalStudents'] = (int)$stmt->fetch()['total'];
        
        // Today's attendance
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT lrn) as present
            FROM attendance 
            WHERE date = CURDATE() AND time_in IS NOT NULL
        ");
        $stmt->execute();
        $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['presentToday'] = (int)$todayStats['present'];
        
        // Absent students today
        $data['absentToday'] = $data['totalStudents'] - $data['presentToday'];
        
        // Today's attendance rate
        $data['attendanceRate'] = $data['totalStudents'] > 0 
            ? round(($data['presentToday'] / $data['totalStudents']) * 100, 1) 
            : 0;
        
        // 2. WEEKLY ATTENDANCE TREND (Last 7 days - Present vs Absent)
        $stmt = $pdo->prepare("
            WITH RECURSIVE dates AS (
                SELECT DATE_SUB(CURDATE(), INTERVAL 6 DAY) as date
                UNION ALL
                SELECT DATE_ADD(date, INTERVAL 1 DAY)
                FROM dates
                WHERE date < CURDATE()
            )
            SELECT 
                dates.date,
                COALESCE(COUNT(DISTINCT a.lrn), 0) as present,
                (SELECT COUNT(*) FROM students) - COALESCE(COUNT(DISTINCT a.lrn), 0) as absent
            FROM dates
            LEFT JOIN attendance a ON dates.date = a.date
            GROUP BY dates.date
            ORDER BY dates.date ASC
        ");
        $stmt->execute();
        $data['weeklyTrend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. ATTENDANCE BY SECTION (Today)
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(s.section, 'No Section') as section,
                COUNT(DISTINCT CASE WHEN a.date = CURDATE() AND a.time_in IS NOT NULL THEN a.lrn END) as present,
                COUNT(DISTINCT s.lrn) as total
            FROM students s
            LEFT JOIN attendance a ON s.lrn = a.lrn
            GROUP BY s.section
            HAVING total > 0
            ORDER BY section
        ");
        $stmt->execute();
        $data['sectionAttendance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. RECENT ACTIVITY (Last 10 records with time_out info)
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.lrn,
                s.first_name,
                s.last_name,
                COALESCE(s.section, 'N/A') as section,
                a.time_in,
                a.time_out,
                a.date,
                CASE 
                    WHEN a.time_out IS NULL AND a.date < CURDATE() THEN 'incomplete'
                    WHEN a.time_out IS NOT NULL THEN 'complete'
                    ELSE 'present'
                END as status,
                a.created_at
            FROM attendance a
            JOIN students s ON a.lrn = s.lrn
            ORDER BY a.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $data['recentActivity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. NEEDS ATTENTION (Incomplete attendance records)
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.lrn,
                s.first_name,
                s.last_name,
                COALESCE(s.section, 'N/A') as section,
                a.date,
                a.time_in,
                DATEDIFF(CURDATE(), a.date) as days_ago
            FROM attendance a
            JOIN students s ON a.lrn = s.lrn
            WHERE a.time_out IS NULL 
            AND a.date < CURDATE()
            ORDER BY a.date DESC
            LIMIT 15
        ");
        $stmt->execute();
        $data['needsAttention'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 6. ADDITIONAL STATS
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM attendance");
        $data['totalRecords'] = (int)$stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(DISTINCT COALESCE(section, 'default')) as total FROM students");
        $data['activeSections'] = (int)$stmt->fetch()['total'];
        
        return $data;
        
    } catch (Exception $e) {
        error_log("Dashboard data fetch error: " . $e->getMessage());
        // Return safe defaults
        return [
            'totalStudents' => 0,
            'presentToday' => 0,
            'absentToday' => 0,
            'attendanceRate' => 0,
            'weeklyTrend' => [],
            'sectionAttendance' => [],
            'recentActivity' => [],
            'needsAttention' => [],
            'totalRecords' => 0,
            'activeSections' => 0
        ];
    }
}

// Fetch all dashboard data
$dashboardData = getDashboardData($pdo);

// Extract for easy access
$totalStudents = $dashboardData['totalStudents'];
$presentToday = $dashboardData['presentToday'];
$absentToday = $dashboardData['absentToday'];
$attendanceRate = $dashboardData['attendanceRate'];
$totalRecords = $dashboardData['totalRecords'];
$activeSections = $dashboardData['activeSections'];
$recentAttendance = $dashboardData['recentActivity'];

// Include the modern admin header
include 'includes/header_modern.php';
?>

<!-- Dashboard Data (JSON) -->
<script>
    window.dashboardData = <?php echo json_encode($dashboardData); ?>;
</script>

<!-- Loading Overlay -->
<div class="dash-loader" id="dashboardLoader">
    <div class="dash-loader-content">
        <div class="dash-loader-spinner"></div>
        <p>Loading Dashboard...</p>
    </div>
</div>

<!-- Page Header — Glassmorphism Bento -->
<div class="page-header-glass">
    <div class="page-header-inner">
        <nav class="breadcrumb-glass" aria-label="Breadcrumb">
            <span class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-home"></i> Dashboard
            </span>
        </nav>
        <div class="page-header-content-glass">
            <div class="page-header-title-row">
                <div class="page-header-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="page-header-text">
                    <h1>Welcome back, <?php echo isset($currentAdmin) ? sanitizeOutput($currentAdmin['username']) : 'Admin'; ?></h1>
                    <p><i class="fas fa-calendar-day"></i> <?php echo date('l, F j, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards — Bento Grid -->
<div class="bento" style="margin-bottom: var(--sp-6);">
    <!-- Total Students -->
    <div class="dash-stat-card">
        <div class="dash-stat-icon dash-stat-icon-green">
            <i class="fas fa-users"></i>
        </div>
        <div class="dash-stat-value"><?php echo number_format($totalStudents); ?></div>
        <div class="dash-stat-label">Total Students</div>
        <div class="dash-stat-footer">
            <i class="fas fa-layer-group"></i> <?php echo $activeSections; ?> sections
        </div>
    </div>

    <!-- Present Today -->
    <div class="dash-stat-card">
        <div class="dash-stat-icon dash-stat-icon-green">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="dash-stat-value"><?php echo number_format($presentToday); ?></div>
        <div class="dash-stat-label">Present Today</div>
        <div class="dash-stat-footer">
            <i class="fas fa-arrow-up"></i> <?php echo $attendanceRate; ?>% rate
        </div>
    </div>

    <!-- Absent Today -->
    <div class="dash-stat-card">
        <div class="dash-stat-icon dash-stat-icon-amber">
            <i class="fas fa-user-xmark"></i>
        </div>
        <div class="dash-stat-value"><?php echo number_format($absentToday); ?></div>
        <div class="dash-stat-label">Absent Today</div>
        <div class="dash-stat-footer" style="color: #B45309;">
            <i class="fas fa-arrow-down"></i> <?php echo number_format(100 - $attendanceRate, 1); ?>% of total
        </div>
    </div>

    <!-- Total Records -->
    <div class="dash-stat-card">
        <div class="dash-stat-icon dash-stat-icon-purple">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="dash-stat-value"><?php echo number_format($totalRecords); ?></div>
        <div class="dash-stat-label">Total Records</div>
        <div class="dash-stat-footer" style="color: #6D28D9;">
            <i class="fas fa-database"></i> All time
        </div>
    </div>
</div>

<!-- Main Bento Grid -->
<div class="bento">
    <!-- Weekly Attendance Chart — spans 2 columns -->
    <div class="dash-card bento-span-2">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><i class="fas fa-chart-line"></i> Weekly Attendance Trend</h3>
        </div>
        <div class="dash-card-body">
            <div class="dash-chart-wrap">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="dash-card-body">
            <div class="dash-actions">
                <a href="manage_students.php" class="dash-action">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Student</span>
                </a>
                <a href="manual_attendance.php" class="dash-action">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Manual Entry</span>
                </a>
                <a href="../scan_attendance.php" class="dash-action" target="_blank">
                    <i class="fas fa-qrcode"></i>
                    <span>QR Scanner</span>
                </a>
                <a href="attendance_reports_sections.php" class="dash-action">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Section Chart -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><i class="fas fa-chart-pie"></i> Attendance by Section</h3>
        </div>
        <div class="dash-card-body">
            <div class="dash-chart-wrap" style="height: 260px;">
                <canvas id="sectionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Attendance — spans 2 columns -->
    <div class="dash-card bento-span-2">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><i class="fas fa-clock-rotate-left"></i> Recent Attendance</h3>
            <a href="attendance_reports_sections.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="dash-card-body-flush">
            <?php if (!empty($recentAttendance)): ?>
                <?php foreach ($recentAttendance as $record): ?>
                    <div class="dash-activity-item">
                        <div class="dash-activity-left">
                            <div class="dash-activity-avatar">
                                <?php echo strtoupper(substr($record['first_name'], 0, 1)); ?>
                            </div>
                            <div class="dash-activity-info">
                                <div class="dash-activity-name"><?php echo sanitizeOutput($record['first_name'] . ' ' . $record['last_name']); ?></div>
                                <div class="dash-activity-meta"><?php echo sanitizeOutput($record['section']); ?> &bull; In: <?php echo date('g:i A', strtotime($record['time_in'])); ?><?php echo $record['time_out'] ? ' &bull; Out: ' . date('g:i A', strtotime($record['time_out'])) : ''; ?></div>
                            </div>
                        </div>
                        <span class="dash-badge dash-badge-<?php echo $record['status'] === 'incomplete' ? 'warning' : ($record['status'] === 'complete' ? 'success' : 'primary'); ?>">
                            <?php echo $record['status'] === 'complete' ? 'Complete' : ($record['status'] === 'incomplete' ? 'Incomplete' : 'Present'); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="dash-empty">
                    <i class="fas fa-inbox"></i>
                    <p>No attendance records yet today</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Needs Attention -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title">
                <i class="fas fa-exclamation-triangle" style="color: #B45309;"></i>
                Needs Attention
            </h3>
            <a href="manual_attendance.php" class="btn btn-sm btn-outline">Fix Records</a>
        </div>
        <div class="dash-card-body-flush" style="max-height: 380px; overflow-y: auto;">
            <div id="needsAttentionList">
                <div class="dash-empty">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3 class="dash-card-title"><i class="fas fa-info-circle"></i> System Information</h3>
        </div>
        <div class="dash-card-body">
            <div class="dash-sysinfo-row">
                <span class="dash-sysinfo-label">Total Records</span>
                <span class="dash-sysinfo-value"><?php echo number_format($totalRecords); ?></span>
            </div>
            <div class="dash-sysinfo-row">
                <span class="dash-sysinfo-label">Active Sections</span>
                <span class="dash-sysinfo-value"><?php echo $activeSections; ?></span>
            </div>
            <div class="dash-sysinfo-row">
                <span class="dash-sysinfo-label">Last Updated</span>
                <span class="dash-sysinfo-value"><?php echo date('g:i A'); ?></span>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * AttendEase Dashboard - Interactive Data Visualization
 * Fully functional dashboard with real-time data and Chart.js integration
 */

(function() {
    'use strict';
    
    // Get dashboard data
    const data = window.dashboardData;
    
    if (!data) {
        console.error('Dashboard data not available');
        return;
    }
    
    // Chart instances
    let weeklyChart = null;
    let sectionChart = null;
    
    /**
     * Initialize Weekly Attendance Trend Chart
     * Bar chart showing Present vs Absent for last 7 days
     */
    function initWeeklyChart() {
        const ctx = document.getElementById('weeklyChart');
        if (!ctx) return;
        
        const weeklyData = data.weeklyTrend || [];
        
        // Prepare data
        const labels = weeklyData.map(day => {
            const date = new Date(day.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        
        const presentData = weeklyData.map(day => parseInt(day.present) || 0);
        const absentData = weeklyData.map(day => parseInt(day.absent) || 0);
        
        // Emerald Color Palette
        const asjColors = {
            green: {
                primary: '#10B981',
                light: 'rgba(16, 185, 129, 0.8)',
                lighter: 'rgba(16, 185, 129, 0.5)'
            },
            red: {
                primary: '#EF4444',
                light: 'rgba(239, 68, 68, 0.8)',
                lighter: 'rgba(239, 68, 68, 0.5)'
            }
        };
        
        // Create chart
        weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Present',
                        data: presentData,
                        backgroundColor: asjColors.green.light,
                        borderColor: asjColors.green.primary,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    },
                    {
                        label: 'Absent',
                        data: absentData,
                        backgroundColor: asjColors.red.light,
                        borderColor: asjColors.red.primary,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        bodySpacing: 6,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            title: function(context) {
                                return context[0].label || '';
                            },
                            label: function(context) {
                                const label = context.dataset.label || '';
                                const value = context.parsed.y || 0;
                                const total = presentData[context.dataIndex] + absentData[context.dataIndex];
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            precision: 0,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Initialize Section Attendance Chart
     * Donut chart showing today's attendance by section
     */
    function initSectionChart() {
        const ctx = document.getElementById('sectionChart');
        if (!ctx) return;
        
        const sectionData = data.sectionAttendance || [];
        
        // Prepare data - only show sections with present students today
        const labels = sectionData.map(s => s.section || 'Unknown');
        const presentData = sectionData.map(s => parseInt(s.present) || 0);
        const totalData = sectionData.map(s => parseInt(s.total) || 0);
        
        // Emerald palette for sections
        const asjSectionColors = [
            'rgba(16, 185, 129, 0.82)',   // Emerald 500
            'rgba(37, 99, 235, 0.78)',    // Blue 600
            'rgba(109, 40, 217, 0.78)',   // Violet 700
            'rgba(180, 83, 9, 0.78)',     // Amber 700
            'rgba(5, 150, 105, 0.82)',    // Emerald 600
            'rgba(190, 18, 60, 0.78)',    // Rose 700
            'rgba(4, 120, 87, 0.82)',     // Emerald 700
            'rgba(29, 78, 216, 0.78)',    // Blue 700
            'rgba(52, 211, 153, 0.78)',   // Emerald 400
            'rgba(124, 58, 237, 0.78)'    // Violet 600
        ];
        
        const borderColors = asjSectionColors.map(c => c.replace('0.8', '1'));
        
        // Create chart
        sectionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: presentData,
                    backgroundColor: asjSectionColors,
                    borderColor: borderColors,
                    borderWidth: 2,
                    hoverOffset: 10,
                    spacing: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 12,
                            font: {
                                size: 11,
                                weight: '600'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const total = totalData[i];
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
                                    return {
                                        text: `${label}: ${value}/${total} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor[i],
                                        lineWidth: 2,
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                        },
                        onClick: function(e, legendItem, legend) {
                            const index = legendItem.index;
                            const chart = legend.chart;
                            const meta = chart.getDatasetMeta(0);
                            
                            // Toggle visibility
                            meta.data[index].hidden = !meta.data[index].hidden;
                            chart.update();
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = totalData[context.dataIndex] || 0;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                const absent = total - value;
                                return [
                                    `Present: ${value} students`,
                                    `Absent: ${absent} students`,
                                    `Rate: ${percentage}%`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Populate Recent Activity List
     */
    function populateRecentActivity() {
        // Already populated by PHP, but we can add animations
        const items = document.querySelectorAll('.dash-activity-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 50);
        });
    }
    
    /**
     * Populate Needs Attention List
     */
    function populateNeedsAttention() {
        const container = document.getElementById('needsAttentionList');
        if (!container) return;
        
        const needsAttention = data.needsAttention || [];
        
        if (needsAttention.length === 0) {
            container.innerHTML = `
                <div class="dash-empty">
                    <i class="fas fa-check-circle"></i>
                    <p>All attendance records are complete!</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        needsAttention.forEach(record => {
            const daysAgo = parseInt(record.days_ago) || 0;
            const daysText = daysAgo === 1 ? '1 day ago' : `${daysAgo} days ago`;
            const date = new Date(record.date);
            const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const timeIn = record.time_in ? new Date(`2000-01-01 ${record.time_in}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }) : 'N/A';
            
            html += `
                <div class="dash-attention-item">
                    <div class="dash-attention-left">
                        <div class="dash-attention-icon">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <div class="dash-attention-info">
                            <h4>${escapeHtml(record.first_name)} ${escapeHtml(record.last_name)}</h4>
                            <p>${escapeHtml(record.section)} &bull; ${dateStr} &bull; In: ${timeIn} &bull; Missing Time Out</p>
                        </div>
                    </div>
                    <span class="dash-badge dash-badge-error">${daysText}</span>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Animate items
        const items = container.querySelectorAll('.dash-attention-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
            }, index * 50);
        });
    }
    
    /**
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Hide loading overlay
     */
    function hideLoader() {
        const loader = document.getElementById('dashboardLoader');
        if (loader) {
            setTimeout(() => {
                loader.classList.add('hidden');
            }, 500);
        }
    }
    
    /**
     * Initialize Dashboard
     */
    function init() {
        console.log('Initializing dashboard with data:', data);
        
        // Initialize charts
        initWeeklyChart();
        initSectionChart();
        
        // Populate lists
        populateRecentActivity();
        populateNeedsAttention();
        
        // Hide loader
        hideLoader();
        
        console.log('Dashboard initialization complete');
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(() => {
        console.log('Auto-refreshing dashboard...');
        window.location.reload();
    }, 5 * 60 * 1000);
    
})();
</script>

<?php include 'includes/footer_modern.php'; ?>

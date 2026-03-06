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
        /* ── Sidebar Collapse ────────────────────────────────────── */
        function toggleSidebarCollapse() {
            var layout = document.querySelector('.admin-layout');
            layout.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', layout.classList.contains('sidebar-collapsed'));
        }

        /* Restore collapsed state from localStorage */
        (function() {
            if (window.innerWidth >= 1024 && localStorage.getItem('sidebarCollapsed') === 'true') {
                document.querySelector('.admin-layout').classList.add('sidebar-collapsed');
            }
        })();

        /* ── Mobile Drawer / Desktop Collapse (combined) ─────── */
        function toggleAdminMenu() {
            if (window.innerWidth >= 1024) {
                toggleSidebarCollapse();
            } else {
                document.getElementById('admin-mobile-menu').classList.toggle('active');
                document.getElementById('admin-menu-backdrop').classList.toggle('active');
            }
        }

        /* ── Dropdown Helpers ────────────────────────────────────── */
        function closeAllDropdowns() {
            document.querySelectorAll('.topbar-dropdown, .topbar-notif-dropdown, .topbar-search-results').forEach(function(el) {
                el.classList.remove('active');
            });
            var userBtn = document.querySelector('.topbar-user-btn');
            if (userBtn) userBtn.setAttribute('aria-expanded', 'false');
        }

        function toggleUserDropdown(e) {
            e.stopPropagation();
            var dropdown = document.querySelector('.topbar-dropdown');
            var btn = document.querySelector('.topbar-user-btn');
            var isOpen = dropdown.classList.contains('active');
            closeAllDropdowns();
            if (!isOpen) {
                dropdown.classList.add('active');
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        function toggleNotifDropdown(e) {
            e.stopPropagation();
            var dropdown = document.querySelector('.topbar-notif-dropdown');
            var isOpen = dropdown.classList.contains('active');
            closeAllDropdowns();
            if (!isOpen) {
                dropdown.classList.add('active');
            }
        }

        /* Close dropdowns on outside click */
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.topbar-user-menu') &&
                !e.target.closest('.topbar-notif') &&
                !e.target.closest('.topbar-search')) {
                closeAllDropdowns();
            }
        });

        /* Escape key closes everything */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllDropdowns();
                var mobileMenu = document.getElementById('admin-mobile-menu');
                var backdrop = document.getElementById('admin-menu-backdrop');
                if (mobileMenu) mobileMenu.classList.remove('active');
                if (backdrop) backdrop.classList.remove('active');
            }
        });

        /* ── Page Search ─────────────────────────────────────────── */
        (function() {
            var searchInput = document.querySelector('.topbar-search-input');
            var searchResults = document.querySelector('.topbar-search-results');
            if (!searchInput || !searchResults) return;

            var pages = [
                { name: 'Dashboard',        icon: 'fa-home',            url: 'dashboard.php' },
                { name: 'Students',          icon: 'fa-users',           url: 'view_students.php' },
                { name: 'Manage Students',   icon: 'fa-user-edit',       url: 'manage_students.php' },
                { name: 'Sections',          icon: 'fa-layer-group',     url: 'manage_sections.php' },
                { name: 'Manual Entry',      icon: 'fa-clipboard-check', url: 'manual_attendance.php' },
                { name: 'Reports',           icon: 'fa-chart-bar',       url: 'attendance_reports_sections.php' },
                { name: 'QR Scanner',        icon: 'fa-qrcode',          url: '../scan_attendance.php' },
                { name: 'View Site',         icon: 'fa-globe',           url: '../index.php' }
            ];

            searchInput.addEventListener('input', function() {
                var query = this.value.toLowerCase().trim();
                if (!query) {
                    searchResults.classList.remove('active');
                    searchResults.innerHTML = '';
                    return;
                }
                var matches = pages.filter(function(p) {
                    return p.name.toLowerCase().indexOf(query) !== -1;
                });
                if (matches.length) {
                    searchResults.innerHTML = matches.map(function(p) {
                        var el = document.createElement('a');
                        el.href = p.url;
                        el.className = 'topbar-search-result-item';
                        el.setAttribute('role', 'option');
                        el.innerHTML = '<i class="fas ' + p.icon + '"></i><span>' + p.name + '</span>';
                        return el.outerHTML;
                    }).join('');
                } else {
                    searchResults.innerHTML = '<div class="topbar-search-no-results">No pages found</div>';
                }
                searchResults.classList.add('active');
            });

            searchInput.addEventListener('focus', function() {
                if (this.value.trim()) {
                    this.dispatchEvent(new Event('input'));
                }
            });
        })();

        /* ── Sidebar Tooltip (collapsed mode) ────────────────────── */
        (function() {
            var tooltip = null;

            function showTooltip(text, rect) {
                if (!tooltip) {
                    tooltip = document.createElement('div');
                    tooltip.className = 'sidebar-tooltip';
                    document.body.appendChild(tooltip);
                }
                tooltip.textContent = text;
                tooltip.style.top = (rect.top + rect.height / 2) + 'px';
                tooltip.style.left = (rect.right + 12) + 'px';
                tooltip.classList.add('visible');
            }

            function hideTooltip() {
                if (tooltip) tooltip.classList.remove('visible');
            }

            document.querySelectorAll('.admin-sidebar .sidebar-link').forEach(function(link) {
                var label = link.querySelector('span');
                if (!label) return;

                link.addEventListener('mouseenter', function() {
                    var layout = document.querySelector('.admin-layout');
                    if (!layout || !layout.classList.contains('sidebar-collapsed')) return;
                    if (window.innerWidth < 1024) return;
                    showTooltip(label.textContent, this.getBoundingClientRect());
                });

                link.addEventListener('mouseleave', hideTooltip);
            });
        })();
    </script>

    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

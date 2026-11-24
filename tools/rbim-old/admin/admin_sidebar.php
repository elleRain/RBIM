<?php
// Check if session is not started, start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in or not an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../login_page/index.php");
    exit;
}
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header" onclick="window.location.href='../admin/admin_dashboard.php'" style="cursor: pointer;">
        <div class="logo-container">
            <img src="../images/rbim_logo.jpg" alt="RBIM Logo" class="sidebar-logo">
            <h2>RBIM Admin</h2>
        </div>
    </div>
    <div class="sidebar-content">
        <div class="sidebar-divider"><span>Main Navigation</span></div>
        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="../admin/admin_dashboard.php" class="sidebar-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="../rbim_census_from/household_list.php" class="sidebar-link">
                        <i class="fas fa-list"></i>
                        <span>Household List</span>
                    </a>
                </li>
                <li>
                    <a href="../rbim_census_from/add_household.php" class="sidebar-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Household</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-divider"><span>System</span></div>
        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="#" class="sidebar-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="../login_page/logout.php" class="sidebar-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="sidebar-footer">
        <p>RBIM v2.0 &copy; <?php echo date('Y'); ?></p>
    </div>
</div>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    /* Sidebar Styles - Enhanced Professional Design */
    .sidebar {
        width: 200px;
        background-color: var(--white);
        color: var(--neutral-800);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        transition: all var(--transition-normal) var(--transition-ease);
        overflow-x: hidden;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--neutral-200);
    }
    
    body.sidebar-collapsed .sidebar {
        width: 60px;
    }
    
    /* Hover expansion for collapsed sidebar */
    body.sidebar-hover-expanded .sidebar {
        width: 200px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        z-index: 1001;
    }
    
    body.sidebar-hover-expanded .sidebar-header h2,
    body.sidebar-hover-expanded .sidebar-link span,
    body.sidebar-hover-expanded .sidebar-divider span,
    body.sidebar-hover-expanded .sidebar-footer p {
        opacity: 1;
        width: auto;
        height: auto;
        overflow: visible;
    }
    
    body.sidebar-hover-expanded .sidebar-logo {
        margin-right: 12px;
    }
    
    body.sidebar-hover-expanded .sidebar-header h2:after {
        display: block;
    }
    
    body.sidebar-hover-expanded .navbar {
        left: 260px;
    }
    
    /* Enhanced Header */
    .sidebar-header {
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 50px;
        background: linear-gradient(to right, var(--primary-light), var(--white));
        position: sticky;
        top: 0;
        z-index: 10;
        cursor: pointer;
        transition: all var(--transition-fast) var(--transition-ease);
        border-bottom: 1px solid var(--neutral-200);
    }
    
    .sidebar-toggle {

        border: none;
        color: var(--neutral-600);
        font-size: var(--font-size-md);
        cursor: pointer;
        padding: 8px;
        border-radius: var(--radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition-fast) var(--transition-ease);
        width: 30px;
        height: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .logo-container {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    transition: all var(--transition-normal) var(--transition-ease);
    width: 100%;
    height: 100%;
}
    
    .sidebar-logo {
        width: 28px;
        height: 28px;
        border-radius: var(--radius-md);
        object-fit: cover;
        margin-right: 10px;
        transition: all var(--transition-normal) var(--transition-ease);
        box-shadow: var(--shadow-sm);
    }
    
    body.sidebar-collapsed .sidebar-logo {
        margin-right: 0;
    }
    
    .sidebar-header h2 {
        margin: 0;
        font-size: var(--font-size-md);
        color: var(--neutral-800);
        font-weight: var(--font-weight-semibold);
        white-space: nowrap;
        transition: all var(--transition-normal) var(--transition-ease);
        position: relative;
    }
    
    .sidebar-header h2:after {
        content: '';
        display: block;
        width: 40px;
        height: 3px;
        background-color: var(--primary);
        margin-top: 5px;
        border-radius: 3px;
    }
    
    body.sidebar-collapsed .sidebar-header h2 {
    opacity: 0;
    width: 0;
    height: 0;
    overflow: hidden;
}
    
body.sidebar-collapsed .sidebar-header h2:after {
    display: none;
}
    
    /* User Profile Section */
    .user-profile {
        display: flex;
        align-items: center;
        padding: 16px;
        background-color: var(--neutral-50);
        transition: all var(--transition-normal) var(--transition-ease);
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
    }
    
    .user-avatar i {
        font-size: 20px;
        color: var(--primary);
    }
    
    .user-info {
        overflow: hidden;
        transition: all var(--transition-normal) var(--transition-ease);
    }
    
    .user-name {
        margin: 0;
        font-weight: var(--font-weight-semibold);
        font-size: var(--font-size-sm);
        color: var(--neutral-900);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .user-role {
        margin: 0;
        font-size: var(--font-size-xs);
        color: var(--neutral-600);
    }
    
    body.sidebar-collapsed .user-info {
        opacity: 0;
        width: 0;
        height: 0;
        overflow: hidden;
    }
    
    /* Dividers */
    .sidebar-divider {
        padding: 0 16px;
        margin: 16px 0 8px;
        display: flex;
        align-items: center;
        color: var(--neutral-500);
        font-size: var(--font-size-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .sidebar-divider span {
        position: relative;
        padding-right: 10px;
    }
    
    .sidebar-divider span:after {
        content: '';
        position: absolute;
        left: 100%;
        top: 50%;
        width: 100%;
        height: 1px;
        background: transparent;
    }
    
    body.sidebar-collapsed .sidebar-divider {
        height: 1px;
        background: transparent;
        margin: 10px 5px;
        padding: 0;
    }
    
    body.sidebar-collapsed .sidebar-divider span {
        display: none;
    }
    
    /* Enhanced Menu */
    .sidebar-content {
        padding: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .sidebar-menu {
        margin-bottom: 10px;
        padding: 8px 0;
    }
    
    .sidebar-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-menu li {
        margin: 2px 0;
        position: relative;
    }
    
    .sidebar-link {
        color: var(--neutral-700);
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 10px 20px;
        border-left: 3px solid transparent;
        transition: all var(--transition-fast) var(--transition-ease);
        font-weight: var(--font-weight-medium);
        position: relative;
    }
    
    .sidebar-link:hover, .sidebar-link.active {
        background-color: var(--primary-light);
        color: var(--primary-dark);
        border-left-color: var(--primary);
        font-weight: var(--font-weight-semibold);
    }
    
    .sidebar-link i {
        font-size: var(--font-size-sm);
        width: 16px;
        margin-right: 10px;
        text-align: center;
        color: var(--primary);
        transition: all var(--transition-fast) var(--transition-ease);
    }
    
    .sidebar-link:hover i, .sidebar-link.active i {
        color: var(--primary-dark);
    }
    
    .sidebar-link span {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all var(--transition-normal) var(--transition-ease);
    }
    
    body.sidebar-collapsed .sidebar-link span {
        opacity: 0;
        width: 0;
        height: 0;
        overflow: hidden;
    }
    
    /* Dropdown Styles */
    .dropdown-icon {
        font-size: var(--font-size-xs);
        margin-left: 5px;
        transition: transform var(--transition-fast) var(--transition-ease);
    }
    
    .sidebar-dropdown.open .dropdown-icon {
        transform: rotate(90deg);
    }
    
    .sidebar-submenu {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height var(--transition-normal) var(--transition-ease);
        background-color: var(--neutral-50);
    }
    
    .sidebar-dropdown.open .sidebar-submenu {
        max-height: 500px;
    }
    
    .sidebar-sublink {
        color: var(--neutral-700);
        text-decoration: none;
        display: flex;
        align-items: center;
        padding: 8px 16px 8px 50px;
        transition: all var(--transition-fast) var(--transition-ease);
        font-weight: var(--font-weight-normal);
        font-size: var(--font-size-sm);
    }
    
    .sidebar-sublink:hover, .sidebar-sublink.active {
        background-color: var(--primary-light);
        color: var(--primary-dark);
    }
    
    .sidebar-sublink i {
        font-size: var(--font-size-sm);
        width: 20px;
        margin-right: 8px;
        text-align: center;
        color: var(--neutral-600);
    }
    
    .sidebar-sublink:hover i, .sidebar-sublink.active i {
        color: var(--primary);
    }
    
    body.sidebar-collapsed .sidebar-submenu {
        position: absolute;
        left: 70px;
        top: 0;
        width: 200px;
        max-height: none;
        box-shadow: var(--shadow-md);
        border-radius: var(--radius-md);
        z-index: 100;
        display: none;
    }
    
    body.sidebar-collapsed .sidebar-dropdown:hover .sidebar-submenu {
        display: block;
    }
    
    /* Footer */
    .sidebar-footer {
        padding: 12px 16px;
        background-color: var(--neutral-50);
        font-size: var(--font-size-xs);
        color: var(--neutral-600);
        text-align: center;
        transition: all var(--transition-normal) var(--transition-ease);
    }
    
    .sidebar-footer p {
        margin: 0;
    }
    
    body.sidebar-collapsed .sidebar-footer {
        padding: 12px 0;
    }
    
    body.sidebar-collapsed .sidebar-footer p {
        opacity: 0;
    }
    
    /* Main content adjustment */
    .main-content {
        margin-left: 200px;  /* Changed from 260px to match sidebar width */
        padding: 90px 20px 20px;
        min-height: 100vh;
        transition: margin-left var(--transition-normal) var(--transition-ease);
    }
    
    body.sidebar-collapsed .main-content {
        margin-left: 60px;  /* Changed from 70px to match collapsed sidebar width */
    }
    
    /* Responsive styles */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            box-shadow: none;
            width: 260px;
        }
        
        body.sidebar-open .sidebar {
            transform: translateX(0);
            box-shadow: var(--shadow-lg);
        }
        
        
        
        .main-content {
            margin-left: 0;
            padding-top: 90px;
        }
        
        body.sidebar-collapsed .main-content {
            margin-left: 0;
        }
    }
    
    /* Scrollbar Styling */
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background-color: var(--neutral-300);
        border-radius: var(--radius-full);
    }
    
    .sidebar::-webkit-scrollbar-thumb:hover {
        background-color: var(--neutral-400);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const body = document.body;
        
        // Load saved sidebar state
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            body.classList.add('sidebar-collapsed');
        }
        
        // Toggle sidebar when hamburger button is clicked
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                body.classList.toggle('sidebar-collapsed');
                
                // Save state to localStorage
                const isCollapsed = body.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
        }
        
        // Handle dropdown menus
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Get parent li element
                const parentLi = this.closest('.sidebar-dropdown');
                
                // Check if sidebar is collapsed on desktop
                if (window.innerWidth > 992 && document.body.classList.contains('sidebar-collapsed')) {
                    return; // Let CSS handle hover behavior
                }
                
                // Toggle open class
                parentLi.classList.toggle('open');
                
                // Close other open dropdowns
                const otherDropdowns = document.querySelectorAll('.sidebar-dropdown.open');
                otherDropdowns.forEach(dropdown => {
                    if (dropdown !== parentLi) {
                        dropdown.classList.remove('open');
                    }
                });
            });
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            
            if (window.innerWidth <= 992 && 
                document.body.classList.contains('sidebar-open') && 
                !sidebar.contains(event.target) && 
                event.target !== sidebarToggle) {
                document.body.classList.remove('sidebar-open');
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 992) {
                document.body.classList.remove('sidebar-collapsed');
            }
        });
        
        // Set active menu item based on current page
        const currentPage = window.location.pathname.split('/').pop();
        const allLinks = document.querySelectorAll('.sidebar-link, .sidebar-sublink');
        
        allLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.includes(currentPage)) {
                link.classList.add('active');
                
                // If it's a sublink, open its parent dropdown
                if (link.classList.contains('sidebar-sublink')) {
                    const parentDropdown = link.closest('.sidebar-dropdown');
                    if (parentDropdown) {
                        parentDropdown.classList.add('open');
                    }
                }
            }
        });
        
        // Enhanced tooltip functionality for collapsed sidebar with hover expansion
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        let tooltipElement = null;
        let hoverTimeout = null;
        
        function createTooltip(text, x, y) {
            if (document.body.classList.contains('sidebar-collapsed') && window.innerWidth > 992) {
                tooltipElement = document.createElement('div');
                tooltipElement.className = 'sidebar-tooltip';
                tooltipElement.textContent = text;
                tooltipElement.style.position = 'fixed';
                tooltipElement.style.left = x + 'px';
                tooltipElement.style.top = y + 'px';
                tooltipElement.style.backgroundColor = 'var(--neutral-800)';
                tooltipElement.style.color = 'var(--white)';
                tooltipElement.style.padding = '8px 12px';
                tooltipElement.style.borderRadius = 'var(--radius-md)';
                tooltipElement.style.fontSize = 'var(--font-size-sm)';
                tooltipElement.style.zIndex = '1000';
                tooltipElement.style.pointerEvents = 'none';
                tooltipElement.style.boxShadow = 'var(--shadow-lg)';
                tooltipElement.style.whiteSpace = 'nowrap';
                document.body.appendChild(tooltipElement);
            }
        }
        
        function removeTooltip() {
            if (tooltipElement) {
                document.body.removeChild(tooltipElement);
                tooltipElement = null;
            }
        }
        
        // Temporary expansion on hover for collapsed sidebar
        function expandSidebarTemporarily() {
            if (document.body.classList.contains('sidebar-collapsed') && window.innerWidth > 992) {
                document.body.classList.add('sidebar-hover-expanded');
            }
        }
        
        function collapseSidebarBack() {
            if (document.body.classList.contains('sidebar-hover-expanded')) {
                document.body.classList.remove('sidebar-hover-expanded');
            }
        }
        
        // Add hover functionality to sidebar
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.addEventListener('mouseenter', function() {
                if (hoverTimeout) clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(expandSidebarTemporarily, 300); // Delay expansion
            });
            
            sidebar.addEventListener('mouseleave', function() {
                if (hoverTimeout) clearTimeout(hoverTimeout);
                collapseSidebarBack();
            });
        }
        
        sidebarLinks.forEach(link => {
            if (!link.classList.contains('dropdown-toggle')) {
                link.addEventListener('mouseenter', function(e) {
                    const text = this.querySelector('span').textContent;
                    const rect = this.getBoundingClientRect();
                    createTooltip(text, rect.right + 10, rect.top + rect.height / 2 - 10);
                });
                
                link.addEventListener('mouseleave', removeTooltip);
            }
        });
    });
</script>
<?php
// Check if session is not started, start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in or not a super admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "super_admin") {
    header("location: ../login_page/index.php");
    exit;
}
?>
<link rel="stylesheet" href="../css/global-styles.css">
<div class="navbar">
    <div class="navbar-left">
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1>Super Admin Dashboard</h1>
    </div>
    <div class="navbar-right">
        <div class="user-avatar" id="userProfile">
            <?php echo strtoupper(substr($_SESSION["username"], 0, 1)); ?>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                <a href="../login_page/logout.php" class="dropdown-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>
<style>

    /* Navbar Styles */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 20px;
        background-color: var(--white);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
        position: fixed;
        top: 0;
        right: 0;
        left: 260px; /* Same as sidebar width */
        height: 50px;
        z-index: 99;
        transition: left var(--transition-normal) var(--transition-ease);
        border-bottom: 1px solid var(--neutral-200);
    }
    
    body.sidebar-collapsed .navbar {
        left: 60px;
    }
    
    .navbar-left {
        display: flex;
        align-items: center;
        gap: var(--spacing-4);
    }
    
    .sidebar-toggle {
        background: none;
        border: none;
        color: var(--neutral-600);
        font-size: var(--font-size-md);
        cursor: pointer;
        padding: 6px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all var(--transition-fast) var(--transition-ease);
        width: 32px;
        height: 32px;
        position: relative;
        overflow: hidden;
    }
    
    .sidebar-toggle:hover {
        background-color: var(--neutral-100);
        color: var(--primary);
        transform: scale(1.05);
    }
    
    .sidebar-toggle:active {
        transform: scale(0.95);
    }
    
    .navbar-left h1 {
        margin: 0;
        font-size: var(--font-size-lg);
        color: var(--neutral-800);
        font-weight: var(--font-weight-semibold);
        position: relative;
    }
    
    .navbar-left h1:after {
        content: '';
        display: block;
        width: 30px;
        height: 2px;
        background: linear-gradient(45deg, var(--primary), var(--secondary));
        margin-top: 3px;
        border-radius: 2px;
    }
    

    .navbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .user-avatar {
        position: relative;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-weight: var(--font-weight-bold);
        font-size: var(--font-size-sm);
        text-transform: uppercase;
        cursor: pointer;
        transition: all var(--transition-fast) var(--transition-ease);
        user-select: none;
    }
    
    .user-avatar:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }
    
    .dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: var(--white);
        border: 1px solid var(--neutral-200);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        min-width: 160px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all var(--transition-fast) var(--transition-ease);
        z-index: 1000;
    }
    
    .user-avatar.active .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        color: var(--neutral-700);
        text-decoration: none;
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        transition: all var(--transition-fast) var(--transition-ease);
        border-radius: var(--radius-md);
        margin: 4px;
    }
    
    .dropdown-item:hover {
        background-color: var(--neutral-100);
        color: var(--neutral-800);
    }
    
    .dropdown-item.logout {
        color: var(--danger);
        border-top: 1px solid var(--neutral-200);
        margin-top: 8px;
        border-radius: 0 0 var(--radius-md) var(--radius-md);
    }
    
    .dropdown-item.logout:hover {
        background-color: #fef2f2;
        color: #dc2626;
    }
    
    .dropdown-item i {
        width: 16px;
        font-size: var(--font-size-sm);
    }
    
    /* Responsive styles */
    @media (max-width: 768px) {
        .navbar {
            left: 0;
            padding: 15px 10px;
        }
        
        body.sidebar-collapsed .navbar {
            left: 0;
        }
        
        .navbar-left h1 {
            font-size: var(--font-size-lg);
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-info span {
            display: none;
        }
    }
    
    @media (max-width: 480px) {
        .navbar-left h1 {
            max-width: 150px;
            font-size: var(--font-size-md);
        }
        
        .logout-btn {
            padding: 5px 10px;
        }
        
        .dropdown-menu {
            min-width: 140px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userProfile = document.getElementById('userProfile');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    // Toggle dropdown when clicking on user profile
    userProfile.addEventListener('click', function(e) {
        e.stopPropagation();
        userProfile.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userProfile.contains(e.target)) {
            userProfile.classList.remove('active');
        }
    });
    
    // Prevent dropdown from closing when clicking inside it
    dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
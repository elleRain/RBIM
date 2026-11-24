<?php
// Initialize the session with secure settings
session_start([
    'cookie_secure' => isset($_SERVER['HTTPS']), // Secure cookie when using HTTPS
    'cookie_httponly' => true,                    // Prevent JavaScript access to session cookie
    'use_only_cookies' => true,                   // Force the use of cookies for session
    'cookie_samesite' => 'Lax'                    // Protect against CSRF attacks
]);

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['last_regeneration']) || 
    (time() - $_SESSION['last_regeneration']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Check if the user is already logged in, if yes then redirect based on role
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if(isset($_SESSION["role"]) && $_SESSION["role"] === "super_admin") {
        header("location: ../super_admin/super_admin_dashboard.php");
    } elseif(isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
        header("location: ../admin/admin_dashboard.php");
    } else {
        header("location: ../index.php");
    }
    exit;
}

// Include database connection
require_once "../plugins/conn.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Define maximum login attempts and lockout time
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15 * 60); // 15 minutes in seconds

// Check if remember me cookie exists
if(!isset($_SESSION["loggedin"]) && isset($_COOKIE["remember_me"])) {
    $token = $_COOKIE["remember_me"];
    
    // Verify the remember me token
    $sql = "SELECT user_id, username, role FROM remember_tokens WHERE token = ? AND expires_at > NOW()";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $username, $role);
                if(mysqli_stmt_fetch($stmt)) {
                    // Start a new session
                    session_regenerate_id(true);
                    
                    // Store data in session variables
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["admin_id"] = $id;
                    $_SESSION["username"] = $username;
                    $_SESSION["role"] = $role;
                    $_SESSION["last_activity"] = time();
                    
                    // For admin users, also store their barangay information
                    if($role === "admin") {
                        // Get barangay information for this admin
                        $barangay_query = "SELECT u.barangay, u.barangay_id FROM users u WHERE u.admin_id = ?";
                        $barangay_stmt = mysqli_prepare($conn, $barangay_query);
                        mysqli_stmt_bind_param($barangay_stmt, "i", $id);
                        mysqli_stmt_execute($barangay_stmt);
                        $barangay_result = mysqli_stmt_get_result($barangay_stmt);
                        $barangay_data = mysqli_fetch_assoc($barangay_result);
                        
                        if($barangay_data) {
                            $_SESSION["barangay"] = $barangay_data['barangay'];
                            $_SESSION["barangay_id"] = $barangay_data['barangay_id'];
                        }
                        mysqli_stmt_close($barangay_stmt);
                    }
                    
                    // Redirect user based on role
                    if($role === "super_admin") {
                        header("location: ../super_admin/super_admin_dashboard.php");
                    } elseif($role === "admin") {
                        header("location: ../admin/admin_dashboard.php");
                    } else {
                        header("location: ../index.php");
                    }
                    exit;
                }
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    // If we get here, the token is invalid or expired, clear the cookie
    setcookie("remember_me", "", time() - 3600, "/");
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Implement rate limiting
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        if (time() - $_SESSION['last_attempt_time'] < LOCKOUT_TIME) {
            $login_err = "Too many failed login attempts. Please try again after some time.";
        } else {
            // Reset attempts after lockout period
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    // Continue only if not locked out
    if (empty($login_err)) {
        // Check if username is empty
        if(empty(trim($_POST["username"]))) {
            $username_err = "Please enter username.";
        } else {
            // Sanitize the username input
            $username = filter_var(trim($_POST["username"]), FILTER_SANITIZE_STRING);
            
            // Additional validation if needed
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $username_err = "Username can only contain letters, numbers, and underscores.";
            }
        }
        
        // Check if password is empty
        if(empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Validate credentials
        if(empty($username_err) && empty($password_err)) {
            // Prepare a select statement
            $sql = "SELECT admin_id, username, password, role, account_status FROM users WHERE username = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                
                // Set parameters
                $param_username = $username;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)) {
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    // Check if username exists, if yes then verify password
                    if(mysqli_stmt_num_rows($stmt) == 1) {                    
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $admin_id, $username, $hashed_password, $role, $account_status);
                        if(mysqli_stmt_fetch($stmt)) {
                            // Check if account is active
                            if ($account_status !== 'active' && $account_status !== NULL) {
                                $login_err = "This account is not active. Please contact your administrator.";
                                // Track login attempt
                                $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
                                $_SESSION['last_attempt_time'] = time();
                            } 
                            // Verify password
                            elseif(password_verify($password, $hashed_password)) {
                                // Password is correct, reset login attempts
                                unset($_SESSION['login_attempts']);
                                unset($_SESSION['last_attempt_time']);
                                
                                // Start a new session
                                session_regenerate_id(true); // Important for security
                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $admin_id;
                                $_SESSION["admin_id"] = $admin_id;
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $role;
                                $_SESSION["last_activity"] = time(); // For session timeout
                                
                                // For admin users, also store their barangay information
                                if($role === "admin") {
                                    // Get barangay information for this admin
                                    $barangay_query = "SELECT u.barangay, u.barangay_id FROM users u WHERE u.admin_id = ?";
                                    $barangay_stmt = mysqli_prepare($conn, $barangay_query);
                                    mysqli_stmt_bind_param($barangay_stmt, "i", $admin_id);
                                    mysqli_stmt_execute($barangay_stmt);
                                    $barangay_result = mysqli_stmt_get_result($barangay_stmt);
                                    $barangay_data = mysqli_fetch_assoc($barangay_result);
                                    
                                    if($barangay_data) {
                                        $_SESSION["barangay"] = $barangay_data['barangay'];
                                        $_SESSION["barangay_id"] = $barangay_data['barangay_id'];
                                    }
                                    mysqli_stmt_close($barangay_stmt);
                                }
                                
                                // Handle remember me functionality
                                if(isset($_POST["remember"]) && $_POST["remember"] === "on") {
                                    // Generate a unique token
                                    $token = bin2hex(random_bytes(32));
                                    
                                    // Set expiration time (30 days from now)
                                    $expiry = date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60);
                                    
                                    // Store token in database
                                    $token_sql = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)";
                                    $token_stmt = mysqli_prepare($conn, $token_sql);
                                    mysqli_stmt_bind_param($token_stmt, "iss", $admin_id, $token, $expiry);
                                    mysqli_stmt_execute($token_stmt);
                                    mysqli_stmt_close($token_stmt);
                                    
                                    // Set cookie with token
                                    setcookie(
                                        "remember_me",
                                        $token,
                                        time() + 30 * 24 * 60 * 60, // 30 days
                                        "/",
                                        "", // domain
                                        isset($_SERVER['HTTPS']), // secure
                                        true // httponly
                                    );
                                }
                                
                                // Log successful login
                                error_log("Successful login: User $username (ID: $admin_id) logged in from {$_SERVER['REMOTE_ADDR']}");
                                
                                // Redirect user to appropriate page based on role
                                if($role === "super_admin") {
                                    header("location: ../super_admin/super_admin_dashboard.php");
                                } elseif($role === "admin") {
                                    header("location: ../admin/admin_dashboard.php");
                                } else {
                                    header("location: ../index.php");
                                }
                                exit;
                            } else {
                                // Password is not valid, display a generic error message
                                $login_err = "Invalid username or password.";
                                
                                // Track login attempt
                                $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
                                $_SESSION['last_attempt_time'] = time();
                                
                                // Log failed login attempt
                                error_log("Failed login attempt: Username $username from {$_SERVER['REMOTE_ADDR']}");
                            }
                        }
                    } else {
                        // Username doesn't exist, display a generic error message
                        $login_err = "Invalid username or password.";
                        
                        // Track login attempt
                        $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
                        $_SESSION['last_attempt_time'] = time();
                        
                        // Log failed login attempt
                        error_log("Failed login attempt: Username $username (not found) from {$_SERVER['REMOTE_ADDR']}");
                    }
                } else {
                    $login_err = "Something went wrong. Please try again later.";
                    error_log("Login query execution error: " . mysqli_error($conn));
                }

                // Close statement
                mysqli_stmt_close($stmt);
            } else {
                $login_err = "Something went wrong. Please try again later.";
                error_log("Login prepare statement error: " . mysqli_error($conn));
            }
        }
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBIM - Registry of Barangay Inhabitants and Migrants</title>
    <style>
    /* Error styles */
    .alert-error {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    .is-invalid {
        border-color: #dc3545 !important;
    }
    
    .is-invalid:focus {
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            overflow: hidden;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, rgba(30, 60, 114, 0.9), rgba(42, 82, 152, 0.9));
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.15) 0%, transparent 50%);
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .logo-section {
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            backdrop-filter: blur(10px);
        }

        .logo-icon svg {
            width: 24px;
            height: 24px;
            fill: white;
        }

        .brand-text {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 500;
        }

        .main-title {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .subtitle {
            font-size: 18px;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 50px;
            max-width: 500px;
            position: relative;
            z-index: 1;
        }

        .features {
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 16px;
            opacity: 0.9;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            margin-right: 20px;
            opacity: 0.8;
        }

        .right-section {
            width: 450px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 50px;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .welcome-subtitle {
            color: #64748b;
            font-size: 16px;
        }

        .admin-portal {
            color: #3b82f6;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.2s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
        }

        .checkbox {
            margin-right: 8px;
            accent-color: #3b82f6;
        }

        .checkbox-label {
            color: #64748b;
            font-size: 14px;
        }

        .forgot-password {
            color: #3b82f6;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .signin-button {
            width: 100%;
            background: #3b82f6;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
        }

        .signin-button:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .signin-button svg {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }

        .footer-text {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .contact-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-link:hover {
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }

        .login-logo {
            width: 100px; /* Adjust size as needed */
            height: auto;
            margin: 0 15px; /* Adjust spacing between logos */
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .left-section {
                padding: 40px 30px;
                min-height: 40vh;
            }
            
            .right-section {
                width: 100%;
                padding: 40px 30px;
            }
            
            .main-title {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <div class="logo-section">
                <div class="logo">
                    <div class="logo-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.9 1 3 1.9 3 3V21C3 22.1 3.9 23 5 23H19C20.1 23 21 22.1 21 21V11H19V19H5V3H13V9H21Z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 18px;">RBIM</div>
                        <div class="brand-text">Registry System</div>
                    </div>
                </div>
            </div>
            
            <h1 class="main-title">Registry of Barangay Inhabitants and Migrants</h1>
            
            <p class="subtitle">A comprehensive system for managing and tracking residents and migrants within barangays, providing efficient data management and reporting tools for local government units.</p>
            
            <ul class="features">
                <li class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4H5C3.9 4 3 4.9 3 6V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V6C21 4.9 20.1 4 19 4H16ZM10 4H14V6H10V4ZM19 19H5V8H7V10H9V8H15V10H17V8H19V19Z"/>
                    </svg>
                    Centralized household and resident management
                </li>
                <li class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19ZM7 10H9V16H7V10ZM15 7H17V16H15V7ZM11 13H13V16H11V13Z"/>
                    </svg>
                    Advanced analytics and reporting capabilities
                </li>
                <li class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 1L3 5V11C3 16.55 6.84 21.74 12 23C17.16 21.74 21 16.55 21 11V5L12 1ZM12 7C13.4 7 14.8 8.6 14.8 10V11.5C15.4 11.5 16 12.1 16 12.7V16.7C16 17.4 15.4 18 14.8 18H9.2C8.6 18 8 17.4 8 16.8V12.8C8 12.2 8.6 11.6 9.2 11.6V10C9.2 8.6 10.6 7 12 7ZM12 8.2C11.2 8.2 10.5 8.7 10.5 10V11.5H13.5V10C13.5 8.7 12.8 8.2 12 8.2Z"/>
                    </svg>
                    Secure and compliant data handling
                </li>
                <li class="feature">
                    <svg class="feature-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2ZM17 13H13V17H11V13H7V11H11V7H13V11H17V13Z"/>
                    </svg>
                    Real-time updates and synchronization
                </li>
            </ul>
        </div>
        
        <div class="right-section">
            <div class="login-logos" style="text-align: center; margin-bottom: 20px;">
                <img src="../images/Bago_City_logo.png" alt="Bago City Logo" class="login-logo">
                <img src="../images/rbim_logo.jpg" alt="RBIM Logo" class="login-logo">
            </div>
            <div class="welcome-header">
                <h2 class="welcome-title">Welcome Back</h2>
                <p class="welcome-subtitle">Sign in to access the <span class="admin-portal">Admin Portal</span></p>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <?php if(!empty($login_err)): ?>
                <div class="alert alert-error">
                    <?php echo $login_err; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter your username" value="<?php echo htmlspecialchars($username); ?>">
                    <?php if(!empty($username_err)): ?>
                    <div style="color: #dc3545; font-size: 14px; margin-top: 5px;"><?php echo $username_err; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter your password">
                    <?php if(!empty($password_err)): ?>
                    <div style="color: #dc3545; font-size: 14px; margin-top: 5px;"><?php echo $password_err; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-options">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remember" name="remember" class="checkbox">
                        <label for="remember" class="checkbox-label">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="signin-button">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10 17L15 12L10 7V10H3V14H10V17ZM21 3V21C21 21.6 20.6 22 20 22H12V20H19V4H12V2H20C20.6 2 21 2.4 21 3Z"/>
                    </svg>
                    Sign In
                </button>
                
                <div class="footer-text">
                    Don't have an account? <a href="#" class="contact-link">Contact your administrator</a>
                </div>
                
                <div class="copyright">
                    © 2025 RBIM - Registry of Barangay Inhabitants and Migrants
                </div>
            </form>
        </div>
    </div>
</body>
</html>
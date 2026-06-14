<?php
session_start();
require 'config.php';

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Regenerate session ID on each page load (prevents session fixation)
if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Whitelist of allowed modules
$allowed_modules = [
    'dashboard', 'students', 'academics', 'results', 'fees', 
    'parent_inquiry', 'notifications', 'announcements', 'reports', 
    'downloads', 'settings'
];

// Get and validate module parameter
$module = $_GET['module'] ?? 'dashboard';

// Sanitize module name - only alphanumeric and underscores
if (!preg_match('/^[a-z0-9_]+$/', $module) || !in_array($module, $allowed_modules, true)) {
    $module = 'dashboard';
}

// Get and validate sub parameter
$sub = isset($_GET['sub']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['sub']) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>KTI Central Admin Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            display: flex; 
            font-family: Arial, sans-serif; 
            background: #f4f4f4;
        }
        
        .sidebar { 
            width: 280px; 
            background: #800000; 
            color: #fff; 
            height: 100vh; 
            padding: 20px; 
            overflow-y: auto; 
            position: fixed;
            left: 0;
            top: 0;
        }
        
        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .sidebar a { 
            display: block; 
            color: #DAA520; 
            padding: 10px; 
            text-decoration: none; 
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .sidebar a:hover { 
            background: rgba(218, 165, 32, 0.1);
        }
        
        .sidebar a.active {
            background: #DAA520;
            color: #800000;
        }
        
        .sidebar h4 { 
            color: #fff; 
            margin-top: 20px; 
            margin-bottom: 10px;
            font-size: 13px;
            border-bottom: 2px solid #DAA520; 
            padding-bottom: 8px;
        }
        
        .sidebar hr {
            border: none;
            border-top: 1px solid #DAA520;
            margin: 20px 0;
        }
        
        .main { 
            flex: 1; 
            margin-left: 280px;
            padding: 30px; 
            background: #f4f4f4; 
            min-height: 100vh;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: #800000;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: #600000;
        }
        
        .content-area {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>KTI Admin</h2>
        <a href="?module=dashboard" <?php echo $module === 'dashboard' ? 'class="active"' : ''; ?>>Dashboard</a>
        
        <h4>Students</h4>
        <a href="?module=students&sub=register" <?php echo $module === 'students' && $sub === 'register' ? 'class="active"' : ''; ?>>Register Student</a>
        <a href="?module=students&sub=records" <?php echo $module === 'students' && $sub === 'records' ? 'class="active"' : ''; ?>>Student Records</a>
        <a href="?module=students&sub=graduates" <?php echo $module === 'students' && $sub === 'graduates' ? 'class="active"' : ''; ?>>Graduates</a>

        <h4>Academics</h4>
        <a href="?module=academics&sub=depts" <?php echo $module === 'academics' && $sub === 'depts' ? 'class="active"' : ''; ?>>Departments</a>
        <a href="?module=academics&sub=courses" <?php echo $module === 'academics' && $sub === 'courses' ? 'class="active"' : ''; ?>>Courses</a>
        <a href="?module=academics&sub=units" <?php echo $module === 'academics' && $sub === 'units' ? 'class="active"' : ''; ?>>Units</a>
        <a href="?module=academics&sub=reg" <?php echo $module === 'academics' && $sub === 'reg' ? 'class="active"' : ''; ?>>Unit Registration</a>

        <h4>Results</h4>
        <a href="?module=results&sub=cat" <?php echo $module === 'results' && $sub === 'cat' ? 'class="active"' : ''; ?>>CAT Marks</a>
        <a href="?module=results&sub=exam" <?php echo $module === 'results' && $sub === 'exam' ? 'class="active"' : ''; ?>>Exam Marks</a>

        <h4>Financials</h4>
        <a href="?module=fees&sub=payments" <?php echo $module === 'fees' && $sub === 'payments' ? 'class="active"' : ''; ?>>Payments</a>
        <a href="?module=fees&sub=balances" <?php echo $module === 'fees' && $sub === 'balances' ? 'class="active"' : ''; ?>>Balances</a>

        <h4>Communication & Admin</h4>
        <a href="?module=parent_inquiry" <?php echo $module === 'parent_inquiry' ? 'class="active"' : ''; ?>>Parent Inquiry</a>
        <a href="?module=notifications" <?php echo $module === 'notifications' ? 'class="active"' : ''; ?>>Notifications</a>
        <a href="?module=announcements" <?php echo $module === 'announcements' ? 'class="active"' : ''; ?>>Announcements</a>
        <a href="?module=reports" <?php echo $module === 'reports' ? 'class="active"' : ''; ?>>Reports</a>
        <a href="?module=downloads" <?php echo $module === 'downloads' ? 'class="active"' : ''; ?>>Downloads</a>
        <a href="?module=settings" <?php echo $module === 'settings' ? 'class="active"' : ''; ?>>Settings</a>
        
        <hr>
        <a href="logout.php" style="color:white;">Logout</a>
    </div>

    <div class="main">
        <div class="top-bar">
            <h1><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['role'] ?? 'admin'); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="content-area">
            <?php
            // Include the appropriate module
            $file = "modules/" . $module . ".php";
            
            if (file_exists($file)) {
                include $file; 
            } else {
                echo "<h1>Dashboard Overview</h1>";
                echo "<p>Welcome to the Kabete Central Admin Portal.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>

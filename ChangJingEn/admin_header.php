<?php
// admin_header.php - Grand Hotel Melaka
session_start();

$config_path = __DIR__ . '/../Shared/config.php';
if (!file_exists($config_path)) {
    die('Configuration file not found: ' . $config_path);
}
require_once $config_path;

if (!isset($conn) || $conn === null) {
    die('Database connection not established.');
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name   = $_SESSION['admin_username'] ?? 'Admin';
$admin_role   = $_SESSION['admin_role'] ?? 'admin';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Grand Hotel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-body: #f5f5f5;
            --bg-sidebar: #ffffff;
            --bg-header: #ffffff;
            --text-primary: #1e1e1e;
            --text-secondary: #6c6c6c;
            --border-light: #eaeaea;
            --gold: #c5a059;
            --gold-light: #d4af6a;
            --gold-hover: #b88d3a;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 6px 20px rgba(0, 0, 0, 0.05);
            --header-height: 70px;
        }

        [data-theme="dark"] {
            --bg-body: #0a0a0a;         
            --bg-sidebar: #121212;       
            --bg-header: #121212;        
            --text-primary: #E2E8F0;     
            --text-secondary: #94A3B8;   
            --border-light: #2a2a2a;    
            --gold: #fbbf24;            
            --gold-light: #fcd34d;
            --gold-hover: #f59e0b;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            transition: background 0.3s, color 0.2s;
            font-size: 1rem;
            line-height: 1.5;
        }

        .main-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--bg-header);
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }
        .brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--gold);
            margin: 0;
            letter-spacing: -0.3px;
        }
        .header-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .theme-toggle {
            background: transparent;
            border: 1px solid var(--border-light);
            border-radius: 40px;
            padding: 8px 18px;
            cursor: pointer;
            color: var(--text-primary);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .theme-toggle:hover {
            border-color: var(--gold);
            color: var(--gold);
        }
        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg-body);
            padding: 6px 18px 6px 14px;
            border-radius: 48px;
            border: 1px solid var(--border-light);
        }
        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e1e1e;
            font-weight: bold;
            font-size: 1rem;
        }
        [data-theme="dark"] .user-avatar {
            color: #0a0a0a;
        }
        .user-info-text { line-height: 1.3; }
        .user-name { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); }
        .user-role { font-size: 0.65rem; color: var(--text-secondary); }
        .logout-btn {
            background: transparent;
            border: 1px solid var(--border-light);
            border-radius: 40px;
            padding: 8px 20px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            transition: 0.2s;
        }
        .logout-btn:hover {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
        }
        [data-theme="dark"] .logout-btn:hover {
            background: #3a1a1a;
            border-color: #5a2a2a;
            color: #ffa2a2;
        }

        /* 侧边栏 */
        .main-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: 280px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-light);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            transition: background 0.3s;
            z-index: 999;
        }
        .main-sidebar::-webkit-scrollbar { display: none; }
        .sidebar-menu {
            flex: 1;
            padding: 20px 16px 30px;
        }
        .menu-header {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-secondary);
            padding: 14px 12px 6px;
            font-weight: 700;
        }
        .nav-item { list-style: none; margin-bottom: 2px; }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .nav-link i { width: 22px; font-size: 1rem; text-align: center; }
        .nav-link:hover {
            background: rgba(197, 160, 89, 0.1);
            color: var(--gold);
        }
        .dropdown-btn {
            cursor: pointer;
            justify-content: space-between;
        }
        .dropdown-btn .fa-chevron-down {
            transition: transform 0.2s;
            margin-left: auto;
            font-size: 0.8rem;
        }
        .dropdown-container {
            display: none;
            padding-left: 32px;
            list-style: none;
        }
        .dropdown-container .nav-link {
            padding: 8px 14px;
            font-size: 0.85rem;
        }
        .dropdown-container .nav-link i {
            width: 20px;
            font-size: 0.85rem;
        }
        .show { display: block; }
        .rotate { transform: rotate(180deg); }

        /* 主内容区 */
        .content-wrapper {
            margin-left: 280px;
            margin-top: var(--header-height);
            padding: 28px 32px;
            background: var(--bg-body);
            min-height: calc(100vh - var(--header-height));
        }

        @media (max-width: 768px) {
            .main-header { padding: 0 16px; }
            .brand h1 { font-size: 1.2rem; }
            .user-area .user-name { display: none; }
            .main-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                width: 260px;
            }
            .main-sidebar.open { transform: translateX(0); }
            .content-wrapper { margin-left: 0; padding: 20px; }
            .sidebar-toggle {
                display: block;
                background: transparent;
                border: none;
                font-size: 1.3rem;
                cursor: pointer;
                margin-right: 12px;
                color: var(--text-primary);
            }
            .header-left { display: flex; align-items: center; gap: 12px; }
        }
    </style>
</head>
<body>

<header class="main-header">
    <div style="display: flex; align-items: center; gap: 16px;">
        <button class="sidebar-toggle" id="sidebarToggle" style="display: none;"><i class="fas fa-bars"></i></button>
        <div class="brand"><h1>Grand Hotel Admin</h1></div>
    </div>
    <div class="header-right">
        <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon"></i> <span>Dark</span></button>
        <div class="user-area">
            <div class="user-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
            <div class="user-info-text">
                <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                <div class="user-role"><?php echo ucfirst($admin_role); ?></div>
            </div>
        </div>
        <form method="POST" action="admin_logout.php" style="display: inline;">
            <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
</header>

<aside class="main-sidebar" id="mainSidebar">
    <div class="sidebar-menu">
        <div class="menu-header">MAIN</div>
        <ul style="list-style: none;">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link <?php echo $current_page == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        </ul>

        <div class="menu-header">MANAGEMENT</div>
        <ul style="list-style: none;">
            <?php if ($admin_role === 'superadmin'): ?>
            <li class="nav-item"><a href="admin_manage_admins.php" class="nav-link <?php echo $current_page == 'admin_manage_admins.php' ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> Admins</a></li>
            <?php endif; ?>
            <li class="nav-item"><a href="admin_users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
            <li class="nav-item"><a href="../ChongEeLynn/roommanagement.php" class="nav-link"><i class="fas fa-bed"></i> Rooms</a></li>
            <li class="nav-item"><a href="admin_bookings.php" class="nav-link"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li class="nav-item"><a href="../ChongEeLynn/admin_offers.php" class="nav-link"><i class="fas fa-tags"></i> Offers</a></li>
            <li class="nav-item"><a href="admin_reviews.php" class="nav-link"><i class="fas fa-star"></i> Reviews</a></li>
        </ul>

        <div class="menu-header">EXPLORE</div>
        <ul style="list-style: none;">
            <li class="nav-item"><a href="admin_facilities.php" class="nav-link"><i class="fas fa-building"></i> Facilities</a></li>
            <li class="nav-item"><a href="admin_experience.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Experience</a></li>
        </ul>

        <ul style="list-style: none;">
            <li class="nav-item">
                <div class="nav-link dropdown-btn">
                    <i class="fas fa-concierge-bell"></i> <span>Services & Events</span> <i class="fas fa-chevron-down"></i>
                </div>
                <ul class="dropdown-container">
                    <li><a href="admin_dining.php" class="nav-link"><i class="fas fa-utensils"></i> Dining</a></li>
                    <li><a href="admin_events.php" class="nav-link"><i class="fas fa-ring"></i> Events</a></li>
                </ul>
            </li>
        </ul>

        <div class="menu-header">SYSTEM</div>
        <ul style="list-style: none;">
            <li class="nav-item"><a href="admin_reports.php" class="nav-link"><i class="fas fa-chart-line"></i> Reports</a></li>
            <li class="nav-item"><a href="admin_profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profile</a></li>
        </ul>
    </div>
</aside>

<div class="content-wrapper">
    <main class="main-content">
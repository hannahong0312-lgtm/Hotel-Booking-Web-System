<?php
// admin_dashboard.php - Grand Hotel Admin Dashboard
session_start();
require_once '../Shared/config.php';

// 检查管理员登录状态
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_name = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';

// ========== 统计数据查询 ==========
// 总用户数
$sql_users = "SELECT COUNT(*) AS total FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total'];

// 总房间数 (仅活跃)
$sql_rooms = "SELECT COUNT(*) AS total FROM rooms WHERE is_active = 1";
$result_rooms = $conn->query($sql_rooms);
$total_rooms = $result_rooms->fetch_assoc()['total'];

// 总预订数
$sql_bookings = "SELECT COUNT(*) AS total FROM book";
$result_bookings = $conn->query($sql_bookings);
$total_bookings = $result_bookings->fetch_assoc()['total'];

// 总收入 (已确认或已完成)
$sql_revenue = "SELECT SUM(grand_total) AS total FROM book WHERE status IN ('confirmed', 'completed')";
$result_revenue = $conn->query($sql_revenue);
$total_revenue = $result_revenue->fetch_assoc()['total'] ?? 0;

// 最近预订 (5条)
$sql_recent = "SELECT b.id, b.booking_ref, b.check_in, b.check_out, b.grand_total, b.status, b.created_at,
                      u.first_name, u.last_name, r.name AS room_name
               FROM book b
               JOIN users u ON b.user_id = u.id
               JOIN rooms r ON b.room_id = r.id
               ORDER BY b.created_at DESC
               LIMIT 5";
$recent_bookings = $conn->query($sql_recent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Grand Hotel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
            color: #1e293b;
            line-height: 1.5;
        }

        /* 侧边栏 */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100%;
            background: #0f172a;
            color: #e2e8f0;
            transition: all 0.3s;
            z-index: 100;
            box-shadow: 2px 0 12px rgba(0,0,0,0.08);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 1.5rem;
        }

        .sidebar-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #c5a059;
            letter-spacing: 1px;
        }

        .sidebar-header p {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        .nav-menu {
            list-style: none;
            padding: 0 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-link i {
            width: 24px;
            font-size: 1.2rem;
        }

        .nav-link:hover {
            background: #1e293b;
            color: white;
        }

        .nav-link.active {
            background: #c5a059;
            color: #0f172a;
        }

        /* 主内容区 */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }

        /* 顶部栏 */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #0f172a;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-badge {
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.2s;
        }

        .logout-btn:hover {
            background: #dc2626;
        }

        /* 统计卡片 */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #eef2f6;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.08);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-header i {
            font-size: 2.2rem;
            color: #c5a059;
            opacity: 0.8;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* 表格区域 */
        .recent-section {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid #eef2f6;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 1rem 0.5rem;
            background: #f8fafc;
            font-weight: 600;
            color: #334155;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 1rem 0.5rem;
            border-bottom: 1px solid #eef2f6;
            vertical-align: middle;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 40px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fed7aa; color: #9a3412; }

        .ref-link {
            font-family: monospace;
            font-weight: 600;
            color: #c5a059;
        }

        @media (max-width: 768px) {
            .sidebar { width: 0; overflow: hidden; }
            .main-content { margin-left: 0; padding: 1rem; }
            .stats-grid { gap: 1rem; }
            .stat-number { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<!-- 侧边栏 -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Grand Hotel</h2>
        <p>Management System</p>
    </div>
    <ul class="nav-menu">
        <!-- 现有菜单 -->
        <li class="nav-item">
            <a href="admin_dashboard.php" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_users.php" class="nav-link">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_rooms.php" class="nav-link">
                <i class="fas fa-bed"></i> Rooms
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_bookings.php" class="nav-link">
                <i class="fas fa-calendar-check"></i> Bookings
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_offers.php" class="nav-link">
                <i class="fas fa-tags"></i> Offers
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_reports.php" class="nav-link">
                <i class="fas fa-chart-line"></i> Reports
            </a>
        </li>
        <!-- 新增的三个菜单项（页面暂未创建，使用 # 占位） -->
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="return false;">
                <i class="fas fa-user-cog"></i> Profile
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="return false;">
                <i class="fas fa-utensils"></i> Dining
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" onclick="return false;">
                <i class="fas fa-ring"></i> Events
            </a>
        </li>
    </ul>
</div>

<!-- 主内容 -->
<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h1>Dashboard</h1>
        </div>
        <div class="admin-info">
            <span class="admin-badge"><i class="fas fa-user-shield"></i> <?php echo ucfirst($admin_role); ?></span>
            <span class="admin-badge"><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_name); ?></span>
            <form method="POST" action="process/admin_logout.php" style="display: inline;">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Total Users</div>
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number"><?php echo number_format($total_users); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Total Rooms</div>
                <i class="fas fa-bed"></i>
            </div>
            <div class="stat-number"><?php echo number_format($total_rooms); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Total Bookings</div>
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-number"><?php echo number_format($total_bookings); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-label">Total Revenue</div>
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-number">RM <?php echo number_format($total_revenue, 2); ?></div>
        </div>
    </div>

    <!-- 最近预订列表 -->
    <div class="recent-section">
        <div class="section-title">
            <i class="fas fa-history"></i> Recent Bookings
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ref #</th>
                        <th>Customer</th>
                        <th>Room</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total (RM)</th>
                        <th>Status</th>
                        <th>Booked On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                        <?php while ($row = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><span class="ref-link"><?php echo htmlspecialchars($row['booking_ref']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['room_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['check_in'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['check_out'])); ?></td>
                                <td><?php echo number_format($row['grand_total'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
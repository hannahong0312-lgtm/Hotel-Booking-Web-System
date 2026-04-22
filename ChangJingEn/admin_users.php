<?php
// admin_users.php - Manage Customer Users
require_once 'admin_header.php';

// 仅管理员可访问（admin_header 已确保登录）
$message = '';
$error = '';

// 处理 POST 请求（状态切换）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    if ($user_id > 0 && in_array($new_status, ['active', 'inactive'])) {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $user_id);
        if ($stmt->execute()) {
            $message = 'User status updated successfully.';
        } else {
            $error = 'Failed to update status.';
        }
        $stmt->close();
    } else {
        $error = 'Invalid request.';
    }
}

// 获取筛选和分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}
if (!empty($status_filter) && in_array($status_filter, ['active', 'inactive'])) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}
$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// 计算总数
$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_users = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
$count_stmt->close();

// 查询当前页
$sql = "SELECT id, first_name, last_name, email, phone, country, status, subscribe, points, last_login, created_at 
        FROM users $where_clause 
        ORDER BY created_at DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
$stmt->close();

// 统计数据
$total_all = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$active_count = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetch_row()[0];
$inactive_count = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetch_row()[0];
$today_count = $conn->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetch_row()[0];
?>

<style>
    /* 统计卡片（复用 admins 页面样式，稍作调整） */
    .stats-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }
    .stat-summary-card {
        flex: 1;
        border-radius: 28px;
        padding: 20px 24px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    [data-theme="dark"] .stat-summary-card.total {
        background: linear-gradient(135deg, #2a2418 0%, #1e1912 100%);
    }
    [data-theme="dark"] .stat-summary-card.active {
        background: linear-gradient(135deg, #1a3a2a 0%, #0f2e20 100%);
    }
    [data-theme="dark"] .stat-summary-card.inactive {
        background: linear-gradient(135deg, #3a2a1a 0%, #2e2213 100%);
    }
    .stat-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    .stat-summary-card .stat-icon {
        position: absolute;
        right: 20px;
        bottom: 20px;
        font-size: 3rem;
        opacity: 0.15;
        color: var(--gold);
    }
    [data-theme="dark"] .stat-summary-card .stat-icon {
        opacity: 0.25;
        color: var(--gold-light);
    }
    .stat-summary-card .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--gold);
        line-height: 1.2;
    }
    .stat-summary-card .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-secondary);
        margin-top: 8px;
    }
    .stat-summary-card .stat-sub {
        font-size: 0.7rem;
        color: var(--text-secondary);
        margin-top: 4px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .page-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    .btn-outline-gold {
        background: transparent;
        border: 1px solid var(--gold);
        padding: 10px 20px;
        border-radius: 40px;
        cursor: pointer;
        transition: 0.2s;
        font-weight: 500;
        color: var(--gold);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        font-size: 0.9rem;
        line-height: 1;
        font-family: inherit;
    }
    .btn-outline-gold:hover {
        background: rgba(197,160,89,0.1);
        border-color: var(--gold-hover);
        color: var(--gold-hover);
    }
    .filter-bar {
        background: var(--bg-sidebar);
        border-radius: 24px;
        padding: 16px 24px;
        margin-bottom: 28px;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: center;
        border: 1px solid var(--border-light);
    }
    .filter-bar input, .filter-bar select {
        padding: 10px 16px;
        border: 1px solid var(--border-light);
        border-radius: 40px;
        background: var(--bg-body);
        color: var(--text-primary);
        font-size: 0.85rem;
        min-width: 180px;
    }
    .filter-bar select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c6c6c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 14px;
    }
    [data-theme="dark"] .filter-bar select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a0a0a0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    }
    .user-table {
        width: 100%;
        background: var(--bg-sidebar);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        border-collapse: separate;
        border-spacing: 0;
    }
    .user-table th {
        text-align: left;
        padding: 18px 16px;
        background: rgba(0,0,0,0.02);
        font-weight: 700;
        font-size: 0.8rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-light);
    }
    .user-table td {
        padding: 16px 16px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        font-size: 0.85rem;
        vertical-align: middle;
    }
    .user-table tr:hover td {
        background: rgba(197, 160, 89, 0.05);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-active { background: #e6f9ed; color: #0b5e42; }
    .status-inactive { background: #fee2e2; color: #991b1b; }
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .action-btn {
        background: transparent;
        border: 1px solid var(--border-light);
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .action-btn.toggle {
        border-color: var(--gold);
        color: var(--gold);
    }
    .action-btn.toggle:hover {
        background: rgba(197,160,89,0.15);
        border-color: var(--gold-hover);
    }
    .pagination {
        margin-top: 32px;
        display: flex;
        justify-content: center;
        gap: 8px;
    }
    .pagination a {
        padding: 8px 14px;
        background: var(--bg-sidebar);
        border: 1px solid var(--border-light);
        border-radius: 40px;
        text-decoration: none;
        color: var(--text-primary);
        font-size: 0.85rem;
        transition: 0.2s;
    }
    .pagination a.active {
        background: var(--gold);
        color: white;
        border-color: var(--gold);
    }
    .pagination a:hover:not(.active) {
        border-color: var(--gold);
        color: var(--gold);
    }
    .alert {
        padding: 14px 20px;
        border-radius: 20px;
        margin-bottom: 24px;
        font-size: 0.85rem;
    }
    .alert-success {
        background: #e6f9ed;
        color: #0b5e42;
        border: 1px solid #c8e6d9;
    }
    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    [data-theme="dark"] .alert-success {
        background: #1a3a2a;
        color: #b8e6cc;
        border-color: #2a5a3a;
    }
    [data-theme="dark"] .alert-danger {
        background: #3a1a1a;
        color: #ffa2a2;
        border-color: #5a2a2a;
    }
    @media (max-width: 768px) {
        .user-table th, .user-table td { padding: 12px 8px; font-size: 0.75rem; }
        .action-buttons { gap: 4px; }
        .action-btn { padding: 2px 8px; font-size: 0.65rem; }
        .stats-summary { flex-direction: column; }
        .filter-bar { flex-direction: column; align-items: stretch; }
        .filter-bar input, .filter-bar select, .filter-bar button { width: 100%; }
    }
</style>
<div class="stats-summary">
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value"><?php echo $total_all; ?></div>
        <div class="stat-label">Total Customers</div>
        <div class="stat-sub">All registered users</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-user-check"></i></div>
        <div class="stat-value"><?php echo $active_count; ?></div>
        <div class="stat-label">Active</div>
        <div class="stat-sub">Currently active</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-user-slash"></i></div>
        <div class="stat-value"><?php echo $inactive_count; ?></div>
        <div class="stat-label">Inactive</div>
        <div class="stat-sub">Temporarily locked</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-value"><?php echo $today_count; ?></div>
        <div class="stat-label">New Today</div>
        <div class="stat-sub">Registered in last 24h</div>
    </div>
</div>
<div class="page-header">
    <h2>Customer Management</h2>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="filter-bar">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%; align-items: center;">
        <input type="text" name="search" placeholder="Search by name, email or phone" value="<?php echo htmlspecialchars($search); ?>" style="flex: 2;">
        <select name="status" style="flex: 1;">
            <option value="">All Status</option>
            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
        <button type="submit" class="btn-outline-gold">Filter</button>
        <a href="admin_users.php" class="btn-outline-gold">Reset</a>
    </form>
</div>

<table class="user-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Country</th>
            <th>Status</th>
            <th>Points</th>
            <th>Last Login</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($users->num_rows > 0): ?>
            <?php while ($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['country'] ?: '—'); ?></td>
                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td><?php echo number_format($row['points']); ?></td>
                    <td><?php echo $row['last_login'] ? date('d M Y, H:i', strtotime($row['last_login'])) : '—'; ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="status" value="<?php echo $row['status'] == 'active' ? 'inactive' : 'active'; ?>">
                            <button type="submit" class="action-btn toggle" 
                                onclick="return confirm('Are you sure you want to <?php echo $row['status'] == 'active' ? 'deactivate' : 'activate'; ?> this user?');">
                                <?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10" style="text-align: center; padding: 48px;">No users found.<?php echo ' '; ?></td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php
// 关闭主内容区（admin_header 已打开）
?>
    </main>
</div>

<script>
    // 主题切换和侧边栏交互（与 admin_header 一致，但 admin_header 已包含，为避免重复可省略，但保留无妨）
    (function() {
        const toggle = document.getElementById('themeToggle');
        if (!toggle) return;
        const html = document.documentElement;
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
            html.setAttribute('data-theme', 'dark');
            toggle.innerHTML = '<i class="fas fa-sun"></i> <span>Light</span>';
        } else {
            html.setAttribute('data-theme', 'light');
            toggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark</span>';
        }
        toggle.addEventListener('click', () => {
            if (html.getAttribute('data-theme') === 'light') {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                toggle.innerHTML = '<i class="fas fa-sun"></i> <span>Light</span>';
            } else {
                html.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                toggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark</span>';
            }
        });
    })();

    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const container = this.nextElementSibling;
            if (container && container.classList.contains('dropdown-container')) {
                container.classList.toggle('show');
                const icon = this.querySelector('.fa-chevron-down');
                if (icon) icon.classList.toggle('rotate');
            }
        });
    });

    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('mainSidebar');
    if (toggleBtn && sidebar) {
        const updateDisplay = () => {
            toggleBtn.style.display = window.innerWidth <= 768 ? 'block' : 'none';
            if (window.innerWidth > 768) sidebar.classList.remove('open');
        };
        updateDisplay();
        window.addEventListener('resize', updateDisplay);
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });
        const contentWrapper = document.querySelector('.content-wrapper');
        if (contentWrapper) {
            contentWrapper.addEventListener('click', function() {
                if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            });
        }
    }
</script>
</body>
</html>
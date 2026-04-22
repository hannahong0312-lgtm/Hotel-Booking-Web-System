<?php
// admin_manage_admins.php - Grand Hotel Melaka
require_once 'admin_header.php';

if ($admin_role !== 'superadmin') {
    header("Location: admin_dashboard.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $role = isset($_POST['role']) && $_POST['role'] == 1 ? 1 : 0;

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Valid email is required.';
        } elseif (empty($username)) {
            $error = 'Username is required.';
        } elseif (empty($password) || strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = 'Password must contain at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Password must contain at least one number.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $check = $conn->prepare("SELECT id FROM admins WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = 'Email already registered.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admins (email, username, password, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
                $stmt->bind_param("sssi", $email, $username, $hashed, $role);
                if ($stmt->execute()) {
                    $message = 'Admin created successfully.';
                } else {
                    $error = 'Failed to create admin.';
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    elseif ($action === 'reset_password') {
        $id = intval($_POST['id'] ?? 0);
        if ($id == $_SESSION['admin_id']) {
            $error = 'You cannot reset your own password here. Use Profile page.';
        } else {
            $letters = 'abcdefghijklmnopqrstuvwxyz';
            $upper = strtoupper($letters);
            $numbers = '0123456789';
            $new_password = 
                $upper[random_int(0, 25)] . 
                $letters[random_int(0, 25)] . 
                $numbers[random_int(0, 9)] . 
                substr(str_shuffle($letters . $upper . $numbers), 0, 7);
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $id);
            if ($stmt->execute()) {
                $message = "Password reset successfully. New temporary password: <strong>$new_password</strong> (Please inform the admin to change it after login).";
            } else {
                $error = 'Failed to reset password.';
            }
            $stmt->close();
        }
    }

    elseif ($action === 'toggle_status') {
        $id = intval($_POST['id'] ?? 0);
        if ($id == $_SESSION['admin_id']) {
            $error = 'You cannot change your own status.';
        } else {
            $stmt = $conn->prepare("SELECT status FROM admins WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();
            if ($admin) {
                $new_status = ($admin['status'] === 'active') ? 'suspended' : 'active';
                $update = $conn->prepare("UPDATE admins SET status = ? WHERE id = ?");
                $update->bind_param("si", $new_status, $id);
                if ($update->execute()) {
                    $message = 'Admin status updated.';
                } else {
                    $error = 'Failed to update status.';
                }
                $update->close();
            } else {
                $error = 'Admin not found.';
            }
        }
    }

    elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id == $_SESSION['admin_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $conn->prepare("SELECT role FROM admins WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            $stmt->close();
            if ($admin && $admin['role'] == 1) {
                $error = 'Cannot delete a super administrator.';
            } else {
                $delete = $conn->prepare("DELETE FROM admins WHERE id = ?");
                $delete->bind_param("i", $id);
                if ($delete->execute()) {
                    $message = 'Admin deleted successfully.';
                } else {
                    $error = 'Failed to delete admin.';
                }
                $delete->close();
            }
        }
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(email LIKE ? OR username LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
if (!empty($status_filter) && in_array($status_filter, ['active', 'suspended', 'inactive'])) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}
$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_sql = "SELECT COUNT(*) as total FROM admins $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_admins = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_admins / $limit);
$count_stmt->close();

$sql = "SELECT id, email, username, role, status, last_login, created_at FROM admins $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$admins = $stmt->get_result();
$stmt->close();

$total_all = $conn->query("SELECT COUNT(*) FROM admins")->fetch_row()[0];
$active_count = $conn->query("SELECT COUNT(*) FROM admins WHERE status = 'active'")->fetch_row()[0];
$suspended_count = $conn->query("SELECT COUNT(*) FROM admins WHERE status = 'suspended'")->fetch_row()[0];
?>

<style>
    .stats-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }
    .stat-summary-card {
        flex: 1;
        background: linear-gradient(135deg, var(--bg-sidebar) 0%, var(--bg-body) 100%);
        border-radius: 28px;
        padding: 20px 24px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
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
    .btn-primary {
        background: linear-gradient(135deg, var(--gold), var(--gold-hover));
        border: none;
        padding: 12px 28px;
        border-radius: 40px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        box-shadow: 0 2px 6px rgba(197,160,89,0.3);
    }
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(197,160,89,0.4);
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
        gap: 8px;
        text-decoration: none;
        font-size: 0.9rem;
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
    .admin-table {
        width: 100%;
        background: var(--bg-sidebar);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        border-collapse: separate;
        border-spacing: 0;
    }
    .admin-table th {
        text-align: left;
        padding: 18px 16px;
        background: rgba(0,0,0,0.02);
        font-weight: 700;
        font-size: 0.8rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-light);
    }
    .admin-table td {
        padding: 16px 16px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        font-size: 0.85rem;
        vertical-align: middle;
    }
    .admin-table tr:hover td {
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
    .status-suspended { background: #fee2e2; color: #991b1b; }
    .status-inactive { background: #fef3c7; color: #b45309; }
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
    .action-btn.reset {
        border-color: #f39c12;
        color: #e67e22;
    }
    .action-btn.reset:hover {
        background: #fef5e7;
        border-color: #e67e22;
    }
    .action-btn.toggle {
        border-color: #d4af6a;
        color: #c5a059;
    }
    .action-btn.toggle:hover {
        background: rgba(197,160,89,0.15);
        border-color: #c5a059;
    }
    .action-btn.delete {
        border-color: #e74c3c;
        color: #c0392b;
    }
    .action-btn.delete:hover {
        background: #fce4e4;
        border-color: #c0392b;
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
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        justify-content: center;
        align-items: center;
        z-index: 1100;
        backdrop-filter: blur(2px);
    }
    .modal-content {
        background: var(--bg-sidebar);
        border-radius: 28px;
        width: 500px;
        max-width: 90%;
        padding: 28px;
        position: relative;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
    }
    .modal-content h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        margin-bottom: 20px;
        color: var(--text-primary);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-primary);
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-light);
        border-radius: 16px;
        background: var(--bg-body);
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: 0.2s;
    }
    .form-group select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c6c6c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 14px;
    }
    [data-theme="dark"] .form-group select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a0a0a0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    }
    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: var(--gold);
    }
    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
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
        .admin-table th, .admin-table td { padding: 12px 8px; font-size: 0.75rem; }
        .action-buttons { gap: 4px; }
        .action-btn { padding: 2px 8px; font-size: 0.65rem; }
        .stats-summary { flex-direction: column; }
        .btn-outline-gold, .btn-primary { padding: 8px 16px; font-size: 0.8rem; }
    }
</style>

<div class="stats-summary">
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
        <div class="stat-value"><?php echo $total_all; ?></div>
        <div class="stat-label">Total Admins</div>
        <div class="stat-sub">All administrator accounts</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo $active_count; ?></div>
        <div class="stat-label">Active</div>
        <div class="stat-sub">Currently active</div>
    </div>
    <div class="stat-summary-card">
        <div class="stat-icon"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-value"><?php echo $suspended_count; ?></div>
        <div class="stat-label">Suspended</div>
        <div class="stat-sub">Temporarily locked</div>
    </div>
</div>

<div class="page-header">
    <h2>Administrators</h2>
    <button class="btn-primary" id="openCreateModalBtn"><i class="fas fa-user-plus"></i> New Admin</button>
</div>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="filter-bar">
    <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%; align-items: center;">
        <input type="text" name="search" placeholder="Search by email or username" value="<?php echo htmlspecialchars($search); ?>" style="flex: 2;">
        <select name="status" style="flex: 1;">
            <option value="">All Status</option>
            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="suspended" <?php echo $status_filter == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
        <button type="submit" class="btn-outline-gold">Filter</button>
        <button type="button" class="btn-outline-gold" onclick="window.location.href='admin_manage_admins.php'">Reset</button>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($admins->num_rows > 0): ?>
            <?php while ($row = $admins->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo $row['role'] == 1 ? 'Super Admin' : 'Admin'; ?></td>
                    <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td><?php echo $row['last_login'] ? date('d M Y, H:i', strtotime($row['last_login'])) : '—'; ?></td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                            <div class="action-buttons">
                                <button class="action-btn reset" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['username']); ?>" onclick="resetAdmin(this)">Reset</button>
                                <button class="action-btn toggle" data-id="<?php echo $row['id']; ?>" data-status="<?php echo $row['status']; ?>" onclick="toggleAdminStatus(this)"><?php echo $row['status'] == 'active' ? 'Suspend' : 'Activate'; ?></button>
                                <button class="action-btn delete" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['username']); ?>" onclick="deleteAdmin(this)">Delete</button>
                            </div>
                        <?php else: ?>
                            <span style="color: var(--text-secondary); font-size:0.75rem;">(Your account)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align: center; padding: 48px;">No administrators found.<?php echo ' '; ?></td></tr>
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

<div id="createModal" class="modal">
    <div class="modal-content">
        <h3>Create New Admin</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password * (min 8 chars, 1 uppercase, 1 lowercase, 1 number)</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password *</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role">
                    <option value="0">Admin</option>
                    <option value="1">Super Admin</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn-outline-gold" onclick="closeModal('createModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<form id="resetPasswordForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="reset_password">
    <input type="hidden" name="id" id="reset_id">
</form>
<form id="toggleStatusForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="toggle_status">
    <input type="hidden" name="id" id="toggle_id">
</form>
<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    document.getElementById('openCreateModalBtn').onclick = () => openModal('createModal');

    function resetAdmin(btn) {
        const name = btn.getAttribute('data-name');
        if (confirm(`Reset password for "${name}"? A new random password will be generated and shown.`)) {
            document.getElementById('reset_id').value = btn.getAttribute('data-id');
            document.getElementById('resetPasswordForm').submit();
        }
    }
    function toggleAdminStatus(btn) {
        const currentStatus = btn.getAttribute('data-status');
        const newAction = currentStatus === 'active' ? 'suspend' : 'activate';
        if (confirm(`Are you sure you want to ${newAction} this admin?`)) {
            document.getElementById('toggle_id').value = btn.getAttribute('data-id');
            document.getElementById('toggleStatusForm').submit();
        }
    }
    function deleteAdmin(btn) {
        const name = btn.getAttribute('data-name');
        if (confirm(`Permanently delete admin "${name}"? This action cannot be undone.`)) {
            document.getElementById('delete_id').value = btn.getAttribute('data-id');
            document.getElementById('deleteForm').submit();
        }
    }
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
    </main>
</div>

<script>
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
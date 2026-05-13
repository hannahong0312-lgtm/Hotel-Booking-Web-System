<?php
// admin_profile.php - Grand Hotel Melaka
require_once 'admin_header.php';

$admin_id = $_SESSION['admin_id'];
$message = '';
$error = '';
$stmt = $conn->prepare("SELECT id, username, email, role, created_at, updated_at, last_login FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    die("Admin not found.");
}

$current_username = $admin['username'];
$current_email = $admin['email'];
$role = $admin['role'] == 1 ? 'Super Admin' : 'Admin';
$created_at = date('d M Y, H:i', strtotime($admin['created_at']));
$updated_at = date('d M Y, H:i', strtotime($admin['updated_at']));
$last_login = $admin['last_login'] ? date('d M Y, H:i', strtotime($admin['last_login'])) : 'Never';

// handle POST requests for profile update and password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update profile
    if (isset($_POST['update_profile'])) {
        $new_username = trim($_POST['username'] ?? '');
        $new_email = trim($_POST['email'] ?? '');

        $errors = [];
        if (empty($new_username)) {
            $errors[] = 'Username is required.';
        } elseif (strlen($new_username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        }
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required.';
        }

        if (empty($errors)) {
            $check = $conn->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $check->bind_param("si", $new_email, $admin_id);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $error = 'Email already used by another admin.';
            } else {
                $update = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
                $update->bind_param("ssi", $new_username, $new_email, $admin_id);
                if ($update->execute()) {
                    $message = 'Profile updated successfully.';
                    $_SESSION['admin_username'] = $new_username;
                    $current_username = $new_username;
                    $current_email = $new_email;
                    header("Location: admin_profile.php?success=1");
                    exit();
                } else {
                    $error = 'Failed to update profile.';
                }
                $update->close();
            }
            $check->close();
        } else {
            $error = implode('<br>', $errors);
        }
    }

    // change password
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            $error = 'All password fields are required.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $new)) {
            $error = 'New password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $new)) {
            $error = 'New password must contain at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $new)) {
            $error = 'New password must contain at least one number.';
        } elseif ($new !== $confirm) {
            $error = 'New password and confirmation do not match.';
        } else {
            $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->bind_param("i", $admin_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if (password_verify($current, $row['password'])) {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $update->bind_param("si", $hashed, $admin_id);
                if ($update->execute()) {
                    $message = 'Password changed successfully.';
                } else {
                    $error = 'Failed to update password.';
                }
                $update->close();
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}
?>

<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 28px;
    }
    .info-card {
        background: var(--bg-sidebar);
        border-radius: 28px;
        border: 1px solid var(--border-light);
        padding: 28px 24px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    .avatar-wrapper {
        text-align: center;
        margin-bottom: 24px;
    }
    .avatar-circle {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--gold), var(--gold-light));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        box-shadow: 0 8px 20px rgba(197,160,89,0.25);
    }
    .avatar-circle span {
        font-size: 2.5rem;
        font-weight: 600;
        color: #1e1e1e;
        text-transform: uppercase;
    }
    [data-theme="dark"] .avatar-circle span {
        color: #0a0a0a;
    }
    .info-name {
        font-size: 1.3rem;
        font-weight: 600;
        text-align: center;
        margin-bottom: 4px;
    }
    .info-role {
        text-align: center;
        display: inline-block;
        width: 100%;
        background: rgba(197,160,89,0.12);
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gold);
        margin-top: 8px;
    }
    .info-details {
        margin-top: 28px;
        border-top: 1px solid var(--border-light);
        padding-top: 20px;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 14px;
        font-size: 0.85rem;
    }
    .detail-label {
        color: var(--text-secondary);
        font-weight: 500;
    }
    .detail-value {
        color: var(--text-primary);
        font-weight: 500;
    }
    /* 右侧表单卡片 */
    .form-card {
        background: var(--bg-sidebar);
        border-radius: 28px;
        border: 1px solid var(--border-light);
        padding: 28px 32px;
        margin-bottom: 28px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s;
    }
    .form-card:hover {
        box-shadow: var(--shadow-md);
    }
    .form-card h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 24px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        border-left: 4px solid var(--gold);
        padding-left: 16px;
    }
    .form-card h2 i {
        color: var(--gold);
        font-size: 1.1rem;
    }
    .form-group {
        margin-bottom: 22px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-primary);
        font-size: 0.85rem;
    }
    .form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-light);
        border-radius: 20px;
        background: var(--bg-body);
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: 0.2s;
    }
    .form-group input:focus {
        outline: none;
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(197,160,89,0.1);
    }
    .btn-primary {
        background: var(--gold);
        border: none;
        padding: 10px 26px;
        border-radius: 40px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.85rem;
    }
    .btn-primary:hover {
        background: var(--gold-hover);
        transform: translateY(-1px);
    }
    .alert {
        padding: 12px 20px;
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
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        .form-card {
            padding: 20px;
        }
    }
</style>

<div class="profile-grid">
    <div class="info-card">
        <div class="avatar-wrapper">
            <div class="avatar-circle">
                <span><?php echo strtoupper(substr($current_username, 0, 1)); ?></span>
            </div>
            <div class="info-name"><?php echo htmlspecialchars($current_username); ?></div>
            <div class="info-role"><?php echo $role; ?></div>
        </div>
        <div class="info-details">
            <div class="detail-row">
                <span class="detail-label">Admin ID</span>
                <span class="detail-value">#<?php echo $admin['id']; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created at</span>
                <span class="detail-value"><?php echo $created_at; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last update</span>
                <span class="detail-value"><?php echo $updated_at; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last login</span>
                <span class="detail-value"><?php echo $last_login; ?></span>
            </div>
        </div>
    </div>

    <div>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h2><i class="fas fa-user-edit"></i> Personal Information</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
            </form>
        </div>

        <div class="form-card">
            <h2><i class="fas fa-key"></i> Security</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>New Password (min 8 chars, 1 uppercase, 1 lowercase, 1 number)</label>
                    <input type="password" name="new_password">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password">
                </div>
                <button type="submit" name="change_password" class="btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php
?>
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
                toggle.innerHTML = '<i class="fasfa-moon"></i> <span>Dark</span>';
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
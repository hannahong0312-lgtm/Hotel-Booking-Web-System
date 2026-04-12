<?php
// admin_login_process.php - Admin Authentication (Email Login)
session_start();
require_once '../../Shared/config.php';

// 获取表单数据
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// 后端验证
if (empty($email)) {
    $_SESSION['admin_login_error'] = 'Email address is required.';
    header("Location: ../admin_login.php");
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['admin_login_error'] = 'Please enter a valid email address.';
    header("Location: ../admin_login.php");
    exit();
}
if (empty($password)) {
    $_SESSION['admin_login_error'] = 'Password is required.';
    header("Location: ../admin_login.php");
    exit();
}

// 保存邮箱以便返回时回填
$_SESSION['admin_login_email'] = $email;

// 查询管理员（通过 email）
$stmt = $conn->prepare("SELECT id, email, username, password, is_superadmin, status FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    
    if ($admin['status'] !== 'active') {
        $_SESSION['admin_login_error'] = 'Account is inactive or suspended. Contact super administrator.';
        header("Location: ../admin_login.php");
        exit();
    }
    
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_username'] = $admin['username'] ?? $admin['email'];
        $_SESSION['admin_role'] = $admin['is_superadmin'] == 1 ? 'superadmin' : 'admin';
        
        $updateStmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $admin['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        // 清除临时存储的邮箱
        unset($_SESSION['admin_login_email']);
        header("Location: ../admin_dashboard.php");
        exit();
    } else {
        $_SESSION['admin_login_error'] = 'Invalid email or password.';
        header("Location: ../admin_login.php");
        exit();
    }
} else {
    $_SESSION['admin_login_error'] = 'Invalid email or password.';
    header("Location: ../admin_login.php");
    exit();
}

$stmt->close();
$conn->close();
?>
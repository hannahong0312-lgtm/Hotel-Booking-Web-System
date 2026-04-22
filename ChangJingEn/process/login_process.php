<?php
// login_process.php - Grand Hotel Melaka
require_once '../../Shared/config.php';

session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer') {
    redirect('../profile.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied.');
}

$email = cleanInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

$errors = [];

// 输入验证
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (empty($password)) {
    $errors['password'] = 'Password is required.';
}

if (empty($errors)) {
    // 查询用户，包含 email_verified 字段
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status, email_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
   
    if ($row && password_verify($password, $row['password'])) {
        // 检查账户状态
        if ($row['status'] !== 'active') {
            $errors['general'] = 'Your account is not activated yet. Please contact admin.';
        } 
        // 检查邮箱是否已验证（OTP 验证后为 1）
        elseif ($row['email_verified'] == 0) {
            $errors['general'] = 'Please verify your email address before logging in. Check your inbox (or spam folder) for the verification code.';
        } 
        else {
            // 登录成功，更新最后登录时间
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $row['id']);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['user_role'] = $row['role'];
            
            // Remember me
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30);
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
                $tokenStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $tokenStmt->bind_param("si", $token, $row['id']);
                $tokenStmt->execute();
                $tokenStmt->close();
            }

            redirect('../profile.php');
        }
    } else {
        $errors['general'] = 'Invalid email or password.';
    }
}

// 如果有错误，重定向回登录页
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_email'] = $email;
    redirect('../login.php');
}
?>
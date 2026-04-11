<?php
// login_process.php - 处理顾客登录请求
require_once '../../Shared/config.php';

session_start();

// 如果已经登录，直接跳转个人资料页
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer') {
    redirect('../../ChongEeLynn/accommodation.php');
}

// 仅接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied.');
}

$email = cleanInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

$errors = [];

// 验证
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (empty($password)) {
    $errors['password'] = 'Password is required.';
}

// 如果没有基本错误，再查询数据库
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row && password_verify($password, $row['password'])) {
        if ($row['status'] !== 'active') {
            $errors['general'] = 'Your account is not activated yet. Please contact admin.';
        } else {
            // 登录成功，更新 last_login
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $row['id']);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['user_role'] = $row['role'];  // 实际总是 customer

            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + (86400 * 30);
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
                $tokenStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $tokenStmt->bind_param("si", $token, $row['id']);
                $tokenStmt->execute();
                $tokenStmt->close();
            }

            redirect('../../ChongEeLynn/accommodation.php');
        }
    } else {
        $errors['general'] = 'Invalid email or password.';
    }
}

// 如果有错误，存储到 session 并重定向回登录页
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_email'] = $email;  // 保留用户输入的邮箱
    redirect('login.php');
}
?>
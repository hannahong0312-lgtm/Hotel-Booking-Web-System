<?php
// register_process.php - 处理顾客注册请求
require_once '../../Shared/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 权限与请求方式检查
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer') {
    redirect('../profile.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied.');
}

// 获取并清理输入
$first_name = cleanInput($_POST['first_name'] ?? '');
$last_name  = cleanInput($_POST['last_name']  ?? '');
$email      = cleanInput($_POST['email']      ?? '');
$phone      = cleanInput($_POST['phone']      ?? '');
$country    = cleanInput($_POST['country']    ?? '');
$password   = $_POST['password']         ?? '';
$confirm    = $_POST['confirm_password'] ?? '';
$subscribe  = isset($_POST['subscribe']) ? 1 : 0;
$terms      = isset($_POST['terms']);

$input_data = [
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'email'      => $email,
    'phone'      => $phone,
    'country'    => $country,
    'subscribe'  => $subscribe,
    'terms'      => $terms
];

$errors = [];

// 姓名验证
if (empty($first_name)) {
    $errors['first_name'] = 'First name is required.';
} elseif (strlen($first_name) < 2) {
    $errors['first_name'] = 'First name must be at least 2 characters.';
}

if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required.';
} elseif (strlen($last_name) < 2) {
    $errors['last_name'] = 'Last name must be at least 2 characters.';
}

// 邮箱验证
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors['email'] = 'This email is already registered.';
    }
    $stmt->close();
}

// 手机号验证
if (empty($phone)) {
    $errors['phone'] = 'Phone number is required.';
} elseif (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}

// 国家验证
if (empty($country)) {
    $errors['country'] = 'Please select your country/region.';
}

// ========== 新密码规则验证（与前端规则清单一致）==========
if (empty($password)) {
    $errors['password'] = 'Password is required.';
} else {
    if (strlen($password) < 8 || strlen($password) > 32) {
        $errors['password'] = 'Password must be 8–32 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $password)) {
        $errors['password'] = 'Password must contain at least one number or special character.';
    }
}
// ========== 密码规则验证结束 ==========

if ($password !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

if (!$terms) {
    $errors['terms'] = 'You must agree to the Terms & Conditions.';
}

// 错误处理
if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old']    = $input_data;
    redirect('../register.php');
}

// 数据库插入
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role       = 'customer';
$status     = 'active';
$created_at = date('Y-m-d H:i:s');

$query = "INSERT INTO users (first_name, last_name, email, phone, country, password, role, status, subscribe, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssssssis", $first_name, $last_name, $email, $phone, $country, $hashed_password, $role, $status, $subscribe, $created_at);

if ($stmt->execute()) {
    $new_user_id = $stmt->insert_id;
    
    $_SESSION['user_id']    = $new_user_id;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name']  = $first_name . ' ' . $last_name;
    $_SESSION['user_role']  = 'customer';
    
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $new_user_id);
    $updateStmt->execute();
    $updateStmt->close();
    $stmt->close();
    
    redirect('../profile.php');
} else {
    $errors['general']      = 'Registration failed. Please try again later.';
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old']    = $input_data;
    redirect('../register.php');
}
?>
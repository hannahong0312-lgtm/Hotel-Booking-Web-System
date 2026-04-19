<?php
// admin_login_process.php 
session_start();
require_once '../../Shared/config.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// validation
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

$_SESSION['admin_login_email'] = $email;

// check admin by email
$stmt = $conn->prepare("SELECT id, email, username, password, is_superadmin, status FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    //check admin account status
    if ($admin['status'] !== 'active') {
        $_SESSION['admin_login_error'] = 'Account is inactive or suspended. Contact super administrator.';
        header("Location: ../admin_login.php");
        exit();
    }
    //verify password
    if (password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_username'] = $admin['username'] ?? $admin['email'];
        $_SESSION['admin_role'] = $admin['is_superadmin'] == 1 ? 'superadmin' : 'admin';
        
        //update last login time
        $updateStmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $updateStmt->bind_param("i", $admin['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
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
<?php
// login_process.php 
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

// Input validation 
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if (empty($password)) {
    $errors['password'] = 'Password is required.';
}

if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
   
    if ($row && password_verify($password, $row['password'])) {
        // Check if account is active
        if ($row['status'] !== 'active') {
            $errors['general'] = 'Your account is not activated yet. Please contact admin.';
        } else {
            // Login success (update last login time)
            $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $row['id']);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['user_role'] = $row['role'];  
            
            // Remember me (generate token)
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

// if error, redirect back to login page
if (!empty($errors)) {
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_email'] = $email;  
    redirect('../login.php');
}
?>
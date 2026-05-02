<?php
// process_reset.php - Grand Hotel Melaka 
require_once __DIR__ . '/../../Shared/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_reset'])) {
    http_response_code(403);
    exit('Access denied.');
}

$token = $_POST['token'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($token) || empty($email)) {
    $errors[] = 'Invalid request.';
}

if (empty($password)) {
    $errors[] = 'New password is required.';
} else {
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors[] = 'Password must be 8–16 characters.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $password)) {
        $errors[] = 'Password must contain at least one number or special character.';
    }
}

if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}

if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE LOWER(email) = LOWER(?) AND reset_token = ? AND reset_expires > UTC_TIMESTAMP()");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $errors[] = 'Invalid or expired reset link. Please request a new one.';
        $clean = $conn->prepare("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE LOWER(email) = LOWER(?)");
        $clean->bind_param("s", $email);
        $clean->execute();
        $clean->close();
    } else {
        $user = $result->fetch_assoc();
        $current_hash = $user['password'];

        if (password_verify($password, $current_hash)) {
            $_SESSION['same_password_error'] = true;
            $errors[] = 'New password cannot be the same as your current password. Please choose a different one.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE LOWER(email) = LOWER(?)");
            $update->bind_param("ss", $hashed, $email);
            if ($update->execute()) {
                $_SESSION['reset_message'] = 'Password reset successful! Please login with your new password.';
                $_SESSION['reset_message_type'] = 'success';
                redirect('../login.php');
            } else {
                $errors[] = 'Database error, please try again.';
            }
            $update->close();
        }
    }
    $stmt->close();
}

if (!empty($errors)) {
    $_SESSION['reset_message'] = implode(' ', $errors);
    $_SESSION['reset_message_type'] = 'error';
    redirect("../reset_password.php?token=" . urlencode($token) . "&email=" . urlencode($email));
}
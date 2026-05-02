<?php
// process_forgot.php - Grand Hotel Melaka 
require_once __DIR__ . '/../../Shared/config.php';
require_once __DIR__ . '/../mail_functions.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_forgot'])) {
    http_response_code(403);
    exit('Access denied.');
}

$email = trim($_POST['email'] ?? '');
$error = null;

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
} else {
    $stmt = $conn->prepare("SELECT id, first_name, email_verified FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $error = 'This email address is not registered in our system.';
    } elseif ($user['email_verified'] == 0) {
        $error = 'Please verify your email address before resetting password. Check your inbox for the verification OTP.';
    }
}

if ($error) {
    $_SESSION['reset_field_error'] = $error;
    $_SESSION['reset_email'] = $email;  // 保留输入的邮箱
    redirect('../forgot_password.php');
}

$clean = $conn->prepare("UPDATE users SET reset_token = NULL, reset_expires = NULL WHERE LOWER(email) = LOWER(?)");
$clean->bind_param("s", $email);
$clean->execute();
$clean->close();

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE LOWER(email) = LOWER(?)");
$update->bind_param("sss", $token, $expires, $email);
$update->execute();
$update->close();

$reset_link = "http://localhost/Hotel-Booking-Web-System/ChangJingEn/reset_password.php?token=" . urlencode($token) . "&email=" . urlencode($email);

$subject = "Password Reset - Grand Hotel Melaka";
$html = "<div style='font-family: Arial; max-width:600px;'><h2>Hello {$user['first_name']},</h2><p>We received a request to reset your password. Click the button below (valid for 1 hour):</p><div style='margin:30px 0;'><a href='{$reset_link}' style='background:#C5A059; color:white; padding:12px 24px; text-decoration:none; border-radius:40px;'>Reset Password</a></div><p>If the button doesn't work, copy this link:<br>{$reset_link}</p><p>If you didn't request this, ignore this email.</p></div>";
$alt = "Reset your password: $reset_link";

$sent = sendCustomEmail($email, $user['first_name'], $subject, $html, $alt);

if ($sent) {
    $_SESSION['reset_message'] = 'A reset link has been sent to your email. Check your inbox (or spam folder).';
    $_SESSION['reset_message_type'] = 'success';
} else {
    $_SESSION['reset_message'] = 'Failed to send email. Please try again later.';
    $_SESSION['reset_message_type'] = 'error';
}

redirect('../forgot_password.php');
?>
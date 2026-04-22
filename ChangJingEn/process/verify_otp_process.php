<?php
// verify_otp_process.php - Grand Hotel Melaka
require_once '../../Shared/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if pending_user_id is set in session
if (!isset($_SESSION['pending_user_id'])) {
    redirect('../register.php');
}

$user_id = $_SESSION['pending_user_id'];
$otp = $_POST['otp'] ?? '';

if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
    $_SESSION['reg_errors']['general'] = 'Please enter a valid 6-digit OTP.';
    redirect('../register.php?step=otp');
}

// Fetch user info to check OTP
$stmt = $conn->prepare("SELECT id, otp_code, otp_expires FROM users WHERE id = ? AND email_verified = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['reg_errors']['general'] = 'Invalid request. Please register again.';
    unset($_SESSION['pending_user_id']);
    redirect('../register.php');
}

// Check if OTP matches
if ($user['otp_code'] !== $otp) {
    $_SESSION['reg_errors']['general'] = 'Invalid OTP. Please try again.';
    redirect('../register.php?step=otp');
}

// Check if OTP is expired
if (strtotime($user['otp_expires']) < time()) {
    $_SESSION['reg_errors']['general'] = 'OTP has expired. Please request a new one.';
    redirect('../register.php?step=otp');
}

// OTP is valid, update user to set email_verified = 1
$update = $conn->prepare("UPDATE users SET email_verified = 1, otp_code = NULL, otp_expires = NULL WHERE id = ?");
$update->bind_param("i", $user_id);
if ($update->execute()) {
    // Fetch user info for auto-login
    $userStmt = $conn->prepare("SELECT first_name, last_name, email, role FROM users WHERE id = ?");
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userData = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();

    // Auto login 
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = $userData['role'];
    $_SESSION['user_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
    $_SESSION['user_email'] = $userData['email'];
    $_SESSION['welcome_message'] = 'Email verified successfully! Welcome to Grand Hotel.';

    unset($_SESSION['pending_user_id']);
    redirect('../profile.php');
} else {
    $_SESSION['reg_errors']['general'] = 'Verification failed. Please contact support.';
    redirect('../register.php?step=otp');
}
?>
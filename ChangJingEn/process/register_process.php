<?php
// register_process.php - Grand Hotel Melaka 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';
require_once '../../Shared/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer') {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied.');
}

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

// Name validation 
$name_pattern = '/^[A-Za-z\s\-\'\.]+$/';
if (empty($first_name)) {
    $errors['first_name'] = 'First name is required.';
} elseif (strlen($first_name) < 2) {
    $errors['first_name'] = 'First name must be at least 2 characters.';
} elseif (!preg_match($name_pattern, $first_name)) {
    $errors['first_name'] = 'First name can only contain letters, spaces, hyphens, dots, and apostrophes.';
}

if (empty($last_name)) {
    $errors['last_name'] = 'Last name is required.';
} elseif (strlen($last_name) < 2) {
    $errors['last_name'] = 'Last name must be at least 2 characters.';
} elseif (!preg_match($name_pattern, $last_name)) {
    $errors['last_name'] = 'Last name can only contain letters, spaces, hyphens, dots, and apostrophes.';
}

// Email validation 
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

// Phone validation
if (empty($phone)) {
    $errors['phone'] = 'Phone number is required.';
} elseif (!preg_match('/^[0-9+\-\s]+$/', $phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}

// Country validation
if (empty($country)) {
    $errors['country'] = 'Please select your country/region.';
}

// Password validation
if (empty($password)) {
    $errors['password'] = 'Password is required.';
} else {
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors['password'] = 'Password must be 8–16 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors['password'] = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $password)) {
        $errors['password'] = 'Password must contain at least one number or special character.';
    }
}

// Confirm Password
if ($password !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

// Agree to Terms 
if (!$terms) {
    $errors['terms'] = 'You must agree to the Terms & Conditions.';
}

if (!empty($errors)) {
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old']    = $input_data;
    redirect('../register.php');
}

// Create OTP
$otp_code = sprintf("%06d", mt_rand(1, 999999));
$otp_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

// Insert user into database
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'customer';
$status = 'active';
$created_at = date('Y-m-d H:i:s');

$query = "INSERT INTO users (first_name, last_name, email, phone, country, password, role, status, subscribe, created_at, otp_code, otp_expires, email_verified) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssssssisss", 
    $first_name, $last_name, $email, $phone, $country, 
    $hashed_password, $role, $status, $subscribe, $created_at, 
    $otp_code, $otp_expires
);

if (!$stmt->execute()) {
    $errors['general'] = 'Registration failed. Please try again later.';
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old']    = $input_data;
    redirect('../register.php');
}

$new_user_id = $conn->insert_id;
$stmt->close();

// Send OTP email
function sendOtpEmail($toEmail, $firstName, $otp) {
    $mail = new PHPMailer(true);
    try {
        // SMTP 配置（使用 Gmail）
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info.grandhotelmelaka@gmail.com';
        $mail->Password   = 'vwaf jose etky kzpt';   // 应用专用密码
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('no-reply@grandhotel.com', 'Grand Hotel Melaka');
        $mail->addAddress($toEmail, $firstName);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Grand Hotel Registration';
        $mail->Body    = "
            <h2>Welcome to Grand Hotel, $firstName!</h2>
            <p>Your One-Time Password (OTP) for email verification is:</p>
            <h1 style='background:#f4f4f4; padding:10px; text-align:center; letter-spacing:5px;'>$otp</h1>
            <p>This OTP is valid for <strong>10 minutes</strong>. Please enter it on the registration page to complete your account activation.</p>
            <p>If you did not register, please ignore this email.</p>
        ";
        $mail->AltBody = "Your OTP code is: $otp. Valid for 10 minutes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

if (sendOtpEmail($email, $first_name, $otp_code)) {
    $_SESSION['pending_user_id'] = $new_user_id;
    $_SESSION['pending_user_email'] = $email;
    $_SESSION['reg_success'] = "A 6-digit OTP has been sent to your email. Please enter it below to verify your account.";
    header('Location: ../register.php?step=otp');
    exit;
} else {
    $conn->query("DELETE FROM users WHERE id = $new_user_id");
    $errors['general'] = 'Failed to send OTP email. Please try again later.';
    $_SESSION['reg_errors'] = $errors;
    $_SESSION['reg_old']    = $input_data;
    redirect('../register.php');
}
?>
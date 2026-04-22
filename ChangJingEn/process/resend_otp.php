<?php
// resend_otp.php - Grand Hotel Melaka
require_once '../../Shared/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['pending_user_id'])) {
    redirect('../register.php');
}

$user_id = $_SESSION['pending_user_id'];

// Fetch user info to check if OTP can be resent
$stmt = $conn->prepare("SELECT email, first_name FROM users WHERE id = ? AND email_verified = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['reg_errors']['general'] = 'Unable to resend OTP. Please register again.';
    unset($_SESSION['pending_user_id']);
    redirect('../register.php');
}

// Create new OTP
$new_otp = sprintf("%06d", mt_rand(1, 999999));
$new_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE id = ?");
$update->bind_param("ssi", $new_otp, $new_expires, $user_id);
$update->execute();
$update->close();

// Resend OTP email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';

function resendOtpMail($toEmail, $firstName, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shillachang9@gmail.com';
        $mail->Password   = 'zeox hdgt zhpu ghvz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('no-reply@grandhotel.com', 'Grand Hotel Melaka');
        $mail->addAddress($toEmail, $firstName);
        $mail->isHTML(true);
        $mail->Subject = 'Your New OTP for Grand Hotel';
        $mail->Body    = "<h2>Hello $firstName,</h2><p>Your new OTP is: <strong>$otp</strong><br>Valid for 10 minutes.</p>";
        $mail->AltBody = "Your new OTP is: $otp";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (resendOtpMail($user['email'], $user['first_name'], $new_otp)) {
    $_SESSION['reg_success'] = "A new OTP has been sent to your email.";
} else {
    $_SESSION['reg_errors']['general'] = "Failed to resend OTP. Please try again later.";
}
redirect('../register.php?step=otp');
?>
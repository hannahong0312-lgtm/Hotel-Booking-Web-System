<?php
// test_mail.php - Test PHPMailer configuration
// Place this file in: C:\xampp\htdocs\Hotel-Booking-Web-System\Hannah\

require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ========== CONFIGURE YOUR CREDENTIALS HERE ==========
$smtpUsername = 'your-email@gmail.com';   // ← CHANGE: Your Gmail address
$smtpPassword = 'your-app-password';      // ← CHANGE: 16-char App Password
$testRecipient = 'recipient@example.com'; // ← CHANGE: Your own email to receive test
// =====================================================

echo "<h2>PHPMailer Test</h2>";

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    
    // Optional: Disable SSL verification (only for testing if certificate issues)
    // $mail->SMTPOptions = array(
    //     'ssl' => array(
    //         'verify_peer' => false,
    //         'verify_peer_name' => false,
    //         'allow_self_signed' => true
    //     )
    // );
    
    // Recipients
    $mail->setFrom($smtpUsername, 'Grand Hotel Test');
    $mail->addAddress($testRecipient);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from Grand Hotel';
    $mail->Body    = '<h3>Test Successful!</h3><p>Your PHPMailer is working correctly.</p>';
    
    $mail->send();
    echo "<p style='color:green;'>✅ Email sent successfully to <strong>" . htmlspecialchars($testRecipient) . "</strong>!</p>";
    echo "<p>Check your inbox (and spam folder).</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Mailer Error: " . $mail->ErrorInfo . "</p>";
    echo "<p>Check your username/password and that 2FA + App Password is set up.</p>";
}
?>
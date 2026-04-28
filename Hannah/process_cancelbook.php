<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once '../Shared/config.php';   // provides $conn, session start
require_once '../Shared/header.php';   // may include additional checks

// Only POST with JSON allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!$data || !isset($data['booking_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$bookingId = (int)$data['booking_id'];
$reason    = trim($data['reason'] ?? 'Cancelled by user');
$userId    = $_SESSION['user_id'] ?? 0;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// --------------------------------------------------------------
// 1. Fetch booking details with necessary fields
// --------------------------------------------------------------
$sql = "SELECT b.*, 
               u.email AS user_email, u.first_name, u.points AS current_points,
               r.id AS room_id,
               p.points_used, p.points_earned
        FROM book b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        LEFT JOIN payment p ON b.payment_id = p.id
        WHERE b.id = ? AND b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}
if ($booking['status'] !== 'confirmed') {
    echo json_encode(['success' => false, 'message' => 'Booking is already cancelled or not confirmed']);
    exit;
}

// --------------------------------------------------------------
// 2. Check 24‑hour cancellation rule (check‑in at 15:00)
// --------------------------------------------------------------
$checkinTime = strtotime($booking['check_in'] . ' 15:00:00');
$now = time();
$hoursDiff = ($checkinTime - $now) / 3600;

if ($hoursDiff < 24) {
    echo json_encode(['success' => false, 'message' => 'Cannot cancel: less than 24 hours before check‑in (3:00 PM)']);
    exit;
}

// --------------------------------------------------------------
// 3. Perform cancellation in a transaction
// --------------------------------------------------------------
mysqli_begin_transaction($conn);
try {
    // 3.1 Update booking status
    $updateBook = "UPDATE book SET status = 'cancelled', cancellation_reason = ? WHERE id = ?";
    $stmt = $conn->prepare($updateBook);
    $stmt->bind_param("si", $reason, $bookingId);
    $stmt->execute();

    // 3.2 Free the room (increase availability)
    $updateRoom = "UPDATE rooms SET rooms_available = rooms_available + 1 WHERE id = ?";
    $stmt = $conn->prepare($updateRoom);
    $stmt->bind_param("i", $booking['room_id']);
    $stmt->execute();

    // 3.3 Adjust user points 
    //     (refund points_used, remove points_earned)
    $used = (int)($booking['points_used'] ?? 0);
    $earn = (int)($booking['points_earned'] ?? 0);
    $newPoints = $booking['current_points'] + $used - $earn;
    $updatePoints = "UPDATE users SET points = ? WHERE id = ?";
    $stmt = $conn->prepare($updatePoints);
    $stmt->bind_param("ii", $newPoints, $userId);
    $stmt->execute();

    // 3.4 Mark payment as cancelled
    $updatePayment = "UPDATE payment SET status = 'cancelled' WHERE book_id = ?";
    $stmt = $conn->prepare($updatePayment);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();

    mysqli_commit($conn);

    // --------------------------------------------------------------
    // 4. Send email notification (errors are logged, not shown to user)
    // --------------------------------------------------------------
    try {
        use PHPMailer\PHPMailer\{PHPMailer, Exception};
        require '../PHPMailer-master/src/Exception.php';
        require '../PHPMailer-master/src/PHPMailer.php';
        require '../PHPMailer-master/src/SMTP.php';

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'grandhotelreservation67@gmail.com';
        $mail->Password   = 'Grandhotel67';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('grandhotelreservation67@gmail.com', 'Grand Hotel Melaka');
        $mail->addAddress($booking['user_email'], $booking['first_name']);
        $mail->isHTML(true);
        $mail->Subject = 'Booking Cancellation Confirmed';
        $mail->Body = "
            <h3>Dear {$booking['first_name']},</h3>
            <p>Your booking (Ref: {$booking['booking_ref']}) has been cancelled successfully.</p>
            <p>Your points have been refunded back to your account.</p>
            <br>
            <p>Thank you,<br>Grand Hotel Melaka Team</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        // Log error but don't break the cancellation
        error_log("PHPMailer Error: " . $e->getMessage());
    }

    // Success response
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
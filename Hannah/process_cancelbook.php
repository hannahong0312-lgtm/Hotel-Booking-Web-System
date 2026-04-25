<?php
// cancel_booking.php - handles cancellation, restores room, adjusts points, sends emails
session_start();
header('Content-Type: application/json');
include '../Shared/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = $input['booking_id'] ?? 0;
$reason = trim($input['reason'] ?? '');

if (!$booking_id || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Fetch booking details with user email, room info, payment details
$sql = "SELECT b.*, u.email as user_email, u.first_name, u.last_name, 
               r.name as room_name, r.id as room_id, r.rooms_available,
               p.points_used, p.points_earned, p.grand_total
        FROM book b
        JOIN users u ON b.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        LEFT JOIN payment p ON b.payment_id = p.id
        WHERE b.id = $booking_id AND b.user_id = $user_id";
$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit();
}

if ($booking['status'] !== 'confirmed') {
    echo json_encode(['success' => false, 'message' => 'Booking already cancelled']);
    exit();
}

// Check if cancellation is allowed (more than 24h before check-in)
$checkin_time = strtotime($booking['check_in']);
$now = time();
$hours_diff = ($checkin_time - $now) / 3600;
if ($hours_diff < 24) {
    echo json_encode(['success' => false, 'message' => 'Cancellation only allowed at least 24 hours before check-in']);
    exit();
}

// Begin transaction
mysqli_begin_transaction($conn);
try {
    // 1. Update booking status
    $update_book = "UPDATE book SET status = 'cancelled', cancellation_reason = '$reason', cancelled_at = NOW() WHERE id = $booking_id";
    mysqli_query($conn, $update_book);
    
    // 2. Increase room availability
    $room_id = $booking['room_id'];
    $update_room = "UPDATE rooms SET rooms_available = rooms_available + 1 WHERE id = $room_id";
    mysqli_query($conn, $update_room);
    
    // 3. Adjust user points: add back used points, subtract earned points
    $points_used = (int)$booking['points_used'];
    $points_earned = (int)$booking['points_earned'];
    $user_points_query = "SELECT points FROM users WHERE id = $user_id";
    $user_points_res = mysqli_query($conn, $user_points_query);
    $current_points = mysqli_fetch_assoc($user_points_res)['points'];
    $new_points = $current_points + $points_used - $points_earned;
    $update_points = "UPDATE users SET points = $new_points WHERE id = $user_id";
    mysqli_query($conn, $update_points);
    
    // 4. Update payment status to refunded
    $update_payment = "UPDATE payment SET status = 'refunded' WHERE book_id = $booking_id";
    mysqli_query($conn, $update_payment);
    
    mysqli_commit($conn);
    
    // --- Send email notifications ---
    $customer_email = $booking['user_email'];
    $customer_name = trim($booking['first_name'] . ' ' . $booking['last_name']);
    $admin_email = "info.grandhotelmelaka@gmail.com";
    $booking_ref = $booking['booking_ref'];
    $room_name = $booking['room_name'];
    $check_in = date('d F Y', strtotime($booking['check_in']));
    $check_out = date('d F Y', strtotime($booking['check_out']));
    $total_paid = number_format($booking['grand_total'], 2);
    
    // Subject & body for customer
    $customer_subject = "Booking Cancellation Confirmation - Refund Processing";
    $customer_message = "Dear $customer_name,\n\n";
    $customer_message .= "Your booking (Ref: $booking_ref) for $room_name from $check_in to $check_out has been successfully cancelled.\n";
    $customer_message .= "Refund amount: RM $total_paid\n";
    $customer_message .= "The refund will be processed within 3 business days to your original payment method.\n\n";
    $customer_message .= "If you have any questions, please contact us at info.grandhotelmelaka@gmail.com\n\n";
    $customer_message .= "Thank you,\nGrand Hotel Melaka Team";
    
    // Subject & body for admin
    $admin_subject = "Booking Cancellation - Refund Required";
    $admin_message = "A booking has been cancelled by the customer.\n\n";
    $admin_message .= "Customer: $customer_name ($customer_email)\n";
    $admin_message .= "Booking Ref: $booking_ref\n";
    $admin_message .= "Room: $room_name\n";
    $admin_message .= "Dates: $check_in to $check_out\n";
    $admin_message .= "Total Paid: RM $total_paid\n";
    $admin_message .= "Cancellation Reason: $reason\n\n";
    $admin_message .= "Please process the refund within 3 days.";
    
    // Headers
    $headers = "From: Grand Hotel Melaka <noreply@grandhotel.com>\r\n";
    $headers .= "Reply-To: info.grandhotelmelaka@gmail.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Send emails (suppress errors if mail not configured, but log)
    @mail($customer_email, $customer_subject, $customer_message, $headers);
    @mail($admin_email, $admin_subject, $admin_message, $headers);
    
    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully. Refund will be processed within 3 days.']);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => 'Cancellation failed: ' . $e->getMessage()]);
}
?>
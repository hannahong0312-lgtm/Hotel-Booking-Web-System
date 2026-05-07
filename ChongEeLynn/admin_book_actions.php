<?php
// admin_book_actions.php - Handle AJAX check-in/check-out
require_once '../Shared/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$booking_id = (int)($data['booking_id'] ?? 0);

if (!$booking_id) {
    echo json_encode(['success' => false, 'error' => 'Booking ID required']);
    exit;
}

if ($action === 'checkin') {
    // Check if check-in date is valid (not before booked check_in)
    $stmt = $conn->prepare("SELECT check_in, status, checked_in_at FROM book WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        exit;
    }
    
    if ($booking['checked_in_at']) {
        echo json_encode(['success' => false, 'error' => 'Guest already checked in']);
        exit;
    }
    
    $today = date('Y-m-d');
    $check_in_date = $booking['check_in'];
    
    if ($today < $check_in_date) {
        echo json_encode(['success' => false, 'error' => 'Cannot check in before ' . $check_in_date]);
        exit;
    }
    
    if ($booking['status'] !== 'confirmed') {
        echo json_encode(['success' => false, 'error' => 'Only confirmed bookings can be checked in']);
        exit;
    }
    
    // Update check-in
    $stmt = $conn->prepare("UPDATE book SET checked_in_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    
} elseif ($action === 'checkout') {
    // Get booking details for late checkout penalty
    $stmt = $conn->prepare("SELECT user_id, check_out, status, checked_in_at, checked_out_at FROM book WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        echo json_encode(['success' => false, 'error' => 'Booking not found']);
        exit;
    }
    
    if ($booking['checked_out_at']) {
        echo json_encode(['success' => false, 'error' => 'Guest already checked out']);
        exit;
    }
    
    if (!$booking['checked_in_at']) {
        echo json_encode(['success' => false, 'error' => 'Guest must be checked in first']);
        exit;
    }
    
    // Calculate late checkout penalty (if any)
    $today = new DateTime();
    $check_out_date = new DateTime($booking['check_out']);
    $late_penalty = 0;
    
    if ($today > $check_out_date) {
        $diff = $today->diff($check_out_date);
        $late_hours = $diff->h + ($diff->days * 24);
        $late_penalty = ceil($late_hours / 4) * 5; // 5 points per 4 hours late
    }
    
    $conn->begin_transaction();
    
    try {
        // Update checkout and penalty
        $stmt = $conn->prepare("UPDATE book SET checked_out_at = NOW(), late_checkout_penalty = ? WHERE id = ?");
        $stmt->bind_param("di", $late_penalty, $booking_id);
        $stmt->execute();
        
        // Deduct points if late
        if ($late_penalty > 0) {
            $stmt = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
            $stmt->bind_param("ii", $late_penalty, $booking['user_id']);
            $stmt->execute();
        }
        
        // Update booking status to completed
        $stmt = $conn->prepare("UPDATE book SET status = 'completed' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'late_penalty' => $late_penalty]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
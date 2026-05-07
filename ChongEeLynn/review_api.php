<?php
// review_api.php - Single API file for all review operations
session_start();
require_once __DIR__ . '/../Shared/config.php';

header('Content-Type: 'application/json');

// Check if user is logged in for user-side actions
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// If POST with JSON body, decode it
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['action'])) {
    $action = $input['action'];
}

// Route to appropriate function based on action
switch ($action) {
    
    // Check if user has any pending review (for auto-popup)
    case 'check_pending':
        if (!$is_logged_in) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }
        
        $sql = "SELECT 
                    b.id as booking_id, 
                    b.booking_ref,
                    r.name as room_name,
                    r.id as room_id
                FROM book b
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE b.user_id = ? 
                AND b.checked_out_at IS NOT NULL
                AND b.review_points_awarded = 0
                AND b.review_skipped = 0
                ORDER BY b.checked_out_at DESC
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            echo json_encode([
                'success' => true,
                'has_review' => true,
                'booking' => $booking
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'has_review' => false
            ]);
        }
        break;
    
    // Check if a specific booking has been reviewed
    case 'check_booking':
        if (!$is_logged_in) {
            echo json_encode(['reviewed' => false, 'error' => 'Not logged in']);
            exit;
        }
        
        $booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : (isset($input['booking_id']) ? (int)$input['booking_id'] : 0);
        
        if (!$booking_id) {
            echo json_encode(['reviewed' => false, 'error' => 'No booking ID']);
            exit;
        }
        
        $sql = "SELECT review_points_awarded FROM book WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['reviewed' => $row['review_points_awarded'] == 1]);
        } else {
            echo json_encode(['reviewed' => false]);
        }
        break;
    
    // Get room_id for a booking
    case 'get_room':
        if (!$is_logged_in) {
            echo json_encode(['room_id' => 0]);
            exit;
        }
        
        $booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : (isset($input['booking_id']) ? (int)$input['booking_id'] : 0);
        
        if (!$booking_id) {
            echo json_encode(['room_id' => 0]);
            exit;
        }
        
        $sql = "SELECT room_id FROM book WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['room_id' => $row['room_id']]);
        } else {
            echo json_encode(['room_id' => 0]);
        }
        break;
    
    // Get current user points
    case 'get_points':
        if (!$is_logged_in) {
            echo json_encode(['points' => 0]);
            exit;
        }
        
        $sql = "SELECT points FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode(['points' => $row['points']]);
        } else {
            echo json_encode(['points' => 0]);
        }
        break;
    
    // Submit review and award 10 points
    case 'submit_review':
        if (!$is_logged_in) {
            echo json_encode(['success' => false, 'error' => 'Please login to submit review']);
            exit;
        }
        
        $booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
        $room_id = isset($input['room_id']) ? (int)$input['room_id'] : 0;
        $rating = isset($input['rating']) ? (int)$input['rating'] : 0;
        $comment = isset($input['comment']) ? trim($input['comment']) : '';
        
        // Validation
        if (!$booking_id || !$room_id) {
            echo json_encode(['success' => false, 'error' => 'Missing booking or room information']);
            exit;
        }
        
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'error' => 'Please select a rating from 1 to 5 stars']);
            exit;
        }
        
        if (empty($comment)) {
            echo json_encode(['success' => false, 'error' => 'Please write a review comment']);
            exit;
        }
        
        // Verify booking belongs to user and is eligible
        $sql = "SELECT review_points_awarded, checked_out_at FROM book WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        
        if (!$booking) {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
            exit;
        }
        
        if ($booking['review_points_awarded'] == 1) {
            echo json_encode(['success' => false, 'error' => 'You already reviewed this booking']);
            exit;
        }
        
        if (!$booking['checked_out_at']) {
            echo json_encode(['success' => false, 'error' => 'You can only review after checkout']);
            exit;
        }
        
        $conn->begin_transaction();
        
        try {
            // Insert review
            $sql = "INSERT INTO review (user_id, booking_id, room_id, r_rating, r_comment, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiis", $user_id, $booking_id, $room_id, $rating, $comment);
            $stmt->execute();
            
            // Mark booking as reviewed
            $sql = "UPDATE book SET review_points_awarded = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            
            // Add 10 points to user
            $sql = "UPDATE users SET points = points + 10 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your review! You earned 10 points.',
                'new_points' => true
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
    
    // Skip review (Later button)
    case 'skip_review':
        if (!$is_logged_in) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }
        
        $booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;
        
        if (!$booking_id) {
            echo json_encode(['success' => false, 'error' => 'Booking ID required']);
            exit;
        }
        
        $sql = "UPDATE book SET review_skipped = 1 WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
?>
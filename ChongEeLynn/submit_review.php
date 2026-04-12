<?php
session_start();
include '../Shared/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate inputs
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$user_id = $_SESSION['user_id'];

// Validate inputs
if ($room_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a review comment']);
    exit;
}

if (strlen($comment) > 255) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot exceed 255 characters']);
    exit;
}

// Check if room exists and is active
$room_check = $conn->query("SELECT id FROM rooms WHERE id = $room_id AND is_active = 1");
if ($room_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Room not found']);
    exit;
}

// Check if user already reviewed this room
$check_sql = "SELECT REV_ID FROM REVIEW WHERE USER_ID = $user_id AND ROOM_ID = $room_id";
$check_result = $conn->query($check_sql);
if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this room']);
    exit;
}

// Insert the review
$comment_escaped = mysqli_real_escape_string($conn, $comment);
$insert_sql = "INSERT INTO REVIEW (USER_ID, ROOM_ID, R_RATING, R_COMMENT, CREATED_AT) 
               VALUES ($user_id, $room_id, $rating, '$comment_escaped', NOW())";

if ($conn->query($insert_sql)) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();
?>
<?php
session_start();
include '../Shared/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../ChangJingEn/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_booking'])) {
    header('Location: payment.php');
    exit();
}

// Retrieve all form data with fallbacks
$room_id = (int)($_POST['room_id'] ?? 0);
$room_price = (float)($_POST['room_price'] ?? 0);
$check_in = isset($_POST['check_in']) ? mysqli_real_escape_string($conn, $_POST['check_in']) : date('Y-m-d');
$check_out = isset($_POST['check_out']) ? mysqli_real_escape_string($conn, $_POST['check_out']) : date('Y-m-d', strtotime('+2 days'));
$guests = (int)($_POST['guests'] ?? 2);
$nights = (int)($_POST['nights'] ?? 1);
$subtotal = (float)($_POST['subtotal'] ?? 0);
$discount_amount = (float)($_POST['discount_amount'] ?? 0);
$points_deduction = (float)($_POST['points_deduction'] ?? 0);
$points_used = (int)($_POST['points_used'] ?? 0);
$payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : 'credit_card';
$special_requests = isset($_POST['special_requests']) ? mysqli_real_escape_string($conn, $_POST['special_requests']) : '';
$fullname = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
$ic_no = isset($_POST['ic_no']) ? trim(mysqli_real_escape_string($conn, $_POST['ic_no'])) : '';

// ---- FIX: Get nationality from database if POST is missing ----
if (!isset($_POST['nationality']) || empty($_POST['nationality'])) {
    $user_query = "SELECT country FROM users WHERE id = $user_id";
    $user_res = mysqli_query($conn, $user_query);
    $user_row = mysqli_fetch_assoc($user_res);
    $nationality = ($user_row['country'] == 'Malaysia') ? 'malaysian' : 'foreigner';
} else {
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
}

// Get user's current points from database
$user_point_query = "SELECT points FROM users WHERE id = $user_id";
$point_res = mysqli_query($conn, $user_point_query);
$user_points = mysqli_fetch_assoc($point_res)['points'];

// Use points if checkbox was checked
$use_points_checkbox = isset($_POST['use_points']);
if ($use_points_checkbox && $points_used == 0 && $user_points > 0) {
    $after_discount = $subtotal - $discount_amount;
    if ($after_discount < 0) $after_discount = 0;
    $max_deduction = min($after_discount, floor($user_points / 100));
    $points_deduction = $max_deduction;
    $points_used = $max_deduction * 100;
}

// Credit card last4 & expiry
$card_last4 = null;
$card_expiry = null;
if ($payment_method == 'credit_card') {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_last4 = substr($card_number, -4);
    $card_expiry = isset($_POST['expiry_date']) ? mysqli_real_escape_string($conn, $_POST['expiry_date']) : null;
}

// ---- Determine Malaysian by IC (12 digits) ----
$is_malaysian = (preg_match('/^\d{12}$/', $ic_no)) ? true : false;

// ---- TAX CALCULATIONS ----
$total_before_tax = $subtotal - $discount_amount - $points_deduction;
if ($total_before_tax < 0) $total_before_tax = 0;
$sst_tax = $total_before_tax * 0.12;
$tourism_tax = ($is_malaysian) ? 0 : (10 * $nights);   // RM10 per night for foreigners
$service_fee = $total_before_tax * 0.05;
$grand_total = $total_before_tax + $sst_tax + $tourism_tax + $service_fee;

$points_per_rm = 10;
$points_earned = floor($subtotal * $points_per_rm);
$booking_ref = 'BK' . strtoupper(uniqid());

// 1. Insert booking
$insert_booking = "INSERT INTO book (booking_ref, user_id, room_id, check_in, check_out, guests, 
                    grand_total, nationality, special_requests, status, created_at)
                    VALUES ('$booking_ref', $user_id, $room_id, '$check_in', '$check_out', $guests,
                    $grand_total, '$nationality', '$special_requests', 'confirmed', NOW())";

if (!mysqli_query($conn, $insert_booking)) {
    die("Booking failed: " . mysqli_error($conn));
}
$book_id = mysqli_insert_id($conn);

// 2. Insert payment (with ic_no column)
$transaction_id = 'TXN' . strtoupper(uniqid());

$insert_payment = "INSERT INTO payment (book_id, user_id, method, card_no, card_expiry, transaction_id,
                    subtotal, grand_total, points_used, points_deduction_amount, points_earned,
                    sst_tax, foreigner_tax, service_fee, ic_no, created_at, status)
                    VALUES (
                        $book_id, $user_id, '$payment_method', 
                        " . ($card_last4 ? "'$card_last4'" : "NULL") . ", 
                        " . ($card_expiry ? "'$card_expiry'" : "NULL") . ", 
                        '$transaction_id',
                        $subtotal, $grand_total, $points_used, $points_deduction, $points_earned,
                        $sst_tax, $tourism_tax, $service_fee, '$ic_no', NOW(), 'confirmed'
                    )";

if (!mysqli_query($conn, $insert_payment)) {
    mysqli_query($conn, "DELETE FROM book WHERE id = $book_id");
    die("Payment recording failed: " . mysqli_error($conn));
}
$payment_id = mysqli_insert_id($conn);

// 3. Link payment to booking
mysqli_query($conn, "UPDATE book SET payment_id = $payment_id WHERE id = $book_id");

// 4. Update user points
$current_points = $user_points;
$new_points = $current_points - $points_used + $points_earned;
mysqli_query($conn, "UPDATE users SET points = $new_points WHERE id = $user_id");

// 5. Redirect to confirmation
$_SESSION['last_booking_ref'] = $booking_ref;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Processing Payment</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; background: #f8f8f8; }
        .message-box { background: white; padding: 30px; border-radius: 15px; max-width: 500px; margin: auto; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #C5A059; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
    <meta http-equiv="refresh" content="5;url=confirm_book.php?ref=<?= $booking_ref ?>">
</head>
<body>
    <div class="message-box">
        <h2>Processing Your Payment</h2>
        <div class="spinner"></div>
        <p>We will redirect you to the payment process, please wait patiently...</p>
        <p>If you are not redirected automatically, <a href="confirm_book.php?ref=<?= $booking_ref ?>">click here</a>.</p>
    </div>
</body>
</html>
<?php
exit();
?>
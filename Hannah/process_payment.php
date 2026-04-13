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

// Retrieve all form data
$room_id = (int)$_POST['room_id'];
$room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
$room_price = (float)$_POST['room_price'];
$check_in = mysqli_real_escape_string($conn, $_POST['check_in']);
$check_out = mysqli_real_escape_string($conn, $_POST['check_out']);
$guests = (int)$_POST['guests'];
$nights = (int)$_POST['nights'];
$subtotal = (float)$_POST['subtotal'];
$nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
$discount_amount = (float)$_POST['discount_amount'];
$points_deduction = isset($_POST['points_deduction']) ? (float)$_POST['points_deduction'] : 0;
$points_used = isset($_POST['points_used']) ? (int)$_POST['points_used'] : 0;
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
$special_requests = mysqli_real_escape_string($conn, $_POST['special_requests']);
$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);

// Get user's current points from database (for fallback)
$user_point_query = "SELECT points FROM users WHERE id = $user_id";
$point_res = mysqli_query($conn, $user_point_query);
$user_points = mysqli_fetch_assoc($point_res)['points'];

// Check if the checkbox was checked (use_points) – it comes as '1' or null
$use_points_checkbox = isset($_POST['use_points']);

// If checkbox is checked but points_used is 0, recalculate from available points (fallback)
if ($use_points_checkbox && $points_used == 0 && $user_points > 0) {
    $after_discount = $subtotal - $discount_amount;
    if ($after_discount < 0) $after_discount = 0;
    $max_deduction = min($after_discount, floor($user_points / 100));
    $points_deduction = $max_deduction;
    $points_used = $max_deduction * 100;
}

// For credit card, capture last4 and expiry
$card_last4 = null;
$card_expiry = null;
if ($payment_method == 'credit_card') {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
    $card_last4 = substr($card_number, -4);
    $card_expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']); // note: expiry_date
}

// Calculate final totals
$total_before_tax = $subtotal - $discount_amount - $points_deduction;
if ($total_before_tax < 0) $total_before_tax = 0;
$sst_tax = $total_before_tax * 0.12;
$foreigner_tax = ($nationality == 'foreigner') ? $subtotal * 0.10 : 0;
$service_fee = $total_before_tax * 0.05;
$grand_total = $total_before_tax + $sst_tax + $foreigner_tax + $service_fee;

$points_per_rm = 10;
$points_earned = floor($grand_total * $points_per_rm);

$booking_ref = 'BK' . strtoupper(uniqid());

// Insert into book table (your table name is 'book')
$insert_booking = "INSERT INTO book (booking_ref, user_id, room_id, room_name, check_in, check_out, guests,
                    subtotal, discount_amount, points_used, points_deduction_amount, points_earned,
                    sst_tax, foreigner_tax, service_fee, grand_total, payment_method, nationality, special_requests, status, created_at)
                    VALUES ('$booking_ref', $user_id, $room_id, '$room_name', '$check_in', '$check_out', $guests,
                    $subtotal, $discount_amount, $points_used, $points_deduction, $points_earned,
                    $sst_tax, $foreigner_tax, $service_fee, $grand_total, '$payment_method', '$nationality',
                    '$special_requests', 'confirmed', NOW())";

if (mysqli_query($conn, $insert_booking)) {
    $book_id = mysqli_insert_id($conn);  // this is the auto-increment id from 'book' table
    
    // Generate transaction ID for payment
    $transaction_id = 'TXN' . strtoupper(uniqid());
    
    // Insert into payment table (foreign key references book.id)
    $insert_payment = "INSERT INTO payment (book_id, user_id, method, card_no, card_expiry, transaction_id, amount, payment_date)
                       VALUES ($book_id, $user_id, '$payment_method', " . ($card_last4 ? "'$card_last4'" : "NULL") . ", " . ($card_expiry ? "'$card_expiry'" : "NULL") . ", '$transaction_id', $grand_total, NOW())";
    
    if (mysqli_query($conn, $insert_payment)) {
        // Update user points: deduct used points and add earned points
        $current_points = $user_points; // already fetched
        $new_points = $current_points - $points_used + $points_earned;
        mysqli_query($conn, "UPDATE users SET points = $new_points WHERE id = $user_id");
        
        $_SESSION['last_booking_ref'] = $booking_ref;
        
        // Show processing page
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
    } else {
        echo "Payment recording failed: " . mysqli_error($conn);
    }
} else {
    echo "Booking failed: " . mysqli_error($conn);
}
?>
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
$tokens_deduction = isset($_POST['tokens_deduction']) ? (float)$_POST['tokens_deduction'] : 0;
$tokens_used = isset($_POST['tokens_used']) ? (int)$_POST['tokens_used'] : 0;
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
$special_requests = mysqli_real_escape_string($conn, $_POST['special_requests']);
$fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);

// Get user's current tokens from database (for fallback)
$user_token_query = "SELECT token FROM users WHERE id = $user_id";
$token_res = mysqli_query($conn, $user_token_query);
$user_tokens = mysqli_fetch_assoc($token_res)['token'];

// Check if the checkbox was checked (use_tokens) – it comes as '1' or null
$use_tokens_checkbox = isset($_POST['use_tokens']);

// If checkbox is checked but tokens_used is 0, recalculate from available tokens (fallback)
if ($use_tokens_checkbox && $tokens_used == 0 && $user_tokens > 0) {
    $after_discount = $subtotal - $discount_amount;
    if ($after_discount < 0) $after_discount = 0;
    $max_deduction = min($after_discount, floor($user_tokens / 100));
    $tokens_deduction = $max_deduction;
    $tokens_used = $max_deduction * 100;
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
$total_before_tax = $subtotal - $discount_amount - $tokens_deduction;
if ($total_before_tax < 0) $total_before_tax = 0;
$sst_tax = $total_before_tax * 0.12;
$foreigner_tax = ($nationality == 'foreigner') ? $subtotal * 0.10 : 0;
$service_fee = $total_before_tax * 0.05;
$grand_total = $total_before_tax + $sst_tax + $foreigner_tax + $service_fee;

$tokens_per_rm = 10;
$tokens_earned = floor($grand_total * $tokens_per_rm);

$booking_ref = 'BK' . strtoupper(uniqid());

// Insert into book table (your table name is 'book')
$insert_booking = "INSERT INTO book (booking_ref, user_id, room_id, room_name, check_in, check_out, guests,
                    subtotal, discount_amount, tokens_used, tokens_deduction_amount, tokens_earned,
                    sst_tax, foreigner_tax, service_fee, grand_total, payment_method, nationality, special_requests, status, created_at)
                    VALUES ('$booking_ref', $user_id, $room_id, '$room_name', '$check_in', '$check_out', $guests,
                    $subtotal, $discount_amount, $tokens_used, $tokens_deduction, $tokens_earned,
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
        // Update user tokens
        $current_tokens = $user_tokens; // already fetched
        $new_tokens = $current_tokens - $tokens_used + $tokens_earned;
        mysqli_query($conn, "UPDATE users SET token = $new_tokens WHERE id = $user_id");
        
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
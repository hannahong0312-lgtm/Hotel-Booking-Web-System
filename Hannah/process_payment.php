<?php
session_start();
include '../Shared/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../ChangJingEn/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_booking'])) {
    header('Location: cart.php');
    exit();
}

$is_cart = isset($_POST['cart_mode']) && $_POST['cart_mode'] == '1';

if ($is_cart) {
    $cart_items = json_decode($_POST['selected_cart_data'], true);
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit();
    }
}

// Common form data
$special_requests = isset($_POST['special_requests']) ? mysqli_real_escape_string($conn, $_POST['special_requests']) : '';
$fullname = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
$ic_no = isset($_POST['ic_no']) ? trim(mysqli_real_escape_string($conn, $_POST['ic_no'])) : '';
$payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : 'credit_card';
$voucher_code = isset($_POST['voucher_code']) ? trim(mysqli_real_escape_string($conn, $_POST['voucher_code'])) : '';

$subtotal = (float)($_POST['subtotal'] ?? 0);
$discount_amount = (float)($_POST['discount_amount'] ?? 0);
$points_deduction = (float)($_POST['points_deduction'] ?? 0);
$points_used = (int)($_POST['points_used'] ?? 0);
$tourism_tax = (float)($_POST['tourism_tax'] ?? 0);

// Recalculate taxes (same logic as payment.php)
$tax_base = $subtotal - $discount_amount;
if ($tax_base < 0) $tax_base = 0;
$sst_tax = $tax_base * 0.08;
$service_fee = $tax_base * 0.05;
$total_before_tax = $subtotal - $discount_amount - $points_deduction;
if ($total_before_tax < 0) $total_before_tax = 0;
$grand_total = $total_before_tax + $sst_tax + $tourism_tax + $service_fee;

// Credit card details
$card_last4 = null;
$card_expiry = null;
if ($payment_method == 'credit_card') {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_last4 = substr($card_number, -4);
    $card_expiry = isset($_POST['expiry_date']) ? mysqli_real_escape_string($conn, $_POST['expiry_date']) : null;
}

$points_earned = floor($subtotal * 10);
$is_malaysian = preg_match('/^\d{12}$/', $ic_no);
$nationality = $is_malaysian ? 'malaysian' : 'foreigner';

// Get user's current points
$point_res = mysqli_query($conn, "SELECT points FROM users WHERE id = $user_id");
if (!$point_res) die("Failed to fetch user points: " . mysqli_error($conn));
$user_points = mysqli_fetch_assoc($point_res)['points'];

if (isset($_POST['use_points']) && $points_used == 0 && $user_points > 0) {
    $after_discount = $subtotal - $discount_amount;
    if ($after_discount < 0) $after_discount = 0;
    $max_deduction = min($after_discount, floor($user_points / 100));
    $points_deduction = $max_deduction;
    $points_used = $max_deduction * 100;
}

// Helper function to validate room ID
function validateRoomId($conn, $room_id, $room_name) {
    $room_id = (int)$room_id;
    if ($room_id > 0) {
        $check = mysqli_query($conn, "SELECT id FROM rooms WHERE id = $room_id");
        if ($check && mysqli_num_rows($check) > 0) {
            return $room_id;
        }
    }
    // Fallback: try to find room by name
    $name_escaped = mysqli_real_escape_string($conn, $room_name);
    $find = mysqli_query($conn, "SELECT id FROM rooms WHERE name = '$name_escaped' LIMIT 1");
    if ($find && mysqli_num_rows($find) > 0) {
        $row = mysqli_fetch_assoc($find);
        return (int)$row['id'];
    }
    throw new Exception("Invalid room: '$room_name' (ID: $room_id) does not exist in rooms table.");
}

// Start transaction
mysqli_begin_transaction($conn);
try {
    $book_ids = [];
    $base_ref = 'BK' . strtoupper(uniqid());

    if ($is_cart) {
        $counter = 1;
        foreach ($cart_items as $item) {
            $booking_ref = $base_ref . '_' . $counter++;
            // Validate and get correct room_id
            $room_id = isset($item['room_id']) ? (int)$item['room_id'] : 0;
            $room_name = $item['room_name'] ?? '';
            $room_id = validateRoomId($conn, $room_id, $room_name);
            
            $check_in = mysqli_real_escape_string($conn, $item['check_in']);
            $check_out = mysqli_real_escape_string($conn, $item['check_out']);
            $quantity = (int)($item['quantity'] ?? 1);
            $nights = (int)($item['nights'] ?? 1);
            $room_total = $item['room_price'] * $nights * $quantity;
            
            $insert_book = "INSERT INTO book (booking_ref, user_id, room_id, check_in, check_out, guests, quantity,
                             grand_total, nationality, special_requests, status, created_at)
                            VALUES ('$booking_ref', $user_id, $room_id, '$check_in', '$check_out', 1, $quantity,
                             $room_total, '$nationality', '$special_requests', 'confirmed', NOW())";
            if (!mysqli_query($conn, $insert_book)) {
                throw new Exception("Booking insert failed: " . mysqli_error($conn));
            }
            $book_ids[] = mysqli_insert_id($conn);
        }
    } else {
        // Single room
        $booking_ref = $base_ref;
        $room_id = (int)($_POST['room_id'] ?? 0);
        $room_name = isset($_POST['room_name']) ? $_POST['room_name'] : '';
        $room_id = validateRoomId($conn, $room_id, $room_name);
        
        $check_in = isset($_POST['check_in']) ? mysqli_real_escape_string($conn, $_POST['check_in']) : date('Y-m-d');
        $check_out = isset($_POST['check_out']) ? mysqli_real_escape_string($conn, $_POST['check_out']) : date('Y-m-d', strtotime('+2 days'));
        $guests = (int)($_POST['guests'] ?? 2);
        $nights = (int)($_POST['nights'] ?? 1);
        $room_total = $subtotal;
        
        $insert_book = "INSERT INTO book (booking_ref, user_id, room_id, check_in, check_out, guests, quantity,
                         grand_total, nationality, special_requests, status, created_at)
                        VALUES ('$booking_ref', $user_id, $room_id, '$check_in', '$check_out', $guests, 1,
                         $room_total, '$nationality', '$special_requests', 'confirmed', NOW())";
        if (!mysqli_query($conn, $insert_book)) {
            throw new Exception("Booking insert failed: " . mysqli_error($conn));
        }
        $book_ids[] = mysqli_insert_id($conn);
    }

    // Insert one payment record for the whole order
    $first_book_id = $book_ids[0];
    $transaction_id = 'TXN' . strtoupper(uniqid());
    $insert_payment = "INSERT INTO payment (book_id, user_id, method, card_no, card_expiry, transaction_id,
                        subtotal, grand_total, points_used, points_deduction_amount, points_earned,
                        sst_tax, foreigner_tax, service_fee, ic_no, created_at, status)
                        VALUES (
                            $first_book_id, $user_id, '$payment_method',
                            " . ($card_last4 ? "'$card_last4'" : "NULL") . ",
                            " . ($card_expiry ? "'$card_expiry'" : "NULL") . ",
                            '$transaction_id',
                            $subtotal, $grand_total, $points_used, $points_deduction, $points_earned,
                            $sst_tax, $tourism_tax, $service_fee, '$ic_no', NOW(), 'confirmed'
                        )";
    if (!mysqli_query($conn, $insert_payment)) {
        throw new Exception("Payment insert failed: " . mysqli_error($conn));
    }
    $payment_id = mysqli_insert_id($conn);

    // Link all bookings to this payment
    foreach ($book_ids as $bid) {
        mysqli_query($conn, "UPDATE book SET payment_id = $payment_id WHERE id = $bid");
    }

    // Update user points
    $new_points = $user_points - $points_used + $points_earned;
    mysqli_query($conn, "UPDATE users SET points = $new_points WHERE id = $user_id");

    // Mark birthday voucher as used if applied
    if (!empty($voucher_code)) {
        $update_code = "UPDATE birthday_discount_codes SET used_at = NOW() WHERE code = '$voucher_code' AND user_id = $user_id AND used_at IS NULL";
        mysqli_query($conn, $update_code);
    }

    // Clear cart session
    unset($_SESSION['cart']);

    mysqli_commit($conn);
    $_SESSION['last_booking_ref'] = $base_ref . '_1'; // first booking reference

    // Redirect to confirmation page
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
        <meta http-equiv="refresh" content="5;url=confirm_book.php?ref=<?= $base_ref . '_1' ?>">
    </head>
    <body>
        <div class="message-box">
            <h2>Processing Your Payment</h2>
            <div class="spinner"></div>
            <p>Thank you for your booking! Please wait while we confirm your reservation...</p>
            <p>If you are not redirected automatically, <a href="confirm_book.php?ref=<?= $base_ref . '_1' ?>">click here</a>.</p>
        </div>
        <script>localStorage.removeItem('hotelCart');</script>
    </body>
    </html>
    <?php
    exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Error processing your booking: " . $e->getMessage());
}
?>
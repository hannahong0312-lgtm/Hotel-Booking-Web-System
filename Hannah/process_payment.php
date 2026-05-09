<?php
//process_payment.php
// Start session to access user login data
session_start();

// Include database connection
include '../Shared/config.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../ChangJingEn/login.php');
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// Only allow POST requests with confirm_booking button
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['confirm_booking'])) {
    header('Location: cart.php');
    exit();
}

// Check if booking comes from cart (multi-room) or single room page
$is_cart = isset($_POST['cart_mode']) && $_POST['cart_mode'] == '1';

// CART MODE: Validate & repair cart items
if ($is_cart) {
    $cart_items = json_decode($_POST['selected_cart_data'], true);
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit();
    }

    // Extreme validation to fix missing room_id or room_name
    $valid_items = [];
    $errors = [];
    foreach ($cart_items as $idx => $item) {
        $room_id = isset($item['room_id']) ? (int)$item['room_id'] : 0;
        $room_name = isset($item['room_name']) ? trim($item['room_name']) : '';

        // Auto-fix room_id if missing but name exists
        if ($room_id == 0 && !empty($room_name)) {
            $name_esc = mysqli_real_escape_string($conn, $room_name);
            $find = mysqli_query($conn, "SELECT id, name FROM rooms WHERE name = '$name_esc' LIMIT 1");
            if ($find && mysqli_num_rows($find) > 0) {
                $row = mysqli_fetch_assoc($find);
                $room_id = (int)$row['id'];
                $room_name = $row['name'];
            } else {
                $errors[] = "Room not found: $room_name";
                continue;
            }
        }

        // Auto-fix room name if missing but ID exists
        if (empty($room_name) && $room_id > 0) {
            $find = mysqli_query($conn, "SELECT name FROM rooms WHERE id = $room_id LIMIT 1");
            if ($find && mysqli_num_rows($find) > 0) {
                $row = mysqli_fetch_assoc($find);
                $room_name = $row['name'];
            } else {
                $errors[] = "Invalid room ID: $room_id";
                continue;
            }
        }

        // Final validation
        if ($room_id == 0 || empty($room_name)) {
            $errors[] = "Invalid room data.";
            continue;
        }

        // Ensure required date/price fields exist
        if (!isset($item['check_in'], $item['check_out'], $item['nights'], $item['room_price'], $item['quantity'])) {
            $errors[] = "Missing booking details.";
            continue;
        }

        // Save repaired item
        $item['room_id'] = $room_id;
        $item['room_name'] = $room_name;
        $valid_items[] = $item;
    }

    // If any invalid items, redirect back to cart
    if (!empty($errors)) {
        $_SESSION['cart_error'] = "Invalid cart items: " . implode('; ', $errors);
        header('Location: cart.php');
        exit();
    }

    if (empty($valid_items)) {
        unset($_SESSION['cart']);
        $_SESSION['cart_error'] = "Your cart is empty or invalid.";
        header('Location: cart.php');
        exit();
    }

    $cart_items = $valid_items;
}

// Retrieve form payment & guest details
$special_requests = isset($_POST['special_requests']) ? mysqli_real_escape_string($conn, $_POST['special_requests']) : '';
$fullname = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
$phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
$ic_no = isset($_POST['ic_no']) ? trim(mysqli_real_escape_string($conn, $_POST['ic_no'])) : '';
$payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : 'credit_card';
$voucher_code = isset($_POST['voucher_code']) ? trim(mysqli_real_escape_string($conn, $_POST['voucher_code'])) : '';

// Price breakdown from payment page
$subtotal = (float)($_POST['subtotal'] ?? 0);
$discount_amount = (float)($_POST['discount_amount'] ?? 0);
$points_deduction = (float)($_POST['points_deduction'] ?? 0);
$points_used = (int)($_POST['points_used'] ?? 0);
$tourism_tax = (float)($_POST['tourism_tax'] ?? 0);

// Calculate taxes
$tax_base = $subtotal - $discount_amount;
if ($tax_base < 0) $tax_base = 0;
$sst_tax = $tax_base * 0.08;
$service_fee = $tax_base * 0.05;
$total_before_tax = $subtotal - $discount_amount - $points_deduction;
if ($total_before_tax < 0) $total_before_tax = 0;
$grand_total = $total_before_tax + $sst_tax + $tourism_tax + $service_fee;

// Credit card last 4 digits (for record only)
$card_last4 = null;
$card_expiry = null;
if ($payment_method == 'credit_card') {
    $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $card_last4 = substr($card_number, -4);
    $card_expiry = isset($_POST['expiry_date']) ? mysqli_real_escape_string($conn, $_POST['expiry_date']) : null;
}

// Points earned: RM1 spent = 10 points
$points_earned = floor($subtotal * 10);

// Determine nationality by IC (12 digits = Malaysian)
$is_malaysian = preg_match('/^\\d{12}$/', $ic_no);
$nationality = $is_malaysian ? 'malaysian' : 'foreigner';

// Get current user points
$point_res = mysqli_query($conn, "SELECT points FROM users WHERE id = $user_id");
if (!$point_res) die("Failed to load user points.");
$user_points = mysqli_fetch_assoc($point_res)['points'];

// Auto-calculate max points deduction if enabled
if (isset($_POST['use_points']) && $points_used == 0 && $user_points > 0) {
    $after_discount = $subtotal - $discount_amount;
    if ($after_discount < 0) $after_discount = 0;
    $max_deduction = min($after_discount, floor($user_points / 100));
    $points_deduction = $max_deduction;
    $points_used = $max_deduction * 100;
}

// Function to validate room exists in database
function validateRoomId($conn, $room_id, $room_name) {
    $room_id = (int)$room_id;
    if ($room_id > 0) {
        $check = mysqli_query($conn, "SELECT id FROM rooms WHERE id = $room_id");
        if ($check && mysqli_num_rows($check) > 0) {
            return $room_id;
        }
    }

    // Fallback: find room by name
    $name_escaped = mysqli_real_escape_string($conn, $room_name);
    $find = mysqli_query($conn, "SELECT id FROM rooms WHERE name = '$name_escaped' LIMIT 1");
    if ($find && mysqli_num_rows($find) > 0) {
        $row = mysqli_fetch_assoc($find);
        return (int)$row['id'];
    }

    throw new Exception("Room not found: '$room_name' (ID: $room_id)");
}

// Generate ONE unique base booking reference FOR ALL transactions
// Fix: single room = no _1, cart = _1, _2...
$base_ref = 'BK' . strtoupper(uniqid());

// Start database transaction 
mysqli_begin_transaction($conn);

try {
    $book_ids = [];
    $redirect_ref = '';

    // INSERT BOOKINGS: CART MODE (multi-room)
    if ($is_cart) {
        $counter = 1;
        foreach ($cart_items as $item) {
            // Cart booking ref: BKXXXX_1, BKXXXX_2...
            $booking_ref = $base_ref . '_' . $counter++;
            $room_id = $item['room_id'];
            $room_name = $item['room_name'];

            // Validate room
            $room_id = validateRoomId($conn, $room_id, $room_name);

            // Get booking details
            $check_in = mysqli_real_escape_string($conn, $item['check_in']);
            $check_out = mysqli_real_escape_string($conn, $item['check_out']);
            $quantity = (int)($item['quantity'] ?? 1);
            $nights = (int)($item['nights'] ?? 1);
            $room_total = $item['room_price'] * $nights * $quantity;

            // Insert into book table
            $insert_book = "INSERT INTO book (booking_ref, user_id, room_id, check_in, check_out, guests, quantity,
                             grand_total, nationality, special_requests, status, created_at)
                            VALUES ('$booking_ref', $user_id, $room_id, '$check_in', '$check_out', 1, $quantity,
                             $room_total, '$nationality', '$special_requests', 'confirmed', NOW())";

            if (!mysqli_query($conn, $insert_book)) {
                throw new Exception("Booking failed: " . mysqli_error($conn));
            }

            $book_ids[] = mysqli_insert_id($conn);
        }

        // Redirect to first cart item: BKXXXX_1
        $redirect_ref = $base_ref . '_1';
    }

    // INSERT BOOKING: SINGLE ROOM MODE
    else {
        // Single room ref: BKXXXX (NO _1)
        $booking_ref = $base_ref;
        $room_id = (int)($_POST['room_id'] ?? 0);
        $room_name = '';

        // Get room name from DB
        if ($room_id > 0) {
            $sql = mysqli_query($conn, "SELECT name FROM rooms WHERE id = $room_id LIMIT 1");
            if ($sql && mysqli_num_rows($sql) > 0) {
                $room_name = mysqli_fetch_assoc($sql)['name'];
            }
        }

        // Validate room
        $room_id = validateRoomId($conn, $room_id, $room_name);

        // Get booking dates
        $check_in = isset($_POST['check_in']) ? mysqli_real_escape_string($conn, $_POST['check_in']) : date('Y-m-d');
        $check_out = isset($_POST['check_out']) ? mysqli_real_escape_string($conn, $_POST['check_out']) : date('Y-m-d', strtotime('+2 days'));
        $guests = (int)($_POST['guests'] ?? 2);
        $nights = (int)($_POST['nights'] ?? 1);
        $room_total = $subtotal;

        // Insert single booking
        $insert_book = "INSERT INTO book (booking_ref, user_id, room_id, check_in, check_out, guests, quantity,
                         grand_total, nationality, special_requests, status, created_at)
                        VALUES ('$booking_ref', $user_id, $room_id, '$check_in', '$check_out', $guests, 1,
                         $room_total, '$nationality', '$special_requests', 'confirmed', NOW())";

        if (!mysqli_query($conn, $insert_book)) {
            throw new Exception("Booking failed: " . mysqli_error($conn));
        }

        $book_ids[] = mysqli_insert_id($conn);

        // Redirect to single ref (NO _1)
        $redirect_ref = $base_ref;
    }

    // Insert payment record
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
        throw new Exception("Payment failed: " . mysqli_error($conn));
    }

    $payment_id = mysqli_insert_id($conn);

    // Link all bookings to this payment
    foreach ($book_ids as $bid) {
        mysqli_query($conn, "UPDATE book SET payment_id = $payment_id WHERE id = $bid");
    }

    // Update user points
    $new_points = $user_points - $points_used + $points_earned;
    mysqli_query($conn, "UPDATE users SET points = $new_points WHERE id = $user_id");

    // Mark voucher as used if applied
    if (!empty($voucher_code)) {
        mysqli_query($conn, "UPDATE birthday_discount_codes SET used_at = NOW() 
                             WHERE code = '$voucher_code' AND user_id = $user_id AND used_at IS NULL");
    }

    // Clear cart after successful booking
    unset($_SESSION['cart']);

    // Commit transaction
    mysqli_commit($conn);

    // Save ref to session for confirmation page
    $_SESSION['last_booking_ref'] = $redirect_ref;
}

// Rollback transaction if any error occurs
catch (Exception $e) {
    mysqli_rollback($conn);
    die("Booking Error: " . $e->getMessage());
}
?>


<!-- Payment Success Loading Page -->
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
    <!-- Auto redirect to confirmation page with CORRECT booking ref -->
    <meta http-equiv="refresh" content="5;url=confirm_book.php?ref=<?= $redirect_ref ?>">
</head>
<body>
    <div class="message-box">
        <h2>Processing Your Payment</h2>
        <div class="spinner"></div>
        <p>Thank you for your booking! Please wait while we confirm your reservation...</p>
        <p>If you are not redirected automatically, <a href="confirm_book.php?ref=<?= $redirect_ref ?>">click here</a>.</p>
    </div>
    <script>localStorage.removeItem('hotelCart');</script>
</body>
</html>
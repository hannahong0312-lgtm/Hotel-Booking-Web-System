<?php
// cart.php - Shopping cart with multiple rooms + IC/Passport field + tax based on user's country
session_start();
include '../Shared/config.php';
include '../Shared/header.php';

// --- AJAX: Recalculate taxes when IC changes ---
if (isset($_GET['action']) && $_GET['action'] == 'check_ic' && isset($_GET['ic_no']) && isset($_GET['nights'])) {
    header('Content-Type: application/json');
    $ic = trim($_GET['ic_no']);
    $nights = (int)$_GET['nights'];
    $subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
    $discount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;
    $points = isset($_GET['points']) ? floatval($_GET['points']) : 0;
    $country_from_db = isset($_GET['country']) ? $_GET['country'] : '';

    // Determine Malaysian: either IC is 12 digits OR country in DB is Malaysia
    $is_malaysian_by_ic = (preg_match('/^\d{12}$/', $ic)) ? true : false;
    $is_malaysian_by_country = ($country_from_db == 'Malaysia');
    $is_malaysian = $is_malaysian_by_ic;
    $tourism_tax = ($is_malaysian) ? 0 : 10 * $nights;

    $total_before_tax = $subtotal - $discount - $points;
    if ($total_before_tax < 0) $total_before_tax = 0;
    $sst = $total_before_tax * 0.08;
    $service = $total_before_tax * 0.05;
    $grand = $total_before_tax + $sst + $tourism_tax + $service;

    echo json_encode([
        'tourism_tax' => $tourism_tax,
        'sst' => $sst,
        'service_fee' => $service,
        'grand_total' => $grand,
        'is_malaysian' => $is_malaysian
    ]);
    exit();
}

// --- AJAX: Validate voucher code ---
if (isset($_GET['action']) && $_GET['action'] == 'validate_voucher' && isset($_GET['code'])) {
    header('Content-Type: application/json');
    $code = mysqli_real_escape_string($conn, $_GET['code']);
    $current_subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
    
    $voucher_query = "SELECT * FROM hotel_offers WHERE code = '$code' AND is_active = 1 AND (valid_to IS NULL OR valid_to >= CURDATE())";
    $voucher_result = mysqli_query($conn, $voucher_query);
    
    if ($voucher_result && mysqli_num_rows($voucher_result) > 0) {
        $voucher = mysqli_fetch_assoc($voucher_result);
        $discount = round(($current_subtotal * $voucher['discount_percentage']) / 100, 2);
        echo json_encode(['success' => true, 'discount_percent' => $voucher['discount_percentage'], 'discount_amount' => $discount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired voucher code']);
    }
    exit();
}

// --- AJAX: Calculate points deduction ---
if (isset($_GET['action']) && $_GET['action'] == 'calculate_points' && isset($_GET['use_points'])) {
    header('Content-Type: application/json');
    $use_points = $_GET['use_points'] == 'true';
    $current_subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
    $current_discount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;
    $user_points_available = isset($_GET['user_points']) ? intval($_GET['user_points']) : 0;
    
    $after_discount = max(0, $current_subtotal - $current_discount);
    $points_deduction_amount = 0;
    $points_used = 0;
    
    if ($use_points && $user_points_available > 0) {
        $max_deduction = min($after_discount, floor($user_points_available / 100));
        $points_deduction_amount = $max_deduction;
        $points_used = $max_deduction * 100;
    }
    
    echo json_encode([
        'success' => true,
        'points_deduction' => $points_deduction_amount,
        'points_used' => $points_used,
        'remaining_points' => $user_points_available - $points_used
    ]);
    exit();
}

// --- AJAX: Remove item from cart ---
if (isset($_GET['action']) && $_GET['action'] == 'remove_item' && isset($_GET['index'])) {
    header('Content-Type: application/json');
    $index = (int)$_GET['index'];
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        if (empty($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
    exit();
}

// --- AJAX: Update cart item (guests) ---
if (isset($_GET['action']) && $_GET['action'] == 'update_item' && isset($_GET['index']) && isset($_GET['guests'])) {
    header('Content-Type: application/json');
    $index = (int)$_GET['index'];
    $guests = (int)$_GET['guests'];
    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['guests'] = max(1, min($_SESSION['cart'][$index]['max_guests'], $guests));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// Check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: ../ChangJingEn/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch user data (including country)
$fullname = $email = $country = '';
$user_points = 0;
$user_query = "SELECT first_name, last_name, email, country, points FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    $fullname = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $email = $user['email'] ?? '';
    $country = $user['country'] ?? '';
    $user_points = (int)($user['points'] ?? 0);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding item to cart from GET parameters
if (isset($_GET['room_id']) && isset($_GET['arrive']) && isset($_GET['depart']) && isset($_GET['guests'])) {
    $room_id = (int)$_GET['room_id'];
    $check_in = $_GET['arrive'];
    $check_out = $_GET['depart'];
    $guests = (int)$_GET['guests'];
    
    // Validate dates
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date2->diff($date1)->days;
    if ($nights <= 0) $nights = 1;
    
    // Fetch room details
    $room_sql = "SELECT id, name, price, max_guests, image FROM rooms WHERE id = $room_id AND is_active = 1";
    $room_result = mysqli_query($conn, $room_sql);
    if ($room_result && mysqli_num_rows($room_result) > 0) {
        $room = mysqli_fetch_assoc($room_result);
        
        // Check if same room with same dates already exists in cart
        $exists = false;
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['room_id'] == $room_id && $item['check_in'] == $check_in && $item['check_out'] == $check_out) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $_SESSION['cart'][] = [
                'room_id' => $room['id'],
                'room_name' => $room['name'],
                'room_price' => (float)$room['price'],
                'max_guests' => $room['max_guests'],
                'check_in' => $check_in,
                'check_out' => $check_out,
                'nights' => $nights,
                'guests' => min($guests, $room['max_guests']),
                'image' => $room['image']
            ];
        }
    }
    
    // Redirect to cart to avoid re-adding on refresh
    header('Location: cart.php');
    exit();
}

// Calculate cart totals
$subtotal = 0;
$cart_items = $_SESSION['cart'];
$total_nights = 0;

foreach ($cart_items as $item) {
    $item_subtotal = $item['room_price'] * $item['nights'];
    $subtotal += $item_subtotal;
    $total_nights += $item['nights'];
}

// Points earned (10 points per RM spent)
$points_per_rm = 10;
$points_earned_display = floor($subtotal * $points_per_rm);

// Initial tax based on user's country
$user_is_malaysian = ($country == 'Malaysia');
$tourism_tax_initial = $user_is_malaysian ? 0 : (10 * $total_nights);
$sst_initial = $subtotal * 0.08;
$service_initial = $subtotal * 0.05;
$grand_initial = $subtotal + $sst_initial + $tourism_tax_initial + $service_initial;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart | Grand Hotel</title>
    <link rel="stylesheet" href="css/payment.css">
    <style>
        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }
        .cart-item-details {
            flex: 1;
        }
        .cart-item-details h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .cart-item-details p {
            margin: 5px 0;
            color: #666;
            font-size: 0.9rem;
        }
        .cart-item-price {
            text-align: right;
            min-width: 150px;
        }
        .cart-item-price .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--gold);
        }
        .remove-item {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 1.2rem;
            transition: 0.3s;
        }
        .remove-item:hover {
            color: #bd2130;
            transform: scale(1.1);
        }
        .guest-select {
            width: 80px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
        }
        .empty-cart h3 {
            color: #666;
            margin-bottom: 20px;
        }
        .cart-summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }
        .btn-continue {
            background: var(--gold);
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-continue:hover {
            background: var(--gold-dark);
        }
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
            }
            .cart-item-price {
                text-align: left;
            }
            .remove-item {
                top: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>
<main>
<div class="booking-container">
    <div class="booking-header">
        <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
        <p>Review your selected rooms and complete your booking</p>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h3>Your cart is empty</h3>
            <p>You haven't added any rooms to your cart yet.</p>
            <a href="../ChongEeLynn/accommodation.php" class="btn btn-primary btn-continue">Browse Rooms</a>
        </div>
    <?php else: ?>
    <form method="POST" action="process_payment.php" id="bookingForm" onsubmit="return validatePaymentForm()">
        <div class="booking-grid">
            <!-- Left Column - Cart Items -->
            <div>
                <div class="booking-details-card">
                    <div class="card-header"><h3><i class="fas fa-list"></i> Selected Rooms (<?= count($cart_items) ?>)</h3></div>
                    <div class="card-content" id="cartItemsContainer">
                        <?php foreach ($cart_items as $index => $item): ?>
                            <div class="cart-item" data-index="<?= $index ?>">
                                <button type="button" class="remove-item" onclick="removeCartItem(<?= $index ?>)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <img src="../ChongEeLynn/images/<?= htmlspecialchars($item['image'] ?? 'room-default.jpg') ?>" 
                                     alt="<?= htmlspecialchars($item['room_name']) ?>" 
                                     class="cart-item-image"
                                     onerror="this.src='../ChongEeLynn/images/room-default.jpg'">
                                <div class="cart-item-details">
                                    <h4><?= htmlspecialchars($item['room_name']) ?></h4>
                                    <p><i class="fas fa-calendar-check"></i> Check-in: <?= date('d F Y', strtotime($item['check_in'])) ?></p>
                                    <p><i class="fas fa-calendar-times"></i> Check-out: <?= date('d F Y', strtotime($item['check_out'])) ?></p>
                                    <p><i class="fas fa-moon"></i> <?= $item['nights'] ?> nights</p>
                                    <p>
                                        <i class="fas fa-users"></i> Guests: 
                                        <select class="guest-select" data-index="<?= $index ?>" onchange="updateGuests(<?= $index ?>, this.value)">
                                            <?php for ($g = 1; $g <= $item['max_guests']; $g++): ?>
                                                <option value="<?= $g ?>" <?= $item['guests'] == $g ? 'selected' : '' ?>><?= $g ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </p>
                                </div>
                                <div class="cart-item-price">
                                    <div>RM <?= number_format($item['room_price'], 0) ?> / night</div>
                                    <div class="price">RM <?= number_format($item['room_price'] * $item['nights'], 2) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-summary">
                            <div class="summary-item"><span>Subtotal</span><span>RM <?= number_format($subtotal, 2) ?></span></div>
                            <div class="summary-item discount-row" id="discountRow" style="display:none"><span>Voucher Discount</span><span>-RM <span id="discountAmount">0.00</span></span></div>
                            <div class="summary-item points-row" id="pointsRow" style="display:none"><span>Points Deduction</span><span>-RM <span id="pointsDeductionAmount">0.00</span></span></div>
                        </div>
                    </div>
                </div>
                <div class="cancellation-policy">
                    <i class="fas fa-shield-alt"></i> <strong>Free Cancellation</strong> <small>Cancel up to 24 hours before check-in for a full refund.</small><br>
                    <i class="fas fa-id-card"></i> <strong>Tourism Tax</strong> <small>RM10 per room per night applies to Foreigners. Enter your MyKad (12 digits) to confirm Malaysian status.</small>
                </div>
            </div>

            <!-- Right Column - Payment Details -->
            <div>
                <div class="payment-card">
                    <div class="card-header"><h3><i class="fas fa-credit-card"></i> Payment Details</h3></div>
                    <div class="card-content">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Guest Name</label>
                            <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($fullname) ?>" readonly required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" readonly required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="tel" class="form-control" name="phone" placeholder="Enter your phone number" required>
                        </div>

                        <!-- IC Number Field -->
                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> IC/Passport Number</label>
                            <input type="text" class="form-control" id="ic_no" name="ic_no" placeholder="MyKad (12 digits) or Passport number" required>
                            <small class="form-text text-muted" id="icStatus"></small>
                        </div>

                        <!-- Voucher Section -->
                        <div class="form-group voucher-section">
                            <label><i class="fas fa-ticket-alt"></i> Voucher Code</label>
                            <div class="voucher-input-group">
                                <input type="text" class="form-control" id="voucher_code" name="voucher_code" placeholder="Enter promo code">
                                <button type="button" id="applyVoucherBtn" class="btn-voucher">Apply</button>
                            </div>
                            <div id="voucherMessage" class="voucher-message"></div>
                        </div>

                        <!-- Points Section -->
                        <div class="form-group points-section">
                            <div class="points-header">
                                <label><i class="fas fa-coins"></i> Your Points</label>
                                <span class="points-balance">Available: <strong id="userPoints"><?= number_format($user_points) ?></strong></span>
                            </div>
                            <div class="points-toggle-row">
                                <span class="toggle-label">Use points to reduce total</span>
                                <label class="switch">
                                    <input type="checkbox" id="usePointsToggle" name="use_points" value="1">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="points-info" id="pointsInfo" style="display:none"><small>100 points = RM1 deduction</small><div id="pointsDeductionInfo"></div></div>
                            <div class="points-earned"><small>You'll earn <strong id="pointsEarned"><?= number_format($points_earned_display) ?></strong> points (excluding taxes)</small></div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="payment-methods">
                            <label>Select Payment Method</label>
                            <div class="payment-option"><input type="radio" id="credit_card" name="payment_method" value="credit_card" checked><label for="credit_card"><i class="fab fa-cc-visa"></i> Credit/Debit Card</label></div>
                            <div class="payment-option"><input type="radio" id="TouchnGo" name="payment_method" value="TouchnGo"><label for="TouchnGo"><i class="fas fa-wifi"></i> Touch 'n Go</label></div>
                            <div class="payment-option"><input type="radio" id="online_banking" name="payment_method" value="online_banking"><label for="online_banking"><i class="fas fa-university"></i> Online Banking</label></div>
                        </div>

                        <!-- Credit Card Details -->
                        <div class="form-group" id="card_details" style="display: block;">
                            <label>Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px">
                                <div><label>Expiry Date</label><input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY"></div>
                                <div><label>CVV</label><input type="text" class="form-control" id="cvv" name="cvv" placeholder="123"></div>
                            </div>
                        </div>

                        <!-- Tax Breakdown -->
                        <div class="total-section" id="totalSection">
                            <div class="total-item"><span>Subtotal (<?= $total_nights ?> total nights)</span><span>RM <span id="subtotalAmount"><?= number_format($subtotal, 2) ?></span></span></div>
                            <div class="total-item discount-row" id="taxDiscountRow" style="display:none"><span>Voucher Discount</span><span>-RM <span id="taxDiscountAmount">0.00</span></span></div>
                            <div class="total-item points-row" id="taxPointsRow" style="display:none"><span>Points Deduction</span><span>-RM <span id="taxPointsDeductionAmount">0.00</span></span></div>
                            <div class="total-item"><span>SST Tax (8%)</span><span>RM <span id="sstTax"><?= number_format($sst_initial, 2) ?></span></span></div>
                            <div class="total-item" id="tourismTaxRow"><span>Tourism Tax (RM10/room/night)</span><span>RM <span id="tourismTax"><?= number_format($tourism_tax_initial, 2) ?></span></span></div>
                            <div class="total-item"><span>Service Fee (5%)</span><span>RM <span id="serviceFee"><?= number_format($service_initial, 2) ?></span></span></div>
                            <div class="grand-total"><span><strong>Total Amount</strong></span><span><strong>RM <span id="grandTotal"><?= number_format($grand_initial, 2) ?></span></strong></span></div>
                        </div>

                        <div class="form-group"><label>Special Requests</label><textarea class="form-control" name="special_requests" rows="3" placeholder="Any special requests for your stay?"></textarea></div>

                        <!-- Hidden fields for cart data -->
                        <input type="hidden" name="is_cart" value="1">
                        <input type="hidden" name="cart_data" id="cartData" value='<?= htmlspecialchars(json_encode($cart_items)) ?>'>
                        <input type="hidden" name="subtotal" id="hiddenSubtotal" value="<?= $subtotal ?>">
                        <input type="hidden" name="total_nights" id="hiddenTotalNights" value="<?= $total_nights ?>">
                        <input type="hidden" name="discount_amount" id="hiddenDiscountAmount" value="0">
                        <input type="hidden" name="points_deduction" id="hiddenPointsDeduction" value="0">
                        <input type="hidden" name="points_used" id="hiddenPointsUsed" value="0">
                        <input type="hidden" name="tourism_tax" id="hiddenTourismTax" value="<?= $tourism_tax_initial ?>">
                        <input type="hidden" id="userCountry" value="<?= htmlspecialchars($country) ?>">

                        <div class="button-group">
                            <a href="../ChongEeLynn/accommodation.php" class="btn btn-secondary">Continue Shopping</a>
                            <button type="submit" name="confirm_booking" class="btn btn-primary">Pay Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>
</main>

<script>
let subtotal = <?= json_encode($subtotal) ?>;
let totalNights = <?= json_encode($total_nights) ?>;
let userPoints = <?= json_encode($user_points) ?>;
let discountAmount = 0;
let pointsDeduction = 0;
let currentIcValue = '';
let userCountry = document.getElementById('userCountry').value;
let isMalaysianByIc = (userCountry === 'Malaysia') ? true : false;

function refreshTotals() {
    let afterDiscount = subtotal - discountAmount - pointsDeduction;
    if (afterDiscount < 0) afterDiscount = 0;
    let tourismTax = isMalaysianByIc ? 0 : (totalNights * 10);
    let sst = afterDiscount * 0.08;
    let serviceFee = afterDiscount * 0.05;
    let grand = afterDiscount + sst + tourismTax + serviceFee;
    
    document.getElementById('sstTax').innerText = sst.toFixed(2);
    document.getElementById('tourismTax').innerText = tourismTax.toFixed(2);
    document.getElementById('serviceFee').innerText = serviceFee.toFixed(2);
    document.getElementById('grandTotal').innerText = grand.toFixed(2);
    document.getElementById('hiddenTourismTax').value = tourismTax;
    
    let statusSpan = document.getElementById('icStatus');
    if (statusSpan) {
        if (isMalaysianByIc) {
            statusSpan.innerHTML = 'Malaysian profile/IC detected – Tourism tax waived.';
            statusSpan.style.color = 'green';
        } else {
            statusSpan.innerHTML = 'Foreigner - Tourism tax of RM10 per room per night applies.';
            statusSpan.style.color = '#dc3545';
        }
    }
}

function checkIcAndRecalc() {
    let ic = document.getElementById('ic_no').value.trim();
    if (ic === currentIcValue) return;
    currentIcValue = ic;
    if (ic === '') {
        isMalaysianByIc = (userCountry === 'Malaysia');
        refreshTotals();
        return;
    }
    
    fetch(`?action=check_ic&ic_no=${encodeURIComponent(ic)}&nights=${totalNights}&subtotal=${subtotal - discountAmount - pointsDeduction}&discount=${discountAmount}&points=${pointsDeduction}&country=${encodeURIComponent(userCountry)}`)
        .then(res => res.json())
        .then(data => {
            if (data && typeof data.tourism_tax !== 'undefined') {
                isMalaysianByIc = data.is_malaysian;
                refreshTotals();
            }
        })
        .catch(err => console.log('IC check failed', err));
}

function removeCartItem(index) {
    if (confirm('Remove this room from your cart?')) {
        fetch(`?action=remove_item&index=${index}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to remove item');
                }
            })
            .catch(err => console.log('Remove failed', err));
    }
}

function updateGuests(index, guests) {
    fetch(`?action=update_item&index=${index}&guests=${guests}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => console.log('Update failed', err));
}

function toggleCreditCardFields() {
    let selected = document.querySelector('input[name="payment_method"]:checked').value;
    let cardDiv = document.getElementById('card_details');
    if (selected === 'credit_card') {
        cardDiv.style.display = 'block';
        ['card_number','expiry_date','cvv'].forEach(id => {
            let el = document.getElementById(id);
            if (el) el.setAttribute('required','required');
        });
    } else {
        cardDiv.style.display = 'none';
        ['card_number','expiry_date','cvv'].forEach(id => {
            let el = document.getElementById(id);
            if (el) el.removeAttribute('required');
        });
    }
}

function restrictCardNumber() {
    let cardInput = document.getElementById('card_number');
    if(cardInput) {
        cardInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g,'').slice(0,16);
        });
    }
}

function validatePaymentForm() {
    let selected = document.querySelector('input[name="payment_method"]:checked').value;
    if(selected === 'credit_card') {
        let cardNum = document.getElementById('card_number').value.trim();
        let expiry = document.getElementById('expiry_date').value.trim();
        let cvv = document.getElementById('cvv').value.trim();
        if(!cardNum || !/^\d{16}$/.test(cardNum)) {
            alert('Please enter a valid 16-digit card number');
            return false;
        }
        if(!expiry || !/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) {
            alert('Please enter a valid expiry date (MM/YY)');
            return false;
        }
        if(!cvv || !/^\d{3,4}$/.test(cvv)) {
            alert('Please enter a valid CVV (3-4 digits)');
            return false;
        }
    }
    if(!document.querySelector('input[name="phone"]').value.trim()) {
        alert('Please enter your phone number');
        return false;
    }
    if(!document.getElementById('ic_no').value.trim()) {
        alert('Please enter your IC/Passport number');
        return false;
    }
    if (<?= count($cart_items) ?> === 0) {
        alert('Your cart is empty');
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', toggleCreditCardFields));
    toggleCreditCardFields();
    restrictCardNumber();
    
    let icInput = document.getElementById('ic_no');
    if (icInput) icInput.addEventListener('input', checkIcAndRecalc);
    
    refreshTotals();
    if (userCountry === 'Malaysia') {
        let statusSpan = document.getElementById('icStatus');
        if (statusSpan) statusSpan.innerHTML = 'Malaysian profile detected – Please enter your MyKad for verification.';
    }
    
    // Voucher Application
    let applyBtn = document.getElementById('applyVoucherBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            let code = document.getElementById('voucher_code').value;
            if(!code) {
                alert('Enter voucher code');
                return;
            }
            fetch(`?action=validate_voucher&code=${encodeURIComponent(code)}&subtotal=${subtotal}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        discountAmount = data.discount_amount;
                        document.getElementById('discountAmount').innerText = discountAmount.toFixed(2);
                        document.getElementById('taxDiscountAmount').innerText = discountAmount.toFixed(2);
                        document.getElementById('discountRow').style.display = 'flex';
                        document.getElementById('taxDiscountRow').style.display = 'flex';
                        document.getElementById('hiddenDiscountAmount').value = discountAmount;
                        document.getElementById('voucherMessage').innerHTML = '<span class="success">Voucher applied! RM' + discountAmount.toFixed(2) + ' off</span>';
                        refreshTotals();
                        checkIcAndRecalc();
                    } else {
                        document.getElementById('voucherMessage').innerHTML = '<span class="error">' + data.message + '</span>';
                    }
                });
        });
    }
    
    // Points Toggle
    let pointsToggle = document.getElementById('usePointsToggle');
    if (pointsToggle) {
        pointsToggle.addEventListener('change', function() {
            let usePoints = this.checked;
            fetch(`?action=calculate_points&use_points=${usePoints}&subtotal=${subtotal - discountAmount}&discount=${discountAmount}&user_points=${userPoints}`)
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        pointsDeduction = data.points_deduction;
                        let used = data.points_used;
                        document.getElementById('pointsDeductionAmount').innerText = pointsDeduction.toFixed(2);
                        document.getElementById('taxPointsDeductionAmount').innerText = pointsDeduction.toFixed(2);
                        document.getElementById('pointsRow').style.display = pointsDeduction > 0 ? 'flex' : 'none';
                        document.getElementById('taxPointsRow').style.display = pointsDeduction > 0 ? 'flex' : 'none';
                        document.getElementById('hiddenPointsDeduction').value = pointsDeduction;
                        document.getElementById('hiddenPointsUsed').value = used;
                        document.getElementById('pointsDeductionInfo').innerHTML = `Using ${used} points (RM${pointsDeduction.toFixed(2)})`;
                        document.getElementById('pointsInfo').style.display = pointsDeduction > 0 ? 'block' : 'none';
                        refreshTotals();
                        checkIcAndRecalc();
                    }
                });
        });
    }
});
</script>
<?php include '../Shared/footer.php'; ?>
<?php
// payment.php - Multiple rooms from cart, points deduction does not affect taxes
session_start();
include '../Shared/config.php';

// --- Handle selected cart items from cart.php checkout ---
$selected_cart_items = [];
$cart_mode = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
    $cart_mode = true;
    $selected_indices = array_map('intval', $_POST['selected_items']);
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($selected_indices as $idx) {
            if (isset($_SESSION['cart'][$idx])) {
                $selected_cart_items[] = $_SESSION['cart'][$idx];
            }
        }
    }
    if (empty($selected_cart_items)) {
        header('Location: cart.php');
        exit();
    }
    // Calculate summary from selected items
    $subtotal = 0;
    $total_nights = 0;
    foreach ($selected_cart_items as $item) {
        $qty = $item['quantity'] ?? 1;
        $subtotal += $item['room_price'] * $item['nights'] * $qty;
        $total_nights += $item['nights'] * $qty;
    }
    $nights = $total_nights;
} else {
    // Old single-room mode (GET parameters)
    $room_id   = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 1;
    $check_in  = isset($_GET['arrive']) ? $_GET['arrive'] : date('Y-m-d');
    $check_out = isset($_GET['depart']) ? $_GET['depart'] : date('Y-m-d', strtotime('+2 days'));

    $room_sql = "SELECT name, price, image FROM rooms WHERE id = $room_id";
    $room_result = mysqli_query($conn, $room_sql);
    if ($room_result && mysqli_num_rows($room_result) > 0) {
        $room = mysqli_fetch_assoc($room_result);
        $room_name = htmlspecialchars($room['name']);
        $room_price = (float)$room['price'];
        $room_image = '../ChongEeLynn/images/' . trim($room['image']);
    } else {
        $room_name = 'Standard Room';
        $room_price = 150.00;
        $room_image = '../ChongEeLynn/images/room-default.jpg';
    }
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date2->diff($date1)->days;
    if ($nights <= 0) $nights = 1;
    $subtotal = $room_price * $nights;
    $selected_cart_items = [
        [
            'room_name' => $room_name,
            'room_price' => $room_price,
            'nights' => $nights,
            'quantity' => 1,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'image' => basename($room_image)
        ]
    ];
    $total_nights = $nights;
}

// --- AJAX: Recalculate taxes when IC changes (points do NOT reduce tax base) ---
if (isset($_GET['action']) && $_GET['action'] == 'check_ic' && isset($_GET['ic_no']) && isset($_GET['nights'])) {
    header('Content-Type: application/json');
    $ic = trim($_GET['ic_no']);
    $nights = (int)$_GET['nights'];
    $subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
    $discount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;
    $points = isset($_GET['points']) ? floatval($_GET['points']) : 0;
    $country_from_db = isset($_GET['country']) ? $_GET['country'] : '';

    $is_malaysian_by_ic = (preg_match('/^\d{12}$/', $ic)) ? true : false;
    $is_malaysian = $is_malaysian_by_ic;
    $tourism_tax = ($is_malaysian) ? 0 : 10 * $nights;

    // Tax base = subtotal - discount (voucher only), points do NOT reduce taxes
    $tax_base = $subtotal - $discount;
    if ($tax_base < 0) $tax_base = 0;
    $sst = $tax_base * 0.08;
    $service = $tax_base * 0.05;
    
    // Final total = (subtotal - discount - points) + taxes
    $grand = ($subtotal - $discount - $points) + $sst + $tourism_tax + $service;

    echo json_encode([
        'tourism_tax' => $tourism_tax,
        'sst' => $sst,
        'service_fee' => $service,
        'grand_total' => $grand,
        'is_malaysian' => $is_malaysian
    ]);
    exit();
}

// --- AJAX: Validate voucher code (unchanged) ---
if (isset($_GET['action']) && $_GET['action'] == 'validate_voucher' && isset($_GET['code'])) {
    header('Content-Type: application/json');
    $code = mysqli_real_escape_string($conn, $_GET['code']);
    $current_subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
    $discount_percent = 0;
    $discount_amount = 0;
    $source = '';
    $user_id = $_SESSION['user_id'] ?? 0;
    $error_message = '';

    $voucher_query = "SELECT discount_percentage FROM hotel_offers WHERE code = '$code' AND is_active = 1 AND (valid_to IS NULL OR valid_to >= CURDATE())";
    $voucher_res = mysqli_query($conn, $voucher_query);
    if ($voucher_res && mysqli_num_rows($voucher_res) > 0) {
        $row = mysqli_fetch_assoc($voucher_res);
        $discount_percent = (float)$row['discount_percentage'];
        $source = 'hotel_offers';
    } else {
        $birthday_query = "SELECT discount_percent FROM birthday_discount_codes WHERE code = '$code' AND user_id = $user_id AND used_at IS NULL AND expires_at > NOW()";
        $birthday_res = mysqli_query($conn, $birthday_query);
        if ($birthday_res && mysqli_num_rows($birthday_res) > 0) {
            $row = mysqli_fetch_assoc($birthday_res);
            $discount_percent = (float)$row['discount_percent'];
            $source = 'birthday';
        } else {
            $used_check = "SELECT id FROM birthday_discount_codes WHERE code = '$code' AND user_id = $user_id AND used_at IS NOT NULL";
            $used_res = mysqli_query($conn, $used_check);
            if ($used_res && mysqli_num_rows($used_res) > 0) {
                $error_message = 'This code has already been used.';
            } else {
                $error_message = 'Invalid or expired voucher code';
            }
        }
    }

    if ($discount_percent > 0) {
        $discount_amount = round(($current_subtotal * $discount_percent) / 100, 2);
        echo json_encode([
            'success' => true,
            'discount_percent' => $discount_percent,
            'discount_amount' => $discount_amount,
            'source' => $source
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $error_message ?: 'Invalid or expired voucher code']);
    }
    exit();
}

// --- AJAX: Calculate points deduction (unchanged logic, works on after-discount) ---
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

include '../Shared/header.php';

// --- Login check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: ../ChangJingEn/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// --- Fetch user data ---
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

// --- Initial tax calculation (before any discounts/points) ---
$user_is_malaysian = ($country == 'Malaysia');
$tourism_tax_initial = $user_is_malaysian ? 0 : (10 * $total_nights);
$sst_initial = $subtotal * 0.08;
$service_initial = $subtotal * 0.05;
$grand_initial = $subtotal + $sst_initial + $tourism_tax_initial + $service_initial;

// Points earned: RM1 = 10 points (not 100)
$points_earned_display = floor($subtotal * 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Your Booking | Grand Hotel</title>
    <link rel="stylesheet" href="css/payment.css">
    <style>
        .room-list { margin-bottom: 20px; }
        .room-summary-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .room-summary-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .room-summary-details { flex: 1; }
        .room-summary-details h4 { margin: 0 0 5px; font-size: 1rem; }
        .room-summary-details p { margin: 3px 0; font-size: 0.85rem; color: #555; }
        .room-summary-price { text-align: right; font-weight: bold; color: var(--gold); }
        .total-section { margin-top: 15px; }
    </style>
</head>
<body>
<main>
<div class="booking-container">
    <div class="booking-header">
        <h1>Confirm Your Booking</h1>
        <p>Please review your booking details and complete the payment</p>
    </div>

    <form method="POST" action="process_payment.php" id="bookingForm" onsubmit="return validatePaymentForm()">
        <div class="booking-grid">
            <!-- Left Column - Booking Summary (multiple rooms) -->
            <div>
                <div class="booking-details-card">
                    <div class="card-header"><h3><i class="fas fa-file-alt"></i> Booking Summary</h3></div>
                    <div class="card-content">
                        <div class="room-list">
                            <?php foreach ($selected_cart_items as $item): 
                                $qty = $item['quantity'] ?? 1;
                                $nights_item = $item['nights'] ?? 1;
                                $item_total = $item['room_price'] * $nights_item * $qty;
                                $img = !empty($item['image']) ? '../ChongEeLynn/images/' . htmlspecialchars($item['image']) : '../ChongEeLynn/images/room-default.jpg';
                            ?>
                                <div class="room-summary-item">
                                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['room_name']) ?>" class="room-summary-image" onerror="this.src='../ChongEeLynn/images/room-default.jpg'">
                                    <div class="room-summary-details">
                                        <h4><?= htmlspecialchars($item['room_name']) ?></h4>
                                        <p><i class="fas fa-calendar-alt"></i> <?= date('d F Y', strtotime($item['check_in'])) ?> – <?= date('d F Y', strtotime($item['check_out'])) ?> (<?= $nights_item ?> nights)</p>
                                        <p><i class="fas fa-door-open"></i> <?= $qty ?> room(s) × RM<?= number_format($item['room_price'], 0) ?>/night</p>
                                    </div>
                                    <div class="room-summary-price">
                                        RM <?= number_format($item_total, 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="total-section" id="totalSection">
                            <div class="total-item"><span>Subtotal (<?= $total_nights ?> total room‑nights)</span><span>RM <span id="subtotalAmount"><?= number_format($subtotal, 2) ?></span></span></div>
                            <div class="total-item discount-row" id="discountRow" style="display:none"><span>Voucher Discount</span><span>-RM <span id="discountAmount">0.00</span></span></div>
                            <div class="total-item points-row" id="pointsRow" style="display:none"><span>Points Deduction</span><span>-RM <span id="pointsDeductionAmount">0.00</span></span></div>
                            <div class="total-item"><span>SST Tax (8%)</span><span>RM <span id="sstTax"><?= number_format($sst_initial, 2) ?></span></span></div>
                            <div class="total-item" id="tourismTaxRow"><span>Tourism Tax (RM10/room/night)</span><span>RM <span id="tourismTax"><?= number_format($tourism_tax_initial, 2) ?></span></span></div>
                            <div class="total-item"><span>Service Fee (5%)</span><span>RM <span id="serviceFee"><?= number_format($service_initial, 2) ?></span></span></div>
                            <div class="grand-total"><span><strong>Total Amount</strong></span><span><strong>RM <span id="grandTotal"><?= number_format($grand_initial, 2) ?></span></strong></span></div>
                        </div>
                    </div>
                </div>
                <div class="cancellation-policy">
                    <i class="fas fa-shield-alt"></i> <strong>Free Cancellation</strong> <small>Cancel up to 24 hours before check-in for a full refund.</small><br>
                    <i class="fas fa-id-card"></i> <strong>Tourism Tax</strong> <small>RM10 per room per night applies to Foreigners. Enter your MyKad (12 digits) or Passport Number to confirm your status.</small><br>
                    <i class="fas fa-coins"></i> <strong>Earn Points</strong> <small>Every RM1 spent (before tax) earns you 10 points! Points can be redeemed for discounts on future stays.</small><br>
                    <a style="color:#0077cc; text-decoration:underline; font-size:0.9rem;" href="javascript:void(0)" onclick="openLightbox('img/hotel tax.jpg'); return false;">View Terms and Conditions</a>
                </div>
            </div>

            <!-- Right Column - Payment Details (unchanged) -->
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

                        <div class="form-group">
                            <label><i class="fas fa-id-card"></i> IC Number & Passport</label>
                            <input type="text" class="form-control" id="ic_no" name="ic_no" placeholder="MyKad (12 digits) or Passport Number" required>
                            <small class="form-text text-muted" id="icStatus"></small>
                        </div>

                        <div class="form-group voucher-section">
                            <label><i class="fas fa-ticket-alt"></i> Voucher Code</label>
                            <div class="voucher-input-group">
                                <input type="text" class="form-control" id="voucher_code" name="voucher_code" placeholder="Enter promo code">
                                <button type="button" id="applyVoucherBtn" class="btn-voucher">Apply</button>
                            </div>
                            <div id="voucherMessage" class="voucher-message"></div>
                        </div>

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

                        <div class="payment-methods">
                            <label>Select Payment Method</label>
                            <div class="payment-option"><input type="radio" id="credit_card" name="payment_method" value="credit_card" checked><label for="credit_card"><i class="fab fa-cc-visa"></i> Credit/Debit Card</label></div>
                            <div class="payment-option"><input type="radio" id="TouchnGo" name="payment_method" value="TouchnGo"><label for="TouchnGo"><i class="fas fa-wifi"></i> Touch 'n Go</label></div>
                            <div class="payment-option"><input type="radio" id="online_banking" name="payment_method" value="online_banking"><label for="online_banking"><i class="fas fa-university"></i> Online Banking</label></div>
                        </div>

                        <div class="form-group" id="card_details" style="display: block;">
                            <label>Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px">
                                <div><label>Expiry Date</label><input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY"></div>
                                <div><label>CVV</label><input type="text" class="form-control" id="cvv" name="cvv" placeholder="123"></div>
                            </div>
                        </div>

                        <div class="form-group"><label>Special Requests</label><textarea class="form-control" name="special_requests" rows="3" placeholder="Any special requests?"></textarea></div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="cart_mode" value="<?= $cart_mode ? '1' : '0' ?>">
                        <input type="hidden" name="selected_cart_data" id="selectedCartData" value='<?= htmlspecialchars(json_encode($selected_cart_items)) ?>'>
                        <input type="hidden" name="nights" id="nightsHidden" value="<?= $total_nights ?>">
                        <input type="hidden" name="subtotal" id="hiddenSubtotal" value="<?= $subtotal ?>">
                        <input type="hidden" name="discount_amount" id="hiddenDiscountAmount" value="0">
                        <input type="hidden" name="points_deduction" id="hiddenPointsDeduction" value="0">
                        <input type="hidden" name="points_used" id="hiddenPointsUsed" value="0">
                        <input type="hidden" name="tourism_tax" id="hiddenTourismTax" value="<?= $tourism_tax_initial ?>">
                        <input type="hidden" id="userCountry" value="<?= htmlspecialchars($country) ?>">

                        <div class="button-group">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Back</button>
                            <button type="submit" name="confirm_booking" class="btn btn-primary">Pay Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</main>

<script>
let subtotal = <?= json_encode($subtotal) ?>;
let nights = <?= json_encode($total_nights) ?>;
let userPoints = <?= json_encode($user_points) ?>;
let discountAmount = 0;
let pointsDeduction = 0;
let currentIcValue = '';
let userCountry = document.getElementById('userCountry').value;
let isMalaysianByIc = (userCountry === 'Malaysia') ? true : false;

// --- Update totals: taxes on (subtotal - discount), points affect only final total ---
function refreshTotals() {
    let taxBase = subtotal - discountAmount;
    if (taxBase < 0) taxBase = 0;
    
    let afterDiscount = taxBase;
    let afterPoints = afterDiscount - pointsDeduction;
    if (afterPoints < 0) afterPoints = 0;
    
    let tourismTax = isMalaysianByIc ? 0 : (nights * 10);
    let sst = taxBase * 0.08;
    let serviceFee = taxBase * 0.05;
    let grand = afterPoints + sst + tourismTax + serviceFee;
    
    document.getElementById('sstTax').innerText = sst.toFixed(2);
    document.getElementById('tourismTax').innerText = tourismTax.toFixed(2);
    document.getElementById('serviceFee').innerText = serviceFee.toFixed(2);
    document.getElementById('grandTotal').innerText = grand.toFixed(2);
    document.getElementById('hiddenTourismTax').value = tourismTax;
    
    // Update status message based on current IC and Malaysian flag
    let icValue = document.getElementById('ic_no').value.trim();
    let statusSpan = document.getElementById('icStatus');
    if (statusSpan) {
        if (isMalaysianByIc) {
            statusSpan.innerHTML = 'Malaysian (MyKad verified) – Tourism tax waived.';
            statusSpan.style.color = 'green';
        } else {
            if (icValue !== '' && !/^\d{12}$/.test(icValue)) {
                statusSpan.innerHTML = 'Foreigner (Passport detected) – Tourism tax applies.';
            } else if (icValue === '' && userCountry === 'Malaysia') {
                statusSpan.innerHTML = 'Malaysian profile detected – Please enter your 12-digit MyKad to waive tourism tax.';
            } else {
                statusSpan.innerHTML = 'Foreigner – Tourism tax applies.';
            }
            statusSpan.style.color = '#dc3545';
        }
    }
}

function checkIcAndRecalc() {
    let ic = document.getElementById('ic_no').value.trim();
    if (ic === currentIcValue) return;
    currentIcValue = ic;
    if (ic === '') {
        // Empty IC: revert to country-based rule
        isMalaysianByIc = (userCountry === 'Malaysia');
        refreshTotals();
        return;
    }
    // Use AJAX to check IC (12-digit rule)
    fetch(`?action=check_ic&ic_no=${encodeURIComponent(ic)}&nights=${nights}&subtotal=${subtotal}&discount=${discountAmount}&points=${pointsDeduction}&country=${encodeURIComponent(userCountry)}`)
        .then(res => res.json())
        .then(data => {
            if (data && typeof data.tourism_tax !== 'undefined') {
                isMalaysianByIc = data.is_malaysian;
                // Update the displayed values from AJAX
                document.getElementById('sstTax').innerText = data.sst.toFixed(2);
                document.getElementById('tourismTax').innerText = data.tourism_tax.toFixed(2);
                document.getElementById('serviceFee').innerText = data.service_fee.toFixed(2);
                document.getElementById('grandTotal').innerText = data.grand_total.toFixed(2);
                document.getElementById('hiddenTourismTax').value = data.tourism_tax;
                // Also update status message to reflect new flag
                refreshTotals();
            }
        })
        .catch(err => console.log('IC check failed', err));
}

function toggleCreditCardFields() {
    let selected = document.querySelector('input[name="payment_method"]:checked').value;
    let cardDiv = document.getElementById('card_details');
    if (selected === 'credit_card') {
        cardDiv.style.display = 'block';
        ['card_number','expiry_date','cvv'].forEach(id => document.getElementById(id).setAttribute('required','required'));
    } else {
        cardDiv.style.display = 'none';
        ['card_number','expiry_date','cvv'].forEach(id => document.getElementById(id).removeAttribute('required'));
    }
}

function restrictCardNumber() {
    let cardInput = document.getElementById('card_number');
    if(cardInput) cardInput.addEventListener('input',function(e){this.value=this.value.replace(/\D/g,'').slice(0,16);});
}

function validatePaymentForm() {
    let selected = document.querySelector('input[name="payment_method"]:checked').value;
    if(selected==='credit_card'){
        let cardNum=document.getElementById('card_number').value.trim();
        let expiry=document.getElementById('expiry_date').value.trim();
        let cvv=document.getElementById('cvv').value.trim();
        if(!cardNum||!/^\d{16}$/.test(cardNum)){alert('Valid 16-digit card number');return false;}
        if(!expiry||!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)){alert('Expiry MM/YY');return false;}
        if(!cvv||!/^\d{3,4}$/.test(cvv)){alert('CVV 3-4 digits');return false;}
    }
    if(!document.querySelector('input[name="phone"]').value.trim()){alert('Phone required');return false;}
    if(!document.getElementById('ic_no').value.trim()){alert('IC/Passport required');return false;}
    return true;
}

document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('input[name="payment_method"]').forEach(r=>r.addEventListener('change',toggleCreditCardFields));
    toggleCreditCardFields(); restrictCardNumber();
    let icInput=document.getElementById('ic_no');
    icInput.addEventListener('input',checkIcAndRecalc);
    refreshTotals();
    if (userCountry === 'Malaysia') {
        let statusSpan = document.getElementById('icStatus');
        if (statusSpan) statusSpan.innerHTML = 'Malaysian profile detected – Tourism tax waived. Please enter your MyKad for verification.';
    }
    document.getElementById('applyVoucherBtn').addEventListener('click',function(){
        let code=document.getElementById('voucher_code').value.trim();
        if(!code){alert('Enter voucher code');return;}
        fetch(`?action=validate_voucher&code=${encodeURIComponent(code)}&subtotal=${subtotal}`)
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    discountAmount=data.discount_amount;
                    document.getElementById('discountAmount').innerText=discountAmount.toFixed(2);
                    document.getElementById('discountRow').style.display='flex';
                    document.getElementById('hiddenDiscountAmount').value=discountAmount;
                    let message = (data.source === 'birthday') ? 'Birthday code applied! ' + data.discount_percent + '% off' : 'Voucher applied! RM'+discountAmount.toFixed(2)+' off';
                    document.getElementById('voucherMessage').innerHTML='<span class="success">'+message+'</span>';
                    refreshTotals(); checkIcAndRecalc();
                } else {
                    document.getElementById('voucherMessage').innerHTML='<span class="error">'+data.message+'</span>';
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                document.getElementById('voucherMessage').innerHTML='<span class="error">Network error, please try again.</span>';
            });
    });
    let pointsToggle=document.getElementById('usePointsToggle');
    pointsToggle.addEventListener('change',function(){
        let usePoints=this.checked;
        fetch(`?action=calculate_points&use_points=${usePoints}&subtotal=${subtotal-discountAmount}&discount=${discountAmount}&user_points=${userPoints}`)
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    pointsDeduction=data.points_deduction;
                    let used=data.points_used;
                    document.getElementById('pointsDeductionAmount').innerText=pointsDeduction.toFixed(2);
                    document.getElementById('pointsRow').style.display=pointsDeduction>0?'flex':'none';
                    document.getElementById('hiddenPointsDeduction').value=pointsDeduction;
                    document.getElementById('hiddenPointsUsed').value=used;
                    document.getElementById('pointsDeductionInfo').innerHTML=`Using ${used} points (RM${pointsDeduction.toFixed(2)})`;
                    document.getElementById('pointsInfo').style.display=pointsDeduction>0?'block':'none';
                    refreshTotals(); checkIcAndRecalc();
                }
            });
    });
});
</script>

<!-- Lightbox -->
<div id="lightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center;">
  <span onclick="closeLightbox()" style="position:absolute; top:20px; right:30px; color:white; font-size:40px; cursor:pointer;">&times;</span>
  <img id="lightboxImg" style="max-width:90%; max-height:90%;">
</div>

<script>
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').style.display = 'flex';
}
function closeLightbox() {
  document.getElementById('lightbox').style.display = 'none';
}
document.getElementById('lightbox').onclick = function(e) {
  if (e.target === this) closeLightbox();
}
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
<?php
// payment.php - Booking Payment Page (Clean & Defensive)

session_start();
include '../Shared/config.php';
include '../Shared/header.php';

// --- AJAX Handlers ---
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
// --- End AJAX handlers ---

// Check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: ../ChangJingEn/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// 1. Fetch user data safely
$fullname = '';
$email = '';
$country = '';
$nationality = 'malaysian';
$user_points = 0;

$user_query = "SELECT first_name, last_name, email, country, points FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    $fullname = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $email = $user['email'] ?? '';
    $country = $user['country'] ?? '';
    $nationality = ($country == 'Malaysia') ? 'malaysian' : 'foreigner';
    $user_points = (int)($user['points'] ?? 0);
} else {
    die("<p>Error: User not found. Please log in again.</p><a href='../ChangJingEn/login.php'>Login</a>");
}

// 2. Get booking data from GET
$room_id   = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 1;
$check_in  = isset($_GET['arrive']) ? $_GET['arrive'] : date('Y-m-d');
$check_out = isset($_GET['depart']) ? $_GET['depart'] : date('Y-m-d', strtotime('+2 days'));
$guests    = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

// 3. Fetch room data safely with relative image path
$room_name = 'Unknown Room';
$room_price = 0.0;
$room_image = '../images/room-default.jpg';  // relative fallback

$room_sql = "SELECT name, price, image FROM rooms WHERE id = $room_id";
$room_result = mysqli_query($conn, $room_sql);
if ($room_result && mysqli_num_rows($room_result) > 0) {
    $room = mysqli_fetch_assoc($room_result);
    $room_name = htmlspecialchars($room['name'] ?? 'Unknown Room');
    $room_price = (float)($room['price'] ?? 0);
    $img_file = trim($room['image'] ?? '');
    if (!empty($img_file)) {
        $room_image = '../images/' . $img_file;
    }
} else {
    $room_name = 'Standard Room';
    $room_price = 150.00;
}

// 4. Calculate nights & subtotal
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$nights = $date2->diff($date1)->days;
if ($nights <= 0) $nights = 1;

$subtotal = $room_price * $nights;

$points_per_rm = 10;
$points_earned_display = floor($subtotal * $points_per_rm);

// Initial totals (no discount yet)
$sst_tax = $subtotal * 0.12;
$foreigner_tax = ($nationality == 'foreigner') ? $subtotal * 0.10 : 0;
$service_fee = $subtotal * 0.05;
$grand_total = $subtotal + $sst_tax + $foreigner_tax + $service_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Your Booking | Grand Hotel</title>
    <link rel="stylesheet" href="css/payment.css">
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
            <!-- Left Column - Summary -->
            <div>
                <div class="booking-details-card">
                    <div class="card-header">
                        <h3><i class="fas fa-file-alt"></i> Booking Summary</h3>
                    </div>
                    <div class="card-content">
                        <div class="room-info">
                            <div class="room-image">
                                <img src="<?= htmlspecialchars($room_image) ?>" 
                                     alt="<?= htmlspecialchars($room_name) ?>" 
                                     style="width:80px;height:80px;object-fit:cover;border-radius:10px"
                                     onerror="this.src='../images/room-default.jpg'; this.onerror=null;">
                            </div>
                            <div class="room-details">
                                <h3><?= htmlspecialchars($room_name) ?></h3>
                                <p><i class="fas fa-tag"></i> RM<?= number_format($room_price, 0) ?> / night</p>
                                <p><i class="fas fa-users"></i> Up to <?= (int)$guests ?> guests</p>
                            </div>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><i class="fas fa-calendar-check"></i> Check-in</span>
                            <span class="summary-value"><?= date('d F Y', strtotime($check_in)) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><i class="fas fa-calendar-times"></i> Check-out</span>
                            <span class="summary-value"><?= date('d F Y', strtotime($check_out)) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><i class="fas fa-moon"></i> Nights</span>
                            <span class="summary-value"><?= (int)$nights ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><i class="fas fa-users"></i> Guests</span>
                            <span class="summary-value"><?= (int)$guests ?></span>
                        </div>
                        <div class="total-section" id="totalSection">
                            <div class="total-item">
                                <span>Subtotal (<?= $nights ?> nights)</span>
                                <span>RM <span id="subtotalAmount"><?= number_format($subtotal, 2) ?></span></span>
                            </div>
                            <div class="total-item discount-row" id="discountRow" style="display:none">
                                <span>Voucher Discount</span>
                                <span>-RM <span id="discountAmount">0.00</span></span>
                            </div>
                            <div class="total-item points-row" id="pointsRow" style="display:none">
                                <span>Points Deduction</span>
                                <span>-RM <span id="pointsDeductionAmount">0.00</span></span>
                            </div>
                            <div class="total-item">
                                <span>SST Tax (12%)</span>
                                <span>RM <span id="sstTax"><?= number_format($sst_tax, 2) ?></span></span>
                            </div>
                            <div class="total-item" id="foreignerTaxRow" style="display:<?= $nationality == 'foreigner' ? 'flex' : 'none' ?>">
                                <span>Tourism Tax (10%)</span>
                                <span>RM <span id="foreignerTax"><?= number_format($foreigner_tax, 2) ?></span></span>
                            </div>
                            <div class="total-item">
                                <span>Service Fee (5%)</span>
                                <span>RM <span id="serviceFee"><?= number_format($service_fee, 2) ?></span></span>
                            </div>
                            <div class="grand-total">
                                <span><strong>Total Amount</strong></span>
                                <span><strong>RM <span id="grandTotal"><?= number_format($grand_total, 2) ?></span></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cancellation-policy"> 
                    <i class="fas fa-shield-alt"></i> <strong>Free Cancellation</strong>
                    <small>Cancel up to 24 hours before check-in for a full refund.</small><br>
                    <i class="fas fa-money-bill-wave"></i> <strong>Tourism Fee</strong>
                    <small>Foreigner guests will be charged an additional 10% tourism tax.</small>
                    <br><small>*Terms and Conditions apply & Subject to Change.</small>
                </div>
            </div>

            <!-- Right Column - Payment Details -->
            <div>
                <div class="payment-card">
                    <div class="card-header">
                        <h3><i class="fas fa-credit-card"></i> Payment Details</h3>
                    </div>
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
                            <div class="points-info" id="pointsInfo" style="display:none">
                                <small>100 points = RM1 deduction</small>
                                <div id="pointsDeductionInfo"></div>
                            </div>
                            <div class="points-earned">
                                <small>You'll earn <strong id="pointsEarned"><?= number_format($points_earned_display) ?></strong> points (excluding taxes)</small>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="payment-methods">
                            <label>Select Payment Method</label>
                            <div class="payment-option">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                <label for="credit_card"><i class="fab fa-cc-visa"></i> Credit/Debit Card</label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="TouchnGo" name="payment_method" value="TouchnGo">
                                <label for="TouchnGo"><i class="fas fa-wifi"></i> Touch 'n Go</label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="online_banking" name="payment_method" value="online_banking">
                                <label for="online_banking"><i class="fas fa-university"></i> Online Banking</label>
                            </div>
                        </div>

                        <!-- Credit Card Details -->
                        <div class="form-group" id="card_details" style="display: block;">
                            <label>Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px">
                                <div>
                                    <label>Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                </div>
                                <div>
                                    <label>CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Special Requests</label>
                            <textarea class="form-control" name="special_requests" rows="3" placeholder="Any special requests?"></textarea>
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">
                        <input type="hidden" name="room_price" value="<?= $room_price ?>">
                        <input type="hidden" name="check_in" value="<?= $check_in ?>">
                        <input type="hidden" name="check_out" value="<?= $check_out ?>">
                        <input type="hidden" name="guests" value="<?= $guests ?>">
                        <input type="hidden" name="nights" value="<?= $nights ?>">
                        <input type="hidden" name="subtotal" id="hiddenSubtotal" value="<?= $subtotal ?>">
                        <input type="hidden" name="nationality" id="hiddenNationality" value="<?= $nationality ?>">
                        <input type="hidden" name="discount_amount" id="hiddenDiscountAmount" value="0">
                        <input type="hidden" name="points_deduction" id="hiddenPointsDeduction" value="0">
                        <input type="hidden" name="points_used" id="hiddenPointsUsed" value="0">

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
// Global variables for calculations
let subtotal = <?= json_encode($subtotal) ?>;
let userPoints = <?= json_encode($user_points) ?>;
let discountAmount = 0;
let pointsDeduction = 0;
let currentNationality = <?= json_encode($nationality) ?>;

function toggleCreditCardFields() {
    let selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    let cardDetailsDiv = document.getElementById('card_details');
    if (selectedMethod === 'credit_card') {
        cardDetailsDiv.style.display = 'block';
        document.getElementById('card_number').setAttribute('required', 'required');
        document.getElementById('expiry_date').setAttribute('required', 'required');
        document.getElementById('cvv').setAttribute('required', 'required');
    } else {
        cardDetailsDiv.style.display = 'none';
        document.getElementById('card_number').removeAttribute('required');
        document.getElementById('expiry_date').removeAttribute('required');
        document.getElementById('cvv').removeAttribute('required');
    }
}

function restrictCardNumber() {
    let cardInput = document.getElementById('card_number');
    if (cardInput) {
        cardInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });
    }
}

function validatePaymentForm() {
    let selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
    if (selectedMethod === 'credit_card') {
        let cardNum = document.getElementById('card_number').value.trim();
        let expiry = document.getElementById('expiry_date').value.trim();
        let cvv = document.getElementById('cvv').value.trim();
        
        if (cardNum === '') {
            alert('Please enter card number');
            return false;
        }
        if (!/^\d{16}$/.test(cardNum)) {
            alert('Credit card number must be exactly 16 digits (no spaces or dashes)');
            return false;
        }
        if (expiry === '') {
            alert('Please enter expiry date (MM/YY)');
            return false;
        }
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) {
            alert('Expiry date must be in MM/YY format (e.g., 12/25)');
            return false;
        }
        if (cvv === '') {
            alert('Please enter CVV');
            return false;
        }
        if (!/^\d{3,4}$/.test(cvv)) {
            alert('CVV must be 3 or 4 digits');
            return false;
        }
    }
    let phone = document.querySelector('input[name="phone"]').value.trim();
    if (phone === '') {
        alert('Please enter phone number');
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    let radios = document.querySelectorAll('input[name="payment_method"]');
    radios.forEach(radio => {
        radio.addEventListener('change', toggleCreditCardFields);
    });
    toggleCreditCardFields();
    restrictCardNumber();

    document.getElementById('applyVoucherBtn').addEventListener('click', function() {
        let code = document.getElementById('voucher_code').value;
        if (!code) {
            alert('Please enter voucher code');
            return;
        }
        fetch(`?action=validate_voucher&code=${encodeURIComponent(code)}&subtotal=${subtotal}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    discountAmount = data.discount_amount;
                    document.getElementById('discountAmount').innerText = discountAmount.toFixed(2);
                    document.getElementById('discountRow').style.display = 'flex';
                    document.getElementById('hiddenDiscountAmount').value = discountAmount;
                    document.getElementById('voucherMessage').innerHTML = '<span class="success">Voucher applied! RM' + discountAmount.toFixed(2) + ' off</span>';
                    recalculateTotals();
                } else {
                    document.getElementById('voucherMessage').innerHTML = '<span class="error">' + data.message + '</span>';
                }
            });
    });

    let pointsToggle = document.getElementById('usePointsToggle');
    pointsToggle.addEventListener('change', function() {
        let usePoints = this.checked;
        fetch(`?action=calculate_points&use_points=${usePoints}&subtotal=${subtotal - discountAmount}&discount=${discountAmount}&user_points=${userPoints}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    pointsDeduction = data.points_deduction;
                    let pointsUsedAmount = data.points_used;
                    document.getElementById('pointsDeductionAmount').innerText = pointsDeduction.toFixed(2);
                    document.getElementById('pointsRow').style.display = pointsDeduction > 0 ? 'flex' : 'none';
                    document.getElementById('hiddenPointsDeduction').value = pointsDeduction;
                    document.getElementById('hiddenPointsUsed').value = pointsUsedAmount;
                    document.getElementById('pointsDeductionInfo').innerHTML = `Using ${pointsUsedAmount} points (RM${pointsDeduction.toFixed(2)})`;
                    document.getElementById('pointsInfo').style.display = pointsDeduction > 0 ? 'block' : 'none';
                    recalculateTotals();
                }
            });
    });

    function recalculateTotals() {
        let afterDiscount = subtotal - discountAmount - pointsDeduction;
        if (afterDiscount < 0) afterDiscount = 0;
        let sst = afterDiscount * 0.12;
        let foreignerTax = (currentNationality === 'foreigner') ? subtotal * 0.10 : 0;
        let serviceFee = afterDiscount * 0.05;
        let grand = afterDiscount + sst + foreignerTax + serviceFee;
        document.getElementById('sstTax').innerText = sst.toFixed(2);
        document.getElementById('foreignerTax').innerText = foreignerTax.toFixed(2);
        document.getElementById('serviceFee').innerText = serviceFee.toFixed(2);
        document.getElementById('grandTotal').innerText = grand.toFixed(2);
    }
});
</script>

<?php include '../Shared/footer.php'; ?>
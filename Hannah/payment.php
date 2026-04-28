<?php
// payment.php - with IC/Passport field (ic_no) + initial tax based on user's country
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
    // If country is Malaysia but IC is empty or invalid, still consider Malaysian? We'll use IC as final.
    // For AJAX, we respect IC only.
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
// --- End AJAX ---

// --- Voucher & Points AJAX (unchanged) ---
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

// Get booking data
$room_id   = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 1;
$check_in  = isset($_GET['arrive']) ? $_GET['arrive'] : date('Y-m-d');
$check_out = isset($_GET['depart']) ? $_GET['depart'] : date('Y-m-d', strtotime('+2 days'));
$guests    = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

// Fetch room details
$room_name = 'Unknown Room';
$room_price = 0.0;
$room_image = '../ChongEeLynn/images/room-default.jpg';
$room_sql = "SELECT name, price, image FROM rooms WHERE id = $room_id";
$room_result = mysqli_query($conn, $room_sql);
if ($room_result && mysqli_num_rows($room_result) > 0) {
    $room = mysqli_fetch_assoc($room_result);
    $room_name = htmlspecialchars($room['name'] ?? 'Unknown Room');
    $room_price = (float)($room['price'] ?? 0);
    $img_file = trim($room['image'] ?? '');
    if (!empty($img_file)) $room_image = '../ChongEeLynn/images/' . $img_file;
} else {
    $room_name = 'Standard Room';
    $room_price = 150.00;
}

// Calculate nights & subtotal
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$nights = $date2->diff($date1)->days;
if ($nights <= 0) $nights = 1;
$subtotal = $room_price * $nights;
$points_per_rm = 10;
$points_earned_display = floor($subtotal * $points_per_rm);

// ---- NEW: Initial tax based on user's country ----
$user_is_malaysian = ($country == 'Malaysia');  // true if profile country is Malaysia
$tourism_tax_initial = $user_is_malaysian ? 0 : (10 * $nights);
$sst_initial = $subtotal * 0.08;
$service_initial = $subtotal * 0.05;
$grand_initial = $subtotal + $sst_initial + $tourism_tax_initial + $service_initial;
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
                    <div class="card-header"><h3><i class="fas fa-file-alt"></i> Booking Summary</h3></div>
                    <div class="card-content">
                        <div class="room-info">
                            <div class="room-image">
                                <img src="<?= htmlspecialchars($room_image) ?>" alt="<?= htmlspecialchars($room_name) ?>" onerror="this.src='../ChongEeLynn/images/room-default.jpg'">
                            </div>
                            <div class="room-details">
                                <h3><?= htmlspecialchars($room_name) ?></h3>
                                <p><i class="fas fa-tag"></i> RM<?= number_format($room_price, 0) ?> / night</p>
                                <p><i class="fas fa-users"></i> Up to <?= (int)$guests ?> guests</p>
                            </div>
                        </div>
                        <div class="summary-item"><span class="summary-label"><i class="fas fa-calendar-check"></i> Check-in</span><span class="summary-value"><?= date('d F Y', strtotime($check_in)) ?></span></div>
                        <div class="summary-item"><span class="summary-label"><i class="fas fa-calendar-times"></i> Check-out</span><span class="summary-value"><?= date('d F Y', strtotime($check_out)) ?></span></div>
                        <div class="summary-item"><span class="summary-label"><i class="fas fa-moon"></i> Nights</span><span class="summary-value"><?= $nights ?></span></div>
                        <div class="summary-item"><span class="summary-label"><i class="fas fa-users"></i> Guests</span><span class="summary-value"><?= $guests ?></span></div>
                        <div class="total-section" id="totalSection">
                            <div class="total-item"><span>Subtotal (<?= $nights ?> nights)</span><span>RM <span id="subtotalAmount"><?= number_format($subtotal, 2) ?></span></span></div>
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
                            <label><i class="fas fa-id-card"></i> IC Number</label>
                            <input type="text" class="form-control" id="ic_no" name="ic_no" placeholder="MyKad (12 digits)" required>
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

                        <div class="form-group"><label>Special Requests</label><textarea class="form-control" name="special_requests" rows="3" placeholder="Any special requests?"></textarea></div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">
                        <input type="hidden" name="room_price" value="<?= $room_price ?>">
                        <input type="hidden" name="check_in" value="<?= $check_in ?>">
                        <input type="hidden" name="check_out" value="<?= $check_out ?>">
                        <input type="hidden" name="guests" value="<?= $guests ?>">
                        <input type="hidden" name="nights" id="nightsHidden" value="<?= $nights ?>">
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
let nights = <?= json_encode($nights) ?>;
let userPoints = <?= json_encode($user_points) ?>;
let discountAmount = 0;
let pointsDeduction = 0;
let currentIcValue = '';
let userCountry = document.getElementById('userCountry').value;
// Initialise isMalaysianByIc based on user's country (if Malaysia, start with true)
let isMalaysianByIc = (userCountry === 'Malaysia') ? true : false;

function refreshTotals() {
    let afterDiscount = subtotal - discountAmount - pointsDeduction;
    if (afterDiscount < 0) afterDiscount = 0;
    let tourismTax = isMalaysianByIc ? 0 : (nights * 10);
    let sst = afterDiscount * 0.08;
    let serviceFee = afterDiscount * 0.05;
    let grand = afterDiscount + sst + tourismTax + serviceFee;
    document.getElementById('sstTax').innerText = sst.toFixed(2);
    document.getElementById('tourismTax').innerText = tourismTax.toFixed(2);
    document.getElementById('serviceFee').innerText = serviceFee.toFixed(2);
    document.getElementById('grandTotal').innerText = grand.toFixed(2);
    document.getElementById('hiddenTourismTax').value = tourismTax;
    let taxRow = document.getElementById('tourismTaxRow');
    if (taxRow) {
        let labelSpan = taxRow.querySelector('span:first-child');
        if (labelSpan) labelSpan.innerHTML = isMalaysianByIc ? 'Tourism Tax (RM10/room/night)': 'Tourism Tax (RM10/room/night)';
    }
    let statusSpan = document.getElementById('icStatus');
    if (statusSpan) {
        if (isMalaysianByIc) {
            if (userCountry === 'Malaysia' && currentIcValue === '') {
                statusSpan.innerHTML = 'Malaysian profile detected – Tourism tax waived. Please enter your MyKad for verification.';
            }
            statusSpan.style.color = 'green';
        } 
    }
}

function checkIcAndRecalc() {
    let ic = document.getElementById('ic_no').value.trim();
    if (ic === currentIcValue) return;
    currentIcValue = ic;
    if (ic === '') {
        // If IC field becomes empty, revert to country-based rule
        isMalaysianByIc = (userCountry === 'Malaysia');
        refreshTotals();
        return;
    }
    // Use AJAX to check IC (12-digit rule)
    fetch(`?action=check_ic&ic_no=${encodeURIComponent(ic)}&nights=${nights}&subtotal=${subtotal - discountAmount - pointsDeduction}&discount=${discountAmount}&points=${pointsDeduction}&country=${encodeURIComponent(userCountry)}`)
        .then(res => res.json())
        .then(data => {
            if (data && typeof data.tourism_tax !== 'undefined') {
                isMalaysianByIc = data.is_malaysian;
                refreshTotals();
            }
        })
        .catch(err => console.log('IC check failed', err));
}

// The rest of your JS functions (toggleCreditCardFields, restrictCardNumber, validatePaymentForm, DOMContentLoaded) remain the same
// ... (keep your existing functions below)

function toggleCreditCardFields() {
    let selected = document.querySelector('input[name="payment_method"]:checked').value;
    let cardDiv = document.getElementById('card_details');
    if (selected === 'credit_card') {
        cardDiv.style.display = 'block';
        ['card_number','expiry_date','cvv'].forEach(id => {
            document.getElementById(id).setAttribute('required','required');
        });
    } else {
        cardDiv.style.display = 'none';
        ['card_number','expiry_date','cvv'].forEach(id => {
            document.getElementById(id).removeAttribute('required');
        });
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
    // Initial check: if user is Malaysian by profile, show message without waiting for IC
    refreshTotals();
    if (userCountry === 'Malaysia') {
        let statusSpan = document.getElementById('icStatus');
        if (statusSpan) statusSpan.innerHTML = 'Malaysian profile detected – Tourism tax waived. Please enter your MyKad for verification.';
    }
    document.getElementById('applyVoucherBtn').addEventListener('click',function(){
        let code=document.getElementById('voucher_code').value;
        if(!code){alert('Enter voucher code');return;}
        fetch(`?action=validate_voucher&code=${encodeURIComponent(code)}&subtotal=${subtotal}`)
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    discountAmount=data.discount_amount;
                    document.getElementById('discountAmount').innerText=discountAmount.toFixed(2);
                    document.getElementById('discountRow').style.display='flex';
                    document.getElementById('hiddenDiscountAmount').value=discountAmount;
                    document.getElementById('voucherMessage').innerHTML='<span class="success">Voucher applied! RM'+discountAmount.toFixed(2)+' off</span>';
                    refreshTotals(); checkIcAndRecalc();
                } else document.getElementById('voucherMessage').innerHTML='<span class="error">'+data.message+'</span>';
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
<?php include '../Shared/footer.php'; ?>
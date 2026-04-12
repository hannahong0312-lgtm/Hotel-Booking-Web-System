<?php
// payment.php - Booking Payment Page

session_start();
include '../Shared/config.php';
include '../Shared/header.php';

// --- AJAX Handlers (must be before any HTML output) ---
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

if (isset($_GET['action']) && $_GET['action'] == 'calculate_tokens' && isset($_GET['use_tokens'])) {
    header('Content-Type: application/json');
    $use_tokens_flag = $_GET['use_tokens'] == 'true';
    $current_subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
    $current_discount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;
    $user_tokens_available = isset($_GET['user_tokens']) ? intval($_GET['user_tokens']) : 0;
    
    $after_discount = $current_subtotal - $current_discount;
    if ($after_discount < 0) $after_discount = 0;
    
    $tokens_deduction_amount = 0;
    $tokens_used = 0;
    
    if ($use_tokens_flag && $user_tokens_available > 0) {
        $max_deduction = min($after_discount, floor($user_tokens_available / 100));
        $tokens_deduction_amount = $max_deduction;
        $tokens_used = $max_deduction * 100;
    }
    
    echo json_encode([
        'success' => true,
        'tokens_deduction' => $tokens_deduction_amount,
        'tokens_used' => $tokens_used,
        'remaining_tokens' => $user_tokens_available - $tokens_used
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

// Fetch user details
$user_query = "SELECT first_name, last_name, email, country, token FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$fullname = $user['first_name'] . ' ' . $user['last_name'];
$email = $user['email'];
$country = $user['country'];
$nationality = ($country == 'Malaysia') ? 'malaysian' : 'foreigner';
$user_tokens = $user['token'];

// Get booking data from GET (from roomdetails.php)
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 1;
$check_in = isset($_GET['arrive']) ? $_GET['arrive'] : date('Y-m-d');
$check_out = isset($_GET['depart']) ? $_GET['depart'] : date('Y-m-d', strtotime('+2 days'));
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 2;

// Fetch room details
$room_sql = "SELECT name, price, image FROM rooms WHERE id = $room_id";
$room_result = mysqli_query($conn, $room_sql);
$room = mysqli_fetch_assoc($room_result);
$room_name = $room['name'];
$room_price = $room['price'];
$room_image = $room['image'] ?: 'images/room-default.jpg';

// Calculate nights & subtotal
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$nights = $date2->diff($date1)->days;
if ($nights <= 0) $nights = 1;
$subtotal = $room_price * $nights;

$tokens_per_rm = 10;
$tokens_earned_display = floor($subtotal * $tokens_per_rm);

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

    <form method="POST" action="process_payment.php" id="bookingForm">
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
                                <img src="<?= htmlspecialchars($room_image) ?>" alt="<?= htmlspecialchars($room_name) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:10px">
                            </div>
                            <div class="room-details">
                                <h3><?= htmlspecialchars($room_name) ?></h3>
                                <p><i class="fas fa-tag"></i> RM<?= number_format($room_price, 0) ?> / night</p>
                                <p><i class="fas fa-users"></i> Up to <?= $guests ?> guests</p>
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
                            <span class="summary-value"><?= $nights ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label"><i class="fas fa-users"></i> Guests</span>
                            <span class="summary-value"><?= $guests ?></span>
                        </div>
                        <div class="total-section" id="totalSection">
                            <div class="total-item">
                                <span>Subtotal (<?= $nights ?> nights)</span>
                                <span>RM <span id="subtotalAmount"><?= number_format($subtotal,2) ?></span></span>
                            </div>
                            <div class="total-item discount-row" id="discountRow" style="display:none">
                                <span>Voucher Discount</span>
                                <span>-RM <span id="discountAmount">0.00</span></span>
                            </div>
                            <div class="total-item tokens-row" id="tokensRow" style="display:none">
                                <span>Tokens Deduction</span>
                                <span>-RM <span id="tokensDeductionAmount">0.00</span></span>
                            </div>
                            <div class="total-item">
                                <span>SST Tax (12%)</span>
                                <span>RM <span id="sstTax"><?= number_format($sst_tax,2) ?></span></span>
                            </div>
                            <div class="total-item" id="foreignerTaxRow" style="display:<?= $nationality=='foreigner'?'flex':'none' ?>">
                                <span>Tourism Tax (10%)</span>
                                <span>RM <span id="foreignerTax"><?= number_format($foreigner_tax,2) ?></span></span>
                            </div>
                            <div class="total-item">
                                <span>Service Fee (5%)</span>
                                <span>RM <span id="serviceFee"><?= number_format($service_fee,2) ?></span></span>
                            </div>
                            <div class="grand-total">
                                <span><strong>Total Amount</strong></span>
                                <span><strong>RM <span id="grandTotal"><?= number_format($grand_total,2) ?></span></strong></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cancellation-policy"> 
                    <i class="fas fa-shield-alt"></i> <strong>Free Cancellation</strong>
                    <small>Cancel up to 24 hours before check-in for a full refund.</small><br>
                    <i class="fas fa-money-bill-wave"></i> <strong>Tourism Fee</strong>
                    <small>Foreigner guests will be charged an additional 10% tourism tax.</small>
                    <br><small>*Terms and Conditions apply.</small>
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

                        <!-- Tokens Section -->
                        <div class="form-group tokens-section">
                            <div class="tokens-header">
                                <label><i class="fas fa-coins"></i> Your Tokens</label>
                                <span class="tokens-balance">Available: <strong id="userTokens"><?= number_format($user_tokens) ?></strong></span>
                            </div>
                            <div class="tokens-toggle-row">
                                <span class="toggle-label">Use tokens to reduce total</span>
                                <label class="switch">
                                    <input type="checkbox" id="useTokensToggle" name="use_tokens" value="1">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="tokens-info" id="tokensInfo" style="display:none">
                                <small>100 tokens = RM1 deduction</small>
                                <div id="tokensDeductionInfo"></div>
                            </div>
                            <div class="tokens-earned">
                                <small>You'll earn <strong id="tokensEarned"><?= number_format($tokens_earned_display) ?></strong> tokens</small>
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

                        <div class="form-group" id="card_details">
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

                        <!-- Hidden fields to pass booking data -->
                        <input type="hidden" name="room_id" value="<?= $room_id ?>">
                        <input type="hidden" name="room_name" value="<?= htmlspecialchars($room_name) ?>">
                        <input type="hidden" name="room_price" value="<?= $room_price ?>">
                        <input type="hidden" name="check_in" value="<?= $check_in ?>">
                        <input type="hidden" name="check_out" value="<?= $check_out ?>">
                        <input type="hidden" name="guests" value="<?= $guests ?>">
                        <input type="hidden" name="nights" value="<?= $nights ?>">
                        <input type="hidden" name="subtotal" id="hiddenSubtotal" value="<?= $subtotal ?>">
                        <input type="hidden" name="nationality" id="hiddenNationality" value="<?= $nationality ?>">
                        <input type="hidden" name="discount_amount" id="hiddenDiscountAmount" value="0">
                        <input type="hidden" name="tokens_deduction" id="hiddenTokensDeduction" value="0">
                        <input type="hidden" name="tokens_used" id="hiddenTokensUsed" value="0">

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
// Your existing JavaScript (keep as is, it's already correct)
let subtotal = <?= $subtotal ?>;
let userTokens = <?= $user_tokens ?>;
let discountAmount = 0;
let tokensDeduction = 0;
let currentNationality = '<?= $nationality ?>';

function recalculateTotals() { /* your existing function */ }
function applyVoucher() { /* your existing function */ }
function handleTokensToggle() { /* your existing function */ }

// Event listeners etc.
</script>

<?php include '../Shared/footer.php'; ?>
<?php
// payment.php - Payment Simulation Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include header
include '../Shared/header.php';

// Check if booking data exists in session or GET parameters
if (isset($_SESSION['pending_booking'])) {
    $booking_data = $_SESSION['pending_booking'];
} else {
    // Fallback to GET parameters
    $booking_data = [
        'room_id' => isset($_GET['room_id']) ? $_GET['room_id'] : 1,
        'room_name' => isset($_GET['room_name']) ? $_GET['room_name'] : 'Deluxe Ocean View',
        'room_price' => isset($_GET['room_price']) ? $_GET['room_price'] : 299,
        'check_in' => isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d'),
        'check_out' => isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+2 days')),
        'guests' => isset($_GET['guests']) ? $_GET['guests'] : 2,
        'fullname' => isset($_GET['fullname']) ? $_GET['fullname'] : '',
        'email' => isset($_GET['email']) ? $_GET['email'] : '',
        'phone' => isset($_GET['phone']) ? $_GET['phone'] : ''
    ];
}

// Extract booking data
$room_id = $booking_data['room_id'];
$room_name = $booking_data['room_name'];
$room_price = $booking_data['room_price'];
$check_in = $booking_data['check_in'];
$check_out = $booking_data['check_out'];
$guests = $booking_data['guests'];
$fullname = $booking_data['fullname'];
$email = $booking_data['email'];
$phone = $booking_data['phone'];

// Calculate nights and total
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$nights = $date2->diff($date1)->days;
$total_price = $room_price * $nights;
$tax = $total_price * 0.12;
$service_fee = $total_price * 0.05;
$grand_total = $total_price + $tax + $service_fee;

// Generate booking reference if not exists
$booking_ref = isset($_SESSION['booking_ref']) ? $_SESSION['booking_ref'] : 'BK' . strtoupper(uniqid());
$_SESSION['booking_ref'] = $booking_ref;

// Payment processing simulation
$payment_processed = false;
$payment_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'credit_card';
    $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
    $card_name = isset($_POST['card_name']) ? $_POST['card_name'] : '';
    $expiry = isset($_POST['expiry']) ? $_POST['expiry'] : '';
    $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';
    
    // Basic validation
    $errors = [];
    
    if ($payment_method === 'credit_card') {
        if (empty($card_number) || strlen(preg_replace('/\s/', '', $card_number)) < 16) {
            $errors[] = 'Please enter a valid card number.';
        }
        if (empty($card_name)) {
            $errors[] = 'Please enter the cardholder name.';
        }
        if (empty($expiry)) {
            $errors[] = 'Please enter expiry date.';
        }
        if (empty($cvv) || strlen($cvv) < 3) {
            $errors[] = 'Please enter a valid CVV.';
        }
    }
    
    if (empty($errors)) {
        // Simulate payment processing
        $payment_processed = true;
        
        // Store complete booking in session
        $_SESSION['confirmed_booking'] = [
            'reference' => $booking_ref,
            'room_id' => $room_id,
            'room_name' => $room_name,
            'room_price' => $room_price,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests,
            'nights' => $nights,
            'total_price' => $total_price,
            'tax' => $tax,
            'service_fee' => $service_fee,
            'grand_total' => $grand_total,
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'payment_method' => $payment_method,
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        // Clear pending booking
        unset($_SESSION['pending_booking']);
        
        // Redirect to success page
        header('Location: booking_success.php?ref=' . $booking_ref);
        exit();
    } else {
        $payment_error = $errors;
    }
}
?>

<!-- Payment Page Specific CSS - only what's not in main.css -->
<style>
    /* Payment page specific styles - complements main.css without duplication */
    
    /* Payment container spacing */
    .payment-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 110px 20px 60px;
    }
    
    /* Payment header */
    .payment-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .payment-header h1 {
        font-size: 2.2rem;
        font-family: 'Playfair Display', serif;
        font-weight: 500;
        color: var(--black);
        margin-bottom: 12px;
    }
    
    .payment-header h1 i {
        color: var(--accent);
        margin-right: 12px;
    }
    
    .payment-header p {
        color: var(--gray-text);
        font-size: 1rem;
    }
    
    /* Payment grid layout */
    .payment-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 35px;
    }
    
    /* Order summary card */
    .order-summary {
        background: var(--white);
        border-radius: 24px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 1px solid var(--gray-border);
        position: sticky;
        top: 100px;
    }
    
    .summary-header {
        background: var(--black);
        color: var(--white);
        padding: 20px 25px;
        border-bottom: 3px solid var(--accent);
    }
    
    .summary-header h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .summary-header h2 i {
        color: var(--accent);
    }
    
    .summary-body {
        padding: 25px;
    }
    
    /* Room info in summary */
    .summary-room {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--gray-border);
    }
    
    .summary-room-icon {
        font-size: 2rem;
        color: var(--accent);
    }
    
    .summary-room-details h3 {
        margin: 0 0 8px 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--black);
    }
    
    .summary-room-details p {
        margin: 4px 0;
        color: var(--gray-text);
        font-size: 0.85rem;
    }
    
    .summary-room-details p i {
        color: var(--accent);
        width: 20px;
        margin-right: 5px;
    }
    
    /* Summary items */
    .summary-line {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid var(--gray-border);
        font-size: 0.9rem;
    }
    
    .summary-line.total {
        border-bottom: none;
        font-weight: 700;
        font-size: 1rem;
        color: var(--black);
    }
    
    .summary-line.grand {
        background: var(--accent);
        color: var(--white);
        padding: 15px;
        border-radius: 12px;
        margin-top: 10px;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .summary-line.grand span:first-child {
        font-weight: 600;
    }
    
    .summary-label {
        color: var(--gray-text);
    }
    
    .summary-label i {
        margin-right: 8px;
        color: var(--accent);
        width: 20px;
    }
    
    .summary-value {
        font-weight: 600;
        color: var(--black);
    }
    
    /* Payment card */
    .payment-card {
        background: var(--white);
        border-radius: 24px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 1px solid var(--gray-border);
    }
    
    .payment-card-header {
        background: var(--black);
        color: var(--white);
        padding: 20px 25px;
        border-bottom: 3px solid var(--accent);
    }
    
    .payment-card-header h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .payment-card-header h2 i {
        color: var(--accent);
    }
    
    .payment-card-body {
        padding: 25px;
    }
    
    /* Form styling - using main.css variables */
    .payment-form-group {
        margin-bottom: 22px;
    }
    
    .payment-form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--black);
        font-weight: 500;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .payment-form-group label i {
        margin-right: 8px;
        color: var(--accent);
    }
    
    .payment-form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--gray-border);
        border-radius: 12px;
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
        transition: var(--transition);
        background: var(--white);
    }
    
    .payment-form-control:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(255, 107, 74, 0.1);
    }
    
    /* Payment methods */
    .payment-methods-section {
        margin-bottom: 25px;
    }
    
    .payment-methods-section > label {
        display: block;
        margin-bottom: 15px;
        color: var(--black);
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .payment-option {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border: 1px solid var(--gray-border);
        border-radius: 12px;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .payment-option:hover {
        border-color: var(--accent);
        background: var(--gray-bg);
    }
    
    .payment-option input[type="radio"] {
        margin-right: 12px;
        width: 18px;
        height: 18px;
        accent-color: var(--accent);
        cursor: pointer;
    }
    
    .payment-option label {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        color: var(--black);
        font-weight: 500;
        flex: 1;
        margin: 0;
        text-transform: none;
        letter-spacing: normal;
    }
    
    .payment-option i {
        margin-right: 10px;
        font-size: 1.2rem;
        color: var(--accent);
    }
    
    /* Card details section */
    #card_details_section {
        transition: var(--transition);
    }
    
    .card-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }
    
    /* Button styling - consistent with main.css */
    .payment-btn {
        display: inline-block;
        padding: 14px 28px;
        border: none;
        border-radius: 40px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        width: 100%;
        font-family: 'Inter', sans-serif;
    }
    
    .payment-btn-primary {
        background: var(--accent);
        color: var(--white);
    }
    
    .payment-btn-primary:hover {
        background: var(--accent-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 74, 0.3);
    }
    
    .payment-btn-secondary {
        background: transparent;
        color: var(--black);
        border: 1px solid var(--gray-border);
    }
    
    .payment-btn-secondary:hover {
        background: var(--gray-bg);
        border-color: var(--black);
        transform: translateY(-2px);
    }
    
    /* Button group */
    .payment-button-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }
    
    .payment-button-group .payment-btn {
        flex: 1;
    }
    
    /* Error messages */
    .payment-error {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    
    .payment-error ul {
        margin: 0;
        padding-left: 20px;
        color: #991b1b;
        font-size: 0.85rem;
    }
    
    .payment-error li {
        margin: 5px 0;
    }
    
    /* Secure payment badge */
    .secure-badge {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--gray-border);
    }
    
    .secure-badge i {
        color: #10b981;
        margin-right: 8px;
    }
    
    .secure-badge span {
        color: var(--gray-text);
        font-size: 0.8rem;
    }
    
    /* Loading animation */
    @keyframes payment-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .payment-spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: white;
        animation: payment-spin 0.8s ease-in-out infinite;
        margin-right: 8px;
        vertical-align: middle;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .payment-container {
            padding: 90px 15px 40px;
        }
        
        .payment-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .payment-header h1 {
            font-size: 1.6rem;
        }
        
        .payment-button-group {
            flex-direction: column;
        }
        
        .card-row {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .order-summary {
            position: relative;
            top: 0;
        }
    }
</style>

<main>
    <div class="payment-container">
        <div class="payment-header">
            <h1><i class="fas fa-credit-card"></i> Secure Payment</h1>
            <p>Complete your booking by making a secure payment</p>
        </div>
        
        <div class="payment-grid">
            <!-- Left Column - Order Summary -->
            <div class="order-summary">
                <div class="summary-header">
                    <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                </div>
                <div class="summary-body">
                    <div class="summary-room">
                        <div class="summary-room-icon">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <div class="summary-room-details">
                            <h3><?php echo htmlspecialchars($room_name); ?></h3>
                            <p><i class="fas fa-tag"></i> RM<?php echo number_format($room_price, 0); ?> per night</p>
                            <p><i class="fas fa-users"></i> <?php echo htmlspecialchars($guests); ?> guest(s)</p>
                        </div>
                    </div>
                    
                    <div class="summary-line">
                        <span class="summary-label"><i class="fas fa-calendar-check"></i> Check-in</span>
                        <span class="summary-value"><?php echo date('M d, Y', strtotime($check_in)); ?></span>
                    </div>
                    <div class="summary-line">
                        <span class="summary-label"><i class="fas fa-calendar-times"></i> Check-out</span>
                        <span class="summary-value"><?php echo date('M d, Y', strtotime($check_out)); ?></span>
                    </div>
                    <div class="summary-line">
                        <span class="summary-label"><i class="fas fa-moon"></i> Nights</span>
                        <span class="summary-value"><?php echo $nights; ?> night(s)</span>
                    </div>
                    <div class="summary-line">
                        <span class="summary-label"><i class="fas fa-hashtag"></i> Booking Ref</span>
                        <span class="summary-value"><?php echo $booking_ref; ?></span>
                    </div>
                    
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid var(--gray-border);">
                        <div class="summary-line">
                            <span>Subtotal (<?php echo $nights; ?> nights)</span>
                            <span>RM<?php echo number_format($total_price, 2); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Tax (12%)</span>
                            <span>RM<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Service Fee (5%)</span>
                            <span>RM<?php echo number_format($service_fee, 2); ?></span>
                        </div>
                        <div class="summary-line grand">
                            <span><strong>Total Amount</strong></span>
                            <span><strong>RM<?php echo number_format($grand_total, 2); ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Payment Form -->
            <div class="payment-card">
                <div class="payment-card-header">
                    <h2><i class="fas fa-lock"></i> Payment Details</h2>
                </div>
                <div class="payment-card-body">
                    <?php if ($payment_error): ?>
                    <div class="payment-error">
                        <ul>
                            <?php foreach ($payment_error as $error): ?>
                                <li><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="paymentForm">
                        <!-- Customer Information Display -->
                        <div class="payment-form-group">
                            <label><i class="fas fa-user"></i> Guest Name</label>
                            <input type="text" class="payment-form-control" value="<?php echo htmlspecialchars($fullname ?: 'Guest'); ?>" readonly disabled style="background: var(--gray-bg);">
                        </div>
                        
                        <div class="payment-form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="text" class="payment-form-control" value="<?php echo htmlspecialchars($email ?: 'guest@example.com'); ?>" readonly disabled style="background: var(--gray-bg);">
                        </div>
                        
                        <!-- Payment Method Selection -->
                        <div class="payment-methods-section">
                            <label><i class="fas fa-credit-card"></i> Select Payment Method</label>
                            
                            <div class="payment-option">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                <label for="credit_card"><i class="fab fa-cc-visa"></i> Credit / Debit Card</label>
                                <i class="fas fa-check-circle" style="color: var(--accent); display: none;"></i>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="touchngo" name="payment_method" value="touchngo">
                                <label for="touchngo"><i class="fas fa-mobile-alt"></i> Touch 'n Go eWallet</label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="online_banking" name="payment_method" value="online_banking">
                                <label for="online_banking"><i class="fas fa-university"></i> Online Banking</label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="qr_pay" name="payment_method" value="qr_pay">
                                <label for="qr_pay"><i class="fas fa-qrcode"></i> QR Pay</label>
                            </div>
                        </div>
                        
                        <!-- Credit Card Details Section -->
                        <div id="card_details_section">
                            <div class="payment-form-group">
                                <label for="card_number"><i class="fas fa-credit-card"></i> Card Number</label>
                                <input type="text" class="payment-form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" autocomplete="off">
                            </div>
                            
                            <div class="payment-form-group">
                                <label for="card_name"><i class="fas fa-user"></i> Cardholder Name</label>
                                <input type="text" class="payment-form-control" id="card_name" name="card_name" placeholder="Name as on card">
                            </div>
                            
                            <div class="card-row">
                                <div class="payment-form-group">
                                    <label for="expiry"><i class="fas fa-calendar-alt"></i> Expiry Date</label>
                                    <input type="text" class="payment-form-control" id="expiry" name="expiry" placeholder="MM/YY">
                                </div>
                                <div class="payment-form-group">
                                    <label for="cvv"><i class="fas fa-shield-alt"></i> CVV</label>
                                    <input type="password" class="payment-form-control" id="cvv" name="cvv" placeholder="123" maxlength="4">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alternative Payment Methods Info -->
                        <div id="alternative_payment_info" style="display: none; text-align: center; padding: 30px 20px; background: var(--gray-bg); border-radius: 16px;">
                            <i class="fas fa-mobile-alt" style="font-size: 3rem; color: var(--accent); margin-bottom: 15px; display: block;"></i>
                            <p style="color: var(--gray-text); margin-bottom: 10px;">You will be redirected to complete your payment.</p>
                            <p style="color: var(--black); font-weight: 500;">Click "Pay Now" to continue</p>
                        </div>
                        
                        <div class="secure-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure payment • 256-bit encryption</span>
                        </div>
                        
                        <div class="payment-button-group">
                            <button type="button" class="payment-btn payment-btn-secondary" onclick="window.location.href='booksum.php'">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" name="process_payment" class="payment-btn payment-btn-primary" id="payNowBtn">
                                <i class="fas fa-lock"></i> Pay Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Payment method toggle
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetailsSection = document.getElementById('card_details_section');
    const alternativeInfo = document.getElementById('alternative_payment_info');
    
    function togglePaymentFields() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (selectedMethod === 'credit_card') {
            cardDetailsSection.style.display = 'block';
            alternativeInfo.style.display = 'none';
        } else {
            cardDetailsSection.style.display = 'none';
            alternativeInfo.style.display = 'block';
        }
    }
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', togglePaymentFields);
    });
    
    // Initial call
    togglePaymentFields();
    
    // Format card number
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\s/g, '');
            if (value.length > 16) value = value.slice(0, 16);
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            this.value = value;
        });
    }
    
    // Format expiry date
    const expiryInput = document.getElementById('expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\//g, '');
            if (value.length > 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            if (value.length > 5) value = value.slice(0, 5);
            this.value = value;
        });
    }
    
    // Restrict CVV to numbers only
    const cvvInput = document.getElementById('cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    
    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'credit_card') {
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const cardName = document.getElementById('card_name').value;
            const expiry = document.getElementById('expiry').value;
            const cvv = document.getElementById('cvv').value;
            
            if (!cardNumber || cardNumber.length < 16) {
                e.preventDefault();
                alert('Please enter a valid 16-digit card number.');
                return false;
            }
            
            if (!cardName) {
                e.preventDefault();
                alert('Please enter the cardholder name.');
                return false;
            }
            
            if (!expiry || expiry.length < 5) {
                e.preventDefault();
                alert('Please enter a valid expiry date (MM/YY).');
                return false;
            }
            
            if (!cvv || cvv.length < 3) {
                e.preventDefault();
                alert('Please enter a valid CVV.');
                return false;
            }
        }
        
        // Show loading state
        const submitBtn = document.getElementById('payNowBtn');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="payment-spinner"></span> Processing Payment...';
        submitBtn.disabled = true;
        
        // Allow form submission
        return true;
    });
    
    // Add visual feedback for payment method selection
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        radio.addEventListener('change', function() {
            paymentOptions.forEach(opt => {
                opt.style.borderColor = 'var(--gray-border)';
                opt.style.background = 'transparent';
            });
            if (this.checked) {
                option.style.borderColor = 'var(--accent)';
                option.style.background = 'var(--gray-bg)';
            }
        });
        
        // Highlight the selected one on page load
        if (radio.checked) {
            option.style.borderColor = 'var(--accent)';
            option.style.background = 'var(--gray-bg)';
        }
    });
</script>

<?php
include '../Shared/footer.php';
?>
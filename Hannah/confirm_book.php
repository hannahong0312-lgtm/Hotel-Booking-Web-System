<?php
// confirm_booking.php - Booking Confirmation Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include header
include '../Shared/header.php';

// Get booking data from POST or GET
$room_id = isset($_POST['room_id']) ? $_POST['room_id'] : (isset($_GET['room_id']) ? $_GET['room_id'] : 1);
$check_in = isset($_POST['check_in']) ? $_POST['check_in'] : (isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d'));
$check_out = isset($_POST['check_out']) ? $_POST['check_out'] : (isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+2 days')));
$guests = isset($_POST['guests']) ? $_POST['guests'] : (isset($_GET['guests']) ? $_GET['guests'] : 2);
$room_name = isset($_POST['room_name']) ? $_POST['room_name'] : (isset($_GET['room_name']) ? $_GET['room_name'] : 'Deluxe Ocean View');
$room_price = isset($_POST['room_price']) ? $_POST['room_price'] : (isset($_GET['room_price']) ? $_GET['room_price'] : 299);

// Calculate nights and total
$date1 = new DateTime($check_in);
$date2 = new DateTime($check_out);
$nights = $date2->diff($date1)->days;
$total_price = $room_price * $nights;
$tax = $total_price * 0.12; // 12% tax
$service_fee = $total_price * 0.05; // 5% service fee
$grand_total = $total_price + $tax + $service_fee;

// Generate booking reference
$booking_ref = 'BK' . strtoupper(uniqid());

// Process form submission
$booking_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    // Here you would normally save to database
    $booking_success = true;
    
    // Store booking details in session for confirmation
    $_SESSION['last_booking'] = [
        'reference' => $booking_ref,
        'room_name' => $room_name,
        'check_in' => $check_in,
        'check_out' => $check_out,
        'guests' => $guests,
        'nights' => $nights,
        'total' => $grand_total
    ];
    
    // Redirect to success page or show success message
    header('Location: booking_success.php?ref=' . $booking_ref);
    exit();
}
?>

<!-- Internal CSS -->
<style>
    /* Main container styling */
    .booking-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 100px 10px;
    }
    
    /* Booking header */
    .booking-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .booking-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
    }
    
    .booking-header h1 i {
        color: green;
        margin-right: 10px;
    }
    
    .booking-header p {
        color: #666;
        font-size: 1.1rem;
    }
    
    /* Booking grid layout */
    .booking-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 30px;
    }
    
    /* Booking details card */
    .booking-details-card,
    .payment-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .card-header {
        background: #E55A3B;
        color: white;
        padding: 20px 25px;
    }
    
    .card-header h2 {
        margin: 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .card-header h2 i {
        font-size: 1.3rem;
    }
    
    .card-content {
        padding: 25px;
    }
    
    /* Room info section */
    .room-info {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
    }
    
    .room-icon {
        font-size: 3rem;
        color: #E55A3B;
    }
    
    .room-details h3 {
        margin: 0 0 8px 0;
        font-size: 1.3rem;
        color: #333;
    }
    
    .room-details p {
        margin: 5px 0;
        color: #666;
    }
    
    /* Booking summary items */
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .summary-item:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        color: #666;
        font-weight: 500;
    }
    
    .summary-label i {
        margin-right: 8px;
        color: #E55A3B;
        width: 20px;
    }
    
    .summary-value {
        color: #333;
        font-weight: 600;
    }
    
    /* Total sections */
    .total-section {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #E55A3B;
    }
    
    .total-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-weight: 600;
    }
    
    .grand-total {
        background: #E55A3B;
        color: white;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
        font-size: 1.2rem;
    }
    
    /* Form styling */
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }
    
    .form-group label i {
        margin-right: 8px;
        color: #E55A3B;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    /* Payment methods */
    .payment-methods {
        margin-bottom: 25px;
        color: #E55A3B;
    }
    
    .payment-option {
        margin-bottom: 15px;
    }
    
    .payment-option input[type="radio"] {
        margin-right: 10px;
    }
    
    .payment-option label {
        display: inline-block;
        cursor: pointer;
        color: #333;
        padding: 2px 5px;
    }
    
    .payment-option i {
        margin-right: 8px;
        font-size: 1.2rem;
    }
    
    /* Button styling - matching main.css */
    .btn {
        display: inline-block;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
    }
    
    .btn-primary {
        background: #E55A3B;
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-block {
        width: 100%;
    }
    
    .btn-large {
        padding: 15px 30px;
        font-size: 1.1rem;
    }
    
    /* Button group */
    .button-group {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }
    
    .button-group .btn {
        flex: 1;
    }
    
    /* Cancellation policy */
    .cancellation-policy {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        border-left: 3px solid #E55A3B;
    }
    
    .cancellation-policy i {
        color: #E55A3B;
        margin-right: 8px;
    }
    
    .cancellation-policy small {
        color: #666;
        line-height: 1.5;
        display: block;
        margin-top: 8px;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .booking-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .booking-container {
            padding: 20px 15px;
        }
        
        .booking-header h1 {
            font-size: 1.8rem;
        }
        
        .button-group {
            flex-direction: column;
        }
        
        .room-info {
            flex-direction: column;
            text-align: center;
        }
        
        .room-icon {
            font-size: 2.5rem;
        }
    }
    
    /* Loading animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
    }
</style>

<main>
    <div class="booking-container">
        <div class="booking-header">
            <h1><i class="fas fa-check-circle"></i> Confirm Your Booking</h1>
            <p>Please review your booking details and complete the payment</p>
        </div>
        
        <form method="POST" action="" id="bookingForm">
            <div class="booking-grid">
                <!-- Left Column - Booking Summary -->
                <div>
                    <div class="booking-details-card">
                        <div class="card-header">
                            <h2><i class="fas fa-file-alt"></i> Booking Summary</h2>
                        </div>
                        <div class="card-content">
                            <div class="room-info">
                                <div class="room-icon">
                                    <i class="fas fa-hotel"></i>
                                </div>
                                <div class="room-details">
                                    <h3><?php echo htmlspecialchars($room_name); ?></h3>
                                    <p><i class="fas fa-tag"></i> $<?php echo number_format($room_price, 0); ?> per night</p>
                                    <p><i class="fas fa-users"></i> Up to <?php echo htmlspecialchars($guests); ?> guests</p>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-calendar-check"></i> Check-in Date</span>
                                <span class="summary-value"><?php echo date('F d, Y', strtotime($check_in)); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-calendar-times"></i> Check-out Date</span>
                                <span class="summary-value"><?php echo date('F d, Y', strtotime($check_out)); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-moon"></i> Number of Nights</span>
                                <span class="summary-value"><?php echo $nights; ?> night(s)</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-users"></i> Number of Guests</span>
                                <span class="summary-value"><?php echo htmlspecialchars($guests); ?> guest(s)</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-hashtag"></i> Booking Reference</span>
                                <span class="summary-value"><?php echo $booking_ref; ?></span>
                            </div>
                            
                            <div class="total-section">
                                <div class="total-item">
                                    <span>Subtotal (<?php echo $nights; ?> nights × $<?php echo number_format($room_price, 0); ?>)</span>
                                    <span>$<?php echo number_format($total_price, 2); ?></span>
                                </div>
                                <div class="total-item">
                                    <span>Tax (12%)</span>
                                    <span>$<?php echo number_format($tax, 2); ?></span>
                                </div>
                                <div class="total-item">
                                    <span>Service Fee (5%)</span>
                                    <span>$<?php echo number_format($service_fee, 2); ?></span>
                                </div>
                                <div class="grand-total">
                                    <span><strong>Total Amount</strong></span>
                                    <span><strong>$<?php echo number_format($grand_total, 2); ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cancellation-policy">
                        <i class="fas fa-shield-alt"></i> <strong>Free Cancellation</strong>
                        <small>Cancel up to 24 hours before check-in for a full refund. Terms and conditions apply.</small>
                    </div>
                </div>
                
                <!-- Right Column - Payment Details -->
                <div>
                    <div class="payment-card">
                        <div class="card-header">
                            <h2><i class="fas fa-credit-card"></i> Payment Details</h2>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required value="John Doe">
                            </div>
                            
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required value="john@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required value="+1 234 567 8900">
                            </div>
                            
                            <div class="payment-methods">
                                <label><i class="fas fa-credit-card"></i>Payment Method</label>                                <div class="payment-option">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                    <label for="credit_card"><i class="fab fa-cc-visa" ></i> Credit Card</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="TouchnGo" name="payment_method" value="TouchnGo">
                                    <label for="TouchnGo"><i class="fas fa-wifi"></i> TouchnGo</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                                    <label for="bank_transfer"><i class="fas fa-university"></i> Bank Transfer</label>
                                </div>
                            </div>
                            
                            <div class="form-group" id="card_details">
                                <label for="card_number"><i class="fas fa-credit-card"></i> Card Number</label>
                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                                
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                                    <div>
                                        <label for="expiry">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry" placeholder="MM/YY">
                                    </div>
                                    <div>
                                        <label for="cvv">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="special_requests"><i class="fas fa-comment"></i> Special Requests (Optional)</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="3" placeholder="Any special requests or preferences?"></textarea>
                            </div>
                            
                            <div class="button-group">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button type="submit" name="confirm_booking" class="btn btn-primary">
                                    <i class="fas fa-lock"></i> Confirm & Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden fields to pass data -->
            <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
            <input type="hidden" name="room_name" value="<?php echo htmlspecialchars($room_name); ?>">
            <input type="hidden" name="room_price" value="<?php echo htmlspecialchars($room_price); ?>">
            <input type="hidden" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
            <input type="hidden" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
            <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">
            <input type="hidden" name="nights" value="<?php echo $nights; ?>">
            <input type="hidden" name="total_price" value="<?php echo $total_price; ?>">
        </form>
    </div>
</main>

<script>
    // Toggle card details based on payment method
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('card_details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        });
    });
    
    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'credit_card') {
            const cardNumber = document.getElementById('card_number').value;
            const expiry = document.getElementById('expiry').value;
            const cvv = document.getElementById('cvv').value;
            
            if (!cardNumber || !expiry || !cvv) {
                e.preventDefault();
                alert('Please fill in all credit card details');
                return false;
            }
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
        submitBtn.disabled = true;
        
        return true;
    });
    
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
</script>

<?php
include '../Shared/footer.php';
?>
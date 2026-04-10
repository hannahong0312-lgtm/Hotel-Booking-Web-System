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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Booking | Grand Hotel</title>
    <link rel="stylesheet" href="css/payment.css">
<main>   
    <div class="booking-container">
        <div class="booking-header">
            <h1>Confirm Your Booking</h1>
            <p>Please review your booking details and complete the payment</p>
        </div>
        
        <form method="POST" action="" id="bookingForm">
            <div class="booking-grid">
                <!-- Left Column - Booking Summary -->
                <div>
                    <div class="booking-details-card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-alt"></i> Booking Summary</h3>
                        </div>
                        <div class="card-content">
                            <div class="room-info">
                                <div class="room-icon">
                                    <i class="fas fa-hotel"></i>
                                </div>
                                <div class="room-details">
                                    <h3><?php echo htmlspecialchars($room_name); ?></h3>
                                    <p><i class="fas fa-tag"></i> RM<?php echo number_format($room_price, 0); ?> per night</p>
                                    <p><i class="fas fa-users"></i> Up to <?php echo htmlspecialchars($guests); ?> guests</p>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-calendar-check"></i> Check-in Date</span>
                                <span class="summary-value"><?php echo date('d F Y', strtotime($check_in)); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-calendar-times"></i> Check-out Date</span>
                                <span class="summary-value"><?php echo date('d F Y', strtotime($check_out)); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-moon"></i> Number of Nights</span>
                                <span class="summary-value"><?php echo $nights; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-users"></i> Number of Guests</span>
                                <span class="summary-value"><?php echo htmlspecialchars($guests); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label"><i class="fas fa-hashtag"></i> Booking Reference</span>
                                <span class="summary-value"><?php echo $booking_ref; ?></span>
                            </div>
                            
                            <div class="total-section">
                                <div class="total-item">
                                    <span>Subtotal (<?php echo $nights; ?> nights × RM<?php echo number_format($room_price, 0); ?>)</span>
                                    <span>RM <?php echo number_format($total_price, 2); ?></span>
                                </div>
                                <div class="total-item">
                                    <span>Tax (12%)</span>
                                    <span>RM <?php echo number_format($tax, 2); ?></span>
                                </div>
                                <div class="total-item">
                                    <span>Service Fee (5%)</span>
                                    <span>RM <?php echo number_format($service_fee, 2); ?></span>
                                </div>
                                <div class="grand-total">
                                    <span><strong>Total Amount</strong></span>
                                    <span><strong>RM<?php echo number_format($grand_total, 2); ?></strong></span>
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
                            <h3><i class="fas fa-credit-card"></i> Payment Details</h3>
                        </div>
                        <div class="card-content">
                            <div class="form-group">
                                <label for="fullname"><i class="fas fa-user"></i> Guest Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required value="John Doe">
                            </div>
                            
                            <div class="form-group">
                                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required value="john@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required value="+60123456789">
                            </div>
                            
                            <div class="payment-methods">
                                <label><i class="fas fa-credit-card"></i> Select Payment Method</label>   
                                <div class="payment-option">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                    <label for="credit_card"><i class="fab fa-cc-visa" ></i> Credit/Debit Card</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="TouchnGo" name="payment_method" value="TouchnGo">
                                    <label for="TouchnGo"><i class="fas fa-wifi"></i> Touch 'n Go eWallet</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                                    <label for="bank_transfer"><i class="fas fa-university"></i> Online Banking</label>
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
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="3" 
                                          placeholder="Please advise your request, arrival time, flight details, food preferences, airline membership number..."></textarea>
                            </div>
                            
                            <div class="button-group">
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button type="submit" name="confirm_booking" class="btn btn-primary">
                                    <i class="fas fa-lock"></i> Pay Now
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
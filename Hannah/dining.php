<?php
// dining.php
// Include header - this also starts session and loads config
include '../Shared/header.php';

// Set page-specific CSS
$pageCSS = 'css/dining.css';

// Define restaurant options
$restaurants = [
    'royale' => 'Royale Chinese Restaurant',
    'palette' => 'The Palette Cafe',
    'lobby' => 'Lobby Lounge'
];

// Define available time slots (30 min intervals)
$timeSlots = [];
$start = strtotime('12:00');
$end = strtotime('22:00');
while ($start <= $end) {
    $timeSlots[] = date('H:i:s', $start);
    $start = strtotime('+30 minutes', $start);
}

// Handle form submission
$successMsg = '';
$errorMsg = '';
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_table'])) {
    // Validate and sanitize inputs
    $restaurant = cleanInput($_POST['restaurant'] ?? '');
    $reservation_date = cleanInput($_POST['reservation_date'] ?? '');
    $reservation_time = cleanInput($_POST['reservation_time'] ?? '');
    $guests = intval($_POST['guests'] ?? 0);
    $first_name = cleanInput($_POST['first_name'] ?? '');
    $last_name = cleanInput($_POST['last_name'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $special_requests = cleanInput($_POST['special_requests'] ?? '');
    $agree = isset($_POST['agree_terms']);

    // Store for sticky form
    $formData = [
        'restaurant' => $restaurant,
        'reservation_date' => $reservation_date,
        'reservation_time' => $reservation_time,
        'guests' => $guests,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'phone' => $phone,
        'email' => $email,
        'special_requests' => $special_requests
    ];

    // Validation
    $errors = [];
    if (!array_key_exists($restaurant, $restaurants)) {
        $errors[] = "Please select a valid restaurant.";
    }
    if (empty($reservation_date)) {
        $errors[] = "Please select a reservation date.";
    } else {
        $today = date('Y-m-d');
        if ($reservation_date < $today) {
            $errors[] = "Reservation date cannot be in the past.";
        }
    }
    if (empty($reservation_time)) {
        $errors[] = "Please select a reservation time.";
    }
    if ($guests < 1 || $guests > 20) {
        $errors[] = "Number of guests must be between 1 and 20.";
    }
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!$agree) {
        $errors[] = "You must agree to the Terms of Use & Privacy Policy.";
    }

    if (empty($errors)) {
        // Generate unique reservation code
        $reservation_code = 'DINE-' . strtoupper(uniqid());
        
        // Get user_id if logged in
        $user_id = null;
        if ($is_logged_in && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        // Insert into database using prepared statement
        $sql = "INSERT INTO dining_reservations (user_id, restaurant_name, reservation_date, reservation_time, guests, first_name, last_name, phone, email, special_requests, status, reservation_code, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssissssss", 
            $user_id, 
            $restaurants[$restaurant], 
            $reservation_date, 
            $reservation_time, 
            $guests, 
            $first_name, 
            $last_name, 
            $phone, 
            $email, 
            $special_requests, 
            $reservation_code
        );
        
        if ($stmt->execute()) {
            $reservation_id = $stmt->insert_id;
            
            // Send email confirmation
            $emailSent = sendReservationEmail($email, $first_name, $last_name, [
                'code' => $reservation_code,
                'restaurant' => $restaurants[$restaurant],
                'date' => date('l, F j, Y', strtotime($reservation_date)),
                'time' => date('g:i A', strtotime($reservation_time)),
                'guests' => $guests,
                'special_requests' => $special_requests ?: 'None'
            ]);
            
            if ($emailSent) {
                $successMsg = "✓ Reservation confirmed! A confirmation email has been sent to $email. Your reservation code: <strong>$reservation_code</strong>";
            } else {
                $successMsg = "✓ Reservation confirmed! (Email could not be sent, but your reservation is saved. Code: $reservation_code)";
            }
            
            // Clear form data on success
            $formData = [];
            
            // Optionally reset guests
            $_POST = [];
        } else {
            $errorMsg = "Database error: Unable to complete reservation. Please try again.";
        }
        $stmt->close();
    } else {
        $errorMsg = implode("<br>", $errors);
    }
}

// Function to send email confirmation
function sendReservationEmail($to, $firstName, $lastName, $details) {
    $subject = "Dining Reservation Confirmation - Grand Hotel";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; line-height: 1.6; color: #1A1A1A; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #E5E5E5; border-radius: 16px; }
            .header { text-align: center; border-bottom: 2px solid #C5A059; padding-bottom: 15px; margin-bottom: 20px; }
            .header h2 { color: #C5A059; font-family: 'Playfair Display', serif; margin: 0; }
            .content { padding: 10px 0; }
            .details { background: #F8F8F8; padding: 15px; border-radius: 12px; margin: 15px 0; }
            .detail-row { margin-bottom: 10px; }
            .label { font-weight: 600; color: #C5A059; }
            .footer { text-align: center; font-size: 12px; color: #666666; margin-top: 25px; padding-top: 15px; border-top: 1px solid #E5E5E5; }
            .btn { display: inline-block; background: #C5A059; color: white; padding: 10px 20px; text-decoration: none; border-radius: 40px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Grand Hotel</h2>
                <p>Dining Reservation Confirmation</p>
            </div>
            <div class='content'>
                <p>Dear <strong>" . htmlspecialchars($firstName) . " " . htmlspecialchars($lastName) . "</strong>,</p>
                <p>Thank you for choosing Grand Hotel. Your table reservation has been confirmed.</p>
                <div class='details'>
                    <div class='detail-row'><span class='label'>Reservation Code:</span> " . htmlspecialchars($details['code']) . "</div>
                    <div class='detail-row'><span class='label'>Restaurant:</span> " . htmlspecialchars($details['restaurant']) . "</div>
                    <div class='detail-row'><span class='label'>Date:</span> " . htmlspecialchars($details['date']) . "</div>
                    <div class='detail-row'><span class='label'>Time:</span> " . htmlspecialchars($details['time']) . "</div>
                    <div class='detail-row'><span class='label'>Number of Guests:</span> " . $details['guests'] . "</div>
                    <div class='detail-row'><span class='label'>Special Requests:</span> " . htmlspecialchars($details['special_requests']) . "</div>
                </div>
                <p>Please arrive 10 minutes before your reservation time. If you need to modify or cancel, please contact us at +60 6 289 6886 or reply to this email.</p>
                <p>We look forward to welcoming you!</p>
                <div style='text-align: center;'>
                    <a href='#' style='display: inline-block; background: #C5A059; color: white; padding: 10px 24px; text-decoration: none; border-radius: 40px; margin-top: 10px;'>View Your Reservation</a>
                </div>
            </div>
            <div class='footer'>
                <p>Grand Hotel | Kota Laksamana, Melaka, Malaysia | +607-666-8888 | dining@grandhotel.com</p>
                <p>&copy; " . date('Y') . " Grand Hotel. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Grand Hotel Dining <dining@grandhotel.com>" . "\r\n";
    $headers .= "Reply-To: dining@grandhotel.com" . "\r\n";
    
    // Attempt to send email
    return mail($to, $subject, $message, $headers);
}

// Pre-fill form for logged-in users
if ($is_logged_in && empty($_POST) && empty($formData)) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT name, email, phone FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($userData = $result->fetch_assoc()) {
        $nameParts = explode(' ', trim($userData['name']), 2);
        $formData['first_name'] = $nameParts[0] ?? '';
        $formData['last_name'] = $nameParts[1] ?? '';
        $formData['email'] = $userData['email'];
        $formData['phone'] = $userData['phone'] ?? '';
    }
    $stmt->close();
}

// Set default guest count if not set
if (!isset($formData['guests']) || $formData['guests'] < 1) {
    $formData['guests'] = 2;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dining Reservations | Grand Hotel</title>
    <link rel="stylesheet" href="css/dining.css">
</head>
<body>

<!-- Hero Section with specified background -->
<section class="dining-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.4)), url('https://cafe.hardrock.com/files/5282/EWP2024_HardRockCafe-2995-edited.jpg');">
    <div class="container">
        <div class="hero-content">
            <h1>Dining Reservations</h1>
            <p>Experience culinary excellence at our signature restaurants. Reserve your table for an unforgettable dining journey.</p>
        </div>
    </div>
</section>

<!-- Restaurants Showcase -->
<section class="restaurants-showcase">
    <div class="container">
        <div class="section-header">
            <h2>Our Signature Restaurants</h2>
            <p>Renowned restaurants and on-site dining experiences curated by master chefs</p>
        </div>
        <div class="restaurant-grid">
            <div class="restaurant-card">
                <div class="restaurant-img">
                    <img src="https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Royale Chinese Restaurant">
                </div>
                <div class="restaurant-info">
                    <h3>Royale Chinese Restaurant</h3>
                    <p>Authentic Szechuan and Nyonya cuisine. Chef Sum's signature spicy dishes and Chef Felix's legendary Assam Pedas Fish.</p>
                    <div class="restaurant-meta">
                        <span><i class="fas fa-clock"></i> 11:00 AM - 10:30 PM</span>
                        <span><i class="fas fa-utensils"></i> Chinese • Nyonya</span>
                    </div>
                </div>
            </div>
            <div class="restaurant-card">
                <div class="restaurant-img">
                    <img src="https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg?auto=compress&cs=tinysrgb&w=800" alt="The Palette Cafe">
                </div>
                <div class="restaurant-info">
                    <h3>The Palette Cafe</h3>
                    <p>International buffet with live stations. Freshly baked breads, pastries, and signature slow-roasted specialties.</p>
                    <div class="restaurant-meta">
                        <span><i class="fas fa-clock"></i> 6:00 AM - 11:00 PM</span>
                        <span><i class="fas fa-utensils"></i> International • Buffet</span>
                    </div>
                </div>
            </div>
            <div class="restaurant-card">
                <div class="restaurant-img">
                    <img src="https://images.pexels.com/photos/1183434/pexels-photo-1183434.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Lobby Lounge">
                </div>
                <div class="restaurant-info">
                    <h3>Lobby Lounge</h3>
                    <p>Unwind with sports TV, premium spirits, and crafted cocktails. Perfect for evening relaxation and light bites.</p>
                    <div class="restaurant-meta">
                        <span><i class="fas fa-clock"></i> 3:00 PM - 12:00 AM</span>
                        <span><i class="fas fa-utensils"></i> Bar • Light Fare</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reservation Form Section - Oval Card Design -->
<section class="reservation-section">
    <div class="container">
        <div class="reservation-oval-card">
            <div class="reservation-header">
                <h2>Make a Reservation</h2>
                <p>Fill in your details to secure your table</p>
            </div>
            
            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?php echo $successMsg; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMsg): ?>
                <div class="alert alert-error"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="reservation-form" id="reservationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-store"></i> SELECT RESTAURANT</label>
                        <select name="restaurant" required class="styled-select">
                            <option value="">Choose a restaurant</option>
                            <?php foreach ($restaurants as $key => $name): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($formData['restaurant']) && $formData['restaurant'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row grid-3">
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> RESERVATION DATE</label>
                        <input type="date" name="reservation_date" class="date-input" 
                               value="<?php echo htmlspecialchars($formData['reservation_date'] ?? ''); ?>"
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> RESERVATION TIME</label>
                        <select name="reservation_time" class="styled-select" required>
                            <option value="">Select time</option>
                            <?php foreach ($timeSlots as $time): ?>
                                <option value="<?php echo $time; ?>" <?php echo (isset($formData['reservation_time']) && $formData['reservation_time'] == $time) ? 'selected' : ''; ?>>
                                    <?php echo date('g:i A', strtotime($time)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> NUMBER OF GUESTS</label>
                        <div class="guest-stepper">
                            <button type="button" class="guest-btn" onclick="changeGuests(-1)">−</button>
                            <span class="guest-value" id="guestVal"><?php echo $formData['guests'] ?? 2; ?></span>
                            <input type="hidden" name="guests" id="guestInput" value="<?php echo $formData['guests'] ?? 2; ?>">
                            <button type="button" class="guest-btn" onclick="changeGuests(1)">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="form-divider">
                    <span>Contact Details</span>
                </div>
                
                <div class="form-row grid-2">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" 
                               placeholder="Your first name" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" 
                               placeholder="Your last name" required>
                    </div>
                </div>
                
                <div class="form-row grid-2">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" 
                               placeholder="+60 XX XXX XXXX" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-input" 
                               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" 
                               placeholder="your@email.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Special Requests (Optional)</label>
                    <textarea name="special_requests" class="form-textarea" rows="3" 
                              placeholder="Dietary requirements, allergies, celebration notes, etc."><?php echo htmlspecialchars($formData['special_requests'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree_terms" required> 
                        <span>I agree to Grand Hotel's <a href="#" class="terms-link">Terms of Use</a> & <a href="#" class="terms-link">Privacy Policy</a></span>
                    </label>
                </div>
                
                <button type="submit" name="reserve_table" class="btn-reserve">Confirm Reservation Details →</button>
            </form>
        </div>
    </div>
</section>

<!-- Private Events CTA -->
<section class="private-events">
    <div class="container">
        <div class="events-card">
            <div class="events-content">
                <i class="fas fa-glass-cheers events-icon"></i>
                <h3>Private Events & Celebrations</h3>
                <p>Want to celebrate a birthday, anniversary, or host a private event? Contact our dedicated events team to discuss the details.</p>
                <a href="../ChangJingEn/events.php" class="btn-outline">Request Information <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<script>
// Guest stepper function
function changeGuests(delta) {
    let input = document.getElementById('guestInput');
    let span = document.getElementById('guestVal');
    let val = parseInt(input.value) + delta;
    if(val >= 1 && val <= 20) {
        input.value = val;
        span.textContent = val;
    }
}

// Set min date for date picker and handle date validation
document.addEventListener('DOMContentLoaded', function() {
    let dateInput = document.querySelector('input[name="reservation_date"]');
    if(dateInput) {
        let today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
    
    // Form validation enhancement
    const form = document.getElementById('reservationForm');
    if(form) {
        form.addEventListener('submit', function(e) {
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            let checked = false;
            checkboxes.forEach(cb => {
                if(cb.checked) checked = true;
            });
            if(!checked) {
                e.preventDefault();
                alert('Please agree to the Terms of Use & Privacy Policy.');
            }
        });
    }
});
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
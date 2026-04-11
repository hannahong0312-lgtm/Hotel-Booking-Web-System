<?php
// dining.php - Public dining reservations (no login required)
include '../Shared/header.php';

use PHPMailer\PHPMailer\{PHPMailer, Exception};
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// SMTP CREDENTIALS 
define('SMTP_USERNAME', 'grandhotelreservation67@gmail.com');
define('SMTP_PASSWORD', 'pcurrscmnzgqnyky');

$restaurants = [
    'royale'   => 'Royale Restaurant',
    'palette'  => 'The Palette Cafe',
    'bar'      => 'Rooftop Bar'
];

// Operating hours (hour numbers, 24-hour format)
$operatingHours = [
    'royale'  => ['open' => 10, 'close' => 16],
    'palette' => ['open' => 10, 'close' => 16],
    'bar'     => ['open' => 18, 'close' => 26] // 26 means 2AM 
];

// Generate hourly time slots from 00:00 to 23:00 (1-hour distance)
$allHourlySlots = [];
for ($hour = 0; $hour < 24; $hour++) {
    $allHourlySlots[] = sprintf("%02d:00:00", $hour);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_table'])) {
    // Get form data
    $restaurantKey = cleanInput($_POST['restaurant'] ?? '');
    $date = cleanInput($_POST['reservation_date'] ?? '');
    $time = cleanInput($_POST['reservation_time'] ?? '');
    $guests = intval($_POST['guests'] ?? 0);
    $first_name = cleanInput($_POST['first_name'] ?? '');
    $last_name = cleanInput($_POST['last_name'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $special_requests = cleanInput($_POST['special_requests'] ?? '');
    $agree = isset($_POST['agree_terms']);

    // Validation
    if (!array_key_exists($restaurantKey, $restaurants)) {
        $errors[] = "Select a valid restaurant.";
    }
    if (empty($date)) {
        $errors[] = "Select a reservation date.";
    } elseif ($date < date('Y-m-d')) {
        $errors[] = "Date cannot be in the past.";
    }

        // Time validation
    if (empty($time)) {
        $errors[] = "Select a reservation time.";
    } else {
        $hour = (int)date('H', strtotime($time));
        if ($restaurantKey == 'bar') {
            if (!($hour >= 18 || $hour < 2)) {
                $errors[] = "Bar operates from 6:00 PM to 2:00 AM.";
            }
        } else {
            if ($hour < 10 || $hour >= 16) {
                $errors[] = "Restaurant operates from 10:00 AM to 4:00 PM.";
            }
        }
    }
  
    if ($guests < 1 || $guests > 50) $errors[] = "Guests must be 1-50.";
    if (empty($first_name)) $errors[] = "First name required.";
    if (empty($last_name)) $errors[] = "Last name required.";
    if (empty($phone)) $errors[] = "Phone number required.";
    if (empty($email)) $errors[] = "Email required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (!$agree) $errors[] = "You must agree to the Terms.";


    if (empty($errors)) {
        $reservation_code = 'DINE-' . strtoupper(uniqid());
        $restaurantName = $restaurants[$restaurantKey];

        // Insert into database
        $sql = "INSERT INTO dining (name, date, time, guests, first_name, last_name, phone, email, special_requests, status, code, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssissssss", 
            $restaurantName, $date, $time, $guests, $first_name, $last_name, $phone, $email, $special_requests, $reservation_code
        );

            if ($stmt->execute()) {
                
                // Send email (optional)
                $emailDetails = [
                    'code' => $reservation_code,
                    'name' => $restaurantName,
                    'date' => date('j F Y, l', strtotime($date)),
                    'time' => date('g:i A', strtotime($time)),
                    'guests' => $guests,
                    'special_requests' => $special_requests ?: 'None'
                ];
                sendReservationEmail($email, $first_name, $last_name, $emailDetails);
                
                // Redirect to confirmation page
                $query = http_build_query([
                    'code' => $reservation_code,
                    'restaurant' => $restaurantName,
                    'date' => $date,
                    'time' => $time,
                    'guests' => $guests,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]);
                header("Location: dining_confirm.php?$query");
                exit;

            } else {
                // Check if the execution failed (e.g., data too long for a column)
                $errors[] = "Database Execution Error: " . $stmt->error;
            }
            $stmt->close();
        }
}

function sendReservationEmail($to, $firstName, $lastName, $details) {
    // Create a new PHPMailer instance inside the function
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // SMTP Server Settings 
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Assuming you use Gmail
        $mail->SMTPAuth   = true;
        // Use the constants you defined at the top of dining.php
        $mail->Username   = SMTP_USERNAME; 
        $mail->Password   = SMTP_PASSWORD; 
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Sender & Recipient ---
        $mail->setFrom(SMTP_USERNAME, 'Grand Hotel Reservations');
        $mail->addAddress($to, $firstName . ' ' . $lastName);

        // --- Email Content ---
        $mail->isHTML(true);
        $mail->Subject = 'Dining Reservation Confirmation - Grand Hotel';
        
        $message = 
        "<html>
            <head>
                <style>
                body{font-family:Arial;}
                .container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #E5E5E5;border-radius:16px;}
                .header{text-align:center;border-bottom:2px solid #C5A059;}
                .header h2{color:#C5A059;}
                .details{background:#F8F8F8;padding:15px;border-radius:12px;margin:15px 0;}
                .label{font-weight:600;}
                </style>
            </head>
            <body>
            <div class='container'>
                <div class='header'><h2>Grand Hotel</h2><p>Dining Reservation Confirmation</p></div>
                    <p>Dear <strong>{$firstName} {$lastName}</strong>,</p>
                    <p>Your table has been reserved.</p>
                <div class='details'>
                    <p><span class='label'>Code:</span> {$details['code']}</p>
                    <p><span class='label'>Restaurant:</span> {$details['name']}</p>
                    <p><span class='label'>Date:</span> {$details['date']}</p>
                    <p><span class='label'>Time:</span> {$details['time']}</p>
                    <p><span class='label'>Guests:</span> {$details['guests']}</p>
                    <p><span class='label'>Special Requests:</span> {$details['special_requests']}</p>
                </div>
                    <p>Please arrive 10 minutes before your reservation time.</p>
                    <p>Thank you for choosing <strong>{$details['name']}</strong> @ Grand Hotel !</p>
                    <p>Keep contacting us for any changes +60 6 289 6886</p>
                </div>
            </body>
        </html>";

        $mail->Body = $message;
        $mail->send();
        return true;
        
        } catch (Exception $e) {
           echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
           return false;
        }
}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dining Reservations | Grand Hotel</title>
    <link rel="stylesheet" href="css/dining.css">
    <link rel="stylesheet" href="../Shared/main.css">
</head>
<body>

<?php if (!empty($errors)): ?>
    <div class="error-box">
        <?php foreach ($errors as $err): ?>
            <p><?php echo htmlspecialchars($err); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Hero section -->
<section class="dining-hero">
    <div class="container">
        <h1>Dining Reservations</h1>
        <p>Experience culinary excellence at our signature restaurants. Reserve your table for an unforgettable dining journey.</p>
        <div class="hero-buttons">
            <a href="#section-header" class="btn-menu">Our Special Menu</a>
            <a href="#reservation-form" class="btn-book">Make a Reservation</a>
        </div>
    </div>
</section>

<!-- Restaurants Showcase -->
<section class="restaurants-showcase" id="section-header">
    <div class="section-header"><h2>Our Signature Restaurants</h2><p>Renowned restaurants and on-site dining experiences</p></div>
    <div class="restaurant-grid">
        <a href="royale.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="restaurant-card"><div class="restaurant-img"><img src="img/nyonyadining.jpg" alt="Nyonya"></div><div class="restaurant-info"><h3>Royale Restaurant</h3><p>Authentic Szechuan and Nyonya cuisine.</p><div class="restaurant-meta"><span><i class="fas fa-clock"></i> 10:00 AM - 4:00 PM</span><span><i class="fas fa-utensils"></i> Nyonya • Cuisine</span></div></div></div></a>
        <a href="cafe.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="restaurant-card"><div class="restaurant-img"><img src="img/palettecafe.jpeg" alt="Cafe"></div><div class="restaurant-info"><h3>The Palette Cafe</h3><p>Western buffet with live stations.</p><div class="restaurant-meta"><span><i class="fas fa-clock"></i> 10:00 AM - 4:00 PM</span><span><i class="fas fa-utensils"></i> Western • Buffet</span></div></div></div></a>
        <a href="bar.php" style="text-decoration: none; color: inherit; display: block;">
        <div class="restaurant-card"><div class="restaurant-img"><img src="img/hotelbar.jpg" alt="Bar"></div><div class="restaurant-info"><h3>Rooftop Bar</h3><p>Sky-high cocktails with city views.</p><div class="restaurant-meta"><span><i class="fas fa-clock"></i> 6:00 PM - 2:00 AM</span><span><i class="fas fa-utensils"></i> Night Bar • Light Fare</span></div></div></div></a>
    </div>
</section>

<!-- Reservation Form -->
<section class="reservation-section" id="reservation-form">
    <div class="reservation-oval-card">
        <div class="reservation-header"><h2>Make a Reservation</h2><p>Fill in your details to secure your table</p></div>
        <form method="POST" class="reservation-form">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-store"></i> SELECT RESTAURANT</label>
                    <select name="restaurant" id="restaurantSelect" class="styled-select" required>
                        <option value="">Choose a restaurant</option>
                        <?php foreach ($restaurants as $key => $restaurantName): ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($_POST['restaurant']) && $_POST['restaurant'] == $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($restaurantName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row grid-3">
                <div class="form-group"><label><i class="fas fa-calendar-alt"></i> RESERVATION DATE</label><input type="date" name="reservation_date" class="date-input" value="<?php echo htmlspecialchars($_POST['reservation_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>" required></div>
                <div class="form-group"><label><i class="fas fa-clock"></i> RESERVATION TIME</label><select name="reservation_time" id="timeSelect" class="styled-select" required><option value="">Select time</option></select></div>
                <div class="form-group"><label><i class="fas fa-users"></i> NUMBER OF GUESTS</label><div class="guest-stepper"><button type="button" class="guest-btn" onclick="changeGuests(-1)">−</button><span class="guest-value" id="guestVal"><?php echo $_POST['guests'] ?? 2; ?></span><input type="hidden" name="guests" id="guestInput" value="<?php echo $_POST['guests'] ?? 2; ?>"><button type="button" class="guest-btn" onclick="changeGuests(1)">+</button></div></div>
            </div>
            <div class="form-divider"><span>Contact Details</span></div>
            <div class="form-row grid-2"><div class="form-group"><label>First Name</label><input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" placeholder="First name" required></div><div class="form-group"><label>Last Name</label><input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" placeholder="Last name" required></div></div>
            <div class="form-row grid-2"><div class="form-group"><label>Phone Number</label><input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="+60 XX XXX XXXX" required></div><div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="your@email.com" required></div></div>
            <div class="form-group"><label>Special Requests (Optional)</label><textarea name="special_requests" class="form-textarea" rows="3" placeholder="Any special requests?"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea></div>
            <div class="form-group checkbox-group"><label class="checkbox-label"><input type="checkbox" name="agree_terms" required> <span>I agree to Grand Hotel's Terms of Use and Privacy Policy</span></label></div>
            <button type="submit" name="reserve_table" class="btn-reserve">Confirm Reserve →</button>
        </form>
    </div>
</section>

<!-- Private Events CTA -->
<section class="private-events">
    <div class="events-card">
        <i class="fas fa-glass-cheers events-icon"></i>
        <h3>Private Events & Celebrations</h3>
        <p>Want to celebrate a birthday, anniversary, or host a private event? Contact our dedicated events team to discuss the details.</p>
        <a href="../ChangJingEn/events.php" class="btn-outline">Request Information <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

<script>
// Hourly slots array (00:00, 01:00, ..., 23:00)
const hourlySlots = <?php echo json_encode($allHourlySlots); ?>;

// Operating hours (same as PHP)
const hoursConfig = {
    royale:  { open: 10, close: 16 },
    palette: { open: 10, close: 16 },
    bar:     { open: 18, close: 26 }  // 26 means 2 AM next day
};

function formatHourlyTime(timeStr) {
    // timeStr format: "17:00:00"
    let hour = parseInt(timeStr.split(':')[0]);
    let ampm = hour >= 12 ? 'PM' : 'AM';
    let hour12 = hour % 12 || 12;
    return hour12 + ' ' + ampm;   // e.g., "5 PM", "10 AM"
}

function updateTimeOptions() {
    let restaurant = document.getElementById('restaurantSelect').value;
    let timeSelect = document.getElementById('timeSelect');
    if (!restaurant || !hoursConfig[restaurant]) {
        timeSelect.innerHTML = '<option value="">Select a restaurant first</option>';
        return;
    }
    let { open, close } = hoursConfig[restaurant];
    let options = '<option value="">Select time</option>';
    
    for (let slot of hourlySlots) {
        let hour = parseInt(slot.split(':')[0]);
        let valid = false;
        if (restaurant === 'bar') {
            if ((hour >= 18 && hour <= 23) || (hour >= 0 && hour < 2)) {
                valid = true;
            }
        } else {
            if (hour >= open && hour < close) {
                valid = true;
            }
        }
        if (valid) {
            let display = formatHourlyTime(slot);
            options += `<option value="${slot}">${display}</option>`;
        }
    }
    timeSelect.innerHTML = options;
}

function changeGuests(delta) {
    let input = document.getElementById('guestInput');
    let span = document.getElementById('guestVal');
    let val = parseInt(input.value) + delta;
    if(val >= 1 && val <= 50) { input.value = val; span.textContent = val; }
}

document.addEventListener('DOMContentLoaded', function() {
    let restaurantSelect = document.getElementById('restaurantSelect');
    restaurantSelect.addEventListener('change', updateTimeOptions);
    if (restaurantSelect.value) updateTimeOptions();
    
    let oldTime = <?php echo json_encode($_POST['reservation_time'] ?? ''); ?>;
    if (oldTime) {
        let timeSelect = document.getElementById('timeSelect');
        for(let opt of timeSelect.options) {
            if(opt.value === oldTime) opt.selected = true;
        }
    }
});
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
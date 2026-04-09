<?php
// dining.php
include '../Shared/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/PHPMailer-master/src/Exception.php';

// ========== EDIT THESE ==========
define('SMTP_USERNAME', 'your-email@gmail.com');   // Your Gmail
define('SMTP_PASSWORD', 'your-app-password');      // 16-char App Password
// ================================

$restaurants = [
    'royale' => 'Royale Chinese Restaurant',
    'palette' => 'The Palette Cafe',
    'lobby' => 'Lobby Lounge'
];

$timeSlots = [];
$start = strtotime('12:00');
$end = strtotime('22:00');
while ($start <= $end) {
    $timeSlots[] = date('H:i:s', $start);
    $start = strtotime('+30 minutes', $start);
}

$toastMessage = '';
$toastType = ''; // 'success' or 'error'
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve_table'])) {
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

    $formData = compact('restaurant', 'reservation_date', 'reservation_time', 'guests', 'first_name', 'last_name', 'phone', 'email', 'special_requests');

    $errors = [];
    if (!array_key_exists($restaurant, $restaurants)) $errors[] = "Select a valid restaurant.";
    if (empty($reservation_date)) $errors[] = "Select a reservation date.";
    elseif ($reservation_date < date('Y-m-d')) $errors[] = "Date cannot be in the past.";
    if (empty($reservation_time)) $errors[] = "Select a reservation time.";
    if ($guests < 1 || $guests > 20) $errors[] = "Guests must be 1-20.";
    if (empty($first_name)) $errors[] = "First name required.";
    if (empty($last_name)) $errors[] = "Last name required.";
    if (empty($phone)) $errors[] = "Phone number required.";
    if (empty($email)) $errors[] = "Email required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email.";
    if (!$agree) $errors[] = "You must agree to the Terms.";

    if (empty($errors)) {
        $reservation_code = 'DINE-' . strtoupper(uniqid());
        $user_id = $is_logged_in ? $_SESSION['user_id'] : null;

        $sql = "INSERT INTO dining (user_id, restaurant_name, reservation_date, reservation_time, guests, first_name, last_name, phone, email, special_requests, status, reservation_code, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssissssss", $user_id, $restaurants[$restaurant], $reservation_date, $reservation_time, $guests, $first_name, $last_name, $phone, $email, $special_requests, $reservation_code);
        
        if ($stmt->execute()) {
            $emailDetails = [
                'code' => $reservation_code,
                'restaurant' => $restaurants[$restaurant],
                'date' => date('l, F j, Y', strtotime($reservation_date)),
                'time' => date('g:i A', strtotime($reservation_time)),
                'guests' => $guests,
                'special_requests' => $special_requests ?: 'None'
            ];
            $emailSent = sendReservationEmail($email, $first_name, $last_name, $emailDetails);
            if ($emailSent) {
                $toastMessage = "✓ Reservation confirmed! A confirmation email has been sent to $email. Code: $reservation_code";
                $toastType = 'success';
            } else {
                $toastMessage = "✓ Reservation saved! (Email could not be sent. Code: $reservation_code)";
                $toastType = 'success';
            }
            $formData = [];
            $_POST = [];
        } else {
            $toastMessage = "Database error: Unable to save reservation.";
            $toastType = 'error';
        }
        $stmt->close();
    } else {
        $toastMessage = implode("<br>", $errors);
        $toastType = 'error';
    }
}

function sendReservationEmail($to, $firstName, $lastName, $details) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom(SMTP_USERNAME, 'Grand Hotel Dining');
        $mail->addAddress($to, $firstName . ' ' . $lastName);
        $mail->isHTML(true);
        $mail->Subject = 'Dining Reservation Confirmation - Grand Hotel';
        $mail->Body = "
        <html><head><style>
            body{font-family:Arial;}.container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #E5E5E5;border-radius:16px;}
            .header{text-align:center;border-bottom:2px solid #C5A059;}.header h2{color:#C5A059;}.details{background:#F8F8F8;padding:15px;border-radius:12px;margin:15px 0;}
            .label{font-weight:600;color:#C5A059;}
        </style></head>
        <body>
        <div class='container'>
            <div class='header'><h2>Grand Hotel</h2><p>Dining Reservation Confirmation</p></div>
            <p>Dear <strong>{$firstName} {$lastName}</strong>,</p>
            <p>Your table has been reserved.</p>
            <div class='details'>
                <p><span class='label'>Code:</span> {$details['code']}</p>
                <p><span class='label'>Restaurant:</span> {$details['restaurant']}</p>
                <p><span class='label'>Date:</span> {$details['date']}</p>
                <p><span class='label'>Time:</span> {$details['time']}</p>
                <p><span class='label'>Guests:</span> {$details['guests']}</p>
                <p><span class='label'>Special Requests:</span> {$details['special_requests']}</p>
            </div>
            <p>Please arrive 10 minutes before your reservation time.</p>
            <p>We look forward to welcoming you!</p>
        </div>
        </body></html>";
        $mail->send();
        return true;
    } catch (Exception $e) {
        $log = __DIR__ . '/mail_error.log';
        file_put_contents($log, date('Y-m-d H:i:s') . " - " . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
        return false;
    }
}

// Pre-fill for logged-in users
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
if (!isset($formData['guests']) || $formData['guests'] < 1) $formData['guests'] = 2;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dining Reservations | Grand Hotel</title>
    <link rel="stylesheet" href="css/dining.css">
</head>
<body>

<?php if ($toastMessage): ?>
<div id="toast" class="toast-notification toast-<?php echo $toastType; ?>">
    <span class="toast-close" onclick="this.parentElement.style.display='none';">&times;</span>
    <?php echo $toastMessage; ?>
</div>
<script>
    setTimeout(function() {
        let toast = document.getElementById('toast');
        if(toast) toast.style.opacity = '0';
        setTimeout(() => { if(toast) toast.style.display = 'none'; }, 300);
    }, 5000);
</script>
<?php endif; ?>

<!-- Hero section with two buttons -->
<section class="dining-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.4)), url('https://cafe.hardrock.com/files/5282/EWP2024_HardRockCafe-2995-edited.jpg');">
    <div class="container">
        <div class="hero-content">
            <h1>Dining Reservations</h1>
            <p>Experience culinary excellence at our signature restaurants. Reserve your table for an unforgettable dining journey.</p>
            <div class="hero-buttons">
                <a href="#" class="btn-menu">View Special Menu</a>
                <a href="#reservation-form" class="btn-book">Book Reservation</a>
            </div>
        </div>
    </div>
</section>

<!-- Restaurants Showcase -->
<section class="restaurants-showcase">
    <div class="container">
        <div class="section-header"><h2>Our Signature Restaurants</h2><p>Renowned restaurants and on-site dining experiences</p></div>
        <div class="restaurant-grid">
            <div class="restaurant-card"><div class="restaurant-img"><img src="https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Royale"></div><div class="restaurant-info"><h3>Royale Chinese Restaurant</h3><p>Authentic Szechuan and Nyonya cuisine.</p><div class="restaurant-meta"><span><i class="fas fa-clock"></i> 11:00 AM - 10:30 PM</span><span><i class="fas fa-utensils"></i> Chinese • Nyonya</span></div></div></div>
            <div class="restaurant-card"><div class="restaurant-img"><img src="https://images.pexels.com/photos/67468/pexels-photo-67468.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Palette"></div><div class="restaurant-info"><h3>The Palette Cafe</h3><p>International buffet with live stations.</p><div class="restaurant-meta"><span><i class="fas fa-clock"></i> 9:00 AM - 5:00 PM</span><span><i class="fas fa-utensils"></i> International • Buffet</span></div></div></div>
            <div class="restaurant-card"><div class="restaurant-img"><img src="https://images.pexels.com/photos/1183434/pexels-photo-1183434.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Lobby"></div><div class="restaurant-info"><h3>Lobby Lounge</h3><p>Unwind with sports TV and crafted cocktails.</p><div class="restaurant-meta"><span><i class="fas fa-clock"></i> 3:00 PM - 12:00 AM</span><span><i class="fas fa-utensils"></i> Bar • Light Fare</span></div></div></div>
        </div>
    </div>
</section>

<!-- Reservation Form (added id="reservation-form" for smooth scroll) -->
<section class="reservation-section" id="reservation-form">
    <div class="container">
        <div class="reservation-oval-card">
            <div class="reservation-header"><h2>Make a Reservation</h2><p>Fill in your details to secure your table</p></div>
            <form method="POST" class="reservation-form" id="reservationForm">
                <div class="form-row"><div class="form-group"><label><i class="fas fa-store"></i> SELECT RESTAURANT</label><select name="restaurant" class="styled-select" required><option value="">Choose a restaurant</option><?php foreach ($restaurants as $key => $name): ?><option value="<?php echo $key; ?>" <?php echo (isset($formData['restaurant']) && $formData['restaurant'] == $key) ? 'selected' : ''; ?>><?php echo htmlspecialchars($name); ?></option><?php endforeach; ?></select></div></div>
                <div class="form-row grid-3">
                    <div class="form-group"><label><i class="fas fa-calendar-alt"></i> RESERVATION DATE</label><input type="date" name="reservation_date" class="date-input" value="<?php echo htmlspecialchars($formData['reservation_date'] ?? ''); ?>" min="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="form-group"><label><i class="fas fa-clock"></i> RESERVATION TIME</label><select name="reservation_time" class="styled-select" required><option value="">Select time</option><?php foreach ($timeSlots as $time): ?><option value="<?php echo $time; ?>" <?php echo (isset($formData['reservation_time']) && $formData['reservation_time'] == $time) ? 'selected' : ''; ?>><?php echo date('g:i A', strtotime($time)); ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label><i class="fas fa-users"></i> NUMBER OF GUESTS</label><div class="guest-stepper"><button type="button" class="guest-btn" onclick="changeGuests(-1)">−</button><span class="guest-value" id="guestVal"><?php echo $formData['guests'] ?? 2; ?></span><input type="hidden" name="guests" id="guestInput" value="<?php echo $formData['guests'] ?? 2; ?>"><button type="button" class="guest-btn" onclick="changeGuests(1)">+</button></div></div>
                </div>
                <div class="form-divider"><span>Contact Details</span></div>
                <div class="form-row grid-2"><div class="form-group"><label>First Name</label><input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" placeholder="Your first name" required></div><div class="form-group"><label>Last Name</label><input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" placeholder="Your last name" required></div></div>
                <div class="form-row grid-2"><div class="form-group"><label>Phone Number</label><input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" placeholder="+60 XX XXX XXXX" required></div><div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" placeholder="your@email.com" required></div></div>
                <div class="form-group"><label>Special Requests (Optional)</label><textarea name="special_requests" class="form-textarea" rows="3" placeholder="Dietary requirements, allergies, celebration notes, etc."><?php echo htmlspecialchars($formData['special_requests'] ?? ''); ?></textarea></div>
                <div class="form-group checkbox-group"><label class="checkbox-label"><input type="checkbox" name="agree_terms" required> <span>I agree to Grand Hotel's <a href="#" class="terms-link">Terms of Use</a> & <a href="#" class="terms-link">Privacy Policy</a></span></label></div>
                <button type="submit" name="reserve_table" class="btn-reserve">Confirm →</button>
            </form>
        </div>
    </div>
</section>

<!-- Private Events CTA -->
<section class="private-events"><div class="container"><div class="events-card"><div class="events-content"><i class="fas fa-glass-cheers events-icon"></i><h3>Private Events & Celebrations</h3><p>Want to celebrate a birthday, anniversary, or host a private event? Contact our dedicated events team to discuss the details.</p><a href="../ChangJingEn/events.php" class="btn-outline">Request Information <i class="fas fa-arrow-right"></i></a></div></div></div></section>

<script>
function changeGuests(delta) {
    let input = document.getElementById('guestInput');
    let span = document.getElementById('guestVal');
    let val = parseInt(input.value) + delta;
    if(val >= 1 && val <= 20) { input.value = val; span.textContent = val; }
}
document.addEventListener('DOMContentLoaded', function() {
    let dateInput = document.querySelector('input[name="reservation_date"]');
    if(dateInput) dateInput.min = new Date().toISOString().split('T')[0];
    document.getElementById('reservationForm').addEventListener('submit', function(e) {
        let checked = false;
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => { if(cb.checked) checked = true; });
        if(!checked) { e.preventDefault(); alert('Please agree to the Terms of Use & Privacy Policy.'); }
    });
});
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
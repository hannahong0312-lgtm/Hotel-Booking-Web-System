<?php
// dining_confirm.php - Reservation confirmation page
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Confirmed | Grand Hotel</title>
    <link rel="stylesheet" href="css/dining.css">
    <link rel="stylesheet" href="css/dining_confirm.css">
</head>
<body>

<?php
$code = $_GET['code'] ?? '';
$restaurant = $_GET['restaurant'] ?? '';
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$guests = intval($_GET['guests'] ?? 0);
$firstName = htmlspecialchars($_GET['first_name'] ?? 'Guest');
$lastName = htmlspecialchars($_GET['last_name'] ?? '');

if (!$code) {
    header("Location: dining.php");
    exit;
}

$formattedDate = date('l, F j, Y', strtotime($date));
$formattedTime = date('g:i A', strtotime($time));
?>

<div class="confirm-container">
    <div class="confirm-card">
        <div class="confirm-header">
            <h1>🎉 Reservation Confirmed!</h1>
            <p>We've sent a confirmation email to your inbox.</p>
        </div>
        
        <div class="confirm-details">
            <div class="detail-row"><div class="detail-label">Reservation Code</div><div class="detail-value"><?php echo $code; ?></div></div>
            <div class="detail-row"><div class="detail-label">Restaurant</div><div class="detail-value"><?php echo htmlspecialchars($restaurant); ?></div></div>
            <div class="detail-row"><div class="detail-label">Date</div><div class="detail-value"><?php echo $formattedDate; ?></div></div>
            <div class="detail-row"><div class="detail-label">Time</div><div class="detail-value"><?php echo $formattedTime; ?></div></div>
            <div class="detail-row"><div class="detail-label">Guests</div><div class="detail-value"><?php echo $guests; ?> people</div></div>
            <div class="detail-row"><div class="detail-label">Name</div><div class="detail-value"><?php echo "$firstName $lastName"; ?></div></div>
        </div>

        <div class="button-group">
            <a href="http://localhost/Hotel-Booking-Web-System/Hannah/dining.php" class="btn-confirm btn-reserve-again">Make Another Reservation</a>
            <a href="https://calendar.google.com/calendar/u/0/r" class="btn-confirm btn-calendar" id="addToCalendar">Add to Calendar</a>
        </div>
        
        <div class="map-placeholder">
            <img src="img/GrandMap.png" alt="Grand Hotel Map Location">
        </div>

         <div class="brand-info">
            <h3>📍 Grand Hotel Melaka</h3>
            <p>Kota Laksamana, Melaka, Malaysia</p>
            <p style="font-size: 0.85rem; color: #777;">28, Lorong Hang Jebat, 75200 Melaka</p>
        </div>
    </div>
</div>


<?php include '../Shared/footer.php'; ?>
</body>
</html>
<?php
session_start();
include '../Shared/config.php';
include '../Shared/header.php';

$ref = isset($_GET['ref']) ? $_GET['ref'] : (isset($_SESSION['last_booking_ref']) ? $_SESSION['last_booking_ref'] : '');
if (!$ref) {
    header('Location: ../ChongEeLynn/accommodation.php');
    exit();
}

// Join book with rooms and payment to get all needed data
$sql = "SELECT b.*, r.name AS room_name, p.points_earned, p.grand_total AS payment_grand_total
        FROM book b
        JOIN rooms r ON b.room_id = r.id
        LEFT JOIN payment p ON b.payment_id = p.id
        WHERE b.booking_ref = '$ref'";
$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);
if (!$booking) {
    echo "<p>Booking not found.</p>";
    include '../Shared/footer.php';
    exit();
}

// Use grand_total from payment if available, otherwise fallback to book.grand_total
$total_paid = isset($booking['payment_grand_total']) ? $booking['payment_grand_total'] : $booking['grand_total'];
$points_earned = isset($booking['points_earned']) ? $booking['points_earned'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmed</title>
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
<main>
<div class="booking-container" style="text-align:center">
    <div class="booking-header">
        <h1><i class="fas fa-check-circle" style="color:green"></i> Booking Confirmed!</h1>
        <p>Thank you for your reservation.</p>
    </div>
    <div class="payment-card" style="max-width:600px; margin:auto">
        <div class="card-content">
            <p><strong>Booking Reference:</strong> <?= htmlspecialchars($booking['booking_ref']) ?></p>
            <p><strong>Room:</strong> <?= htmlspecialchars($booking['room_name']) ?></p>
            <p><strong>Check-in:</strong> <?= date('d F Y', strtotime($booking['check_in'])) ?></p>
            <p><strong>Check-out:</strong> <?= date('d F Y', strtotime($booking['check_out'])) ?></p>
            <p><strong>Total Paid:</strong> RM <?= number_format($total_paid, 2) ?></p>
            <p><strong>Points Earned:</strong> <?= number_format($points_earned) ?></p>
            <a href="../ChongEeLynn/accommodation.php" class="btn btn-primary">Back to Rooms</a>
        </div>
    </div>
</div>
</main>
<?php include '../Shared/footer.php'; ?>
</body>
</html>
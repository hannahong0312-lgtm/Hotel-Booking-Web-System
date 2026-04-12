<?php
session_start();
include '../Shared/config.php';
include '../Shared/header.php';

$ref = isset($_GET['ref']) ? $_GET['ref'] : (isset($_SESSION['last_booking_ref']) ? $_SESSION['last_booking_ref'] : '');
if (!$ref) {
    header('Location:../ChongEeLynn/accommodation.php');
    exit();
}

// Use the same table name as above
$table_name = 'book';   // change to 'bookings' if needed
$sql = "SELECT * FROM $table_name WHERE booking_ref = '$ref'";
$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);
if (!$booking) {
    echo "<p>Booking not found.</p>";
    include '../Shared/footer.php';
    exit();
}
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
            <p><strong>Booking Reference:</strong> <?= $booking['booking_ref'] ?></p>
            <p><strong>Room:</strong> <?= htmlspecialchars($booking['room_name']) ?></p>
            <p><strong>Check-in:</strong> <?= date('d F Y', strtotime($booking['check_in'])) ?></p>
            <p><strong>Check-out:</strong> <?= date('d F Y', strtotime($booking['check_out'])) ?></p>
            <p><strong>Total Paid:</strong> RM <?= number_format($booking['grand_total'], 2) ?></p>
            <p><strong>Tokens Earned:</strong> <?= number_format($booking['tokens_earned']) ?></p>
            <a href="../ChongEeLynn/accommodation.php" class="btn btn-primary">Back to Rooms</a>
        </div>
    </div>
</div>
</main>
<?php include '../Shared/footer.php'; ?>
</body>
</html>
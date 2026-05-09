<?php
session_start();
include '../Shared/config.php';
include '../Shared/header.php';

$ref = isset($_GET['ref']) ? $_GET['ref'] : (isset($_SESSION['last_booking_ref']) ? $_SESSION['last_booking_ref'] : '');
if (!$ref) {
    header('Location: ../ChongEeLynn/accommodation.php');
    exit();
}

// Join book with rooms and payment to get all details in one query
$sql = "SELECT b.*, p.points_earned, p.grand_total AS payment_grand_total
        FROM book b
        LEFT JOIN payment p ON b.payment_id = p.id
        WHERE b.booking_ref = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $ref);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    echo "<p>Booking not found.</p>";
    include '../Shared/footer.php';
    exit();
}

// single room details to display on confirmation page
$room_name = 'Unknown Room';
if (!empty($booking['room_id'])) {
    $room_sql = "SELECT name FROM rooms WHERE id = ? LIMIT 1";
    $room_stmt = mysqli_prepare($conn, $room_sql);
    mysqli_stmt_bind_param($room_stmt, "i", $booking['room_id']);
    mysqli_stmt_execute($room_stmt);
    $room_result = mysqli_stmt_get_result($room_stmt);
    if ($room_result && mysqli_num_rows($room_result) > 0) {
        $room = mysqli_fetch_assoc($room_result);
        $room_name = $room['name'];
    }
}

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
            <p><strong>Room:</strong> <?= htmlspecialchars($room_name) ?></p>
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
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
include '../Shared/config.php';
include '../Shared/header.php';

// Session & user validation (same as history.php)
if (!isset($_SESSION['user_id'])) {
    $result = $conn->query("SELECT id, first_name FROM users LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['first_name'] = $row['first_name'];
    } else {
        $conn->query("INSERT INTO users (first_name, last_name, email, phone, country, password, role, status) 
                      VALUES ('Hannah', 'Ong', 'hannah@example.com', '0123456789', 'Malaysia', 'demo', 'customer', 'active')");
        $newId = $conn->insert_id;
        $_SESSION['user_id'] = $newId;
        $_SESSION['first_name'] = 'Hannah';
    }
}
$userId = (int)$_SESSION['user_id'];

// Get booking ID from URL
$bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if ($bookingId <= 0) {
    header("Location: history.php?error=Invalid booking ID");
    exit;
}

// Fetch booking details - REMOVED r.price_per_night (unknown column)
$sql = "
    SELECT 
        b.id AS booking_id,
        b.booking_ref,
        b.check_in,
        b.check_out,
        b.guests,
        b.grand_total AS booking_total,
        b.status AS booking_status,
        b.created_at AS booking_created,
        r.name AS room_name,
        r.category,
        r.image,
        p.id AS payment_id,
        p.method AS payment_method,
        p.card_no,
        p.grand_total AS payment_amount,
        p.created_at AS payment_datetime,
        p.status AS payment_status,
        u.first_name,
        u.last_name,
        u.email,
        u.phone
    FROM book b
    INNER JOIN rooms r ON b.room_id = r.id
    LEFT JOIN payment p ON b.payment_id = p.id
    LEFT JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: history.php?error=Booking not found");
    exit;
}
$booking = $result->fetch_assoc();

// Calculate nights and cancellation eligibility
$nights = (new DateTime($booking['check_out']))->diff(new DateTime($booking['check_in']))->days;
function getRemainingCancelTime($checkInDate) {
    if (empty($checkInDate)) return ['can_cancel' => false];
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $checkInTimestamp = strtotime($checkInDate . ' 15:00:00');
    $deadlineTimestamp = $checkInTimestamp - 86400;
    $nowTimestamp = time();
    if ($nowTimestamp >= $deadlineTimestamp) return ['can_cancel' => false];
    $remaining = $deadlineTimestamp - $nowTimestamp;
    return [
        'days' => floor($remaining / 86400),
        'hours' => floor(($remaining % 86400) / 3600),
        'minutes' => floor(($remaining % 3600) / 60),
        'can_cancel' => true
    ];
}
$cancelInfo = getRemainingCancelTime($booking['check_in']);
$canCancel = ($booking['booking_status'] === 'confirmed' && $cancelInfo['can_cancel']);
$isCancelled = ($booking['booking_status'] === 'cancelled');

// Mask card number
$cardLast4 = '';
if (!empty($booking['card_no']) && strlen($booking['card_no']) >= 4) {
    $cardLast4 = "•••• •••• •••• " . substr($booking['card_no'], -4);
} elseif (!empty($booking['card_no'])) {
    $cardLast4 = "•••• " . $booking['card_no'];
} else {
    $cardLast4 = 'Not stored';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/history.css">
    <link rel="stylesheet" href="css/historydetails.css">
</head>
<body>

<div class="page-hero">
    <div class="container">
        <h1 class="hero-title">Booking Details</h1>
        <p class="hero-desc">Reference: <?= htmlspecialchars($booking['booking_ref']) ?></p>
    </div>
</div>

<div class="details-wrapper">
    <a href="history.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <!-- Room & Stay Card -->
    <div class="detail-card">
        <img src="../ChongEeLynn/images/<?= htmlspecialchars($booking['image'] ?? 'default-room.jpg') ?>" 
             class="room-img" onerror="this.src='../Shared/images/default-room.jpg'" alt="Room">
        <div class="detail-header"><i class="fas fa-hotel"></i> Room & Stay</div>
        <div class="detail-body">
            <h2 style="font-family: 'Playfair Display';"><?= htmlspecialchars($booking['room_name']) ?></h2>
            <div class="info-row">
                <span class="info-label">Category</span>
                <span class="info-value"><?= ucfirst($booking['category']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Guests</span>
                <span class="info-value"><?= $booking['guests'] ?> person(s)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Check-in</span>
                <span class="info-value"><?= date('d M Y', strtotime($booking['check_in'])) ?> (from 15:00)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Check-out</span>
                <span class="info-value"><?= date('d M Y', strtotime($booking['check_out'])) ?> (until 12:00)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nights</span>
                <span class="info-value"><?= $nights ?> nights</span>
            </div>
            <div class="info-row">
                <span class="info-label">Booking status</span>
                <span class="info-value">
                    <span class="status-badge <?= $booking['booking_status'] === 'confirmed' ? 'status-confirmed' : 'status-cancelled' ?>">
                        <?= ucfirst($booking['booking_status']) ?>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <!-- Payment Details Card -->
    <div class="detail-card">
        <div class="detail-header"><i class="fas fa-credit-card"></i> Payment Details</div>
        <div class="detail-body">
            <?php if ($booking['payment_id']): ?>
                <div class="info-row">
                    <span class="info-label">Transaction ID</span>
                    <span class="info-value">#<?= $booking['payment_id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment method</span>
                    <span class="info-value"><?= htmlspecialchars($booking['payment_method'] ?? 'Credit Card') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Card / Account</span>
                    <span class="info-value"><?= htmlspecialchars($cardLast4) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment date & time</span>
                    <span class="info-value"><?= date('d M Y, H:i', strtotime($booking['payment_datetime'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment status</span>
                    <span class="info-value">
                        <span class="status-badge status-confirmed"><?= ucfirst($booking['payment_status'] ?? 'Confirmed') ?></span>
                    </span>
                </div>
                <div class="info-row" style="border-bottom: none; margin-top: 0.5rem;">
                    <span class="info-label"><strong>Total paid</strong></span>
                    <span class="info-value total-price">MYR <?= number_format($booking['payment_amount'] ?? $booking['booking_total'], 2) ?></span>
                </div>
            <?php else: ?>
                <p>No payment record found. Please contact hotel reception.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Guest Info Card -->
    <div class="detail-card">
        <div class="detail-header"><i class="fas fa-user"></i> Guest Information</div>
        <div class="detail-body">
            <div class="info-row">
                <span class="info-label">Name</span>
                <span class="info-value"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?= htmlspecialchars($booking['email'] ?? '—') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone</span>
                <span class="info-value"><?= htmlspecialchars($booking['phone'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <!-- Cancellation Section (if eligible) -->
    <?php if ($canCancel): ?>
        <div class="cancel-section">
            <div class="timer-warning" style="background: white; display: inline-block; padding: 0.3rem 1rem; border-radius: 40px; margin-bottom: 1rem;">
                <i class="fas fa-hourglass-half"></i> Free cancellation for 
                <strong><?= $cancelInfo['days'] ?>d <?= $cancelInfo['hours'] ?>h <?= $cancelInfo['minutes'] ?>m</strong>
            </div>
            <button class="btn-cancel" onclick="confirmCancel(<?= $booking['booking_id'] ?>)">
                <i class="fas fa-times-circle"></i> Cancel Booking & Refund
            </button>
            <p class="policy-note" style="font-size: 0.7rem; text-align: center; margin-top: 0.8rem;">
                <i class="fas fa-shield-alt"></i> Full refund to original payment method (<?= htmlspecialchars($booking['payment_method'] ?? 'card') ?>)
            </p>
        </div>
    <?php elseif ($isCancelled): ?>
        <div class="alert alert-info" style="background: #e7f1fa;">This booking has been cancelled. Refund has been processed.</div>
    <?php else: ?>
        <div class="nonrefundable" style="background: #f1f1f1; padding: 1rem; border-radius: 20px; text-align: center;">
            <i class="fas fa-lock"></i> Non-cancellable – free cancellation period has passed.
        </div>
    <?php endif; ?>
</div>

<script>
function confirmCancel(bookingId) {
    if (!confirm('Are you sure you want to cancel this booking? You will receive a full refund.')) return;
    fetch('process_cancelbook.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ booking_id: bookingId, reason: 'Cancelled via details page' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = 'historydetails.php?booking_id=' + bookingId + '&msg=Booking+cancelled';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Something went wrong. Try again.'));
}
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
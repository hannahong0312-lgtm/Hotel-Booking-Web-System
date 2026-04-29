<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
include '../Shared/config.php';
include '../Shared/header.php';

$userId = (int)$_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');

// ========== SEARCH / FILTER ==========
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "
    SELECT 
        b.id AS booking_id,
        b.booking_ref,
        b.check_in,
        b.check_out,
        b.guests,
        b.grand_total,
        b.status AS booking_status,
        b.created_at,
        r.name AS room_name,
        r.category,
        r.image,
        p.created_at AS payment_datetime,
        p.method AS payment_method,
        p.grand_total AS paid_amount,
        p.status AS payment_status
    FROM book b
    INNER JOIN rooms r ON b.room_id = r.id
    LEFT JOIN payment p ON b.payment_id = p.id
    WHERE b.user_id = ?
";

$params = [$userId];
$types = "i";

if (!empty($search)) {
    $sql .= " AND (b.booking_ref LIKE ? OR r.name LIKE ?)";
    $wildcard = "%$search%";
    $params[] = $wildcard;
    $params[] = $wildcard;
    $types .= "ss";
}
if ($filter_status !== 'all') {
    $sql .= " AND b.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}
$sql .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ========== TIME CALCULATION FUNCTION ==========
function getRemainingCancelTime($checkInDate) {
    if (empty($checkInDate)) {
        return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'can_cancel' => false];
    }

    date_default_timezone_set('Asia/Kuala_Lumpur');

    $checkInTimestamp = strtotime($checkInDate . ' 15:00:00');
    $deadlineTimestamp = $checkInTimestamp - 86400;
    $nowTimestamp = time();

    if ($nowTimestamp >= $deadlineTimestamp) {
        return ['days' => 0, 'hours' => 0, 'minutes' => 0, 'can_cancel' => false];
    }

    $remainingSeconds = $deadlineTimestamp - $nowTimestamp;
    $days = floor($remainingSeconds / 86400);
    $remainingSeconds %= 86400;
    $hours = floor($remainingSeconds / 3600);
    $remainingSeconds %= 3600;
    $minutes = floor($remainingSeconds / 60);

    return [
        'days' => $days,
        'hours' => $hours,
        'minutes' => $minutes,
        'can_cancel' => true
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30">
    <title>Booking History | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/history.css">
</head>
<body>

<div class="page-hero">
    <div class="container">
        <div class="hero-badge"><i class="fas fa-clock"></i> 24-HOUR FREE CANCELLATION</div>
        <h1 class="hero-title">Your Booking History</h1>
        <p class="hero-desc">Manage upcoming stays & cancel up to 24 hours before check‑in (3:00 PM).</p>
    </div>

    <div class="search-filter-bar">
        <form method="GET" class="search-form">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search reference or room..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All bookings</option>
                    <option value="confirmed" <?= $filter_status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="cancelled" <?= $filter_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Apply</button>
            <?php if ($search || $filter_status !== 'all'): ?>
                <a href="history.php" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="container booking-container">
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-calendar-alt"></i></div>
            <h3>No bookings found</h3>
            <p>Explore our rooms now!</p>
            <a href="../ChongEeLynn/accommodation.php" class="btn-gold">Discover Our Suites →</a>
        </div>
    <?php else: ?>
        <div class="booking-grid">
            <?php foreach ($bookings as $b):
                $cancelInfo = getRemainingCancelTime($b['check_in']);
                $canCancel = ($b['booking_status'] === 'confirmed' && $cancelInfo['can_cancel']);
                $isCancelled = ($b['booking_status'] === 'cancelled');
            ?>
            <a href="historydetails.php?booking_id=<?= $b['booking_id'] ?>">
            <div class="booking-card <?= $isCancelled ? 'cancelled-card' : '' ?>">
                <div class="card-image" style="background-image: url('../ChongEeLynn/images/<?= htmlspecialchars($b['image'] ?? 'default-room.jpg') ?>');">
                    <?php if ($canCancel): ?>
                        <span class="cancel-badge"><i class="far fa-clock"></i> Can cancel</span>
                    <?php elseif ($isCancelled): ?>
                        <span class="cancelled-badge"><i class="fas fa-ban"></i> Cancelled</span>
                    <?php else: ?>
                        <span class="confirmed-badge"><i class="fas fa-check"></i> Confirmed</span>
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <div class="booking-ref">Ref: <?= htmlspecialchars($b['booking_ref']) ?></div>
                    <h3 class="room-title"><?= htmlspecialchars($b['room_name']) ?></h3>
                    <div class="room-meta">
                        <span><i class="fas fa-bed"></i> <?= ucfirst($b['category']) ?></span>
                        <span><i class="fas fa-users"></i> <?= $b['guests'] ?> guests</span>
                    </div>
                    <div class="date-range">
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('M d, Y', strtotime($b['check_in'])) ?> –
                        <?= date('M d, Y', strtotime($b['check_out'])) ?>
                        <span class="nights">(<?= (new DateTime($b['check_out']))->diff(new DateTime($b['check_in']))->days ?> nights)</span>
                    </div>
                    <div class="payment-details">
                        <div class="payment-row">
                            <span>Paid via <?= htmlspecialchars($b['payment_method'] ?? 'N/A') ?></span>
                            <span class="amount">MYR <?= number_format($b['paid_amount'] ?? $b['grand_total'], 2) ?></span>
                        </div>
                        <?php if ($b['payment_datetime']): ?>
                        <div class="payment-date">
                            <i class="far fa-credit-card"></i> Payment: <?= date('M d, Y H:i', strtotime($b['payment_datetime'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($canCancel): ?>
                        <div class="cancel-panel">
                            <div class="timer-warning">
                                <i class="fas fa-hourglass-half"></i>
                                Free cancellation for
                                <strong>
                                    <?php if ($cancelInfo['days'] > 0): ?><?= $cancelInfo['days'] ?>d <?php endif; ?>
                                    <?= $cancelInfo['hours'] ?>h <?= $cancelInfo['minutes'] ?>m
                                </strong>
                            </div>
                            <button 
                                class="btn-cancel" 
                                onclick="confirmCancel(<?= $b['booking_id'] ?>)">
                                <i class="fas fa-times-circle"></i> Cancel Booking & Refund
                            </button>
                            <p class="policy-note"><i class="fas fa-shield-alt"></i> Full refund to original payment method</p>
                        </div>
                    <?php elseif ($isCancelled): ?>
                        <div class="cancelled-notice">
                            <i class="fas fa-info-circle"></i> This booking has been cancelled.
                        </div>
                    <?php else: ?>
                        <div class="nonrefundable">
                            <i class="fas fa-lock"></i> Non-cancellable (beyond 24h before check-in)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmCancel(bookingId) {
    if (!confirm('⚠️ Are you sure you want to cancel this booking?')) {
        return;
    }

    fetch('process_cancelbook.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            booking_id: bookingId,
            reason: 'Cancelled by user via history page'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        alert('Something went wrong. Please try again.');
    });
}
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
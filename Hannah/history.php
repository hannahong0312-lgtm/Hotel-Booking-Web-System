<?php
include '../Shared/config.php';
include '../Shared/header.php';

// ========== SESSION & USER VALIDATION ==========
if (!isset($_SESSION['user_id'])) {
    // Auto-assign demo user if needed (for seamless demo)
    $result = $conn->query("SELECT id, first_name FROM users LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['first_name'] = $row['first_name'];
    } else {
        // Insert a demo user (since users table might be empty)
        $conn->query("INSERT INTO users (first_name, last_name, email, phone, country, password, role, status) 
                      VALUES ('Emily', 'Wong', 'emily@example.com', '0123456789', 'Malaysia', 'demo', 'customer', 'active')");
        $newId = $conn->insert_id;
        $_SESSION['user_id'] = $newId;
        $_SESSION['first_name'] = 'Emily';

        // Insert a sample booking + payment to make history non-empty
        $ref = 'BKG-' . time();
        $conn->query("INSERT INTO book (booking_ref, user_id, room_id, check_in, check_out, guests, grand_total, status, created_at) 
                      VALUES ('$ref', $newId, 1, CURDATE() + INTERVAL 10 DAY, CURDATE() + INTERVAL 12 DAY, 2, 560.00, 'confirmed', NOW())");
        $bookId = $conn->insert_id;
        $conn->query("INSERT INTO payment (book_id, user_id, method, card_no, grand_total, payment_date, status) 
                      VALUES ($bookId, $newId, 'Visa', '4242', 560.00, NOW() - INTERVAL 6 HOUR, 'confirmed')");
        $payId = $conn->insert_id;
        $conn->query("UPDATE book SET payment_id = $payId WHERE id = $bookId");
    }
}

$userId = (int)$_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['first_name'] ?? 'Guest');

// ========== HANDLE CANCELLATION (24h policy) ==========
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancelId = (int)$_POST['cancel_booking_id'];

    // Fetch booking + payment details with user ownership
    $stmt = $conn->prepare("
        SELECT b.id, b.status AS book_status, p.id AS payment_id, p.payment_date, p.status AS payment_status
        FROM book b
        INNER JOIN payment p ON b.payment_id = p.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->bind_param("ii", $cancelId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        $error = "Booking not found or access denied !.";
    } elseif ($row['book_status'] === 'cancelled') {
        $error = "⚠️ This booking is already cancelled.";
    } elseif ($row['payment_status'] === 'cancelled') {
        $error = "⚠️ Payment already refunded.";
    } else {
        $payDate = new DateTime($row['payment_date']);
        $now = new DateTime();
        $diffHours = ($now->getTimestamp() - $payDate->getTimestamp()) / 3600;
        if ($diffHours <= 24) {
            $conn->begin_transaction();
            try {
                $updBook = $conn->prepare("UPDATE book SET status = 'cancelled' WHERE id = ?");
                $updBook->bind_param("i", $cancelId);
                $updBook->execute();

                $updPay = $conn->prepare("UPDATE payment SET status = 'cancelled' WHERE id = ?");
                $updPay->bind_param("i", $row['payment_id']);
                $updPay->execute();

                $conn->commit();
                $msg =  "Booking cancelled. Full refund will be processed within 2-3 business days.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Cancellation failed. Please try again.";
            }
        } else {
            $error = "Cancellation period expired. You can only cancel within 24 hours of payment.";
        }
    }
}

// ========== SEARCH / FILTER ==========
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build base query with search conditions
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
        p.payment_date,
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
    $searchWildcard = "%$search%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
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
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

// Helper: remaining cancel hours
function getRemainingHours($paymentDate) {
    if (!$paymentDate) return 0;
    $pay = new DateTime($paymentDate);
    $now = new DateTime();
    $diff = $now->getTimestamp() - $pay->getTimestamp();
    $hours = $diff / 3600;
    return max(0, 24 - $hours);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation History | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/history.css">
</head>
<body>

    <div class="page-hero">
        <div class="container">
            <div class="hero-badge"><i class="fas fa-clock"></i> 24-HOUR FREE CANCELLATION</div>
            <h1 class="hero-title">Your Reservation History</h1>
            <p class="hero-desc">Manage upcoming stays, review past visits & cancel eligible bookings within 24 hours of payment.</p>
        </div>
        
        <!-- Search & Filter Bar -->
        <div class="search-filter-bar">
            <form method="GET" class="search-form">
                <div class="search-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search by booking reference or room name..." value="<?= htmlspecialchars($search) ?>">
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
        <!-- Alert messages -->
        <?php if ($msg): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-calendar-alt"></i></div>
                <h3>No reservations found</h3>
                <p><?= ($search || $filter_status !== 'all') ? 'Try changing your search criteria.' : 'Explore our luxurious rooms and create unforgettable memories.' ?></p>
                <a href="../ChongEeLynn/accommodation.php" class="btn-gold">Discover Our Suites →</a>
            </div>
        <?php else: ?>
            <div class="booking-grid">
                <?php foreach ($bookings as $b):
                    $remaining = getRemainingHours($b['payment_date']);
                    $canCancel = ($b['booking_status'] === 'confirmed' && $b['payment_status'] === 'confirmed' && $remaining > 0);
                    $isCancelled = ($b['booking_status'] === 'cancelled');
                ?>
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
                            <i class="fas fa-calendar-alt"></i> <?= date('M d, Y', strtotime($b['check_in'])) ?> – <?= date('M d, Y', strtotime($b['check_out'])) ?>
                            <span class="nights">(<?= (new DateTime($b['check_out']))->diff(new DateTime($b['check_in']))->days ?> nights)</span>
                        </div>
                        <div class="payment-details">
                            <div class="payment-row">
                                <span>Paid via <?= htmlspecialchars($b['payment_method'] ?? 'N/A') ?></span>
                                <span class="amount">MYR <?= number_format($b['paid_amount'] ?? $b['grand_total'], 2) ?></span>
                            </div>
                            <?php if ($b['payment_date']): ?>
                            <div class="payment-date">
                                <i class="far fa-credit-card"></i> Payment: <?= date('M d, Y H:i', strtotime($b['payment_date'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($canCancel): ?>
                            <div class="cancel-panel">
                                <div class="timer-warning">
                                    <i class="fas fa-hourglass-half"></i> 
                                    Free cancellation for <strong><?= floor($remaining) ?>h <?= round(($remaining - floor($remaining)) * 60) ?>m</strong>
                                </div>
                                <form method="POST" onsubmit="return confirm('⚠️ Are you sure? This will cancel your booking and refund the full amount.');">
                                    <input type="hidden" name="cancel_booking_id" value="<?= $b['booking_id'] ?>">
                                    <button type="submit" class="btn-cancel"><i class="fas fa-times-circle"></i> Cancel Booking & Refund</button>
                                </form>
                                <p class="policy-note"><i class="fas fa-shield-alt"></i> Full refund to original payment method</p>
                            </div>
                        <?php elseif ($isCancelled): ?>
                            <div class="cancelled-notice">
                                <i class="fas fa-info-circle"></i> This reservation has been cancelled.
                            </div>
                        <?php else: ?>
                            <div class="nonrefundable">
                                <i class="fas fa-lock"></i> Non-cancellable (beyond 24h window)
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../Shared/footer.php'; ?>
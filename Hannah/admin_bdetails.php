<?php
// admin_bdetails.php - Edit booking status, special requests, etc.
require_once '../Shared/config.php';
require_once '../ChangJingEn/admin_header.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
if (!$booking_id) {
    header('Location: admin_book.php');
    exit();
}

// Handle update
$update_success = '';
$update_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? '';
    $special_requests = trim($_POST['special_requests'] ?? '');
    $allowed_status = ['confirmed', 'cancelled', 'completed'];
    if (!in_array($new_status, $allowed_status)) {
        $update_error = 'Invalid status.';
    } else {
        $update_stmt = $conn->prepare("UPDATE book SET status = ?, special_requests = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_status, $special_requests, $booking_id);
        if ($update_stmt->execute()) {
            $update_success = 'Booking updated successfully.';
        } else {
            $update_error = 'Failed to update booking.';
        }
        $update_stmt->close();
    }
}

// Fetch booking details with user and payment info
$sql = "SELECT 
            b.*, u.first_name, u.last_name, u.email, u.phone, u.country,
            r.name AS room_name, r.price,
            p.method AS payment_method, p.grand_total AS paid_total,
            p.subtotal, p.sst_tax, p.foreigner_tax, p.service_fee,
            p.points_used, p.points_earned, p.ic_no
        FROM book b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN rooms r ON b.room_id = r.id
        LEFT JOIN payment p ON b.payment_id = p.id
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
if (!$booking) {
    echo "<div class='content-wrapper'><p>Booking not found. <a href='admin_book.php'>Go back</a></p></div>";
    include '../Shared/footer.php';
    exit();
}
$nights = (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
$total = $booking['paid_total'] ?? ($booking['grand_total'] ?? 0);
?>

<style>
    .edit-container {
        max-width: 800px;
        margin: 30px auto;
        background: var(--bg-sidebar);
        border-radius: 28px;
        padding: 32px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-md);
    }
    .edit-header {
        margin-bottom: 28px;
        border-bottom: 2px solid var(--gold);
        padding-bottom: 16px;
    }
    .edit-header h2 {
        font-family: 'Playfair Display', serif;
        color: var(--gold);
        margin: 0;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
        background: var(--bg-body);
        padding: 20px;
        border-radius: 20px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }
    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-primary);
    }
    .form-group {
        margin-bottom: 24px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-primary);
    }
    .form-group select, .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--border-light);
        border-radius: 16px;
        background: var(--bg-body);
        color: var(--text-primary);
        font-size: 0.9rem;
    }
    .form-actions {
        display: flex;
        gap: 16px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    .btn-save {
        background: var(--gold);
        color: #1e1e1e;
        border: none;
        padding: 12px 28px;
        border-radius: 40px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-cancel {
        background: transparent;
        border: 1px solid var(--border-light);
        padding: 12px 28px;
        border-radius: 40px;
        color: var(--text-secondary);
        text-decoration: none;
        display: inline-block;
    }
    .alert {
        padding: 14px 20px;
        border-radius: 20px;
        margin-bottom: 24px;
    }
    .alert-success { background: #e6f9ed; color: #0b5e42; }
    .alert-danger { background: #fee2e2; color: #991b1b; }
</style>

<div class="content-wrapper" style="margin-left:0; padding-top:20px;">
    <div class="edit-container">
        <div class="edit-header">
            <h2><i class="fas fa-edit"></i> Edit Reservation</h2>
            <p>Booking Reference: <strong><?= htmlspecialchars($booking['booking_ref']) ?></strong></p>
        </div>

        <?php if ($update_success): ?>
            <div class="alert alert-success"><?= $update_success ?></div>
        <?php elseif ($update_error): ?>
            <div class="alert alert-danger"><?= $update_error ?></div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-item"><span class="info-label">Room</span><span class="info-value"><?= htmlspecialchars($booking['room_name']) ?> (x<?= $booking['quantity'] ?>)</span></div>
            <div class="info-item"><span class="info-label">Guest</span><span class="info-value"><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span></div>
            <div class="info-item"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($booking['email']) ?></span></div>
            <div class="info-item"><span class="info-label">Phone</span><span class="info-value"><?= htmlspecialchars($booking['phone'] ?? '-') ?></span></div>
            <div class="info-item"><span class="info-label">IC/Passport</span><span class="info-value"><?= htmlspecialchars($booking['ic_no'] ?? '-') ?></span></div>
            <div class="info-item"><span class="info-label">Nationality</span><span class="info-value"><?= htmlspecialchars($booking['country']) ?></span></div>
            <div class="info-item"><span class="info-label">Check-in</span><span class="info-value"><?= date('d F Y', strtotime($booking['check_in'])) ?></span></div>
            <div class="info-item"><span class="info-label">Check-out</span><span class="info-value"><?= date('d F Y', strtotime($booking['check_out'])) ?> (<?= $nights ?> nights)</span></div>
            <div class="info-item"><span class="info-label">Total Paid</span><span class="info-value">RM <?= number_format($total, 2) ?></span></div>
            <div class="info-item"><span class="info-label">Payment Method</span><span class="info-value"><?= strtoupper($booking['payment_method'] ?? 'N/A') ?></span></div>
        </div>

        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Booking Status</label>
                <select name="status">
                    <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-comment"></i> Special Requests</label>
                <textarea name="special_requests" rows="4"><?= htmlspecialchars($booking['special_requests'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <a href="admin_book.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

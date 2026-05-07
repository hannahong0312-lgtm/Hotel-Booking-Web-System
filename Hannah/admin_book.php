<?php
// admin_book.php - Complete booking management with clean print windows
require_once '../Shared/config.php';
require_once '../ChangJingEn/admin_header.php';

// -------------------------------------------------------------------
// Handle actions: single invoice (clean HTML for print)
// -------------------------------------------------------------------
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// ------------------- SINGLE INVOICE (printable, no admin theme) -------------------
if ($action === 'single_invoice_print' && isset($_GET['booking_id'])) {
    $booking_id = (int)$_GET['booking_id'];
    $sql = "SELECT 
                b.*, u.first_name, u.last_name, u.email, u.phone, u.country,
                r.name AS room_name, r.price as room_price,
                p.method AS payment_method, p.grand_total AS paid_total, p.subtotal,
                p.sst_tax, p.foreigner_tax, p.service_fee, p.ic_no, p.points_used,
                p.points_earned, p.points_deduction_amount, p.transaction_id,
                p.created_at as payment_date
            FROM book b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN payment p ON b.payment_id = p.id
            WHERE b.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if (!$booking) { echo "<p>Booking not found.</p>"; exit; }
    
    $nights = (new DateTime($booking['check_in']))->diff(new DateTime($booking['check_out']))->days;
    $total = $booking['paid_total'] ?? ($booking['grand_total'] ?? 0);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice #<?= htmlspecialchars($booking['booking_ref']) ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', sans-serif;
                background: #f2f2f2;
                padding: 40px 20px;
                display: flex;
                justify-content: center;
            }
            .invoice-wrapper {
                max-width: 800px;
                width: 100%;
                background: white;
                border-radius: 24px;
                box-shadow: 0 20px 35px rgba(0,0,0,0.1);
                overflow: hidden;
            }
            .invoice-header {
                background: #c5a059;
                padding: 30px;
                color: white;
                text-align: center;
            }
            .invoice-header h1 { font-size: 2rem; margin-bottom: 8px; }
            .invoice-body { padding: 30px; }
            .row { display: flex; justify-content: space-between; margin-bottom: 12px; }
            .label { font-weight: 600; color: #555; }
            .total-row { font-size: 1.2rem; margin-top: 20px; padding-top: 12px; border-top: 2px solid #eee; }
            .print-btn {
                background: #2c3e66;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 40px;
                cursor: pointer;
                margin: 20px 0;
                font-size: 1rem;
            }
            @media print {
                .print-btn, .no-print { display: none; }
                body { background: white; padding: 0; }
                .invoice-wrapper { box-shadow: none; }
            }
        </style>
    </head>
    <body>
    <div class="invoice-wrapper">
        <div class="invoice-header"><h1>Grand Hotel Melaka</h1><p>Official Booking Invoice</p></div>
        <div class="invoice-body">
            <div class="row"><span class="label">Invoice No:</span><span><?= htmlspecialchars($booking['booking_ref']) ?></span></div>
            <div class="row"><span class="label">Date:</span><span><?= date('d/m/Y H:i', strtotime($booking['payment_date'] ?? $booking['created_at'])) ?></span></div>
            <div class="row"><span class="label">Guest:</span><span><?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']) ?></span></div>
            <div class="row"><span class="label">Email:</span><span><?= htmlspecialchars($booking['email']) ?></span></div>
            <div class="row"><span class="label">Phone:</span><span><?= htmlspecialchars($booking['phone'] ?? '-') ?></span></div>
            <div class="row"><span class="label">IC/Passport:</span><span><?= htmlspecialchars($booking['ic_no'] ?? '-') ?></span></div>
            <div class="row"><span class="label">Nationality:</span><span><?= htmlspecialchars($booking['country']) ?></span></div>
            <hr style="margin: 20px 0">
            <div class="row"><span class="label">Room:</span><span><?= htmlspecialchars($booking['room_name']) ?> x <?= $booking['quantity'] ?></span></div>
            <div class="row"><span class="label">Check-in:</span><span><?= date('d F Y', strtotime($booking['check_in'])) ?></span></div>
            <div class="row"><span class="label">Check-out:</span><span><?= date('d F Y', strtotime($booking['check_out'])) ?> (<?= $nights ?> nights)</span></div>
            <?php if ($booking['special_requests']): ?>
            <div class="row"><span class="label">Special Requests:</span><span><?= nl2br(htmlspecialchars($booking['special_requests'])) ?></span></div>
            <?php endif; ?>
            <hr style="margin: 20px 0">
            <div class="row"><span class="label">Subtotal:</span><span>RM <?= number_format($booking['subtotal'] ?? ($total - ($booking['sst_tax']+$booking['foreigner_tax']+$booking['service_fee'])), 2) ?></span></div>
            <div class="row"><span class="label">SST (8%):</span><span>RM <?= number_format($booking['sst_tax'] ?? 0, 2) ?></span></div>
            <div class="row"><span class="label">Tourism Tax:</span><span>RM <?= number_format($booking['foreigner_tax'] ?? 0, 2) ?></span></div>
            <div class="row"><span class="label">Service Fee (5%):</span><span>RM <?= number_format($booking['service_fee'] ?? 0, 2) ?></span></div>
            <?php if ($booking['points_deduction_amount'] > 0): ?>
            <div class="row"><span class="label">Points Discount:</span><span>- RM <?= number_format($booking['points_deduction_amount'], 2) ?></span></div>
            <?php endif; ?>
            <div class="row total-row"><span class="label"><strong>GRAND TOTAL:</strong></span><span><strong>RM <?= number_format($total, 2) ?></strong></span></div>
            <div class="row"><span class="label">Payment Method:</span><span><?= strtoupper($booking['payment_method'] ?? 'N/A') ?></span></div>
            <div class="row"><span class="label">Transaction ID:</span><span><?= $booking['transaction_id'] ?? '-' ?></span></div>
            <div class="row"><span class="label">Points Earned:</span><span><?= number_format($booking['points_earned'] ?? 0) ?></span></div>
            <div style="text-align: center; margin-top: 40px;">
                <button class="print-btn no-print" onclick="window.print(); setTimeout(() => window.close(), 500);"><i class="fas fa-print"></i> Print / Save PDF</button>
            </div>
            <div style="margin-top: 20px; font-size: 0.7rem; text-align: center; color: #aaa;">
                Grand Hotel Melaka – 88, Jalan Kota Laksamana, 75200 Melaka<br>Thank you for choosing us.
            </div>
        </div>
    </div>
    <script>
        // Auto-trigger print dialog when window loads
        window.onload = function() {
            window.print();
        };
        // After printing, close the window (optional, user can keep it)
        window.onafterprint = function() {
            window.close();
        };
    </script>
    </body>
    </html>
    <?php
    exit;
}

// ------------------- ALL INVOICES (printable, no admin theme) -------------------
if ($action === 'all_invoices_print') {
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    $search        = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    $sql = "SELECT 
                b.*, u.first_name, u.last_name, u.email, u.phone, u.country,
                r.name AS room_name,
                p.method AS payment_method, p.grand_total AS paid_total, p.subtotal,
                p.sst_tax, p.foreigner_tax, p.service_fee, p.ic_no, p.points_used,
                p.points_earned, p.points_deduction_amount, p.transaction_id,
                p.created_at as payment_date
            FROM book b
            LEFT JOIN users u ON b.user_id = u.id
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN payment p ON b.payment_id = p.id
            WHERE 1=1";
    $params = []; $types = "";
    if ($status_filter !== 'all') {
        $sql .= " AND b.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }
    if (!empty($search)) {
        $sql .= " AND (b.booking_ref LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
        $sw = "%$search%";
        $params[] = $sw; $params[] = $sw; $params[] = $sw;
        $types .= "sss";
    }
    $sql .= " ORDER BY b.created_at DESC";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $bookings = $stmt->get_result();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>All Bookings Invoice Report</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Inter', sans-serif; background: #fff; padding: 30px; }
            .invoice-page {
                max-width: 900px;
                margin: 0 auto 40px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 16px;
                padding: 30px;
                page-break-after: always;
            }
            .header { text-align: center; border-bottom: 2px solid #c5a059; padding-bottom: 15px; margin-bottom: 25px; }
            .header h1 { color: #c5a059; }
            .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
            .label { font-weight: 600; color: #555; }
            .total-row { border-top: 1px solid #ccc; margin-top: 15px; padding-top: 10px; font-weight: bold; }
            hr { margin: 20px 0; }
            .print-footer {
                text-align: center;
                margin-top: 40px;
            }
            @media print {
                body { padding: 0; margin: 0; }
                .invoice-page { margin: 0; page-break-after: always; border: none; }
                .print-footer { display: none; }
            }
        </style>
    </head>
    <body>
    <?php $count = 0; while ($b = $bookings->fetch_assoc()): $count++;
        $nights = (new DateTime($b['check_in']))->diff(new DateTime($b['check_out']))->days;
        $total = $b['paid_total'] ?? ($b['grand_total'] ?? 0);
    ?>
    <div class="invoice-page">
        <div class="header"><h1>Grand Hotel Melaka</h1><p>Booking Invoice #<?= htmlspecialchars($b['booking_ref']) ?></p></div>
        <div class="row"><span class="label">Guest:</span><span><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></span></div>
        <div class="row"><span class="label">Email:</span><span><?= htmlspecialchars($b['email']) ?></span></div>
        <div class="row"><span class="label">IC/Passport:</span><span><?= htmlspecialchars($b['ic_no'] ?? '-') ?></span></div>
        <div class="row"><span class="label">Room:</span><span><?= htmlspecialchars($b['room_name']) ?> (x<?= $b['quantity'] ?>)</span></div>
        <div class="row"><span class="label">Stay:</span><span><?= date('d M Y', strtotime($b['check_in'])) ?> – <?= date('d M Y', strtotime($b['check_out'])) ?> (<?= $nights ?> nights)</span></div>
        <hr>
        <div class="row"><span class="label">Subtotal:</span><span>RM <?= number_format($b['subtotal'] ?? ($total - ($b['sst_tax']+$b['foreigner_tax']+$b['service_fee'])), 2) ?></span></div>
        <div class="row"><span class="label">SST (8%):</span><span>RM <?= number_format($b['sst_tax'] ?? 0, 2) ?></span></div>
        <div class="row"><span class="label">Tourism Tax:</span><span>RM <?= number_format($b['foreigner_tax'] ?? 0, 2) ?></span></div>
        <div class="row"><span class="label">Service Fee:</span><span>RM <?= number_format($b['service_fee'] ?? 0, 2) ?></span></div>
        <?php if ($b['points_deduction_amount'] > 0): ?>
        <div class="row"><span class="label">Points Discount:</span><span>- RM <?= number_format($b['points_deduction_amount'], 2) ?></span></div>
        <?php endif; ?>
        <div class="row total-row"><span class="label"><strong>TOTAL PAID:</strong></span><span><strong>RM <?= number_format($total, 2) ?></strong></span></div>
        <div class="row"><span class="label">Payment:</span><span><?= strtoupper($b['payment_method'] ?? 'N/A') ?> (<?= $b['transaction_id'] ?? 'online' ?>)</span></div>
        <div style="font-size:0.7rem; margin-top: 30px; text-align:center;">Issued on <?= date('d/m/Y H:i') ?> | Grand Hotel Melaka</div>
    </div>
    <?php endwhile; ?>
    <?php if ($count == 0): ?><div style="text-align:center; padding:50px;">No bookings match filters.</div><?php endif; ?>
    <div class="print-footer">
        <button onclick="window.print(); setTimeout(() => window.close(), 500);" style="padding:12px 30px; background:#c5a059; border:none; border-radius:40px; cursor:pointer;">Print All Invoices</button>
    </div>
    <script>
        // Auto-trigger print if there are invoices
        <?php if ($count > 0): ?>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.close();
        };
        <?php endif; ?>
    </script>
    </body>
    </html>
    <?php
    exit;
}

// ------------------- DEFAULT: LIST VIEW (unchanged except invoice buttons) -------------------
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search        = isset($_GET['search']) ? trim($_GET['search']) : '';
$page          = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit         = 20;
$offset        = ($page - 1) * $limit;

// Count total
$count_sql = "SELECT COUNT(*) as total FROM book b LEFT JOIN users u ON b.user_id = u.id WHERE 1=1";
$count_params = []; $count_types = "";
if ($status_filter !== 'all') {
    $count_sql .= " AND b.status = ?";
    $count_params[] = $status_filter;
    $count_types .= "s";
}
if (!empty($search)) {
    $count_sql .= " AND (b.booking_ref LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
    $sw = "%$search%";
    $count_params[] = $sw; $count_params[] = $sw; $count_params[] = $sw;
    $count_types .= "sss";
}
$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) $count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch bookings
$sql = "SELECT 
            b.*, u.first_name, u.last_name, u.email,
            r.name AS room_name,
            p.method AS payment_method, p.grand_total AS payment_grand_total,
            p.sst_tax, p.foreigner_tax, p.service_fee, p.ic_no, p.points_used,
            p.points_earned, p.points_deduction_amount, p.transaction_id,
            p.created_at as payment_created_at
        FROM book b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN rooms r ON b.room_id = r.id
        LEFT JOIN payment p ON b.payment_id = p.id
        WHERE 1=1";
$params = []; $types = "";
if ($status_filter !== 'all') {
    $sql .= " AND b.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if (!empty($search)) {
    $sql .= " AND (b.booking_ref LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
    $sw = "%$search%";
    $params[] = $sw; $params[] = $sw; $params[] = $sw;
    $types .= "sss";
}
$sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit; $params[] = $offset;
$types .= "ii";
$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result();

// Stats
$total_all = $conn->query("SELECT COUNT(*) FROM book")->fetch_row()[0];
$confirmed_count = $conn->query("SELECT COUNT(*) FROM book WHERE status = 'confirmed'")->fetch_row()[0];
$cancelled_count = $conn->query("SELECT COUNT(*) FROM book WHERE status = 'cancelled'")->fetch_row()[0];
?>

<style>
    /* Same styling as before for stats, table, etc. */
    .stats-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }
    .stat-summary-card {
        flex: 1;
        background: linear-gradient(135deg, var(--bg-sidebar) 0%, var(--bg-body) 100%);
        border-radius: 28px;
        padding: 20px 24px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .stat-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    .stat-summary-card .stat-icon {
        position: absolute;
        right: 20px;
        bottom: 20px;
        font-size: 3rem;
        opacity: 0.15;
        color: var(--gold);
    }
    .stat-summary-card .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--gold);
        line-height: 1.2;
    }
    .stat-summary-card .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-secondary);
        margin-top: 8px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .page-header h2 {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    .filter-bar {
        background: var(--bg-sidebar);
        border-radius: 24px;
        padding: 16px 24px;
        margin-bottom: 28px;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: center;
        border: 1px solid var(--border-light);
    }
    .filter-bar input, .filter-bar select {
        padding: 10px 16px;
        border: 1px solid var(--border-light);
        border-radius: 40px;
        background: var(--bg-body);
        color: var(--text-primary);
        font-size: 0.85rem;
        min-width: 180px;
    }
    .bookings-table {
        width: 100%;
        background: var(--bg-sidebar);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        border-collapse: separate;
        border-spacing: 0;
    }
    .bookings-table th {
        text-align: left;
        padding: 18px 16px;
        background: rgba(0,0,0,0.02);
        font-weight: 700;
        font-size: 0.8rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-light);
    }
    .bookings-table td {
        padding: 16px 16px;
        border-bottom: 1px solid var(--border-light);
        color: var(--text-primary);
        font-size: 0.85rem;
        vertical-align: middle;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: capitalize;
    }
    .status-confirmed { background: #e6f9ed; color: #0b5e42; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-completed { background: #e0e7ff; color: #1e3a8a; }
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .action-btn {
        background: transparent;
        border: 1px solid var(--border-light);
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 0.7rem;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        display: inline-block;
        color: var(--text-secondary);
    }
    .action-btn.edit {
        border-color: var(--gold);
        color: var(--gold);
    }
    .action-btn.edit:hover {
        background: rgba(197,160,89,0.1);
    }
    .action-btn.invoice {
        border-color: #2c3e66;
        color: #2c3e66;
    }
    .action-btn.invoice:hover {
        background: rgba(44,62,102,0.1);
    }
    .pagination {
        margin-top: 32px;
        display: flex;
        justify-content: center;
        gap: 8px;
    }
    .pagination a {
        padding: 8px 14px;
        background: var(--bg-sidebar);
        border: 1px solid var(--border-light);
        border-radius: 40px;
        text-decoration: none;
        color: var(--text-primary);
        font-size: 0.85rem;
        transition: 0.2s;
    }
    .pagination a.active {
        background: var(--gold);
        color: white;
        border-color: var(--gold);
    }
</style>

<div class="content-wrapper" style="margin-left:0; padding-top:20px;">
    <div class="stats-summary">
        <div class="stat-summary-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-value"><?= $total_all ?></div>
            <div class="stat-label">Total Bookings</div>
        </div>
        <div class="stat-summary-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?= $confirmed_count ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-summary-card">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-value"><?= $cancelled_count ?></div>
            <div class="stat-label">Cancelled</div>
        </div>
    </div>

    <div class="page-header">
        <h2><i class="fas fa-calendar-alt"></i> Booking Management</h2>
    </div>

    <div class="filter-bar">
        <form method="GET" style="display: contents;" id="filterForm">
            <input type="text" name="search" placeholder="Ref / Guest name / Email" value="<?= htmlspecialchars($search) ?>">
            <select name="status">
                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <button type="submit" class="action-btn" style="background: var(--gold); color:white; border:none;">Filter</button>
            <a href="admin_book.php" class="action-btn">Reset</a>
            <button type="button" id="exportAllBtn" class="action-btn" style="background: #2c3e66; color:white;">Generate All Invoices</button>
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table class="bookings-table">
            <thead>
                <tr><th>ID</th><th>Ref</th><th>Guest</th><th>Room</th><th>Check In</th><th>Check Out</th><th>Total (RM)</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if ($bookings->num_rows == 0): ?>
                    <tr><td colspan="9" style="text-align:center; padding:48px;">No bookings found.</td></td>
                <?php else: while ($row = $bookings->fetch_assoc()):
                    $total_display = $row['payment_grand_total'] ?? $row['grand_total'] ?? 0;
                    $status_class = match($row['status']) {
                        'confirmed' => 'status-confirmed',
                        'cancelled' => 'status-cancelled',
                        'completed' => 'status-completed',
                        default => ''
                    };
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['booking_ref']) ?></strong></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?><br><small><?= htmlspecialchars($row['email']) ?></small></td>
                    <td><?= htmlspecialchars($row['room_name']) ?> (x<?= $row['quantity'] ?>)</td>
                    <td><?= date('d M Y', strtotime($row['check_in'])) ?></td>
                    <td><?= date('d M Y', strtotime($row['check_out'])) ?></td>
                    <td>RM <?= number_format($total_display, 2) ?></td>
                    <td><span class="status-badge <?= $status_class ?>"><?= ucfirst($row['status']) ?></span></td>
                    <td class="action-buttons">
                        <a href="admin_bdetails.php?booking_id=<?= $row['id'] ?>" class="action-btn edit"><i class="fas fa-edit"></i> Edit</a>
                        <button class="action-btn invoice" data-id="<?= $row['id'] ?>"><i class="fas fa-receipt"></i> Invoice</button>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i=1; $i<=$total_pages; $i++): ?>
            <a href="?status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= $i==$page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Handle single invoice: open a new printable window
document.querySelectorAll('.invoice').forEach(btn => {
    btn.addEventListener('click', () => {
        const bookingId = btn.dataset.id;
        const url = `?action=single_invoice_print&booking_id=${bookingId}`;
        window.open(url, '_blank', 'width=800,height=900,scrollbars=yes,resizable=yes');
    });
});

// Handle all invoices: open printable window with current filters
document.getElementById('exportAllBtn').addEventListener('click', () => {
    const params = new URLSearchParams(window.location.search);
    const url = `?action=all_invoices_print&${params.toString()}`;
    window.open(url, '_blank', 'width=900,height=1000,scrollbars=yes,resizable=yes');
});
</script>

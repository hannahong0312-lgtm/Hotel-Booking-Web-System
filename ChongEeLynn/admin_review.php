<?php
// admin_review.php - Manage all customer reviews
require_once '../Shared/config.php';
require_once '../ChangJingEn/admin_header.php';

// Helper function to safely escape values
function safeHtml($value, $default = '') {
    if ($value === null || $value === '') {
        return $default;
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Helper function to safely format numbers
function safeNumberFormat($value, $decimals = 1) {
    if ($value === null || $value === '') {
        return '0.0';
    }
    return number_format((float)$value, $decimals);
}

// Get filter parameters
$status_filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, pending, awarded
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Handle approve/delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['review_id'])) {
        $review_id = (int)$_POST['review_id'];
        
        if ($_POST['action'] === 'delete') {
            // Delete review (optionally remove points too)
            $conn->begin_transaction();
            try {
                // Get review details
                $stmt = $conn->prepare("SELECT user_id, booking_id FROM review WHERE id = ?");
                $stmt->bind_param("i", $review_id);
                $stmt->execute();
                $review = $stmt->get_result()->fetch_assoc();
                
                if ($review) {
                    // Delete review
                    $stmt = $conn->prepare("DELETE FROM review WHERE id = ?");
                    $stmt->bind_param("i", $review_id);
                    $stmt->execute();
                    
                    // Remove points awarded (10 points)
                    $stmt = $conn->prepare("UPDATE users SET points = points - 10 WHERE id = ?");
                    $stmt->bind_param("i", $review['user_id']);
                    $stmt->execute();
                    
                    // Reset review_points_awarded in book table
                    $stmt = $conn->prepare("UPDATE book SET review_points_awarded = 0 WHERE id = ?");
                    $stmt->bind_param("i", $review['booking_id']);
                    $stmt->execute();
                }
                $conn->commit();
                $success_msg = "Review deleted and points removed.";
            } catch (Exception $e) {
                $conn->rollback();
                $error_msg = "Error deleting review: " . $e->getMessage();
            }
        }
    }
}

// Build query
$sql = "SELECT 
            r.id, r.r_rating, r.r_comment, r.created_at,
            r.user_id, r.room_id, r.booking_id,
            u.first_name, u.last_name, u.email, u.points,
            rm.name AS room_name,
            b.booking_ref, b.review_points_awarded, b.status AS booking_status
        FROM review r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        LEFT JOIN book b ON r.booking_id = b.id
        WHERE 1=1";

$params = [];
$types = "";

if ($status_filter === 'pending') {
    $sql .= " AND b.review_points_awarded = 0";
} elseif ($status_filter === 'awarded') {
    $sql .= " AND b.review_points_awarded = 1";
}

if (!empty($search)) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR rm.name LIKE ? OR r.r_comment LIKE ?)";
    $sw = "%$search%";
    $params = array_fill(0, 5, $sw);
    $types = "sssss";
}

$sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reviews = $stmt->get_result();

// Count total for pagination
$count_sql = "SELECT COUNT(*) as total FROM review r 
              LEFT JOIN book b ON r.booking_id = b.id 
              WHERE 1=1";
$count_params = [];
$count_types = "";

if ($status_filter === 'pending') {
    $count_sql .= " AND b.review_points_awarded = 0";
} elseif ($status_filter === 'awarded') {
    $count_sql .= " AND b.review_points_awarded = 1";
}

if (!empty($search)) {
    $count_sql .= " AND (r.r_comment LIKE ?)";
    $count_params[] = "%$search%";
    $count_types .= "s";
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Stats - with null handling
$total_reviews = $conn->query("SELECT COUNT(*) FROM review")->fetch_row()[0];
$pending_points = $conn->query("SELECT COUNT(*) FROM review r LEFT JOIN book b ON r.booking_id = b.id WHERE b.review_points_awarded = 0")->fetch_row()[0];
$awarded_points = $conn->query("SELECT COUNT(*) FROM review r LEFT JOIN book b ON r.booking_id = b.id WHERE b.review_points_awarded = 1")->fetch_row()[0];
$avg_rating_result = $conn->query("SELECT AVG(r_rating) as avg FROM review")->fetch_assoc();
$avg_rating = $avg_rating_result['avg'] ?? null;
?>

<link rel="stylesheet" href="css/admin_review.css">

<div class="content-wrapper" style="margin-left:0; padding-top:20px;">
    <!-- Stats Cards -->
    <div class="review-stats">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-value"><?= $total_reviews ?></div>
            <div class="stat-label">Total Reviews</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-value"><?= $pending_points ?></div>
            <div class="stat-label">Pending Points</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⭐</div>
            <div class="stat-value"><?= safeNumberFormat($avg_rating, 1) ?></div>
            <div class="stat-label">Avg Rating</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🎁</div>
            <div class="stat-value"><?= $awarded_points ?></div>
            <div class="stat-label">Points Awarded</div>
        </div>
    </div>

    <!-- Header -->
    <div class="review-header">
        <h2><i class="fas fa-star"></i> Customer Reviews Management</h2>
    </div>

    <!-- Filter Bar -->
    <div class="review-filter-bar">
        <form method="GET" style="display: contents;" id="filterForm">
            <input type="text" name="search" placeholder="Search by guest name, room, or comment..." value="<?= safeHtml($search) ?>">
            <select name="filter">
                <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Reviews</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending Points</option>
                <option value="awarded" <?= $status_filter == 'awarded' ? 'selected' : '' ?>>Points Awarded</option>
            </select>
            <button type="submit" class="filter-btn">Filter</button>
            <a href="admin_review.php" class="reset-btn">Reset</a>
        </form>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?= safeHtml($success_msg) ?></div>
    <?php endif; ?>
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-error"><?= safeHtml($error_msg) ?></div>
    <?php endif; ?>

    <!-- Reviews Grid -->
    <div class="reviews-grid">
        <?php if ($reviews->num_rows == 0): ?>
            <div class="no-reviews">
                <i class="fas fa-star-of-life"></i>
                <p>No reviews found</p>
            </div>
        <?php else: while ($review = $reviews->fetch_assoc()): 
            $stars = (int)($review['r_rating'] ?? 0);
            $is_awarded = ($review['review_points_awarded'] ?? 0) == 1;
            $fullName = trim(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? ''));
            if (empty($fullName)) $fullName = 'Anonymous';
        ?>
            <div class="review-card">
                <div class="review-card-header">
                    <div class="guest-info">
                        <div class="guest-name">
                            <?= safeHtml($fullName) ?>
                        </div>
                        <div class="guest-email"><?= safeHtml($review['email'] ?? 'No email') ?></div>
                    </div>
                    <div class="points-badge <?= $is_awarded ? 'awarded' : 'pending' ?>">
                        <?= $is_awarded ? '✓ 10 Pts Awarded' : '⏳ Pending Points' ?>
                    </div>
                </div>

                <div class="review-card-body">
                    <div class="booking-info">
                        <span class="booking-ref">
                            <?php if (!empty($review['booking_ref'])): ?>
                                📋 <?= safeHtml($review['booking_ref']) ?>
                            <?php else: ?>
                                📋 No booking reference
                            <?php endif; ?>
                        </span>
                        <span class="room-name">🏨 <?= safeHtml($review['room_name'] ?? 'Unknown Room') ?></span>
                    </div>
                    
                    <div class="rating-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= $stars): ?>
                                <i class="fas fa-star star-filled"></i>
                            <?php else: ?>
                                <i class="far fa-star star-empty"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <span class="rating-number">(<?= $stars ?>/5)</span>
                    </div>

                    <div class="review-comment">
                        "<?= nl2br(safeHtml($review['r_comment'] ?? 'No comment provided')) ?>"
                    </div>

                    <div class="review-meta">
                        <span class="review-date">📅 <?= date('d M Y, H:i', strtotime($review['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>

                <div class="review-card-footer">
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this review and remove 10 points from the user?');">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="delete-review-btn">
                            <i class="fas fa-trash-alt"></i> Delete Review
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?filter=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>" 
               class="<?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Auto-hide alerts after 3 seconds
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 3000);
</script>
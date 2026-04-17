<?php
include '../Shared/config.php';
include '../Shared/header.php';

// Get room ID from URL
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($room_id <= 0) {
    header('Location: accommodation.php');
    exit;
}

// Handle review submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $error = 'Please login to submit a review.';
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        
        // Validate
        if ($rating < 1 || $rating > 5) {
            $error = 'Please select a valid rating.';
        } elseif (empty($comment)) {
            $error = 'Please enter your review comment.';
        } elseif (strlen($comment) > 255) {
            $error = 'Comment cannot exceed 255 characters.';
        } else {
            // Check if user already reviewed this room
            $check_sql = "SELECT user_id FROM review WHERE user_id = $user_id AND room_id = $room_id";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                $error = 'You have already reviewed this room.';
            } else {
                // Insert review - MATCHING YOUR EXACT COLUMN NAMES
                $comment_safe = mysqli_real_escape_string($conn, $comment);
                $insert_sql = "INSERT INTO review (user_id, room_id, r_rating, r_comment, created_at) 
                               VALUES ($user_id, $room_id, $rating, '$comment_safe', NOW())";
                
                if ($conn->query($insert_sql)) {
                    $message = 'Your review has been submitted successfully!';
                    echo '<meta http-equiv="refresh" content="2;url=review.php?id=' . $room_id . '">';
                } else {
                    $error = 'Database error: ' . $conn->error;
                }
            }
        }
    }
}

// Handle admin delete - using user_id since there's no rev_id
if (isset($_GET['delete']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $delete_user_id = intval($_GET['delete']);
    $conn->query("DELETE FROM review WHERE user_id = $delete_user_id AND room_id = $room_id");
    header("Location: review.php?id=$room_id");
    exit;
}

// Fetch room details
$room_sql = "SELECT * FROM rooms WHERE id = $room_id AND is_active = 1";
$room_result = $conn->query($room_sql);

if (!$room_result || $room_result->num_rows == 0) {
    header('Location: accommodation.php');
    exit;
}

$room = $room_result->fetch_assoc();

// Get filter parameters
$filter_rating = isset($_GET['rating']) ? intval($_GET['rating']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$where = "r.room_id = $room_id";
if ($filter_rating > 0) {
    $where .= " AND r.r_rating = $filter_rating";
}

// Sorting
if ($sort == 'highest') {
    $order = "r.r_rating DESC";
} elseif ($sort == 'lowest') {
    $order = "r.r_rating ASC";
} else {
    $order = "r.created_at DESC";
}

// Get total reviews
$count_result = $conn->query("SELECT COUNT(*) as total FROM review r WHERE $where");
$total_reviews = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_reviews / $limit);

// Get reviews - using user_id as identifier since no rev_id
$sql = "SELECT r.user_id, r.r_rating, r.r_comment, r.created_at,
               u.first_name, u.last_name
        FROM review r
        JOIN users u ON r.user_id = u.id
        WHERE $where
        ORDER BY $order
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$reviews = array();
while ($row = $result->fetch_assoc()) {
    $row['created_at_formatted'] = date('F j, Y', strtotime($row['created_at']));
    $row['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
    $reviews[] = $row;
}

// Get statistics
$stats = $conn->query("SELECT COUNT(*) as total, AVG(r_rating) as avg FROM review WHERE room_id = $room_id")->fetch_assoc();
$avg_rating = $stats['avg'] ? round($stats['avg'], 1) : 0;
$total_reviews_count = $stats['total'] ?: 0;

// Rating distribution
$distribution = array();
for ($i = 5; $i >= 1; $i--) {
    $dist = $conn->query("SELECT COUNT(*) as count FROM review WHERE room_id = $room_id AND r_rating = $i")->fetch_assoc();
    $distribution[$i] = $dist['count'];
}

// Check if user already reviewed
$has_reviewed = false;
if (isset($_SESSION['user_id'])) {
    $check = $conn->query("SELECT user_id FROM review WHERE user_id = " . $_SESSION['user_id'] . " AND room_id = $room_id");
    $has_reviewed = $check->num_rows > 0;
}

// Function to generate star HTML
function getStars($rating) {
    $rating = (float)$rating;
    $fullStars = floor($rating);
    $stars = str_repeat('★', $fullStars);
    $stars .= str_repeat('☆', 5 - $fullStars);
    return $stars;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews for <?php echo htmlspecialchars($room['name']); ?></title>
    <link rel="stylesheet" href="css/review.css">
</head>
<body>

<!-- Hero Section -->
<section class="reviews-hero" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/<?php echo $room['image']; ?>');">
    <div class="reviews-hero-content">
        <div class="breadcrumb">
            <a href="accommodation.php">Accommodations</a> / 
            <a href="roomdetails.php?id=<?php echo $room_id; ?>"><?php echo htmlspecialchars($room['name']); ?></a> / 
            <span>Reviews</span>
        </div>
        <h1>Guest Reviews</h1>
        <div class="hero-rating">
            <div class="rating-number"><?php echo $avg_rating; ?></div>
            <div class="rating-stars" id="heroStars"></div>
            <div class="rating-count">(<?php echo $total_reviews_count; ?> reviews)</div>
        </div>
    </div>
</section>

<!-- Message Display -->
<?php if ($message): ?>
    <div class="message-container">
        <div class="message success"><?php echo $message; ?></div>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="message-container">
        <div class="message error"><?php echo $error; ?></div>
    </div>
<?php endif; ?>

<!-- Main Content -->
<section class="reviews-main">
    <div class="reviews-container">
        <div class="reviews-grid">
            <!-- Left Column -->
            <div class="reviews-left">
                <div class="back-to-room">
                    <a href="roomdetails.php?id=<?php echo $room_id; ?>" class="back-btn">← Back to Room</a>
                </div>

                <!-- Filters -->
                <div class="filters-bar">
                    <select id="ratingFilter" onchange="applyFilters()">
                        <option value="0">All Ratings</option>
                        <option value="5" <?php if($filter_rating == 5) echo 'selected'; ?>>5 Stars</option>
                        <option value="4" <?php if($filter_rating == 4) echo 'selected'; ?>>4 Stars</option>
                        <option value="3" <?php if($filter_rating == 3) echo 'selected'; ?>>3 Stars</option>
                        <option value="2" <?php if($filter_rating == 2) echo 'selected'; ?>>2 Stars</option>
                        <option value="1" <?php if($filter_rating == 1) echo 'selected'; ?>>1 Star</option>
                    </select>
                    
                    <select id="sortFilter" onchange="applyFilters()">
                        <option value="latest" <?php if($sort == 'latest') echo 'selected'; ?>>Latest First</option>
                        <option value="highest" <?php if($sort == 'highest') echo 'selected'; ?>>Highest Rated</option>
                        <option value="lowest" <?php if($sort == 'lowest') echo 'selected'; ?>>Lowest Rated</option>
                    </select>
                </div>
                
                <div class="results-count"><?php echo count($reviews); ?> of <?php echo $total_reviews; ?> reviews</div>
                
                <!-- Reviews List -->
                <div class="reviews-list">
                    <?php if (empty($reviews)): ?>
                        <div class="no-reviews">
                            <div class="no-reviews-icon">📝</div>
                            <h4>No reviews yet</h4>
                            <p>Be the first to share your experience!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar"><?php echo strtoupper(substr($review['user_name'], 0, 1)); ?></div>
                                        <div>
                                            <h4><?php echo htmlspecialchars($review['user_name']); ?></h4>
                                            <div class="review-stars"><?php echo getStars($review['r_rating']); ?></div>
                                        </div>
                                    </div>
                                    <div class="review-date">
                                        <?php echo $review['created_at_formatted']; ?>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                            <a href="?id=<?php echo $room_id; ?>&delete=<?php echo $review['user_id']; ?>" class="delete-btn" onclick="return confirm('Delete this review?')">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['r_comment'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?id=<?php echo $room_id; ?>&page=<?php echo $page-1; ?>&rating=<?php echo $filter_rating; ?>&sort=<?php echo $sort; ?>" class="page-btn">← Prev</a>
                        <?php endif; ?>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?id=<?php echo $room_id; ?>&page=<?php echo $i; ?>&rating=<?php echo $filter_rating; ?>&sort=<?php echo $sort; ?>" class="page-btn <?php if($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?id=<?php echo $room_id; ?>&page=<?php echo $page+1; ?>&rating=<?php echo $filter_rating; ?>&sort=<?php echo $sort; ?>" class="page-btn">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column - Sidebar -->
            <div class="reviews-right">
                <div class="sidebar-card">
                    <h3>Rating Summary</h3>
                    <div class="overall-rating">
                        <div class="rating-number"><?php echo $avg_rating; ?></div>
                        <div class="rating-stars"><?php echo getStars($avg_rating); ?></div>
                        <div class="total-reviews"><?php echo $total_reviews_count; ?> reviews</div>
                    </div>
                    
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <?php 
                        $percent = ($total_reviews_count > 0) ? ($distribution[$i] / $total_reviews_count * 100) : 0; 
                        ?>
                        <div class="rating-bar-item">
                            <div class="rating-label"><?php echo $i; ?> ★</div>
                            <div class="rating-bar-bg"><div class="rating-bar-fill" style="width: <?php echo $percent; ?>%"></div></div>
                            <div class="rating-count"><?php echo $distribution[$i]; ?></div>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div class="sidebar-card">
                    <img src="images/<?php echo $room['image']; ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="room-image">
                    <h4><?php echo htmlspecialchars($room['name']); ?></h4>
                    <p>👥 Max <?php echo $room['max_guests']; ?> guests</p>
                    <p>🛏️ <?php echo htmlspecialchars($room['bed_type']); ?></p>
                    <a href="roomdetails.php?id=<?php echo $room_id; ?>" class="view-room-btn">View Room →</a>
                </div>
                
                <!-- Write Review Form -->
                <?php if (isset($_SESSION['user_id']) && !$has_reviewed): ?>
                    <div class="sidebar-card write-review-card">
                        <h3>Write a Review</h3>
                        <form method="POST" action="" class="review-form-simple">
                            <div class="form-group">
                                <label>Rating</label>
                                <select name="rating" required>
                                    <option value="">Select rating</option>
                                    <option value="5">5 Stars - Excellent</option>
                                    <option value="4">4 Stars - Very Good</option>
                                    <option value="3">3 Stars - Good</option>
                                    <option value="2">2 Stars - Fair</option>
                                    <option value="1">1 Star - Poor</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Your Review</label>
                                <textarea name="comment" rows="4" maxlength="255" placeholder="Share your experience..." required></textarea>
                                <small>Max 255 characters</small>
                            </div>
                            
                            <button type="submit" name="submit_review" class="submit-review-btn">Submit Review</button>
                        </form>
                    </div>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <div class="sidebar-card write-review-card">
                        <h3>Write a Review</h3>
                        <p>Please <a href="../ChangJingEn/login.php" class="login-link">login</a> to submit a review.</p>
                    </div>
                <?php elseif ($has_reviewed): ?>
                    <div class="sidebar-card write-review-card">
                        <h3>Thank You!</h3>
                        <p>You have already reviewed this room.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Generate star rating HTML
function getStars(rating) {
    var stars = '';
    var fullStars = Math.floor(rating);
    for (var i = 0; i < fullStars; i++) stars += '★';
    for (var i = stars.length; i < 5; i++) stars += '☆';
    return stars;
}

// Display hero stars
document.getElementById('heroStars').innerHTML = getStars(<?php echo $avg_rating; ?>);

// Apply filters
function applyFilters() {
    var rating = document.getElementById('ratingFilter').value;
    var sort = document.getElementById('sortFilter').value;
    window.location.href = 'review.php?id=<?php echo $room_id; ?>&rating=' + rating + '&sort=' + sort;
}
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
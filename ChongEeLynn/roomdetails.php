<?php
// roomdetails.php
include '../Shared/config.php';
include '../Shared/header.php';

// Get room ID from URL
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($room_id <= 0) {
    header('Location: accommodation.php');
    exit;
}

// Fetch room details
$sql = "SELECT * FROM rooms WHERE id = $room_id AND is_active = 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    header('Location: accommodation.php');
    exit;
}

$room = $result->fetch_assoc();

// Fetch 4 similar rooms (same category, different id)
$similar_sql = "SELECT * FROM rooms WHERE category = '{$conn->real_escape_string($room['category'])}' AND id != $room_id AND is_active = 1 LIMIT 4";
$similar_result = $conn->query($similar_sql);
$similar_rooms = [];
if ($similar_result && $similar_result->num_rows > 0) {
    while ($row = $similar_result->fetch_assoc()) {
        $similar_rooms[] = $row;
    }
}

// Get date parameters from URL (if coming from search)
$arrive = isset($_GET['arrive']) ? htmlspecialchars($_GET['arrive']) : '';
$depart = isset($_GET['depart']) ? htmlspecialchars($_GET['depart']) : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : $room['max_guests'];

// Calculate number of nights
$nights = 0;
if ($arrive && $depart) {
    $date1 = new DateTime($arrive);
    $date2 = new DateTime($depart);
    $interval = $date1->diff($date2);
    $nights = $interval->days;
}
$total_price = $nights * $room['price'];

// Fetch review statistics for this room
$review_stats_sql = "SELECT COUNT(*) as total, AVG(R_RATING) as avg_rating FROM REVIEW WHERE ROOM_ID = $room_id";
$review_stats_result = $conn->query($review_stats_sql);
$review_stats = $review_stats_result->fetch_assoc();
$total_reviews = $review_stats['total'] ? $review_stats['total'] : 0;
$avg_rating = $review_stats['avg_rating'] ? round($review_stats['avg_rating'], 1) : 0;

// Fetch top 2 reviews
$top_reviews_sql = "SELECT r.REV_ID, r.R_RATING, r.R_COMMENT, r.CREATED_AT,
                           u.first_name, u.last_name
                    FROM REVIEW r
                    JOIN users u ON r.USER_ID = u.id
                    WHERE r.ROOM_ID = $room_id
                    ORDER BY r.R_RATING DESC, r.CREATED_AT DESC
                    LIMIT 2";
$top_reviews_result = $conn->query($top_reviews_sql);
$top_reviews = [];
if ($top_reviews_result && $top_reviews_result->num_rows > 0) {
    while ($row = $top_reviews_result->fetch_assoc()) {
        $row['user_name'] = $row['first_name'] . ' ' . $row['last_name'];
        $row['created_at_formatted'] = date('F j, Y', strtotime($row['CREATED_AT']));
        $top_reviews[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($room['name']) ?> | Luxury Accommodations</title>
    <link rel="stylesheet" href="css/roomdetails.css">
</head>
<body>

<!-- Hero Section with Room Image -->
<section class="detail-hero" style="background-image: linear-gradient(rgba(0, 0, 0, 0.09), rgba(49, 48, 48, 0.6)), url('images/<?php echo $room['image']; ?>');">
    <div class="detail-hero-content">
        <div class="breadcrumb">
            <a href="accommodation.php">Accommodations</a> / 
            <span><?= htmlspecialchars($room['name']) ?></span>
        </div>
        <h1><?= htmlspecialchars($room['name']) ?></h1>
        <div class="hero-badges">
            <span class="hero-badge category-badge <?= $room['category'] ?>"><?= ucfirst($room['category']) ?></span>
            <span class="hero-badge price-badge">RM <?= number_format($room['price'], 0) ?> / night</span>
        </div>
    </div>
</section>

<!-- Room Details Main Content -->
<section class="detail-main">
    <div class="detail-container">
        <!-- Back Button - ABOVE room description -->
        <div class="back-section-top">
            <a href="accommodation.php" class="back-btn">← Back to All Rooms</a>
        </div>
        
        <div class="detail-grid">
            <!-- Left Column: Room Info -->
            <div class="detail-info">
                <div class="info-card">
                    <h2>Room Description</h2>
                    <p class="description"><?= htmlspecialchars($room['description']) ?></p>
                </div>

                <!-- Photo Gallery Section -->
                <div class="info-card">
                    <h2>Room Gallery</h2>
                    <p class="gallery-subtitle">Take a closer look at what this room has to offer</p>
                    
                    <div class="gallery-grid">
                        <div class="gallery-item main-room">
                            <img src="images/<?php echo $room['image']; ?>" alt="<?= htmlspecialchars($room['name']) ?> - Main Room">
                            <div class="gallery-caption">
                                <span class="caption-icon">🛏️</span>
                                <span>Comfortable <?= htmlspecialchars($room['bed_type']) ?></span>
                            </div>
                        </div>
                        
                        <div class="gallery-item bathroom">
                            <img src="images/bathroom-<?= $room['category'] ?>.jpg" 
                                 alt="Luxury Bathroom" 
                                 onerror="this.src='images/bathroom-default.jpg'">
                            <div class="gallery-caption">
                                <span class="caption-icon">🚿</span>
                                <span>Luxury Bathroom</span>
                            </div>
                        </div>
                        
                        <div class="gallery-item amenities-area">
                            <img src="images/tea-coffee-<?= $room['category'] ?>.jpg" 
                                 alt="Tea, Coffee & Mini Fridge" 
                                 onerror="this.src='images/tea-coffee-default.jpg'">
                            <div class="gallery-caption">
                                <span class="caption-icon">☕</span>
                                <span>Tea, Coffee & Mini Fridge</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-card">
                    <div class="features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">👥</div>
                            <div class="feature-text">
                                <strong>Max Guests</strong>
                                <span><?= $room['max_guests'] ?> guests</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🛏️</div>
                            <div class="feature-text">
                                <strong>Bed Type</strong>
                                <span><?= htmlspecialchars($room['bed_type']) ?></span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">📐</div>
                            <div class="feature-text">
                                <strong>Room Size</strong>
                                <span><?= $room['size'] ?> m²</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🚪</div>
                            <div class="feature-text">
                                <strong>Availability</strong>
                                <span class="<?= $room['rooms_available'] > 0 ? 'avail-text' : 'sold-text' ?>">
                                    <?= $room['rooms_available'] > 0 ? $room['rooms_available'] . ' rooms left' : 'Sold Out' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-card">
                    <h2>Room Amenities</h2>
                    <div class="amenities-list">
                        <span class="amenity">✓ Free Wi-Fi</span>
                        <span class="amenity">✓ Air Conditioning</span>
                        <span class="amenity">✓ Flat-screen TV</span>
                        <span class="amenity">✓ Mini Bar</span>
                        <span class="amenity">✓ In-room Safe</span>
                        <span class="amenity">✓ Tea & Coffee Maker</span>
                        <span class="amenity">✓ Bathrobe & Slippers</span>
                        <span class="amenity">✓ 24hr Room Service</span>
                        <span class="amenity">✓ Workspace Desk</span>
                        <span class="amenity">✓ Premium Toiletries</span>
                    </div>
                </div>
                
                <!-- Room Policies Section -->
                <div class="info-card">
                    <h2>Room Policies</h2>
                    <div class="policies-list">
                        <div class="policy-item">
                            <span class="policy-icon">⏰</span>
                            <div class="policy-text">
                                <strong>Check-in / Check-out</strong>
                                <p>Check-in from 3:00 PM | Check-out by 12:00 PM</p>
                            </div>
                        </div>
                        <div class="policy-item">
                            <span class="policy-icon">🔄</span>
                            <div class="policy-text">
                                <strong>Cancellation Policy</strong>
                                <p>Free cancellation up to 24 hours before check-in for a full refund.</p>
                            </div>
                        </div>
                        <div class="policy-item">
                            <span class="policy-icon">👶</span>
                            <div class="policy-text">
                                <strong>Child Policy</strong>
                                <p>Children under 12 stay free (using existing bedding)</p>
                            </div>
                        </div>
                        <div class="policy-item">
                            <span class="policy-icon">🐾</span>
                            <div class="policy-text">
                                <strong>Pet Policy</strong>
                                <p>Pets not allowed (service animals welcome)</p>
                            </div>
                        </div>
                        <div class="policy-item">
                            <span class="policy-icon">🚭</span>
                            <div class="policy-text">
                                <strong>Smoking Policy</strong>
                                <p>Smoking-free environment (fine applies for violations)</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reviews Section - NOW INSIDE THE LEFT COLUMN -->
                <div class="info-card reviews-section-card">
                    <div class="reviews-section-header">
                        <h2>Guest Reviews</h2>
                        <div class="review-summary">
                            <div class="review-summary-rating">
                                <span class="avg-rating-number"><?php echo $avg_rating; ?></span>
                                <div class="stars-display">
                                    <?php
                                    $full_stars = floor($avg_rating);
                                    for ($i = 0; $i < $full_stars; $i++) echo '★';
                                    for ($i = $full_stars; $i < 5; $i++) echo '☆';
                                    ?>
                                </div>
                                <span class="review-count">(<?php echo $total_reviews; ?> reviews)</span>
                            </div>
                            <a href="review.php?id=<?php echo $room_id; ?>" class="view-all-reviews-btn">View All Reviews →</a>
                        </div>
                    </div>
                    
                    <?php if (empty($top_reviews)): ?>
                        <div class="no-reviews-message">
                            <p>No reviews yet for this room.</p>
                            <a href="review.php?id=<?php echo $room_id; ?>" class="be-first-btn">Be the first to review →</a>
                        </div>
                    <?php else: ?>
                        <div class="top-reviews-list">
                            <?php foreach($top_reviews as $review): ?>
                                <div class="top-review-item">
                                    <div class="review-header-simple">
                                        <div class="reviewer-name">
                                            <span class="reviewer-initial"><?php echo strtoupper(substr($review['user_name'], 0, 1)); ?></span>
                                            <span class="name"><?php echo htmlspecialchars($review['user_name']); ?></span>
                                        </div>
                                        <div class="review-rating-simple">
                                            <?php
                                            for ($i = 0; $i < $review['R_RATING']; $i++) echo '★';
                                            for ($i = $review['R_RATING']; $i < 5; $i++) echo '☆';
                                            ?>
                                        </div>
                                    </div>
                                    <p class="review-comment-simple"><?php echo nl2br(htmlspecialchars(substr($review['R_COMMENT'], 0, 150))); ?><?php if(strlen($review['R_COMMENT']) > 150) echo '...'; ?></p>
                                    <div class="review-date-simple"><?php echo $review['created_at_formatted']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($total_reviews > 2): ?>
                            <div class="more-reviews-link">
                                <a href="review.php?id=<?php echo $room_id; ?>">Read all <?php echo $total_reviews; ?> reviews →</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div> <!-- END OF LEFT COLUMN detail-info -->
            
            <!-- Right Column: Booking Card (STICKY/SCROLLABLE) -->
            <div class="detail-booking">
                <div class="booking-card sticky-card">
                    <h3>Book This Room</h3>
                    <div class="booking-price">
                        <span class="price">RM <?= number_format($room['price'], 0) ?></span>
                        <span class="night">/ night</span>
                    </div>
                    
                    <form method="GET" action="../Hannah/payment.php" class="booking-form" id="bookingForm">
                        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                        
                        <div class="form-group">
                            <label>ARRIVE DATE</label>
                            <input type="date" name="arrive" class="form-input" id="arriveInput" value="<?= $arrive ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>DEPART DATE</label>
                            <input type="date" name="depart" class="form-input" id="departInput" value="<?= $depart ?>" required>
                        </div>
                        
                        <!-- Total Price Display -->
                        <div class="total-price-display" id="totalPriceDisplay">
                            <div class="total-row">
                                <span>RM <?= number_format($room['price'], 0) ?> × <span id="nightCount"><?= $nights ?></span> night(s)</span>
                                <span>RM <span id="subtotal"><?= number_format($total_price, 0) ?></span></span>
                            </div>
                            <div class="total-row total-grand">
                                <strong>Total (incl. taxes)</strong>
                                <strong>RM <span id="grandTotal"><?= number_format($total_price, 0) ?></span></strong>
                            </div>
                        </div>
                        
                        <button type="submit" class="book-now-btn" <?= $room['rooms_available'] == 0 ? 'disabled' : '' ?>>
                            <?= $room['rooms_available'] > 0 ? 'Proceed to Booking →' : 'Sold Out' ?>
                        </button>
                    </form>
                    
                    <div class="booking-note">
                        <p>✓ Free cancellation up to 24 hours before check-in</p>
                        <p>✓ No prepayment required</p>
                        <p>✓ Best price guaranteed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Rooms Section (shows 4 rooms) -->
<?php if (!empty($similar_rooms)): ?>
<section class="similar-rooms">
    <div class="detail-container">
        <div class="similar-header">
            <h2>You May Also Like</h2>
            <p>Explore other luxurious accommodations</p>
        </div>
        <div class="similar-grid">
            <?php foreach($similar_rooms as $similar): ?>
                <div class="similar-card" onclick="window.location.href='roomdetails.php?id=<?= $similar['id'] ?>&arrive=<?= urlencode($arrive) ?>&depart=<?= urlencode($depart) ?>&guests=<?= $guests ?>'">
                    <div class="similar-img">
                        <img src="images/<?= htmlspecialchars($similar['image']) ?>" alt="<?= htmlspecialchars($similar['name']) ?>">
                        <span class="similar-badge"><?= ucfirst($similar['category']) ?></span>
                    </div>
                    <div class="similar-info">
                        <h3><?= htmlspecialchars($similar['name']) ?></h3>
                        <div class="similar-meta">
                            <span>👥 <?= $similar['max_guests'] ?> guests</span>
                            <span>🛏️ <?= htmlspecialchars($similar['bed_type']) ?></span>
                        </div>
                        <div class="similar-price">
                            RM <?= number_format($similar['price'], 0) ?>
                            <span>/ night</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
// Date picker logic with total calculation
let arriveInput = document.getElementById('arriveInput');
let departInput = document.getElementById('departInput');
let nightCountSpan = document.getElementById('nightCount');
let subtotalSpan = document.getElementById('subtotal');
let grandTotalSpan = document.getElementById('grandTotal');
let roomPrice = <?= $room['price'] ?>;

function calculateTotal() {
    let arrive = arriveInput.value;
    let depart = departInput.value;
    
    if (arrive && depart) {
        let date1 = new Date(arrive);
        let date2 = new Date(depart);
        let timeDiff = date2.getTime() - date1.getTime();
        let nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (nights > 0) {
            let total = nights * roomPrice;
            nightCountSpan.textContent = nights;
            subtotalSpan.textContent = total.toLocaleString();
            grandTotalSpan.textContent = total.toLocaleString();
        } else {
            nightCountSpan.textContent = '0';
            subtotalSpan.textContent = '0';
            grandTotalSpan.textContent = '0';
        }
    }
}

let today = new Date().toISOString().split('T')[0];
arriveInput.min = today;

if (!arriveInput.value) {
    let tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    arriveInput.value = tomorrow.toISOString().split('T')[0];
    let dayAfter = new Date(tomorrow);
    dayAfter.setDate(dayAfter.getDate() + 1);
    departInput.value = dayAfter.toISOString().split('T')[0];
    calculateTotal();
}

arriveInput.addEventListener('change', function() {
    if (this.value) {
        departInput.min = this.value;
        if (departInput.value && departInput.value < this.value) {
            departInput.value = '';
        }
        calculateTotal();
    }
});

departInput.addEventListener('change', function() {
    if (this.value) {
        calculateTotal();
    }
});

function changeGuestsDetail(delta) {
    let input = document.getElementById('guestInputDetail');
    let span = document.getElementById('guestValDetail');
    let val = parseInt(input.value) + delta;
    let maxGuests = <?= $room['max_guests'] ?>;
    if (val >= 1 && val <= maxGuests) {
        input.value = val;
        span.textContent = val;
    }
}

// Gallery Lightbox Popup Script
document.addEventListener('DOMContentLoaded', function() {
    // Create lightbox modal element
    const lightbox = document.createElement('div');
    lightbox.id = 'imageLightbox';
    lightbox.className = 'lightbox-modal';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="lightbox-close">&times;</span>
            <img class="lightbox-image" src="" alt="Full size image">
            <div class="lightbox-caption"></div>
            <button class="lightbox-prev">❮</button>
            <button class="lightbox-next">❯</button>
        </div>
    `;
    document.body.appendChild(lightbox);
    
    const lightboxModal = document.getElementById('imageLightbox');
    const lightboxImg = lightboxModal.querySelector('.lightbox-image');
    const lightboxCaption = lightboxModal.querySelector('.lightbox-caption');
    const closeBtn = lightboxModal.querySelector('.lightbox-close');
    const prevBtn = lightboxModal.querySelector('.lightbox-prev');
    const nextBtn = lightboxModal.querySelector('.lightbox-next');
    
    let currentImageIndex = 0;
    let galleryImages = [];
    
    // Function to open lightbox
    function openLightbox(index) {
        if (index >= 0 && index < galleryImages.length) {
            currentImageIndex = index;
            const img = galleryImages[currentImageIndex];
            lightboxImg.src = img.src;
            lightboxCaption.textContent = img.alt || '';
            lightboxModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Function to close lightbox
    function closeLightbox() {
        lightboxModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    // Function to show next image
    function showNextImage() {
        if (currentImageIndex < galleryImages.length - 1) {
            openLightbox(currentImageIndex + 1);
        }
    }
    
    // Function to show previous image
    function showPrevImage() {
        if (currentImageIndex > 0) {
            openLightbox(currentImageIndex - 1);
        }
    }
    
    // Get all gallery images
    galleryImages = document.querySelectorAll('.gallery-item img');
    
    // Add click event to each gallery image
    galleryImages.forEach((img, index) => {
        img.addEventListener('click', function(e) {
            e.stopPropagation();
            openLightbox(index);
        });
    });
    
    // Event listeners for lightbox controls
    closeBtn.addEventListener('click', closeLightbox);
    nextBtn.addEventListener('click', showNextImage);
    prevBtn.addEventListener('click', showPrevImage);
    
    // Close lightbox when clicking outside the image
    lightboxModal.addEventListener('click', function(e) {
        if (e.target === lightboxModal) {
            closeLightbox();
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (lightboxModal.classList.contains('active')) {
            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowRight') {
                showNextImage();
            } else if (e.key === 'ArrowLeft') {
                showPrevImage();
            }
        }
    });
});
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
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
            </div>
            
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
                        <img src="<?= htmlspecialchars($similar['image']) ?>" alt="<?= htmlspecialchars($similar['name']) ?>">
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
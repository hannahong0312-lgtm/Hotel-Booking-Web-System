<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../Shared/header.php';

$conn = new mysqli("localhost", "root", "", "hotel_booking");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($room_id <= 0) { header('Location: accommodation.php'); exit; }

$result = $conn->query("SELECT * FROM room WHERE id = $room_id");
$room = $result->fetch_assoc();
if (!$room) { header('Location: accommodation.php'); exit; }

$room['amenities'] = json_decode($room['amenities'], true);
$room['images'] = json_decode($room['images'], true);

$similar_rooms = [];
$sim_result = $conn->query("SELECT * FROM room WHERE id != $room_id LIMIT 3");
while ($s_row = $sim_result->fetch_assoc()) {
    $s_row['images'] = json_decode($s_row['images'], true);
    $similar_rooms[] = $s_row;
}

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<link rel="stylesheet" href="css/roomdetails.css">

<main>
    <!-- Hero Section -->
    <div class="room-hero">
        <div class="hero-overlay"></div>
        
        <div class="gallery-container">
            <div class="gallery-wrapper">
                <div class="gallery-scroll" id="galleryScroll">
                    <?php foreach ($room['images'] as $i => $img): ?>
                        <div class="gallery-item" data-index="<?= $i ?>">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($room['name']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="scroll-btn scroll-left" id="scrollLeftBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="scroll-btn scroll-right" id="scrollRightBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <div class="image-counter">
                <span id="currentImageIndex">1</span> / <span id="totalImages"><?= count($room['images']) ?></span>
            </div>
        </div>
        
        <div class="hero-content">
            <h1><?= htmlspecialchars($room['name']) ?></h1>
            
            <div class="hero-meta">
                <span class="room-type-badge"><?= strtoupper($room['type']) ?></span>
                
                <?php if ($room['popular']): ?>
                    <span class="popular-badge"><i class="fas fa-star"></i> Most Popular</span>
                <?php endif; ?>
                
                <div class="rating">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <span>4.8 (245 reviews)</span>
                </div>
            </div>
            
            <div class="hero-price">
                <span class="price">RM<?= number_format($room['price'], 0) ?></span>
                <span class="per-night">per night</span>
            </div>
            
            <div class="scroll-indicator">
                <span>Scroll to explore details</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Quick Links -->
        <div class="quick-links">
            <a href="accommodation.php" class="quick-link">
                <i class="fas fa-arrow-left"></i> Back to All Rooms
            </a>
        </div>

        <!-- Content Grid -->
        <div class="details-grid">
            <!-- Left Column -->
            <div class="info-section">
                <!-- Overview Card -->
                <div id="overview" class="info-card">
                    <h2><i class="fas fa-info-circle"></i> Overview</h2>
                    <p class="description"><?= htmlspecialchars($room['long_description']) ?></p>
                    
                    <div class="specs">
                        <div class="spec-item">
                            <i class="fas fa-bed"></i>
                            <div><strong>Bed Type</strong><span><?= $room['bed_type'] ?></span></div>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-arrows-alt"></i>
                            <div><strong>Room Size</strong><span><?= $room['size'] ?></span></div>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-eye"></i>
                            <div><strong>View</strong><span><?= $room['view'] ?></span></div>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-users"></i>
                            <div><strong>Max Occupancy</strong><span><?= $room['capacity'] ?> guests</span></div>
                        </div>
                    </div>
                </div>

                <!-- Amenities Card -->
                <div id="amenities" class="info-card">
                    <h2><i class="fas fa-concierge-bell"></i> Amenities</h2>
                    <div class="amenities-grid">
                        <?php foreach ($room['amenities'] as $amenity): ?>
                            <div class="amenity-item">
                                <i class="fas fa-check-circle"></i>
                                <span><?= htmlspecialchars($amenity) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Policies Card -->
                <div id="policies" class="info-card">
                    <h2><i class="fas fa-clipboard-list"></i> Room Policies</h2>
                    <ul class="policies">
                        <li><i class="fas fa-clock"></i> Check-in: 3:00 PM | Check-out: 11:00 AM</li>
                        <li><i class="fas fa-credit-card"></i> Credit card required for guarantee</li>
                        <li><i class="fas fa-ban"></i> No smoking in the room</li>
                        <li><i class="fas fa-paw"></i> Pets are not allowed</li>
                        <li><i class="fas fa-child"></i> Children of all ages welcome</li>
                    </ul>
                </div>

                <!-- Similar Rooms -->
                <?php if ($similar_rooms): ?>
                <div id="similar" class="similar-rooms">
                    <h3><i class="fas fa-hotel"></i> You might also like</h3>
                    <div class="similar-grid">
                        <?php foreach ($similar_rooms as $sr): ?>
                            <a href="roomdetails.php?id=<?= $sr['id'] ?>" class="similar-card">
                                <img src="<?= $sr['images'][0] ?>" alt="<?= htmlspecialchars($sr['name']) ?>">
                                <div class="similar-info">
                                    <h4><?= htmlspecialchars($sr['name']) ?></h4>
                                    <p>From RM<?= number_format($sr['price'], 0) ?>/night</p>
                                    <small><i class="fas fa-users"></i> Up to <?= $sr['capacity'] ?> guests</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Booking Form -->
            <div class="booking-section-wrapper">
                <div class="booking-card" id="bookingCard">
                    <div class="price-display">
                        <span class="price">RM<?= number_format($room['price'], 0) ?></span>
                        <span class="per-night">per night</span>
                    </div>
                    
                    <div class="availability-status">
                        <?php $a = $room['available']; ?>
                        <span class="status <?= $a > 2 ? 'available' : ($a > 0 ? 'limited' : 'soldout') ?>">
                            <i class="fas fa-<?= $a > 2 ? 'check-circle' : ($a > 0 ? 'exclamation-circle' : 'times-circle') ?>"></i>
                            <?= $a > 2 ? 'Available' : ($a > 0 ? "Only $a rooms left!" : 'Sold Out') ?>
                        </span>
                    </div>

                    <form id="bookingForm">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Check-in Date</label>
                            <input type="date" id="checkIn" value="<?= $today ?>" min="<?= $today ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Check-out Date</label>
                            <input type="date" id="checkOut" value="<?= $tomorrow ?>" min="<?= $tomorrow ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Number of Guests</label>
                            <select id="guests" required>
                                <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="booking-summary" id="bookingSummary">
                            <div class="summary-line">
                                <span>Price per night:</span>
                                <span>RM<?= number_format($room['price'], 0) ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Number of nights:</span>
                                <span id="nightsCount">1</span>
                            </div>
                            <div class="summary-line total">
                                <span>Total:</span>
                                <span id="totalPrice">RM<?= number_format($room['price'], 0) ?></span>
                            </div>
                        </div>
                        
                        <button type="button" id="bookNowBtn" class="btn btn-book-now" <?= $a <= 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-<?= $a > 0 ? 'check-circle' : 'times-circle' ?>"></i>
                            <?= $a > 0 ? 'Book Now' : 'Currently Unavailable' ?>
                        </button>
                    </form>
                    
                    <div class="secure-booking">
                        <i class="fas fa-lock"></i> Secure booking, guaranteed best rates
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modals -->
<div id="bookingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <h2><i class="fas fa-check-circle"></i> Confirm Your Booking</h2>
        <div id="modalBookingSummary"></div>
        <div class="modal-buttons">
            <button class="btn btn-secondary" id="cancelModalBtn">Cancel</button>
            <button class="btn btn-primary" id="confirmBookingBtn">Confirm Booking</button>
        </div>
    </div>
</div>

<div id="successModal" class="modal" style="display: none;">
    <div class="modal-content success">
        <span class="close" id="closeSuccessBtn">&times;</span>
        <i class="fas fa-check-circle success-icon"></i>
        <h2>Booking Confirmed!</h2>
        <p>Thank you for choosing Paradise Hotel. We've sent a confirmation email with your booking details.</p>
        <button class="btn btn-primary" id="continueBtn">Continue</button>
    </div>
</div>

<script>
// Room data
const roomData = {
    name: <?= json_encode($room['name']) ?>,
    price: <?= $room['price'] ?>
};

// Gallery functionality
const galleryScroll = document.getElementById('galleryScroll');
const scrollLeftBtn = document.getElementById('scrollLeftBtn');
const scrollRightBtn = document.getElementById('scrollRightBtn');
const currentImageSpan = document.getElementById('currentImageIndex');
const galleryItems = document.querySelectorAll('.gallery-item');

let currentIndex = 0;
let isDragging = false;
let startX, scrollLeftStart;

function updateCounter() {
    if (currentImageSpan) {
        currentImageSpan.textContent = currentIndex + 1;
    }
}

function scrollToImage(index) {
    if (index < 0) index = 0;
    if (index >= galleryItems.length) index = galleryItems.length - 1;
    
    if (galleryItems[index]) {
        galleryItems[index].scrollIntoView({
            behavior: 'smooth',
            block: 'nearest',
            inline: 'center'
        });
        currentIndex = index;
        updateCounter();
    }
}

function scrollLeft() {
    scrollToImage(currentIndex > 0 ? currentIndex - 1 : galleryItems.length - 1);
}

function scrollRight() {
    scrollToImage(currentIndex < galleryItems.length - 1 ? currentIndex + 1 : 0);
}

// Drag to scroll
galleryScroll.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.pageX - galleryScroll.offsetLeft;
    scrollLeftStart = galleryScroll.scrollLeft;
    galleryScroll.style.cursor = 'grabbing';
});

galleryScroll.addEventListener('mouseleave', () => {
    isDragging = false;
    galleryScroll.style.cursor = 'grab';
});

galleryScroll.addEventListener('mouseup', () => {
    isDragging = false;
    galleryScroll.style.cursor = 'grab';
});

galleryScroll.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - galleryScroll.offsetLeft;
    const walk = (x - startX) * 2;
    galleryScroll.scrollLeft = scrollLeftStart - walk;
});

// Touch events
galleryScroll.addEventListener('touchstart', (e) => {
    isDragging = true;
    startX = e.touches[0].pageX - galleryScroll.offsetLeft;
    scrollLeftStart = galleryScroll.scrollLeft;
});

galleryScroll.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.touches[0].pageX - galleryScroll.offsetLeft;
    const walk = (x - startX) * 2;
    galleryScroll.scrollLeft = scrollLeftStart - walk;
});

galleryScroll.addEventListener('touchend', () => {
    isDragging = false;
});

// Gallery item clicks
galleryItems.forEach((item, i) => {
    item.addEventListener('click', () => scrollToImage(i));
});

// Update index on scroll
galleryScroll.addEventListener('scroll', () => {
    const itemWidth = galleryItems[0]?.offsetWidth || 0;
    const newIndex = Math.round(galleryScroll.scrollLeft / (itemWidth + 20));
    if (newIndex !== currentIndex && newIndex >= 0 && newIndex < galleryItems.length) {
        currentIndex = newIndex;
        updateCounter();
    }
});

// Button clicks
if (scrollLeftBtn) scrollLeftBtn.addEventListener('click', scrollLeft);
if (scrollRightBtn) scrollRightBtn.addEventListener('click', scrollRight);

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') scrollLeft();
    if (e.key === 'ArrowRight') scrollRight();
});

galleryScroll.style.cursor = 'grab';

// Sticky booking card
const bookingCard = document.getElementById('bookingCard');
window.addEventListener('scroll', () => {
    const heroHeight = document.querySelector('.room-hero').offsetHeight;
    bookingCard.classList.toggle('sticky', window.scrollY > heroHeight - 100);
});

// Booking form elements
const checkIn = document.getElementById('checkIn');
const checkOut = document.getElementById('checkOut');
const guests = document.getElementById('guests');
const nightsCount = document.getElementById('nightsCount');
const totalPrice = document.getElementById('totalPrice');
const bookNowBtn = document.getElementById('bookNowBtn');
const bookingModal = document.getElementById('bookingModal');
const successModal = document.getElementById('successModal');

// Update booking summary
function updateBookingSummary() {
    const nights = Math.ceil((new Date(checkOut.value) - new Date(checkIn.value)) / 86400000);
    const total = roomData.price * nights;
    nightsCount.textContent = nights;
    totalPrice.textContent = 'RM' + total.toLocaleString();
}

// Date validation
checkIn.addEventListener('change', () => {
    let checkInDate = new Date(checkIn.value);
    let checkOutDate = new Date(checkOut.value);
    
    if (checkOutDate <= checkInDate) {
        let nextDay = new Date(checkInDate);
        nextDay.setDate(nextDay.getDate() + 1);
        checkOut.value = nextDay.toISOString().split('T')[0];
    }
    updateBookingSummary();
});

checkOut.addEventListener('change', () => {
    let checkInDate = new Date(checkIn.value);
    let checkOutDate = new Date(checkOut.value);
    
    if (checkOutDate <= checkInDate) {
        alert('Check-out date must be after check-in date.');
        let nextDay = new Date(checkInDate);
        nextDay.setDate(nextDay.getDate() + 1);
        checkOut.value = nextDay.toISOString().split('T')[0];
    }
    updateBookingSummary();
});

guests.addEventListener('change', updateBookingSummary);

// Process booking
if (bookNowBtn) {
    bookNowBtn.addEventListener('click', () => {
        const nights = Math.ceil((new Date(checkOut.value) - new Date(checkIn.value)) / 86400000);
        const total = roomData.price * nights;
        
        document.getElementById('modalBookingSummary').innerHTML = `
            <p><strong><i class="fas fa-hotel"></i> Room:</strong> ${roomData.name}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-in:</strong> ${new Date(checkIn.value).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-out:</strong> ${new Date(checkOut.value).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-moon"></i> Nights:</strong> ${nights}</p>
            <p><strong><i class="fas fa-users"></i> Guests:</strong> ${guests.value}</p>
            <p><strong><i class="fas fa-tag"></i> Price per night:</strong> RM${roomData.price.toLocaleString()}</p>
            <p class="total-price"><strong>Total:</strong> RM${total.toLocaleString()}</p>
        `;
        bookingModal.style.display = 'flex';
    });
}

// Modal handlers
document.getElementById('closeModalBtn')?.addEventListener('click', () => bookingModal.style.display = 'none');
document.getElementById('cancelModalBtn')?.addEventListener('click', () => bookingModal.style.display = 'none');
document.getElementById('confirmBookingBtn')?.addEventListener('click', () => {
    bookingModal.style.display = 'none';
    successModal.style.display = 'flex';
    setTimeout(() => successModal.style.display = 'none', 5000);
});
document.getElementById('closeSuccessBtn')?.addEventListener('click', () => successModal.style.display = 'none');
document.getElementById('continueBtn')?.addEventListener('click', () => successModal.style.display = 'none');

// Close modal on outside click
window.addEventListener('click', (e) => {
    if (e.target === bookingModal) bookingModal.style.display = 'none';
    if (e.target === successModal) successModal.style.display = 'none';
});

// Initialize
updateBookingSummary();

 // Sticky header
window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
</script>

<?php include '../Shared/footer.php'; ?>
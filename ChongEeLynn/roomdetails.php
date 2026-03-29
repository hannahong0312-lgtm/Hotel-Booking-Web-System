<?php
// roomdetails.php - Individual Room Details Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include header
include '../Shared/header.php';

// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "hotel_booking");
if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

// 2. GET ROOM ID FROM URL
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($room_id <= 0) {
    header('Location: accommodation.php');
    exit;
}

// 3. FETCH SPECIFIC ROOM DATA
$sql = "SELECT * FROM room WHERE id = $room_id";
$result = $conn->query($sql);
$room = $result->fetch_assoc();

// If room not found in DB, redirect
if(!$room) {
    header('Location: accommodation.php');
    exit;
}

// 4. DECODE JSON DATA (Crucial for gallery and amenities)
$room['amenities'] = json_decode($room['amenities'], true);
$room['images'] = json_decode($room['images'], true);

// 5. FETCH SIMILAR ROOMS (For the "You might also like" section)
$similar_rooms = [];
$sim_sql = "SELECT * FROM room WHERE id != $room_id LIMIT 3";
$sim_result = $conn->query($sim_sql);

if ($sim_result->num_rows > 0) {
    while($s_row = $sim_result->fetch_assoc()) {
        // Decode images for the similar room thumbnails
        $s_row['images'] = json_decode($s_row['images'], true);
        $similar_rooms[] = $s_row;
    }
}

// 6. GET DEFAULT DATES
$today = date('Y-m-d');
$tomorrow_date = date('Y-m-d', strtotime('+1 day'));
?>

<link rel="stylesheet" href="css/roomdetails.css">

<main>
    <!-- Hero Section with Full Screen Image Gallery -->
    <div class="room-hero">
        <div class="hero-overlay"></div>
        <div class="gallery-container">
            <div class="gallery-wrapper">
                <div class="gallery-scroll" id="galleryScroll">
                    <?php for($i = 0; $i < count($room['images']); $i++): ?>
                        <div class="gallery-item" data-index="<?php echo $i; ?>">
                            <img src="<?php echo $room['images'][$i]; ?>" alt="<?php echo htmlspecialchars($room['name']); ?> - Image <?php echo $i + 1; ?>">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Scroll Buttons -->
            <button class="scroll-btn scroll-left" id="scrollLeftBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="scroll-btn scroll-right" id="scrollRightBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Image Counter -->
            <div class="image-counter" id="imageCounter">
                <span id="currentImageIndex">1</span> / <span id="totalImages"><?php echo count($room['images']); ?></span>
            </div>
        </div>
        
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($room['name']); ?></h1>
            <div class="hero-meta">
                <span class="room-type-badge"><?php echo strtoupper($room['type']); ?></span>
                <?php if($room['popular']): ?>
                    <span class="popular-badge"><i class="fas fa-star"></i> Most Popular</span>
                <?php endif; ?>
                <div class="rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <span>4.8 (245 reviews)</span>
                </div>
            </div>
            <div class="hero-price">
                <span class="price">RM<?php echo number_format($room['price'], 0); ?></span>
                <span class="per-night">per night</span>
            </div>
            <div class="scroll-indicator">
                <span>Scroll to explore details</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Quick Links Navigation -->
        <div class="quick-links">
            <a href="accommodation.php" class="quick-link">
                <i class="fas fa-arrow-left"></i> Back to All Rooms
            </a>
        </div>

        <!-- Content Grid -->
        <div class="details-grid">
            <!-- Left Column - Room Information -->
            <div class="info-section">
                <div id="overview" class="info-card">
                    <h2><i class="fas fa-info-circle"></i> Overview</h2>
                    <p class="description"><?php echo htmlspecialchars($room['long_description']); ?></p>
                    
                    <div class="specs">
                        <div class="spec-item">
                            <i class="fas fa-bed"></i>
                            <div>
                                <strong>Bed Type</strong>
                                <span><?php echo $room['bed_type']; ?></span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-arrows-alt"></i>
                            <div>
                                <strong>Room Size</strong>
                                <span><?php echo $room['size']; ?></span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-eye"></i>
                            <div>
                                <strong>View</strong>
                                <span><?php echo $room['view']; ?></span>
                            </div>
                        </div>
                        <div class="spec-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Max Occupancy</strong>
                                <span><?php echo $room['capacity']; ?> guests</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="amenities" class="info-card">
                    <h2><i class="fas fa-concierge-bell"></i> Amenities</h2>
                    <div class="amenities-grid">
                        <?php for($i = 0; $i < count($room['amenities']); $i++): ?>
                            <div class="amenity-item">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo htmlspecialchars($room['amenities'][$i]); ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

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
                <?php if(count($similar_rooms) > 0): ?>
                <div id="similar" class="similar-rooms">
                    <h3><i class="fas fa-hotel"></i> You might also like</h3>
                    <div class="similar-grid">
                        <?php for($i = 0; $i < count($similar_rooms); $i++): ?>
                            <a href="roomdetails.php?id=<?php echo $similar_rooms[$i]['id']; ?>" class="similar-card">
                                <img src="<?php echo $similar_rooms[$i]['images'][0]; ?>" alt="<?php echo htmlspecialchars($similar_rooms[$i]['name']); ?>">
                                <div class="similar-info">
                                    <h4><?php echo htmlspecialchars($similar_rooms[$i]['name']); ?></h4>
                                    <p>From RM<?php echo number_format($similar_rooms[$i]['price'], 0); ?>/night</p>
                                    <small><i class="fas fa-users"></i> Up to <?php echo $similar_rooms[$i]['capacity']; ?> guests</small>
                                </div>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Sticky Booking Form -->
            <div class="booking-section-wrapper">
                <div class="booking-card" id="bookingCard">
                    <div class="price-display">
                        <span class="price">RM<?php echo number_format($room['price'], 0); ?></span>
                        <span class="per-night">per night</span>
                    </div>
                    
                    <div class="availability-status">
                        <?php if($room['available'] > 2): ?>
                            <span class="status available"><i class="fas fa-check-circle"></i> Available</span>
                        <?php elseif($room['available'] > 0): ?>
                            <span class="status limited"><i class="fas fa-exclamation-circle"></i> Only <?php echo $room['available']; ?> rooms left!</span>
                        <?php else: ?>
                            <span class="status soldout"><i class="fas fa-times-circle"></i> Sold Out</span>
                        <?php endif; ?>
                    </div>

                    <form id="bookingForm">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Check-in Date</label>
                            <input type="date" id="checkIn" name="checkIn" value="<?php echo $today; ?>" min="<?php echo $today; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Check-out Date</label>
                            <input type="date" id="checkOut" name="checkOut" value="<?php echo $tomorrow_date; ?>" min="<?php echo $tomorrow_date; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Number of Guests</label>
                            <select id="guests" name="guests" required>
                                <?php for($i = 1; $i <= $room['capacity']; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php if($i > 1) echo 's'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="booking-summary" id="bookingSummary">
                            <div class="summary-line">
                                <span>Price per night:</span>
                                <span>RM<?php echo number_format($room['price'], 0); ?></span>
                            </div>
                            <div class="summary-line" id="nightsLine">
                                <span>Number of nights:</span>
                                <span id="nightsCount">1</span>
                            </div>
                            <div class="summary-line total">
                                <span>Total:</span>
                                <span id="totalPrice">RM<?php echo number_format($room['price'], 0); ?></span>
                            </div>
                        </div>
                        
                        <?php if($room['available'] > 0): ?>
                            <button type="button" id="bookNowBtn" class="btn btn-book-now">
                                <i class="fas fa-check-circle"></i> Book Now
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-book-now" disabled>
                                <i class="fas fa-times-circle"></i> Currently Unavailable
                            </button>
                        <?php endif; ?>
                    </form>
                    
                    <div class="secure-booking">
                        <i class="fas fa-lock"></i> Secure booking, guaranteed best rates
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Booking Confirmation Modal -->
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

<!-- Success Modal -->
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
    // Store room data
    const roomData = {
        name: <?php echo json_encode($room['name']); ?>,
        price: <?php echo $room['price']; ?>
    };
    
    // Gallery scroll functionality with both drag and buttons
    const galleryScroll = document.getElementById('galleryScroll');
    const scrollLeftBtn = document.getElementById('scrollLeftBtn');
    const scrollRightBtn = document.getElementById('scrollRightBtn');
    const currentImageIndexSpan = document.getElementById('currentImageIndex');
    const totalImagesSpan = document.getElementById('totalImages');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    let currentIndex = 0;
    const totalImages = galleryItems.length;
    let isDragging = false;
    let startX;
    let scrollLeftStart;
    
    // Update counter
    function updateCounter() {
        if(currentImageIndexSpan) {
            currentImageIndexSpan.textContent = currentIndex + 1;
        }
    }
    
    // Scroll to specific image
    function scrollToImage(index) {
        if(index < 0) index = 0;
        if(index >= totalImages) index = totalImages - 1;
        
        const item = galleryItems[index];
        if(item) {
            item.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
                inline: 'center'
            });
            currentIndex = index;
            updateCounter();
        }
    }
    
    // Scroll left
    function scrollLeft() {
        if(currentIndex > 0) {
            scrollToImage(currentIndex - 1);
        } else {
            scrollToImage(totalImages - 1);
        }
    }
    
    // Scroll right
    function scrollRight() {
        if(currentIndex < totalImages - 1) {
            scrollToImage(currentIndex + 1);
        } else {
            scrollToImage(0);
        }
    }
    
    // Mouse/Touch drag scrolling
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
        if(!isDragging) return;
        e.preventDefault();
        const x = e.pageX - galleryScroll.offsetLeft;
        const walk = (x - startX) * 2;
        galleryScroll.scrollLeft = scrollLeftStart - walk;
    });
    
    // Touch events for mobile
    galleryScroll.addEventListener('touchstart', (e) => {
        isDragging = true;
        startX = e.touches[0].pageX - galleryScroll.offsetLeft;
        scrollLeftStart = galleryScroll.scrollLeft;
    });
    
    galleryScroll.addEventListener('touchmove', (e) => {
        if(!isDragging) return;
        e.preventDefault();
        const x = e.touches[0].pageX - galleryScroll.offsetLeft;
        const walk = (x - startX) * 2;
        galleryScroll.scrollLeft = scrollLeftStart - walk;
    });
    
    galleryScroll.addEventListener('touchend', () => {
        isDragging = false;
    });
    
    // Add click handlers for gallery items
    for(let i = 0; i < galleryItems.length; i++) {
        galleryItems[i].addEventListener('click', function() {
            scrollToImage(i);
        });
    }
    
    // Add scroll event listener to update current index
    galleryScroll.addEventListener('scroll', function() {
        const scrollLeft = galleryScroll.scrollLeft;
        const itemWidth = galleryItems[0]?.offsetWidth || 0;
        const gap = 20; // Gap between items
        const newIndex = Math.round(scrollLeft / (itemWidth + gap));
        
        if(newIndex !== currentIndex && newIndex >= 0 && newIndex < totalImages) {
            currentIndex = newIndex;
            updateCounter();
        }
    });
    
    // Button click handlers
    if(scrollLeftBtn) scrollLeftBtn.addEventListener('click', scrollLeft);
    if(scrollRightBtn) scrollRightBtn.addEventListener('click', scrollRight);
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if(e.key === 'ArrowLeft') {
            scrollLeft();
        } else if(e.key === 'ArrowRight') {
            scrollRight();
        }
    });
    
    // Set initial cursor
    galleryScroll.style.cursor = 'grab';
    
    // Sticky booking card
    const bookingCard = document.getElementById('bookingCard');
    const bookingSectionWrapper = document.querySelector('.booking-section-wrapper');
    
    function handleStickyBooking() {
        const scrollY = window.scrollY;
        const windowHeight = window.innerHeight;
        const heroHeight = document.querySelector('.room-hero').offsetHeight;
        const containerTop = document.querySelector('.container').offsetTop;
        
        if(scrollY > heroHeight - 100) {
            bookingCard.classList.add('sticky');
        } else {
            bookingCard.classList.remove('sticky');
        }
    }
    
    window.addEventListener('scroll', handleStickyBooking);
    window.addEventListener('load', handleStickyBooking);
    
    // Smooth scroll for quick links
    const quickLinks = document.querySelectorAll('.quick-link[href^="#"]');
    for(let i = 0; i < quickLinks.length; i++) {
        quickLinks[i].addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
    
    // Get DOM elements for booking
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');
    const guestsSelect = document.getElementById('guests');
    const nightsCountSpan = document.getElementById('nightsCount');
    const totalPriceSpan = document.getElementById('totalPrice');
    const bookNowBtn = document.getElementById('bookNowBtn');
    const bookingModal = document.getElementById('bookingModal');
    const successModal = document.getElementById('successModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const confirmBookingBtn = document.getElementById('confirmBookingBtn');
    const closeSuccessBtn = document.getElementById('closeSuccessBtn');
    const continueBtn = document.getElementById('continueBtn');
    const modalBookingSummary = document.getElementById('modalBookingSummary');
    
    // Update booking summary function
    function updateBookingSummary() {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);
        const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        const totalPrice = roomData.price * nights;
        
        nightsCountSpan.textContent = nights;
        totalPriceSpan.textContent = '$' + totalPrice.toLocaleString();
    }
    
    // Process booking
    function processBooking() {
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        const guests = guestsSelect.value;
        const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
        const totalPrice = roomData.price * nights;
        
        const modalContent = `
            <p><strong><i class="fas fa-hotel"></i> Room:</strong> ${roomData.name}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-in:</strong> ${new Date(checkIn).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-out:</strong> ${new Date(checkOut).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-moon"></i> Nights:</strong> ${nights}</p>
            <p><strong><i class="fas fa-users"></i> Guests:</strong> ${guests}</p>
            <p><strong><i class="fas fa-tag"></i> Price per night:</strong> $${roomData.price.toLocaleString()}</p>
            <p class="total-price"><strong>Total:</strong> $${totalPrice.toLocaleString()}</p>
        `;
        
        modalBookingSummary.innerHTML = modalContent;
        bookingModal.style.display = 'flex';
    }
    
    // Confirm booking
    function confirmBooking() {
        bookingModal.style.display = 'none';
        successModal.style.display = 'flex';
        
        setTimeout(function() {
            successModal.style.display = 'none';
        }, 5000);
    }
    
    // Close modals
    function closeModal() {
        bookingModal.style.display = 'none';
    }
    
    function closeSuccessModal() {
        successModal.style.display = 'none';
    }
    
    // Event listeners for booking
    if(checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const checkOutDate = new Date(checkOutInput.value);
            
            if(checkOutDate <= checkInDate) {
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.value = nextDay.toISOString().split('T')[0];
            }
            updateBookingSummary();
        });
        
        checkOutInput.addEventListener('change', function() {
            const checkInDate = new Date(checkInInput.value);
            const checkOutDate = new Date(this.value);
            
            if(checkOutDate <= checkInDate) {
                alert('Check-out date must be after check-in date.');
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                this.value = nextDay.toISOString().split('T')[0];
            }
            updateBookingSummary();
        });
    }
    
    if(guestsSelect) {
        guestsSelect.addEventListener('change', updateBookingSummary);
    }
    
    if(bookNowBtn) {
        bookNowBtn.addEventListener('click', processBooking);
    }
    
    if(closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    if(cancelModalBtn) {
        cancelModalBtn.addEventListener('click', closeModal);
    }
    
    if(confirmBookingBtn) {
        confirmBookingBtn.addEventListener('click', confirmBooking);
    }
    
    if(closeSuccessBtn) {
        closeSuccessBtn.addEventListener('click', closeSuccessModal);
    }
    
    if(continueBtn) {
        continueBtn.addEventListener('click', closeSuccessModal);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if(event.target === bookingModal) {
            closeModal();
        }
        if(event.target === successModal) {
            closeSuccessModal();
        }
    });
    
    // Initialize booking summary
    updateBookingSummary();
</script>

<?php include '../Shared/footer.php'; ?>
<?php
// accommodation.php - Main Accommodation Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "hotel_booking");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM room");
$rooms = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['amenities'] = json_decode($row['amenities'], true);
        $rooms[] = $row;
    }
}

include '../Shared/header.php';

// Get unique room types
$room_types = array_unique(array_column($rooms, 'type'));
?>

<link rel="stylesheet" href="css/accommodation.css">

<main>
    <!-- Hero Section -->
    <div class="accommodation-hero">
        <div class="hero-overlay"></div>
        <div class="hero-bg-image"></div>
        <div class="hero-container">
            <div class="hero-content">
                <h1>Luxury Accommodations</h1>
                <p>Experience unparalleled comfort and elegance in our carefully designed rooms and suites</p>
                <div class="hero-buttons">
                    <a href="#filter-section" class="btn btn-primary">View Rooms</a>
                    <a href="#rooms-section" class="btn btn-outline">Explore Suites</a>
                </div>
            </div>
            <div class="scroll-indicator">
                <span>Scroll to explore</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Filter Section -->
        <section id="filter-section" class="filter-section">
            <div style="display: flex; justify-content: center; align-items: center;">
                <h2><i class="fas fa-search"></i> Find Your Perfect Stay</h2>
            </div>
            <div class="filter-grid">
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> Check-in</label>
                    <input type="date" id="checkIn" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> Check-out</label>
                    <input type="date" id="checkOut" class="form-control" value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-users"></i> Guests</label>
                    <select id="guests" class="form-control">
                        <?php for($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == 2 ? 'selected' : '' ?>><?= $i ?> Guest<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                        <option value="7">6+ Guests</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-tag"></i> Max Price / Night</label>
                    <input type="range" id="priceRange" min="50" max="1500" value="1500" step="10">
                    <div class="price-range-value">
                        <span>RM50</span>
                        <span id="priceValue">RM1500</span>
                        <span>RM1500</span>
                    </div>
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-door-open"></i> Room Type</label>
                    <select id="roomType" class="form-control">
                        <option value="all">All Types</option>
                        <?php foreach($room_types as $type): ?>
                            <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <button class="btn btn-primary btn-search" id="searchButton">
                        <i class="fas fa-search"></i> Search Rooms
                    </button>
                </div>
            </div>
        </section>

        <!-- Loading Indicator -->
        <div id="loading" class="loading" style="display: none;">
            <div class="spinner"></div>
            <p>Finding the best rooms for you...</p>
        </div>

        <!-- Rooms Grid -->
        <section id="rooms-section">
            <div class="section-header">
                <h2>Choose Your Perfect Stay</h2>
                <p>Each room is thoughtfully designed with your comfort in mind</p>
            </div>
            
            <div class="room-grid" id="roomGrid">
                <?php foreach($rooms as $room): ?>
                    <div class="room-card fade-in" 
                         data-id="<?= $room['id'] ?>"
                         data-price="<?= $room['price'] ?>"
                         data-type="<?= $room['type'] ?>"
                         data-capacity="<?= $room['capacity'] ?>">
                        
                        <div class="room-image" style="background-image: url('<?= $room['image'] ?>')">
                            <div class="price-badge">RM<?= number_format($room['price'], 0) ?><span>/night</span></div>
                            <?php if($room['popular']): ?>
                                <div class="popular-badge"><i class="fas fa-star"></i> Most Popular</div>
                            <?php endif; ?>
                            <div class="availability-badge <?= $room['available'] > 2 ? 'available' : ($room['available'] > 0 ? 'limited' : 'soldout') ?>">
                                <?php if($room['available'] > 2): ?>
                                    <i class="fas fa-check-circle"></i> Available
                                <?php elseif($room['available'] > 0): ?>
                                    <i class="fas fa-exclamation-circle"></i> Only <?= $room['available'] ?> left!
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i> Sold Out
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="room-content">
                            <h3 class="room-title"><?= htmlspecialchars($room['name']) ?></h3>
                            <div class="room-type"><?= strtoupper($room['type']) ?></div>
                            <p class="room-description"><?= htmlspecialchars($room['description']) ?></p>
                            
                            <div class="room-features">
                                <div class="feature"><i class="fas fa-bed"></i><span><?= $room['bed_type'] ?></span></div>
                                <div class="feature"><i class="fas fa-arrows-alt"></i><span><?= $room['size'] ?></span></div>
                                <div class="feature"><i class="fas fa-eye"></i><span><?= $room['view'] ?></span></div>
                                <div class="feature"><i class="fas fa-users"></i><span>Up to <?= $room['capacity'] ?></span></div>
                            </div>
                            
                            <div class="amenities">
                                <?php 
                                $amenitiesCount = count($room['amenities']);
                                $displayCount = min(4, $amenitiesCount);
                                for($a = 0; $a < $displayCount; $a++): 
                                ?>
                                    <span class="amenity-tag"><i class="fas fa-check-circle"></i> <?= $room['amenities'][$a] ?></span>
                                <?php endfor; ?>
                                <?php if($amenitiesCount > 4): ?>
                                    <span class="amenity-tag"><i class="fas fa-plus-circle"></i> +<?= $amenitiesCount - 4 ?> more</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="room-footer">
                                <div class="room-price">RM<?= number_format($room['price'], 0) ?><span>/night</span></div>
                                <?php if($room['available'] > 0): ?>
                                    <button class="btn btn-primary btn-book" data-room-id="<?= $room['id'] ?>">Book Now <i class="fas fa-arrow-right"></i></button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-book" disabled>Unavailable</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item"><div class="stat-number">200+</div><div class="stat-label">Luxury Rooms</div></div>
            <div class="stat-item"><div class="stat-number">4.8</div><div class="stat-label">Guest Rating</div></div>
            <div class="stat-item"><div class="stat-number">15</div><div class="stat-label">Suite Options</div></div>
            <div class="stat-item"><div class="stat-number">24/7</div><div class="stat-label">Concierge Service</div></div>
        </div>
    </div>
</section>

<!-- Booking Modal -->
<div id="bookingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <h2><i class="fas fa-calendar-check"></i> Booking Summary</h2>
        <div id="bookingSummary"></div>
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
        <p>Thank you for your booking. We'll send you a confirmation email shortly.</p>
        <button class="btn btn-primary" id="continueBtn">Continue</button>
    </div>
</div>

<script>
    const roomsData = <?= json_encode($rooms) ?>;
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const searchButton = document.getElementById('searchButton');
    const loadingDiv = document.getElementById('loading');
    const roomGrid = document.getElementById('roomGrid');
    const bookingModal = document.getElementById('bookingModal');
    const successModal = document.getElementById('successModal');
    const checkInInput = document.getElementById('checkIn');
    const checkOutInput = document.getElementById('checkOut');
    let currentRoom = null;
    
    priceRange?.addEventListener('input', () => priceValue.textContent = 'RM' + priceRange.value);
    
    function filterRooms() {
        const maxPrice = parseInt(document.getElementById('priceRange').value);
        const roomType = document.getElementById('roomType').value;
        const guests = parseInt(document.getElementById('guests').value);
        
        loadingDiv.style.display = 'block';
        roomGrid.style.opacity = '0.5';
        
        setTimeout(() => {
            const cards = document.querySelectorAll('.room-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const price = parseInt(card.dataset.price);
                const type = card.dataset.type;
                const capacity = parseInt(card.dataset.capacity);
                const matches = price <= maxPrice && (roomType === 'all' || type === roomType) && capacity >= guests;
                
                card.style.display = matches ? 'block' : 'none';
                if(matches) visibleCount++;
            });
            
            loadingDiv.style.display = 'none';
            roomGrid.style.opacity = '1';
            
            let noResultsMsg = document.querySelector('.no-results-message');
            if(visibleCount === 0 && !noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-message';
                noResultsMsg.innerHTML = `<i class="fas fa-search"></i><h3>No Rooms Found</h3><p>Try adjusting your filters to see more options.</p>`;
                roomGrid.appendChild(noResultsMsg);
            } else if(visibleCount > 0 && noResultsMsg) {
                noResultsMsg.remove();
            }
        }, 500);
    }
    
    function openBookingModal(roomId) {
        currentRoom = roomsData.find(room => room.id === roomId);
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        const guests = document.getElementById('guests').value;
        
        const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
        const totalPrice = currentRoom.price * nights;
        
        document.getElementById('bookingSummary').innerHTML = `
            <p><strong><i class="fas fa-hotel"></i> Room:</strong> ${currentRoom.name}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-in:</strong> ${new Date(checkIn).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-out:</strong> ${new Date(checkOut).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-moon"></i> Nights:</strong> ${nights}</p>
            <p><strong><i class="fas fa-users"></i> Guests:</strong> ${guests}</p>
            <p><strong><i class="fas fa-tag"></i> Price per night:</strong> RM${currentRoom.price}</p>
            <p class="total-price"><strong>Total:</strong> RM${totalPrice}</p>
        `;
        bookingModal.style.display = 'flex';
    }
    
    function closeModal() { bookingModal.style.display = 'none'; }
    function closeSuccessModal() { successModal.style.display = 'none'; }
    function confirmBooking() { closeModal(); successModal.style.display = 'flex'; setTimeout(closeSuccessModal, 5000); }
    
    // Fade animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if(entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    
    // Event Listeners
    searchButton?.addEventListener('click', filterRooms);
    
    document.querySelectorAll('.room-card').forEach(card => {
        card.addEventListener('click', (e) => {
            if(!e.target.classList?.contains('btn-book')) {
                window.location.href = 'roomdetails.php?id=' + card.dataset.id;
            }
        });
    });
    
    document.querySelectorAll('.btn-book:not([disabled])').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            openBookingModal(parseInt(btn.closest('.room-card').dataset.id));
        });
    });
    
    // Modal listeners
    ['closeModalBtn', 'cancelModalBtn'].forEach(id => 
        document.getElementById(id)?.addEventListener('click', closeModal));
    document.getElementById('confirmBookingBtn')?.addEventListener('click', confirmBooking);
    ['closeSuccessBtn', 'continueBtn'].forEach(id => 
        document.getElementById(id)?.addEventListener('click', closeSuccessModal));
    
    window.addEventListener('click', (e) => {
        if(e.target === bookingModal) closeModal();
        if(e.target === successModal) closeSuccessModal();
    });
    
    checkInInput?.addEventListener('change', () => {
        if(new Date(checkOutInput.value) <= new Date(checkInInput.value)) {
            const nextDay = new Date(checkInInput.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.value = nextDay.toISOString().split('T')[0];
        }
    });
    
    // Smooth scroll
    document.querySelectorAll('.hero-buttons a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelector(link.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
        });
    });
</script>

<?php include '../Shared/footer.php'; ?>
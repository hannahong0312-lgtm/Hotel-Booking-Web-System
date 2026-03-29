<?php
// accommodation.php - Main Accommodation Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manual room data array - no external generators used
$rooms = [
    [
        'id' => 1,
        'name' => 'Deluxe Ocean View',
        'type' => 'deluxe',
        'description' => 'Experience luxury with breathtaking ocean views from your private balcony. Features king-size bed, marble bathroom, and premium amenities.',
        'price' => 299,
        'capacity' => 2,
        'bed_type' => 'King Size Bed',
        'size' => '45 m² / 484 ft²',
        'view' => 'Ocean View',
        'amenities' => ['King Bed', 'Ocean View', 'Private Balcony', 'Mini Bar', 'WiFi', 'Smart TV', 'Rain Shower'],
        'image' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=800&h=600&fit=crop',
        'available' => 3,
        'popular' => true
    ],
    [
        'id' => 2,
        'name' => 'Executive Suite',
        'type' => 'suite',
        'description' => 'Spacious suite with separate living and dining areas. Perfect for business travelers or families seeking extra space.',
        'price' => 499,
        'capacity' => 4,
        'bed_type' => 'King Bed + Sofa Bed',
        'size' => '75 m² / 807 ft²',
        'view' => 'City View',
        'amenities' => ['King Bed', 'Living Room', 'Dining Area', 'Jacuzzi', 'Kitchenette', 'WiFi', '65" TV'],
        'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800&h=600&fit=crop',
        'available' => 2,
        'popular' => true
    ],
    [
        'id' => 3,
        'name' => 'Standard Twin Room',
        'type' => 'standard',
        'description' => 'Comfortable room with two twin beds. Ideal for friends, colleagues, or solo travelers.',
        'price' => 149,
        'capacity' => 2,
        'bed_type' => '2 Twin Beds',
        'size' => '30 m² / 323 ft²',
        'view' => 'Garden View',
        'amenities' => ['Twin Beds', 'Work Desk', 'Flat Screen TV', 'WiFi', 'Coffee Maker'],
        'image' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800&h=600&fit=crop',
        'available' => 5,
        'popular' => false
    ],
    [
        'id' => 4,
        'name' => 'Family Suite',
        'type' => 'family',
        'description' => 'Designed for families with connecting rooms and child-friendly amenities. Plenty of space for everyone.',
        'price' => 399,
        'capacity' => 5,
        'bed_type' => 'Queen + 2 Singles',
        'size' => '65 m² / 700 ft²',
        'view' => 'Pool View',
        'amenities' => ['2 Bedrooms', 'Kids Corner', 'Kitchen', 'Game Console', 'WiFi', 'DVD Player'],
        'image' => 'https://images.unsplash.com/photo-1568495248636-6432b97bd949?w=800&h=600&fit=crop',
        'available' => 2,
        'popular' => true
    ],
    [
        'id' => 5,
        'name' => 'Presidential Penthouse',
        'type' => 'suite',
        'description' => 'Ultimate luxury with panoramic views, private rooftop terrace, and 24/7 butler service.',
        'price' => 1299,
        'capacity' => 6,
        'bed_type' => 'Super King + 2 Doubles',
        'size' => '150 m² / 1615 ft²',
        'view' => 'Panoramic City',
        'amenities' => ['Super King Bed', 'Private Rooftop', 'Butler Service', 'Private Pool', 'Home Theater'],
        'image' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop',
        'available' => 1,
        'popular' => true
    ],
    [
        'id' => 6,
        'name' => 'Garden View Room',
        'type' => 'standard',
        'description' => 'Peaceful room overlooking lush tropical gardens with private patio for morning coffee.',
        'price' => 189,
        'capacity' => 2,
        'bed_type' => 'Queen Size Bed',
        'size' => '35 m² / 377 ft²',
        'view' => 'Garden View',
        'amenities' => ['Queen Bed', 'Private Patio', 'Garden Access', 'WiFi', 'Mini Fridge'],
        'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800&h=600&fit=crop',
        'available' => 4,
        'popular' => false
    ]
];

include '../Shared/header.php';

$room_types = [];
for($i = 0; $i < count($rooms); $i++) {
    $found = false;
    for($j = 0; $j < count($room_types); $j++) {
        if($room_types[$j] == $rooms[$i]['type']) {
            $found = true;
            break;
        }
    }
    if(!$found) {
        $room_types[] = $rooms[$i]['type'];
    }
}
?>

<link rel="stylesheet" href="css/accommodation.css">

<!-- Main Content -->
<main>
    <!-- Hero Section with Full Screen Background Image -->
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
                    <input type="date" id="checkIn" class="form-control" value="<?php 
                        $today = date('Y-m-d');
                        echo $today;
                    ?>">
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> Check-out</label>
                    <input type="date" id="checkOut" class="form-control" value="<?php 
                        $tomorrow = date('Y-m-d', strtotime('+2 days'));
                        echo $tomorrow;
                    ?>">
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-users"></i> Guests</label>
                    <select id="guests" class="form-control">
                        <option value="1">1 Guest</option>
                        <option value="2" selected>2 Guests</option>
                        <option value="3">3 Guests</option>
                        <option value="4">4 Guests</option>
                        <option value="5">5 Guests</option>
                        <option value="6">6+ Guests</option>
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
                        <?php 
                        for($i = 0; $i < count($room_types); $i++): 
                            $typeDisplay = ucfirst($room_types[$i]);
                            echo "<option value=\"" . $room_types[$i] . "\">" . $typeDisplay . "</option>";
                        endfor; 
                        ?>
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
                <?php for($i = 0; $i < count($rooms); $i++): 
                    $room = $rooms[$i];
                ?>
                    <div class="room-card fade-in" 
                         data-id="<?php echo $room['id']; ?>"
                         data-price="<?php echo $room['price']; ?>"
                         data-type="<?php echo $room['type']; ?>"
                         data-capacity="<?php echo $room['capacity']; ?>">
                        
                        <div class="room-image" style="background-image: url('<?php echo $room['image']; ?>')">
                            <div class="price-badge">$<?php echo number_format($room['price'], 0); ?><span>/night</span></div>
                            <?php if($room['popular']): ?>
                                <div class="popular-badge"><i class="fas fa-star"></i> Most Popular</div>
                            <?php endif; ?>
                            <div class="availability-badge <?php 
                                if($room['available'] > 2) {
                                    echo 'available';
                                } elseif($room['available'] > 0) {
                                    echo 'limited';
                                } else {
                                    echo 'soldout';
                                }
                            ?>">
                                <?php if($room['available'] > 2): ?>
                                    <i class="fas fa-check-circle"></i> Available
                                <?php elseif($room['available'] > 0): ?>
                                    <i class="fas fa-exclamation-circle"></i> Only <?php echo $room['available']; ?> left!
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i> Sold Out
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="room-content">
                            <h3 class="room-title"><?php echo htmlspecialchars($room['name']); ?></h3>
                            <div class="room-type"><?php echo strtoupper($room['type']); ?></div>
                            <p class="room-description"><?php echo htmlspecialchars($room['description']); ?></p>
                            
                            <div class="room-features">
                                <div class="feature">
                                    <i class="fas fa-bed"></i>
                                    <span><?php echo $room['bed_type']; ?></span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-arrows-alt"></i>
                                    <span><?php echo $room['size']; ?></span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-eye"></i>
                                    <span><?php echo $room['view']; ?></span>
                                </div>
                                <div class="feature">
                                    <i class="fas fa-users"></i>
                                    <span>Up to <?php echo $room['capacity']; ?></span>
                                </div>
                            </div>
                            
                            <div class="amenities">
                                <?php 
                                $amenitiesCount = count($room['amenities']);
                                $displayCount = 4;
                                if($amenitiesCount < 4) {
                                    $displayCount = $amenitiesCount;
                                }
                                for($a = 0; $a < $displayCount; $a++): 
                                ?>
                                    <span class="amenity-tag">
                                        <i class="fas fa-check-circle"></i> <?php echo $room['amenities'][$a]; ?>
                                    </span>
                                <?php endfor; ?>
                                <?php if($amenitiesCount > 4): ?>
                                    <span class="amenity-tag">
                                        <i class="fas fa-plus-circle"></i> +<?php echo $amenitiesCount - 4; ?> more
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="room-footer">
                                <div class="room-price">
                                    RM<?php echo number_format($room['price'], 0); ?>
                                    <span>/night</span>
                                </div>
                                <?php if($room['available'] > 0): ?>
                                    <button class="btn btn-primary btn-book" data-room-id="<?php echo $room['id']; ?>">
                                        Book Now <i class="fas fa-arrow-right"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-book" disabled>
                                        Unavailable
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>
    </div>
</main>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">200+</div>
                <div class="stat-label">Luxury Rooms</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">4.8</div>
                <div class="stat-label">Guest Rating</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15</div>
                <div class="stat-label">Suite Options</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Concierge Service</div>
            </div>
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
    // Sticky header
    window.addEventListener('scroll', function() {
        const header = document.getElementById('header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Store rooms data
    const roomsData = <?php echo json_encode($rooms); ?>;
    
    // Manual DOM manipulation - no frameworks
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
    
    // Update price range display
    if(priceRange) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = '$' + this.value;
        });
    }
    
    // Filter rooms function
    function filterRooms() {
        const maxPrice = parseInt(document.getElementById('priceRange').value);
        const roomType = document.getElementById('roomType').value;
        const guests = parseInt(document.getElementById('guests').value);
        
        loadingDiv.style.display = 'block';
        roomGrid.style.opacity = '0.5';
        
        setTimeout(function() {
            const cards = document.querySelectorAll('.room-card');
            let visibleCount = 0;
            
            for(let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const price = parseInt(card.getAttribute('data-price'));
                const type = card.getAttribute('data-type');
                const capacity = parseInt(card.getAttribute('data-capacity'));
                
                const priceMatch = price <= maxPrice;
                const typeMatch = roomType === 'all' || type === roomType;
                const guestsMatch = capacity >= guests;
                
                if(priceMatch && typeMatch && guestsMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            }
            
            loadingDiv.style.display = 'none';
            roomGrid.style.opacity = '1';
            
            let noResultsMsg = document.querySelector('.no-results-message');
            
            if(visibleCount === 0) {
                if(!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No Rooms Found</h3>
                        <p>Try adjusting your filters to see more options.</p>
                    `;
                    roomGrid.appendChild(noResultsMsg);
                }
            } else if(noResultsMsg) {
                roomGrid.removeChild(noResultsMsg);
            }
        }, 500);
    }
    
    // Open booking modal
    function openBookingModal(roomId) {
        for(let i = 0; i < roomsData.length; i++) {
            if(roomsData[i].id === roomId) {
                currentRoom = roomsData[i];
                break;
            }
        }
        
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        const guests = document.getElementById('guests').value;
        
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const timeDiff = checkOutDate - checkInDate;
        const nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
        const totalPrice = currentRoom.price * nights;
        
        const summaryDiv = document.getElementById('bookingSummary');
        summaryDiv.innerHTML = `
            <p><strong><i class="fas fa-hotel"></i> Room:</strong> ${currentRoom.name}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-in:</strong> ${checkInDate.toLocaleDateString()}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-out:</strong> ${checkOutDate.toLocaleDateString()}</p>
            <p><strong><i class="fas fa-moon"></i> Nights:</strong> ${nights}</p>
            <p><strong><i class="fas fa-users"></i> Guests:</strong> ${guests}</p>
            <p><strong><i class="fas fa-tag"></i> Price per night:</strong> $${currentRoom.price}</p>
            <p class="total-price"><strong>Total:</strong> $${totalPrice}</p>
        `;
        
        bookingModal.style.display = 'flex';
    }
    
    // Close modal
    function closeModal() {
        bookingModal.style.display = 'none';
    }
    
    function closeSuccessModal() {
        successModal.style.display = 'none';
    }
    
    function confirmBooking() {
        closeModal();
        successModal.style.display = 'flex';
        
        setTimeout(function() {
            closeSuccessModal();
        }, 5000);
    }
    
    // Scroll animation for fade-in elements
    const fadeElements = document.querySelectorAll('.fade-in');
    
    function checkFade() {
        fadeElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if(elementTop < windowHeight - 100) {
                element.classList.add('visible');
            }
        });
    }
    
    // Event Listeners
    if(searchButton) {
        searchButton.addEventListener('click', filterRooms);
    }
    
    // Card click navigation
    const roomCards = document.querySelectorAll('.room-card');
    for(let i = 0; i < roomCards.length; i++) {
        roomCards[i].addEventListener('click', function(e) {
            const target = e.target;
            if(target.classList && target.classList.contains('btn-book')) {
                return;
            }
            const roomId = this.getAttribute('data-id');
            window.location.href = 'roomdetails.php?id=' + roomId;
        });
    }
    
    // Book button listeners
    const bookButtons = document.querySelectorAll('.btn-book');
    for(let i = 0; i < bookButtons.length; i++) {
        if(!bookButtons[i].disabled) {
            bookButtons[i].addEventListener('click', function(e) {
                e.stopPropagation();
                const roomCard = this.closest('.room-card');
                const roomId = parseInt(roomCard.getAttribute('data-id'));
                openBookingModal(roomId);
            });
        }
    }
    
    // Modal close listeners
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const confirmBookingBtn = document.getElementById('confirmBookingBtn');
    const closeSuccessBtn = document.getElementById('closeSuccessBtn');
    const continueBtn = document.getElementById('continueBtn');
    
    if(closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if(cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);
    if(confirmBookingBtn) confirmBookingBtn.addEventListener('click', confirmBooking);
    if(closeSuccessBtn) closeSuccessBtn.addEventListener('click', closeSuccessModal);
    if(continueBtn) continueBtn.addEventListener('click', closeSuccessModal);
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if(event.target === bookingModal) {
            closeModal();
        }
        if(event.target === successModal) {
            closeSuccessModal();
        }
    });
    
    // Validate dates
    if(checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const checkOutDate = new Date(checkOutInput.value);
            
            if(checkOutDate <= checkInDate) {
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutInput.value = nextDay.toISOString().split('T')[0];
            }
        });
    }
    
    // Add fade animation on scroll
    window.addEventListener('scroll', checkFade);
    window.addEventListener('load', checkFade);
    
    // Smooth scroll for anchor links
    const heroButtons = document.querySelectorAll('.hero-buttons a');
    for(let i = 0; i < heroButtons.length; i++) {
        heroButtons[i].addEventListener('click', function(e) {
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
</script>

<?php
include '../Shared/footer.php';
?>
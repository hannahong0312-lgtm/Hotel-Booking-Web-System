<?php
// accommodation.php - Main Accommodation Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Include header
include '../Shared/header.php';

// Get unique room types for filter
$room_types = array_unique(array_column($rooms, 'type'));
?>

<!-- Page Specific CSS -->
<link rel="stylesheet" href="accommodation.css">

<!-- Main Content -->
<main>
    <div class="container">
        <!-- Filter Section -->
        <section class="filter-section">
            <div style="display: flex; justify-content: center; align-items: center;">
            <h2><i class="fas fa-search"></i> Find Your Perfect Stay</h2>
            </div>
            <div class="filter-grid">
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> Check-in</label>
                    <input type="date" id="checkIn" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-calendar-alt"></i> Check-out</label>
                    <input type="date" id="checkOut" class="form-control" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
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
                        <span>$50</span>
                        <span id="priceValue">$1500</span>
                        <span>$1500</span>
                    </div>
                </div>
                
                <div class="filter-item">
                    <label><i class="fas fa-door-open"></i> Room Type</label>
                    <select id="roomType" class="form-control">
                        <option value="all">All Types</option>
                        <?php foreach($room_types as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo ucfirst($type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <button class="btn btn-primary btn-search" onclick="filterRooms()">
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
        <div class="section-header">
            <h2>Choose Your Perfect Stay</h2>
            <p>Each room is thoughtfully designed with your comfort in mind</p>
        </div>
        
        <div class="room-grid" id="roomGrid">
            <?php foreach($rooms as $room): ?>
                <div class="room-card" 
                     data-id="<?php echo $room['id']; ?>"
                     data-price="<?php echo $room['price']; ?>"
                     data-type="<?php echo $room['type']; ?>"
                     data-capacity="<?php echo $room['capacity']; ?>">
                    
                    <div class="room-image" style="background-image: url('<?php echo $room['image']; ?>')">
                        <div class="price-badge">$<?php echo number_format($room['price'], 0); ?><span>/night</span></div>
                        <?php if($room['popular']): ?>
                            <div class="popular-badge"><i class="fas fa-star"></i> Most Popular</div>
                        <?php endif; ?>
                        <div class="availability-badge <?php echo $room['available'] > 2 ? 'available' : ($room['available'] > 0 ? 'limited' : 'soldout'); ?>">
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
                            <?php foreach(array_slice($room['amenities'], 0, 4) as $amenity): ?>
                                <span class="amenity-tag">
                                    <i class="fas fa-check-circle"></i> <?php echo $amenity; ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if(count($room['amenities']) > 4): ?>
                                <span class="amenity-tag">
                                    <i class="fas fa-plus-circle"></i> +<?php echo count($room['amenities']) - 4; ?> more
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="room-footer">
                            <div class="room-price">
                                $<?php echo number_format($room['price'], 0); ?>
                                <span>/night</span>
                            </div>
                            <?php if($room['available'] > 0): ?>
                                <button class="btn btn-primary btn-book" onclick="openBookingModal(<?php echo $room['id']; ?>)">
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
            <?php endforeach; ?>
        </div>
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

<script>
    // Store rooms data for JavaScript
    const roomsData = <?php echo json_encode($rooms); ?>;
    
    // Update price range display
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    
    if (priceRange) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = '$' + this.value;
        });
    }
    
    // Filter rooms function
    function filterRooms() {
        const maxPrice = parseInt(document.getElementById('priceRange').value);
        const roomType = document.getElementById('roomType').value;
        const guests = parseInt(document.getElementById('guests').value);
        
        // Show loading
        document.getElementById('loading').style.display = 'block';
        document.getElementById('roomGrid').style.opacity = '0.5';
        
        // Simulate loading delay
        setTimeout(() => {
            const cards = document.querySelectorAll('.room-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const price = parseInt(card.dataset.price);
                const type = card.dataset.type;
                const capacity = parseInt(card.dataset.capacity);
                
                const priceMatch = price <= maxPrice;
                const typeMatch = roomType === 'all' || type === roomType;
                const guestsMatch = capacity >= guests;
                
                if (priceMatch && typeMatch && guestsMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Hide loading
            document.getElementById('loading').style.display = 'none';
            document.getElementById('roomGrid').style.opacity = '1';
            
            // Show no results message if needed
            const grid = document.getElementById('roomGrid');
            let noResultsMsg = document.querySelector('.no-results-message');
            
            if (visibleCount === 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No Rooms Found</h3>
                        <p>Try adjusting your filters to see more options.</p>
                    `;
                    grid.appendChild(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }, 500);
    }
    
    // Open booking modal
    function openBookingModal(roomId) {
        const room = roomsData.find(r => r.id === roomId);
        const checkIn = document.getElementById('checkIn').value;
        const checkOut = document.getElementById('checkOut').value;
        const guests = document.getElementById('guests').value;
        
        // Calculate nights
        const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
        const totalPrice = room.price * nights;
        
        // Display booking summary
        document.getElementById('bookingSummary').innerHTML = `
            <p><strong><i class="fas fa-hotel"></i> Room:</strong> ${room.name}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-in:</strong> ${new Date(checkIn).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-calendar-alt"></i> Check-out:</strong> ${new Date(checkOut).toLocaleDateString()}</p>
            <p><strong><i class="fas fa-moon"></i> Nights:</strong> ${nights}</p>
            <p><strong><i class="fas fa-users"></i> Guests:</strong> ${guests}</p>
            <p><strong><i class="fas fa-tag"></i> Price per night:</strong> $${room.price}</p>
            <p class="total-price"><strong>Total:</strong> $${totalPrice}</p>
        `;
        
        document.getElementById('bookingModal').style.display = 'flex';
    }
    
    // Confirm booking
    function confirmBooking() {
        closeModal();
        document.getElementById('successModal').style.display = 'flex';
        setTimeout(closeSuccessModal, 5000);
    }
    
    // Close modals
    function closeModal() {
        document.getElementById('bookingModal').style.display = 'none';
    }
    
    function closeSuccessModal() {
        document.getElementById('successModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const bookingModal = document.getElementById('bookingModal');
        const successModal = document.getElementById('successModal');
        
        if (event.target === bookingModal) {
            closeModal();
        }
        if (event.target === successModal) {
            closeSuccessModal();
        }
    }
    
    // Validate dates
    const checkIn = document.getElementById('checkIn');
    const checkOut = document.getElementById('checkOut');
    
    if (checkIn && checkOut) {
        checkIn.addEventListener('change', function() {
            const checkInDate = new Date(this.value);
            const checkOutDate = new Date(checkOut.value);
            
            if (checkOutDate <= checkInDate) {
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOut.value = nextDay.toISOString().split('T')[0];
            }
        });
    }
</script>

<?php
include '../Shared/footer.php';
?>

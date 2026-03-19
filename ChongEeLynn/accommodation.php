<?php
// accommodations.php
$pageTitle = "Accommodations";
$pageCSS = "accommodations.css";
require_once 'includes/header.php';

// Fetch rooms from database
$sql = "SELECT * FROM rooms WHERE is_active = 1 ORDER BY price";
$result = $conn->query($sql);
$rooms = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Get room types for filter
$types_sql = "SELECT DISTINCT room_type FROM rooms WHERE is_active = 1";
$types_result = $conn->query($types_sql);
$room_types = [];

if ($types_result->num_rows > 0) {
    while($row = $types_result->fetch_assoc()) {
        $room_types[] = $row['room_type'];
    }
}
?>

<div class="container">
    <!-- Filter Section -->
    <section class="filter-section">
        <h2>Find Your Perfect Stay</h2>
        <form id="filterForm" method="GET" action="accommodations.php">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="checkIn">Check-in Date</label>
                    <input type="date" class="form-control" id="checkIn" name="check_in" 
                           value="<?php echo $_GET['check_in'] ?? date('Y-m-d'); ?>" 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="checkOut">Check-out Date</label>
                    <input type="date" class="form-control" id="checkOut" name="check_out" 
                           value="<?php echo $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day')); ?>" 
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="guests">Guests</label>
                    <select class="form-control" id="guests" name="guests">
                        <?php for($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" 
                                <?php echo (isset($_GET['guests']) && $_GET['guests'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="roomType">Room Type</label>
                    <select class="form-control" id="roomType" name="room_type">
                        <option value="all">All Types</option>
                        <?php foreach($room_types as $type): ?>
                            <option value="<?php echo $type; ?>" 
                                <?php echo (isset($_GET['room_type']) && $_GET['room_type'] == $type) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="priceRange">Max Price (per night)</label>
                    <input type="range" class="form-control" id="priceRange" name="max_price" 
                           min="50" max="1000" value="<?php echo $_GET['max_price'] ?? 1000; ?>" step="10">
                    <div class="price-range-display">
                        <span>$50</span>
                        <span id="priceValue">$<?php echo $_GET['max_price'] ?? 1000; ?></span>
                        <span>$1000</span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                    <i class="fas fa-search"></i> Search Rooms
                </button>
            </div>
        </form>
    </section>

    <!-- Loading Indicator -->
    <div id="loading" style="display: none; text-align: center; padding: 2rem;">
        <div class="loading-spinner"></div>
        <p style="margin-top: 1rem;">Loading available rooms...</p>
    </div>

    <!-- Accommodation Grid -->
    <section class="accommodation-grid" id="accommodationGrid">
        <?php if (empty($rooms)): ?>
            <div class="no-results">
                <i class="fas fa-hotel"></i>
                <h3>No Rooms Found</h3>
                <p>Try adjusting your filters to see more options.</p>
            </div>
        <?php else: ?>
            <?php foreach($rooms as $room): ?>
                <div class="room-card">
                    <div class="room-image" style="background-image: url('<?php echo $room['image_url']; ?>')">
                        <div class="room-badge">
                            $<?php echo number_format($room['price'], 2); ?>/night
                        </div>
                        <?php if($room['available_rooms'] <= 2): ?>
                            <div class="availability-badge <?php echo $room['available_rooms'] > 0 ? 'limited' : 'sold-out'; ?>" 
                                 style="position: absolute; bottom: 1rem; left: 1rem;">
                                <?php if($room['available_rooms'] > 0): ?>
                                    Only <?php echo $room['available_rooms']; ?> left!
                                <?php else: ?>
                                    Sold Out
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="room-content">
                        <h3 class="room-title"><?php echo htmlspecialchars($room['name']); ?></h3>
                        <div class="room-type"><?php echo ucfirst($room['room_type']); ?></div>
                        
                        <p class="room-description"><?php echo htmlspecialchars($room['description']); ?></p>
                        
                        <?php 
                        $amenities = explode(',', $room['amenities']);
                        if(!empty($amenities)): 
                        ?>
                            <div class="amenities-list">
                                <?php foreach($amenities as $amenity): ?>
                                    <span class="amenity-tag">
                                        <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                                        <?php echo trim($amenity); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="room-details">
                            <span><i class="fas fa-users"></i> Max <?php echo $room['max_guests']; ?> guests</span>
                            <span><i class="fas fa-bed"></i> <?php echo $room['bed_type']; ?></span>
                            <span><i class="fas fa-ruler-combined"></i> <?php echo $room['room_size']; ?></span>
                        </div>
                        
                        <?php if($room['available_rooms'] > 0): ?>
                            <button class="btn btn-primary book-btn" 
                                    onclick="openBookingModal(<?php echo $room['id']; ?>)"
                                    style="width: 100%;">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary" style="width: 100%;" disabled>
                                <i class="fas fa-times-circle"></i> Not Available
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<!-- Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <h2><i class="fas fa-calendar-check"></i> Confirm Your Booking</h2>
        <div id="bookingDetails" class="booking-details"></div>
        
        <form id="bookingForm" action="booking-process.php" method="POST">
            <input type="hidden" name="room_id" id="modalRoomId">
            <input type="hidden" name="check_in" id="modalCheckIn">
            <input type="hidden" name="check_out" id="modalCheckOut">
            <input type="hidden" name="guests" id="modalGuests">
            <input type="hidden" name="total_price" id="modalTotalPrice">
            
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirm Booking
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <h2 style="color: var(--success-color);">
            <i class="fas fa-check-circle"></i> Booking Confirmed!
        </h2>
        <p>Your room has been successfully booked. Check your email for confirmation details.</p>
        <button class="btn btn-primary" onclick="closeSuccessModal()" style="width: 100%;">
            <i class="fas fa-thumbs-up"></i> Great!
        </button>
    </div>
</div>

<script>
// Store rooms data for JavaScript
const rooms = <?php echo json_encode($rooms); ?>;

// Update price range display
document.getElementById('priceRange')?.addEventListener('input', function(e) {
    document.getElementById('priceValue').textContent = '$' + e.target.value;
});

// Validate dates before form submission
document.getElementById('filterForm')?.addEventListener('submit', function(e) {
    const checkIn = new Date(document.getElementById('checkIn').value);
    const checkOut = new Date(document.getElementById('checkOut').value);
    
    if (checkOut <= checkIn) {
        e.preventDefault();
        alert('Check-out date must be after check-in date');
    }
});

// Open booking modal
function openBookingModal(roomId) {
    const room = rooms.find(r => r.id == roomId);
    const checkIn = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    const guests = document.getElementById('guests').value;
    
    // Calculate nights and total
    const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
    const totalPrice = room.price * nights;
    
    // Set modal fields
    document.getElementById('modalRoomId').value = room.id;
    document.getElementById('modalCheckIn').value = checkIn;
    document.getElementById('modalCheckOut').value = checkOut;
    document.getElementById('modalGuests').value = guests;
    document.getElementById('modalTotalPrice').value = totalPrice;
    
    // Display booking details
    document.getElementById('bookingDetails').innerHTML = `
        <p><strong><i class="fas fa-hotel"></i> Room:</strong> ${room.name}</p>
        <p><strong><i class="fas fa-calendar-alt"></i> Check-in:</strong> ${new Date(checkIn).toLocaleDateString()}</p>
        <p><strong><i class="fas fa-calendar-alt"></i> Check-out:</strong> ${new Date(checkOut).toLocaleDateString()}</p>
        <p><strong><i class="fas fa-moon"></i> Nights:</strong> ${nights}</p>
        <p><strong><i class="fas fa-users"></i> Guests:</strong> ${guests}</p>
        <p><strong><i class="fas fa-tag"></i> Price per night:</strong> $${room.price}</p>
        <p><strong><i class="fas fa-calculator"></i> Total:</strong> <span style="color: var(--primary-color); font-size: 1.2rem;">$${totalPrice}</span></p>
    `;
    
    document.getElementById('bookingModal').style.display = 'block';
}

// Close modal
function closeModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
}

// Check for success parameter in URL
if (window.location.search.includes('booking=success')) {
    document.getElementById('successModal').style.display = 'block';
    setTimeout(closeSuccessModal, 3000);
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
</script>

<?php
// roomdetails.php
include '../Shared/config.php';

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

// Fetch 4 similar rooms (same category, different id) - changed from 3 to 4
$similar_sql = "SELECT * FROM rooms WHERE category = '{$conn->real_escape_string($room['category'])}' AND id != $room_id AND is_active = 1 LIMIT 4";
$similar_result = $conn->query($sql);
$similar_rooms = [];
if ($similar_result && $similar_result->num_rows > 0) {
    while ($row = $similar_result->fetch_assoc()) {
        $similar_rooms[] = $row;
    }
}

// Get date parameters from URL (if coming from search)
$check_in = isset($_GET['check_in']) ? htmlspecialchars($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : $room['max_guests'];

// Calculate number of nights
$nights = 0;
if ($check_in && $check_out) {
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
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
<section class="detail-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('<?= htmlspecialchars($room['image']) ?>');">
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
        <!-- Back Button - MOVED ABOVE room description -->
        <div class="back-section-top">
            <a href="accommodation.php" class="back-btn">← Back to All Rooms</a>
        </div>
        
        <div class="detail-grid">
            <!-- Left Column: Room Info -->
            <div class="detail-info">
                <div class="info-card">
                    <h2>Room Description</h2>
                    <p class="description"><?= htmlspecialchars($room['description']) ?></p>
                    
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
                
                <!-- NEW: Room Policies Section -->
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
                                <p>Free cancellation up to 48 hours before check-in</p>
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
                    
                    <form method="GET" action="booking.php" class="booking-form" id="bookingForm">
                        <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                        
                        <div class="form-group">
                            <label>CHECK-IN DATE</label>
                            <input type="date" name="check_in" class="form-input" id="checkInInput" value="<?= $check_in ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>CHECK-OUT DATE</label>
                            <input type="date" name="check_out" class="form-input" id="checkOutInput" value="<?= $check_out ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>NUMBER OF GUESTS</label>
                            <div class="guest-stepper-detail">
                                <button type="button" class="guest-btn-detail" onclick="changeGuestsDetail(-1)">−</button>
                                <span class="guest-value-detail" id="guestValDetail"><?= $guests ?></span>
                                <input type="hidden" name="guests" id="guestInputDetail" value="<?= $guests ?>">
                                <button type="button" class="guest-btn-detail" onclick="changeGuestsDetail(1)">+</button>
                            </div>
                        </div>
                        
                        <!-- NEW: Total Price Display -->
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
                        <p>✓ Free cancellation up to 48 hours before check-in</p>
                        <p>✓ No prepayment required</p>
                        <p>✓ Best price guaranteed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Rooms Section (now shows 4 rooms) -->
<?php if (!empty($similar_rooms)): ?>
<section class="similar-rooms">
    <div class="detail-container">
        <div class="similar-header">
            <h2>You May Also Like</h2>
            <p>Explore other luxurious accommodations</p>
        </div>
        <div class="similar-grid">
            <?php foreach($similar_rooms as $similar): ?>
                <div class="similar-card" onclick="window.location.href='roomdetails.php?id=<?= $similar['id'] ?>&check_in=<?= urlencode($check_in) ?>&check_out=<?= urlencode($check_out) ?>&guests=<?= $guests ?>'">
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
let checkInInput = document.getElementById('checkInInput');
let checkOutInput = document.getElementById('checkOutInput');
let nightCountSpan = document.getElementById('nightCount');
let subtotalSpan = document.getElementById('subtotal');
let grandTotalSpan = document.getElementById('grandTotal');
let roomPrice = <?= $room['price'] ?>;

function calculateTotal() {
    let checkIn = checkInInput.value;
    let checkOut = checkOutInput.value;
    
    if (checkIn && checkOut) {
        let date1 = new Date(checkIn);
        let date2 = new Date(checkOut);
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
checkInInput.min = today;

if (!checkInInput.value) {
    let tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    checkInInput.value = tomorrow.toISOString().split('T')[0];
    let dayAfter = new Date(tomorrow);
    dayAfter.setDate(dayAfter.getDate() + 1);
    checkOutInput.value = dayAfter.toISOString().split('T')[0];
    calculateTotal();
}

checkInInput.addEventListener('change', function() {
    if (this.value) {
        checkOutInput.min = this.value;
        if (checkOutInput.value && checkOutInput.value < this.value) {
            checkOutInput.value = '';
        }
        calculateTotal();
    }
});

checkOutInput.addEventListener('change', function() {
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
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
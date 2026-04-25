<?php
// accommodation.php
include '../Shared/config.php';
include '../Shared/header.php';

$room_type = $_GET['room_type'] ?? '';
$guests = $_GET['guests'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$arrive = $_GET['arrive'] ?? '';
$depart = $_GET['depart'] ?? '';

// NEW: Validate that dates are within 1 year
$today = date('Y-m-d');
$oneYearLater = date('Y-m-d', strtotime('+1 year'));

if (!empty($arrive) && $arrive < $today) {
    $arrive = ''; // Clear invalid past date
}

if (!empty($arrive) && $arrive > $oneYearLater) {
    $arrive = ''; // Clear date beyond 1 year
    $error_message = "Bookings are only allowed within 1 year from today.";
}

if (!empty($depart) && $depart > $oneYearLater) {
    $depart = ''; // Clear invalid departure date
    $error_message = "Bookings are only allowed within 1 year from today.";
}

$sql = "SELECT * FROM rooms WHERE is_active = 1";
if(!empty($room_type)) $sql .= " AND category = '" . $conn->real_escape_string($room_type) . "'";
if(!empty($guests)) $sql .= " AND max_guests >= " . intval($guests);
if(!empty($max_price)) $sql .= " AND price <= " . floatval($max_price);
$sql .= " ORDER BY id ASC";

$result = $conn->query($sql);
$rooms = [];
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Fetch review stats for each room
        $room_id = $row['id'];
        $review_stats_sql = "SELECT COUNT(*) as total, AVG(r_rating) as avg_rating FROM review WHERE room_id = $room_id";
        $review_stats_result = $conn->query($review_stats_sql);
        $review_stats = $review_stats_result->fetch_assoc();
        $row['total_reviews'] = $review_stats['total'] ? $review_stats['total'] : 0;
        $row['avg_rating'] = $review_stats['avg_rating'] ? round($review_stats['avg_rating'], 1) : 0;
        $rooms[] = $row;
    }
}

// Function to generate star HTML
function getStars($rating) {
    $rating = (float)$rating;
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $stars = '';
    for ($i = 0; $i < $fullStars; $i++) $stars .= '★';
    if ($hasHalfStar) $stars .= '½';
    for ($i = strlen($stars); $i < 5; $i++) $stars .= '☆';
    return $stars;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Accommodations</title>
    <link rel="stylesheet" href="css/accommodation.css">
</head>
<body>

<!-- Hero -->
<section class="hero">
    <div class="hero-content">
        <h1>Luxury Accommodations</h1>
        <p>Experience unparalleled comfort and elegance in our carefully designed rooms and suites</p>
        <div class="hero-buttons">
            <a href="#rooms" class="btn-primary">View Rooms</a>
        </div>
    </div>
</section>

<!-- Filter Section - Oval Frame Redesign -->
<section class="filter-section">
    <div class="filter-wrapper">
        <div class="filter-oval-card">
            <?php if(isset($error_message)): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    ⚠️ <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="GET" action="" class="filter-form" id="bookingForm">
                <!-- Arrive Date -->
                <div class="filter-group">
                    <label>ARRIVE</label>
                    <input type="date" name="arrive" id="arriveDate" value="<?= htmlspecialchars($arrive) ?>" class="date-input">
                </div>
                
                <!-- Depart Date -->
                <div class="filter-group">
                    <label>DEPART</label>
                    <input type="date" name="depart" id="departDate" value="<?= htmlspecialchars($depart) ?>" class="date-input">
                </div>
                
                <!-- Guests (Pax) -->
                <div class="filter-group">
                    <label>GUESTS</label>
                    <div class="guest-stepper">
                        <button type="button" class="guest-btn" onclick="changeGuests(-1)">−</button>
                        <span class="guest-value" id="guestVal"><?= $guests ?: 2 ?></span>
                        <input type="hidden" name="guests" id="guestInput" value="<?= $guests ?: 2 ?>">
                        <button type="button" class="guest-btn" onclick="changeGuests(1)">+</button>
                    </div>
                </div>
                
                <!-- Room Type -->
                <div class="filter-group">
                    <label>ROOM TYPE</label>
                    <select name="room_type" class="room-type-select">
                        <option value="">All Rooms</option>
                        <option value="standard" <?= $room_type == 'standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="deluxe" <?= $room_type == 'deluxe' ? 'selected' : '' ?>>Deluxe</option>
                        <option value="family" <?= $room_type == 'family' ? 'selected' : '' ?>>Family</option>
                        <option value="suite" <?= $room_type == 'suite' ? 'selected' : '' ?>>Suite</option>
                    </select>
                </div>
                
                <!-- Price Range Slider -->
                <div class="filter-group">
                    <label>MAX PRICE (RM)</label>
                    <div class="price-range-wrapper">
                        <div class="price-values">
                            <span id="priceValue">RM <?= $max_price ?: 800 ?></span>
                        </div>
                        <input type="range" id="priceSlider" name="max_price" min="0" max="1500" value="<?= $max_price ?: 800 ?>" step="10">
                    </div>
                </div>
                
                <button type="submit" class="search-btn-oval">Check Availability →</button>
            </form>
        </div>
    </div>
</section>

<!-- Rooms -->
<section class="rooms" id="rooms">
    <div class="container">
        <div class="rooms-header">
            <h2>Our Premium Collection</h2>
            <p>Discover thoughtfully designed spaces for your ultimate comfort</p>
        </div>
        <div class="rooms-grid">
            <?php if(empty($rooms)): ?>
                <p class="no-results">No rooms found.</p>
            <?php else: foreach($rooms as $room): ?>
                <div class="room-card" onclick="window.location.href='roomdetails.php?id=<?= $room['id'] ?>&arrive=<?= urlencode($arrive) ?>&depart=<?= urlencode($depart) ?>&guests=<?= $guests ?: 2 ?>'" style="cursor: pointer;">
                    <div class="room-img">
                        <img src="images/<?php echo $room['image']; ?>" alt="<?php echo $room['name']; ?>">
                        <span class="room-badge <?= $room['category'] ?>"><?= ucfirst($room['category']) ?></span>
                        <span class="avail-badge <?= $room['rooms_available'] > 0 ? 'avail' : 'sold' ?>">
                            <?= $room['rooms_available'] > 0 ? $room['rooms_available'] . ' left' : 'Sold Out' ?>
                        </span>
                    </div>
                    <div class="room-info">
                        <div class="room-header">
                            <h3><?= htmlspecialchars($room['name']) ?></h3>
                            <div class="rating-container">
                                <?php if($room['total_reviews'] > 0): ?>
                                    <div class="stars"><?= getStars($room['avg_rating']) ?></div>
                                    <div class="rating-value"><?= $room['avg_rating'] ?></div>
                                    <div class="review-count">(<?= $room['total_reviews'] ?>)</div>
                                <?php else: ?>
                                    <div class="no-reviews">No reviews yet</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p><?= htmlspecialchars($room['description']) ?></p>
                        <div class="room-meta">
                            <span>👥 <?= $room['max_guests'] ?> guests</span>
                            <span>🛏️ <?= $room['bed_type'] ?></span>
                            <span>📐 <?= $room['size'] ?>m²</span>
                        </div>
                        <div class="room-price">
                            <span class="price">RM <?= number_format($room['price'], 0) ?></span>
                            <span class="night">/ night</span>
                        </div>
                        <a href="roomdetails.php?id=<?= $room['id'] ?>&arrive=<?= urlencode($arrive) ?>&depart=<?= urlencode($depart) ?>&guests=<?= $guests ?: 2 ?>" class="book-btn <?= $room['rooms_available'] == 0 ? 'disabled' : '' ?>" onclick="event.stopPropagation();">
                            <?= $room['rooms_available'] > 0 ? 'View Details →' : 'Unavailable' ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

<script>
// Price slider with live update
let slider = document.getElementById('priceSlider');
let priceValueSpan = document.getElementById('priceValue');

function updatePrice() {
    let val = slider.value;
    priceValueSpan.textContent = 'RM ' + val;
    // Update slider track fill color
    let percent = (val / 1500) * 100;
    slider.style.setProperty('--fill-percent', percent + '%');
}

slider.addEventListener('input', updatePrice);
updatePrice();

// Function to change number of guests
function changeGuests(delta) {
    let input = document.getElementById('guestInput');
    let span = document.getElementById('guestVal');
    let val = parseInt(input.value) + delta;
    if(val >= 1 && val <= 6) {
        input.value = val;
        span.textContent = val;
    }
}

// NEW: Date validation with 1-year limit
const today = new Date();
const oneYearLater = new Date();
oneYearLater.setFullYear(today.getFullYear() + 1);

// Format dates for min/max attributes
const todayStr = today.toISOString().split('T')[0];
const oneYearLaterStr = oneYearLater.toISOString().split('T')[0];

let arriveInput = document.getElementById('arriveDate');
let departInput = document.getElementById('departDate');

if(arriveInput) {
    // Set min date to today and max date to 1 year from today
    arriveInput.min = todayStr;
    arriveInput.max = oneYearLaterStr;
    
    // If there's an existing value, validate it
    if(arriveInput.value && (arriveInput.value < todayStr || arriveInput.value > oneYearLaterStr)) {
        arriveInput.value = '';
        alert('Please select a date within 1 year from today.');
    }
    
    arriveInput.addEventListener('change', function() {
        // Validate arrive date is within 1 year
        if(this.value && this.value > oneYearLaterStr) {
            alert('Bookings are only allowed within 1 year from today. Please select an earlier date.');
            this.value = '';
            return;
        }
        
        // Update depart date minimum
        if(departInput) {
            departInput.min = this.value;
            if(departInput.value && departInput.value < this.value) {
                departInput.value = '';
            }
        }
    });
}

if(departInput) {
    // Set max date to 1 year from today
    departInput.max = oneYearLaterStr;
    
    // Validate existing depart date
    if(departInput.value && departInput.value > oneYearLaterStr) {
        departInput.value = '';
        alert('Please select a departure date within 1 year from today.');
    }
    
    departInput.addEventListener('change', function() {
        if(this.value && this.value > oneYearLaterStr) {
            alert('Bookings are only allowed within 1 year from today. Please select an earlier date.');
            this.value = '';
        }
    });
}

// Form submission validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    let arrive = arriveInput ? arriveInput.value : '';
    let depart = departInput ? departInput.value : '';
    
    if(arrive && arrive > oneYearLaterStr) {
        e.preventDefault();
        alert('❌ Arrival date must be within 1 year from today!');
        return false;
    }
    
    if(depart && depart > oneYearLaterStr) {
        e.preventDefault();
        alert('❌ Departure date must be within 1 year from today!');
        return false;
    }
    
    if(arrive && depart && depart <= arrive) {
        e.preventDefault();
        alert('❌ Departure date must be after arrival date!');
        return false;
    }
    
    return true;
});
</script>

<style>
/* Optional: Style for error message */
.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
    border: 1px solid #f5c6cb;
}
</style>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
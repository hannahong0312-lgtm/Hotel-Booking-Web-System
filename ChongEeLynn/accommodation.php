<?php
// accommodation.php
include '../Shared/config.php';

include '../Shared/header.php';

$room_type = $_GET['room_type'] ?? '';
$guests = $_GET['guests'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$arrive = $_GET['arrive'] ?? '';
$depart = $_GET['depart'] ?? '';

$sql = "SELECT * FROM rooms WHERE is_active = 1";
if(!empty($room_type)) $sql .= " AND category = '" . $conn->real_escape_string($room_type) . "'";
if(!empty($guests)) $sql .= " AND max_guests >= " . intval($guests);
if(!empty($max_price)) $sql .= " AND price <= " . floatval($max_price);
$sql .= " ORDER BY id ASC";

$result = $conn->query($sql);
$rooms = [];
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) $rooms[] = $row;
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
            <a href="#suites" class="btn-secondary">Explore Suites</a>
        </div>
    </div>
</section>

<!-- Filter Section - Oval Frame Redesign -->
<section class="filter-section">
    <div class="filter-wrapper">
        <div class="filter-oval-card">
            <form method="GET" action="" class="filter-form">
                <!-- Arrive Date -->
                <div class="filter-group">
                    <label>ARRIVE</label>
                    <input type="date" name="arrive" value="<?= htmlspecialchars($arrive) ?>" class="date-input">
                </div>
                
                <!-- Depart Date -->
                <div class="filter-group">
                    <label>DEPART</label>
                    <input type="date" name="depart" value="<?= htmlspecialchars($depart) ?>" class="date-input">
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
                        <h3><?= htmlspecialchars($room['name']) ?></h3>
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
function changeGuests(delta) {
    let input = document.getElementById('guestInput');
    let span = document.getElementById('guestVal');
    let val = parseInt(input.value) + delta;
    if(val >= 1 && val <= 8) {
        input.value = val;
        span.textContent = val;
    }
}

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

// Optional: Set min date for arrive to today
let arriveInput = document.querySelector('input[name="arrive"]');
if(arriveInput) {
    let today = new Date().toISOString().split('T')[0];
    if(!arriveInput.value) arriveInput.min = today;
    arriveInput.addEventListener('change', function() {
        let departInput = document.querySelector('input[name="depart"]');
        if(departInput && this.value) {
            departInput.min = this.value;
            if(departInput.value && departInput.value < this.value) {
                departInput.value = '';
            }
        }
    });
}
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
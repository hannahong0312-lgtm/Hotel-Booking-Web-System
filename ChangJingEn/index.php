<?php
// index.php - Grand Hotel Homepage (Minimalist Black/White + Coral)
require_once '../Shared/config.php';

$featured_rooms = [];
$sql = "SELECT id, room_name, room_type, price_per_night, description, image_url 
        FROM rooms 
        WHERE status = 'available' 
        ORDER BY featured DESC, id DESC 
        LIMIT 4";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_rooms[] = $row;
    }
}

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
$is_logged_in = isLoggedIn();

// Link to header.php for consistent header across pages
include '../Shared/header.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hotel | Modern Luxury</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<style>
    /* ----- OVERRIDE MAIN CSS FOR THIS PAGE (transparent header + scrolled state) ----- */
            .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: transparent;
            padding: 1.5rem 0;
            transition: var(--transition);
            backdrop-filter: blur(0);
        }
        /* When scrolled: semi‑transparent black background */
        .header.scrolled {
            background: rgba(26, 26, 26, 0.85);  /* #1A1A1A with 85% opacity */
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 0.8rem 0;
        }
</style>
<body>
    <!-- Hero with Search -->
    <section class="hero">
        <div class="hero-content">
            <h2>A Grand Experience Awaits</h2>
            <p>Discover timeless elegance, world-class amenities, and impeccable service in the heart of the city</p>
            <div class="search-widget">
                <form action="room_booking.php" method="GET" class="search-form">
                    <div class="search-group">
                        <label>ARRIVE</label>
                        <input type="date" name="checkin" id="checkin" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="search-group">
                        <label>DEPART</label>
                        <input type="date" name="checkout" id="checkout" required value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    <div class="search-group">
                        <label>GUESTS</label>
                        <select name="guests">
                            <option value="1">1 Adult</option>
                            <option value="2" selected>2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <button type="submit" class="search-btn">CHECK AVAILABILITY</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Amenities -->
    <div class="section">
        <div class="container">
            <h2 class="section-title">Unmatched Experiences</h2>
            <p class="section-subtitle">Indulge in world-class facilities designed for your comfort</p>
            <div class="amenities-grid">
                <div class="amenity-item">
                    <i class="fas fa-swimming-pool"></i>
                    <h4>Infinity Pool</h4>
                    <p>Panoramic city views</p>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-dumbbell"></i>
                    <h4>Fitness Center</h4>
                    <p>State-of-the-art equipment</p>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-utensils"></i>
                    <h4>Gourmet Dining</h4>
                    <p>Michelin-starred chefs</p>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-spa"></i>
                    <h4>Spa & Wellness</h4>
                    <p>Rejuvenating treatments</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Rooms -->
    <div class="section" style="background: var(--gray-bg);">
        <div class="container">
            <h2 class="section-title">Featured Suites</h2>
            <p class="section-subtitle">Discover our most sought-after accommodations</p>
            <?php if (count($featured_rooms) > 0): ?>
                <div class="rooms-grid">
                    <?php foreach ($featured_rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-img" style="background-image: url('<?php echo !empty($room['image_url']) ? htmlspecialchars($room['image_url']) : 'https://images.pexels.com/photos/1648777/pexels-photo-1648777.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&dpr=2'; ?>');"></div>
                            <div class="room-info">
                                <span class="room-type"><?php echo htmlspecialchars($room['room_type']); ?></span>
                                <h3><?php echo htmlspecialchars($room['room_name']); ?></h3>
                                <div class="room-price">$<?php echo number_format($room['price_per_night'], 2); ?><small> / night</small></div>
                                <p><?php echo htmlspecialchars(substr($room['description'], 0, 90)); ?>...</p>
                                <a href="room_details.php?id=<?php echo $room['id']; ?>" class="btn-details">View Details →</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center;">No featured rooms available. Please check back later.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="container">
        <div class="cta">
            <h3>Amplify Your Stay</h3>
            <p>Book now and enjoy exclusive benefits: daily breakfast, RM50 dining credit, and 20% off spa treatments.</p>
            <a href="offers.php" class="btn-cta">Explore Offers</a>
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
        // Date validation
        const checkin = document.getElementById('checkin');
        const checkout = document.getElementById('checkout');
        function updateCheckoutMin() {
            if (checkin.value) {
                checkout.min = checkin.value;
                if (checkout.value <= checkin.value) {
                    let newCheckout = new Date(checkin.value);
                    newCheckout.setDate(newCheckout.getDate() + 1);
                    checkout.value = newCheckout.toISOString().split('T')[0];
                }
            }
        }
        checkin.addEventListener('change', updateCheckoutMin);
        updateCheckoutMin();
    </script>

<!-- Link to footer.php for consistent footer across pages -->
<?php
include '../Shared/footer.php';
?>

</body>
</html>
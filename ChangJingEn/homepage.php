<?php
// homepage.php - Grand Hotel Homepage
require_once '../Shared/config.php';

$pageCSS = 'css/homepage.css';   

$featured_rooms = [
    [
        'id' => 1,
        'room_name' => 'Grand Deluxe Suite',
        'room_type' => 'Suite',
        'price_per_night' => 450.00,
        'description' => 'Spacious suite with separate living area, panoramic city views, and marble bathroom.',
        'image_url' => 'https://images.pexels.com/photos/1648777/pexels-photo-1648777.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&dpr=2'
    ],
    [
        'id' => 2,
        'room_name' => 'Premier Ocean View',
        'room_type' => 'Double',
        'price_per_night' => 320.00,
        'description' => 'King-size bed, floor-to-ceiling windows overlooking the ocean, and a private balcony.',
        'image_url' => 'https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&dpr=2'
    ],
    [
        'id' => 3,
        'room_name' => 'Executive Club Room',
        'room_type' => 'Single',
        'price_per_night' => 280.00,
        'description' => 'Access to executive lounge, complimentary breakfast, and high-speed Wi-Fi.',
        'image_url' => 'https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&dpr=2'
    ],
    [
        'id' => 4,
        'room_name' => 'Family Garden View',
        'room_type' => 'Family',
        'price_per_night' => 380.00,
        'description' => 'Two connecting rooms, garden terrace, and kid-friendly amenities.',
        'image_url' => 'https://images.pexels.com/photos/189296/pexels-photo-189296.jpeg?auto=compress&cs=tinysrgb&w=600&h=400&dpr=2'
    ]
];

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
$is_logged_in = isLoggedIn();

include '../Shared/header.php';
?>

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

<div class="container">
    <div class="cta">
        <h3>Amplify Your Stay</h3>
        <p>Book now and enjoy exclusive benefits: daily breakfast, RM50 dining credit, and 20% off spa treatments.</p>
        <a href="offers.php" class="btn-cta">Explore Offers</a>
    </div>
</div>

<script>
    window.addEventListener('scroll', function() {
        const header = document.getElementById('header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

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

<?php
include '../Shared/footer.php';
?>
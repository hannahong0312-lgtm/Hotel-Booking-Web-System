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
    <style>
        /* ----- MINIMALIST BLACK/WHITE + CORAL ----- */
        :root {
            --black: #1A1A1A;
            --black-light: #2C2C2C;
            --white: #FFFFFF;
            --gray-bg: #F8F8F8;
            --gray-border: #E5E5E5;
            --gray-text: #666666;
            --accent: #FF6B4A;
            --accent-dark: #E55A3B;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.02);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.04);
            --transition: all 0.25s ease;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--black);
            background: var(--white);
            line-height: 1.5;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        /* ----- HEADER (transparent to white on scroll) ----- */
        .header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            background: transparent;
            padding: 1.5rem 0;
            transition: var(--transition);
        }
        .header.scrolled {
            position: fixed;
            background: var(--white);
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            padding: 0.8rem 0;
        }
        .header.scrolled .logo h1,
        .header.scrolled .nav ul li a,
        .header.scrolled .btn-login {
            color: var(--black);
        }
        .header.scrolled .btn-login {
            border-color: var(--black);
        }
        .header.scrolled .btn-login:hover {
            background: var(--black);
            color: var(--white);
        }
        .header.scrolled .btn-register {
            background: var(--accent);
            color: var(--white);
        }
        .header-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 500;
            color: var(--white);
            letter-spacing: -0.5px;
            transition: var(--transition);
        }
        .header.scrolled .logo h1 {
            color: var(--black);
        }
        .nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .nav ul li a {
            color: var(--white);
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .header.scrolled .nav ul li a {
            color: var(--black);
        }
        .nav ul li a:hover,
        .nav ul li a.active {
            color: var(--accent);
        }
        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .btn-login {
            background: transparent;
            border: 1px solid var(--white);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            color: var(--white);
            font-weight: 500;
            transition: var(--transition);
        }
        .btn-register {
            background: var(--accent);
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            color: var(--white);
            font-weight: 600;
            transition: var(--transition);
        }
        .btn-register:hover {
            background: var(--accent-dark);
        }
        /* ----- HERO SECTION (full-screen) ----- */
        .hero {
            position: relative;
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.3)), url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-content {
            max-width: 900px;
            padding: 0 2rem;
        }
        .hero h2 {
            font-size: 3.2rem;
            font-weight: 500;
            color: white;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            letter-spacing: -0.5px;
        }
        .hero p {
            font-size: 1rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 2rem;
            font-weight: 300;
        }
        /* Search Widget */
        .search-widget {
            background: rgba(255,255,255,0.96);
            border-radius: 60px;
            padding: 0.5rem;
            max-width: 1000px;
            margin: 2rem auto 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }
        .search-group {
            flex: 1;
            min-width: 150px;
            padding: 0.5rem 1rem;
            border-right: 1px solid var(--gray-border);
        }
        .search-group:last-child {
            border-right: none;
        }
        .search-group label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--black);
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }
        .search-group input,
        .search-group select {
            width: 100%;
            border: none;
            background: transparent;
            padding: 0.5rem 0;
            font-size: 0.95rem;
            color: var(--black);
            outline: none;
        }
        .search-btn {
            background: var(--accent);
            color: var(--white);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }
        .search-btn:hover {
            background: var(--accent-dark);
        }
        /* ----- SECTIONS ----- */
        .section {
            padding: 6rem 0;
        }
        .section-title {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 500;
            color: var(--black);
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }
        .section-subtitle {
            text-align: center;
            color: var(--gray-text);
            margin-bottom: 3rem;
            font-size: 1rem;
        }
        /* Amenities */
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        .amenity-item {
            padding: 2rem;
            background: var(--gray-bg);
            border-radius: 24px;
            transition: var(--transition);
        }
        .amenity-item:hover {
            transform: translateY(-5px);
            background: var(--white);
            box-shadow: var(--shadow-md);
        }
        .amenity-item i {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        .amenity-item h4 {
            color: var(--black);
            margin-bottom: 0.5rem;
        }
        /* Featured Rooms */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        .room-card {
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border: 1px solid var(--gray-border);
        }
        .room-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: transparent;
        }
        .room-img {
            height: 250px;
            background-size: cover;
            background-position: center;
        }
        .room-info {
            padding: 1.5rem;
        }
        .room-info h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--black);
        }
        .room-type {
            color: var(--accent);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        .room-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--black);
            margin: 0.8rem 0;
        }
        .room-price small {
            font-size: 0.9rem;
            font-weight: normal;
            color: var(--gray-text);
        }
        .btn-details {
            display: inline-block;
            background: transparent;
            border: 1px solid var(--black);
            color: var(--black);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            font-weight: 500;
            transition: var(--transition);
        }
        .btn-details:hover {
            background: var(--black);
            color: var(--white);
            border-color: var(--black);
        }
        /* CTA Section */
        .cta {
            background: var(--gray-bg);
            padding: 4rem;
            border-radius: 32px;
            text-align: center;
            margin: 3rem 0;
        }
        .cta h3 {
            font-size: 2rem;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            font-weight: 500;
        }
        .btn-cta {
            background: var(--accent);
            color: var(--white);
            padding: 0.8rem 2rem;
            border-radius: 40px;
            font-weight: 600;
            display: inline-block;
            margin-top: 1rem;
            transition: var(--transition);
        }
        .btn-cta:hover {
            background: var(--accent-dark);
        }
        /* ========== FOOTER ENHANCEMENTS ========== */
        .footer {
            background: var(--black);
            color: #CCCCCC;
            padding: 4rem 0 2rem;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .footer-section h4 {
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            letter-spacing: 0.5px;
            position: relative;
            display: inline-block;
        }
        .footer-section h4:after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--accent);
            border-radius: 2px;
        }

        .footer-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 500;
            color: var(--white);
            margin-bottom: 1rem;
            letter-spacing: -0.3px;
        }

        .brand-desc {
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            color: #CCCCCC;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            margin-right: 10px;
            color: #CCCCCC;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        .social-links a:hover {
            background: var(--accent);
            color: var(--white);
            transform: translateY(-2px);
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        .footer-section ul li {
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }
        .footer-section ul li a {
            color: #CCCCCC;
            transition: color 0.2s ease;
        }
        .footer-section ul li a:hover {
            color: var(--accent);
            padding-left: 3px;
        }
        .contact-info li {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .contact-info i {
            width: 20px;
            color: var(--accent);
            font-size: 0.9rem;
        }

        .newsletter p {
            font-size: 0.85rem;
            margin-bottom: 1rem;
            color: #AAAAAA;
        }
        .newsletter-form .form-group {
            display: flex;
            background: rgba(255,255,255,0.05);
            border-radius: 60px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.2s;
        }
        .newsletter-form .form-group:focus-within {
            border-color: var(--accent);
            background: rgba(255,255,255,0.08);
        }
        .newsletter-form input {
            flex: 1;
            background: transparent;
            border: none;
            padding: 0.8rem 1rem;
            color: var(--white);
            font-size: 0.9rem;
            outline: none;
            border-radius: 60px 0 0 60px;
        }
        .newsletter-form input::placeholder {
            color: #888;
        }
        .newsletter-form button {
            background: transparent;
            border: none;
            padding: 0 1.2rem;
            color: var(--accent);
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.2s;
            border-radius: 0 60px 60px 0;
        }
        .newsletter-form button:hover {
            color: var(--white);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            font-size: 0.8rem;
        }
        .legal-links a {
            color: #CCCCCC;
            margin-left: 0.5rem;
            transition: color 0.2s;
        }
        .legal-links a:hover {
            color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .header-inner {
                flex-direction: column;
            }
            .nav ul {
                gap: 1.2rem;
                justify-content: center;
            }
            .hero h2 {
                font-size: 2.2rem;
            }
            .search-widget {
                border-radius: 20px;
            }
            .search-group {
                border-right: none;
                width: 100%;
            }
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            .hero {
                min-height: 600px;
            }
            .section {
                padding: 3rem 0;
            }
            .cta {
                padding: 2rem;
            }
            .footer-grid {
                gap: 2rem;
            }
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            .legal-links a:first-child {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <h1>Grand Hotel</h1>
                </div>
                <nav class="nav">
                    <ul>
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="room_list.php">Rooms & Suites</a></li>
                        <li><a href="dining.php">Eat & Drink</a></li>
                        <li><a href="meetings.php">Meetings & Events</a></li>
                        <li><a href="offers.php">Offers</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <?php if ($is_logged_in): ?>
                        <a href="profile.php" class="btn-login">My Profile</a>
                        <a href="logout.php" class="btn-register">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">Sign In</a>
                        <a href="register.php" class="btn-register">Join</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section brand">
                    <h3 class="footer-logo">Grand Hotel</h3>
                    <p class="brand-desc">Luxury hospitality since 1995.<br>Creating unforgettable experiences in the heart of the city.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="room_list.php">Rooms & Suites</a></li>
                        <li><a href="dining.php">Eat & Drink</a></li>
                        <li><a href="meetings.php">Meetings & Events</a></li>
                        <li><a href="offers.php">Offers</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Visit Us</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Bukit Beruang, Melaka, Malaysia</li>
                        <li><i class="fas fa-phone-alt"></i> +607-666-8888</li>
                        <li><i class="fas fa-envelope"></i> info@grandhotel.com</li>
                    </ul>
                </div>

                <div class="footer-section newsletter">
                    <h4>Stay Inspired</h4>
                    <p>Receive exclusive offers & travel inspiration.</p>
                    <form class="newsletter-form" action="#" method="post">
                        <div class="form-group">
                            <input type="email" placeholder="Your email address" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Grand Hotel. All rights reserved.</p>
                <p class="legal-links">
                    <a href="#">Privacy Policy</a> | 
                    <a href="#">Terms of Use</a>
                </p>
            </div>
        </div>
    </footer>

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
</body>
</html>
<?php
// homepage.php - Grand Hotel Melaka 
require_once '../Shared/config.php';

$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
$is_logged_in = isLoggedIn();

include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hotel Melaka | Timeless Elegance</title>
    <link rel="stylesheet" href="css/homepage.css">
</head>
<body>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">
        <h2>A Grand Experience Awaits</h2>
        <p>Discover timeless elegance, world-class amenities, and impeccable service in the heart of Melaka</p>
        
        <div class="hero-search-wrapper">
            <div class="search-oval-card">
                <form action="../ChongEeLynn/accommodation.php" method="GET" class="search-form-horizontal">
                    <div class="search-group-compact">
                        <label>ARRIVE</label>
                        <input type="date" name="arrive" id="home_arrive" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="search-group-compact">
                        <label>DEPART</label>
                        <input type="date" name="depart" id="home_depart" required value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    <div class="search-group-compact">
                        <label>GUESTS</label>
                        <div class="guest-stepper-compact">
                            <button type="button" class="guest-btn-compact" onclick="changeHomeGuests(-1)">−</button>
                            <span class="guest-value-compact" id="homeGuestVal">2</span>
                            <input type="hidden" name="guests" id="homeGuestInput" value="2">
                            <button type="button" class="guest-btn-compact" onclick="changeHomeGuests(1)">+</button>
                        </div>
                    </div>
                    <button type="submit" class="search-btn-oval-home">CHECK AVAILABILITY</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- THREE-PANEL SECTION -->
<section class="three-panel-section">
    <div class="section-header">
        <h2>Discover Grand Hotel</h2>
        <div class="hotel-intro">
            <strong>Relax in Elegance at Grand Hotel Melaka</strong><br>
            Grand Hotel Melaka sits along the historic Straits, where old-world charm meets modern luxury. Step into a lobby that whispers the past, then retreat to warm, spacious rooms. Start your morning in our outdoor pool, enjoy Peranakan-inspired dishes, and unwind with a cocktail at sunset. Whether exploring the old town or hosting a gathering, every moment here is effortlessly memorable.
        </div>
    </div>
    <div class="three-panel-container">
        <!-- ROOMS & SUITES PART -->
        <div class="panel-card">
            <div class="panel-bg" style="background-image: url('images/accommodation.webp');"></div>
            <div class="panel-overlay"></div>
            <div class="panel-content">
                <h3>ROOMS & SUITES</h3>
                <h2>REST EASY,<br>YOUR WAY</h2>
                <a href="../ChongEeLynn/accommodation.php" class="panel-btn">VIEW ROOMS</a>
            </div>
        </div>
        <!-- FACILITIES PART -->
        <div class="panel-card">
            <div class="panel-bg" style="background-image: url('images/sky-fitness.jpeg');"></div>
            <div class="panel-overlay"></div>
            <div class="panel-content">
                <h3>FACILITIES</h3>
                <h2>LIVE IT UP,<br>YOUR STAY</h2>
                <a href="../ChangJingEn/facilities.php" class="panel-btn">VIEW FACILITIES</a>
            </div>
        </div>
        <!-- DINING PART -->
        <div class="panel-card">
            <div class="panel-bg" style="background-image: url('images/dining.jpeg');"></div>
            <div class="panel-overlay"></div>
            <div class="panel-content">
                <h3>EAT & DRINK</h3>
                <h2>TASTE THE BEST</h2>
                <a href="../Hannah/dining.php" class="panel-btn">DISCOVER MORE</a>
            </div>
        </div>
    </div>
</section>

<!-- LIMITED TIME OFFERS SECTION -->
<section class="limited-offers-fullwidth">
    <div class="limited-offers-container">
        <h2>LIMITED TIME OFFERS</h2>
        <div class="subhead">ELEVATE YOUR ESCAPE</div>
        <p>From the moment you book, get ready for a stay you'll never forget. Our exclusive packages and special offers are made to make your experience extra special. Whether you want a relaxing holiday, a business trip, or a short escape near the historic straits, we have the right deal for you. Don't miss these limited offers to upgrade your stay.</p>
        <a href="../ChongEeLynn/offers.php" class="btn-limited">VIEW OFFERS ></a>
    </div>
</section>

<!-- WEDDINGS & EVENTS SECTION -->
<section class="events-split-section">
    <div class="events-split-container">
        <div class="events-image-side"></div>
        <div class="events-text-side">
            <div class="events-text-content">
                <div class="events-subtitle">WEDDINGS & EVENTS</div>
                <h2 class="events-title">ELEVATE YOUR GATHERINGS IN THE HEART OF ELEGANCE</h2>
                <p class="events-description">
                    From high-profile corporate boardrooms to lavish wedding celebrations, our versatile spaces are designed to inspire. Experience seamless service, state-of-the-art technology, and a refined atmosphere tailored to your every need.
                </p>
                <a href="../ChangJingEn/events.php" class="events-explore-btn">EXPLORE SPACES →</a>
            </div>
        </div>
    </div>
</section>

<!-- LOCAL EXPERIENCES SECTION -->
<section class="experiences-section">
    <div class="experiences-container">
        <div class="experiences-image"></div>
        <div class="experiences-text-side">
            <div class="experiences-text-content">
                <div class="experiences-subtitle">LOCAL EXPERIENCES</div>
                <h2 class="experiences-title">UNLOCK THE MAGIC OF MELAKA</h2>
                <p class="experiences-description">
                   Step outside into a living museum. From the red Stadthuys and Christ Church to Jonker Walk night market. Enjoy a quiet river cruise past old shophouses, taste authentic Nyonya laksa, discover hidden boutiques, and catch a traditional Portuguese folk performance. Whether it's your first visit or your fiftieth, you'll find an experience that feels like home, yet full of wonder.
                </p>
                <a href="../ChongEeLynn/experiences.php" class="experiences-btn">EXPLORE MORE →</a>
            </div>
        </div>
    </div>
</section>

<!-- MEMBER CARD SECTION -->
<section class="member-card-section">
    <div class="member-card">
        <div class="member-grid">
            <div class="member-content">
                <h2>The Best Rates Are Always Here</h2>
                <p>Get the best prices plus free Wi-Fi when you become a Grand member.</p>
                <div class="member-buttons">
                    <a href="../ChangJingEn/register.php" class="btn-member-primary">Join for Free</a>
                    <a href="../ChangJingEn/login.php" class="btn-member-secondary">Sign In</a>
                </div>
            </div>
            <div class="member-features">
                <div class="member-feature-item">
                    <i class="fas fa-tag"></i>
                    <h4>BEST RATE GUARANTEE</h4>
                </div>
                <div class="member-feature-item">
                    <i class="fas fa-moon"></i>
                    <h4>EARN FREE NIGHTS</h4>
                </div>
                <div class="member-feature-item">
                    <i class="fas fa-wifi"></i>
                    <h4>FREE PREMIUM WI-FI</h4>
                </div>
                <div class="member-feature-item">
                    <i class="fas fa-gift"></i>
                    <h4>EARN POINTS</h4>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    //  Date validation 
    const today = new Date();
    const oneYearLater = new Date();
    oneYearLater.setFullYear(today.getFullYear() + 1);
    const todayStr = today.toISOString().split('T')[0];
    const oneYearLaterStr = oneYearLater.toISOString().split('T')[0];

    const homeArrive = document.getElementById('home_arrive');
    const homeDepart = document.getElementById('home_depart');

    // Set min and max attributes
    if (homeArrive) {
        homeArrive.min = todayStr;
        homeArrive.max = oneYearLaterStr;
        // Validate existing value (should not be needed because we set value to today)
        if (homeArrive.value && (homeArrive.value < todayStr || homeArrive.value > oneYearLaterStr)) {
            homeArrive.value = todayStr;
        }
        homeArrive.addEventListener('change', function() {
            if (this.value && this.value > oneYearLaterStr) {
                alert('Bookings are only allowed within 1 year from today. Please select an earlier date.');
                this.value = todayStr;
                return;
            }
            // Update depart date min
            if (homeDepart) {
                homeDepart.min = this.value;
                if (homeDepart.value && homeDepart.value < this.value) {
                    let newDepart = new Date(this.value);
                    newDepart.setDate(newDepart.getDate() + 1);
                    homeDepart.value = newDepart.toISOString().split('T')[0];
                }
            }
        });
    }

    if (homeDepart) {
        homeDepart.min = todayStr;
        homeDepart.max = oneYearLaterStr;
        if (homeDepart.value && (homeDepart.value < todayStr || homeDepart.value > oneYearLaterStr)) {
            let defaultDepart = new Date();
            defaultDepart.setDate(defaultDepart.getDate() + 1);
            homeDepart.value = defaultDepart.toISOString().split('T')[0];
        }
        homeDepart.addEventListener('change', function() {
            if (this.value && this.value > oneYearLaterStr) {
                alert('Bookings are only allowed within 1 year from today. Please select an earlier date.');
                let defaultDepart = new Date(homeArrive ? homeArrive.value : todayStr);
                defaultDepart.setDate(defaultDepart.getDate() + 1);
                this.value = defaultDepart.toISOString().split('T')[0];
            }
        });
    }

    // Function to handle the guest number stepper (Min: 1, Max: 6)
    function changeHomeGuests(delta) {
        let input = document.getElementById('homeGuestInput');
        let span = document.getElementById('homeGuestVal');
        let val = parseInt(input.value) + delta;
        if(val >= 1 && val <= 6) {
            input.value = val;
            span.textContent = val;
        }
    }

    // Form submission validation (prevent invalid dates)
    const homeForm = document.querySelector('.search-form-horizontal');
    if (homeForm) {
        homeForm.addEventListener('submit', function(e) {
            let arrive = homeArrive ? homeArrive.value : '';
            let depart = homeDepart ? homeDepart.value : '';
            
            if (arrive && arrive > oneYearLaterStr) {
                e.preventDefault();
                alert('❌ Arrival date must be within 1 year from today!');
                return false;
            }
            if (depart && depart > oneYearLaterStr) {
                e.preventDefault();
                alert('❌ Departure date must be within 1 year from today!');
                return false;
            }
            if (arrive && depart && depart <= arrive) {
                e.preventDefault();
                alert('❌ Departure date must be after arrival date!');
                return false;
            }
            return true;
        });
    }
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
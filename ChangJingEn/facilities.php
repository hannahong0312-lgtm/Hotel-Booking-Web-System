<?php
// facilities.php - Grand Hotel Melaka 
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <!-- Separate CSS file for facilities page – keeps styles modular -->
    <link rel="stylesheet" href="css/facilities.css">
</head>
<body>

<!-- HERO SECTION -->
<section class="hero-fullscreen">
    <div>
        <h1>World‑Class Facilities</h1>
        <p>Where every stay becomes an experience.</p>
    </div>
</section>

<!-- FEATURE LUXURY (PILLS NAVIGATION) -->
<section class="feature-luxury">
    <div class="container">
        <h2>ELEVATE YOUR GETAWAY</h2>
        <p>From sunrise swims to sunset cocktails, our world‑class facilities transform every moment into something extraordinary.</p>
        <div class="facility-pills">
            <div class="pill" data-target="facility-skyfitness"><i class="fas fa-dumbbell"></i> Sky Fitness</div>
            <div class="pill" data-target="facility-pool"><i class="fas fa-swimming-pool"></i> Rooftop Infinity Pool</div>
            <div class="pill" data-target="facility-spa"><i class="fas fa-spa"></i> The Spa</div>
            <div class="pill" data-target="facility-shop"><i class="fas fa-shopping-bag"></i> Retail Shop</div>
            <div class="pill" data-target="facility-rangers"><i class="fas fa-child"></i> Rangers Club</div>
            <div class="pill" data-target="facility-ev"><i class="fas fa-charging-station"></i> EV Charging Station</div>
        </div>
    </div>
</section>

<!-- FACILITY CARDS  -->
<div class="container">
    <!-- 1. Sky Fitness -->
    <div class="facility-row" id="facility-skyfitness">
        <div class="facility-image">
            <img src="images/sky-fitness.jpeg" alt="Fitness Centre">
        </div>
        <div class="facility-content">
            <h2>Sky Fitness</h2>
            <div class="facility-meta">
                <span><i class="fas fa-clock"></i> 24 hours</span>
                <span><i class="fas fa-dumbbell"></i> State-of-the-art equipment</span>
            </div>
            <p class="facility-desc">Maintain your peak performance with panoramic city views, premium cardio machines, free weights, and personal training sessions available upon request.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> 24/7 keycard access</li>
                <li><i class="fas fa-check-circle"></i> Complimentary towels & water</li>
                <li><i class="fas fa-check-circle"></i> Yoga studio & mats</li>
            </ul>
        </div>
    </div>

    <!-- 2. Rooftop Infinity Pool -->
    <div class="facility-row reverse" id="facility-pool">
        <div class="facility-image">
            <img src="images/rooftop-infinity-pool.jpeg" alt="Rooftop Pool">
        </div>
        <div class="facility-content">
            <h2>Rooftop Infinity Pool</h2>
            <div class="facility-meta">
                <span><i class="fas fa-clock"></i> 7:00 AM – 10:00 PM</span>
                <span><i class="fas fa-cocktail"></i> Poolside bar</span>
            </div>
            <p class="facility-desc">Take a dip above the city skyline. Our heated infinity pool offers breathtaking sunset views, sun loungers, and refreshing cocktails delivered to your side.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Towel service included</li>
                <li><i class="fas fa-check-circle"></i> Private cabanas available</li>
                <li><i class="fas fa-check-circle"></i> Underwater music system</li>
            </ul>
        </div>
    </div>

    <!-- 3. The Spa -->
    <div class="facility-row" id="facility-spa">
        <div class="facility-image">
            <img src="images/spa.jpeg" alt="Spa & Wellness">
        </div>
        <div class="facility-content">
            <h2>The Spa</h2>
            <div class="facility-meta">
                <span><i class="fas fa-clock"></i> 10:00 AM – 8:00 PM</span>
                <span><i class="fas fa-spa"></i> Signature treatments</span>
            </div>
            <p class="facility-desc">Escape into pure relaxation with aromatherapy massages, organic facials, and traditional Malay therapies. Our expert therapists will tailor each experience.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Couples suite & jacuzzi</li>
                <li><i class="fas fa-check-circle"></i> Steam room & sauna</li>
                <li><i class="fas fa-check-circle"></i> Herbal tea lounge</li>
            </ul>
        </div>
    </div>

    <!-- 4. Grand Hotel Retail Shop -->
    <div class="facility-row reverse" id="facility-shop">
        <div class="facility-image">
            <img src="images/hotel-gift-shop.jpg" alt="Retail Shop">
        </div>
        <div class="facility-content">
            <h2>Grand Hotel Retail Shop</h2>
            <div class="facility-meta">
                <span><i class="fas fa-clock"></i> 10:00 AM – 7:00 PM</span>
                <span><i class="fas fa-shopping-bag"></i> Souvenirs & Apparel</span>
            </div>
            <p class="facility-desc">Take home the little touches that make Grand Hotel unique. Discover a curated collection of local handicrafts, resort apparel, signature spa amenities, and exclusive merchandise – every item to relive your Grand Hotel experience.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Locally crafted souvenirs</li>
                <li><i class="fas fa-check-circle"></i> Signature bath & body products</li>
                <li><i class="fas fa-check-circle"></i> Complimentary gift wrapping</li>
            </ul>
        </div>
    </div>

    <!-- 5. Rangers Club -->
    <div class="facility-row" id="facility-rangers">
        <div class="facility-image">
            <img src="images/rangers-club.jpeg" alt="Rangers Club">
        </div>
        <div class="facility-content">
            <h2>Rangers Club</h2>
            <div class="facility-meta">
                <span><i class="fas fa-clock"></i> 2:00 PM – 5:00 PM</span>
                <span><i class="fas fa-child"></i> Ages 4–12</span>
            </div>
            <p class="facility-desc">A safe, supervised space where little ones can play, create, and explore. Packed with games, arts & crafts, and movie screenings.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Professional childcare staff</li>
                <li><i class="fas fa-check-circle"></i> Healthy snacks included</li>
                <li><i class="fas fa-check-circle"></i> Outdoor playground</li>
            </ul>
        </div>
    </div>

    <!-- 6. EV Charging Station -->
    <div class="facility-row reverse" id="facility-ev">
        <div class="facility-image">
            <img src="images/ev-charging.png" alt="EV Charging">
        </div>
        <div class="facility-content">
            <h2>EV Charging Station</h2>
            <div class="facility-meta">
                <span><i class="fas fa-plug"></i> 24/7 access</span>
                <span><i class="fas fa-leaf"></i> Sustainable luxury</span>
            </div>
            <p class="facility-desc">As part of our commitment to eco-friendly hospitality, we offer complimentary EV charging for in‑house guests. Two stations available in basement B1, accessible with your room key.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Connectors: Type 2 & CCS</li>
                <li><i class="fas fa-check-circle"></i> Power: 22kW AC</li>
                <li><i class="fas fa-check-circle"></i> First come, first served</li>
            </ul>
        </div>
    </div>
</div>

<script>
    // Attach click event to each pill button
    document.querySelectorAll('.pill').forEach(pill => {
        pill.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            if (targetId) {
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    // Smooth scroll to the target facility card
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Add temporary highlight class for visual feedback
                    targetElement.classList.add('highlight');
                    setTimeout(() => {
                        targetElement.classList.remove('highlight');
                    }, 1000);
                }
            }
        });
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
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

<!-- FACILITY CARDS -->
<div class="container">
    <!-- 1. Sky Fitness -->
    <div class="facility-row" id="facility-skyfitness">
        <div class="facility-image">
            <img src="images/sky-fitness.jpeg" alt="Fitness Centre">
        </div>
        <div class="facility-content">
            <h2>Sky Fitness</h2>
            <p class="facility-desc">Maintain your peak performance with panoramic city views, premium cardio machines, free weights, and personal training sessions available upon request.</p>
            <div class="facility-details">
                <div class="detail-block">
                    <div class="detail-label">OPERATING HOURS</div>
                    <div class="detail-value">24 hours, daily</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">EQUIPMENT</div>
                    <div class="detail-value">State-of-the-art machines & free weights</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">INCLUDED</div>
                    <div class="detail-value">Complimentary towels and water provided. Yoga studio access included for all guests.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Rooftop Infinity Pool (reverse) -->
    <div class="facility-row reverse" id="facility-pool">
        <div class="facility-image">
            <img src="images/rooftop-infinity-pool.jpeg" alt="Rooftop Pool">
        </div>
        <div class="facility-content">
            <h2>Rooftop Infinity Pool</h2>
            <p class="facility-desc">Take a dip above the city skyline. Our heated infinity pool offers breathtaking sunset views, sun loungers, and refreshing cocktails delivered to your side.</p>
            <div class="facility-details">
                <div class="detail-block">
                    <div class="detail-label">OPERATING HOURS</div>
                    <div class="detail-value">7:00 AM – 10:00 PM, daily</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">POOLSIDE BAR</div>
                    <div class="detail-value">Signature cocktails & light bites</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">GUEST PERKS</div>
                    <div class="detail-value">Towel service included. Private cabanas available for hotel guests.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. The Spa -->
    <div class="facility-row" id="facility-spa">
        <div class="facility-image">
            <img src="images/spa.jpeg" alt="Spa & Wellness">
        </div>
        <div class="facility-content">
            <h2>The Spa</h2>
            <p class="facility-desc">Escape into pure relaxation with aromatherapy massages, organic facials, and traditional Malay therapies. Our expert therapists will tailor each experience.</p>
            <div class="facility-details">
                <div class="detail-block">
                    <div class="detail-label">OPERATING HOURS</div>
                    <div class="detail-value">10:00 AM – 8:00 PM, daily</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">SIGNATURE TREATMENTS</div>
                    <div class="detail-value">Aromatherapy • Hot stone • Malay massage</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">COMPLIMENTARY ACCESS</div>
                    <div class="detail-value">Enjoy complimentary access to steam room, sauna, and herbal tea lounge with any treatment.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Grand Hotel Retail Shop -->
    <div class="facility-row reverse" id="facility-shop">
        <div class="facility-image">
            <img src="images/hotel-gift-shop.jpg" alt="Retail Shop">
        </div>
        <div class="facility-content">
            <h2>Grand Hotel Retail Shop</h2>
            <p class="facility-desc">Take home the little touches that make Grand Hotel unique. Discover a curated collection of local handicrafts, resort apparel, signature spa amenities, and exclusive merchandise.</p>
            <div class="facility-details">
                <div class="detail-block">
                    <div class="detail-label">OPERATING HOURS</div>
                    <div class="detail-value">10:00 AM – 7:00 PM, daily</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">PRODUCTS</div>
                    <div class="detail-value">Souvenirs • Apparel • Spa products</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Rangers Club -->
    <div class="facility-row" id="facility-rangers">
        <div class="facility-image">
            <img src="images/rangers-club.jpeg" alt="Rangers Club">
        </div>
        <div class="facility-content">
            <h2>Rangers Club</h2>
            <p class="facility-desc">A safe, supervised space where little ones can play, create, and explore. Packed with games, arts & crafts, and movie screenings.</p>
            <div class="facility-details">
                <div class="detail-block">
                    <div class="detail-label">OPERATING HOURS</div>
                    <div class="detail-value">2:00 PM – 5:00 PM, daily</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">AGE GROUP</div>
                    <div class="detail-value">Ages 4–12</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">INCLUDED</div>
                    <div class="detail-value">Healthy snacks included. Parental supervision required.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. EV Charging Station (reverse) -->
    <div class="facility-row reverse" id="facility-ev">
        <div class="facility-image">
            <img src="images/ev-charging.png" alt="EV Charging">
        </div>
        <div class="facility-content">
            <h2>EV Charging Station</h2>
            <p class="facility-desc">As part of our commitment to eco-friendly hospitality, we offer EV charging facilities for in‑house guests. Two stations available in basement B1, accessible with your room key.</p>
            <div class="facility-details">
                <div class="detail-block">
                    <div class="detail-label">ACCESS</div>
                    <div class="detail-value">24/7, self-service</div>
                </div>
                <div class="detail-block">
                    <div class="detail-label">POWER</div>
                    <div class="detail-value">22kW AC (Type 2 & CCS)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Smooth scroll and highlight for pill buttons
    document.querySelectorAll('.pill').forEach(pill => {
        pill.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            if (targetId) {
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    targetElement.classList.add('highlight');
                    setTimeout(() => targetElement.classList.remove('highlight'), 1000);
                }
            }
        });
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
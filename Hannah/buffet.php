<?php
include '../Shared/config.php';
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Grand Buffet | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/restaurant.css">
    <style>
        /* Auto-scrolling carousel */
        .carousel-wrapper {
            overflow: hidden;
            border-radius: 20px;
            margin: 1.5rem 0;
        }
        .carousel-track {
            display: flex;
            animation: scroll 16s linear infinite;
            width: calc(300px * 3);
        }
        .carousel-item {
            flex: 0 0 300px;
            margin: 0 10px;
            border-radius: 16px;
            overflow: hidden;
            height: 220px;
            background-size: cover;
            background-position: center;
        }
        @keyframes scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(calc(-300px * 3)); }
        }
        /* Pause on hover */
        .carousel-wrapper:hover .carousel-track {
            animation-play-state: paused;
        }
    </style>
</head>
<body>

<!-- HERO BANNER -->
<div class="menu-hero" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/buffet.jpeg') center/cover no-repeat;">
    <div class="menu-hero-content">
        <h1>The Grand Buffet</h1>
        <p>All-You-Can-Eat · RM 32 Per Person · FREE Drinks · Variety of Local & Asian Cuisine</p>
    </div>
</div>

<div class="container">
    <a href="dining.php#section-header" class="btn-menu-action" style="margin: 20px 0;"><i class="fas fa-arrow-left"></i> Back to Dining</a>
    <a href="dining.php#reservation-form" class="btn-menu-action"><i class="fas fa-calendar-check"></i> Reserve Now</a>

    <!-- BUFFET INFO BOX -->
    <div class="menu-section" style="text-align:center; background:#fff9f0; border:1px solid #f0d9b5;">
        <h2 class="section-title" style="border-color:#d4a852; color:#946c2a;">🍽️ Buffet Package</h2>
        <h2 style="font-size:2.2rem; color:#b98538; margin:1rem 0;">RM 32 / Person</h2>
        <p style="font-size:1.1rem; color:#555;">✅ All-You-Can-Eat | ✅ Free Flow Drinks | ✅ Daily Fresh Selection</p>
        <p style="font-size:1rem; color:#777;">Enjoy a wide spread of Nyonya, Local, Chinese & Dessert items</p>
    </div>

    <!-- AUTO-SCROLLING BUFFET IMAGES -->
    <div class="menu-section">
        <h2 class="section-title"><i class="fas fa-images"></i> What’s On The Buffet</h2>
        <div class="carousel-wrapper">
            <div class="carousel-track">
                <!-- Repeat images for infinite loop -->
                <div class="carousel-item" style="background-image: url('img/specialbuffet.webp');"></div>
                <div class="carousel-item" style="background-image: url('img/dessertbuffet.webp');"></div>
                <div class="carousel-item" style="background-image: url('img/westernbuffet.jpg');"></div>
                <!-- Duplicate images for smooth loop -->
                <div class="carousel-item" style="background-image: url('img/specialbuffet.webp');"></div>
                <div class="carousel-item" style="background-image: url('img/dessertbuffet.webp');"></div>
                <div class="carousel-item" style="background-image: url('img/westernbuffet.jpg');"></div>
            </div>
        </div>
        <p style="text-align:center; color:#666; margin-top:1rem;">
            Swipe to explore our full buffet spread — main dishes, desserts & Western favorites!
        </p>
    </div>

    <!-- FREE DRINKS SECTION -->
    <div class="menu-section">
        <h2 class="section-title"><i class="fas fa-glass-water"></i> Free Flow Drinks</h2>
        <div class="menu-grid">
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/chinesetea.jpeg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Chinese Tea</span><span class="price">FREE</span></div>
                    <div class="desc">Hot Chinese Tea - free flow for all buffet guests.</div>
                    <span class="diet-badge"><i class="fas fa-leaf"></i> Free Drink</span>
                </div>
            </div>

            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/juice.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Juice</span><span class="price">FREE</span></div>
                    <div class="desc">Cold refreshing juice - unlimited refill.</div>
                    <span class="diet-badge"><i class="fas fa-tint"></i> Free Flow</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
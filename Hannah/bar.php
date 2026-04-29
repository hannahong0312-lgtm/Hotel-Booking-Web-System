<?php
include '../Shared/config.php';
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooftop Bar | Grand Hotel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/restaurant.css">
</head>
<body>

<div class="menu-hero" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/hotelbar.jpg') center/cover no-repeat;">
    <div class="menu-hero-content">
        <h1>Rooftop Bar</h1>
        <p>Signature Cocktails · Fine Wines · Craft Beers · Elegant Lounge</p>
    </div>
</div>

<div class="container" class="back-reserve">
    <a href="dining.php#section-header" class="btn-menu-action" style="margin: 20px 0;"><i class="fas fa-arrow-left"></i> Back to Dining</a>
    <a href="dining.php#reservation-form" class="btn-menu-action"><i class="fas fa-calendar-check"></i> Reserve Now</a>
 
    <!-- Dinner Menu Section -->
    <div class="menu-section">
        <h2 class="section-title"><i class="fas fa-utensils"></i> Dinner Menu · Light Bites</h2>
        <div class="menu-grid">
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/bar-snacks.webp');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Bar Platter</span><span class="price">RM 58</span></div>
                    <div class="desc">Mixed savory platter with nuggets, onion rings, wings and dipping sauce.</div>
                    <span class="diet-badge"><i class="fas fa-drumstick-bite"></i> Sharing</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/truffle-fries.webp');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Truffle Fries</span><span class="price">RM 22</span></div>
                    <div class="desc">Crispy golden fries tossed with truffle oil and parmesan cheese.</div>
                    <span class="diet-badge"><i class="fas fa-seedling"></i> Popular</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/braciole.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Italian Beef Braciole</span><span class="price">RM 42</span></div>
                    <div class="desc">Slow-braised beef rolls stuffed with herbs, breadcrumbs, and cheese in rich tomato sauce.</div>
                    <span class="diet-badge"><i class="fas fa-utensils"></i> Signature</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/salmon.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Pan-Seared Salmon</span><span class="price">RM 48</span></div>
                    <div class="desc">Crispy skin salmon fillet served with seasonal vegetables and lemon butter sauce.</div>
                    <span class="diet-badge"><i class="fas fa-fish"></i> Healthy</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/spaghetti.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Pesto Spaghetti</span><span class="price">RM 32</span></div>
                    <div class="desc">Fresh spaghetti tossed with homemade basil pesto, parmesan, and a hint of chili flakes.</div>
                    <span class="diet-badge"><i class="fas fa-leaf"></i> Vegetarian</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature Cocktails  -->
    <div class="menu-section">
        <h2 class="section-title"><i class="fas fa-glass-martini"></i> Signature Cocktails · Wines & Beers</h2>
        <div class="menu-grid">
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/cocktail1.png');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Grand Sunset</span><span class="price">RM 42</span></div>
                    <div class="desc">Vodka, passion fruit, lime, elderflower, and a splash of prosecco.</div>
                    <span class="diet-badge"><i class="fas fa-glass-martini"></i> Signature</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/mojito.png');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Fresh Mojito</span><span class="price">RM 38</span></div>
                    <div class="desc">White rum, fresh mint, lime, sugar cane, and soda water.</div>
                    <span class="diet-badge"><i class="fas fa-leaf"></i> Refreshing</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/whiskey.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Classic Old Fashioned</span><span class="price">RM 45</span></div>
                    <div class="desc">Bourbon, sugar, bitters, and orange peel. Served on the rocks.</div>
                    <span class="diet-badge"><i class="fas fa-wine-bottle"></i> Classic</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/mocktail.jpeg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Tropical Breeze</span><span class="price">RM 28</span></div>
                    <div class="desc">Non-alcoholic mix of mango, pineapple, and fresh lime.</div>
                    <span class="diet-badge"><i class="fas fa-seedling"></i> Mocktail</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/redwine.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>House Red Wine</span><span class="price">RM 38</span></div>
                    <div class="desc">Smooth red blend with notes of cherry and vanilla.</div>
                    <span class="diet-badge"><i class="fas fa-wine-glass"></i> Red Wine</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/beer.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Craft Lager</span><span class="price">RM 25</span></div>
                    <div class="desc">Light, crisp, and refreshing local craft beer.</div>
                    <span class="diet-badge"><i class="fas fa-beer"></i> Beer</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
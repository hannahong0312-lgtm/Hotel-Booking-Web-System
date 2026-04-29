<?php
include '../Shared/config.php';
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Royale Restaurant Menu | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/restaurant.css">
</head>
<body>

<div class="menu-hero" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/nyonyadining.jpg') center/cover no-repeat;">
    <div class="menu-hero-content">
        <h1>Royale Restaurant</h1>
        <p>Authentic Szechuan · Nyonya Heritage · Exquisite Chinese Cuisine</p>
    </div>
</div>

<div class="container">
    <!-- Nyonya Specialties Section -->
    <a href="dining.php#section-header" class="btn-menu-action" style="margin: 20px 0;"><i class="fas fa-arrow-left"></i> Back to Dining</a>
    <a href="dining.php#reservation-form" class="btn-menu-action"><i class="fas fa-calendar-check"></i> Reserve Now</a>

    <div class="menu-section">
        <h2 class="section-title"><i class="fas fa-dragon"></i> Nyonya Heritage Delights</h2>
        <div class="menu-grid">
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/currylaksa.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Nyonya Laksa</span><span class="price">RM 32</span></div>
                    <div class="desc">Creamy coconut curry noodle soup with prawns, tofu puffs, fish cake & fresh mint. A Peranakan classic.</div>
                    <span class="diet-badge"><i class="fas fa-fish"></i> Signature Nyonya</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/pongteh.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Ayam Pongteh</span><span class="price">RM 48</span></div>
                    <div class="desc">Slow-braised chicken with fermented soybean paste, potatoes, and mushrooms. Sweet and savory.</div>
                    <span class="diet-badge"><i class="fas fa-drumstick-bite"></i> Family Recipe</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/rendang.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Beef Rendang</span><span class="price">RM 55</span></div>
                    <div class="desc">Dry curry beef slow-cooked with coconut milk, lemongrass, and a rich blend of spices.</div>
                    <span class="diet-badge"><i class="fas fa-pepper-hot"></i> Spicy & Rich</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/nyonyakuih.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Nyonya Kuih Platter</span><span class="price">RM 22</span></div>
                    <div class="desc">Assorted traditional Peranakan desserts: Ondeh-ondeh, Kueh Lapis, and Ang Ku Kueh.</div>
                    <span class="diet-badge"><i class="fas fa-candy-cane"></i> Sweet Bites</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Szechuan Specialties Section -->
    <div class="menu-section">
        <h2 class="section-title"><i class="fas fa-pepper-hot"></i> Bold Szechuan Flavors</h2>
        <div class="menu-grid">
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/peranakan.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Mapo Tofu</span><span class="price">RM 38</span></div>
                    <div class="desc">Silken tofu in spicy fermented bean paste, minced pork, and Sichuan peppercorns.</div>
                    <span class="diet-badge"><i class="fas fa-seedling"></i> Numbing & Spicy</span>
                </div>
            </div>
            <div class="menu-item">
                <div class="menu-img" style="background-image: url('img/cendol.jpg');"></div>
                <div class="item-details">
                    <div class="item-title"><span>Chilled Cendol Dessert</span><span class="price">RM 15</span></div>
                    <div class="desc">Traditional coconut dessert with green jelly, palm sugar, and red beans.</div>
                    <span class="diet-badge"><i class="fas fa-ice-cream"></i> Dessert</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
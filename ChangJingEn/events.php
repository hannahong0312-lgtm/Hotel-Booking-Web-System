<?php
// events.php - Grand Hotel Melaka
include '../Shared/header.php';

$form_success = false;
$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weddings & Events | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <style>
        /* Hero Section */
        .events-hero {
            position: relative;
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.4)), url('images/hero-placeholder.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .events-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .events-hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
        }
        .venues-section {
            padding: 4rem 2rem;
            background: #FFFFFF;
        }
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .section-header h2 {
            font-size: 2.5rem;
            font-family: 'Playfair Display', serif;
            color: #1A1A1A;
        }
        .section-header p {
            color: #6B6B6B;
        }
        .venues-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 2rem;
            max-width: 1300px;
            margin: 0 auto;
        }
        .venue-card {
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #EAE6E0;
        }
        .venue-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
        }
        .venue-image {
            height: 260px;
            background-size: cover;
            background-position: center;
            background-image: url('images/venue-placeholder.jpg');
        }
        .venue-content {
            padding: 1.8rem;
        }
        .venue-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            font-family: 'Playfair Display', serif;
            margin-bottom: 0.5rem;
        }
        .venue-meta {
            display: flex;
            gap: 1rem;
            margin: 0.8rem 0;
            font-size: 0.85rem;
            color: #D4AF37;
        }
        .venue-meta i {
            margin-right: 5px;
        }
        .venue-description {
            color: #5A5A5A;
            line-height: 1.5;
            margin: 1rem 0;
        }
        .venue-price {
            font-weight: 700;
            color: #1A1A1A;
            font-size: 1rem;
            margin-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .events-hero h1 { font-size: 2.2rem; }
            .venues-grid { gap: 1.5rem; }
        }
    </style>
</head>
<body>

<section class="events-hero">
    <div class="container">
        <h1>Weddings & Events</h1>
        <p>Create unforgettable moments in our elegant venues, tailored for your special day or corporate gathering.</p>
    </div>
</section>

<!-- Venues Section -->
<section class="venues-section">
    <div class="section-header">
        <h2>Our Venues</h2>
        <p>Discover the perfect setting for your celebration</p>
    </div>
    <div class="venues-grid">
        <!-- Grand Ballroom -->
        <div class="venue-card">
            <div class="venue-image" style="background-image: url('images/grand-ballroom.jpg');"></div>
            <div class="venue-content">
                <h3>Grand Ballroom</h3>
                <div class="venue-meta">
                    <span><i class="fas fa-users"></i> Up to 500 guests</span>
                    <span><i class="fas fa-church"></i> Indoor</span>
                </div>
                <p class="venue-description">Our magnificent ballroom features soaring ceilings, crystal chandeliers, and state-of-the-art AV systems. Perfect for weddings, galas, and large conferences.</p>
                <div class="venue-price">From RM 18,888</div>
            </div>
        </div>
        <!-- Garden Terrace -->
        <div class="venue-card">
            <div class="venue-image" style="background-image: url('images/garden-terrace.jpg');"></div>
            <div class="venue-content">
                <h3>Garden Terrace</h3>
                <div class="venue-meta">
                    <span><i class="fas fa-users"></i> Up to 200 guests</span>
                    <span><i class="fas fa-leaf"></i> Outdoor</span>
                </div>
                <p class="venue-description">A lush, open-air garden with panoramic city views. Ideal for sunset ceremonies, cocktail receptions, and intimate gatherings.</p>
                <div class="venue-price">From RM 12,888</div>
            </div>
        </div>
        <!-- Rooftop Pavilion -->
        <div class="venue-card">
            <div class="venue-image" style="background-image: url('images/rooftop-pavilion.jpg');"></div>
            <div class="venue-content">
                <h3>Rooftop Pavilion</h3>
                <div class="venue-meta">
                    <span><i class="fas fa-users"></i> Up to 120 guests</span>
                    <span><i class="fas fa-glass-cheers"></i> Rooftop</span>
                </div>
                <p class="venue-description">Enjoy breathtaking skyline views from our exclusive rooftop venue. Includes a private bar and customizable lighting for a chic celebration.</p>
                <div class="venue-price">From RM 8,888</div>
            </div>
        </div>
    </div>
</section>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
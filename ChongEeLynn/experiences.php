<?php
include '../Shared/config.php';

// Fetch main experiences
$main_query = "SELECT * FROM experiences WHERE type = 'main' AND is_active = 1 ORDER BY display_order";
$main_result = $conn->query($main_query);
$experiences = [];
while ($row = $main_result->fetch_assoc()) {
    $experiences[] = $row;
}

// Fetch local favorites
$fav_query = "SELECT * FROM experiences WHERE type = 'favorite' AND is_active = 1 ORDER BY display_order";
$fav_result = $conn->query($fav_query);
$localFavorites = [];
while ($row = $fav_result->fetch_assoc()) {
    $localFavorites[] = $row;
}

include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melaka Experiences | Heritage & Culture · Malaysia</title>
    <link rel="stylesheet" href="css/experiences.css">
</head>
<body>

<div class="hero">
    <div class="container hero-content">
        <div class="hero-badge">✨ UNESCO World Heritage · The Historic State</div>
        <h1>Discover <span>Melaka</span><br>Where Stories Live</h1>
        <p>Melaka City — a vibrant tapestry of Peranakan culture, Portuguese-Dutch colonial layers, and bustling Jonker Street. Every corner whispers centuries of spice routes, trade winds, and heartfelt traditions.</p>
        <div class="intro-text">🍜 Authentic experiences · Cultural gems · Riverside magic</div>
    </div>
</div>

<div class="container">
    <!-- Main Experiences Section -->
    <div class="section-head">
        <h2>✨ Unforgettable Melaka Experiences</h2>
        <p>From trishaw rides adorned with flowers to savory nyonya feasts — dive into the soul of Malaysia's most storied city.</p>
    </div>

    <div class="cards-grid">
        <?php foreach ($experiences as $exp): ?>
            <div class="exp-card">
                <div class="card-img" style="background-image: url('<?= htmlspecialchars($exp['image_path']) ?>'); background-size: cover; background-position: center;"></div>
                <div class="card-content">
                    <span class="card-category"><?= htmlspecialchars($exp['category']) ?></span>
                    <h3><?= htmlspecialchars($exp['title']) ?></h3>
                    <p><?= htmlspecialchars($exp['description']) ?></p>
                    <div class="exp-feature">
                        <?php if (!empty($exp['feature1'])): ?>
                            <span><?= htmlspecialchars($exp['feature1']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($exp['feature2'])): ?>
                            <span><?= htmlspecialchars($exp['feature2']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cultural Quote Section -->
    <div class="quote-section">
        <blockquote>"Melaka doesn't just show you history — it invites you to taste, touch, and walk inside it. From the scent of cloves to the rhythm of the rickshaw bells."</blockquote>
        <div class="quote-author">— Local proverb, heart of the Straits</div>
    </div>

    <!-- Local Favorites Section -->
    <div class="section-head">
        <h2>🌿 Beyond the classics · Local favorites</h2>
        <p>Insider experiences that reveal Melaka's living culture and warm hospitality.</p>
    </div>

    <div class="local-grid">
        <?php foreach ($localFavorites as $fav): ?>
            <div class="local-card">
                <div class="local-image">
                    <img src="<?= htmlspecialchars($fav['image_path']) ?>" alt="<?= htmlspecialchars($fav['title']) ?>">
                </div>
                <h3><?= htmlspecialchars($fav['title']) ?></h3>
                <p><?= htmlspecialchars($fav['description']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Practical Information Grid -->
    <div class="info-grid">
        <div class="info-item">
            <div class="info-icon">📅</div>
            <h4>Best time to visit</h4>
            <p>May–August (dry season) & year-end festive vibes.</p>
        </div>
        <div class="info-item">
            <div class="info-icon">🚆</div>
            <h4>Getting there</h4>
            <p>KLIA Express + bus 2h, or direct bus from KL (RM 10–15). Melaka Sentral hub.</p>
        </div>
        <div class="info-item">
            <div class="info-icon">🏨</div>
            <h4>Stay like a local</h4>
            <p>Heritage shophouse hotels in Jonker, or quiet homestays near Portuguese Settlement.</p>
        </div>
        <div class="info-item">
            <div class="info-icon">🛍️</div>
            <h4>Must-buy souvenir</h4>
            <p>Nyonya beaded slippers, pineapple tarts, and gula melaka (palm sugar).</p>
        </div>
    </div>
</div>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
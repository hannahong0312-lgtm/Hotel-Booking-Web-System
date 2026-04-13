<?php

// experiences.php
$experiences = [
    [
        'category' => '🏮 Heritage walk',
        'title' => 'Jonker Walk Night Market',
        'description' => 'Every weekend, Jonker Street transforms into a kaleidoscope of street food, antiques, and live performances. Savor chicken rice balls, cendol, and nyonya kuih under vintage lanterns.',
        'feature1' => '🌙 Fri & Sat evenings',
        'feature2' => '🎶 Live music & antiques',
        'gradient_start' => '#ad6b35',
        'gradient_end' => '#e09d5e',
        'placeholder_img' => 'https://placehold.co/600x400/f0e2d0/8B5A2B?text=Jonker+Walk'
    ],
    [
        'category' => '🏛️ Colonial landmarks',
        'title' => 'Stadthuys & Christ Church',
        'description' => 'The iconic salmon-red Dutch administrative building & 18th-century Christ Church. Explore the History & Ethnography Museum inside — uncover Melaka\'s maritime glory.',
        'feature1' => '📸 Dutch Square vibes',
        'feature2' => '🕰️ Open daily 9am-5pm',
        'gradient_start' => '#6d4c2e',
        'gradient_end' => '#b47c48',
        'placeholder_img' => 'https://placehold.co/600x400/f0e2d0/8B5A2B?text=Stadthuys'
    ],
    [
        'category' => '⚔️ Fortress legacy',
        'title' => 'A\'Famosa Fort & St. Paul\'s Hill',
        'description' => 'One of the oldest surviving European architectural remains in Asia. Climb St. Paul\'s Hill for panoramic views and read weathered tombstones.',
        'feature1' => '🌄 Sunset viewpoint',
        'feature2' => '📜 Free entry',
        'gradient_start' => '#3b5c4a',
        'gradient_end' => '#6d9e7c',
        'placeholder_img' => 'https://placehold.co/600x400/f0e2d0/8B5A2B?text=AFamosa'
    ],
    [
        'category' => '🚤 Riverside cruise',
        'title' => 'Melaka River Cruise',
        'description' => 'Glide along the Melaka River past murals, kampung houses, and colorful bridges. See the city from a different angle — soothing breeze and heritage murals.',
        'feature1' => '⛵ 45-min journey',
        'feature2' => '🎨 Instagram-worthy murals',
        'gradient_start' => '#b56542',
        'gradient_end' => '#d98e5c',
        'placeholder_img' => 'https://placehold.co/600x400/f0e2d0/8B5A2B?text=Melaka+River'
    ],
    [
        'category' => '🕯️ Spiritual heritage',
        'title' => 'Cheng Hoon Teng Temple',
        'description' => 'Malaysia\'s oldest functioning Chinese temple, adorned with intricate carvings and lacquer work. A serene escape showcasing Buddhist and Taoist elements.',
        'feature1' => '🏮 Built in 1645',
        'feature2' => '🙏 Free guided blessings',
        'gradient_start' => '#31625a',
        'gradient_end' => '#509b8c',
        'placeholder_img' => 'https://placehold.co/600x400/f0e2d0/8B5A2B?text=Cheng+Hoon+Teng'
    ],
    [
        'category' => '🍛 Culinary journey',
        'title' => 'Baba Nyonya Heritage Dinner',
        'description' => 'Experience authentic Peranakan cuisine: ayam pongteh, itek tim, and spicy devil curry. Join a cooking class or dine at a classic nyonya restaurant.',
        'feature1' => '🥢 Signature dishes',
        'feature2' => '🍚 Hands-on workshop',
        'gradient_start' => '#aa7c4a',
        'gradient_end' => '#dcae7a',
        'placeholder_img' => 'https://placehold.co/600x400/f0e2d0/8B5A2B?text=Nyonya+Cuisine'
    ]
];

// Local favorites data
$localFavorites = [
    ['emoji' => '🚲', 'title' => 'Trishaw Art Ride', 'desc' => 'Hop onto a flower-decked, karaoke-blasting trishaw — each one uniquely themed, from Disney to local flora.'],
    ['emoji' => '🍡', 'title' => 'Kampung Morten Walk', 'desc' => 'Traditional Malay village nestled along the river. See authentic stilt houses, friendly locals and try traditional kueh.'],
    ['emoji' => '🏺', 'title' => 'Melaka Straits Mosque', 'desc' => 'Floating mosque at sunset — golden domes reflect on water, creating a breathtaking spiritual atmosphere.'],
    ['emoji' => '🖌️', 'title' => 'The Shore Sky Tower', 'desc' => 'Panoramic 360° views of Melaka strait and heritage skyline — especially mesmerizing at golden hour.']
];

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
                <div class="card-img" style="background-image: linear-gradient(125deg, <?= htmlspecialchars($exp['gradient_start']) ?>, <?= htmlspecialchars($exp['gradient_end']) ?>), url('<?= htmlspecialchars($exp['placeholder_img']) ?>'); background-blend-mode: overlay; background-size: cover; background-position: center;"></div>
                <div class="card-content">
                    <span class="card-category"><?= htmlspecialchars($exp['category']) ?></span>
                    <h3><?= htmlspecialchars($exp['title']) ?></h3>
                    <p><?= htmlspecialchars($exp['description']) ?></p>
                    <div class="exp-feature">
                        <span><?= htmlspecialchars($exp['feature1']) ?></span>
                        <span><?= htmlspecialchars($exp['feature2']) ?></span>
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
                <div class="local-emoji"><?= htmlspecialchars($fav['emoji']) ?></div>
                <h3><?= htmlspecialchars($fav['title']) ?></h3>
                <p><?= htmlspecialchars($fav['desc']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Practical Information Grid -->
    <div class="info-grid">
        <div class="info-item">
            <div class="info-icon">📅</div>
            <h4>Best time to visit</h4>
            <p>May–August (dry season) & year-end festive vibes. Avoid Nov–Dec heavy rains.</p>
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
<?php
// facilities.php - Hotel Facilities & Amenities (Full-screen hero + dynamic cards)
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities & Amenities | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <style>
        /* 英雄区 – 全屏背景，与 about us 一致 */
        .facilities-hero {
            position: relative;
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.4)), url('https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .facilities-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }
        .facilities-hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto;
        }
        .section-header {
            text-align: center;
            margin: 4rem 0 2rem;
        }
        .section-header h2 {
            font-size: 2.5rem;
            font-family: 'Playfair Display', serif;
            color: #1A1A1A;
        }
        .section-header p {
            color: #6B6B6B;
            font-size: 1rem;
        }
        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 2rem;
            padding: 2rem 2rem 4rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .facility-card {
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.03);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #EAE6E0;
        }
        .facility-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
        }
        .facility-image {
            height: 240px;
            background-size: cover;
            background-position: center;
            background-color: #f5f2ed;
        }
        .facility-content {
            padding: 1.8rem;
        }
        .facility-icon {
            font-size: 2.2rem;
            color: #D4AF37;
            margin-bottom: 0.8rem;
        }
        .facility-content h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 0.6rem;
            font-family: 'Playfair Display', serif;
        }
        .facility-description {
            color: #5A5A5A;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        .facility-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 0.8rem;
            border-top: 1px solid #F0EBE3;
        }
        .open-time {
            font-size: 0.8rem;
            color: #6B6B6B;
        }
        .open-time i {
            color: #D4AF37;
            margin-right: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-available { background: #E8F0E7; color: #2D6A4F; }
        .status-maintenance { background: #FEF4E6; color: #B47C2E; }
        .status-closed { background: #FCE9E6; color: #B23C1C; }
        .cta-section {
            background: #F8F7F2;
            padding: 3rem 2rem;
            text-align: center;
            margin-top: 1rem;
        }
        .btn-primary {
            background: #D4AF37;
            color: #1A1A1A;
            padding: 12px 32px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: #C5A059;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .facilities-hero h1 { font-size: 2.2rem; }
            .facilities-hero p { font-size: 1rem; }
            .facilities-grid { padding: 1rem; gap: 1.5rem; }
            .facility-image { height: 200px; }
        }
    </style>
</head>
<body>

<!-- 英雄区 – 全屏背景（已更换为健身房图片） -->
<section class="facilities-hero">
    <div class="container">
        <h1>World‑Class Facilities</h1>
        <p>Experience luxury, comfort, and convenience with our premium amenities designed for every guest.</p>
    </div>
</section>

<div class="section-header">
    <h2>Our Amenities</h2>
    <p>Everything you need for a perfect stay</p>
</div>

<?php
// 设施数据（硬编码 + 图片 URL，无需数据库）
$facilities = [
    [
        'name' => 'Fitness Centre',
        'description' => 'State-of-the-art gym with cardio machines, free weights, and personal training sessions.',
        'icon' => 'fas fa-dumbbell',
        'open_time' => '6:00 AM – 10:00 PM',
        'status' => 'available',
        'image' => 'https://images.pexels.com/photos/1954524/pexels-photo-1954524.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2'
    ],
    [
        'name' => 'Spa & Wellness',
        'description' => 'Rejuvenate with our signature massages, facials, and traditional Malay treatments.',
        'icon' => 'fas fa-spa',
        'open_time' => '10:00 AM – 8:00 PM',
        'status' => 'available',
        'image' => 'https://images.pexels.com/photos/1268558/pexels-photo-1268558.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2'
    ],
    [
        'name' => 'Outdoor Pool',
        'description' => 'Infinity pool with sun loungers, poolside bar, and towel service. Perfect for relaxation.',
        'icon' => 'fas fa-swimming-pool',
        'open_time' => '7:00 AM – 8:00 PM',
        'status' => 'available',
        'image' => 'https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2'
    ],
    [
        'name' => 'Business Centre',
        'description' => '24/7 workstations, printing, scanning, and meeting rooms for up to 20 guests.',
        'icon' => 'fas fa-briefcase',
        'open_time' => '24/7',
        'status' => 'available',
        'image' => 'https://images.pexels.com/photos/669615/pexels-photo-669615.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2'
    ],
    [
        'name' => 'Kids Club',
        'description' => 'Supervised indoor and outdoor play area with activities for children aged 4-12.',
        'icon' => 'fas fa-child',
        'open_time' => '9:00 AM – 6:00 PM',
        'status' => 'maintenance',
        'image' => 'https://images.pexels.com/photos/366104/pexels-photo-366104.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2'
    ],
    [
        'name' => 'Rooftop Terrace',
        'description' => 'Panoramic city views, perfect for evening relaxation, cocktails, or private events.',
        'icon' => 'fas fa-umbrella-beach',
        'open_time' => '4:00 PM – 12:00 AM',
        'status' => 'available',
        'image' => 'https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2'
    ]
];
?>

<div class="facilities-grid">
    <?php foreach ($facilities as $facility): ?>
        <div class="facility-card">
            <div class="facility-image" style="background-image: url('<?php echo $facility['image']; ?>');"></div>
            <div class="facility-content">
                <div class="facility-icon">
                    <i class="<?php echo $facility['icon']; ?>"></i>
                </div>
                <h3><?php echo htmlspecialchars($facility['name']); ?></h3>
                <p class="facility-description"><?php echo htmlspecialchars($facility['description']); ?></p>
                <div class="facility-meta">
                    <span class="open-time"><i class="far fa-clock"></i> <?php echo $facility['open_time']; ?></span>
                    <span class="status-badge status-<?php echo $facility['status']; ?>">
                        <?php echo ($facility['status'] == 'available') ? 'Open' : (($facility['status'] == 'maintenance') ? 'Under Maintenance' : 'Closed'); ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 底部 CTA -->
<section class="cta-section">
    <div class="container">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem;">Need Assistance?</h3>
        <p style="margin: 1rem 0; color: #5A5A5A;">Our concierge team is available 24/7 to help you book facilities or answer any questions.</p>
        <a href="contact.php" class="btn-primary">Contact Concierge →</a>
    </div>
</section>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
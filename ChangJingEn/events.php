<?php
// events.php - Weddings & Events Page
include '../Shared/header.php';

// 处理表单提交
$form_success = false;
$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // 获取并清理输入
    $event_type = cleanInput($_POST['event_type'] ?? '');
    $event_date = cleanInput($_POST['event_date'] ?? '');
    $guests = intval($_POST['guests'] ?? 0);
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    
    // 验证
    if (empty($event_type)) $form_errors[] = "Please select an event type.";
    if (empty($event_date)) $form_errors[] = "Please select a preferred date.";
    elseif ($event_date < date('Y-m-d')) $form_errors[] = "Date cannot be in the past.";
    if ($guests < 1 || $guests > 1000) $form_errors[] = "Number of guests must be between 1 and 1000.";
    if (empty($name)) $form_errors[] = "Name is required.";
    if (empty($email)) $form_errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $form_errors[] = "Invalid email format.";
    if (empty($phone)) $form_errors[] = "Phone number is required.";
    
    if (empty($form_errors)) {
        // 插入数据库
        $sql = "INSERT INTO event_bookings (event_type, event_date, guests, name, email, phone, message, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssissss", $event_type, $event_date, $guests, $name, $email, $phone, $message);
        if ($stmt->execute()) {
            $form_success = true;
            // 可选：发送邮件通知
            // sendEventEmail($email, $name, $event_type, $event_date);
        } else {
            $form_errors[] = "Submission failed. Please try again later.";
        }
        $stmt->close();
    }
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
        /* 英雄区 – 全屏背景 */
        .events-hero {
            position: relative;
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.4)), url('https://images.pexels.com/photos/239122/pexels-photo-239122.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') center/cover no-repeat;
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
        /* 场地卡片区 */
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
        /* 套餐亮点 */
        .packages-section {
            background: #F8F7F2;
            padding: 4rem 2rem;
        }
        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .package-item {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 28px;
            border: 1px solid #EAE6E0;
        }
        .package-item i {
            font-size: 2.5rem;
            color: #D4AF37;
            margin-bottom: 1rem;
        }
        .package-item h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .package-item p {
            color: #6B6B6B;
            font-size: 0.9rem;
        }
        /* 咨询表单 */
        .inquiry-section {
            padding: 4rem 2rem;
            background: #FFFFFF;
        }
        .inquiry-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 32px;
            padding: 2.5rem;
            box-shadow: 0 12px 24px -12px rgba(0,0,0,0.05);
            border: 1px solid #EAE6E0;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1A1A1A;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #E0DCD6;
            border-radius: 24px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #D4AF37;
            box-shadow: 0 0 0 3px rgba(212,175,55,0.1);
        }
        .btn-submit {
            background: #D4AF37;
            color: #1A1A1A;
            border: none;
            padding: 14px 28px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            background: #C5A059;
            transform: translateY(-2px);
        }
        .alert {
            padding: 1rem;
            border-radius: 24px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .alert-success {
            background: #E8F0E7;
            color: #2D6A4F;
        }
        .alert-error {
            background: #FCE9E6;
            color: #B23C1C;
        }
        @media (max-width: 768px) {
            .events-hero h1 { font-size: 2.2rem; }
            .venues-grid { gap: 1.5rem; }
            .inquiry-container { padding: 1.5rem; }
        }
    </style>
</head>
<body>

<!-- 英雄区 -->
<section class="events-hero">
    <div class="container">
        <h1>Weddings & Events</h1>
        <p>Create unforgettable moments in our elegant venues, tailored for your special day or corporate gathering.</p>
    </div>
</section>

<!-- 场地展示 -->
<section class="venues-section">
    <div class="section-header">
        <h2>Our Venues</h2>
        <p>Discover the perfect setting for your celebration</p>
    </div>
    <div class="venues-grid">
        <!-- Grand Ballroom -->
        <div class="venue-card">
            <div class="venue-image" style="background-image: url('https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2');"></div>
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
            <div class="venue-image" style="background-image: url('https://images.pexels.com/photos/2387873/pexels-photo-2387873.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2');"></div>
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
            <div class="venue-image" style="background-image: url('https://images.pexels.com/photos/260922/pexels-photo-260922.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2');"></div>
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

<!-- 婚礼/活动套餐亮点 -->
<section class="packages-section">
    <div class="section-header">
        <h2>Why Choose Grand Hotel?</h2>
        <p>Exceptional services to make your event flawless</p>
    </div>
    <div class="packages-grid">
        <div class="package-item">
            <i class="fas fa-ring"></i>
            <h4>Wedding Specialist</h4>
            <p>Dedicated wedding planner to coordinate every detail.</p>
        </div>
        <div class="package-item">
            <i class="fas fa-utensils"></i>
            <h4>Gourmet Catering</h4>
            <p>Customizable menus by our award‑winning chefs.</p>
        </div>
        <div class="package-item">
            <i class="fas fa-music"></i>
            <h4>Entertainment</h4>
            <p>Live bands, DJs, and AV equipment available.</p>
        </div>
        <div class="package-item">
            <i class="fas fa-hotel"></i>
            <h4>Guest Accommodation</h4>
            <p>Special room rates for your attendees.</p>
        </div>
    </div>
</section>

<!-- 咨询表单 -->
<section class="inquiry-section">
    <div class="inquiry-container">
        <div class="section-header" style="margin-bottom: 1.5rem;">
            <h2>Request Information</h2>
            <p>Tell us about your event, and our team will get back to you within 24 hours.</p>
        </div>

        <?php if ($form_success): ?>
            <div class="alert alert-success">✓ Thank you! We have received your inquiry and will contact you shortly.</div>
        <?php elseif (!empty($form_errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($form_errors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Event Type *</label>
                <select name="event_type" required>
                    <option value="">Select an option</option>
                    <option value="wedding">Wedding</option>
                    <option value="corporate">Corporate Event</option>
                    <option value="birthday">Birthday Party</option>
                    <option value="anniversary">Anniversary</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Preferred Date *</label>
                <input type="date" name="event_date" required>
            </div>
            <div class="form-group">
                <label>Estimated Guests *</label>
                <input type="number" name="guests" placeholder="Number of guests" min="1" max="1000" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" placeholder="Your name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" placeholder="+60 XX XXX XXXX" required>
            </div>
            <div class="form-group">
                <label>Special Requests / Message</label>
                <textarea name="message" rows="4" placeholder="Any specific requirements or questions?"></textarea>
            </div>
            <button type="submit" name="submit_inquiry" class="btn-submit">Send Inquiry →</button>
        </form>
    </div>
</section>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
<?php
// facilities.php - Luxury Facilities with Smooth Scroll to Cards
include '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #FFFFFF;
        }
        /* 英雄区 - 全屏 */
        .hero-fullscreen {
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.4)), url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero-fullscreen h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 500;
            margin-bottom: 0.8rem;
        }
        .hero-fullscreen p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }
        /* 特色奢华区块 - 胶囊标签排版 */
        .feature-luxury {
            background: #FDFCF8;
            padding: 2.5rem 2rem;
            border-bottom: 1px solid #F0EBE3;
            text-align: center;
        }
        .feature-luxury .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .feature-luxury h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 500;
            color: #D4AF37;
            letter-spacing: 1px;
            margin-bottom: 1.2rem;
        }
        .feature-luxury p {
            color: #5A5A5A;
            line-height: 1.6;
            margin-bottom: 2rem;
            font-size: 1rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .facility-pills {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        .pill {
            background: #FFFFFF;
            border-radius: 60px;
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #2C2C2C;
            transition: all 0.2s ease;
            border: 1px solid #EAE6E0;
            cursor: pointer;
        }
        .pill i {
            color: #D4AF37;
            margin-right: 8px;
        }
        .pill:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: #D4AF37;
        }
        /* 横向布局容器 */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .facility-row {
            display: flex;
            align-items: center;
            gap: 4rem;
            padding: 4rem 0;
            border-bottom: 1px solid #F0EBE3;
            scroll-margin-top: 100px; /* 滚动时避免被固定头部遮挡 */
            transition: background 0.3s;
        }
        .facility-row:last-child {
            border-bottom: none;
        }
        .facility-row.reverse {
            flex-direction: row-reverse;
        }
        .facility-row.highlight {
            background: rgba(212, 175, 55, 0.05);
            border-radius: 28px;
        }
        .facility-image {
            flex: 1;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.08);
        }
        .facility-image img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s;
        }
        .facility-image:hover img {
            transform: scale(1.02);
        }
        .facility-content {
            flex: 1;
            padding: 1rem;
        }
        .facility-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 500;
            margin-bottom: 1rem;
            color: #1A1A1A;
        }
        .facility-meta {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
            color: #D4AF37;
        }
        .facility-meta i {
            margin-right: 5px;
        }
        .facility-desc {
            color: #5A5A5A;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .feature-list {
            list-style: none;
            margin-top: 1rem;
        }
        .feature-list li {
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: #2C2C2C;
        }
        .feature-list i {
            color: #D4AF37;
            width: 20px;
        }
        @media (max-width: 900px) {
            .facility-row, .facility-row.reverse {
                flex-direction: column;
                gap: 2rem;
                padding: 3rem 0;
            }
            .facility-image img {
                height: 280px;
            }
            .hero-fullscreen h1 {
                font-size: 2.2rem;
            }
            .hero-fullscreen p {
                font-size: 1rem;
            }
            .feature-luxury h2 {
                font-size: 1.8rem;
            }
            .pill {
                padding: 0.5rem 1.2rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>

<!-- 英雄区 - 全屏 -->
<section class="hero-fullscreen">
    <div>
        <h1>World‑Class Facilities</h1>
        <p>Where every stay becomes an experience.</p>
    </div>
</section>

<!-- 特色奢华区块 - 胶囊标签（可点击跳转） -->
<section class="feature-luxury">
    <div class="container">
        <h2>ELEVATE YOUR GETAWAY</h2>
        <p>From sunrise swims to sunset cocktails, our world‑class facilities transform every moment into something extraordinary.</p>
        <div class="facility-pills">
            <div class="pill" data-target="facility-skyfitness"><i class="fas fa-dumbbell"></i> Sky Fitness</div>
            <div class="pill" data-target="facility-pool"><i class="fas fa-swimming-pool"></i> Rooftop Infinity Pool</div>
            <div class="pill" data-target="facility-spa"><i class="fas fa-spa"></i> The Spa</div>
            <div class="pill" data-target="facility-business"><i class="fas fa-briefcase"></i> Business Centre</div>
            <div class="pill" data-target="facility-kids"><i class="fas fa-child"></i> Rangers Kids Club</div>
            <div class="pill" data-target="facility-ev"><i class="fas fa-charging-station"></i> EV Charging Station</div>
        </div>
    </div>
</section>

<!-- 横向设施列表（带ID，用于跳转） -->
<div class="container">
    <!-- 设施 1: 健身房 -->
    <div class="facility-row" id="facility-skyfitness">
        <div class="facility-image">
            <img src="https://images.pexels.com/photos/1954524/pexels-photo-1954524.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2" alt="Fitness Centre">
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

    <!-- 设施 2: 屋顶泳池 -->
    <div class="facility-row reverse" id="facility-pool">
        <div class="facility-image">
            <img src="https://images.pexels.com/photos/261102/pexels-photo-261102.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2" alt="Rooftop Pool">
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

    <!-- 设施 3: 水疗中心 -->
    <div class="facility-row" id="facility-spa">
        <div class="facility-image">
            <img src="https://images.pexels.com/photos/1268558/pexels-photo-1268558.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2" alt="Spa & Wellness">
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

    <!-- 设施 4: 商务中心 -->
    <div class="facility-row reverse" id="facility-business">
        <div class="facility-image">
            <img src="https://images.pexels.com/photos/669615/pexels-photo-669615.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2" alt="Business Centre">
        </div>
        <div class="facility-content">
            <h2>Business Centre</h2>
            <div class="facility-meta">
                <span><i class="fas fa-clock"></i> 24/7</span>
                <span><i class="fas fa-briefcase"></i> Meeting rooms</span>
            </div>
            <p class="facility-desc">Fully equipped workspace with high-speed WiFi, printing, scanning, and private meeting rooms for up to 20 guests. On‑site secretarial support available.</p>
            <ul class="feature-list">
                <li><i class="fas fa-check-circle"></i> Complimentary coffee & tea</li>
                <li><i class="fas fa-check-circle"></i> Video conferencing facilities</li>
                <li><i class="fas fa-check-circle"></i> Day office packages</li>
            </ul>
        </div>
    </div>

    <!-- 设施 5: 儿童俱乐部 -->
    <div class="facility-row" id="facility-kids">
        <div class="facility-image">
            <img src="https://images.pexels.com/photos/366104/pexels-photo-366104.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2" alt="Kids Club">
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

    <!-- 设施 6: EV Charging Station -->
    <div class="facility-row reverse" id="facility-ev">
        <div class="facility-image">
            <img src="https://images.pexels.com/photos/12035698/pexels-photo-12035698.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&dpr=2" alt="EV Charging">
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
    // 胶囊标签点击平滑滚动到对应卡片
    document.querySelectorAll('.pill').forEach(pill => {
        pill.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            if (targetId) {
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    // 平滑滚动
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // 添加短暂高亮效果
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
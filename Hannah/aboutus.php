<?php
// Include header
include '../Shared/header.php';
?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Hotel | About Us & Contact Services</title>
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- About Us Page CSS (separate) -->
    <style>
        /* ========== ABOUT US PAGE SPECIFIC CSS ========== */
        .about-hero {
            position: relative;
            height: 100vh;
            min-height: 700px;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.3)), url('https://www.swissgarden.com/residences-genting/wp-content/uploads/sites/11/2020/03/Executive-2-bedroom-Master-Room.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .about-hero h1 {
            font-family: 'Playfair Display', serif;
            margin-bottom: 1rem;
            max-width: 900px;
            padding: 0 2rem;
            color: white;
            font-size: 3rem;
        }

        .about-hero p {
            font-size: 1rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .about-story {
            padding: 5rem 0;
            background: #FFFFFF;
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .story-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            margin-bottom: 1.2rem;
            color: #1A1A1A;
        }

        .story-text p {
            color: #666666;
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .story-feature-list {
            list-style: none;
            margin-top: 1.5rem;
        }

        .story-feature-list li {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .story-feature-list i {
            color: var(--gold);
            font-size: 1rem;
            width: 20px;
        }

        .story-image img {
            width: 100%;
            border-radius: 24px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            object-fit: cover;
            height: 400px;
        }

        .services-contact {
            background: #F8F8F8;
            padding: 3rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.2rem;
            font-family: 'Playfair Display', serif;
            margin-bottom: 0.8rem;
            color: #1A1A1A;
        }

        .section-subtitle {
            text-align: center;
            color: #666666;
            margin-bottom: 3rem;
            font-size: 1rem;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .service-card {
            background: #FFFFFF;
            border-radius: 28px;
            padding: 2rem 1.8rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.25s ease;
            border: 1px solid #E5E5E5;
            text-align: center;
        }

        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            border-color: transparent;
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 107, 74, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .service-icon i {
            font-size: 2.5rem;
            color: var(--gold-dark);
        }

        .service-card h3 {
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
            color: #1A1A1A;
        }

        .service-card p {
            color: #666666;
            font-size: 0.9rem;
            margin-bottom: 1.2rem;
            line-height: 1.5;
        }

        .contact-detail {
            background: #F8F8F8;
            border-radius: 40px;
            padding: 0.8rem 1rem;
            margin-top: 0.5rem;
        }

        .contact-detail a {
            font-weight: 600;
            color: var(--gold);
            transition: all 0.25s ease;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .service-hours {
            font-size: 0.8rem;
            color: #666666;
            margin-top: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .faq-section {
            background: #FFFFFF;
            padding: 3rem 0 7rem;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .faq-item {
            background: #F8F8F8;
            padding: 1.5rem;
            border-radius: 20px;
            transition: all 0.25s ease;
            border-left: 4px solid var(--gold);
        }

        .faq-item h4 {
            font-weight: 600;
            margin-bottom: 0.6rem;
            display: flex;
            gap: 0.6rem;
            align-items: center;
            font-size: 1rem;
            color: #1A1A1A;
        }

        .faq-item h4 i {
            color: var(--gold-dark);
        }

        .faq-item p {
            color: #666666;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 992px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
            .story-image img {
                height: auto;
                max-height: 350px;
            }
            .about-hero h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1.5rem;
            }
            .section-title {
                font-size: 1.8rem;
            }
            .service-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- ========== ABOUT US  ========== -->
    <section class="about-hero">
        <div class="container">
            <h1>About Us</h1>
            <p>Stylish luxury accommodation near Melaka city centre with world-class service since 2007</p>
        </div>
    </section>

    <!-- ========== OUR STORY SECTION ========== -->
    <section class="about-story">
        <div class="container two-columns">
            <div class="story-text">
                <h2>Our Story & Heritage</h2>
                <p>With a prime spot in a thriving urban enclave, Grand Hotel places you near the Melaka City centre for business, lively entertainment and major cultural attractions. Our stylish accommodation, spacious meeting rooms, tasteful dining options and complimentary WiFi ensure both comfort and accessibility whether travelling for business or pleasure.</p>
                <p>The magic of Melaka beckons outside your room, where the city's best cultural attractions, shopping and dining are within a 10-minute drive. After a morning swim in the outdoor pool and hearty buffet breakfast, you can immerse yourself in centuries of history, cruise the river or explore the ever-lively Jonker Street.</p>
                <ul class="story-feature-list">
                    <li><i class="fas fa-check-circle"></i> 12+ Luxury Rooms & Suites</li>
                    <li><i class="fas fa-check-circle"></i> Award-winning Restaurant & Bar</li>
                    <li><i class="fas fa-check-circle"></i> 24/7 Concierge & Room Service</li>
                    <li><i class="fas fa-check-circle"></i> 5-star reviewed facilities</li>
                </ul>
            </div>
            <div class="story-image">
                <img src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Grand Hotel Luxury Lobby">
            </div>
        </div>
    </section>

    <!-- ========== CONTACT SERVICES SECTION ========== -->
    <section class="services-contact">
        <div class="container">
            <h2 class="section-title">Contact Our Services</h2>
            <p class="section-subtitle">Dedicated teams ready to assist you — reach out directly to the department you need</p>
            
            <div class="contact-grid">
                <!-- Room Service Department -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <h3>Room Service</h3>
                    <p>In-room dining fresh meals delivered to your door & facilities service.</p>
                    <div class="contact-detail">
                        <a href="tel:+6062896888"><i class="fas fa-phone-alt"></i> +60 6 289 6885 ext. 220</a>
                    </div>
                    <div class="contact-detail">
                        <a href="mailto:rooms@grandhotel.com"><i class="fas fa-envelope"></i> rooms@grandhotel.com</a>
                    </div>
                    <div class="service-hours">
                        <i class="far fa-clock"></i> Available 24/7
                    </div>
                </div>

                <!-- Dining Reservation Department -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Dining Reservations</h3>
                    <p>Book tables at The Palette Café, Signature Grill, or private dining experiences.</p>
                    <div class="contact-detail">
                        <a href="tel:+6062896888"><i class="fas fa-phone-alt"></i> +60 6 289 6886</a>
                    </div>
                    <div class="contact-detail">
                        <a href="mailto:dining@grandhotel.com"><i class="fas fa-envelope"></i> dining@grandhotel.com</a>
                    </div>
                    <div class="service-hours">
                        <i class="far fa-clock"></i> Daily 7:00 AM - 10:30 PM
                    </div>
                </div>

                <!-- Weddings & Events Department -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-ring"></i>
                    </div>
                    <h3>Weddings & Events</h3>
                    <p>Plan your dream wedding or corporate gathering with our expert event planners.</p>
                    <div class="contact-detail">
                        <a href="tel:+6062896888"><i class="fas fa-phone-alt"></i> +60 6 289 6887</a>
                    </div>
                    <div class="contact-detail">
                        <a href="mailto:events@grandhotel.com"><i class="fas fa-envelope"></i> events@grandhotel.com</a>
                    </div>
                    <div class="service-hours">
                        <i class="far fa-clock"></i> Mon-Sat: 9:00 AM - 7:00 PM
                    </div>
                </div>

                <!-- Front Desk / Guest Services -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Front Desk</h3>
                    <p>Check-in assistance, transportation and special requests.</p>
                    <div class="contact-detail">
                        <a href="tel:+6062896888"><i class="fas fa-phone-alt"></i> +60 6 289 6888 ext. 330</a>
                    </div>
                    <div class="contact-detail">
                        <a href="mailto:frontdesk@grandhotel.com"><i class="fas fa-envelope"></i> info@grandhotel.com</a>
                    </div>
                    <div class="service-hours">
                        <i class="far fa-clock"></i> 24/7 Guest Support
                    </div>
                </div>

                <!-- Sales & Group Bookings -->
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Sales & Group Bookings</h3>
                    <p>Corporate rates, group accommodations, and exclusive packages.</p>
                    <div class="contact-detail">
                        <a href="tel:+6062896888"><i class="fas fa-phone-alt"></i> +60 6 289 6883</a>
                    </div>
                    <div class="contact-detail">
                        <a href="mailto:sales@grandhotel.com"><i class="fas fa-envelope"></i> sales@grandhotel.com</a>
                    </div>
                    <div class="service-hours">
                        <i class="far fa-clock"></i> Mon-Fri: 9:00 AM - 6:00 PM
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== FAQ SECTION ========== -->
    <section class="faq-section">
        <div class="container">
            <h2 class="section-title">FAQs</h2>
            <p class="section-subtitle">Quick answers to common queries about Grand Hotel</p>
            
            <div class="faq-grid">
                <div class="faq-item">
                    <h4><i class="fas fa-smoking-ban"></i> Do you have a No Smoking room?</h4>
                    <p>Yes, we offer dedicated non-smoking rooms across all categories. Please request during booking.</p>
                </div>
                <div class="faq-item">
                    <h4><i class="fas fa-credit-card"></i> Do I need to pay upon check-in?</h4>
                    <p>Full payment is required at check-in unless you have a pre-paid reservation. We accept all major cards.</p>
                </div>
                <div class="faq-item">
                    <h4><i class="fas fa-wifi"></i> Do you have free WiFi?</h4>
                    <p>Complimentary high-speed WiFi is available throughout the hotel for all guests.</p>
                </div>
                <div class="faq-item">
                    <h4><i class="fas fa-parking"></i> Do you have parking and what is the fee?</h4>
                    <p>Yes, secure underground parking is available for RM 10 per day for in-house guests.</p>
                </div>
                <div class="faq-item">
                    <h4><i class="fas fa-clock"></i> What is the check-in and check-out time?</h4>
                    <p>Check-in: 3:00 PM | Check-out: 12:00 PM. Late check-out subject to availability.</p>
                </div>
                <div class="faq-item">
                    <h4><i class="fas fa-paw"></i> May I bring my pet into the hotel?</h4>
                    <p>Only service animals are permitted. We recommend contacting us for special arrangements.</p>
                </div>
            </div>
        </div>
    </section>
</body>

<?php
// Include footer
include '../Shared/footer.php';
?>

</html>
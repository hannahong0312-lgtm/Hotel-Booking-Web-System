<?php
// terms.php - Terms & Conditions for Grand Hotel Melaka
require_once '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions | Grand Hotel Melaka</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <style>
        .header {
            background: rgba(26, 26, 26, 0.95);
            padding: 0.8rem 0;
        }
        .terms-container {
            max-width: 900px;
            margin: 120px auto 80px;
            padding: 0 2rem;
        }
        .terms-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .terms-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        .terms-header p {
            color: #6B6B6B;
            font-size: 0.9rem;
        }
        .terms-section {
            margin-bottom: 2rem;
        }
        .terms-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: #C5A059;
            margin-bottom: 0.8rem;
            padding-bottom: 0.3rem;
            border-bottom: 1px solid #EAE6E0;
        }
        .terms-section p, .terms-section li {
            color: #5A5A5A;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .terms-section ul {
            padding-left: 1.5rem;
            margin: 0.5rem 0;
        }
        .last-updated {
            margin-top: 3rem;
            padding-top: 1rem;
            border-top: 1px solid #EAE6E0;
            text-align: center;
            font-size: 0.8rem;
            color: #888;
        }
        @media (max-width: 768px) {
            .terms-container {
                margin-top: 100px;
                padding: 0 1rem;
            }
            .terms-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<div class="terms-container">
    <div class="terms-header">
        <h1>Terms & Conditions</h1>
        <p>Effective from <?php echo date('F j, Y'); ?></p>
    </div>

    <div class="terms-section">
        <h2>1. Introduction</h2>
        <p>Welcome to Grand Hotel Melaka. These Terms & Conditions ("Terms") govern your use of our website, mobile applications, online booking services, and your membership in the Grand Rewards loyalty programme. By accessing or using our services, you agree to be bound by these Terms.</p>
    </div>

    <div class="terms-section">
        <h2>2. Membership Registration</h2>
        <p>To become a Grand Rewards member, you must complete the registration form with accurate, current, and complete information. You are responsible for maintaining the confidentiality of your login credentials and for all activities that occur under your account. You may only hold one active membership account at any time.</p>
    </div>

    <div class="terms-section">
        <h2>3. Privacy & Data Protection</h2>
        <p>We collect and process your personal data (including name, email, phone, and booking history) to provide our services, manage your membership, and communicate with you about offers (if you have opted in). We do not sell your personal data to third parties. For full details, please review our <a href="privacy.php" style="color: #C5A059;">Privacy Policy</a>.</p>
    </div>

    <div class="terms-section">
        <h2>4. Bookings & Cancellations</h2>
        <p>Room reservations are subject to availability. Cancellation policies vary by rate plan – please refer to your booking confirmation. Generally, for flexible rates, free cancellation is allowed up to 24 hours before check‑in (local hotel time). Late cancellations or no‑shows may incur a charge equal to one night's room rate plus applicable taxes.</p>
    </div>

    <div class="terms-section">
        <h2>5. Grand Rewards Loyalty Programme</h2>
        <p>Members earn points on eligible room spend (10 points per RM1). Points may be redeemed for future stays, room upgrades, dining credits, and other benefits as specified from time to time. Points have no cash value and are non‑transferable. Grand Hotel reserves the right to modify or terminate the loyalty programme with reasonable notice.</p>
    </div>

    <div class="terms-section">
        <h2>6. Member Conduct & Account Cancellation</h2>
        <p>We may suspend or terminate your membership and forfeit any accrued points if you violate these Terms, provide false information, abuse programme benefits, or engage in fraudulent activity. In such cases, you may be permanently barred from re‑enrolling.</p>
    </div>

    <div class="terms-section">
        <h2>7. Intellectual Property</h2>
        <p>All content on this website – including text, graphics, logos, and images – is the property of Grand Hotel Melaka or its licensors and is protected by copyright and trademark laws. Unauthorised reproduction or distribution is prohibited.</p>
    </div>

    <div class="terms-section">
        <h2>8. Limitation of Liability</h2>
        <p>To the fullest extent permitted by law, Grand Hotel Melaka shall not be liable for any indirect, incidental, or consequential damages arising from your use of our services. Our total liability is limited to the amount paid for your confirmed booking.</p>
    </div>

    <div class="terms-section">
        <h2>9. Amendments to Terms</h2>
        <p>We may update these Terms from time to time. Any changes will be posted on this page with an updated effective date. Your continued use of our services after any such changes constitutes your acceptance of the new Terms.</p>
    </div>

    <div class="terms-section">
        <h2>10. Governing Law & Dispute Resolution</h2>
        <p>These Terms shall be governed by and construed in accordance with the laws of Malaysia. Any disputes arising from these Terms shall be subject to the exclusive jurisdiction of the courts of Melaka, Malaysia.</p>
    </div>

    <div class="terms-section">
        <h2>11. Contact Us</h2>
        <p>If you have any questions about these Terms, please contact us:<br>
        Email: <a href="mailto:legal@grandhotel.com" style="color: #C5A059;">info.grandhotel@gmail.com</a><br>
        Phone: +607-666-8888</p>
    </div>

    <div class="last-updated">
        &copy; <?php echo date('Y'); ?> Grand Hotel Melaka. All rights reserved.
    </div>
</div>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
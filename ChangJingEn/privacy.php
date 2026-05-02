<?php
// privacy.php - Privacy Policy for Grand Hotel Melaka
require_once '../Shared/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | Grand Hotel Melaka</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <style>
        .header {
            background: rgba(26, 26, 26, 0.95);
            padding: 0.8rem 0;
        }
        .privacy-container {
            max-width: 900px;
            margin: 120px auto 80px;
            padding: 0 2rem;
        }
        .privacy-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .privacy-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: #1A1A1A;
            margin-bottom: 0.5rem;
        }
        .privacy-header p {
            color: #6B6B6B;
            font-size: 0.9rem;
        }
        .privacy-section {
            margin-bottom: 2rem;
        }
        .privacy-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: #C5A059;
            margin-bottom: 0.8rem;
            padding-bottom: 0.3rem;
            border-bottom: 1px solid #EAE6E0;
        }
        .privacy-section p, .privacy-section li {
            color: #5A5A5A;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        .privacy-section ul {
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
            .privacy-container {
                margin-top: 100px;
                padding: 0 1rem;
            }
            .privacy-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<div class="privacy-container">
    <div class="privacy-header">
        <h1>Privacy Policy</h1>
        <p>Effective from <?php echo date('F j, Y'); ?></p>
    </div>

    <div class="privacy-section">
        <h2>1. Who We Are</h2>
        <p>Grand Hotel Melaka ("we", "us", "our") operates the website <strong>www.grandhotel.com.my</strong> and the Grand Rewards loyalty programme. This Privacy Policy explains how we collect, use, and protect your personal information when you interact with us.</p>
    </div>

    <div class="privacy-section">
        <h2>2. Information We Collect</h2>
        <p>We may collect the following categories of personal information:</p>
        <ul>
            <li><strong>Identity Data:</strong> Name, date of birth, nationality.</li>
            <li><strong>Contact Data:</strong> Email address, phone number, postal address.</li>
            <li><strong>Booking Data:</strong> Room preferences, check‑in/out dates, special requests, payment information.</li>
            <li><strong>Technical Data:</strong> IP address, browser type, device information when you visit our website.</li>
            <li><strong>Marketing Preferences:</strong> Your opt‑in status for receiving promotional communications.</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>3. How We Use Your Information</h2>
        <p>We use your personal information for the following purposes:</p>
        <ul>
            <li>To process your room reservations and manage your stay.</li>
            <li>To administer your Grand Rewards membership account.</li>
            <li>To communicate with you about your booking or membership (e.g., confirmations, updates).</li>
            <li>To send you promotional offers and newsletters, only if you have given your consent.</li>
            <li>To improve our website, services, and customer experience.</li>
            <li>To comply with legal obligations (e.g., tax reporting, guest registration under Malaysian law).</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>4. Legal Basis for Processing</h2>
        <p>We process your personal information based on one or more of the following legal grounds:</p>
        <ul>
            <li><strong>Contractual necessity:</strong> To fulfil your booking or membership agreement.</li>
            <li><strong>Legitimate interests:</strong> To improve our services, prevent fraud, and conduct analytics.</li>
            <li><strong>Consent:</strong> For marketing communications – you may withdraw consent at any time.</li>
            <li><strong>Legal obligation:</strong> To comply with applicable laws and regulations.</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>5. Sharing Your Information</h2>
        <p>We do not sell your personal data. We may share your information with:</p>
        <ul>
            <li><strong>Service providers:</strong> Payment processors, IT support, marketing platforms – under strict confidentiality agreements.</li>
            <li><strong>Legal authorities:</strong> When required by law or to protect our rights and safety.</li>
            <li><strong>Business transfers:</strong> In the event of a merger, acquisition, or sale of assets, your data may be transferred to the new owner.</li>
        </ul>
    </div>

    <div class="privacy-section">
        <h2>6. Data Security</h2>
        <p>We implement appropriate technical and organisational measures to protect your personal data against unauthorised access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.</p>
    </div>

    <div class="privacy-section">
        <h2>7. Data Retention</h2>
        <p>We retain your personal information only as long as necessary for the purposes outlined in this Privacy Policy, or as required by law (e.g., 7 years for financial records). Booking data may be kept for up to 5 years after your stay.</p>
    </div>

    <div class="privacy-section">
        <h2>8. Your Rights</h2>
        <p>Under applicable data protection laws, you may have the right to:</p>
        <ul>
            <li>Access the personal information we hold about you.</li>
            <li>Request correction of inaccurate or incomplete data.</li>
            <li>Request deletion of your data (subject to legal obligations).</li>
            <li>Object to or restrict processing of your data.</li>
            <li>Withdraw consent for marketing communications at any time.</li>
        </ul>
        <p>To exercise these rights, please contact us at <a href="mailto:privacy@grandhotel.com" style="color: #C5A059;">privacy@grandhotel.com</a>.</p>
    </div>

    <div class="privacy-section">
        <h2>9. Cookies</h2>
        <p>Our website uses cookies to enhance user experience and analyse site traffic. You can manage your cookie preferences through your browser settings.</p>
    </div>

    <div class="privacy-section">
        <h2>10. Children’s Privacy</h2>
        <p>Our services are not directed to individuals under 18 years of age. We do not knowingly collect personal information from children. If you believe we have inadvertently collected such data, please contact us to delete it.</p>
    </div>

    <div class="privacy-section">
        <h2>11. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date. We encourage you to review this policy periodically.</p>
    </div>

    <div class="privacy-section">
        <h2>12. Contact Us</h2>
        <p>If you have any questions or concerns about this Privacy Policy or our data practices, please contact our Data Protection Officer:</p>
        <p>Email: <a href="mailto:privacy@grandhotel.com" style="color: #C5A059;">privacy@grandhotel.com</a><br>
        Postal Address: Grand Hotel Melaka, Kota Laksamana, 75200 Melaka, Malaysia.</p>
    </div>

    <div class="last-updated">
        &copy; <?php echo date('Y'); ?> Grand Hotel Melaka. All rights reserved.
    </div>
</div>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
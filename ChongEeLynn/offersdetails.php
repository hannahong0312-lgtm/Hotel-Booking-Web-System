<?php
include '../Shared/config.php';
include '../Shared/header.php';

// Get offer ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: offers.php');
    exit;
}

// Fetch offer
$sql = "SELECT * FROM hotel_offers WHERE id = $id";
$result = mysqli_query($conn, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    header('Location: offers.php');
    exit;
}

// Decode terms
$terms = json_decode($offer['terms'], true);

// Function to check if offer is expired
function isExpired($valid_to) {
    if (empty($valid_to)) return false;
    return strtotime($valid_to) < time();
}

$expired = isExpired($offer['valid_to']);

// Get similar offers (same category, different id)
$similar_sql = "SELECT * FROM hotel_offers WHERE category = '{$offer['category']}' AND id != $id LIMIT 3";
$similar_result = mysqli_query($conn, $similar_sql);
$similar = [];
while ($row = mysqli_fetch_assoc($similar_result)) {
    $similar[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($offer['title']); ?> | Offer Details</title>
    <link rel="stylesheet" href="css/offers.css">
</head>
<body>

<!-- Hero -->
<section class="detail-hero" style="background-image: linear-gradient(rgba(0, 0, 0, 0.35), rgba(0, 0, 0, 0.35)), url('images/<?php echo $offer['image']; ?>')">
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($offer['title']); ?></h1>
        <div class="badges">
            <span class="badge-cat"><?php echo ucfirst(str_replace('_', ' ', $offer['category'])); ?></span>
            <span class="badge-discount">Save <?php echo $offer['discount_percentage']; ?>%</span>
            <?php if ($offer['is_active'] == 0): ?>
                <span class="badge-inactive">Inactive</span>
            <?php elseif ($expired): ?>
                <span class="badge-expired">Expired</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Content -->
<section class="detail-section">
    <div class="container">
        <a href="offers.php" class="back-btn">← Back to Offers</a>
        
        <div class="detail-grid single-column">
            <!-- Description -->
            <div class="info-box">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($offer['description'])); ?></p>
            </div>
            
            <!-- Discount Info -->
            <div class="info-box">
                <h2>Discount Details</h2>
                <div class="discount-info-box">
                    <div class="discount-percent-large"><?php echo $offer['discount_percentage']; ?>% OFF</div>
                    <p class="discount-note">Get <?php echo $offer['discount_percentage']; ?>% discount on your booking when you use the redemption code below.</p>
                    
                    <?php if (!empty($offer['valid_to']) && !$expired): ?>
                        <div class="valid-date-box">
                            <span class="valid-label">Valid until:</span>
                            <span class="valid-date-value"><?php echo date('l, d F Y', strtotime($offer['valid_to'])); ?></span>
                        </div>
                    <?php elseif (!empty($offer['valid_to']) && $expired): ?>
                        <div class="valid-date-box expired">
                            <span class="valid-label">Expired on:</span>
                            <span class="valid-date-value"><?php echo date('l, d F Y', strtotime($offer['valid_to'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="code-info">
                    <p><strong>Redemption Code:</strong> <span class="code-value"><?php echo $offer['code']; ?></span></p>
                    <p class="code-note">Use this code at checkout to claim your <?php echo $offer['discount_percentage']; ?>% discount.</p>
                </div>
            </div>
            
            <!-- Terms & Conditions -->
            <div class="info-box">
                <h2>Terms & Conditions</h2>
                <?php if ($terms && count($terms) > 0): ?>
                    <ul class="terms-list">
                        <?php foreach ($terms as $term): ?>
                            <li>✓ <?php echo htmlspecialchars($term); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Standard terms apply. Please contact us for more information.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Similar Offers -->
        <?php if (!empty($similar)): ?>
        <div class="similar-section">
            <h2>You May Also Like</h2>
            <div class="similar-grid">
                <?php foreach ($similar as $item): ?>
                    <div class="similar-card" onclick="location.href='offersdetails.php?id=<?php echo $item['id']; ?>'">
                        <div class="similar-img" style="background-image: url('images/<?php echo $item['image']; ?>')">
                            <span>-<?php echo $item['discount_percentage']; ?>%</span>
                        </div>
                        <div class="similar-info">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <div class="similar-discount-badge">
                                <span class="similar-discount-percent"><?php echo $item['discount_percentage']; ?>% OFF</span>
                            </div>
                            <div class="similar-code">Code: <?php echo $item['code']; ?></div>
                            <?php if (!empty($item['valid_to']) && !isExpired($item['valid_to'])): ?>
                                <div class="similar-valid">Valid until: <?php echo date('d M Y', strtotime($item['valid_to'])); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
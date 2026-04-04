<?php
include '../Shared/config.php';
include '../Shared/header.php';

// Calculate discounted price
function getDiscountedPrice($price, $percent) {
    return $price - ($price * $percent / 100);
}

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

// Calculate prices
$discounted = getDiscountedPrice($offer['original_price'], $offer['discount_percentage']);
$terms = json_decode($offer['terms'], true);

// Get similar offers (same category, different id)
$similar_sql = "SELECT * FROM hotel_offers WHERE category = '{$offer['category']}' AND id != $id LIMIT 3";
$similar_result = mysqli_query($conn, $similar_sql);
$similar = [];
while ($row = mysqli_fetch_assoc($similar_result)) {
    $row['discounted_price'] = getDiscountedPrice($row['original_price'], $row['discount_percentage']);
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
<section class="detail-hero" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), url('<?php echo $offer['image']; ?>')">
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($offer['title']); ?></h1>
        <div class="badges">
            <span class="badge-cat"><?php echo ucfirst(str_replace('_', ' ', $offer['category'])); ?></span>
            <span class="badge-discount">Save <?php echo $offer['discount_percentage']; ?>%</span>
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
            
            <!-- Price Breakdown -->
            <div class="info-box">
                <h2>Price Breakdown</h2>
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Original Price</span>
                        <span>RM <?php echo number_format($offer['original_price'], 0); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Discount (<?php echo $offer['discount_percentage']; ?>%)</span>
                        <span class="discount-amt">- RM <?php echo number_format($offer['original_price'] - $discounted, 0); ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Your Price (after discount)</span>
                        <span class="final-price">RM <?php echo number_format($discounted, 0); ?></span>
                    </div>
                </div>
                <div class="code-info">
                    <p><strong>Redemption Code:</strong> <span class="code-value"><?php echo $offer['code']; ?></span></p>
                    <p class="code-note">Use this code at checkout to claim your discount.</p>
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
                        <div class="similar-img" style="background-image: url('<?php echo $item['image']; ?>')">
                            <span>-<?php echo $item['discount_percentage']; ?>%</span>
                        </div>
                        <div class="similar-info">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <div class="similar-price">
                                <span class="old">RM <?php echo number_format($item['original_price'], 0); ?></span>
                                <span class="new">RM <?php echo number_format($item['discounted_price'], 0); ?></span>
                            </div>
                            <div class="similar-code">Code: <?php echo $item['code']; ?></div>
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
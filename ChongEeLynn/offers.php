<?php
include '../Shared/config.php';
include '../Shared/header.php';

// Get filters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query - show ALL offers (including inactive)
$sql = "SELECT * FROM hotel_offers WHERE 1=1";
if (!empty($category) && $category != 'all') {
    $sql .= " AND category = '" . mysqli_real_escape_string($conn, $category) . "'";
}
if (!empty($search)) {
    $sql .= " AND (title LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' 
             OR code LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' 
             OR description LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}
$sql .= " ORDER BY id ASC";

$result = mysqli_query($conn, $sql);
$offers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['terms'] = json_decode($row['terms'], true);
    $offers[] = $row;
}

// Get unique categories
$categories = [];
foreach ($offers as $offer) {
    if (!in_array($offer['category'], $categories)) {
        $categories[] = $offer['category'];
    }
}

// Function to check if offer is expired
function isExpired($valid_to) {
    if (empty($valid_to)) return false;
    return strtotime($valid_to) < time();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offers</title>
    <link rel="stylesheet" href="css/offers.css">
</head>
<body>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Special Offers</h1>
        <p>Exclusive deals for your stay</p>
        <a href="#offers" class="btn-primary">View Offers</a>
    </div>
</section>

<!-- Filter Section -->
<section class="filter-section">
    <div class="filter-wrapper">
        <div class="filter-card">
            <form method="GET" class="filter-form">
                <input type="text" name="search" placeholder="Search by name or code" value="<?php echo htmlspecialchars($search); ?>">
                <select name="category">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $cat)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Filter</button>
                <?php if (!empty($category) || !empty($search)): ?>
                    <a href="offers.php" class="btn-reset">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>

<!-- Offers Grid -->
<section id="offers" class="offers-section">
    <div class="container">
        <h2>All Offers</h2>
        
        <?php if (empty($offers)): ?>
            <p class="no-results">No offers found.</p>
        <?php else: ?>
            <div class="offers-grid">
                <?php foreach ($offers as $offer): ?>
                    <?php $expired = isExpired($offer['valid_to']); ?>
                    <div class="offer-card">
                        <div class="offer-image" style="background-image: url('<?php echo $offer['image']; ?>')">
                            <span class="discount-badge">-<?php echo $offer['discount_percentage']; ?>%</span>
                            <?php if ($offer['is_active'] == 0): ?>
                                <span class="inactive-badge">Inactive</span>
                            <?php elseif ($expired): ?>
                                <span class="expired-badge">Expired</span>
                            <?php endif; ?>
                        </div>
                        <div class="offer-content">
                            <span class="category"><?php echo ucfirst(str_replace('_', ' ', $offer['category'])); ?></span>
                            <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($offer['description'], 0, 100)); ?>...</p>
                            <div class="discount-info">
                                <span class="discount-percent"><?php echo $offer['discount_percentage']; ?>% OFF</span>
                            </div>
                            <div class="code">Code: <strong><?php echo $offer['code']; ?></strong></div>
                            <?php if (!empty($offer['valid_to']) && !$expired): ?>
                                <div class="valid-date">Valid until: <?php echo date('d M Y', strtotime($offer['valid_to'])); ?></div>
                            <?php elseif (!empty($offer['valid_to']) && $expired): ?>
                                <div class="valid-date expired-date">Expired on: <?php echo date('d M Y', strtotime($offer['valid_to'])); ?></div>
                            <?php endif; ?>
                            <a href="offersdetails.php?id=<?php echo $offer['id']; ?>" class="btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Auto-submit filter when category changes
document.querySelector('select[name="category"]')?.addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
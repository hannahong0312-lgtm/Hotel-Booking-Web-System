<?php
// offers.php - Hotel Offers and Promotions Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../Shared/header.php';

$conn = new mysqli("localhost", "root", "", "hotel_booking");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM hotel_offers";
$result = $conn->query($sql);

$offers = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $row['terms'] = json_decode($row['terms'], true);
        $offers[] = $row;
    }
}

// Extract unique categories manually
$categories = [];
$catCount = 0;
foreach ($offers as $offer) {
    $exists = false;
    for ($i = 0; $i < $catCount; $i++) {
        if ($categories[$i] === $offer['category']) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        $categories[$catCount] = $offer['category'];
        $catCount++;
    }
}

// Filter featured and popular offers manually
$featured_offers = [];
$popular_offers = [];
foreach ($offers as $offer) {
    if ($offer['featured']) $featured_offers[] = $offer;
    if ($offer['popular']) $popular_offers[] = $offer;
}

// Helper function to render offer card
function renderOfferCard($offer, $type = 'regular') {
    $cat = htmlspecialchars($offer['category']);
    $catDisplay = ucfirst($cat);
    $title = htmlspecialchars($offer['title']);
    $subtitle = htmlspecialchars($offer['subtitle']);
    $desc = htmlspecialchars($offer['description']);
    $img = $offer['image'];
    $discount = $offer['discount'];
    $origPrice = $offer['original_price'];
    $discPrice = $offer['discounted_price'];
    $id = $offer['id'];
    $popular = $offer['popular'];
    
    if ($type === 'featured') {
        return <<<HTML
        <div class="featured-card" data-category="$cat">
            <div class="featured-badge">Featured</div>
            <div class="featured-image" style="background-image: url('$img')">
                <div class="discount-badge">$discount</div>
            </div>
            <div class="featured-content">
                <div class="category-tag $cat">$catDisplay</div>
                <h3>$title</h3>
                <p class="subtitle">$subtitle</p>
                <p class="description">$desc</p>
                <div class="price-info">
                    <span class="original-price">$origPrice</span>
                    <span class="discounted-price">$discPrice</span>
                    <span class="per-night">/package</span>
                </div>
                <button class="btn btn-primary btn-view-offer" data-offer-id="$id">
                    View Details <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
HTML;
    } else {
        $popularTag = $popular ? '<div class="popular-tag">Popular</div>' : '';
        return <<<HTML
        <div class="offer-card" data-category="$cat">
            <div class="offer-image" style="background-image: url('$img')">
                <div class="offer-discount">$discount</div>
                $popularTag
            </div>
            <div class="offer-content">
                <div class="category-badge $cat">$catDisplay</div>
                <h3>$title</h3>
                <p class="offer-subtitle">$subtitle</p>
                <p class="offer-description">$desc</p>
                <div class="offer-price">
                    <div class="price-details">
                        <span class="original">$origPrice</span>
                        <span class="current">$discPrice</span>
                    </div>
                    <button class="btn btn-secondary btn-details" data-offer-id="$id">Details</button>
                </div>
            </div>
        </div>
HTML;
    }
}
?>

<link rel="stylesheet" href="css/offers.css">

<main>
    <div class="offers-hero">
        <div class="hero-overlay"></div>
        <div class="hero-bg-image"></div>
        <div class="hero-container">
            <div class="hero-content">
                <h1>Special Offers & Promotions</h1>
                <p>Discover exclusive deals and packages designed to make your stay extraordinary</p>
                <div class="hero-buttons">
                    <a href="#featured-offers" class="btn btn-primary">View Featured Offers</a>
                    <a href="#all-offers" class="btn btn-outline">Browse All Deals</a>
                </div>
            </div>
            <div class="scroll-indicator">
                <span>Scroll to explore</span>
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="filters-section">
            <div class="filter-tabs">
                <button class="filter-tab active" data-category="all">All Offers</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-tab" data-category="<?= $cat ?>"><?= ucfirst($cat) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <section id="featured-offers" class="featured-section">
            <div class="section-header">
                <h2>Featured Offers</h2>
                <p>Our most popular packages and limited-time deals</p>
            </div>
            <div class="featured-grid">
                <?php foreach ($featured_offers as $offer) echo renderOfferCard($offer, 'featured'); ?>
            </div>
        </section>

        <section id="all-offers" class="offers-section">
            <div class="section-header">
                <h2>All Offers & Promotions</h2>
                <p>Find the perfect package for your next visit</p>
            </div>
            <div class="offers-grid" id="offersGrid">
                <?php foreach ($offers as $offer) echo renderOfferCard($offer, 'regular'); ?>
            </div>
        </section>

        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item"><div class="stat-number">10+</div><div class="stat-label">Active Offers</div></div>
                    <div class="stat-item"><div class="stat-number">35%</div><div class="stat-label">Maximum Savings</div></div>
                    <div class="stat-item"><div class="stat-number">24/7</div><div class="stat-label">Support Available</div></div>
                    <div class="stat-item"><div class="stat-number">100%</div><div class="stat-label">Satisfaction</div></div>
                </div>
            </div>
        </section>
    </div>
</main>

<div id="offerModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" id="closeModalBtn">&times;</span>
        <div id="offerModalContent"></div>
        <div class="modal-buttons">
            <button class="btn btn-secondary" id="cancelModalBtn">Close</button>
            <button class="btn btn-primary" id="bookOfferBtn">Book This Offer</button>
        </div>
    </div>
</div>

<div id="bookingModal" class="modal" style="display: none;">
    <div class="modal-content success">
        <span class="close" id="closeBookingModalBtn">&times;</span>
        <i class="fas fa-check-circle success-icon"></i>
        <h2>Offer Booked!</h2>
        <p>Thank you for choosing our special offer. We'll send you a confirmation email with all the details.</p>
        <button class="btn btn-primary" id="continueBookingBtn">Continue</button>
    </div>
</div>

<script>
const offersData = <?= json_encode($offers) ?>;
let currentOffer = null;

// Filter offers by category
function filterOffers(category) {
    const cards = document.querySelectorAll('.offer-card, .featured-card');
    let visible = 0;
    
    for (let i = 0; i < cards.length; i++) {
        const show = category === 'all' || cards[i].dataset.category === category;
        cards[i].style.display = show ? 'block' : 'none';
        if (show && cards[i].classList.contains('offer-card')) visible++;
    }
    
    let msg = document.querySelector('.no-results-message');
    if (visible === 0) {
        if (!msg) {
            msg = document.createElement('div');
            msg.className = 'no-results-message';
            msg.innerHTML = '<i class="fas fa-tag"></i><h3>No Offers Found</h3><p>Try a different category to see more offers.</p>';
            document.getElementById('offersGrid').appendChild(msg);
        }
    } else if (msg) {
        msg.remove();
    }
}

// Show offer modal
function showOfferDetails(id) {
    for (let i = 0; i < offersData.length; i++) {
        if (offersData[i].id === id) {
            currentOffer = offersData[i];
            break;
        }
    }
    if (!currentOffer) return;
    
    const o = currentOffer;
    let termsHtml = '';
    for (let i = 0; i < o.terms.length; i++) {
        termsHtml += '<li><i class="fas fa-check-circle"></i> ' + o.terms[i] + '</li>';
    }
    
    document.getElementById('offerModalContent').innerHTML = 
        '<div class="offer-detail">' +
            '<div class="offer-detail-image" style="background-image: url(\'' + o.image + '\')"></div>' +
            '<div class="offer-detail-content">' +
                '<div class="category-tag ' + o.category + '">' + o.category.charAt(0).toUpperCase() + o.category.slice(1) + '</div>' +
                '<h2>' + o.title + '</h2>' +
                '<p class="offer-subtitle">' + o.subtitle + '</p>' +
                '<p class="offer-description">' + o.long_description + '</p>' +
                '<div class="price-section">' +
                    '<div class="price-info"><span class="original-price">' + o.original_price + '</span><span class="discounted-price">' + o.discounted_price + '</span></div>' +
                    '<div class="discount-badge-large">' + o.discount + '</div>' +
                '</div>' +
                '<div class="validity"><i class="fas fa-calendar-alt"></i><span>Valid: ' + new Date(o.valid_from).toLocaleDateString() + ' - ' + new Date(o.valid_to).toLocaleDateString() + '</span></div>' +
                '<div class="terms"><h4><i class="fas fa-clipboard-list"></i> Terms & Conditions:</h4><ul>' + termsHtml + '</ul></div>' +
            '</div>' +
        '</div>';
    document.getElementById('offerModal').style.display = 'flex';
}

// Event delegation for filter tabs
document.querySelector('.filter-tabs').onclick = function(e) {
    if (e.target.classList.contains('filter-tab')) {
        const tabs = document.querySelectorAll('.filter-tab');
        for (let i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
        e.target.classList.add('active');
        filterOffers(e.target.dataset.category);
    }
};

// Event delegation for offer buttons
document.body.onclick = function(e) {
    const btn = e.target.closest('.btn-view-offer, .btn-details');
    if (btn) showOfferDetails(parseInt(btn.dataset.offerId));
    
    if (e.target.id === 'closeModalBtn' || e.target.id === 'cancelModalBtn') 
        document.getElementById('offerModal').style.display = 'none';
    if (e.target.id === 'closeBookingModalBtn' || e.target.id === 'continueBookingBtn') 
        document.getElementById('bookingModal').style.display = 'none';
    if (e.target.id === 'bookOfferBtn') {
        document.getElementById('offerModal').style.display = 'none';
        document.getElementById('bookingModal').style.display = 'flex';
        setTimeout(function() { document.getElementById('bookingModal').style.display = 'none'; }, 5000);
    }
    if (e.target.classList.contains('modal')) e.target.style.display = 'none';
};

// Smooth scroll for hero buttons
document.querySelector('.hero-buttons').onclick = function(e) {
    if (e.target.tagName === 'A') {
        e.preventDefault();
        const target = document.querySelector(e.target.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

// Scroll fade animation
function checkFade() {
    const els = document.querySelectorAll('.fade-in');
    const h = window.innerHeight;
    for (let i = 0; i < els.length; i++) {
        if (els[i].getBoundingClientRect().top < h - 100) els[i].classList.add('visible');
    }
}

const fadeEls = document.querySelectorAll('.featured-card, .offer-card, .stat-item');
for (let i = 0; i < fadeEls.length; i++) fadeEls[i].classList.add('fade-in');
window.onscroll = checkFade;
window.onload = checkFade;
</script>

<?php include '../Shared/footer.php'; ?>

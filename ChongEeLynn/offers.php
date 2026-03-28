<?php
// offers.php - Hotel Offers and Promotions Page
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include header
include '../Shared/header.php';

// Manual offers data array
$offers = [
    [
        'id' => 1,
        'title' => 'Early Bird Special',
        'category' => 'room',
        'subtitle' => 'Book 30 days in advance and save big',
        'description' => 'Plan ahead and enjoy exclusive savings on your next stay. Book your room at least 30 days before arrival and get up to 25% off on room rates.',
        'long_description' => 'Take advantage of our Early Bird Special by planning your getaway in advance. This offer is perfect for travelers who love to organize their trips ahead of time. Enjoy significant savings while securing your preferred room type.',
        'discount' => '25% OFF',
        'original_price' => '$299',
        'discounted_price' => '$224',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Minimum 30 days advance booking', 'Non-refundable', 'Valid for all room types', 'Subject to availability'],
        'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => true
    ],
    [
        'id' => 2,
        'title' => 'Romantic Getaway',
        'category' => 'room',
        'subtitle' => 'Perfect for couples celebrating love',
        'description' => 'Create unforgettable memories with your special someone. Includes champagne, chocolate-covered strawberries, and late check-out.',
        'long_description' => 'Surprise your loved one with a romantic escape. Our Romantic Getaway package includes everything you need for a perfect celebration of love. Enjoy champagne upon arrival, decadent chocolates, and a luxurious breakfast in bed.',
        'discount' => '15% OFF',
        'original_price' => '$399',
        'discounted_price' => '$339',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Minimum 2 nights stay', 'Complimentary champagne', 'Late check-out until 2 PM', 'Romantic turndown service'],
        'image' => 'https://images.unsplash.com/photo-1516434233442-0a69c3696d7e?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => true
    ],
    [
        'id' => 3,
        'title' => 'Family Fun Package',
        'category' => 'room',
        'subtitle' => 'Great savings for family vacations',
        'description' => 'Keep the whole family entertained with complimentary kids meals, game room access, and connecting rooms at special rates.',
        'long_description' => 'Make your family vacation truly memorable with our Family Fun Package. Kids eat free at our restaurants, enjoy unlimited access to our game room, and take advantage of our special family suite rates.',
        'discount' => '20% OFF',
        'original_price' => '$449',
        'discounted_price' => '$359',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Up to 2 kids under 12 eat free', 'Family suite upgrade available', 'Game room access included', 'Children\'s welcome gift'],
        'image' => 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => false
    ],
    [
        'id' => 4,
        'title' => 'Sunset Dinner Experience',
        'category' => 'dining',
        'subtitle' => 'Romantic dinner with ocean views',
        'description' => 'Indulge in a 5-course gourmet dinner while watching the sunset over the ocean. Includes wine pairing and personalized service.',
        'long_description' => 'Experience culinary excellence at our signature restaurant with our Sunset Dinner Experience. Enjoy a carefully curated 5-course meal prepared by our award-winning chef, perfectly paired with premium wines.',
        'discount' => '30% OFF',
        'original_price' => '$250',
        'discounted_price' => '$175',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Reservation required', 'Valid for dinner service only', 'Vegetarian options available', 'Includes wine pairing'],
        'image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => true
    ],
    [
        'id' => 5,
        'title' => 'Weekend Brunch Buffet',
        'category' => 'dining',
        'subtitle' => 'Endless variety for brunch lovers',
        'description' => 'Savor our legendary weekend brunch featuring live cooking stations, seafood bar, and unlimited mimosas.',
        'long_description' => 'Join us for the most talked-about brunch in the city! Our Weekend Brunch Buffet features an extensive selection of international cuisines, fresh seafood, carving stations, and decadent desserts.',
        'discount' => '20% OFF',
        'original_price' => '$85',
        'discounted_price' => '$68',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Saturdays and Sundays only', '11:00 AM - 3:00 PM', 'Children under 10 half price', 'Reservations recommended'],
        'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&h=500&fit=crop',
        'featured' => false,
        'popular' => true
    ],
    [
        'id' => 6,
        'title' => 'Chef\'s Table Experience',
        'category' => 'dining',
        'subtitle' => 'Exclusive culinary journey',
        'description' => 'Enjoy a private dining experience with our executive chef, featuring a personalized 8-course tasting menu.',
        'long_description' => 'Elevate your dining experience with our exclusive Chef\'s Table. Watch our culinary team in action while enjoying a customized tasting menu created just for you and your guests.',
        'discount' => '25% OFF',
        'original_price' => '$450',
        'discounted_price' => '$337',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Minimum 4 guests', '48-hour advance reservation required', 'Wine pairing available', 'Dietary restrictions accommodated'],
        'image' => 'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => false
    ],
    [
        'id' => 7,
        'title' => 'Spa & Stay Retreat',
        'category' => 'room',
        'subtitle' => 'Ultimate relaxation package',
        'description' => 'Combine luxury accommodation with spa treatments. Includes 60-minute massage, access to thermal suite, and healthy breakfast.',
        'long_description' => 'Rejuvenate your body and mind with our Spa & Stay Retreat. Enjoy a deluxe room, personalized spa treatments, and nourishing cuisine designed to restore balance and vitality.',
        'discount' => '35% OFF',
        'original_price' => '$599',
        'discounted_price' => '$389',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Includes one 60-minute spa treatment', 'Access to thermal facilities', 'Healthy breakfast included', 'Valid Sunday - Thursday'],
        'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => true
    ],
    [
        'id' => 8,
        'title' => 'Business Traveler Package',
        'category' => 'room',
        'subtitle' => 'Perfect for corporate travelers',
        'description' => 'Stay productive with complimentary high-speed WiFi, airport transfers, and access to our executive lounge.',
        'long_description' => 'Designed with the modern business traveler in mind, our package includes everything you need for a productive stay. Enjoy priority check-in, workspace amenities, and convenient transportation.',
        'discount' => '15% OFF',
        'original_price' => '$349',
        'discounted_price' => '$296',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Valid Monday - Thursday', 'Includes airport transfer', 'Executive lounge access', 'Late check-out available'],
        'image' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=800&h=500&fit=crop',
        'featured' => false,
        'popular' => false
    ],
    [
        'id' => 9,
        'title' => 'Sake & Sushi Night',
        'category' => 'dining',
        'subtitle' => 'Japanese culinary experience',
        'description' => 'Enjoy premium sake pairing with our chef\'s selection of fresh sushi and sashimi.',
        'long_description' => 'Transport your taste buds to Japan with our Sake & Sushi Night. Experience the finest cuts of fish, expertly prepared by our sushi masters, perfectly complemented by premium sake selections.',
        'discount' => '25% OFF',
        'original_price' => '$120',
        'discounted_price' => '$90',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Thursday nights only', 'Includes 3 sake tastings', 'Reservation required', 'Omakase experience available'],
        'image' => 'https://images.unsplash.com/photo-1553621042-f6e147245754?w=800&h=500&fit=crop',
        'featured' => false,
        'popular' => true
    ],
    [
        'id' => 10,
        'title' => 'Stay 3, Pay 2',
        'category' => 'room',
        'subtitle' => 'Extended stay savings',
        'description' => 'Book three nights and only pay for two! Perfect for longer vacations or extended business trips.',
        'long_description' => 'Make the most of your stay with our popular Stay 3, Pay 2 offer. Extend your vacation without extending your budget. Enjoy an extra day of luxury absolutely free.',
        'discount' => '33% OFF',
        'original_price' => '$299',
        'discounted_price' => '$199',
        'valid_from' => '2024-01-01',
        'valid_to' => '2024-12-31',
        'terms' => ['Minimum 3 nights stay', 'Free night applies to lowest rate night', 'Cannot combine with other offers', 'Blackout dates apply'],
        'image' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800&h=500&fit=crop',
        'featured' => true,
        'popular' => true
    ]
];

// Manual category extraction
$categories = [];
for($i = 0; $i < count($offers); $i++) {
    $found = false;
    for($j = 0; $j < count($categories); $j++) {
        if($categories[$j] == $offers[$i]['category']) {
            $found = true;
            break;
        }
    }
    if(!$found) {
        $categories[] = $offers[$i]['category'];
    }
}

// Get featured offers
$featured_offers = [];
for($i = 0; $i < count($offers); $i++) {
    if($offers[$i]['featured']) {
        $featured_offers[] = $offers[$i];
    }
}

// Get popular offers
$popular_offers = [];
for($i = 0; $i < count($offers); $i++) {
    if($offers[$i]['popular']) {
        $popular_offers[] = $offers[$i];
    }
}
?>

<!-- External CSS -->
<link rel="stylesheet" href="offers.css">

<main>
    <div class="container">
        <!-- Hero Section -->
        <div class="offers-hero">
            <div class="hero-content">
                <h1>Special Offers & Promotions</h1>
                <p>Discover exclusive deals and packages designed to make your stay extraordinary</p>
                <div class="hero-buttons">
                    <a href="#featured-offers" class="btn btn-primary">View Featured Offers</a>
                    <a href="#all-offers" class="btn btn-outline">Browse All Deals</a>
                </div>
            </div>
        </div>

        <!-- Category Filters -->
        <div class="filters-section">
            <div class="filter-tabs">
                <button class="filter-tab active" data-category="all">All Offers</button>
                <?php for($i = 0; $i < count($categories); $i++): 
                    $category_display = ucfirst($categories[$i]);
                ?>
                    <button class="filter-tab" data-category="<?php echo $categories[$i]; ?>">
                        <?php echo $category_display; ?>
                    </button>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Featured Offers Section -->
        <section id="featured-offers" class="featured-section">
            <div class="section-header">
                <h2>Featured Offers</h2>
                <p>Our most popular packages and limited-time deals</p>
            </div>
            <div class="featured-grid">
                <?php for($i = 0; $i < count($featured_offers); $i++): 
                    $offer = $featured_offers[$i];
                ?>
                    <div class="featured-card" data-category="<?php echo $offer['category']; ?>">
                        <div class="featured-badge">Featured</div>
                        <div class="featured-image" style="background-image: url('<?php echo $offer['image']; ?>')">
                            <div class="discount-badge"><?php echo $offer['discount']; ?></div>
                        </div>
                        <div class="featured-content">
                            <div class="category-tag <?php echo $offer['category']; ?>">
                                <?php echo ucfirst($offer['category']); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
                            <p class="subtitle"><?php echo htmlspecialchars($offer['subtitle']); ?></p>
                            <p class="description"><?php echo htmlspecialchars($offer['description']); ?></p>
                            <div class="price-info">
                                <span class="original-price"><?php echo $offer['original_price']; ?></span>
                                <span class="discounted-price"><?php echo $offer['discounted_price']; ?></span>
                                <span class="per-night">/package</span>
                            </div>
                            <button class="btn btn-primary btn-view-offer" data-offer-id="<?php echo $offer['id']; ?>">
                                View Details <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- All Offers Section -->
        <section id="all-offers" class="offers-section">
            <div class="section-header">
                <h2>All Offers & Promotions</h2>
                <p>Find the perfect package for your next visit</p>
            </div>
            <div class="offers-grid" id="offersGrid">
                <?php for($i = 0; $i < count($offers); $i++): 
                    $offer = $offers[$i];
                ?>
                    <div class="offer-card" data-category="<?php echo $offer['category']; ?>">
                        <div class="offer-image" style="background-image: url('<?php echo $offer['image']; ?>')">
                            <div class="offer-discount"><?php echo $offer['discount']; ?></div>
                            <?php if($offer['popular']): ?>
                                <div class="popular-tag">Popular</div>
                            <?php endif; ?>
                        </div>
                        <div class="offer-content">
                            <div class="category-badge <?php echo $offer['category']; ?>">
                                <?php echo ucfirst($offer['category']); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
                            <p class="offer-subtitle"><?php echo htmlspecialchars($offer['subtitle']); ?></p>
                            <p class="offer-description"><?php echo htmlspecialchars($offer['description']); ?></p>
                            <div class="offer-price">
                                <div class="price-details">
                                    <span class="original"><?php echo $offer['original_price']; ?></span>
                                    <span class="current"><?php echo $offer['discounted_price']; ?></span>
                                </div>
                                <button class="btn btn-secondary btn-details" data-offer-id="<?php echo $offer['id']; ?>">
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter-section">
            <div class="newsletter-content">
                <i class="fas fa-envelope-open-text"></i>
                <h3>Get Exclusive Offers</h3>
                <p>Subscribe to our newsletter and be the first to know about special promotions and deals</p>
                <form id="newsletterForm" class="newsletter-form">
                    <input type="email" id="newsletterEmail" placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
                <p class="newsletter-note">No spam, unsubscribe anytime.</p>
            </div>
        </section>
    </div>
</main>

<!-- Offer Details Modal -->
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

<!-- Booking Confirmation Modal -->
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
    // Store offers data
    const offersData = <?php echo json_encode($offers); ?>;
    
    // Get DOM elements
    const filterTabs = document.querySelectorAll('.filter-tab');
    const offerCards = document.querySelectorAll('.offer-card');
    const featuredCards = document.querySelectorAll('.featured-card');
    const offerModal = document.getElementById('offerModal');
    const bookingModal = document.getElementById('bookingModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const closeBookingModalBtn = document.getElementById('closeBookingModalBtn');
    const continueBookingBtn = document.getElementById('continueBookingBtn');
    const bookOfferBtn = document.getElementById('bookOfferBtn');
    const newsletterForm = document.getElementById('newsletterForm');
    
    let currentOffer = null;
    
    // Filter offers by category
    function filterOffers(category) {
        const allCards = document.querySelectorAll('.offer-card');
        const allFeaturedCards = document.querySelectorAll('.featured-card');
        
        for(let i = 0; i < allCards.length; i++) {
            const card = allCards[i];
            const cardCategory = card.getAttribute('data-category');
            
            if(category === 'all' || cardCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        }
        
        for(let i = 0; i < allFeaturedCards.length; i++) {
            const card = allFeaturedCards[i];
            const cardCategory = card.getAttribute('data-category');
            
            if(category === 'all' || cardCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        }
        
        // Show no results message if needed
        let visibleCount = 0;
        for(let i = 0; i < allCards.length; i++) {
            if(allCards[i].style.display !== 'none') {
                visibleCount++;
            }
        }
        
        let noResultsMsg = document.querySelector('.no-results-message');
        if(visibleCount === 0) {
            if(!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-message';
                noResultsMsg.innerHTML = `
                    <i class="fas fa-tag"></i>
                    <h3>No Offers Found</h3>
                    <p>Try a different category to see more offers.</p>
                `;
                const offersGrid = document.getElementById('offersGrid');
                offersGrid.appendChild(noResultsMsg);
            }
        } else if(noResultsMsg) {
            noResultsMsg.remove();
        }
    }
    
    // Show offer details modal
    function showOfferDetails(offerId) {
        for(let i = 0; i < offersData.length; i++) {
            if(offersData[i].id === offerId) {
                currentOffer = offersData[i];
                break;
            }
        }
        
        if(currentOffer) {
            const modalContent = document.getElementById('offerModalContent');
            modalContent.innerHTML = `
                <div class="offer-detail">
                    <div class="offer-detail-image" style="background-image: url('${currentOffer.image}')"></div>
                    <div class="offer-detail-content">
                        <div class="category-tag ${currentOffer.category}">${currentOffer.category.charAt(0).toUpperCase() + currentOffer.category.slice(1)}</div>
                        <h2>${currentOffer.title}</h2>
                        <p class="offer-subtitle">${currentOffer.subtitle}</p>
                        <p class="offer-description">${currentOffer.long_description}</p>
                        <div class="price-section">
                            <div class="price-info">
                                <span class="original-price">${currentOffer.original_price}</span>
                                <span class="discounted-price">${currentOffer.discounted_price}</span>
                            </div>
                            <div class="discount-badge-large">${currentOffer.discount} OFF</div>
                        </div>
                        <div class="validity">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Valid: ${new Date(currentOffer.valid_from).toLocaleDateString()} - ${new Date(currentOffer.valid_to).toLocaleDateString()}</span>
                        </div>
                        <div class="terms">
                            <h4><i class="fas fa-clipboard-list"></i> Terms & Conditions:</h4>
                            <ul>
                                ${currentOffer.terms.map(term => `<li><i class="fas fa-check-circle"></i> ${term}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            offerModal.style.display = 'flex';
        }
    }
    
    // Book offer
    function bookOffer() {
        offerModal.style.display = 'none';
        bookingModal.style.display = 'flex';
        
        setTimeout(function() {
            bookingModal.style.display = 'none';
        }, 5000);
    }
    
    // Close modals
    function closeOfferModal() {
        offerModal.style.display = 'none';
    }
    
    function closeBookingModal() {
        bookingModal.style.display = 'none';
    }
    
    // Newsletter subscription
    function handleNewsletterSubmit(event) {
        event.preventDefault();
        const emailInput = document.getElementById('newsletterEmail');
        const email = emailInput.value;
        
        if(email && email.includes('@') && email.includes('.')) {
            alert('Thank you for subscribing! You\'ll receive our latest offers soon.');
            emailInput.value = '';
        } else {
            alert('Please enter a valid email address.');
        }
    }
    
    // Event listeners for filter tabs
    for(let i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            for(let j = 0; j < filterTabs.length; j++) {
                filterTabs[j].classList.remove('active');
            }
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            filterOffers(category);
        });
    }
    
    // Event listeners for view details buttons
    const viewButtons = document.querySelectorAll('.btn-view-offer');
    for(let i = 0; i < viewButtons.length; i++) {
        viewButtons[i].addEventListener('click', function() {
            const offerId = parseInt(this.getAttribute('data-offer-id'));
            showOfferDetails(offerId);
        });
    }
    
    // Event listeners for details buttons
    const detailButtons = document.querySelectorAll('.btn-details');
    for(let i = 0; i < detailButtons.length; i++) {
        detailButtons[i].addEventListener('click', function() {
            const offerId = parseInt(this.getAttribute('data-offer-id'));
            showOfferDetails(offerId);
        });
    }
    
    // Modal close listeners
    if(closeModalBtn) closeModalBtn.addEventListener('click', closeOfferModal);
    if(cancelModalBtn) cancelModalBtn.addEventListener('click', closeOfferModal);
    if(bookOfferBtn) bookOfferBtn.addEventListener('click', bookOffer);
    if(closeBookingModalBtn) closeBookingModalBtn.addEventListener('click', closeBookingModal);
    if(continueBookingBtn) continueBookingBtn.addEventListener('click', closeBookingModal);
    
    // Newsletter form submit
    if(newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubmit);
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if(event.target === offerModal) {
            closeOfferModal();
        }
        if(event.target === bookingModal) {
            closeBookingModal();
        }
    });
    
    // Smooth scroll for anchor links
    const heroButtons = document.querySelectorAll('.hero-buttons a');
    for(let i = 0; i < heroButtons.length; i++) {
        heroButtons[i].addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if(targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
</script>

<?php include '../Shared/footer.php'; ?>
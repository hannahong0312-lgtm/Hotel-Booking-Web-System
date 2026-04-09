<?php
// Shared/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$user_display_name = '';
if ($is_logged_in && isset($_SESSION['user_name'])) {
    $user_display_name = explode(' ', trim($_SESSION['user_name']))[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Grand Hotel - Luxury accommodations for every traveler">
    <title>Grand Hotel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main CSS (global + header + footer) -->
    <link rel="stylesheet" href="../Shared/main.css">

    <!-- Page Specific CSS (if any) -->
    <?php if(isset($pageCSS)): ?>
      <link rel="stylesheet" href="<?php echo $pageCSS; ?>">
    <?php endif; ?>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <h1>Grand Hotel</h1>
                </div>
                <nav class="nav">
                    <ul>
                        <li><a href="../ChangJingEn/homepage.php">Home</a></li>
                        <li><a href="../ChongEeLynn/accommodation.php">Rooms & Suites</a></li>
                        <li><a href="../Hannah/dining.php">Dining</a></li>
                        <li><a href="../ChangJingEn/events.php">Weddings & Events</a></li>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Explore <i class="fa-solid fa-chevron-down" style="font-size: 0.75em; margin-left: 4px;"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="../ChangJingEn/facilities.php">Facilities</a></li>
                                <li><a href="../ChongEeLynn/experiences.php">Experiences</a></li>
                            </ul>
                        </li>
                        <li><a href="../ChongEeLynn/offers.php">Offers</a></li>
                        <li><a href="../Hannah/aboutus.php">About Us</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <?php if ($is_logged_in): ?>
                        <a href="profile.php" class="btn-login"><i class="fas fa-user-circle"></i> Hi, <?php echo htmlspecialchars($user_display_name); ?></a>
                        <a href="logout.php" class="btn-register">Logout</a>
                    <?php else: ?>
                        <a href="../ChangJingEn/login.php" class="btn-login">Book Now</a>
                        <a href="../ChangJingEn/register.php" class="btn-register">Join</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script>
        window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});
    </script>
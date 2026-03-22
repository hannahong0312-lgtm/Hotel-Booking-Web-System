<?php
// Shared/header.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include configuration file
require_once 'config.php';

$pageTitle = isset($pageTitle) ? $pageTitle : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Grand Hotel - Luxury accommodations for every traveler">
    <title><?php echo $pageTitle ? $pageTitle . ' - ' : ''; ?>Grand Hotel</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main CSS - Contains all header and footer styles -->
    <link rel="stylesheet" href="Shared/main.css">
    
    <!-- Page Specific CSS (if any) -->
    <?php if(isset($pageCSS)): ?>
        <link rel="stylesheet" href="<?php echo $pageCSS; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Header Section -->
    <header class="main-header">
        <div class="container">
            <h1>
                <i class="fas fa-hotel"></i> 
                Grand Hotel
            </h1>
            <p>Luxury accommodations for every traveler | Experience comfort like never before</p>
        </div>
    </header>

    <!-- Navigation Section -->
    <nav class="main-nav">
        <div class="container">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Home
                </a></li>
                <li><a href="accommodation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'accommodation.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bed"></i> Accommodations
                </a></li>
                <li><a href="bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i> My Bookings
                </a></li>
                <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Contact
                </a></li>
            </ul>
            
            <div class="auth-links">
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </div>
                        <span class="user-name">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </div>
                    <a href="profile.php" class="btn-profile">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- JavaScript for Mobile Menu -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                navLinks.classList.toggle('show');
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileMenuBtn && navLinks) {
                if (!mobileMenuBtn.contains(event.target) && !navLinks.contains(event.target)) {
                    navLinks.classList.remove('show');
                }
            }
        });
        
        // Add scroll effect to navigation
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('.main-nav');
            if (nav) {
                if (window.scrollY > 100) {
                    nav.style.background = 'var(--primary-dark)';
                    nav.style.boxShadow = 'var(--shadow-lg)';
                } else {
                    nav.style.background = 'var(--primary-light)';
                    nav.style.boxShadow = 'var(--shadow-md)';
                }
            }
        });
    </script>
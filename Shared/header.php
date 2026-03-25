<?php
// Shared/header.php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Include configuration file
require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="Grand Hotel - Luxury accommodations for every traveler">
    <title>Grand Hotel</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main CSS - Contains all header and footer styles -->
    <link rel="stylesheet" href="../Shared/main.css">
    
    <!-- Page Specific CSS (if any) -->
    <?php if(isset($pageCSS)): ?>
       <link rel="stylesheet" href="<?php echo $base_url . $pageCSS; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-inner">
                <div class="logo">
                    <h1>Grand Hotel</h1>
                </div>
                <nav class="nav">
                    <ul>
                        <li><a href="index.php" class="active">Home</a></li>
                        <li><a href="room_list.php">Rooms & Suites</a></li>
                        <li><a href="dining.php">Eat & Drink</a></li>
                        <li><a href="meetings.php">Meetings & Events</a></li>
                        <li><a href="offers.php">Offers</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <?php if ($is_logged_in): ?>
                        <a href="profile.php" class="btn-login">My Profile</a>
                        <a href="logout.php" class="btn-register">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">Sign In</a>
                        <a href="register.php" class="btn-register">Join</a>
                    <?php endif; ?>
                </div>
            </div>
        </div> 
    </header>
</body>
</html>
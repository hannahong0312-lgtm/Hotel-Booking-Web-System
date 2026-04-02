<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = array();

session_destroy();

// Redirect to homepage
header("Location: ../ChangJingEn/homepage.php");
exit();
?>
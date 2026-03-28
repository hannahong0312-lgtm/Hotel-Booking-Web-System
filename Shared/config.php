<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_booking');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

define('SITE_NAME', 'Grand Hotel');
define('SITE_URL', 'http://localhost/HOTEL-BOOKING-WEB-SYSTEM');

date_default_timezone_set('UTC');

error_reporting(E_ALL);
ini_set('display_errors', 1);

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

$is_logged_in = isLoggedIn();
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

function redirect($url) {
    header("Location: $url");
    exit();
}
?>
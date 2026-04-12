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

//Hannah part database setup
CREATE TABLE IF NOT EXISTS payment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    method VARCHAR(50) NOT NULL,
    card_no VARCHAR(4),
    card_expiry VARCHAR(5),
    transaction_id VARCHAR(50) UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATETIME,
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS book (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    room_name VARCHAR(100),
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT DEFAULT 1,
    subtotal DECIMAL(10,2),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    tokens_used INT DEFAULT 0,
    tokens_deduction_amount DECIMAL(10,2) DEFAULT 0,
    tokens_earned INT DEFAULT 0,
    sst_tax DECIMAL(10,2),
    foreigner_tax DECIMAL(10,2) DEFAULT 0,
    service_fee DECIMAL(10,2),
    grand_total DECIMAL(10,2),
    payment_method VARCHAR(50),
    nationality VARCHAR(20),
    special_requests TEXT,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    created_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE dining (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    guests INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    special_requests TEXT,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    code VARCHAR(20) UNIQUE NOT NULL,
    created_at DATETIME NOT NULL
);

<?php
-----------------------------
//Hannah Part
----------------------------
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
    points_used INT DEFAULT 0,
    points_deduction_amount DECIMAL(10,2) DEFAULT 0,
    points_earned INT DEFAULT 0,
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
//Gmail: grandhotelreservation67@gmail.com  Password: Grandhotel67


--------------------------------
//ChongEeLynn Part
--------------------------------
CREATE TABLE `hotel_offers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `category` enum('seasonal','holiday','corporate','spa','romance','family','last_minute') NOT NULL DEFAULT 'seasonal',
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(500) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL COMMENT 'Discount percentage (e.g., 25 for 25% off)',
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `terms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`terms`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hotel_offers` (`id`, `code`, `category`, `title`, `description`, `image`, `discount_percentage`, `valid_from`, `valid_to`, `is_active`, `terms`, `created_at`) VALUES
(1, 'SUMMER25', 'seasonal', 'Summer Escape Package', 'Beat the heat with 25% off on all room bookings + complimentary breakfast. Experience the ultimate summer getaway at our luxury hotel.', 'breakfast.jpg', 25.00, '2026-02-04', '2026-08-06', 1, '[\"Valid for new bookings only\", \"Cannot be combined with other offers\", \"Minimum 2 nights stay required\"]', '2026-04-02 16:00:39'),
(2, 'WELCOME15', 'holiday', 'Welcome to Paradise', 'First time guest exclusive! Get 15% off your first stay plus a complimentary room upgrade.', 'standard1.jpeg', 15.00, '2026-01-01', '2026-12-31', 1, '[\"Valid for first-time guests only\", \"Room upgrade subject to availability\", \"Valid ID required at check-in\"]', '2026-04-02 16:00:39'),
(3, 'SPA50', 'spa', 'Luxury Spa Retreat', '50% off when you book 2 nights or more. Rejuvenate your mind and body with our spa package.', 'spa.jpg', 50.00, '2026-03-20', '2026-12-31', 1, '[\"Spa treatments must be pre-booked\", \"Minimum 2 nights stay\", \"Taxes and service charges apply\"]', '2026-04-02 16:00:39'),
(4, 'ROMANCE30', 'romance', 'Romantic Getaway', '30% off + late checkout for couples. Perfect for anniversaries & honeymoons.', 'honeymoon.jpg', 30.00, '2026-02-14', '2026-12-14', 1, '[\"Valid for couples only\", \"Advance booking required\", \"Late checkout until 2 PM\"]', '2026-04-02 16:00:39'),
(5, 'FAMILY20', 'family', 'Family Fun Package', '20% off for family rooms. Free kids meals and access to kids club.', 'family.jpg', 20.00, '2026-04-01', '2026-09-10', 1, '[\"Kids meals apply to children under 12\", \"Family rooms subject to availability\", \"Maximum 2 children per room\"]', '2026-04-02 16:00:39'),
(6, 'LASTMIN35', 'last_minute', 'Last Minute Deal', '35% off when booking within 7 days of arrival. Perfect for spontaneous travelers!', 'pool.jpeg', 35.00, '2026-04-07', '2026-09-17', 1, '[\"Booking must be within 7 days of arrival\", \"Non-refundable\", \"Limited availability\"]', '2026-04-02 16:00:39'),
(7, 'CORPORATE25', 'corporate', 'Corporate Traveler', '25% off for business travelers. Includes premium Wi-Fi and business lounge access.', 'business.jpeg', 25.00, '2026-03-18', '2026-08-20', 1, '[\"Valid corporate ID required\", \"Minimum 2 nights stay\", \"Free Wi-Fi upgrade included\"]', '2026-04-02 16:00:39');

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('standard','deluxe','family','suite') NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_guests` int(11) DEFAULT 2,
  `bed_type` varchar(50) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `rooms_available` int(11) DEFAULT 5,
  `image` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rooms` (`id`, `name`, `category`, `description`, `price`, `max_guests`, `bed_type`, `size`, `rooms_available`, `image`, `is_active`, `created_at`) VALUES
(1, 'Standard Queen', 'standard', 'Comfortable room with modern amenities.', 280.00, 2, 'Queen Bed', 24, 8, 'standard1.jpeg', 1, '2026-04-02 14:13:04'),
(2, 'Standard Twin', 'standard', 'Twin beds with city view and free WiFi.', 260.00, 2, '2 Twin Beds', 24, 5, 'standard2.jpg', 1, '2026-04-02 14:13:04'),
(3, 'Standard Plus', 'standard', 'Spacious room with extra living area.', 320.00, 2, 'King Bed', 28, 3, 'standard3.jpg', 1, '2026-04-02 14:13:04'),
(4, 'Deluxe King', 'deluxe', 'Luxurious room with panoramic views.', 450.00, 2, 'King Bed', 32, 6, 'deluxe1.jpg', 1, '2026-04-02 14:13:04'),
(5, 'Deluxe Executive', 'deluxe', 'Executive floor with lounge access.', 520.00, 2, 'King Bed', 35, 4, 'deluxe2.png', 1, '2026-04-02 14:13:04'),
(6, 'Family Suite', 'family', 'Perfect for family vacation.', 580.00, 4, '1 King + 2 Twin', 45, 7, 'family1.jpg', 1, '2026-04-02 14:13:04'),
(7, 'Family Deluxe', 'family', 'Big room for big families', 650.00, 6, '2 Queens + 2 Twin', 50, 2, 'family2.jpg', 1, '2026-04-02 14:13:04'),
(8, 'Executive Suite', 'suite', 'Premium suite with kitchenette.', 850.00, 2, 'Emperor Bed', 65, 4, 'suite1.jpg', 1, '2026-04-02 14:13:04'),
(9, 'Presidential Suite', 'suite', 'Ultimate luxury with private terrace.', 1200.00, 4, '2 King Beds', 95, 1, 'suite2.jpg', 1, '2026-04-02 14:13:04');

------------------------------
//ChangJingEnn Part
------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phone` varchar(20) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `subscribe` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(255) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `birthday` date DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
//superadmin role = 1, normal admin role = 0 
//email:superadmin@grandhotel.com 
//username: superAdmin
//password: Admin123!


CREATE TABLE REVIEW (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    r_rating INT DEFAULT NULL CHECK (r_rating >= 1 AND r_rating <= 5),
    r_comment VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_room_id (room_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
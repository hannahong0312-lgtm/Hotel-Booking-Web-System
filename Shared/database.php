<?php
-----------------------------
//Hannah Part
----------------------------
CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `method` varchar(50) NOT NULL,
  `card_no` varchar(4) DEFAULT NULL,
  `card_expiry` varchar(5) DEFAULT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `points_used` int(11) DEFAULT 0,
  `points_deduction_amount` decimal(10,2) DEFAULT 0.00,
  `points_earned` int(11) DEFAULT 0,
  `sst_tax` decimal(10,2) DEFAULT NULL,
  `foreigner_tax` decimal(10,2) DEFAULT 0.00,
  `service_fee` decimal(10,2) DEFAULT NULL,
  `status` enum('confirmed','cancelled','completed') DEFAULT 'confirmed',
  `ic_no` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `book` (
  `id` int(11) NOT NULL,
  `booking_ref` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) DEFAULT 1,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `grand_total` decimal(10,2) DEFAULT NULL,
  `nationality` varchar(20) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('confirmed','cancelled','completed') DEFAULT 'confirmed',
  `checked_in_at` datetime DEFAULT NULL,
  `checked_out_at` datetime DEFAULT NULL,
  `late_checkout_penalty` decimal(10,2) DEFAULT 0.00,
  `review_skipped` tinyint(1) DEFAULT 0,
  `review_points_awarded` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `dummy_credit_cards` (
  `id` int(11) NOT NULL,
  `card_number` varchar(16) NOT NULL,
  `expiry_date` varchar(5) NOT NULL,
  `cvv` varchar(4) NOT NULL,
  `is_valid` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `dummy_credit_cards` (`id`, `card_number`, `expiry_date`, `cvv`, `is_valid`) VALUES
(1, '4111111111111111', '12/28', '123', 1),
(2, '4242424242424242', '06/29', '456', 1),
(3, '5555555555554444', '08/30', '789', 1),
(4, '4000000000000002', '01/25', '000', 0);


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

CREATE TABLE experiences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('main', 'favorite') DEFAULT 'main',
    category VARCHAR(100),
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    feature1 VARCHAR(255),
    feature2 VARCHAR(255),
    image_path VARCHAR(500),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

------------------------------
//ChangJingEnn Part
------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `country` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `subscribe` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(255) DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `birthday` date DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admins` (`id`, `email`, `username`, `password`, `role`, `status`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'superadmin@grandhotel.com', 'superadmin', '$2y$10$fCMrYYUPnogML0cG3KdYRehhq01AGYPnywXcBNTjvKIPmcwDSAOzW', 1, 'active', '2026-04-12 20:50:26', NULL, '2026-04-12 12:44:44', '2026-04-12 12:50:26');
//superadmin role = 1, normal admin role = 0 
//email:superadmin@grandhotel.com 
//username: Bella
//role: superadmin
//password: Admin123!

CREATE TABLE `birthday_discount_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `discount_percent` int(11) NOT NULL DEFAULT 10,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `hours` varchar(100) DEFAULT NULL,
  `feature1` varchar(255) DEFAULT NULL,
  `feature2` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `reverse_layout` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `facilities` (`id`, `category`, `description`, `image_path`, `hours`, `feature1`, `feature2`, `display_order`, `reverse_layout`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sky Fitness', 'Maintain your peak performance with panoramic city views, premium cardio machines, free weights, and personal training sessions available upon request.', 'images/sky-fitness.jpeg', '24 hours, daily', 'Equipment: State-of-the-art machines & free weights', 'Complimentary: Towels and water provided. Yoga studio access included.', 1, 0, 1, '2026-05-12 17:49:18', '2026-05-12 19:42:00'),
(2, 'Rooftop Infinity Pool', 'Take a dip above the city skyline. Our heated infinity pool offers breathtaking sunset views, sun loungers, and refreshing cocktails delivered to your side.', 'images/rooftop-infinity-pool.jpeg', '7:00 AM – 10:00 PM, daily', 'Poolside Bar: Signature cocktails & light bites', 'Guest Perks: Towel service included. Private cabanas available.', 2, 1, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(3, 'The Spa', 'Escape into pure relaxation with aromatherapy massages, organic facials, and traditional Malay therapies. Our expert therapists will tailor each experience.', 'images/spa.jpeg', '10:00 AM – 8:00 PM, daily', 'Signature Treatments: Aromatherapy • Hot stone • Malay massage', 'Complimentary Access: Steam room, sauna, and herbal tea lounge.', 3, 0, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(4, 'Grand Hotel Retail Shop', 'Take home the little touches that make Grand Hotel unique. Discover a curated collection of local handicrafts, resort apparel, signature spa amenities, and exclusive merchandise.', 'images/hotel-gift-shop.jpg', '10:00 AM – 7:00 PM, daily', 'Products: Souvenirs • Apparel • Spa products', NULL, 4, 1, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(5, 'Rangers Club', 'A safe, supervised space where little ones can play, create, and explore. Packed with games, arts & crafts, and movie screenings.', 'images/rangers-club.jpeg', '2:00 PM – 5:00 PM, daily', 'Age Group: Ages 4–12', 'Included: Healthy snacks. Parental supervision required.', 5, 0, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(6, 'EV Charging Station', 'As part of our commitment to eco-friendly hospitality, we offer EV charging facilities for in‑house guests. Two stations available in basement B1, accessible with your room key.', 'images/ev-charging.png', '24/7, self-service', 'Access: 24/7 with room key', 'Power: 22kW AC (Type 2 & CCS)', 6, 1, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18');


INSERT INTO `facilities` (`id`, `category`, `description`, `image_path`, `hours`, `feature1`, `feature2`, `display_order`, `reverse_layout`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sky Fitness', 'Maintain your peak performance with panoramic city views, premium cardio machines, free weights, and personal training sessions available upon request.', 'images/sky-fitness.jpeg', '24 hours, daily', 'Equipment: State-of-the-art machines & free weights', 'Complimentary: Towels and water provided. Yoga studio access included.', 1, 0, 1, '2026-05-12 17:49:18', '2026-05-12 19:42:00'),
(2, 'Rooftop Infinity Pool', 'Take a dip above the city skyline. Our heated infinity pool offers breathtaking sunset views, sun loungers, and refreshing cocktails delivered to your side.', 'images/rooftop-infinity-pool.jpeg', '7:00 AM – 10:00 PM, daily', 'Poolside Bar: Signature cocktails & light bites', 'Guest Perks: Towel service included. Private cabanas available.', 2, 1, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(3, 'The Spa', 'Escape into pure relaxation with aromatherapy massages, organic facials, and traditional Malay therapies. Our expert therapists will tailor each experience.', 'images/spa.jpeg', '10:00 AM – 8:00 PM, daily', 'Signature Treatments: Aromatherapy • Hot stone • Malay massage', 'Complimentary Access: Steam room, sauna, and herbal tea lounge.', 3, 0, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(4, 'Grand Hotel Retail Shop', 'Take home the little touches that make Grand Hotel unique. Discover a curated collection of local handicrafts, resort apparel, signature spa amenities, and exclusive merchandise.', 'images/hotel-gift-shop.jpg', '10:00 AM – 7:00 PM, daily', 'Products: Souvenirs • Apparel • Spa products', NULL, 4, 1, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(5, 'Rangers Club', 'A safe, supervised space where little ones can play, create, and explore. Packed with games, arts & crafts, and movie screenings.', 'images/rangers-club.jpeg', '2:00 PM – 5:00 PM, daily', 'Age Group: Ages 4–12', 'Included: Healthy snacks. Parental supervision required.', 5, 0, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18'),
(6, 'EV Charging Station', 'As part of our commitment to eco-friendly hospitality, we offer EV charging facilities for in‑house guests. Two stations available in basement B1, accessible with your room key.', 'images/ev-charging.png', '24/7, self-service', 'Access: 24/7 with room key', 'Power: 22kW AC (Type 2 & CCS)', 6, 1, 1, '2026-05-12 17:49:18', '2026-05-12 17:49:18');
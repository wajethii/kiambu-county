-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 06:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kiambu_services_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_offering_id` int(11) NOT NULL,
  `order_status` enum('Pending','Accepted','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `scheduled_datetime` datetime NOT NULL,
  `actual_start_time` datetime DEFAULT NULL,
  `actual_end_time` datetime DEFAULT NULL,
  `customer_address` text NOT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('Pending','Paid','Refunded') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `description`, `category`, `base_price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Access control systems', '\r\nYou can think of a prompt as a conversation starter with your AI-powered assistant. You might write several\r\nprompts as the conversation progresses. While the possibilities are virtually endless, you can put consistent\r\nbest practices to work today.', 'Office solutions', 30000.00, 1, '2025-05-23 06:13:02', '2025-05-23 06:13:02'),
(2, 'Cleaning Services', 'Detailed cleaner services', 'Home solutions', 1000.00, 1, '2025-05-23 06:30:34', '2025-05-23 06:30:34');

-- --------------------------------------------------------

--
-- Table structure for table `service_offerings`
--

CREATE TABLE `service_offerings` (
  `service_offering_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `availability_status` enum('Available','Busy','Offline') DEFAULT 'Available',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_providers`
--

CREATE TABLE `service_providers` (
  `provider_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `average_rating` decimal(2,1) DEFAULT 0.0,
  `is_verified` tinyint(1) DEFAULT 0,
  `location_data` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_providers`
--

INSERT INTO `service_providers` (`provider_id`, `user_id`, `business_name`, `bio`, `average_rating`, `is_verified`, `location_data`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'Kamau Plumbing Services', 'Experienced plumber serving Thika and surrounding areas in Kiambu.', 0.0, 0, 'Thika', 1, '2025-05-22 10:46:36', '2025-05-22 10:46:36'),
(2, 4, 'Structured Cabling', 'Experienced cable man, specializing in laying pipe.', 0.0, 0, 'Thika', 1, '2025-05-23 05:35:28', '2025-05-23 05:35:28'),
(3, 5, 'WiredWise', 'We deal with cctv installations, access control systems for homes and offices', 0.0, 0, 'Thika', 1, '2025-05-23 05:43:40', '2025-05-23 05:43:40'),
(4, 6, 'K Network', 'Experienced network technician', 0.0, 0, 'Thika', 1, '2025-05-23 10:16:25', '2025-05-23 10:16:25'),
(5, 8, 'Interior Designs', 'Non est non sapiente', 0.0, 0, 'Kikuyu', 1, '2025-05-24 04:28:07', '2025-05-24 04:28:07'),
(6, 9, 'Interior Designs', 'Non est non sapiente', 0.0, 0, 'Thika', 1, '2025-05-24 04:31:12', '2025-05-24 04:31:12'),
(7, 10, 'Structured Cabling', 'Experienced cable woman', 0.0, 0, 'Makongeni', 1, '2025-05-24 04:38:16', '2025-05-24 04:38:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('Customer','Service Provider','Admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `phone_number`, `password_hash`, `user_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Jane', 'Muthoni', 'jane.muthoni@example.com', '+254712345678', '$2a$10$abcdefghijklmnopqrstuvwxyzABCDEF0123456789.hashedpassword', 'Customer', 1, '2025-05-22 10:44:51', '2025-05-22 10:44:51'),
(2, 'Peter', 'Kamau', 'peter.kamau@example.com', '+254723456789', '$2a$10$anotherhashedpasswordXYZ0123456789abcdefghijklmnopqrstuv', 'Service Provider', 1, '2025-05-22 10:45:37', '2025-05-22 10:45:37'),
(3, 'Dennis', 'Maina', 'dennismaina@gmail.com', '+254712345600', '$2y$10$8NeOWmaq9ad5HrAoXfJwxeUYD.QDrsKjVn.v82HP47G8PILLZmrxS', 'Customer', 1, '2025-05-23 05:27:37', '2025-05-23 05:27:37'),
(4, 'Dennis', 'Kamau', 'kamau.d@gmail.com', '+254702345600', '$2y$10$UFAzos.IRjNRg4mLHdqgXu.koqdKqB3TRqjjdgXhBaQhrWPo10rEG', 'Service Provider', 1, '2025-05-23 05:35:28', '2025-05-23 05:35:28'),
(5, 'John', 'Koto', 'koto.j@gmail.com', '+254702348800', '$2y$10$6aD77sNxvQ3tLR/D4eMfqOYf2qDIb67nUu4R5XaJpMsI9docG9RLG', 'Service Provider', 1, '2025-05-23 05:43:40', '2025-05-23 05:43:40'),
(6, 'Brian', 'kamau', 'b.kamau@gmail.com', '+254711345600', '$2y$10$3NTZc63wGkHIHEwmrC91k.IY1X10XjGAE36BNoLKK8eDgws/V1Zca', 'Service Provider', 1, '2025-05-23 10:16:25', '2025-05-23 10:16:25'),
(7, 'Tallulah', 'Gross', 'wixe@mailinator.com', '+254755345600', '$2y$10$KVtjnEJDGvWZxvxAxkZ09.FmvaNyJkSltJiYlXdNlhO5XBfDjHKFK', 'Customer', 1, '2025-05-23 10:23:57', '2025-05-23 10:23:57'),
(8, 'Mary', 'Ball', 'vyxi@mailinator.com', '+254790345600', '$2y$10$Gj.gNympgRG2iOlVJFESz.nqIgHltivBa9Nw/2iyxaoORAcMYQeXW', 'Service Provider', 1, '2025-05-24 04:28:07', '2025-05-24 04:28:07'),
(9, 'Magdalene', 'Ball', 'ballm@mailinator.com', '+254790345111', '$2y$10$L1k0CWXA4LBCpnUuPA1c8OAwLJpi7xPyVN12d5D3DvgflgZmfPdA6', 'Service Provider', 1, '2025-05-24 04:31:12', '2025-05-24 04:31:12'),
(10, 'Wangari', 'Maina', 'wangari@gmail.com', '+254712300000', '$2y$10$QPRsNQfjSSBOG25GiYxtreBEU5OCyxGQWdtkkAiDOiClQgEGeF.xm', 'Service Provider', 1, '2025-05-24 04:38:16', '2025-05-24 04:38:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_service_offering_id` (`service_offering_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `fk_review_customer` (`customer_id`),
  ADD KEY `idx_provider_rating` (`provider_id`,`rating`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD UNIQUE KEY `service_name` (`service_name`),
  ADD KEY `idx_service_category_active` (`category`,`is_active`);

--
-- Indexes for table `service_offerings`
--
ALTER TABLE `service_offerings`
  ADD PRIMARY KEY (`service_offering_id`),
  ADD UNIQUE KEY `service_id` (`service_id`,`provider_id`),
  ADD KEY `fk_offering_provider` (`provider_id`),
  ADD KEY `idx_service_provider_pairing_active` (`service_id`,`provider_id`,`is_active`);

--
-- Indexes for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD PRIMARY KEY (`provider_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_provider_verified_active` (`is_verified`,`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_user_type_active` (`user_type`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_offerings`
--
ALTER TABLE `service_offerings`
  MODIFY `service_offering_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_providers`
--
ALTER TABLE `service_providers`
  MODIFY `provider_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_service_offering` FOREIGN KEY (`service_offering_id`) REFERENCES `service_offerings` (`service_offering_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_provider` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`provider_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_offerings`
--
ALTER TABLE `service_offerings`
  ADD CONSTRAINT `fk_offering_provider` FOREIGN KEY (`provider_id`) REFERENCES `service_providers` (`provider_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offering_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_providers`
--
ALTER TABLE `service_providers`
  ADD CONSTRAINT `fk_provider_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

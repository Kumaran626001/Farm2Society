-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 23, 2025 at 12:51 PM
-- Server version: 9.1.0
-- PHP Version: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farm2society_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `delivery_status`
--

DROP TABLE IF EXISTS `delivery_status`;
CREATE TABLE IF NOT EXISTS `delivery_status` (
  `delivery_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `status` varchar(50) NOT NULL,
  `updated_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`delivery_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `consumer_id` int NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('Pending','Packed','Delivered','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `consumer_id` (`consumer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `farmer_id` int NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  KEY `order_id` (`order_id`),
  KEY `farmer_id` (`farmer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `order_id`, `farmer_id`, `rating`, `created_at`) VALUES
(1, 2, 3, 5, '2025-12-23 13:00:00'),
(2, 5, 5, 4, '2025-12-23 13:05:00'),
(3, 8, 7, 5, '2025-12-23 13:10:00'),
(4, 11, 5, 3, '2025-12-23 13:15:00'),
(5, 11, 6, 4, '2025-12-23 13:15:00'),
(6, 14, 5, 5, '2025-12-23 13:20:00'),
(7, 14, 6, 2, '2025-12-23 13:20:00'),
(8, 17, 7, 4, '2025-12-23 13:25:00'),
(9, 20, 5, 5, '2025-12-23 13:30:00'),
(10, 2, 3, 4, '2025-12-23 13:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 3.00, 30.00),
(2, 1, 2, 4.00, 25.00),
(3, 2, 4, 5.00, 50.00),
(4, 2, 5, 3.00, 45.00),
(5, 3, 6, 2.00, 35.00),
(6, 3, 7, 4.00, 30.00),
(7, 4, 8, 3.00, 55.00),
(8, 4, 9, 4.00, 45.00),
(9, 5, 10, 2.00, 60.00),
(10, 5, 11, 3.00, 40.00),
(11, 6, 12, 2.00, 55.00),
(12, 6, 13, 5.00, 35.00),
(13, 7, 14, 4.00, 30.00),
(14, 7, 15, 3.00, 45.00),
(15, 8, 16, 2.00, 40.00),
(16, 8, 17, 3.00, 50.00),
(17, 9, 18, 4.00, 35.00),
(18, 9, 19, 3.00, 25.00),
(19, 10, 20, 5.00, 20.00),
(20, 10, 21, 6.00, 15.00),
(21, 11, 22, 4.00, 15.00),
(22, 11, 23, 3.00, 60.00),
(23, 12, 24, 2.00, 55.00),
(24, 12, 25, 3.00, 70.00),
(25, 13, 26, 2.00, 80.00),
(26, 13, 27, 3.00, 65.00),
(27, 14, 28, 1.00, 90.00),
(28, 14, 29, 4.00, 40.00),
(29, 15, 30, 5.00, 35.00),
(30, 15, 31, 3.00, 30.00),
(31, 16, 32, 2.00, 50.00),
(32, 16, 33, 3.00, 45.00),
(33, 17, 34, 4.00, 40.00),
(34, 17, 35, 3.00, 30.00),
(35, 18, 36, 5.00, 35.00),
(36, 18, 37, 3.00, 45.00),
(37, 19, 38, 2.00, 60.00),
(38, 19, 39, 3.00, 50.00),
(39, 20, 40, 1.00, 95.00),
(40, 20, 1, 5.00, 30.00),
(41, 21, 1, 2.00, 30.00),
(42, 21, 3, 2.00, 40.00),
(43, 21, 11, 2.00, 40.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `farmer_id` int NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `farmer_id` (`farmer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `farmer_id`, `product_name`, `price`, `quantity`, `created_at`) VALUES
(1, 2, 'Tomato', 30.00, 118.00, '2025-12-23 12:43:17'),
(2, 2, 'Potato', 25.00, 200.00, '2025-12-23 12:43:17'),
(3, 2, 'Onion', 40.00, 148.00, '2025-12-23 12:43:17'),
(4, 3, 'Carrot', 50.00, 90.00, '2025-12-23 12:43:17'),
(5, 3, 'Beetroot', 45.00, 70.00, '2025-12-23 12:43:17'),
(6, 3, 'Radish', 35.00, 60.00, '2025-12-23 12:43:17'),
(7, 4, 'Cabbage', 30.00, 80.00, '2025-12-23 12:43:17'),
(8, 4, 'Cauliflower', 55.00, 60.00, '2025-12-23 12:43:17'),
(9, 4, 'Brinjal', 45.00, 100.00, '2025-12-23 12:43:17'),
(10, 5, 'Capsicum', 60.00, 70.00, '2025-12-23 12:43:17'),
(11, 5, 'Green Beans', 40.00, 88.00, '2025-12-23 12:43:17'),
(12, 5, 'Peas', 55.00, 65.00, '2025-12-23 12:43:17'),
(13, 6, 'Pumpkin', 35.00, 110.00, '2025-12-23 12:43:17'),
(14, 6, 'Bottle Gourd', 30.00, 85.00, '2025-12-23 12:43:17'),
(15, 6, 'Bitter Gourd', 45.00, 75.00, '2025-12-23 12:43:17'),
(16, 7, 'Snake Gourd', 40.00, 60.00, '2025-12-23 12:43:17'),
(17, 7, 'Drumstick', 50.00, 55.00, '2025-12-23 12:43:17'),
(18, 7, 'Sweet Corn', 35.00, 95.00, '2025-12-23 12:43:17'),
(19, 2, 'Spinach', 25.00, 120.00, '2025-12-23 12:43:17'),
(20, 3, 'Fenugreek', 20.00, 100.00, '2025-12-23 12:43:17'),
(21, 4, 'Coriander', 15.00, 150.00, '2025-12-23 12:43:17'),
(22, 5, 'Mint', 15.00, 140.00, '2025-12-23 12:43:17'),
(23, 6, 'Garlic', 60.00, 90.00, '2025-12-23 12:43:17'),
(24, 7, 'Ginger', 55.00, 85.00, '2025-12-23 12:43:17'),
(25, 2, 'Chilli', 70.00, 75.00, '2025-12-23 12:43:17'),
(26, 3, 'Mushroom', 80.00, 60.00, '2025-12-23 12:43:17'),
(27, 4, 'Zucchini', 65.00, 50.00, '2025-12-23 12:43:17'),
(28, 5, 'Broccoli', 90.00, 45.00, '2025-12-23 12:43:17'),
(29, 6, 'Lettuce', 40.00, 85.00, '2025-12-23 12:43:17'),
(30, 7, 'Spring Onion', 35.00, 90.00, '2025-12-23 12:43:17'),
(31, 2, 'Turnip', 30.00, 70.00, '2025-12-23 12:43:17'),
(32, 3, 'Yam', 50.00, 65.00, '2025-12-23 12:43:17'),
(33, 4, 'Cluster Beans', 45.00, 80.00, '2025-12-23 12:43:17'),
(34, 5, 'Ridge Gourd', 40.00, 75.00, '2025-12-23 12:43:17'),
(35, 6, 'Ash Gourd', 30.00, 95.00, '2025-12-23 12:43:17'),
(36, 7, 'Raw Banana', 35.00, 85.00, '2025-12-23 12:43:17'),
(37, 2, 'Okra', 45.00, 90.00, '2025-12-23 12:43:17'),
(38, 3, 'Celery', 60.00, 55.00, '2025-12-23 12:43:17'),
(39, 4, 'Kohlrabi', 50.00, 50.00, '2025-12-23 12:43:17'),
(40, 5, 'Artichoke', 95.00, 40.00, '2025-12-23 12:43:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('farmer','consumer','admin') NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `location`, `is_verified`, `created_at`) VALUES
(1, 'Super Admin', 'admin@farm2society.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 1, '2025-12-23 12:38:22'),
(2, 'Ramesh Kumar', 'ramesh.kumar@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Salem', 1, '2025-12-23 12:41:29'),
(3, 'Suresh', 'suresh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Erode', 1, '2025-12-23 12:41:29'),
(4, 'Mahesh', 'mahesh@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Namakkal', 1, '2025-12-23 12:41:29'),
(5, 'Karthik', 'karthik@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Trichy', 1, '2025-12-23 12:41:29'),
(6, 'Velmurugan', 'velmurugan@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Madurai', 1, '2025-12-23 12:41:29'),
(7, 'Arun', 'arun.farmer@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Coimbatore', 1, '2025-12-23 12:41:29'),
(8, 'Bala', 'bala@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'Tiruppur', 1, '2025-12-23 12:41:29'),
(9, 'Anita', 'anita@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(10, 'Priya', 'priya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(11, 'Rahul', 'rahul@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(12, 'Divya', 'divya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(13, 'Arun', 'arun.c@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(14, 'Kavya', 'kavya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(15, 'Naveen', 'naveen@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(16, 'Meena', 'meena@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(17, 'Rohit', 'rohit@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(18, 'Pooja', 'pooja@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(19, 'Sanjay', 'sanjay@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(20, 'Keerthi', 'keerthi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29'),
(21, 'Vikram', 'vikram@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'consumer', 'Chennai', 0, '2025-12-23 12:41:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

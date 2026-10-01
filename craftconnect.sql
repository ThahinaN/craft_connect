-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 08:08 PM
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
-- Database: `craftconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_products`
--

CREATE TABLE `add_products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buyer_addresses`
--

CREATE TABLE `buyer_addresses` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyer_addresses`
--

INSERT INTO `buyer_addresses` (`id`, `buyer_id`, `full_name`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 1, 'thahina', 'thahina@gmail.com', '8589946755', 'darul salam', '2025-10-11 10:23:36'),
(2, 1, 'Thahina', 'thahina@gmail.com', '8589946755', 'dharulsalam', '2025-10-18 06:32:41'),
(3, 1, 'Thahina', 'thahina@gmail.com', '8589946755', 'dharulsalam', '2025-10-18 06:38:24'),
(4, 1, 'thahina', 'thahina@gmail.com', '8589946755', 'darul salam', '2025-10-18 06:42:21'),
(5, 1, 'thahina', 'thahina@gmail.com', '8589946755', 'darul salam', '2025-10-18 06:43:18'),
(6, 1, 'thahina', 'thahina@gmail.com', '8589946755', 'darul salam', '2025-10-18 06:44:19'),
(7, 13, 'thahina', 'thahina@gmail.com', '8589946755', 'thahina manzil', '2025-10-18 11:02:30'),
(8, 13, 'thahina', 'thahina@gmail.com', '8589946755', 'darulsaalam', '2025-10-18 11:25:18'),
(9, 13, 'Thahina', 'thahina@gmail.com', '8589946755', 'Darul salam', '2025-10-18 15:45:17'),
(10, 13, 'thahina', 'thahina@gmail.com', '8589946755', 'hi', '2025-10-18 16:33:58'),
(11, 13, 'thahina', 'thahina@gmail.com', '9876543210', 'vgjtucuci', '2025-10-18 17:44:42');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `order_id`, `message`, `created_at`) VALUES
(6, 'thahina', 'thahina@gmail.com', 'emergency', 7, 'make it fast okey', '2025-10-18 22:01:28'),
(7, 'Thahina', 'thahina@gmail.com', 'damage', 9, 'product had a damage', '2025-10-18 22:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `address_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) NOT NULL DEFAULT 'Online',
  `payment_ref` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `product_id`, `quantity`, `total_price`, `status`, `address_id`, `created_at`, `payment_method`, `payment_ref`, `payment_date`) VALUES
(6, 13, 3, 1, 350.00, 'Processing', 7, '2025-10-18 11:02:30', 'Online', 'PAY-191b7330-ac12-11f0-a727-28c5c847c8d4', '2025-10-18 16:33:37'),
(7, 13, 6, 2, 450.00, 'Processing', 8, '2025-10-18 11:25:18', 'Online', 'PAY-24e564b1-ac15-11f0-a727-28c5c847c8d4', '2025-10-18 16:55:26'),
(8, 13, 7, 3, 1100.00, 'Processing', 9, '2025-10-18 15:45:17', 'Online', 'PAY-75b38ab7-ac39-11f0-a727-28c5c847c8d4', '2025-10-18 21:15:23'),
(9, 13, 10, 1, 550.00, 'Processing', 10, '2025-10-18 16:33:58', 'Online', 'PAY-4176e87f-ac40-11f0-a727-28c5c847c8d4', '2025-10-18 22:04:02'),
(10, 13, 7, 3, 1100.00, 'Processing', 11, '2025-10-18 17:44:42', 'Online', 'PAY-24e96762-ac4a-11f0-a727-28c5c847c8d4', '2025-10-18 23:14:49');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `name`, `description`, `category`, `price`, `stock`, `image_url`, `created_at`) VALUES
(1, 1, 'Crochet Bunny', 'Sweet, soft, and ready for cuddles! Our crochet newborn gift boxes are here.\\r\\nEach set includes a lovely mix of a bear, bunny, rattle, and accessories.\\r\\nBring a little handmade joy into a little one\\\'s life today.', 'Crochet', 400.00, 10, 'home/img/toy1.jpg', '2025-10-06 02:06:44'),
(4, 1, 'Crochet Flower', 'Handmade Crochet flowers with multiple colors', 'Crochet', 200.00, 15, 'home/img/crochetflower.jpg', '2025-10-07 01:09:51'),
(6, 1, 'Pearl Neck chain', 'Handmade beautiful Pearl Chain', 'Accessories', 200.00, 5, 'home/img/access13.jpg', '2025-10-08 20:54:00'),
(7, 15, 'Tulip Candles Bouquet', 'a floral scent that captures the essence of spring blossoming, combining magnolia, peony, and freesia with base notes of powder and wood for a clean, graceful finish.', 'Home Decor', 350.00, 5, 'home/img/candle4.jpg', '2025-10-18 17:43:35'),
(10, 14, 'Handmade Bag', 'Beuaatifull bag', 'Bags', 500.00, 6, 'home/img/bag11.jpg', '2025-10-18 21:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `tutorials`
--

CREATE TABLE `tutorials` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `caption` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutorials`
--

INSERT INTO `tutorials` (`id`, `seller_id`, `title`, `video_url`, `description`, `created_at`, `caption`) VALUES
(1, 1, 'Resin Mini frame ', 'https://youtube.com/shorts/nK6LahTB25Y?si=URepp9JSuY8bksla', 'Handmade Resin mini frame making video', '2025-10-06 16:22:48', ''),
(3, 1, 'Crochet basics', 'https://youtube.com/shorts/_0vj7P94QyE?si=lkpIcEkRchDjG8Hg', 'Teaching the basic crochet Slip knot', '2025-10-06 17:20:51', ''),
(4, 15, 'Handmade neckless', 'https://youtube.com/shorts/7waukoB64aA?si=Ius2ilx5Mltchccu', 'Beautifull peral handmade neckless', '2025-10-18 12:43:05', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('buyer','seller','admin') NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','removed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `profile_pic`, `created_at`, `status`) VALUES
(1, 'Nisa', 'nisa@gmail.com', '$2y$10$cY1cS7eb4jH/.QpEtMgQJ.ZZNnKdI.JnPoyRzOtcWkf2bMLx3oRti', 'nisa@gmail.com', 'seller', 'home/img/3.jpeg', '2025-07-30 15:16:56', 'pending'),
(5, 'Nidhin Nc3', 'nidhin1426@gmail.com', '$2y$10$60HX3g7f5Mg3GBzY9xa8BuhRDzr2Egl4qnn347vDF8Ec4L/JFVmW2', '9567991716@1426', 'admin', 'home/img/1.jpeg', '2025-10-05 19:46:49', 'pending'),
(13, '', 'thahina@gmail.com', '$2y$10$LVSN0kSBC4vQnL6MEIaROOr94YdVXtdMmUERn7cza2wQefrx/fqoK', '', 'buyer', NULL, '2025-10-08 06:26:11', 'pending'),
(14, 'fathima', 'fathima@gmail.com', '$2y$10$N5uiD7zo7uOhsyZtxqUXuekjy9JAe1GkX1trYUV8/DqbKJQVpr4xe', '9876543210', 'seller', NULL, '2025-10-08 07:21:49', 'pending'),
(15, 'Asiya', 'asiya@gmail.com', '$2y$10$80CTEtmPd3ybMOVM7obscOBA71Y61vjrvI2rjCnWTfng4NxSkJeSO', '8589946755', 'seller', NULL, '2025-10-11 10:46:59', 'pending'),
(16, 'Farhan', 'farhan@gmail.com', '$2y$10$5QCTAXkKL9zQw.tIpyDxd.oiRTNZhTUZU7ruvVfw8tm32gZH2i3He', '8589946755', 'buyer', NULL, '2025-10-18 12:08:19', 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_products`
--
ALTER TABLE `add_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `buyer_addresses`
--
ALTER TABLE `buyer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `tutorials`
--
ALTER TABLE `tutorials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_products`
--
ALTER TABLE `add_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buyer_addresses`
--
ALTER TABLE `buyer_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tutorials`
--
ALTER TABLE `tutorials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `add_products`
--
ALTER TABLE `add_products`
  ADD CONSTRAINT `fk_seller_products` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `buyer_addresses`
--
ALTER TABLE `buyer_addresses`
  ADD CONSTRAINT `buyer_addresses_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tutorials`
--
ALTER TABLE `tutorials`
  ADD CONSTRAINT `fk_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

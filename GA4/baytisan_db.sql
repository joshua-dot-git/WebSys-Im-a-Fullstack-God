-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2025 at 11:17 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `baytisan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 3, '2025-09-23 20:46:47', '2025-09-23 20:46:47'),
(2, 4, '2025-09-23 21:13:39', '2025-09-23 21:13:39');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `added_at`) VALUES
(6, 2, 2, 1, '2025-09-24 00:01:54'),
(7, 1, 2, 1, '2025-09-24 11:20:13'),
(8, 1, 5, 1, '2025-09-24 11:20:13'),
(9, 1, 1, 1, '2025-09-24 11:20:13');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(4, 'Abaca Bags'),
(5, 'Abaca Baskets'),
(2, 'Abaca Placemats'),
(3, 'Abaca Slippers'),
(6, 'Pili Sweets'),
(1, 'Pots');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`) VALUES
(1, 'Bacacay'),
(2, 'Camalig'),
(3, 'Daraga'),
(4, 'Guinobatan'),
(5, 'Jovellar'),
(6, 'Legazpi'),
(7, 'Libon'),
(8, 'Ligao'),
(9, 'Malilipot'),
(10, 'Malinao'),
(11, 'Manito'),
(12, 'Oas'),
(19, 'Other'),
(13, 'Pio Duran'),
(14, 'Polangui'),
(15, 'Rapu-Rapu'),
(16, 'Santo Domingo'),
(17, 'Tabaco'),
(18, 'Tiwi');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `placed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `shipping_address`, `status`, `placed_at`) VALUES
(1, 3, 450.00, 'alnay', 'pending', '2025-09-23 13:09:21'),
(2, 3, 600.00, 'alnay', 'pending', '2025-09-23 13:53:29'),
(3, 4, 300.00, 'polangui', 'pending', '2025-09-23 21:13:49'),
(4, 4, 150.00, 'dasdasdasdas', 'pending', '2025-09-23 21:13:58');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `unit_price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 150.00, 2, 300.00),
(2, 1, 2, 150.00, 1, 150.00),
(3, 2, 1, 150.00, 2, 300.00),
(4, 2, 2, 150.00, 1, 150.00),
(5, 2, 8, 150.00, 1, 150.00),
(6, 3, 1, 150.00, 2, 300.00),
(7, 4, 2, 150.00, 1, 150.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`id`, `order_id`, `status`, `note`, `created_at`) VALUES
(1, 1, 'pending', 'Order placed', '2025-09-23 13:09:21'),
(2, 2, 'pending', 'Order placed', '2025-09-23 13:53:29'),
(3, 3, 'pending', 'Order placed', '2025-09-23 21:13:49'),
(4, 4, 'pending', 'Order placed', '2025-09-23 21:13:58');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `origin_location_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image_filename` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `description`, `category_id`, `origin_location_id`, `price`, `stock`, `image_filename`, `created_at`, `updated_at`) VALUES
(1, 'POT001', 'Pot 1', 'Handmade clay pot.', 1, 1, 150.00, 4, 'Untitled-1.png', '2025-09-23 02:12:42', '2025-09-23 21:13:49'),
(2, 'POT002', 'Pot 2', 'Handmade clay pot.', 1, 1, 150.00, 7, 'Untitled-2.png', '2025-09-23 02:12:42', '2025-09-23 21:13:58'),
(3, 'POT003', 'Pot 3', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-3.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(4, 'POT004', 'Pot 4', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-4.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(5, 'POT005', 'Pot 5', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-5.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(6, 'POT006', 'Pot 6', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-6.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(7, 'POT007', 'Pot 7', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-7.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(8, 'POT008', 'Pot 8', 'Handmade clay pot.', 1, 1, 150.00, 9, 'Untitled-8.png', '2025-09-23 02:12:42', '2025-09-23 13:53:29'),
(9, 'POT009', 'Pot 9', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-9.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(10, 'POT010', 'Pot 10', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-10.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(11, 'POT011', 'Pot 11', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-11.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(12, 'POT012', 'Pot 12', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-12.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(13, 'POT013', 'Pot 13', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-13.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(14, 'POT014', 'Pot 14', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-14.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(15, 'POT015', 'Pot 15', 'Handmade clay pot.', 1, 1, 150.00, 10, 'Untitled-15.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(16, 'MAT001', 'Abaca Placemat 1', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-16.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(17, 'MAT002', 'Abaca Placemat 2', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-17.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(18, 'MAT003', 'Abaca Placemat 3', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-18.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(19, 'MAT004', 'Abaca Placemat 4', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-19.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(20, 'MAT005', 'Abaca Placemat 5', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-20.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(21, 'MAT006', 'Abaca Placemat 6', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-21.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(22, 'MAT007', 'Abaca Placemat 7', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-22.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(23, 'MAT008', 'Abaca Placemat 8', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-23.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(24, 'MAT009', 'Abaca Placemat 9', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-24.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(25, 'MAT010', 'Abaca Placemat 10', 'Handwoven abaca placemat.', 2, 2, 100.00, 20, 'Untitled-25.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(26, 'SLIP001', 'Abaca Slipper 1', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-26.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(27, 'SLIP002', 'Abaca Slipper 2', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-27.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(28, 'SLIP003', 'Abaca Slipper 3', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-28.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(29, 'SLIP004', 'Abaca Slipper 4', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-29.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(30, 'SLIP005', 'Abaca Slipper 5', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-30.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(31, 'SLIP006', 'Abaca Slipper 6', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-31.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(32, 'SLIP007', 'Abaca Slipper 7', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-32.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(33, 'SLIP008', 'Abaca Slipper 8', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-33.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(34, 'SLIP009', 'Abaca Slipper 9', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-34.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(35, 'SLIP010', 'Abaca Slipper 10', 'Eco-friendly abaca slippers.', 3, 3, 180.00, 15, 'Untitled-35.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(36, 'BAG001', 'Abaca Bag 1', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-36.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(37, 'BAG002', 'Abaca Bag 2', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-37.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(38, 'BAG003', 'Abaca Bag 3', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-38.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(39, 'BAG004', 'Abaca Bag 4', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-39.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(40, 'BAG005', 'Abaca Bag 5', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-40.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(41, 'BAG006', 'Abaca Bag 6', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-41.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(42, 'BAG007', 'Abaca Bag 7', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-42.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(43, 'BAG008', 'Abaca Bag 8', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-43.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(44, 'BAG009', 'Abaca Bag 9', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-44.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(45, 'BAG010', 'Abaca Bag 10', 'Stylish abaca bag.', 4, 4, 350.00, 8, 'Untitled-45.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(46, 'BASK001', 'Abaca Basket 1', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-46.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(47, 'BASK002', 'Abaca Basket 2', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-47.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(48, 'BASK003', 'Abaca Basket 3', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-48.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(49, 'BASK004', 'Abaca Basket 4', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-49.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(50, 'BASK005', 'Abaca Basket 5', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-50.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(51, 'BASK006', 'Abaca Basket 6', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-51.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(52, 'BASK007', 'Abaca Basket 7', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-52.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(53, 'BASK008', 'Abaca Basket 8', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-53.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(54, 'BASK009', 'Abaca Basket 9', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-54.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(55, 'BASK010', 'Abaca Basket 10', 'Durable abaca basket.', 5, 5, 200.00, 15, 'Untitled-55.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(56, 'PILI001', 'Pili Sweet 1', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-56.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(57, 'PILI002', 'Pili Sweet 2', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-57.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(58, 'PILI003', 'Pili Sweet 3', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-58.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(59, 'PILI004', 'Pili Sweet 4', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-59.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(60, 'PILI005', 'Pili Sweet 5', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-60.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(61, 'PILI006', 'Pili Sweet 6', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-61.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(62, 'PILI007', 'Pili Sweet 7', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-62.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(63, 'PILI008', 'Pili Sweet 8', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-63.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(64, 'PILI009', 'Pili Sweet 9', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-64.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50'),
(65, 'PILI010', 'Pili Sweet 10', 'Sweet pili nut delicacy.', 6, 6, 120.00, 25, 'Untitled-65.png', '2025-09-23 02:12:42', '2025-09-23 02:20:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT '',
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','admin','seller') DEFAULT 'customer',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Admin', 'User', 'admin@baytisan.local', '$2y$10$uQ6rbVtTcf6gVj6V0uln1uTlaG1nA3i9KO33pVb2xF6gk8PUyZKx2', 'admin', '2025-09-22 22:46:15'),
(2, 'Sample', 'Customer', 'customer@baytisan.local', '$2y$10$K8Y8OZQvK8l1o8G2mQ2x1.pFZ1EXAMPLEHASHX2qz', 'customer', '2025-09-22 22:46:15'),
(3, 'Dagul', 'Bornok', 'dagulbornok@yahoo.com', '$2y$10$DXCg3PxOkwTxgVZDbmbiVOr0pdwE41Kp3eANsiQROzIPOOA541Nci', 'customer', '2025-09-22 22:47:45'),
(4, 'Hezekiah', 'Agsunod', 'hezekiahagsunod1@gmail.com', '$2y$10$srdfXafnL8NuPXH8oNohIukNgaee1zyge3yODVvTdCG2hhDW2QGrS', 'admin', '2025-09-23 01:07:59'),
(5, 'Joshua', 'Rebutiaco', 'jor@gmail.com', '$2y$10$/ib/oBflRxvD2nTU04E1K.0oUk7Gc/mysGHSejo85pQU1D6biQec.', 'customer', '2025-10-13 15:58:59'),
(6, 'Pancit', 'Kayo', 'dyan@gmail.com', '$2y$10$JxyQHivq1CbR/V.DHsao9ezaN/JQGlmWEw02wdb1/btBEwc5ftBAG', 'customer', '2025-10-13 16:08:47'),
(7, 'rtx4060ti', 'nvidia', 'mygpu@gmail.com', '$2y$10$eRsvwDKXqJ/t1rAwq2sz4OHok0mGSNqIlmsyV6YnPQf8vaRCSt6Vm', 'customer', '2025-10-13 16:26:44'),
(8, 'pleasework', 'pleasee', 'inthenameofthefather@gmail.com', '$2y$10$RtNAQeujuTHjMScJsT39Dug3aPB7kJD1W.n/e6.WF2nueEvFN8dO2', 'customer', '2025-10-13 16:32:34'),
(9, 'Joshua', 'Rebutiaco', 'verycoolguy@gmail.com', '$2y$10$dZTFA1LWifLwLobY.zhDzeHvZ7JMHqPw4nB9NQNBZeFQYnZRGeQEK', 'admin', '2025-10-13 16:38:28'),
(10, 'drinkwater', 'clear', 'waterisgood@gmail.com', '$2y$10$T2JjxTxdV7DgByemX6LlxeI13WugZ8NGRqb9lGpD8LgpbMoVdmnxy', 'admin', '2025-10-13 16:58:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_origin` (`origin_location_id`);

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
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`origin_location_id`) REFERENCES `locations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

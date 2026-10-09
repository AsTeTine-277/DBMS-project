-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2026 at 06:55 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hermart`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `service_name` varchar(150) DEFAULT NULL,
  `event_type` varchar(120) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `booking_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `vendor_id`, `service_id`, `service_name`, `event_type`, `booking_date`, `booking_time`, `location`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 3, 'Mehendi & Bridal Art', 'Wedding Mehendi', '2025-10-20', '16:00:00', 'Banani, Dhaka', 'Need mehendi for 12 hands', 'accepted', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(2, 5, 2, 2, 'Event Catering', 'Birthday Party', '2025-10-05', '13:00:00', 'Uttara, Dhaka', 'Vegetarian menu for 30 guests', 'pending', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(3, 2, 1, 1, 'Tailoring & Dress Making', 'Custom Tailoring', '2025-09-30', '11:00:00', 'Agrabad, Chattogram', 'Bridal blouse work', 'accepted', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(4, 1, 8, 6, 'Baby Sitting', 'Baby Sitting', '2025-10-12', '09:00:00', 'Banani, Dhaka', 'For 2-year-old, 4 hours', 'completed', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(5, 5, 5, 5, 'Home Cleaning', 'Home Cleaning', '2025-10-25', '10:00:00', 'Uttara, Dhaka', 'Full house deep cleaning', 'pending', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(6, 26, 4, 1, 'Tailoring & Dress Making', 'Wedding', '2026-11-07', '13:00:00', 'Shena Kunja,Dhaka', '50', 'pending', '2026-09-23 17:36:20', '2026-09-23 17:36:20');

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `shop_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `division` varchar(60) DEFAULT NULL,
  `district` varchar(80) DEFAULT NULL,
  `upazila` varchar(80) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `online_shop` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `owner_id`, `shop_name`, `description`, `category_id`, `location_id`, `division`, `district`, `upazila`, `address`, `phone`, `facebook`, `online_shop`, `profile_image`, `cover_image`, `rating`, `verified`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Rina Nakshi House', 'Handwoven Nakshi Kantha and traditional Sylheti crafts.', 5, NULL, 'Sylhet', 'Sylhet', 'Sadar', 'Zindabazar, Sylhet', '01800000001', 'https://facebook.com/rinanakshi', 'https://shop.rina-hermart.bd', NULL, NULL, 4.90, 1, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(2, 4, 'Borni Homemade Pickles', 'Authentic homemade mango, olive and chilli pickles.', 3, NULL, 'Rajshahi', 'Pabna', 'Pabna Sadar', 'Hemayetpur, Pabna', '01751304671', 'https://facebook.com/bornipickles', 'https://shop.borni-hermart.bd', NULL, NULL, 4.80, 1, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(3, 6, 'Dinajpur Traditional Foods', 'Pahari rice, dried fish and traditional sweets.', 3, NULL, 'Rangpur', 'Dinajpur', 'Dinajpur Sadar', 'Chirirbandar Road', '01700000004', 'https://facebook.com/dinajpurfoods', NULL, NULL, NULL, 4.40, 0, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(4, 7, 'Selina Boutique', 'Three-piece, saree and tailor-made clothing.', 2, NULL, 'Rangpur', 'Rangpur', 'Rangpur Sadar', 'Shapla Chottor, Rangpur', '01700000005', 'https://facebook.com/selinaboutique', 'https://selina-hermart.bd', NULL, NULL, 4.70, 1, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(5, 8, 'Mariam Pahari Crafts', 'Pahari bamboo crafts and indigenous home decor.', 7, NULL, 'Rajshahi', 'Rajshahi', 'Rajshahi Sadar', 'Talaimari, Rajshahi', '01700000006', 'https://facebook.com/mariamcrafts', NULL, NULL, NULL, 4.50, 0, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(6, 3, 'Sylhet Jewelry Studio', 'Handcrafted traditional silver jewelry.', 8, NULL, 'Sylhet', 'Moulvibazar', 'Sreemangal', 'Tea Garden Road', '01800000001', 'https://facebook.com/sylhetjewelry', NULL, NULL, NULL, 4.80, 1, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(7, 4, 'Pabna Nakshi & Crafts', 'Traditional Nakshi Kantha & embroidered bedsheets.', 5, NULL, 'Rajshahi', 'Pabna', 'Ishwardi', 'Ishwardi Bazar', '01751304671', 'https://facebook.com/pabnakantha', NULL, NULL, NULL, 4.60, 0, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(8, 8, 'Rajshahi Handicrafts', 'Handmade home decor and wooden crafts.', 4, NULL, 'Rajshahi', 'Rajshahi', 'Boalia', 'Seroil Road', '01700000006', 'https://facebook.com/rajhandicraft', 'https://raj-hermart.bd', NULL, NULL, 4.70, 1, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(9, 6, 'Dinajpur Pahari Honey', 'Pure Pahari honey from Sundarbans foothills.', 3, NULL, 'Rangpur', 'Dinajpur', 'Birampur', 'Birampur Bazar', '01700000004', 'https://facebook.com/paharihoney', NULL, NULL, NULL, 4.50, 0, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(10, 7, 'Rangpur Bedding & Decor', 'Bed covers, cushions and rural home decor.', 4, NULL, 'Rangpur', 'Rangpur', 'Pirganj', 'Pirganj Road', '01700000005', NULL, NULL, NULL, NULL, 4.60, 1, 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(11, 5, 'Rina Akter\'s Shop', NULL, NULL, NULL, NULL, NULL, NULL, 'Sadar, Sylhet', '01800000001', NULL, NULL, NULL, NULL, 0.00, 0, 'active', '2026-09-15 19:46:31', '2026-09-15 19:46:31'),
(12, 22, 'Seller Debug Shop', NULL, NULL, NULL, NULL, NULL, NULL, 'Test address', '01711111111', NULL, NULL, NULL, NULL, 0.00, 0, 'active', '2026-09-22 16:45:02', '2026-09-22 16:45:02'),
(13, 23, 'Seller Test 2\'s Shop', NULL, NULL, NULL, NULL, NULL, NULL, 'Dhaka', '01712345679', NULL, NULL, NULL, NULL, 0.00, 0, 'active', '2026-09-22 16:49:49', '2026-09-22 16:49:49'),
(14, 24, 'Seller Test 4\'s Shop', NULL, NULL, NULL, NULL, NULL, NULL, 'Dhaka', '01712345681', NULL, NULL, NULL, NULL, 0.00, 0, 'active', '2026-09-22 16:50:58', '2026-09-22 16:50:58'),
(15, 27, 'United\'s Shop', NULL, NULL, NULL, NULL, NULL, NULL, 'Dhaka', '0170006526', NULL, NULL, NULL, NULL, 0.00, 0, 'active', '2026-09-23 03:01:05', '2026-09-23 03:01:05'),
(16, 28, 'uiu1\'s Shop', NULL, NULL, NULL, NULL, NULL, NULL, 'Dhaka', '0167576567', NULL, NULL, NULL, NULL, 0.00, 0, 'active', '2026-09-24 03:47:37', '2026-09-24 03:47:37');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `created_at`, `updated_at`) VALUES
(1, 3, '2026-09-15 19:59:44', '2026-09-15 19:59:44'),
(2, 4, '2026-09-16 04:15:39', '2026-09-16 04:15:39'),
(3, 21, '2026-09-22 17:02:07', '2026-09-22 17:02:07'),
(4, 26, '2026-09-23 03:46:03', '2026-09-23 03:46:03'),
(5, 25, '2026-09-23 04:20:27', '2026-09-23 04:20:27'),
(6, 28, '2026-09-24 04:27:41', '2026-09-24 04:27:41');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `created_at`) VALUES
(1, 1, 2, 1, 2800.00, '2026-09-15 19:59:44'),
(2, 2, 7, 1, 1800.00, '2026-09-16 04:15:39'),
(3, 3, 1, 1, 4500.00, '2026-09-22 17:02:07'),
(6, 4, 21, 1, 0.00, '2026-09-24 03:36:36'),
(7, 4, 20, 1, 200.00, '2026-09-24 03:36:51');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `icon` varchar(60) DEFAULT 'category',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Handmade & Crafts', 'handmade', 'palette', 'Handcrafted artisan items'),
(2, 'Fashion & Clothing', 'fashion', 'checkroom', 'Clothing and fashion'),
(3, 'Food & Homemade', 'food', 'restaurant', 'Homemade foods and pickles'),
(4, 'Home & Decoration', 'home-decoration', 'chair', 'Home decor and furniture'),
(5, 'Nakshi Kantha', 'nakshi-kantha', 'bed', 'Traditional embroidered Kantha'),
(6, 'Rural & Traditional', 'rural-traditional', 'agriculture', 'Traditional rural crafts'),
(7, 'Pahari & Indigenous', 'pahari', 'landscape', 'Indigenous Pahari crafts'),
(8, 'Jewelry & Accessories', 'jewelry', 'diamond', 'Jewelry and accessories'),
(9, 'Customized Products', 'customized', 'auto_awesome', 'Custom-made products');

-- --------------------------------------------------------

--
-- Table structure for table `custom_orders`
--

CREATE TABLE `custom_orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `preferred_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `reference_image` varchar(255) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `quoted_price` decimal(10,2) DEFAULT NULL,
  `estimated_completion` date DEFAULT NULL,
  `status` enum('pending','reviewed','quoted','accepted','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `custom_orders`
--

INSERT INTO `custom_orders` (`id`, `customer_id`, `title`, `description`, `budget`, `quantity`, `preferred_date`, `location`, `reference_image`, `seller_id`, `quoted_price`, `estimated_completion`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Custom king-size Nakshi Kantha', 'Custom king-size Nakshi Kantha with floral border', 1.00, 6000, '2025-10-15', 'Banani, Dhaka', NULL, 1, NULL, NULL, 'accepted', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(2, 5, 'Custom three-piece with dupatta', 'Custom three-piece with matching dupatta for wedding', 2.00, 8000, '2025-10-30', 'Uttara, Dhaka', NULL, 4, NULL, NULL, 'quoted', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(3, 2, 'Bridal necklace and earring set', 'Bridal necklace and earring set in silver', 1.00, 12000, '2025-11-12', 'Agrabad, Chattogram', NULL, 6, NULL, NULL, 'pending', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(4, 1, 'Custom wooden name plate', 'Custom wooden name plate with family name carved', 1.00, 2500, '2025-10-20', 'Banani, Dhaka', NULL, 8, NULL, NULL, 'in_progress', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(5, 5, 'Custom mango pickle bulk order', 'Bulk homemade mango pickle for a wedding', 50.00, 15000, '2025-10-10', 'Uttara, Dhaka', NULL, 2, NULL, NULL, 'reviewed', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(6, 26, '0', 'Contact for details', 10000.00, 1, '2026-11-07', 'Badda,Dhaka', NULL, NULL, NULL, NULL, 'pending', '2026-09-23 17:38:43', '2026-09-23 17:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `custom_order_items`
--

CREATE TABLE `custom_order_items` (
  `id` int(11) NOT NULL,
  `custom_order_id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `district` varchar(80) DEFAULT NULL,
  `upazila` varchar(80) DEFAULT NULL,
  `tracking_number` varchar(60) DEFAULT NULL,
  `carrier` varchar(80) DEFAULT 'HerMart Logistics',
  `status` enum('pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `estimated_delivery` date DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `customer_id`, `address`, `district`, `upazila`, `tracking_number`, `carrier`, `status`, `estimated_delivery`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Banani, Dhaka', 'Dhaka', 'Banani', 'TRK-0001', 'HerMart Logistics', 'delivered', '2025-08-15', '2025-08-15 14:00:00', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(2, 2, 2, 'Agrabad, Chattogram', 'Chattogram', 'Agrabad', 'TRK-0002', 'HerMart Logistics', 'shipped', '2025-09-05', NULL, '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(3, 3, 5, 'Uttara, Dhaka', 'Dhaka', 'Uttara', 'TRK-0003', 'HerMart Logistics', 'processing', '2025-09-12', NULL, '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(4, 4, 1, 'Banani, Dhaka', 'Dhaka', 'Banani', 'TRK-0004', 'HerMart Logistics', 'pending', '2025-09-16', NULL, '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(5, 9, 25, 'Badda, Badda, Dhaka', 'Dhaka', 'Badda', 'TRK-7A832E-20260923', 'HerMart Logistics', 'shipped', '2026-09-28', NULL, '2026-09-23 04:20:46', '2026-09-23 04:21:36'),
(6, 10, 26, 'Dhaka, Badda, Dhaka', 'Dhaka', 'Badda', 'TRK-55249B-20260923', 'HerMart Logistics', 'confirmed', '2026-09-28', NULL, '2026-09-23 17:37:14', '2026-09-23 17:37:14'),
(7, 11, 28, 'Dhaka, Badda, Dhaka', 'Dhaka', 'Badda', 'TRK-EA47F9-20260924', 'HerMart Logistics', 'confirmed', '2026-09-29', NULL, '2026-09-24 04:49:23', '2026-09-24 04:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `type` enum('division','district','upazila') NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `type`, `parent_id`) VALUES
(1, 'Dhaka', 'division', NULL),
(2, 'Chattogram', 'division', NULL),
(3, 'Rajshahi', 'division', NULL),
(4, 'Khulna', 'division', NULL),
(5, 'Barishal', 'division', NULL),
(6, 'Sylhet', 'division', NULL),
(7, 'Rangpur', 'division', NULL),
(8, 'Mymensingh', 'division', NULL),
(9, 'Dhaka', 'district', 1),
(10, 'Gazipur', 'district', 1),
(11, 'Narayanganj', 'district', 1),
(12, 'Chattogram', 'district', 2),
(13, 'Cox\'s Bazar', 'district', 2),
(14, 'Rajshahi', 'district', 3),
(15, 'Pabna', 'district', 3),
(16, 'Natore', 'district', 3),
(17, 'Khulna', 'district', 4),
(18, 'Jessore', 'district', 4),
(19, 'Barishal', 'district', 5),
(20, 'Patuakhali', 'district', 5),
(21, 'Sylhet', 'district', 6),
(22, 'Moulvibazar', 'district', 6),
(23, 'Habiganj', 'district', 6),
(24, 'Rangpur', 'district', 7),
(25, 'Dinajpur', 'district', 7),
(26, 'Kurigram', 'district', 7),
(27, 'Mymensingh', 'district', 8),
(28, 'Jamalpur', 'district', 8),
(29, 'Dhaka', 'division', NULL),
(30, 'Chattogram', 'division', NULL),
(31, 'Rajshahi', 'division', NULL),
(32, 'Khulna', 'division', NULL),
(33, 'Barishal', 'division', NULL),
(34, 'Sylhet', 'division', NULL),
(35, 'Rangpur', 'division', NULL),
(36, 'Mymensingh', 'division', NULL),
(37, 'Dhaka', 'district', 1),
(38, 'Gazipur', 'district', 1),
(39, 'Narayanganj', 'district', 1),
(40, 'Chattogram', 'district', 2),
(41, 'Cox\'s Bazar', 'district', 2),
(42, 'Rajshahi', 'district', 3),
(43, 'Pabna', 'district', 3),
(44, 'Natore', 'district', 3),
(45, 'Khulna', 'district', 4),
(46, 'Jessore', 'district', 4),
(47, 'Barishal', 'district', 5),
(48, 'Patuakhali', 'district', 5),
(49, 'Sylhet', 'district', 6),
(50, 'Moulvibazar', 'district', 6),
(51, 'Habiganj', 'district', 6),
(52, 'Rangpur', 'district', 7),
(53, 'Dinajpur', 'district', 7),
(54, 'Kurigram', 'district', 7),
(55, 'Mymensingh', 'district', 8),
(56, 'Jamalpur', 'district', 8),
(57, 'Dhaka', 'division', NULL),
(58, 'Chattogram', 'division', NULL),
(59, 'Rajshahi', 'division', NULL),
(60, 'Khulna', 'division', NULL),
(61, 'Barishal', 'division', NULL),
(62, 'Sylhet', 'division', NULL),
(63, 'Rangpur', 'division', NULL),
(64, 'Mymensingh', 'division', NULL),
(65, 'Dhaka', 'district', 1),
(66, 'Gazipur', 'district', 1),
(67, 'Narayanganj', 'district', 1),
(68, 'Chattogram', 'district', 2),
(69, 'Cox\'s Bazar', 'district', 2),
(70, 'Rajshahi', 'district', 3),
(71, 'Pabna', 'district', 3),
(72, 'Natore', 'district', 3),
(73, 'Khulna', 'district', 4),
(74, 'Jessore', 'district', 4),
(75, 'Barishal', 'district', 5),
(76, 'Patuakhali', 'district', 5),
(77, 'Sylhet', 'district', 6),
(78, 'Moulvibazar', 'district', 6),
(79, 'Habiganj', 'district', 6),
(80, 'Rangpur', 'district', 7),
(81, 'Dinajpur', 'district', 7),
(82, 'Kurigram', 'district', 7),
(83, 'Mymensingh', 'district', 8),
(84, 'Jamalpur', 'district', 8);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'info',
  `title` varchar(180) NOT NULL,
  `body` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `body`, `link`, `is_read`, `created_at`) VALUES
(1, 1, 'order', 'Order #1 has been delivered', 'Your Nakshi Kantha order has reached you. Please leave a review.', 'pages/orders.php', 0, '2026-09-15 19:42:57'),
(2, 1, 'custom', 'Your custom order was accepted', 'Rina accepted your custom Nakshi Kantha request.', 'pages/customer-profile.php', 0, '2026-09-15 19:42:57'),
(3, 1, 'booking', 'Mehendi booking confirmed', 'Selina confirmed your mehendi booking for Oct 20.', 'pages/customer-profile.php', 0, '2026-09-15 19:42:57'),
(4, 2, 'order', 'Order #2 shipped', 'Your silver necklace is on the way.', 'pages/orders.php', 0, '2026-09-15 19:42:57'),
(5, 5, 'order', 'Order #3 is being processed', 'Selina is preparing your three-piece.', 'pages/orders.php', 0, '2026-09-15 19:42:57'),
(6, 25, 'order', 'Order HM-260923-174665 placed', 'Your order has been placed successfully.', 'customer/orders.php', 0, '2026-09-23 04:20:46'),
(7, 25, 'order', 'Order updated: #HM-260923-174665', 'Your order is now \"Shipped\".', 'customer/order-details.php?id=9', 0, '2026-09-23 04:21:36'),
(8, 7, 'booking', 'New booking request', 'You have a new booking for Wedding', 'seller/bookings.php', 0, '2026-09-23 17:36:20'),
(9, 26, 'order', 'Order HM-260923-5CFD76 placed', 'Your order has been placed successfully.', 'customer/orders.php', 0, '2026-09-23 17:37:14'),
(10, 28, 'order', 'Order HM-260924-64D390 placed', 'Your order has been placed successfully.', 'customer/orders.php', 0, '2026-09-24 04:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(40) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_address` varchar(500) DEFAULT NULL,
  `district` varchar(80) DEFAULT NULL,
  `upazila` varchar(80) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` enum('cod','bkash','nagad','card','bank') NOT NULL DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `total_amount`, `shipping_address`, `district`, `upazila`, `phone`, `notes`, `payment_method`, `payment_status`, `order_status`, `created_at`, `updated_at`) VALUES
(1, 'HM-000001', 1, 4850.00, 'Banani, Dhaka', 'Dhaka', 'Banani', '01700000001', NULL, 'cod', 'pending', 'delivered', '2025-08-12 04:32:00', '2026-09-15 19:40:25'),
(2, 'HM-000002', 2, 5400.00, 'Agrabad, Chattogram', 'Chattogram', 'Agrabad', '01700000002', NULL, 'bkash', 'paid', 'shipped', '2025-09-02 08:11:00', '2026-09-15 19:40:25'),
(3, 'HM-000003', 5, 1450.00, 'Uttara, Dhaka', 'Dhaka', 'Uttara', '01700000003', NULL, 'cod', 'pending', 'processing', '2025-09-09 03:25:00', '2026-09-15 19:40:25'),
(4, 'HM-000004', 1, 3500.00, 'Banani, Dhaka', 'Dhaka', 'Banani', '01700000001', NULL, 'cod', 'pending', 'pending', '2025-09-12 12:40:00', '2026-09-15 19:40:25'),
(9, 'HM-260923-174665', 25, 479.00, 'Badda, Badda, Dhaka', 'Dhaka', 'Badda', '0170000000', '', 'cod', 'pending', 'shipped', '2026-09-23 04:20:46', '2026-09-23 04:21:36'),
(10, 'HM-260923-5CFD76', 26, 929.00, 'Dhaka, Badda, Dhaka', 'Dhaka', 'Badda', '01722162416', '', 'cod', 'pending', 'pending', '2026-09-23 17:37:14', '2026-09-23 17:37:14'),
(11, 'HM-260924-64D390', 28, 430.00, 'Dhaka, Badda, Dhaka', 'Dhaka', 'Badda', '0167576567', '', 'cod', 'pending', 'pending', '2026-09-24 04:49:23', '2026-09-24 04:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `product_name` varchar(180) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `seller_id`, `product_name`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 2, 'Homemade Mango Pickle', 1, 350.00, 350.00),
(2, 2, 10, 6, 'Traditional Silver Necklace', 1, 5400.00, 5400.00),
(3, 3, 6, 4, 'Three-Piece Suit (Georgette)', 1, 1450.00, 1450.00),
(4, 4, 16, 10, 'Embroidered Bed Sheet (King)', 1, 3500.00, 3500.00),
(5, 9, 17, 15, '0', 1, 399.00, 399.00),
(6, 10, 18, 15, '0', 1, 849.00, 849.00),
(7, 11, 21, 15, '0', 1, 0.00, 0.00),
(8, 11, 3, 2, '0', 1, 350.00, 350.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('cod','bkash','nagad','card','bank') NOT NULL DEFAULT 'cod',
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `transaction_reference` varchar(120) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `transaction_reference`, `payment_status`, `paid_at`, `created_at`) VALUES
(1, 1, 'cod', 4850.00, 'COD-00000001', 'pending', NULL, '2026-09-15 19:42:57'),
(2, 2, 'bkash', 5400.00, 'BKH-00000002', 'paid', '2025-09-02 14:30:00', '2026-09-15 19:42:57'),
(3, 3, 'cod', 1450.00, 'COD-00000003', 'pending', NULL, '2026-09-15 19:42:57'),
(4, 4, 'cod', 3500.00, 'COD-00000004', 'pending', NULL, '2026-09-15 19:42:57'),
(5, 9, 'cod', 479.00, 'COD-000009-11A7', 'pending', NULL, '2026-09-23 04:20:46'),
(6, 10, 'cod', 929.00, 'COD-000010-5A2C', 'pending', NULL, '2026-09-23 17:37:14'),
(7, 11, 'cod', 430.00, 'COD-000011-1790', 'pending', NULL, '2026-09-24 04:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(80) DEFAULT NULL,
  `material` varchar(120) DEFAULT NULL,
  `color` varchar(60) DEFAULT NULL,
  `size` varchar(60) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','draft','out_of_stock','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `description`, `price`, `discount_price`, `stock`, `sku`, `material`, `color`, `size`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 'Traditional Nakshi Kantha Quilt', 'Hand-stitched multicolor Nakshi Kantha bedspread (queen size).', 4500.00, NULL, 8, 'NK-001', 'Cotton', 'Multicolor', 'Queen', 'nakshi1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(2, 1, 5, 'Sylheti Traditional Saree', 'Handwoven Sylheti cotton saree with traditional motif.', 3200.00, 2800.00, 5, 'SR-002', 'Cotton', 'White', 'Free', 'saree1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(3, 2, 3, 'Homemade Mango Pickle', 'Authentic homemade aam achar - 500g jar.', 350.00, NULL, 49, 'FD-001', 'Mango', NULL, '500g', 'pickle1.jpg', '', '2026-09-15 19:42:57', '2026-09-24 04:49:23'),
(4, 2, 3, 'Chilli Pickle (Naga)', 'Spicy naga chilli pickle - 400g jar.', 380.00, NULL, 40, 'FD-002', 'Chilli', NULL, '400g', 'pickle2.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(5, 3, 3, 'Pahari Dried Fish', 'Sun-dried Pahari fish - 250g pack.', 600.00, NULL, 20, 'FD-003', 'Fish', NULL, '250g', 'fish1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(6, 4, 2, 'Three-Piece Suit (Georgette)', 'Readymade georgette three-piece with embroidery.', 2800.00, NULL, 12, 'FC-001', 'Georgette', 'Pink', 'M', 'three1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(7, 4, 2, 'Cotton Saree (Hand Block Print)', 'Hand block-printed cotton saree - traditional design.', 1800.00, NULL, 25, 'FC-002', 'Cotton', 'Blue', 'Free', 'saree2.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(8, 5, 7, 'Bamboo Pen Stand Set', 'Handmade bamboo pen stand set - 3 pieces.', 450.00, NULL, 30, 'HC-001', 'Bamboo', 'Natural', NULL, 'bamboo1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(9, 5, 7, 'Pahari Wall Hanging', 'Traditional Pahari wall hanging made of bamboo and cane.', 1200.00, NULL, 15, 'HC-002', 'Bamboo', 'Brown', NULL, 'wall1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(10, 6, 8, 'Traditional Silver Necklace', 'Handcrafted tribal-style silver necklace.', 5400.00, NULL, 4, 'JW-001', 'Silver', 'Silver', NULL, 'jewel1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(11, 6, 8, 'Silver Earrings (Pair)', 'Handcrafted oxidized silver earrings.', 950.00, NULL, 18, 'JW-002', 'Silver', 'Silver', NULL, 'jewel2.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(12, 7, 5, 'Embroidered Cushion Cover', 'Hand-embroidered cushion cover - set of 2.', 850.00, NULL, 35, 'NK-003', 'Cotton', 'Red', '16x16', 'cush1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(13, 8, 4, 'Wooden Wall Clock', 'Handcrafted wooden wall clock with rural motifs.', 1800.00, NULL, 9, 'HD-001', 'Wood', 'Brown', NULL, 'clock1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(14, 8, 4, 'Hand-painted Wooden Vase', 'Decorative hand-painted wooden vase.', 1300.00, NULL, 14, 'HD-002', 'Wood', 'Multicolor', NULL, 'vase1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(15, 9, 3, 'Pure Pahari Honey (500g)', 'Raw, unprocessed honey from Pahari region.', 750.00, NULL, 22, 'FD-004', 'Honey', NULL, '500g', 'honey1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(16, 10, 4, 'Embroidered Bed Sheet (King)', 'Hand-embroidered bedsheet with pillow covers.', 3500.00, NULL, 7, 'HD-003', 'Cotton', 'White', 'King', 'bed1.jpg', 'active', '2026-09-15 19:42:57', '2026-09-15 19:42:57'),
(17, 15, 3, 'Organic Food', 'Homemade healthy organic food', 499.00, 399.00, 99, 'NK-101', 'Degradible', 'Multicolor', 'N/A', NULL, '', '2026-09-23 03:56:05', '2026-09-23 04:20:46'),
(18, 15, 2, 'Dress & Saree', 'N/A', 999.00, 849.00, 49, 'FS-023', 'Cotton', 'Multicolor', 'M,L,XL', NULL, '', '2026-09-23 03:58:31', '2026-09-23 17:37:14'),
(19, 15, 4, 'Poster', 'Wall poster for room', 200.00, NULL, 100, 'SK-001', 'PVC', 'Multicolor', '8x4', 'uploads/products/p_65ac66dbb5ab.jpg', 'active', '2026-09-23 04:00:55', '2026-09-23 04:00:55'),
(20, 15, NULL, 'Wall Poster', '', 200.00, NULL, 1, NULL, NULL, NULL, NULL, 'uploads/products/p_59f9c1432cef.jpg', 'active', '2026-09-23 04:01:36', '2026-09-23 04:01:36'),
(21, 15, 9, 'Sample', '', 0.00, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'active', '2026-09-23 04:19:36', '2026-09-24 04:49:23'),
(22, 16, 3, 'Traditional food', 'N/A', 100.00, NULL, 100, 'FD-01', 'N/A', 'Multicolor', 'N/A', 'uploads/products/p_d0584bddb025.jpg', 'active', '2026-09-24 03:48:57', '2026-09-24 03:48:57');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `sort_order`) VALUES
(1, 19, 'uploads/products/p_65ac66dbb5ab.jpg', 1, 0),
(2, 20, 'uploads/products/p_59f9c1432cef.jpg', 1, 0),
(3, 22, 'uploads/products/p_d0584bddb025.jpg', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `review_text` text DEFAULT NULL,
  `status` enum('active','hidden') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `customer_id`, `product_id`, `seller_id`, `order_id`, `rating`, `review_text`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 1, 5, 'Absolutely beautiful Nakshi Kantha. Stitching is flawless!', 'active', '2026-09-15 19:42:57'),
(2, 2, 3, 2, NULL, 5, 'Best homemade pickle I have ever tasted.', 'active', '2026-09-15 19:42:57'),
(3, 5, 6, 4, 3, 4, 'Loved the fabric and the fitting. Will order again.', 'active', '2026-09-15 19:42:57'),
(4, 1, 13, 8, NULL, 5, 'Wooden clock is a real showpiece. Great craftsmanship.', 'active', '2026-09-15 19:42:57'),
(5, 2, 10, 6, 2, 5, 'Silver necklace is gorgeous. Delivery was quick.', 'active', '2026-09-15 19:42:57'),
(6, 1, 4, 2, NULL, 5, 'The Naga chilli pickle is so flavourful — perfect with rice.', 'active', '2026-09-15 19:42:57'),
(7, 5, 11, 6, NULL, 4, 'Beautiful earrings, exactly as shown in the picture.', 'active', '2026-09-15 19:42:57');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(80) DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `category`, `base_price`, `description`, `status`, `created_at`) VALUES
(1, 'Tailoring & Dress Making', 'Fashion', 1500.00, 'Custom tailoring for women and children.', 'active', '2026-09-15 19:42:57'),
(2, 'Event Catering', 'Food', 12000.00, 'Home-style catering for small events and parties.', 'active', '2026-09-15 19:42:57'),
(3, 'Mehendi & Bridal Art', 'Beauty', 3000.00, 'Traditional mehendi, bridal and party makeup.', 'active', '2026-09-15 19:42:57'),
(4, 'Home Tutoring', 'Education', 1500.00, 'Female home tutors for primary and secondary students.', 'active', '2026-09-15 19:42:57'),
(5, 'Home Cleaning', 'Household', 1000.00, 'Trained women home cleaning staff.', 'active', '2026-09-15 19:42:57'),
(6, 'Baby Sitting', 'Care', 1200.00, 'Experienced baby sitters and elderly care.', 'active', '2026-09-15 19:42:57'),
(7, 'Handmade Jewelry Workshop', 'Artisan', 2000.00, 'Bespoke jewelry creation workshops.', 'active', '2026-09-15 19:42:57'),
(8, 'Pottery & Clay Art', 'Artisan', 1800.00, 'Traditional pottery classes and products.', 'active', '2026-09-15 19:42:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','seller','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `address` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `address`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'adminphp@gmail.com', '0188948951', '$2y$10$9O/ckm5iG7BkB.Ot5ZM8des0nCjf7tLhbAcfUFYcml3DDnyLGT82O', 'customer', 'active', 'Dhaka', NULL, '2026-09-15 19:27:10', '2026-09-15 19:27:10'),
(2, 'Customer uiu', 'customeruiu@gmail.com', '01752689751', '$2y$10$emndLKsTVwbzbgvPhs7acOdLhIWaytyFMZvxEHkttp7P8O6oRP7By', 'customer', 'active', 'Dhaka', NULL, '2026-09-15 19:29:00', '2026-09-15 19:29:00'),
(3, 'Ayesha Rahman', 'customer@hermart.com', '01700000001', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'customer', 'active', 'Banani, Dhaka', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(4, 'Sumaiya Hossain', 'sumaiya@hermart.com', '01700000002', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'customer', 'active', 'Agrabad, Chattogram', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(5, 'Rina Akter', 'seller@hermart.com', '01800000001', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'seller', 'active', 'Sadar, Sylhet', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(6, 'Borni Akter', 'borni@gmail.com', '01751304671', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'seller', 'active', 'Pabna Sadar', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(7, 'Fatiha Khatun', 'fatiha@hermart.com', '01700000003', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'customer', 'active', 'Uttara, Dhaka', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(8, 'Nazma Begum', 'nazma@hermart.com', '01700000004', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'seller', 'active', 'Dinajpur Sadar', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(9, 'Selina Akhter', 'selina@hermart.com', '01700000005', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'seller', 'active', 'Rangpur Sadar', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(10, 'Mariam Hossain', 'mariam@hermart.com', '01700000006', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'seller', 'active', 'Rajshahi Sadar', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(11, 'Admin HerMart', 'admin@hermart.com', '01900000000', '$2y$10$cL3Y4mTIFuz7OMIPPfGB3OwEb9Itg.Wl9JyvObYO1xCAwvGJUKwZW', 'admin', 'active', 'Banani, Dhaka', NULL, '2026-09-15 19:40:25', '2026-09-15 19:42:57'),
(21, 'Rafi', 'rmostafa2310234@bscse.uiu.ac.bd', '01316077934', '$2y$10$Z9F5qbkNQ6Hs9FUmreWX4ecHh/4laxDq62RxWBN4KgbTTWwZJsBlS', 'customer', 'active', 'Uttara', NULL, '2026-09-22 16:23:43', '2026-09-22 16:23:43'),
(22, 'Seller Debug', 'sellerdebug@example.com', '01711111111', '$2y$10$HKVJtAeUKiN8KbjD0Tui9emXoDPU7cPCJY5aGU0rRS73wCTJthIiK', 'seller', 'active', 'Test address', NULL, '2026-09-22 16:45:02', '2026-09-22 16:45:02'),
(23, 'Seller Test 2', 'sellertest2@example.com', '01712345679', '$2y$10$koPlvho.gvZZ7sKZkcCaz.WOnOa3pBW7MGbMK6k08OQjDl7e03ASW', 'seller', 'active', 'Dhaka', NULL, '2026-09-22 16:49:48', '2026-09-22 16:49:48'),
(24, 'Seller Test 4', 'sellertest4@example.com', '01712345681', '$2y$10$RN5N6cxpIrVsLesXIMv7E.VgtioOn26F/H8LoCUqRxOMUSLlsKLrC', 'seller', 'active', 'Dhaka', NULL, '2026-09-22 16:50:58', '2026-09-22 16:50:58'),
(25, 'uiu', 'uiu@hermart.com', '0170000000', '$2y$10$cFLUn5xa6zoiqa9a3RuS.eFdk2LbP3CFOgg.J498gnG/VsPBY3hvi', 'customer', 'active', 'Badda', NULL, '2026-09-22 17:02:59', '2026-09-22 17:02:59'),
(26, 'HP', 'hp@hermart.com', '01722162416', '$2y$10$lpkq2HsoFyMmcqZvYQhibOzgDfUwT2cdqzKKVx6MX0/OzO.Y8LDiq', 'customer', 'active', 'Dhaka', NULL, '2026-09-23 02:56:52', '2026-09-23 02:56:52'),
(27, 'United', 'united@hermart.com', '0170006526', '$2y$10$Yp7ZVmpcqOvQVQYTP9cKZuGZmv0Lg/ar3FDEehfx7kVvFNwSoEbWi', 'seller', 'active', 'Dhaka', NULL, '2026-09-23 03:01:05', '2026-09-23 03:01:05'),
(28, 'uiu1', 'uiu1@hermart.com', '0167576567', '$2y$10$QUJ/Zv5Zyk.Bd6dk/w6/MOpFdc1DRmQiVhFFHCXEqAdbCYckeFmEm', 'seller', 'active', 'Dhaka', NULL, '2026-09-24 03:47:37', '2026-09-24 03:47:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bk_user` (`customer_id`),
  ADD KEY `fk_bk_service` (`service_id`),
  ADD KEY `idx_bk_status` (`status`),
  ADD KEY `idx_bk_vendor` (`vendor_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_biz_owner` (`owner_id`),
  ADD KEY `fk_biz_cat` (`category_id`),
  ADD KEY `fk_biz_loc` (`location_id`),
  ADD KEY `idx_biz_verified` (`verified`),
  ADD KEY `idx_biz_district` (`district`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cart_user` (`customer_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_cart_product` (`cart_id`,`product_id`),
  ADD KEY `fk_ci_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_co_user` (`customer_id`),
  ADD KEY `fk_co_seller` (`seller_id`),
  ADD KEY `idx_co_status` (`status`);

--
-- Indexes for table `custom_order_items`
--
ALTER TABLE `custom_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_coi_co` (`custom_order_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_del_user` (`customer_id`),
  ADD KEY `idx_del_order` (`order_id`),
  ADD KEY `idx_del_status` (`status`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loc_type` (`type`),
  ADD KEY `idx_loc_parent` (`parent_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_customer` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_oi_product` (`product_id`),
  ADD KEY `fk_oi_seller` (`seller_id`),
  ADD KEY `idx_oi_order` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pay_order` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_seller` (`seller_id`),
  ADD KEY `fk_product_cat` (`category_id`),
  ADD KEY `idx_product_status` (`status`),
  ADD KEY `idx_product_price` (`price`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pi_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rev_user` (`customer_id`),
  ADD KEY `fk_rev_order` (`order_id`),
  ADD KEY `idx_rev_product` (`product_id`),
  ADD KEY `idx_rev_seller` (`seller_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `custom_orders`
--
ALTER TABLE `custom_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `custom_order_items`
--
ALTER TABLE `custom_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bk_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bk_user` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_bk_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `businesses`
--
ALTER TABLE `businesses`
  ADD CONSTRAINT `fk_biz_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_biz_loc` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_biz_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_orders`
--
ALTER TABLE `custom_orders`
  ADD CONSTRAINT `fk_co_seller` FOREIGN KEY (`seller_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_co_user` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `custom_order_items`
--
ALTER TABLE `custom_order_items`
  ADD CONSTRAINT `fk_coi_co` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `fk_del_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_del_user` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `fk_loc_parent` FOREIGN KEY (`parent_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_oi_seller` FOREIGN KEY (`seller_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_seller` FOREIGN KEY (`seller_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rev_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rev_seller` FOREIGN KEY (`seller_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rev_user` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

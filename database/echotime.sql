-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 10:13 AM
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
-- Database: `echotime`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT 'default_blog_image.jpg',
  `content` text NOT NULL,
  `author` varchar(100) DEFAULT 'Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`) VALUES
(12, 'Watches', NULL),
(13, 'Earbuds', NULL),
(14, 'Smart Watches', 12),
(15, 'Luxury Watches', 12),
(16, 'Digital Watches', 12),
(17, 'Fitness / Health Watches', 12),
(18, 'True Wireless Earbuds (TWS)', 13),
(19, 'Noise Cancelling Earbuds', 13),
(20, 'Gaming Earbuds', 13);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','shipped','cancelled','completed') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `order_notes` text DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `account_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `promotion_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_featured` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `discount` decimal(5,2) DEFAULT 0.00,
  `delivery_time` varchar(100) DEFAULT '4–9 days',
  `sku` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `tags`, `price`, `stock`, `image`, `created_at`, `promotion_id`, `status`, `is_featured`, `stock_quantity`, `discount`, `delivery_time`, `sku`) VALUES
(18, 14, 'P25 Full-touch', 'Material: Plastic, Metal, Glass\r\n\r\nProduct Attributes:  Battery Contains\r\n\r\nPackage Size  :   200*100*40(mm); 150*150*50(mm)\r\n\r\n\r\nProduct information:\r\n\r\nApplicable platform: android platform, Apple iOS platform\r\nCompatible Platforms: iOS\r\nScreen size: 1.69\r\nAppearance size: L*W*H=44.4*37*9.9\r\nMaterial: Alloy case, tempered mineral glass mirror, plastic back cover\r\nBattery capacity: 320MAH\r\nProduct weight: 133G\r\nBody memory: RAM256KB ROM64Mb\r\nWearing method: wristband\r\nOperation mode: touch + button\r\nInterface: USB 2.0\r\nWristband Material: Silicone\r\nColor: black, pink, gold, gray', NULL, 5999.00, 20, '1760764561_5_9bc145aa-2805-4e9b-8048-471ef809d279.jpg', '2025-10-18 05:16:01', 1, 'active', 0, 0, 20.00, '4–9 days', NULL),
(19, 17, 'PulseMate Pro', 'Material:  Plastic, Glass, Others\r\n\r\nProduct Attributes: Battery Contains\r\n\r\nPackage Size:  150*50*60(mm)\r\n\r\n\r\nOverview:\r\n\r\nPulseMate Pro: Your Ultimate Health and Fitness Companion tittle and than Meet PulseMate Pro, the smartwatch designed to elevate your lifestyle. Seamlessly combining advanced sports tracking, weather forecasting, heart rate monitoring, and blood pressure management, PulseMate Pro is your all-in-one partner for health and fitness. Stay informed with real-time weather updates, keep track of your vital signs, and never miss a beat with message reminders—all from your wrist. Experience unparalleled convenience and insight with PulseMate Pro, where technology meets your everyday needs.\r\n\r\nSpecifications:\r\nSupply category: Spot\r\nApplicable people: business, general public, fashion, adult\r\nScreen size: 1.28\r\nBody memory: 64Mb\r\nScreen material: IPS TFT glass\r\nWearing style: wrist strap\r\nCompatible system: fully compatible\r\nWireless distance: 5m (inclusive) -10m (inclusive)\r\nBattery capacity: 220 mA\r\nOperation mode: touch + button\r\nProduct size: 15.1*7.5*3.0cm\r\nScreen resolution: 240*240\r\nWristband material: tpu\r\nProduct weight: 80 (g)\r\ncolor: black\r\nColor: pink, blue, yellow, black\r\nSize Information: 15.1*7.5*3.0cm', NULL, 6299.00, 15, '1760766522_10_4cf13d5b-b180-4a8c-b49a-937843367f50.jpg', '2025-10-18 05:48:42', NULL, 'active', 0, 0, 25.00, '4–9 days', NULL),
(20, 14, 'Multi-scene Sports', 'Material: Plastic, Others\r\nProduct Attributes : Battery Contains\r\nPackage Size:  154*81*25(mm); 100*100*20(mm)\r\n\r\n\r\nProduct information :\r\nWristband Material: Silicone\r\nMemory: 128M\r\nTouch screen: G+F capacitive touch TP, sea oak 816D\r\nScreen: 1.71-inch 280*320 resolution high-definition square screen\r\nConnection: Bluetooth 5.0\r\nHeart Rate: HRS3690\r\nPedometer: Silan Micro SC7A20\r\nCharging method: 2PIN magnetic cable\r\nBattery: Capacity 380mAH (polymer pure cobalt battery)\r\nWaterproof grade: 5ATM\r\nSupported devices: Android 6.0 iOS9.0 and above\r\n\r\n\r\n\r\nSize Information:\r\n\r\nProduct size: length 54.8* width 43.5* thickness 14.8mm', NULL, 10299.00, 15, '1760767408_2_467c9bd5-05d2-4265-a32c-bc8938460194.jpg', '2025-10-18 06:03:28', 1, 'active', 0, 0, 10.00, '4–9 days', NULL),
(21, 15, 'Female Wrist', 'Material:             Metal\r\nProduct Attributes:   Battery Contains                 \r\nPackage Size:         150*65*20(mm)\r\n\r\n\r\nOverview：\r\n\r\nWomen Watches Luxury Quartz Female Wrist Watches Fashion Casual Diamond Ladies Watch Gifts For Women Clock With Box Reloj Mujer\r\n\r\n\r\nSpecification：  \r\n\r\nSpecifications:\r\nWaterproof depth: 30m (3ATM / 3BAR)\r\nCase thickness: 7mm\r\nDial diameter: 28mm\r\nLength of watchband: about 200mm\r\nWatchband width: 14mm\r\nMovement: quartz movement\r\nMirror material: high hardness mineral glass (scratch resistance)\r\nMaterial of watchband: alloy + ceramic\r\nShell material: alloy', NULL, 2899.00, 20, '1762744298_3_1614857521083.jpg', '2025-11-10 03:11:38', 0, 'active', 0, 0, 10.00, '6-12 DAYS', 'CJLX102915802BY'),
(22, 15, 'Gold Dress Watches', 'Material:            Metal\r\nProduct Attributes:  Battery Contains\r\nPackage Size:        130*80*90(mm)', NULL, 3699.00, 30, '1762744762_3_1210959191978.jpg', '2025-11-10 03:19:22', 1, 'active', 0, 0, 8.00, '6-12 DAYS', 'CJZBNSQL00109-Men-Black'),
(23, 19, 'TUNE120TWS wireless', 'Material:            Plastic\r\nProduct Attributes:  Battery Contains\r\nPackage Size:        150*80*40(mm)\r\n\r\nStyle: Earplugs\r\nCommunication: wireless\r\nUse: portable media player, mobile phone, Dj, games, sports, travel, professional\r\nFunction: Bluetooth, microphone', NULL, 3999.00, 10, '1762745762_1_1428098650339.png', '2025-11-10 03:36:02', NULL, 'active', 0, 0, 10.00, '6-12 DAYS', 'CJXFBXEJ01326-Dark Blue'),
(24, 18, 'Wireless Bluetooth Headphones', 'Wireless Bluetooth Headphones, Small, Portable, And Very Practical.\r\n\r\nMaterial:            Plastic\r\nProduct Attributes:  Battery Contains\r\nPackage Size:        130*80*45(mm)\r\n\r\nPackage Size\r\nLength（cm）：13\r\n\r\nWidth（cm）：8\r\n\r\nHeight（cm）：4.5\r\n\r\nWeight（kg）：0.12', NULL, 4799.00, 10, '1762746308_1_d26d512a-5354-4840-b7e3-6ccc3ed8baec.jpg', '2025-11-10 03:45:08', NULL, 'active', 0, 0, 8.00, '4-8 DAYS', 'CJEJ233161101AZ'),
(25, 13, 'Bluetooth-compatible Headset', 'Overview:\r\n\r\n1. 9D immersive stereo sound Stereo Surround Sound-upgrade Bluetooth 5.3 Connect when you turn on the phone.\r\n2. Ergonomics fitted to the curve of the ear precisely considered comfort.\r\n3. Center of gravity optimization stable wear.\r\n4. Lightweight and compact portable.\r\n\r\n\r\nProduct information :\r\nTransmission range: 10m\r\nBluetooth protocol: 5.3\r\nUsage: ear plug type\r\nMonobinaural: Bilateral Stereo\r\nType: neutral transparent warehouse black, neutral transparent warehouse white, neutral transparent warehouse color, electroplating +3 yuan private chat.\r\n\r\nMaterial:            Plastic\r\nProduct Attributes:  Battery Contains\r\nPackage Size:        200*180*30(mm)', NULL, 2840.00, 14, '1762747015_4_24534605-8814-4a03-80bd-6eacea7cc22b.jpg', '2025-11-10 03:56:55', 1, 'active', 1, 0, 15.00, '6-12 DAYS', 'CJEJ171279401AZ'),
(26, 15, 'Diamond Women Watches', 'Material:           Metal\r\nProduct Attributes: Battery Contains\r\nPackage Size:       200*150*50(mm)\r\n\r\nOverview:\r\n\r\nUnique design, stylish and beautiful.\r\nGood material, High quality.\r\n\r\n\r\n\r\nProduct information:\r\nColor: golden suit 1, silver suit 1, rose gold set 1, golden suit suit 2, silver suit suit 2, rose gold suit 2, silver suit suit 3\r\nThickness: 8mm\r\nPacking specification: Watch necklace earrings bracelet box\r\nApplicable people: Female\r\nStyle: Fashion\r\n\r\n\r\nPacking list:\r\nWatch+necklace+earrings*1pair+bracelet+box', NULL, 3250.00, 45, '1762748726_2_ce9a1e79-8a88-4347-bd2b-41ac41e79122.jpg', '2025-11-10 04:25:26', NULL, 'active', 0, 0, 5.00, '6-12 DAYS', 'CJNS203460401AZ'),
(27, 19, 'S35 Wireless Bluetooth', 'S35 Wireless Bluetooth Headset Sports In-ear Noise Reduction Super Long\r\n\r\n\r\nMaterial:           Plastic\r\nProduct Attributes: Battery Contains\r\nPackage Size:       120*100*80(mm)\r\n\r\nProduct information:\r\nColor: Black can be switched between Chinese and English, White can be switched between Chinese and English\r\nTransmission range: 15 meters\r\nChip type: Zhongke Lanxun\r\nBattery life: 4-8 hours\r\nFeatures: ultra-long life battery, call function, voice control, music support, e-sports low latency\r\nMaterial: PC ABS\r\nUsage: in-ear\r\nStyle: E-sports games', NULL, 2360.00, 24, '1762749136_1_c07d50be-be9f-43db-a3f0-5140aa786ee8_trans.jpeg', '2025-11-10 04:32:16', NULL, 'active', 0, 0, 14.00, '6-12 DAYS', 'CJYP239381901AZ'),
(29, 16, 'Fashion Military Wristwatch', 'Fashion Military Wristwatch For Men Women Waterproof Clock LED Light Outdoor Digital Sport Electronic Watches\r\n\r\nMaterial:            Metal, Leather\r\nProduct Attributes:  Battery Contains\r\nPackage Size:        170*50*30(mm)\r\n\r\nOverview:\r\n\r\nUnique design, stylish and beautiful.\r\nGood material, High quality.\r\n\r\n\r\n\r\nProduct information:\r\nColor: gold steel belt, silver steel belt, black steel belt, gold belt, silver belt, black belt\r\nApplicable population: Male\r\nBattery capacity: Button cell\r\nStyle: Street, personality, future style', NULL, 3999.00, 16, '1762750876_4_95f79b0d-b5b3-4b57-a142-b8af9a0512ff.jpg', '2025-11-10 05:01:16', 1, 'active', 0, 0, 10.00, '6-12 DAYS', 'CJQL222402106FU'),
(31, 19, 'K50 Zinc Alloy  earbuds', 'Material:           Plastic\r\nProduct Attributes: Battery Contains\r\nPackage Size:       120*120*80(mm)\r\n\r\nProduct information:\r\nColor: K50 black [zinc alloy material], K50 silver [zinc alloy material], X27PRO Black, X27PRO silver, X27PRO Gold, MAX60 black zinc alloy\r\nTransmission range: 15 meters\r\nChip type: Zhongke Lanxun\r\nBattery life: 4-8 hours\r\nFunctions: noise reduction, ultra-long life battery, call function, voice control, music support, others\r\nMaterial: PC ABS\r\nUsage: in-ear\r\nStyle: sports style\r\n\r\n\r\nPacking list:\r\nHeadphones + data cable + instruction manual', NULL, 3890.00, 23, '1762751754_1_a9a6d878-8a48-4df3-b3e3-87537f60400b_trans.jpeg', '2025-11-10 05:15:54', NULL, 'active', 0, 0, 8.00, '6-12 DAYS', 'CJYP239382501AZ');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `created_at`) VALUES
(25, 18, '1760764781_0_2_7fca1170-22c2-49c2-80b6-7042ac4d95af.jpg', '2025-10-18 05:19:41'),
(26, 18, '1760764781_1_1_f785bfc7-a63c-4a57-ae13-f25182c50c81.jpg', '2025-10-18 05:19:41'),
(27, 18, '1760764781_2_6_6202ccc0-de12-4f44-b9c8-3b6f6033d76d.jpg', '2025-10-18 05:19:41'),
(28, 18, '1760764781_3_3_c747b7d6-22a9-411c-a4ab-a757e37ea41e.jpg', '2025-10-18 05:19:41'),
(29, 18, '1760764781_4_9_5476640d-4faf-42ec-a8c7-65b761a5dda9.jpg', '2025-10-18 05:19:41'),
(30, 19, '1760766522_0_4_1621217438987.jpg', '2025-10-18 05:48:42'),
(31, 19, '1760766522_1_3_1621217438990.jpg', '2025-10-18 05:48:42'),
(32, 19, '1760766522_2_11_0cc79efb-8c20-452e-8ea6-0ad8ea1d87cd.jpg', '2025-10-18 05:48:42'),
(33, 19, '1760766522_3_6_61f8591e-2087-472e-ace9-842dd8f835d9.jpg', '2025-10-18 05:48:42'),
(34, 19, '1760766522_4_12_e5f6c7dc-9232-40e1-9cdd-7524544513d7.jpg', '2025-10-18 05:48:42'),
(35, 20, '1760767408_0_3_88772a73-f250-482f-87bb-e2d3ec9d5a75.jpg', '2025-10-18 06:03:28'),
(36, 20, '1760767408_1_6_713414de-f0b5-41d6-9b32-811f4ca476c5.jpg', '2025-10-18 06:03:28'),
(37, 20, '1760767408_2_8_121c4e73-082a-43dc-aed6-cc14aa45d484.jpg', '2025-10-18 06:03:28'),
(38, 20, '1760767408_3_1_1eb42927-14fc-43f7-a410-08bc4d6f6e78.jpg', '2025-10-18 06:03:28'),
(39, 20, '1760767408_4_7_4227c9af-691b-464b-89c5-955291d88d22.jpg', '2025-10-18 06:03:28'),
(40, 21, '1762744298_0_4_1614857521079.jpg', '2025-11-10 03:11:38'),
(41, 21, '1762744298_1_5_1614857521056.jpg', '2025-11-10 03:11:38'),
(42, 21, '1762744298_2_2_1614857521176.jpg', '2025-11-10 03:11:38'),
(43, 21, '1762744298_3_1_1614857521076.jpg', '2025-11-10 03:11:38'),
(44, 22, '1762744762_0_6_3897637977270.jpg', '2025-11-10 03:19:22'),
(45, 22, '1762744762_1_2_2894934362897.jpg', '2025-11-10 03:19:22'),
(46, 22, '1762744762_2_13_1766322047781.jpg', '2025-11-10 03:19:22'),
(47, 22, '1762744762_3_14_392001694822.jpg', '2025-11-10 03:19:22'),
(48, 22, '1762744762_4_12_4888944426669.jpg', '2025-11-10 03:19:22'),
(49, 23, '1762745762_0_3_4830502286151.png', '2025-11-10 03:36:02'),
(50, 23, '1762745762_1_2_5068802216738.png', '2025-11-10 03:36:02'),
(51, 23, '1762745762_2_1631711834249760768.webp', '2025-11-10 03:36:02'),
(52, 23, '1762745762_3_1631711834522390528.webp', '2025-11-10 03:36:02'),
(53, 23, '1762745762_4_4_272351540620.png', '2025-11-10 03:36:02'),
(54, 24, '1762746308_0_4_32ad88d8-8a2e-4cb7-981c-dbe88983fad0.jpg', '2025-11-10 03:45:08'),
(55, 24, '1762746308_1_2_003c0e09-9719-4bb3-8786-b437f6c3f580.jpg', '2025-11-10 03:45:08'),
(56, 24, '1762746308_2_5_f73443bd-959e-4962-ad82-ddc30d724fbc.jpg', '2025-11-10 03:45:08'),
(57, 24, '1762746308_3_4_32ad88d8-8a2e-4cb7-981c-dbe88983fad0.jpg', '2025-11-10 03:45:08'),
(58, 25, '1762747015_0_5_b8b90b23-8bf9-48a3-bc1f-90be35a8d2e0.jpg', '2025-11-10 03:56:55'),
(59, 25, '1762747015_1_7_86dcfbf8-ed26-4d41-b72c-dcf57b8361c9.jpg', '2025-11-10 03:56:55'),
(60, 25, '1762747015_2_2_e128d4df-7b2f-4094-ac55-64341a13e4ca.jpg', '2025-11-10 03:56:55'),
(61, 25, '1762747015_3_3_c317ee52-7e29-43f6-adab-456ae3f3f089.jpg', '2025-11-10 03:56:55'),
(62, 26, '1762748726_0_2405160359580328700.webp', '2025-11-10 04:25:26'),
(63, 26, '1762748726_1_2405160359590322600.webp', '2025-11-10 04:25:26'),
(64, 26, '1762748726_2_5_357b927e-afd7-4bd0-b8fd-1559153d7e7d.jpg', '2025-11-10 04:25:26'),
(65, 26, '1762748726_3_2405160400000327000.webp', '2025-11-10 04:25:26'),
(66, 27, '1762749136_0_5_a2c0f0d4-e826-48c9-8623-3d6d49d3d594_trans.jpeg', '2025-11-10 04:32:16'),
(67, 27, '1762749136_1_6_89a1df13-5212-4638-b692-9cd427bd8d5b_trans.jpeg', '2025-11-10 04:32:16'),
(68, 27, '1762749136_2_85066074-5fd0-4111-bf23-2e67c8546c62_trans.webp', '2025-11-10 04:32:16'),
(69, 27, '1762749136_3_60b7fac8-7e46-4a58-abb4-961a1d740b42_trans.webp', '2025-11-10 04:32:16'),
(70, 27, '1762749136_4_c4e41713-6549-4e26-8014-c3f8f15d0a75_trans.webp', '2025-11-10 04:32:16'),
(81, 29, '1762751181_0_645f10ca-e2af-4de7-9081-0bb74c6a5a5e_trans.webp', '2025-11-10 05:06:21'),
(82, 29, '1762751181_1_5bb51b59-ceb5-47f9-a42a-b43fc86a0346_trans.webp', '2025-11-10 05:06:21'),
(83, 29, '1762751181_2_2e3191f3-fcb4-4afb-8f40-45ba719bebe0_trans.webp', '2025-11-10 05:06:21'),
(84, 29, '1762751181_3_59080399-8cc6-4c96-8874-641da48a38fc_trans__1_.webp', '2025-11-10 05:06:21'),
(85, 29, '1762751181_4_f60b5428-ef0c-4b60-b66f-9f1d731dad23_trans.webp', '2025-11-10 05:06:21'),
(86, 31, '1762751754_0_2_9840914e-fd68-4112-993e-3600cace5418_trans.jpeg', '2025-11-10 05:15:54'),
(87, 31, '1762751754_1_4_229c80d4-9b77-459c-a84c-5fc471297a2a_trans.jpeg', '2025-11-10 05:15:54'),
(88, 31, '1762751754_2_6_bcc34978-a11a-43ea-a8ac-44dba95fb3b8_trans.jpeg', '2025-11-10 05:15:54'),
(89, 31, '1762751754_3_7_22a6f4f5-5c4b-40cc-a007-7aef87b9d564_trans.jpeg', '2025-11-10 05:15:54'),
(90, 31, '1762751754_4_3_ec1ba51f-1abc-49bf-a08e-d9e07e627bc8_trans.jpeg', '2025-11-10 05:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `name`, `code`, `discount`, `start_date`, `end_date`, `status`) VALUES
(1, 'New Year Sale', '', 30.00, '2025-11-10', '2025-11-11', 'active'),
(2, '11.11', '30', 20.00, '2025-11-10', '2025-11-12', '');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `total_sales` decimal(10,2) DEFAULT NULL,
  `total_orders` int(11) DEFAULT NULL,
  `total_customers` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_name` varchar(255) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_name`, `setting_value`) VALUES
('address', '123 Main Street, City, State, ZIP'),
('contact_email', 'contact@example.com'),
('site_name', 'My E-Commerce Site');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `created_at`, `profile_picture`) VALUES
(3, 'Admin', 'admin@gmail.com', '$2y$10$0EhnlfDAb0zynetO1XiYm.2JEHSSWs4vWOicIn3G86iYNCbEQkyD6', 'admin', '03283668351', 'street 6 blok b abdullah ghoth bin qasim malir karachi', '2025-09-15 09:58:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_ibfk_1` (`parent_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 02:50 AM
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
-- Database: `user_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `status` enum('pending','accepted','removed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `status` enum('pending','accepted') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friendships`
--

INSERT INTO `friendships` (`id`, `user_id`, `friend_id`, `status`, `created_at`) VALUES
(3, 6, 2, 'accepted', '2026-03-06 15:20:32'),
(5, 12, 6, 'accepted', '2026-03-07 03:15:09'),
(6, 3, 2, 'accepted', '2026-03-08 08:54:57'),
(7, 13, 2, 'accepted', '2026-03-08 08:56:26'),
(8, 14, 2, 'accepted', '2026-03-09 02:03:04');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `media_url` varchar(500) DEFAULT NULL,
  `media_type` enum('image','video') DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_edited` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `is_read`, `created_at`, `media_url`, `media_type`, `is_deleted`, `is_edited`) VALUES
(1, 2, 6, 'yo', 1, '2026-03-06 23:50:37', NULL, NULL, 0, 0),
(2, 2, 6, 'musta', 1, '2026-03-06 23:50:43', NULL, NULL, 0, 0),
(3, 6, 2, 'allg', 1, '2026-03-06 23:53:25', NULL, NULL, 0, 0),
(4, 2, 6, 'yownnn', 1, '2026-03-06 23:53:43', NULL, NULL, 0, 0),
(5, 2, 6, 'boiiii', 1, '2026-03-07 00:05:21', NULL, NULL, 0, 0),
(6, 6, 2, 'yun oh', 1, '2026-03-07 00:05:29', NULL, NULL, 0, 0),
(7, 2, 6, '', 1, '2026-03-07 00:12:03', '/Tourna/uploads/chat/chat_69ab6d53454150.09691810.mp4', 'video', 0, 0),
(8, 2, 6, 'ehhhh', 1, '2026-03-07 00:13:29', NULL, NULL, 0, 0),
(9, 2, 6, 'try uli', 1, '2026-03-07 07:17:49', NULL, NULL, 0, 0),
(10, 2, 6, '😄ye', 1, '2026-03-07 11:59:22', NULL, NULL, 0, 1),
(11, 2, 6, '', 1, '2026-03-07 12:46:21', NULL, NULL, 1, 0),
(12, 2, 6, '', 1, '2026-03-08 14:45:22', NULL, 'image', 1, 0),
(13, 2, 3, 'Hi, would you like to join our volleyball practice later?', 1, '2026-03-09 00:44:37', NULL, NULL, 0, 0),
(14, 2, 3, '', 1, '2026-03-09 01:36:33', NULL, NULL, 1, 0),
(15, 3, 2, 'Sure!', 1, '2026-03-09 01:37:23', NULL, NULL, 0, 0),
(16, 2, 6, 'hey', 1, '2026-03-11 00:56:32', NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `headline` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `body` longtext DEFAULT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'General',
  `author` varchar(100) NOT NULL DEFAULT 'TournaMeet',
  `author_bg` varchar(120) DEFAULT 'linear-gradient(135deg,#F97316,#EA580C)',
  `image_path` varchar(255) DEFAULT NULL,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `created_at`) VALUES
(1, 'New user registered: Kurt Ivan Castro (athlete)', '2026-03-03 12:17:10'),
(2, 'New user registered: Kurty (athlete)', '2026-03-04 02:06:38'),
(3, 'New user registered: Selwyn (athlete)', '2026-03-05 03:21:15'),
(4, 'New user registered: Baruch (organizer)', '2026-03-05 03:28:25'),
(5, 'New user registered: Marl Concepcion (organizer)', '2026-03-05 03:43:19'),
(6, 'New user registered: Organizer (organizer)', '2026-03-05 13:41:58'),
(7, 'New user registered: Organizer (organizer)', '2026-03-05 13:48:25'),
(8, 'New user registered: Kurty (athlete)', '2026-03-07 03:13:21'),
(9, 'New user registered: Vyreigh (athlete)', '2026-03-08 08:56:13'),
(10, 'New user registered: Marl A Concepcion (athlete)', '2026-03-09 02:01:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `ref` varchar(30) NOT NULL,
  `customer_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`customer_json`)),
  `address_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`address_json`)),
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items_json`)),
  `payment` varchar(20) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(10,2) NOT NULL DEFAULT 150.00,
  `total` decimal(10,2) NOT NULL,
  `coupon` varchar(30) DEFAULT '',
  `status` varchar(30) NOT NULL DEFAULT 'Order Placed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `ref`, `customer_json`, `address_json`, `items_json`, `payment`, `subtotal`, `discount`, `shipping`, `total`, `coupon`, `status`, `created_at`) VALUES
(1, 'BS-40A5F838', '{\"fn\":\"Kurt\",\"ln\":\"Ivan Castro\",\"em\":\"kurtivancastro@gmail.com\",\"ph\":\"0991 366 3540\"}', '{\"ad\":\"barangay town\",\"ci\":\"baguio city\",\"pv\":\"benguet\",\"zp\":\"2600\",\"rg\":\"CAR \\u2013 Cordillera\",\"nt\":\"\"}', '[{\"id\":1,\"name\":\"Basketball\",\"price\":1500,\"cat\":\"Basketball\",\"qty\":1}]', 'cod', 1500.00, 225.00, 150.00, 1425.00, 'CHAMP15', 'Order Placed', '2026-03-09 02:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_ref` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL,
  `note` varchar(300) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_history`
--

INSERT INTO `order_history` (`id`, `order_ref`, `status`, `note`, `created_at`) VALUES
(1, 'BS-40A5F838', 'Order Placed', 'Your order has been received and is being processed.', '2026-03-09 02:06:03');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `caption` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `media` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video') DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `image`, `caption`, `created_at`, `media`, `media_type`, `location`) VALUES
(25, 2, NULL, 'Vball', '2026-03-11 01:16:23', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_comments`
--

CREATE TABLE `post_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `media_type` enum('image','video') DEFAULT 'image',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_media`
--

INSERT INTO `post_media` (`id`, `post_id`, `filename`, `media_type`, `sort_order`, `created_at`) VALUES
(10, 25, 'post_69b0c267005063.97430984.jpg', 'image', 0, '2026-03-11 01:16:23');

-- --------------------------------------------------------

--
-- Table structure for table `post_reactions`
--

CREATE TABLE `post_reactions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction` enum('like','love','fire','wow','haha') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `cat` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 10,
  `total_stock` int(11) NOT NULL DEFAULT 20,
  `rating` decimal(3,1) NOT NULL DEFAULT 5.0,
  `review_count` int(11) NOT NULL DEFAULT 0,
  `badge` varchar(20) DEFAULT '',
  `badge_text` varchar(50) DEFAULT '',
  `description` text DEFAULT NULL,
  `specs_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specs_json`)),
  `links_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`links_json`)),
  `is_new` tinyint(1) DEFAULT 0,
  `seller_name` varchar(200) DEFAULT '',
  `seller_email` varchar(200) DEFAULT '',
  `seller_contact` varchar(50) DEFAULT '',
  `condition_label` varchar(50) DEFAULT 'Brand New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `cat`, `price`, `stock`, `total_stock`, `rating`, `review_count`, `badge`, `badge_text`, `description`, `specs_json`, `links_json`, `is_new`, `seller_name`, `seller_email`, `seller_contact`, `condition_label`, `created_at`) VALUES
(1, 'Basketball', 'Basketball', 1500.00, 149, 150, 5.0, 0, 'new', 'NEW ARRIVAL', 'Circle', '[]', '[]', 1, '', '', '', 'Brand New', '2026-03-09 00:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `reviewer` varchar(150) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `id` int(11) NOT NULL,
  `shop_name` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `contact` varchar(50) DEFAULT '',
  `is_verified` tinyint(1) DEFAULT 0,
  `total_sales` int(11) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sport` varchar(100) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#F07B20',
  `organizer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `sport` varchar(80) NOT NULL,
  `location` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(20) NOT NULL,
  `format` varchar(100) NOT NULL,
  `entry_fee` varchar(50) NOT NULL,
  `prize` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `slots_total` int(11) NOT NULL DEFAULT 16,
  `slots_taken` int(11) NOT NULL DEFAULT 0,
  `organizer` varchar(150) NOT NULL,
  `image_url` varchar(300) DEFAULT '',
  `event_time` time DEFAULT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `requirements` text DEFAULT NULL,
  `organizer_note` text DEFAULT NULL,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(50) DEFAULT NULL,
  `prize_pool` decimal(10,2) NOT NULL DEFAULT 0.00,
  `registration_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `slots` int(11) NOT NULL DEFAULT 0,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `sport`, `location`, `date`, `time`, `format`, `entry_fee`, `prize`, `description`, `slots_total`, `slots_taken`, `organizer`, `image_url`, `event_time`, `registration_deadline`, `is_closed`, `requirements`, `organizer_note`, `views_count`, `created_by`, `prize_pool`, `registration_fee`, `slots`, `latitude`, `longitude`, `status`) VALUES
(24, 'Volleyball Tryouts', 'Ball Sports', 'athletic bowl, baguio city', '2026-03-14', '13:00', 'Tryouts', '', '0', '', 16, 2, 'Organizer', '', NULL, '2026-03-11 00:00:00', 0, NULL, NULL, 0, 'Organizer', 0.00, 0.00, 0, 16.4075088, 120.5959532, 'approved'),
(26, 'Baguio Basketball Tourny 2026', 'Ball Sports', 'Idogan Court, Baguio City', '2026-03-31', '15:00', 'League', '', '0', '', 24, 1, 'Organizer', '', NULL, '2026-03-21 00:00:00', 0, NULL, NULL, 0, 'Organizer', 0.00, 0.00, 0, 16.4152028, 120.5639342, 'approved'),
(27, 'Badminton Tryouts', 'Racket Sports', 'Baguio City National Science High School', '2026-03-28', '10:00', 'Double Elimination', '', '0', '', 15, 1, 'Organizer', '', NULL, '2026-03-22 00:00:00', 0, NULL, NULL, 0, 'Organizer', 0.00, 0.00, 0, 16.4157905, 120.5614859, 'approved'),
(29, 'BBall', 'Ball Sports', 'Idogan, Baguio City', '2026-03-14', '', 'Tryouts', '', '0', '', 16, 1, 'Organizer', '', NULL, '2026-03-12 00:00:00', 0, NULL, NULL, 0, 'Organizer', 0.00, 0.00, 0, 16.4152575, 120.5639174, 'approved'),
(30, 'Table Tennis', 'Racket Sports', 'Baguio City National Science High School', '2026-03-21', '11:00', 'Tryouts', '', '1,000', '', 6, 0, 'User', '', NULL, '2026-03-19 00:00:00', 0, NULL, NULL, 0, 'User', 0.00, 0.00, 0, 16.4157905, 120.5614859, 'pending'),
(31, 'try', 'Ball Sports', 'baguio central school', '2026-03-21', '11:19', 'Tryouts', '', '0', '', 15, 0, 'User', '', NULL, '2026-03-11 00:00:00', 0, NULL, NULL, 0, 'User', 0.00, 0.00, 0, 16.4133571, 120.5902315, 'pending'),
(32, 'jiujiutsu', 'Combatives', 'Athletic Bowl', '2026-03-21', '11:53', 'Tryouts', '', '', '', 9, 1, 'Organizer', '', NULL, '2026-03-18 00:00:00', 0, NULL, NULL, 0, 'Organizer', 0.00, 0.00, 0, NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_registrations`
--

CREATE TABLE `tournament_registrations` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `athlete_username` varchar(50) NOT NULL,
  `team_name` varchar(120) DEFAULT NULL,
  `members` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','waitlisted') NOT NULL DEFAULT 'pending',
  `attendance_status` enum('unknown','attended','no_show') NOT NULL DEFAULT 'unknown',
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` varchar(50) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_registrations`
--

INSERT INTO `tournament_registrations` (`id`, `tournament_id`, `athlete_username`, `team_name`, `members`, `status`, `attendance_status`, `reviewed_at`, `reviewed_by`, `joined_at`) VALUES
(1, 24, 'Kurt Ivan Castro', 'Karasuno', '', 'approved', 'unknown', '2026-03-07 19:20:41', 'Organizer', '2026-03-07 11:04:44'),
(3, 26, 'Kurt Ivan Castro', 'Barangay Ginebra', '', 'approved', 'unknown', '2026-03-07 19:36:04', 'Organizer', '2026-03-07 11:35:57'),
(4, 27, 'Kurt Ivan Castro', 'Sci High', '', 'approved', 'unknown', '2026-03-07 19:46:45', 'Organizer', '2026-03-07 11:46:35'),
(5, 29, 'Marl A Concepcion', 'PB 2600', '', 'pending', 'unknown', NULL, NULL, '2026-03-09 02:02:32'),
(6, 24, 'Kurt Ivan', 'Sci High', '', 'pending', 'unknown', NULL, NULL, '2026-03-11 00:52:44'),
(7, 32, 'Kurt Ivan', '', '', 'pending', 'unknown', NULL, NULL, '2026-03-11 00:55:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','organizer','athlete') DEFAULT 'athlete',
  `status` enum('pending','approved') DEFAULT 'approved',
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `bio`, `profile_pic`, `created_at`) VALUES
(1, 'TournaMakers', 'TournaMakersAdmin@gmail.com', '$2y$10$fQYd2H1iZtMfFuRsEb0iWuaHRCWeD0l8uyL7WIoyBr5PurskDWlaO', 'admin', 'approved', NULL, NULL, '2026-03-03 12:14:27'),
(2, 'Kurt Ivan', 'kurtivancastro@gmail.com', '$2y$10$apSuIoakJmOe8JMeC0GEzeFNQGb6LUB8Av48JUFH8SHjBEItey30K', 'athlete', 'approved', '', '/pfp_69ae1787417393.93238062.jpg', '2026-03-03 12:17:10'),
(3, 'Kurty', 'kurtivan@gmail.com', '$2y$10$V25F6FIDXoZRSmkLeDJrhuG1Dwq1tKwul.pC4s.Z6YUAjyNznFsm.', 'athlete', 'approved', NULL, NULL, '2026-03-04 02:06:38'),
(5, 'admin', 'admin@example.com', '', 'admin', 'approved', NULL, NULL, '2026-03-05 03:13:42'),
(6, 'Selwyn', 'selwyn@gmail.com', '$2y$10$Em.pf9c17Daq4LLFO9eHeOrz42s1eU8UjcOm7ASHEwsc2EEmRninG', 'athlete', 'approved', '', '/pfp_69b02bbeb397c8.96373692.png', '2026-03-05 03:21:15'),
(7, 'Baruch', 'barchpal@gmail.com', '$2y$10$kPZyoesSBEQ4hBiEvlw8suGSmCJXcHvtebbjTnMKuAYFZKMgyV.Qy', 'organizer', 'approved', NULL, NULL, '2026-03-05 03:28:25'),
(8, 'Marl Concepcion', 'marlvyreigh@gmail.com', '$2y$10$SJohO2rnzKySmUaaWURcKeLpjoJEPf8qoeRMA8fatypi0m8JZXRDy', 'organizer', 'approved', NULL, NULL, '2026-03-05 03:43:19'),
(10, 'Organizer', 'organizer@gmail.com', '$2y$10$tumOV7T815p/ZSsiq.5OAuyVYgPr3QiZgbeN4UO/dStpjjcMGTtCC', 'organizer', '', NULL, NULL, '2026-03-05 13:41:58'),
(11, 'Organizer', 'organizer1@gmail.com', '$2y$10$TSArgp/JQKG8qCqvEBWgaeoYFrZSqV3MOYzIPJOkclnC4OLCNbDn6', 'organizer', 'approved', 'Organizer', '/uploads/profiles/profile_11_1772968649.jpg', '2026-03-05 13:48:25'),
(12, 'Kurty', 'kurty@gmail.com', '$2y$10$ZWcP2QNlQr0aFVL1LN/u8.fmOUuDeKD2dsYS0oJC0R6eFHcrOK.nq', 'athlete', 'approved', NULL, NULL, '2026-03-07 03:13:21'),
(13, 'Vyreigh', 'marl@gmail.com', '$2y$10$u.4Yvl9StYgGtHZYLjFgmOzQqWT/ZBgD.Hb3gcVmlXbvzFOz5W9BC', 'athlete', 'approved', NULL, NULL, '2026-03-08 08:56:13'),
(14, 'Marl A Concepcion', 'ingsssgo@Gmail.com', '$2y$10$Ec0/XycPSjFAcvbniq0Ru.QAbaalkWYNvpAjwtxduogRPodlSG7aW', 'athlete', 'approved', NULL, NULL, '2026-03-09 02:01:14');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('like','comment','friend_request','tournament') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'friend_request', 'Kurty sent you a friend request.', 1, '2026-03-07 11:13:43'),
(2, 6, 'friend_request', 'Kurty sent you a friend request.', 0, '2026-03-07 11:15:09'),
(3, 12, 'friend_request', 'Selwyn accepted your friend request. You are now connected!', 0, '2026-03-07 11:16:00'),
(4, 12, 'friend_request', 'Kurt Ivan Castro accepted your friend request. You are now connected!', 0, '2026-03-07 14:37:54'),
(5, 2, 'comment', 'Vyreigh commented on your post.', 1, '2026-03-08 17:04:07'),
(6, 2, 'like', 'Vyreigh reacted 👍 to your post.', 1, '2026-03-08 17:04:27'),
(7, 2, 'tournament', 'Organizer created a new Ball Sports tournament: \"BBall\" on Mar 14, 2026.', 1, '2026-03-08 17:10:25'),
(8, 3, 'tournament', 'Organizer created a new Ball Sports tournament: \"BBall\" on Mar 14, 2026.', 0, '2026-03-08 17:10:25'),
(9, 6, 'tournament', 'Organizer created a new Ball Sports tournament: \"BBall\" on Mar 14, 2026.', 0, '2026-03-08 17:10:25'),
(10, 12, 'tournament', 'Organizer created a new Ball Sports tournament: \"BBall\" on Mar 14, 2026.', 0, '2026-03-08 17:10:25'),
(11, 13, 'tournament', 'Organizer created a new Ball Sports tournament: \"BBall\" on Mar 14, 2026.', 0, '2026-03-08 17:10:25'),
(12, 3, 'friend_request', 'Kurt Ivan Castro accepted your friend request.', 0, '2026-03-08 22:30:26'),
(13, 13, 'friend_request', 'Kurt Ivan Castro accepted your friend request.', 0, '2026-03-08 22:30:27'),
(14, 2, 'friend_request', 'Marl A Concepcion sent you a friend request.', 1, '2026-03-09 10:03:04'),
(15, 3, 'tournament', 'User created a new Racket Sports tournament: \"Table Tennis\" on Mar 21, 2026.', 0, '2026-03-10 20:04:40'),
(16, 6, 'tournament', 'User created a new Racket Sports tournament: \"Table Tennis\" on Mar 21, 2026.', 0, '2026-03-10 20:04:40'),
(17, 12, 'tournament', 'User created a new Racket Sports tournament: \"Table Tennis\" on Mar 21, 2026.', 0, '2026-03-10 20:04:40'),
(18, 13, 'tournament', 'User created a new Racket Sports tournament: \"Table Tennis\" on Mar 21, 2026.', 0, '2026-03-10 20:04:40'),
(19, 14, 'tournament', 'User created a new Racket Sports tournament: \"Table Tennis\" on Mar 21, 2026.', 0, '2026-03-10 20:04:40'),
(20, 3, 'tournament', 'User created a new Ball Sports tournament: \"try\" on Mar 21, 2026.', 0, '2026-03-10 20:19:25'),
(21, 6, 'tournament', 'User created a new Ball Sports tournament: \"try\" on Mar 21, 2026.', 0, '2026-03-10 20:19:25'),
(22, 12, 'tournament', 'User created a new Ball Sports tournament: \"try\" on Mar 21, 2026.', 0, '2026-03-10 20:19:25'),
(23, 13, 'tournament', 'User created a new Ball Sports tournament: \"try\" on Mar 21, 2026.', 0, '2026-03-10 20:19:25'),
(24, 14, 'tournament', 'User created a new Ball Sports tournament: \"try\" on Mar 21, 2026.', 0, '2026-03-10 20:19:25'),
(25, 2, 'comment', 'Selwyn commented: \"try\"', 1, '2026-03-10 22:32:56'),
(26, 2, 'like', 'Selwyn liked your post.', 1, '2026-03-10 22:37:06'),
(27, 2, 'comment', 'Selwyn commented: \"YEAHHHH\"', 1, '2026-03-10 22:37:10'),
(28, 14, 'friend_request', 'Kurt Ivan accepted your friend request.', 0, '2026-03-10 22:38:31'),
(29, 2, 'tournament', 'Organizer created a new Combatives tournament: \"jiujiutsu\" on Mar 21, 2026.', 1, '2026-03-11 08:54:47'),
(30, 3, 'tournament', 'Organizer created a new Combatives tournament: \"jiujiutsu\" on Mar 21, 2026.', 0, '2026-03-11 08:54:47'),
(31, 6, 'tournament', 'Organizer created a new Combatives tournament: \"jiujiutsu\" on Mar 21, 2026.', 0, '2026-03-11 08:54:47'),
(32, 12, 'tournament', 'Organizer created a new Combatives tournament: \"jiujiutsu\" on Mar 21, 2026.', 0, '2026-03-11 08:54:47'),
(33, 13, 'tournament', 'Organizer created a new Combatives tournament: \"jiujiutsu\" on Mar 21, 2026.', 0, '2026-03-11 08:54:47'),
(34, 14, 'tournament', 'Organizer created a new Combatives tournament: \"jiujiutsu\" on Mar 21, 2026.', 0, '2026-03-11 08:54:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `idx_convo` (`sender_id`,`receiver_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
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
  ADD UNIQUE KEY `ref` (`ref`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_ref` (`order_ref`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ul` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reaction` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_seller` (`seller_email`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`team_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tournament_athlete` (`tournament_id`,`athlete_username`),
  ADD KEY `idx_tournament_status` (`tournament_id`,`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `post_comments`
--
ALTER TABLE `post_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `post_reactions`
--
ALTER TABLE `post_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_ref`) REFERENCES `orders` (`ref`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_comments`
--
ALTER TABLE `post_comments`
  ADD CONSTRAINT `post_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_media`
--
ALTER TABLE `post_media`
  ADD CONSTRAINT `post_media_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD CONSTRAINT `post_reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament_registrations`
--
ALTER TABLE `tournament_registrations`
  ADD CONSTRAINT `fk_registration_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

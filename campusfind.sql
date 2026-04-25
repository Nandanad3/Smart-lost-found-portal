-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 09:01 AM
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
-- Database: `campusfind`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$xSbMdzADyULW8ACUv2bkp.PRKb54hPLgGz3sh6s47sYmR7CNxUeZK'),
(2, 'admin1', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('lost','claimed','found') DEFAULT 'lost',
  `reported_by` int(11) NOT NULL,
  `claimed_by` int(11) DEFAULT NULL,
  `claim_pending` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `category`, `status`, `reported_by`, `claimed_by`, `claim_pending`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', 'claimed', 1, 2, NULL, '2026-04-24 22:06:37', '2026-04-24 22:09:52'),
(2, 'Electronics', 'claimed', 2, 1, NULL, '2026-04-24 22:21:52', '2026-04-24 23:34:19');

-- --------------------------------------------------------

--
-- Table structure for table `lost_notices`
--

CREATE TABLE `lost_notices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(140) NOT NULL,
  `category` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `lost_location` varchar(190) NOT NULL,
  `lost_date` date DEFAULT NULL,
  `contact_phone` varchar(30) NOT NULL,
  `contact_email` varchar(190) DEFAULT NULL,
  `reward_note` varchar(190) DEFAULT NULL,
  `status` enum('active','resolved') NOT NULL DEFAULT 'active',
  `views_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lost_notices`
--

INSERT INTO `lost_notices` (`id`, `user_id`, `title`, `category`, `description`, `lost_location`, `lost_date`, `contact_phone`, `contact_email`, `reward_note`, `status`, `views_count`, `created_at`, `updated_at`) VALUES
(1, 1, 'wallet', 'Electronics', 'red colour and etc', 'b-block', '2002-02-10', '9036363636', 'anas@gmail.com', NULL, 'resolved', 0, '2026-04-24 19:32:22', '2026-04-25 04:54:49'),
(2, 1, 'wallet', 'Wallet / Purse', 'halloo', 'library', '2020-02-10', '9036363636', 'anas@gmail.com', NULL, 'active', 0, '2026-04-25 05:11:42', '2026-04-25 05:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `question1` varchar(200) NOT NULL,
  `answer1` varchar(50) NOT NULL,
  `question2` varchar(200) NOT NULL,
  `answer2` varchar(50) NOT NULL,
  `question3` varchar(200) NOT NULL,
  `answer3` varchar(50) NOT NULL,
  `question4` varchar(200) NOT NULL,
  `answer4` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `item_id`, `question1`, `answer1`, `question2`, `answer2`, `question3`, `answer3`, `question4`, `answer4`) VALUES
(1, 1, 'what is object', 'phone', 'what is color', 'red', 'where did you lost it', 'library', 'what is written on it', 'arjun'),
(2, 2, 'what is color', 'black', 'what is brand', 'apple', 'where didiyou lost it', 'library', 'what is mark on it', 'arjun');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `roll_number` varchar(20) NOT NULL,
  `department` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `joined_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `roll_number`, `department`, `password`, `joined_at`) VALUES
(1, 'muhammed Anas', 'anas@gmail.com', '9036363636', 'cs2000', 'CSE', '$2y$10$Wz2OK9kmS3bedD6vm/ZHcuiMMrgg5dV2f48iJ32uLi8YaZsjZhysW', '2026-04-24 22:00:25'),
(2, 'user2', 'user2@gmail.com', '1234567891', 'cs2002', 'CSE', '$2y$10$8SBsMKZFVp5dMAx80gbfPe2Zrk47miNhzN5xl6XVBbjWQK.z2dO.y', '2026-04-24 22:07:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `claimed_by` (`claimed_by`),
  ADD KEY `claim_pending` (`claim_pending`);

--
-- Indexes for table `lost_notices`
--
ALTER TABLE `lost_notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lost_notices_status_created` (`status`,`created_at`),
  ADD KEY `idx_lost_notices_category` (`category`),
  ADD KEY `fk_lost_notices_user` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lost_notices`
--
ALTER TABLE `lost_notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`claimed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_ibfk_3` FOREIGN KEY (`claim_pending`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lost_notices`
--
ALTER TABLE `lost_notices`
  ADD CONSTRAINT `fk_lost_notices_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

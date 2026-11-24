-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 03:08 AM
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
-- Database: `rbim`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `username` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `username`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(28, 0, 'superadmin', 'login', 'Successful sign-in', '127.0.0.1', 'cli-test', '2026-07-29 14:30:41'),
(29, 0, 'superadmin', 'logout', 'Signed out', '127.0.0.1', 'cli-test', '2026-07-29 14:30:41'),
(30, 0, 'atipuluan', 'login', 'Successful sign-in', '127.0.0.1', 'cli-test', '2026-07-29 14:30:41'),
(31, 0, 'atipuluan', 'logout', 'Signed out', '127.0.0.1', 'cli-test', '2026-07-29 14:30:41'),
(32, 0, 'superadmin', 'login_failed', 'Invalid username or password', '127.0.0.1', 'cli-test', '2026-07-29 14:30:42'),
(33, 0, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-29 14:34:02'),
(34, 0, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-30 11:07:01'),
(35, 0, 'bagroy', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-30 11:07:07'),
(36, 0, 'bagroy', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-30 11:07:16'),
(37, 0, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-30 11:07:21'),
(38, 0, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-30 11:07:22'),
(39, 0, 'taloc', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 13:32:06'),
(40, 0, 'taloc', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 13:42:02'),
(41, 0, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 13:42:07'),
(42, 0, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 13:47:06'),
(43, 0, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 13:47:17'),
(44, 0, 'superadmin', 'qat_reset', 'Reset question assessment tool to census form defaults', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 14:12:15'),
(45, 0, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:24:47'),
(46, 0, 'taloc', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:24:52'),
(47, 2, 'Balolong', 'login', 'Successful sign-in', '0.0.0.0', 'unknown', '2026-08-03 16:28:43'),
(48, 43, 'Catamboan (Lazi)', 'login', 'Successful sign-in', '0.0.0.0', 'unknown', '2026-08-03 16:28:43'),
(49, 1, 'superadmin', 'login', 'Successful sign-in', '0.0.0.0', 'unknown', '2026-08-03 16:28:43'),
(50, 35, 'taloc', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:29:01'),
(51, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:29:07'),
(52, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:29:12'),
(53, 0, 'larena', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:29:23'),
(54, 53, 'Tigbawan', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:30:19'),
(55, 53, 'Tigbawan', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:34:10'),
(56, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:34:15'),
(57, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:34:59'),
(58, 94, 'Banban', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-03 16:35:10'),
(59, 94, 'Banban', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 08:44:10'),
(60, 0, 'admin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 08:44:20'),
(61, 0, 'admin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 08:44:26'),
(62, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 08:44:31'),
(63, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 08:45:24'),
(64, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:24:41'),
(65, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:49:05'),
(66, 0, 'mualban', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:49:13'),
(67, 0, 'Larena', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:49:30'),
(68, 0, 'Larena', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:49:35'),
(69, 123, 'Pili', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:50:23'),
(70, 123, 'Pili', 'add_household', 'Household #260801 in Pili', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 15:53:33'),
(71, 123, 'Pili', 'add_household', 'Household #260802 in Pili', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-06 16:23:43'),
(72, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 18:07:25'),
(73, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 18:07:25'),
(74, 123, 'Pili', 'register_member', 'Member “Liza Torres” for household H260802', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 18:46:16'),
(75, 123, 'Pili', 'register_member', 'Member “Liza Reyes” for household H260802', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 18:46:22'),
(76, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 21:34:20'),
(77, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 21:34:51'),
(78, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 21:34:54'),
(79, 94, 'Banban', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 21:35:06'),
(80, 94, 'Banban', 'add_household', 'Household #260803 in Banban', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-09 21:40:06'),
(81, 94, 'Banban', 'register_member', 'Member “Rosa Torres” for household H260803', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 09:09:48'),
(82, 94, 'Banban', 'register_member', 'Member “Pedro Flores” for household H260803', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 09:09:56'),
(83, 123, 'Pili', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 09:11:33'),
(84, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 09:11:39'),
(85, 94, 'Banban', 'register_member', 'Member “Rosa Cruz” for household H260803', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 09:12:07'),
(86, 94, 'Banban', 'add_household', 'Household #260804 in Banban', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 11:41:59'),
(87, 94, 'Banban', 'register_member', 'Member “Jose Santos” for household H260804', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-10 11:42:54'),
(88, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 21:47:31'),
(89, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 21:47:34'),
(90, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 08:37:01'),
(91, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 09:44:43'),
(92, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 09:44:48'),
(93, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 09:44:48'),
(94, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 09:44:53'),
(95, 94, 'Banban', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 09:45:03'),
(96, 94, 'Banban', 'add_household', 'Household #260805 in Banban', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 09:45:34'),
(97, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 10:48:24'),
(98, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:28:22'),
(99, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:28:26'),
(100, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:28:31'),
(101, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:30:25'),
(102, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:30:34'),
(103, 0, 'siquijor', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:30:42'),
(104, 0, 'siquijor', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:30:42'),
(105, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 11:30:56'),
(106, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 13:10:59'),
(107, 0, 'lazi', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 13:11:03'),
(108, 138, 'Lazi', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-20 13:17:30'),
(109, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 13:15:50'),
(110, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 13:36:48'),
(111, 138, 'Lazi', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 13:36:56'),
(112, 138, 'Lazi', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 13:37:32'),
(113, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 13:37:39'),
(114, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 14:52:14'),
(115, 138, 'Lazi', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 14:52:20'),
(116, 94, 'Banban', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 14:52:45'),
(117, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-01 14:52:53'),
(118, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 12:34:44'),
(119, 138, 'Lazi', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 12:34:49'),
(120, 138, 'Lazi', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 12:35:21'),
(121, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 12:35:28'),
(122, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 12:35:31'),
(123, 138, 'Lazi', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 14:03:32'),
(124, 139, 'Maria', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-02 14:03:44'),
(125, 0, 'laz', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-02 14:06:39'),
(126, 138, 'Lazi', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', '2026-09-02 14:06:46'),
(127, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 16:12:34'),
(128, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 16:12:34'),
(129, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-07 16:12:43'),
(130, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-09 14:24:57'),
(131, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-15 16:47:50'),
(132, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', '2026-09-16 10:30:39'),
(133, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-07 20:28:42'),
(134, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-07 20:28:46'),
(135, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-16 16:10:09'),
(136, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-16 16:10:14'),
(137, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-16 16:10:16'),
(138, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-18 13:15:31'),
(139, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-18 13:15:31'),
(140, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-18 13:15:37'),
(141, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-18 13:15:37'),
(142, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-18 13:15:47'),
(143, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-19 13:51:06'),
(144, 1, 'superadmin', 'login_failed', 'Invalid username or password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-24 09:26:56'),
(145, 1, 'superadmin', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-24 09:27:01'),
(146, 1, 'superadmin', 'logout', 'Signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-24 09:47:51'),
(147, 2, 'Balolong', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-24 09:47:55'),
(148, 2, 'Balolong', 'login', 'Successful sign-in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36', '2026-11-24 09:47:55');

-- --------------------------------------------------------

--
-- Table structure for table `barangay`
--

CREATE TABLE `barangay` (
  `barangay_id` int(11) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city_municipality_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay`
--

INSERT INTO `barangay` (`barangay_id`, `barangay`, `city_municipality_id`) VALUES
(1, 'Balolong', 1),
(2, 'Bino-ongan', 1),
(3, 'Bitaug', 1),
(4, 'Bolot', 1),
(5, 'Camogao', 1),
(6, 'Cangmangki', 1),
(7, 'Libo', 1),
(8, 'Lomangcapan', 1),
(9, 'Lotloton', 1),
(10, 'Manan-ao', 1),
(11, 'Olave', 1),
(12, 'Parian', 1),
(13, 'Poblacion', 1),
(14, 'Tulapos', 1),
(15, 'Bagacay', 2),
(16, 'Balolang', 2),
(17, 'Basac', 2),
(18, 'Bintangan', 2),
(19, 'Bontod', 2),
(20, 'Cabulihan', 2),
(21, 'Calunasan', 2),
(22, 'Candigum', 2),
(23, 'Cang-allas', 2),
(24, 'Cang-apa', 2),
(25, 'Cangbagsa', 2),
(26, 'Cangmalalag', 2),
(27, 'Canlambo', 2),
(28, 'Canlasog', 2),
(29, 'Catamboan', 2),
(30, 'Helen', 2),
(31, 'Nonoc', 2),
(32, 'North Poblacion', 2),
(33, 'Ponong', 2),
(34, 'Sabang', 2),
(35, 'Sandugan', 2),
(36, 'South Poblacion', 2),
(37, 'Taculing', 2),
(38, 'Campalanas', 3),
(39, 'Cangclaran', 3),
(40, 'Cangomantong', 3),
(41, 'Capalasanan', 3),
(42, 'Catamboan', 3),
(43, 'Gabayan', 3),
(44, 'Kimba', 3),
(45, 'Kinamandagan', 3),
(46, 'Lower Cabangcalan', 3),
(47, 'Nagerong', 3),
(48, 'Po-o', 3),
(49, 'Simacolong', 3),
(50, 'Tagmanocan', 3),
(51, 'Talayong', 3),
(52, 'Tigbawan', 3),
(53, 'Tignao', 3),
(54, 'Upper Cabangcalan', 3),
(55, 'Ytaya', 3),
(56, 'Bogo', 4),
(57, 'Bonga', 4),
(58, 'Cabal-asan', 4),
(59, 'Calunasan', 4),
(60, 'Candaping A', 4),
(61, 'Candaping B', 4),
(62, 'Cantaroc A', 4),
(63, 'Cantaroc B', 4),
(64, 'Cantugbas', 4),
(65, 'Lico-an', 4),
(66, 'Lilo-an', 4),
(67, 'Logucan', 4),
(68, 'Looc', 4),
(69, 'Minalulan', 4),
(70, 'Nabutay', 4),
(71, 'Olang', 4),
(72, 'Pisong A', 4),
(73, 'Pisong B', 4),
(74, 'Poblacion Norte', 4),
(75, 'Poblacion Sur', 4),
(76, 'Saguing', 4),
(77, 'Sawang', 4),
(78, 'Canasagan', 5),
(79, 'Candura', 5),
(80, 'Cangmunag', 5),
(81, 'Cansayang', 5),
(82, 'Catulayan', 5),
(83, 'Lala-o', 5),
(84, 'Maite', 5),
(85, 'Napo', 5),
(86, 'Paliton', 5),
(87, 'Poblacion', 5),
(88, 'Solangon', 5),
(89, 'Tag-ibo', 5),
(90, 'Tambisan', 5),
(91, 'Timbaon', 5),
(92, 'Tubod', 5),
(93, 'Banban', 6),
(94, 'Bolos', 6),
(95, 'Caipilan', 6),
(96, 'Caitican', 6),
(97, 'Calalinan', 6),
(98, 'Canal', 6),
(99, 'Candanay Norte', 6),
(100, 'Candanay Sur', 6),
(101, 'Cang-adieng', 6),
(102, 'Cang-agong', 6),
(103, 'Cang-alwang', 6),
(104, 'Cang-asa', 6),
(105, 'Cang-atuyom', 6),
(106, 'Cang-inte', 6),
(107, 'Cang-isad', 6),
(108, 'Canghunoghunog', 6),
(109, 'Cangmatnog', 6),
(110, 'Cangmohao', 6),
(111, 'Cantabon', 6),
(112, 'Caticugan', 6),
(113, 'Dumanhog', 6),
(114, 'Ibabao', 6),
(115, 'Lambojon', 6),
(116, 'Luyang', 6),
(117, 'Luzong', 6),
(118, 'Olo', 6),
(119, 'Pangi', 6),
(120, 'Panlautan', 6),
(121, 'Pasihagon', 6),
(122, 'Pili', 6),
(123, 'Poblacion', 6),
(124, 'Polangyuta', 6),
(125, 'Ponong', 6),
(126, 'Sabang', 6),
(127, 'San Antonio', 6),
(128, 'Songculan', 6),
(129, 'Tacdog', 6),
(130, 'Tacloban', 6),
(131, 'Tambisan', 6),
(132, 'Tebjong', 6),
(133, 'Tinago', 6),
(134, 'Tongo', 6);

-- --------------------------------------------------------

--
-- Table structure for table `city_municipality`
--

CREATE TABLE `city_municipality` (
  `city_municipality_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL DEFAULT 'Siquijor',
  `type` enum('city','municipality') NOT NULL DEFAULT 'municipality'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `city_municipality`
--

INSERT INTO `city_municipality` (`city_municipality_id`, `name`, `province`, `type`) VALUES
(1, 'Enrique Villanueva', 'Siquijor', 'municipality'),
(2, 'Larena', 'Siquijor', 'municipality'),
(3, 'Lazi', 'Siquijor', 'municipality'),
(4, 'Maria', 'Siquijor', 'municipality'),
(5, 'San Juan', 'Siquijor', 'municipality'),
(6, 'Siquijor', 'Siquijor', 'municipality');

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `total_members` int(11) NOT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`id`, `barangay_id`, `total_members`, `status`, `created_at`) VALUES
(250906, 122, 4, 'Completed', '2026-08-06 07:53:33'),
(250907, 122, 2, 'Completed', '2026-08-06 08:23:43'),
(250908, 93, 0, 'Pending', '2026-08-09 13:40:06'),
(250909, 93, 0, 'Completed', '2026-08-10 03:41:59'),
(250910, 93, 0, 'Completed', '2026-08-18 01:45:34');

-- --------------------------------------------------------

--
-- Table structure for table `register_members`
--

CREATE TABLE `register_members` (
  `member_id` bigint(20) NOT NULL,
  `household_number` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `name` text DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `sex` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(150) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `ethnicity` varchar(100) DEFAULT NULL,
  `highest_education` varchar(100) DEFAULT NULL,
  `currently_enrolled` varchar(50) DEFAULT NULL,
  `school_level` varchar(50) DEFAULT NULL,
  `place_of_school` varchar(150) DEFAULT NULL,
  `q15_monthly_income` decimal(12,2) DEFAULT NULL,
  `q15a_source_of_income` varchar(100) DEFAULT NULL,
  `q15b_occupation` varchar(150) DEFAULT NULL,
  `q17_work_status` varchar(50) DEFAULT NULL,
  `q18_place_of_work` varchar(150) DEFAULT NULL,
  `q19_place_of_delivery` varchar(150) DEFAULT NULL,
  `q20_birth_attendant` varchar(100) DEFAULT NULL,
  `q21_immunization` varchar(100) DEFAULT NULL,
  `q22_living_children` varchar(50) DEFAULT NULL,
  `q23_fp_method` varchar(100) DEFAULT NULL,
  `q24_source_of_fp` varchar(100) DEFAULT NULL,
  `q25_intention_to_use_fp` varchar(100) DEFAULT NULL,
  `q26_health_insurance` varchar(100) DEFAULT NULL,
  `q27_facility_visited` varchar(150) DEFAULT NULL,
  `q28_reason_for_visit` varchar(150) DEFAULT NULL,
  `q29_disability` varchar(150) DEFAULT NULL,
  `q30_solo_parent_status` varchar(50) DEFAULT NULL,
  `q31_registered_senior` varchar(50) DEFAULT NULL,
  `q32_registered_voter` varchar(150) DEFAULT NULL,
  `q33_previous_residence_5yrs` varchar(150) DEFAULT NULL,
  `q34_previous_residence_6mos` varchar(150) DEFAULT NULL,
  `q35_length_of_stay` varchar(50) DEFAULT NULL,
  `q36_type_of_resident` varchar(50) DEFAULT NULL,
  `q37_date_of_transfer` varchar(50) DEFAULT NULL,
  `q38_reasons_for_leaving` text DEFAULT NULL,
  `q39_plan_to_return` text DEFAULT NULL,
  `q40_reasons_for_transferring` text DEFAULT NULL,
  `q41_duration_of_stay` varchar(50) DEFAULT NULL,
  `q42a_ctc_possession` varchar(50) DEFAULT NULL,
  `q42b_ctc_issued_in_barangay` varchar(50) DEFAULT NULL,
  `q43_skills_training_interest` text DEFAULT NULL,
  `q44_skills` text DEFAULT NULL,
  `q45_housing_tenure` varchar(50) DEFAULT NULL,
  `q46_lot_tenure` varchar(50) DEFAULT NULL,
  `q47_fuel_for_lighting` varchar(50) DEFAULT NULL,
  `q48_fuel_for_cooking` varchar(50) DEFAULT NULL,
  `q49_drinking_water_source` varchar(100) DEFAULT NULL,
  `q50a_garbage_disposal_method` varchar(100) DEFAULT NULL,
  `q50b_garbage_segregation` varchar(50) DEFAULT NULL,
  `q51_toilet_facility` varchar(50) DEFAULT NULL,
  `q52_building_type` varchar(50) DEFAULT NULL,
  `q53_outer_wall_material` varchar(100) DEFAULT NULL,
  `q54_female_death_details` text DEFAULT NULL,
  `q55_child_death_details` text DEFAULT NULL,
  `q56_common_diseases` text DEFAULT NULL,
  `q57_primary_needs` text DEFAULT NULL,
  `q58_future_residence` text DEFAULT NULL,
  `q26a_nutritional_status` varchar(150) DEFAULT NULL,
  `q59_hunger_experience` varchar(50) DEFAULT NULL,
  `q60_cannot_read` varchar(50) DEFAULT NULL,
  `q61_evacuation_center` varchar(50) DEFAULT NULL,
  `q62_emergency_kit` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `register_members`
--

INSERT INTO `register_members` (`member_id`, `household_number`, `session_id`, `name`, `relationship`, `sex`, `age`, `date_of_birth`, `place_of_birth`, `nationality`, `marital_status`, `religion`, `ethnicity`, `highest_education`, `currently_enrolled`, `school_level`, `place_of_school`, `q15_monthly_income`, `q15a_source_of_income`, `q15b_occupation`, `q17_work_status`, `q18_place_of_work`, `q19_place_of_delivery`, `q20_birth_attendant`, `q21_immunization`, `q22_living_children`, `q23_fp_method`, `q24_source_of_fp`, `q25_intention_to_use_fp`, `q26_health_insurance`, `q27_facility_visited`, `q28_reason_for_visit`, `q29_disability`, `q30_solo_parent_status`, `q31_registered_senior`, `q32_registered_voter`, `q33_previous_residence_5yrs`, `q34_previous_residence_6mos`, `q35_length_of_stay`, `q36_type_of_resident`, `q37_date_of_transfer`, `q38_reasons_for_leaving`, `q39_plan_to_return`, `q40_reasons_for_transferring`, `q41_duration_of_stay`, `q42a_ctc_possession`, `q42b_ctc_issued_in_barangay`, `q43_skills_training_interest`, `q44_skills`, `q45_housing_tenure`, `q46_lot_tenure`, `q47_fuel_for_lighting`, `q48_fuel_for_cooking`, `q49_drinking_water_source`, `q50a_garbage_disposal_method`, `q50b_garbage_segregation`, `q51_toilet_facility`, `q52_building_type`, `q53_outer_wall_material`, `q54_female_death_details`, `q55_child_death_details`, `q56_common_diseases`, `q57_primary_needs`, `q58_future_residence`, `q26a_nutritional_status`, `q59_hunger_experience`, `q60_cannot_read`, `q61_evacuation_center`, `q62_emergency_kit`) VALUES
(8, 260802, 3, 'Liza Torres', 'Son', 2, 58, '1967-09-17', 'Davao City', 'Filipino', 'Widowed', 'Iglesia ni Cristo', 'Ilocano', 'Elementary level', 'No', NULL, NULL, 40.00, 'Employment', 'Farmer', 'Contractual Work', 'Baguio City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PhilHealth paying member', 'RHU/Health center', 'Gave birth', 'Sensory', 'No', NULL, 'No', 'Santa Cruz, Davao City', 'San Miguel, Baguio City', '6 years', 'Migrant (Length of Stay is between 6 months and 1 ', '01/2020', 'Family', 'No', 'Family', '31 months', 'Yes', 'No', 'None', 'Sewing', 'Rented', 'Rented', 'Electricity', 'Electricity', 'Peddler', 'Composting', 'No', 'Others, specify', 'Duplex', 'Makeshift/salvaged/improvised materials', 'None', 'None', 'Hypertension', 'Stable water supply', 'Move to another city/municipality', 'Not assessed', 'Yes', 'Yes', 'Yes', 'Yes'),
(9, 260802, 3, 'Liza Reyes', 'Mother', 2, 14, '2012-07-02', 'Manila', 'Filipino', 'Single', 'Born Again', 'Tagalog', 'High school graduate', 'No', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PhilHealth paying member', 'Private hospital', 'Dental', 'Mental', 'Yes', NULL, NULL, 'Santa Cruz, Davao City', 'San Miguel, Quezon City', '19 years', 'Migrant (Length of Stay is between 6 months and 1 ', NULL, 'Employment', 'Yes', 'Availability of jobs', '22 months', NULL, NULL, NULL, NULL, 'Rent-free without consent of owner', 'Rent-free without consent of owner', 'Kerosene', 'Electricity', 'Own use, tubed/piped deep well', 'Composting', 'Yes', 'Others, specify', 'Multi-unit residential (three units or more)', 'Wood', 'None', 'None', 'Hypertension', 'Livelihood', 'Move within the barangay', 'Overweight', 'No', 'Yes', 'No', 'Yes'),
(10, 260803, 4, 'Rosa Torres', 'Head', 1, 69, '1956-12-26', 'Iloilo City', 'Filipino', 'Unknown', 'Others', 'Bicolano', 'Senior HS graduate', 'No', NULL, NULL, 15.00, 'Business', 'Teacher', 'Contractual Work', 'Davao City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PhilHealth indigent member', 'RHU/Health center', 'Prenatal/Postnatal', 'Physical', 'No', 'Yes', 'Yes', 'Poblacion, Quezon City', 'Poblacion, Davao City', '19 years', 'Migrant (Length of Stay is between 6 months and 1 ', '01/2016', 'Marriage', 'No', 'Family', '41 months', 'No', 'Yes', 'Computer Graphics', 'Driving', 'Rented', 'Rented', 'Kerosene', 'Electricity', 'Own use, faucet community water system', 'Composting', 'Yes', 'Open pit', 'Institutional living quarter (hotel, hospital)', 'Wood', 'None', 'None', 'None', 'Livelihood', 'Move within the barangay', 'Overweight', 'No', 'No', 'No', 'No'),
(11, 260803, 4, 'Pedro Flores', 'Spouse', 1, 60, '1965-09-22', 'Quezon City', 'Filipino', 'Unknown', 'Islam', 'Tagalog', 'Senior HS level', 'No', NULL, NULL, 15.00, 'Others', 'Teacher', 'Permanent Work', 'Quezon City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PhilHealth indigent member', 'Private hospital', 'Others', 'Others', 'No', 'Yes', 'Yes', 'San Miguel, Davao City', 'San Roque, Davao City', '5 years', 'Non-Migrant (Length of Stay is over 5 years)', NULL, NULL, NULL, NULL, NULL, 'Yes', 'No', 'Carpentry', 'Cooking', 'Rented', 'Rented', 'Solar', 'Wood', 'Unprotected spring', 'Burying', 'Yes', 'Water-sealed, sewer septic tank, shared', 'Multi-unit residential (three units or more)', 'Concrete/brick/stone', 'None', 'None', 'Common cold, flu', 'Livelihood', 'Stay in current residence', 'Normal', 'No', 'Yes', 'No', 'No'),
(12, 260803, 4, 'Rosa Cruz', 'Head', 1, 63, '1963-05-24', 'Manila', 'Filipino', 'Single', 'Iglesia ni Cristo', 'Bicolano', 'Senior HS level', 'No', NULL, NULL, 5.00, 'Remittance', 'Office Clerk', 'Permanent Work', 'Baguio City', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PhilHealth dependent of indigent member', 'Government hospital', 'Others', 'Mental', 'No', 'Yes', 'No', 'San Jose, Iloilo City', 'San Jose, Baguio City', '14 years', 'Non-Migrant (Length of Stay is over 5 years)', NULL, NULL, NULL, NULL, NULL, 'Yes', 'Yes', 'None', 'Cooking', 'Rent-free with consent of owner', 'Rented', 'Electricity', 'Wood', 'Own use, tubed/piped deep well', 'Collected by Garbage Truck', 'No', 'Water-sealed, other depository, exclusive', 'Institutional living quarter (hotel, hospital)', 'Asbestos', 'None', 'None', 'Hypertension', 'Health services', 'Move within the barangay', 'Not assessed', 'No', 'Yes', 'No', 'Yes'),
(13, 260804, 5, 'Jose Santos', 'Other relative', 2, 16, '2010-07-11', 'Cebu City', 'Filipino', 'Single', 'Islam', 'Bicolano', 'Elementary graduate', 'No', NULL, NULL, 5.00, 'Remittance', 'Teacher', 'Shared/Partnership Business', 'Iloilo City', NULL, NULL, NULL, '3', 'None', 'Private hospital/clinic', 'Not applicable', 'Others / None', 'Brgy. Health Station', 'Gave birth', 'Mental', 'No', NULL, 'No', 'Poblacion, Quezon City', 'San Miguel, Baguio City', '12 years', 'Migrant (Length of Stay is between 6 months and 1 ', NULL, 'Employment', 'Yes', 'Availability of jobs', '41 months', NULL, NULL, 'Baking', 'Farming', 'Owned/being amortized', 'Rented', 'Electricity', 'LPG', 'Own use, tubed/piped deep well', 'Burying', 'No', 'Water-sealed, sewer septic tank, exclusive', 'Single house', 'Makeshift/salvaged/improvised materials', 'None', 'None', 'Hypertension', 'Road improvement', 'Move to another city/municipality', 'Normal', 'Yes', 'No', 'Yes', 'Yes');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 14, '0cc4cb26df198e766865cca7af0ffda9281f63de55b3f7305dda44f77694a761', '2025-10-04 15:41:56', '2025-09-04 13:41:56'),
(2, 13, 'b0ba887283483b5891e96949910159cd2bb0147ad668b09bfbc3312019bb14fa', '2025-10-04 15:42:43', '2025-09-04 13:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `survey_session`
--

CREATE TABLE `survey_session` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `household_number` int(50) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city_municipality` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `respondent_name` varchar(150) DEFAULT NULL,
  `household_head` varchar(150) NOT NULL,
  `total_members` int(11) DEFAULT NULL,
  `visit_number` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `result` varchar(20) DEFAULT NULL,
  `next_visit_date` date DEFAULT NULL,
  `interviewer_name` varchar(150) DEFAULT NULL,
  `supervisor_name` varchar(150) DEFAULT NULL,
  `date_encoded` date DEFAULT NULL,
  `encoder_name` varchar(150) DEFAULT NULL,
  `encoding_supervisor` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `survey_session`
--

INSERT INTO `survey_session` (`session_id`, `household_number`, `province`, `city_municipality`, `barangay`, `barangay_id`, `address`, `respondent_name`, `household_head`, `total_members`, `visit_number`, `visit_date`, `start_time`, `end_time`, `result`, `next_visit_date`, `interviewer_name`, `supervisor_name`, `date_encoded`, `encoder_name`, `encoding_supervisor`) VALUES
(2, 260801, 'Siquijor', 'Siquijor', 'Pili', 122, '881 Rizal St., Pili', 'Elena Reyes', 'Jose Reyes', 0, 3, '2026-08-01', '09:19:00', '12:28:00', 'Complete', NULL, 'Rosa Torres', 'Carlos Ramos', '2026-08-06', 'Pili', 'Rosa Gonzales'),
(3, 260802, 'Siquijor', 'Siquijor', 'Pili', 122, '213 Rizal St., Pili', 'Sofia Santos', 'Rosa Mendoza', 2, 2, '2026-07-23', '09:02:00', '11:16:00', 'Complete', NULL, 'Pedro Mendoza', 'Jose Mendoza', '2026-08-06', 'Pili', 'Maria Torres'),
(4, 260803, 'Siquijor', 'Siquijor', 'Banban', 93, '963 Rizal St., Banban', 'Ana Mendoza', 'Pedro Garcia', 3, 3, '2026-08-03', '10:05:00', '10:58:00', 'Refused', NULL, 'Juan Santos', 'Ana Torres', '2026-08-09', 'Banban', 'Carlos Mendoza'),
(5, 260804, 'Siquijor', 'Siquijor', 'Banban', 93, '462 Rizal St., Banban', 'Sofia Santos', 'Pedro Mendoza', 1, 3, '2026-08-10', '08:41:00', '10:57:00', 'Complete', NULL, 'Carlos Garcia', 'Juan Torres', '2026-08-10', 'Banban', 'Ana Santos'),
(6, 260805, 'Siquijor', 'Siquijor', 'Banban', 93, '345 Rizal St., Banban', 'Maria Reyes', 'Ana Reyes', 0, 3, '2026-08-13', '10:40:00', '11:01:00', 'Complete', NULL, 'Pedro Cruz', 'Sofia Reyes', '2026-08-18', 'Banban', 'Pedro Torres');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `account_status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `barangay` varchar(100) DEFAULT NULL,
  `barangay_id` int(11) DEFAULT NULL,
  `city_municipality_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`admin_id`, `username`, `password`, `role`, `account_status`, `barangay`, `barangay_id`, `city_municipality_id`) VALUES
(1, 'superadmin', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'super_admin', 'active', NULL, NULL, NULL),
(2, 'Balolong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Balolong', 1, NULL),
(3, 'Bino-ongan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bino-ongan', 2, NULL),
(4, 'Bitaug', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bitaug', 3, NULL),
(5, 'Bolot', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bolot', 4, NULL),
(6, 'Camogao', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Camogao', 5, NULL),
(7, 'Cangmangki', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangmangki', 6, NULL),
(8, 'Libo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Libo', 7, NULL),
(9, 'Lomangcapan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lomangcapan', 8, NULL),
(10, 'Lotloton', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lotloton', 9, NULL),
(11, 'Manan-ao', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Manan-ao', 10, NULL),
(12, 'Olave', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Olave', 11, NULL),
(13, 'Parian', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Parian', 12, NULL),
(14, 'Poblacion (Enrique Villanueva)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Poblacion', 13, NULL),
(15, 'Tulapos', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tulapos', 14, NULL),
(16, 'Bagacay', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bagacay', 15, NULL),
(17, 'Balolang', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Balolang', 16, NULL),
(18, 'Basac', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Basac', 17, NULL),
(19, 'Bintangan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bintangan', 18, NULL),
(20, 'Bontod', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bontod', 19, NULL),
(21, 'Cabulihan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cabulihan', 20, NULL),
(22, 'Calunasan (Larena)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Calunasan', 21, NULL),
(23, 'Candigum', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Candigum', 22, NULL),
(24, 'Cang-allas', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-allas', 23, NULL),
(25, 'Cang-apa', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-apa', 24, NULL),
(26, 'Cangbagsa', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangbagsa', 25, NULL),
(27, 'Cangmalalag', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangmalalag', 26, NULL),
(28, 'Canlambo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Canlambo', 27, NULL),
(29, 'Canlasog', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Canlasog', 28, NULL),
(30, 'Catamboan (Larena)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Catamboan', 29, NULL),
(31, 'Helen', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Helen', 30, NULL),
(32, 'Nonoc', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Nonoc', 31, NULL),
(33, 'North Poblacion', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'North Poblacion', 32, NULL),
(34, 'Ponong (Larena)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Ponong', 33, NULL),
(35, 'Sabang (Larena)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Sabang', 34, NULL),
(36, 'Sandugan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Sandugan', 35, NULL),
(37, 'South Poblacion', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'South Poblacion', 36, NULL),
(38, 'Taculing', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Taculing', 37, NULL),
(39, 'Campalanas', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Campalanas', 38, NULL),
(40, 'Cangclaran', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangclaran', 39, NULL),
(41, 'Cangomantong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangomantong', 40, NULL),
(42, 'Capalasanan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Capalasanan', 41, NULL),
(43, 'Catamboan (Lazi)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Catamboan', 42, NULL),
(44, 'Gabayan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Gabayan', 43, NULL),
(45, 'Kimba', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Kimba', 44, NULL),
(46, 'Kinamandagan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Kinamandagan', 45, NULL),
(47, 'Lower Cabangcalan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lower Cabangcalan', 46, NULL),
(48, 'Nagerong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Nagerong', 47, NULL),
(49, 'Po-o', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Po-o', 48, NULL),
(50, 'Simacolong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Simacolong', 49, NULL),
(51, 'Tagmanocan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tagmanocan', 50, NULL),
(52, 'Talayong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Talayong', 51, NULL),
(53, 'Tigbawan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tigbawan', 52, NULL),
(54, 'Tignao', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tignao', 53, NULL),
(55, 'Upper Cabangcalan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Upper Cabangcalan', 54, NULL),
(56, 'Ytaya', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Ytaya', 55, NULL),
(57, 'Bogo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bogo', 56, NULL),
(58, 'Bonga', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bonga', 57, NULL),
(59, 'Cabal-asan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cabal-asan', 58, NULL),
(60, 'Calunasan (Maria)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Calunasan', 59, NULL),
(61, 'Candaping A', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Candaping A', 60, NULL),
(62, 'Candaping B', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Candaping B', 61, NULL),
(63, 'Cantaroc A', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cantaroc A', 62, NULL),
(64, 'Cantaroc B', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cantaroc B', 63, NULL),
(65, 'Cantugbas', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cantugbas', 64, NULL),
(66, 'Lico-an', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lico-an', 65, NULL),
(67, 'Lilo-an', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lilo-an', 66, NULL),
(68, 'Logucan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Logucan', 67, NULL),
(69, 'Looc', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Looc', 68, NULL),
(70, 'Minalulan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Minalulan', 69, NULL),
(71, 'Nabutay', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Nabutay', 70, NULL),
(72, 'Olang', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Olang', 71, NULL),
(73, 'Pisong A', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Pisong A', 72, NULL),
(74, 'Pisong B', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Pisong B', 73, NULL),
(75, 'Poblacion Norte', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Poblacion Norte', 74, NULL),
(76, 'Poblacion Sur', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Poblacion Sur', 75, NULL),
(77, 'Saguing', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Saguing', 76, NULL),
(78, 'Sawang', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Sawang', 77, NULL),
(79, 'Canasagan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Canasagan', 78, NULL),
(80, 'Candura', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Candura', 79, NULL),
(81, 'Cangmunag', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangmunag', 80, NULL),
(82, 'Cansayang', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cansayang', 81, NULL),
(83, 'Catulayan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Catulayan', 82, NULL),
(84, 'Lala-o', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lala-o', 83, NULL),
(85, 'Maite', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Maite', 84, NULL),
(86, 'Napo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Napo', 85, NULL),
(87, 'Paliton', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Paliton', 86, NULL),
(88, 'Poblacion (San Juan)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Poblacion', 87, NULL),
(89, 'Solangon', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Solangon', 88, NULL),
(90, 'Tag-ibo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tag-ibo', 89, NULL),
(91, 'Tambisan (San Juan)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tambisan', 90, NULL),
(92, 'Timbaon', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Timbaon', 91, NULL),
(93, 'Tubod', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tubod', 92, NULL),
(94, 'Banban', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Banban', 93, NULL),
(95, 'Bolos', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Bolos', 94, NULL),
(96, 'Caipilan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Caipilan', 95, NULL),
(97, 'Caitican', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Caitican', 96, NULL),
(98, 'Calalinan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Calalinan', 97, NULL),
(99, 'Canal', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Canal', 98, NULL),
(100, 'Candanay Norte', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Candanay Norte', 99, NULL),
(101, 'Candanay Sur', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Candanay Sur', 100, NULL),
(102, 'Cang-adieng', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-adieng', 101, NULL),
(103, 'Cang-agong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-agong', 102, NULL),
(104, 'Cang-alwang', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-alwang', 103, NULL),
(105, 'Cang-asa', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-asa', 104, NULL),
(106, 'Cang-atuyom', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-atuyom', 105, NULL),
(107, 'Cang-inte', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-inte', 106, NULL),
(108, 'Cang-isad', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cang-isad', 107, NULL),
(109, 'Canghunoghunog', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Canghunoghunog', 108, NULL),
(110, 'Cangmatnog', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangmatnog', 109, NULL),
(111, 'Cangmohao', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cangmohao', 110, NULL),
(112, 'Cantabon', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Cantabon', 111, NULL),
(113, 'Caticugan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Caticugan', 112, NULL),
(114, 'Dumanhog', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Dumanhog', 113, NULL),
(115, 'Ibabao', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Ibabao', 114, NULL),
(116, 'Lambojon', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Lambojon', 115, NULL),
(117, 'Luyang', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Luyang', 116, NULL),
(118, 'Luzong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Luzong', 117, NULL),
(119, 'Olo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Olo', 118, NULL),
(120, 'Pangi', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Pangi', 119, NULL),
(121, 'Panlautan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Panlautan', 120, NULL),
(122, 'Pasihagon', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Pasihagon', 121, NULL),
(123, 'Pili', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Pili', 122, NULL),
(124, 'Poblacion (Siquijor)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Poblacion', 123, NULL),
(125, 'Polangyuta', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Polangyuta', 124, NULL),
(126, 'Ponong (Siquijor)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Ponong', 125, NULL),
(127, 'Sabang (Siquijor)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Sabang', 126, NULL),
(128, 'San Antonio', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'San Antonio', 127, NULL),
(129, 'Songculan', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Songculan', 128, NULL),
(130, 'Tacdog', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tacdog', 129, NULL),
(131, 'Tacloban', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tacloban', 130, NULL),
(132, 'Tambisan (Siquijor)', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tambisan', 131, NULL),
(133, 'Tebjong', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tebjong', 132, NULL),
(134, 'Tinago', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tinago', 133, NULL),
(135, 'Tongo', '$2y$10$PAbY2k5i2y2PdeJyxxSTNuLdOiYV6d/mqI1IWd8csvSJhlr0GIGOy', 'admin', 'active', 'Tongo', 134, NULL),
(136, 'Enrique Villanueva', '$2y$10$jlzE1YzD9Mlvnza3iaWbsO8ChohOEnXISFWDZWMh9aGBdwLkhhcg.', 'muni_admin', 'active', NULL, NULL, 1),
(137, 'Larena', '$2y$10$jlzE1YzD9Mlvnza3iaWbsO8ChohOEnXISFWDZWMh9aGBdwLkhhcg.', 'muni_admin', 'active', NULL, NULL, 2),
(138, 'Lazi', '$2y$10$jlzE1YzD9Mlvnza3iaWbsO8ChohOEnXISFWDZWMh9aGBdwLkhhcg.', 'muni_admin', 'active', NULL, NULL, 3),
(139, 'Maria', '$2y$10$jlzE1YzD9Mlvnza3iaWbsO8ChohOEnXISFWDZWMh9aGBdwLkhhcg.', 'muni_admin', 'active', NULL, NULL, 4),
(140, 'San Juan', '$2y$10$jlzE1YzD9Mlvnza3iaWbsO8ChohOEnXISFWDZWMh9aGBdwLkhhcg.', 'muni_admin', 'active', NULL, NULL, 5),
(141, 'Siquijor', '$2y$10$jlzE1YzD9Mlvnza3iaWbsO8ChohOEnXISFWDZWMh9aGBdwLkhhcg.', 'muni_admin', 'active', NULL, NULL, 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `barangay`
--
ALTER TABLE `barangay`
  ADD PRIMARY KEY (`barangay_id`),
  ADD KEY `fk_barangay_city_municipality` (`city_municipality_id`);

--
-- Indexes for table `city_municipality`
--
ALTER TABLE `city_municipality`
  ADD PRIMARY KEY (`city_municipality_id`),
  ADD UNIQUE KEY `uq_city_municipality_name` (`name`,`province`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `register_members`
--
ALTER TABLE `register_members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `survey_session`
--
ALTER TABLE `survey_session`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `barangay_id` (`barangay_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `fk_users_barangay` (`barangay_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `barangay`
--
ALTER TABLE `barangay`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `city_municipality`
--
ALTER TABLE `city_municipality`
  MODIFY `city_municipality_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=250911;

--
-- AUTO_INCREMENT for table `register_members`
--
ALTER TABLE `register_members`
  MODIFY `member_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `survey_session`
--
ALTER TABLE `survey_session`
  MODIFY `session_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barangay`
--
ALTER TABLE `barangay`
  ADD CONSTRAINT `fk_barangay_city_municipality` FOREIGN KEY (`city_municipality_id`) REFERENCES `city_municipality` (`city_municipality_id`) ON UPDATE CASCADE;

--
-- Constraints for table `households`
--
ALTER TABLE `households`
  ADD CONSTRAINT `households_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`);

--
-- Constraints for table `survey_session`
--
ALTER TABLE `survey_session`
  ADD CONSTRAINT `survey_session_ibfk_1` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangay` (`barangay_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

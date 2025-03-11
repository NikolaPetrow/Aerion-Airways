-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2025 at 08:38 PM
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
-- Database: `airways_system_data`
--

-- --------------------------------------------------------

--
-- Table structure for table `airports`
--

CREATE TABLE `airports` (
  `id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `airport_code` varchar(3) NOT NULL,
  `country` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airports`
--

INSERT INTO `airports` (`id`, `city`, `airport_code`, `country`) VALUES
(1, 'Sofia', 'SOF', 'Bulgaria'),
(2, 'Varna', 'VAR', 'Bulgaria'),
(3, 'Burgas', 'BOJ', 'Bulgaria'),
(4, 'Plovdiv', 'PDV', 'Bulgaria'),
(5, 'Belgrade', 'BEG', 'Serbia'),
(6, 'Bucharest', 'OTP', 'Romania'),
(7, 'Cluj-Napoca', 'CLJ', 'Romania'),
(8, 'Athens', 'ATH', 'Greece'),
(9, 'Thessaloniki', 'SKG', 'Greece'),
(10, 'Istanbul', 'IST', 'Turkey'),
(11, 'Antalya', 'AYT', 'Turkey'),
(12, 'Vienna', 'VIE', 'Austria'),
(13, 'Prague', 'PRG', 'Czech Republic'),
(14, 'Warsaw', 'WAW', 'Poland'),
(15, 'Krakow', 'KRK', 'Poland'),
(16, 'Budapest', 'BUD', 'Hungary'),
(17, 'Bratislava', 'BTS', 'Slovakia'),
(18, 'Zagreb', 'ZAG', 'Croatia'),
(19, 'Ljubljana', 'LJU', 'Slovenia'),
(20, 'London', 'LHR', 'United Kingdom'),
(21, 'Manchester', 'MAN', 'United Kingdom'),
(22, 'Edinburgh', 'EDI', 'United Kingdom'),
(23, 'Dublin', 'DUB', 'Ireland'),
(24, 'Paris', 'CDG', 'France'),
(25, 'Nice', 'NCE', 'France'),
(26, 'Lyon', 'LYS', 'France'),
(27, 'Amsterdam', 'AMS', 'Netherlands'),
(28, 'Brussels', 'BRU', 'Belgium'),
(29, 'Luxembourg', 'LUX', 'Luxembourg'),
(30, 'Rome', 'FCO', 'Italy'),
(31, 'Milan', 'MXP', 'Italy'),
(32, 'Venice', 'VCE', 'Italy'),
(33, 'Naples', 'NAP', 'Italy'),
(34, 'Madrid', 'MAD', 'Spain'),
(35, 'Barcelona', 'BCN', 'Spain'),
(36, 'Valencia', 'VLC', 'Spain'),
(37, 'Malaga', 'AGP', 'Spain'),
(38, 'Lisbon', 'LIS', 'Portugal'),
(39, 'Porto', 'OPO', 'Portugal'),
(40, 'Stockholm', 'ARN', 'Sweden'),
(41, 'Copenhagen', 'CPH', 'Denmark'),
(42, 'Oslo', 'OSL', 'Norway'),
(43, 'Helsinki', 'HEL', 'Finland'),
(44, 'Riga', 'RIX', 'Latvia'),
(45, 'Tallinn', 'TLL', 'Estonia'),
(46, 'Berlin', 'BER', 'Germany'),
(47, 'Munich', 'MUC', 'Germany'),
(48, 'Frankfurt', 'FRA', 'Germany'),
(49, 'Hamburg', 'HAM', 'Germany'),
(50, 'Zurich', 'ZRH', 'Switzerland'),
(51, 'Geneva', 'GVA', 'Switzerland'),
(52, 'Tel Aviv', 'TLV', 'Israel'),
(53, 'Dubai', 'DXB', 'United Arab Emirates'),
(54, 'Cairo', 'CAI', 'Egypt'),
(55, 'Tbilisi', 'TBS', 'Georgia'),
(56, 'Baku', 'GYD', 'Azerbaijan');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `num_passengers` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `flight_id`, `booking_date`, `num_passengers`, `total_price`, `status`) VALUES
(14, 2, 5, '2025-03-10 17:00:36', 1, 352.00, 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `check_ins`
--

CREATE TABLE `check_ins` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `seat_number` varchar(4) DEFAULT NULL,
  `checked_in_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `has_paid_seat` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `flight_number` varchar(10) NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `arrival_city` varchar(100) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `available_seats` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `flight_number`, `departure_city`, `arrival_city`, `departure_time`, `arrival_time`, `price`, `available_seats`) VALUES
(1, 'AA1234', 'Sofia', 'London', '2025-03-20 10:00:00', '2025-03-20 12:00:00', 200.00, 150),
(2, 'AA1235', 'London', 'Sofia', '2025-03-20 14:00:00', '2025-03-20 16:00:00', 220.00, 150),
(3, 'AA1236', 'Sofia', 'Paris', '2025-03-21 09:00:00', '2025-03-21 11:30:00', 180.00, 150),
(4, 'AA1237', 'Paris', 'Sofia', '2025-03-21 13:00:00', '2025-03-21 15:30:00', 190.00, 150),
(5, 'AA1398', 'London', 'Dubai', '2025-03-11 17:40:00', '2025-03-11 22:40:00', 170.00, 200),
(6, 'AA1398', 'London', 'Dubai', '2025-03-11 17:40:00', '2025-03-11 22:40:00', 170.00, 200);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_role` enum('User','Admin') DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `created_at`, `user_role`) VALUES
(1, 'Admin', 'Profile', 'admin@email.com', '$2y$10$/J0HuOrl62NtM8DlakZYmu.DbOhHenXWElWdtSMLD6BffofIggJtW', '2025-03-10 15:23:42', 'Admin'),
(2, 'Nikola', 'Petrov', 'nikolapetrow06@gmail.com', '$2y$10$tx3JXwrwjzblNdVi6htRVOwtA0vc7HDEcWh41uic8zzR7sKGuaDPK', '2025-03-10 15:55:16', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `flight_id` (`flight_id`);

--
-- Indexes for table `check_ins`
--
ALTER TABLE `check_ins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `airports`
--
ALTER TABLE `airports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `check_ins`
--
ALTER TABLE `check_ins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`);

--
-- Constraints for table `check_ins`
--
ALTER TABLE `check_ins`
  ADD CONSTRAINT `check_ins_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

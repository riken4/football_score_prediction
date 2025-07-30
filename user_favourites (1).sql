-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 10:19 AM
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
-- Database: `football_predict`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_favourites`
--

CREATE TABLE `user_favourites` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `season` varchar(10) NOT NULL DEFAULT '2012-13',
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favourites`
--

INSERT INTO `user_favourites` (`id`, `username`, `team_name`, `season`, `added_date`) VALUES
(6, 'cc', 'Aston Villa', '2014-15', '2025-05-29 05:20:56'),
(8, 'cc', 'Arsenal', '2015-16', '2025-05-29 05:22:26'),
(9, 'cc', 'Man City', '2012-13', '2025-05-29 05:34:06'),
(10, 'cc', 'Crystal Palace', '2013-14', '2025-05-29 05:34:33'),
(11, 'cc', 'Chelsea', '2014-15', '2025-05-29 05:34:49'),
(12, 'qq', 'Chelsea', '2012-13', '2025-05-29 05:38:22'),
(13, 'qq', 'Aston Villa', '2015-16', '2025-05-29 05:47:55'),
(14, 'qq', 'Arsenal', '2012-13', '2025-05-29 08:09:37'),
(15, 'qq', 'Aston Villa', '2014-15', '2025-05-29 08:17:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_favourites`
--
ALTER TABLE `user_favourites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_team_season` (`username`,`team_name`,`season`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_team_name` (`team_name`),
  ADD KEY `idx_season` (`season`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_favourites`
--
ALTER TABLE `user_favourites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

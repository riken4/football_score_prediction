-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2024 at 04:31 PM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `UserName` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `UserName`, `Password`) VALUES
(3, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997');

-- --------------------------------------------------------

--
-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `UserName` varchar(255) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `Number` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `num_likes` int(11) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `UserName`, `fullName`, `Number`, `Email`, `Address`, `Gender`, `profile_picture`, `num_likes`, `password`) VALUES
(61, 'riken', 'riken', '9841193010', 'rikenmaharjan4@gmail.com', 'pulchowk', '', 'Kendrick Lamar - DAMN_ (2017).jfif', 0, '37ecee98ba5775b657cf4014aaa251ffc50fcf7a'),
(62, 'ram', 'ram maharjan', '9841142530', 'ram@gmail.com', 'patan', '', 'Jujutsu Kaisen Sticker _ Jujutsu-kaisen.jfif', 0, '77c7960e890deddebb7ff2e55e340d2ed1708368'),
(64, 'w', 'w', '9841193010', 'w@gmail.com', 'w', '', 'Nike Stickers for Sale.jfif', 0, 'aff024fe4ab0fece4091de044c58c9ae4233383a'),
(65, 'rojen', 'rojen shakya', '9866447837', 'rojen@gmail.com', 'lagankhel', '', 'WIN_20240318_10_23_05_Pro.jpg', 0, '94f6c2741f5ff9c2bac6b012368c4ecb31c0d99e'),
(66, 'sss', 'ss', '9841193010', 'rikenarjan4@gmail.com', 'wsfafdhgashghgwagdh', '', 'sato tower.bmp', 0, 'bf9661defa3daecacfde5bde0214c4a439351d4d');

-- --------------------------------------------------------

--

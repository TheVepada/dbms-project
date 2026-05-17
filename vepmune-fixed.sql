-- Safe import wrapper for VepMune
-- Drops existing tables (including plural leftovers), disables FK checks,
-- then recreates schema and data from the cleaned dump.

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `playlist_songs`;
DROP TABLE IF EXISTS `playlists`;
DROP TABLE IF EXISTS `albums`;
DROP TABLE IF EXISTS `artists`;
DROP TABLE IF EXISTS `songs`;
DROP TABLE IF EXISTS `users`;

DROP TABLE IF EXISTS `playlistsong`;
DROP TABLE IF EXISTS `streaminglog`;
DROP TABLE IF EXISTS `subscription`;
DROP TABLE IF EXISTS `payment`;
DROP TABLE IF EXISTS `song`;
DROP TABLE IF EXISTS `album`;
DROP TABLE IF EXISTS `artist`;
DROP TABLE IF EXISTS `playlist`;
DROP TABLE IF EXISTS `user`;

SET FOREIGN_KEY_CHECKS=1;

-- Now include the cleaned dump below

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 02:15 AM
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
-- Database: `vepmune`
--

-- --------------------------------------------------------

--
-- Table structure for table `album`
--

CREATE TABLE `album` (
  `AlbumID` int(11) NOT NULL,
  `ArtistID` int(11) DEFAULT NULL,
  `Title` varchar(100) DEFAULT NULL,
  `ReleaseDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `album`
--

INSERT INTO `album` (`AlbumID`, `ArtistID`, `Title`, `ReleaseDate`) VALUES
(1, 1, 'My Dear Melancholy,', '2018-03-30'),
(2, 2, 'Currents', '2015-07-17'),
(3, 3, 'Being Funny in a Foreign Language', '2022-10-14'),
(4, 4, 'Nectar', '2020-09-25');

-- --------------------------------------------------------

--
-- Table structure for table `artist`
--

CREATE TABLE `artist` (
  `ArtistID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Genre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artist`
--

INSERT INTO `artist` (`ArtistID`, `Name`, `Genre`) VALUES
(1, 'The Weeknd', 'R&B'),
(2, 'Tame Impala', 'psychedelic rock'),
(3, 'The 1975', 'Indie rock'),
(4, 'Joji', 'R&B');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Method` varchar(50) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'success',
  `CardBrand` varchar(30) DEFAULT NULL,
  `CardLast4` varchar(4) DEFAULT NULL,
  `TransactionRef` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `UserID`, `Amount`, `Date`, `Method`, `Status`, `CardBrand`, `CardLast4`, `TransactionRef`) VALUES
(1, 1, 9.99, '2026-05-06', 'CreditCard', 'success', NULL, NULL, NULL),
(2, 2, 9.99, '2026-05-06', 'CreditCard', 'success', NULL, NULL, NULL),
(4, 3, 4.99, '2026-05-16', 'Card', 'success', 'Visa', '4242', 'txn_d788d44aff36b3c1df4280e2'),
(5, 2, 9.99, '2026-05-16', 'Card', 'success', 'Demo Card', 'card', 'txn_0fc1c4c0e26f61220b293ee7'),
(6, 3, 4.99, '2026-05-16', 'Card', 'success', 'Card', '5547', 'txn_76f7bc94b4948d4bae81df3b');

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

CREATE TABLE `playlist` (
  `PlaylistID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `CreationDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlist`
--

INSERT INTO `playlist` (`PlaylistID`, `UserID`, `Name`, `CreationDate`) VALUES
(1, 1, 'Paroxitene', '2026-05-02'),
(2, 2, 'Mohomaya', '2026-05-02'),
(3, 3, 'Otasha', '2026-05-02');

-- --------------------------------------------------------

--
-- Table structure for table `playlistsong`
--

CREATE TABLE `playlistsong` (
  `PlaylistID` int(11) NOT NULL,
  `SongID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlistsong`
--

INSERT INTO `playlistsong` (`PlaylistID`, `SongID`) VALUES
(1, 2),
(1, 6),
(1, 8),
(1, 10),
(2, 4),
(2, 9),
(3, 1),
(3, 7);

-- --------------------------------------------------------

--
-- Table structure for table `song`
--

CREATE TABLE `song` (
  `SongID` int(11) NOT NULL,
  `AlbumID` int(11) DEFAULT NULL,
  `Title` varchar(100) DEFAULT NULL,
  `Duration` int(11) DEFAULT NULL,
  `Genre` varchar(50) DEFAULT NULL,
  `BlobName` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `song`
--

INSERT INTO `song` (`SongID`, `AlbumID`, `Title`, `Duration`, `Genre`, `BlobName`) VALUES
(1, 1, 'Call Out My Name', 224, 'R&B', NULL),
(2, 1, 'Try Me', 203, 'R&B', NULL),
(3, 1, 'I Was Never There', 241, 'R&B', NULL),
(4, 2, 'Let It Happen', 476, 'Psychedelic Rock', NULL),
(5, 2, 'Eventually', 356, 'Psychedelic Rock', NULL),
(6, 2, 'The Less I Know The Better', 216, 'Psychedelic Rock', NULL),
(7, 3, 'Robbers', 298, 'Indie Rock', NULL),
(8, 3, 'About You', 356, 'Indie Rock', NULL),
(9, 4, 'Sanctuary', 217, 'R&B', NULL),
(10, 4, 'Gimme Love', 197, 'R&B', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `streaminglog`
--

CREATE TABLE `streaminglog` (
  `LogID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `SongID` int(11) DEFAULT NULL,
  `Timestamp` datetime DEFAULT NULL,
  `Device` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `streaminglog`
--

INSERT INTO `streaminglog` (`LogID`, `UserID`, `SongID`, `Timestamp`, `Device`) VALUES
(1, 1, 1, '2026-05-06 23:23:01', 'Mobile');

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `SubID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `PaymentStatus` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription`
--

INSERT INTO `subscription` (`SubID`, `UserID`, `Type`, `StartDate`, `EndDate`, `PaymentStatus`) VALUES
(1, 1, 'Premium', '2026-05-01', '2026-06-01', 'Paid'),
(2, 4, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(3, 4, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(4, 4, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(5, 4, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(6, 4, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(7, 4, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(8, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(9, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(10, 2, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(11, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(12, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(13, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(14, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(15, 2, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(16, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(17, 2, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(18, 2, 'student', '2026-05-16', '2026-06-15', 'Paid'),
(19, 2, 'premium', '2026-05-16', '2026-06-15', 'Paid'),
(20, 3, 'Student', '2026-05-16', '2026-06-15', 'Paid'),
(21, 2, 'Premium', '2026-05-16', '2026-06-15', 'Paid'),
(22, 3, 'Student', '2026-05-16', '2026-06-15', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `SubscriptionType` varchar(50) DEFAULT NULL,
  `JoinDate` date DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Name`, `Email`, `SubscriptionType`, `JoinDate`, `username`, `password`, `profile_url`) VALUES
(1, ' Vepada Ram ', 'thevepada@gmail.com', 'Premium', '2026-05-01', 'vepada', '$2y$12$3eAq.jRwNw.APt0Ort6pOedw3EC5uBuLwGaZ7sQxvh549syALQsBe', NULL),
(2, ' Tomal Devnath ', 'TomalDevnath@gmail.com', 'Premium', '2026-05-01', 'tomal', '$2y$12$3eAq.jRwNw.APt0Ort6pOedw3EC5uBuLwGaZ7sQxvh549syALQsBe', NULL),
(3, 'Farhan Sikder ', 'frhnsikder@gmail.com', 'Student', '2026-05-01', 'farhan', '$2y$12$3eAq.jRwNw.APt0Ort6pOedw3EC5uBuLwGaZ7sQxvh549syALQsBe', NULL),
(4, 'salman rahman', 'tdsalman@gmail.com', 'Free', '2026-05-17', 'tdsalman', '$2y$12$OFpzQsYIU1jKaiHVsc.rlO4Gy5GvmzzpOs8CV7Yb9J0K7jn8aI.sa', NULL);

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `album`
--
ALTER TABLE `album`
  ADD PRIMARY KEY (`AlbumID`),
  ADD KEY `ArtistID` (`ArtistID`);

-- (file continues)


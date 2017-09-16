-- phpMyAdmin SQL Dump
-- version 4.7.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 27, 2017 at 07:57 PM
-- Server version: 5.6.34
-- PHP Version: 7.1.8

USE `charsheet-3137be9d`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `charsheet`
--

-- --------------------------------------------------------

--
-- Table structure for table `character`
--

CREATE TABLE `character` (
  `id` int(11) NOT NULL,
  `age` tinyint(4) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `game` tinytext,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `level` tinyint(4) DEFAULT NULL,
  `name` tinytext,
  `notes` mediumtext,
  `player` int(11) NOT NULL,
  `physical_wounds` int(11) DEFAULT NULL,
  `subdual_wounds` int(11) DEFAULT NULL,
  `charisma` tinyint(4) NOT NULL,
  `intelligence` tinyint(4) NOT NULL,
  `perception` tinyint(4) NOT NULL,
  `luck` tinyint(4) NOT NULL,
  `strength` tinyint(4) NOT NULL,
  `dexterity` tinyint(4) NOT NULL,
  `constitution` tinyint(4) NOT NULL,
  `speed` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stats and Misc details, the base of each character.';

-- --------------------------------------------------------

--
-- Table structure for table `log_entry`
--

CREATE TABLE `log_entry` (
  `id` int(11) DEFAULT NULL,
  `entry` text NOT NULL,
  `datetime` datetime NOT NULL,
  `summary` tinytext NOT NULL,
  `parent` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Each die roll is recorded here.';

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE `player` (
  `id` int(11) NOT NULL,
  `login_name` tinytext NOT NULL,
  `name` tinytext NOT NULL,
  `password` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `skill`
--

CREATE TABLE `skill` (
  `id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `ticks` tinyint(4) NOT NULL,
  `value` tinyint(4) NOT NULL,
  `parent` int(11) NOT NULL,
  `ordering` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Each skill has one of these.';

-- --------------------------------------------------------

--
-- Table structure for table `specialty`
--

CREATE TABLE `specialty` (
  `id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `value` tinyint(4) NOT NULL,
  `parent` int(11) NOT NULL,
  `ordering` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Skills can have a number of specialties.';

-- --------------------------------------------------------

--
-- Table structure for table `xp_gain`
--

CREATE TABLE `xp_gain` (
  `id` int(11) NOT NULL,
  `amount` smallint(6) NOT NULL,
  `reason` text NOT NULL,
  `parent` int(11) NOT NULL,
  `ordering` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Each time the character gains XP I''ll record it here.';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `character`
--
ALTER TABLE `character`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login_name` (`login_name`(255));

--
-- Indexes for table `skill`
--
ALTER TABLE `skill`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_skill_names` (`parent`,`name`(255));

--
-- Indexes for table `specialty`
--
ALTER TABLE `specialty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent` (`parent`,`name`(255));

--
-- Indexes for table `xp_gain`
--
ALTER TABLE `xp_gain`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `character`
--
ALTER TABLE `character`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `player`
--
ALTER TABLE `player`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `skill`
--
ALTER TABLE `skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `specialty`
--
ALTER TABLE `specialty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `xp_gain`
--
ALTER TABLE `xp_gain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Metadata
--
USE `phpmyadmin`;

--
-- Metadata for table character
--

--
-- Metadata for table log_entry
--

--
-- Metadata for table player
--

--
-- Metadata for table skill
--

--
-- Metadata for table specialty
--

--
-- Metadata for table xp_gain
--

--
-- Metadata for database charsheet
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

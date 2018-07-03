-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: ironmanauedata.mysql.db
-- Generation Time: Jul 03, 2018 at 09:36 AM
-- Server version: 5.6.39-log
-- PHP Version: 7.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ironmanauedata`
--

-- --------------------------------------------------------

--
-- Table structure for table `arena`
--

CREATE TABLE `arena` (
  `id` int(2) NOT NULL,
  `arena_id` int(2) NOT NULL,
  `name` varchar(35) NOT NULL,
  `arena` varchar(35) NOT NULL,
  `trophy_limit` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `id` int(6) NOT NULL,
  `card_key` varchar(25) NOT NULL,
  `name` varchar(25) NOT NULL,
  `elixir` int(2) NOT NULL,
  `type` varchar(15) NOT NULL,
  `rarity` varchar(15) NOT NULL,
  `cr_id` int(12) NOT NULL,
  `arena` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `decks`
--

CREATE TABLE `decks` (
  `id` int(6) NOT NULL,
  `player_id` int(5) NOT NULL,
  `card_1` int(4) NOT NULL,
  `card_2` int(4) NOT NULL,
  `card_3` int(4) NOT NULL,
  `card_4` int(4) NOT NULL,
  `card_5` int(4) NOT NULL,
  `card_6` int(4) NOT NULL,
  `card_7` int(4) NOT NULL,
  `card_8` int(4) NOT NULL,
  `war_id` int(4) DEFAULT NULL,
  `current` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(4) NOT NULL,
  `tag` varchar(10) CHARACTER SET utf8 NOT NULL COMMENT 'Tag du joueur',
  `name` varchar(50) CHARACTER SET utf8 NOT NULL COMMENT 'Nom du joueur',
  `rank` int(2) NOT NULL COMMENT 'Placement dans le clan',
  `trophies` int(5) NOT NULL COMMENT 'Nombre de trophée du joueur',
  `role_id` int(1) NOT NULL,
  `exp_level` int(2) NOT NULL COMMENT 'Niveau de la tour',
  `in_clan` tinyint(1) NOT NULL DEFAULT '0',
  `arena` int(3) NOT NULL,
  `donations` int(10) NOT NULL,
  `donations_received` int(10) NOT NULL,
  `donations_delta` int(10) NOT NULL,
  `donations_ratio` float NOT NULL,
  `max_trophies` int(5) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player_war`
--

CREATE TABLE `player_war` (
  `id` int(10) NOT NULL,
  `player_id` int(5) NOT NULL,
  `cards_earned` int(4) NOT NULL,
  `collection_played` int(1) NOT NULL,
  `collection_won` int(1) NOT NULL,
  `battle_played` int(1) NOT NULL,
  `battle_won` int(1) NOT NULL,
  `war_id` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(1) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `machine_name` varchar(20) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `standings`
--

CREATE TABLE `standings` (
  `id` int(5) NOT NULL,
  `tag` varchar(10) NOT NULL,
  `name` varchar(35) NOT NULL,
  `participants` int(2) NOT NULL,
  `battles_played` int(2) NOT NULL,
  `battles_won` int(2) NOT NULL,
  `crowns` int(4) NOT NULL,
  `war_trophies` int(5) NOT NULL,
  `war_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `war`
--

CREATE TABLE `war` (
  `id` int(4) NOT NULL,
  `created` int(15) NOT NULL,
  `past_war` tinyint(1) NOT NULL COMMENT 'boolean, vrai si c''est une ancienne guerre'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `arena`
--
ALTER TABLE `arena`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arena_id` (`arena_id`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arena` (`arena`);

--
-- Indexes for table `decks`
--
ALTER TABLE `decks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `card_1` (`card_1`),
  ADD KEY `card_2` (`card_2`),
  ADD KEY `card_3` (`card_3`),
  ADD KEY `card_4` (`card_4`),
  ADD KEY `card_5` (`card_5`),
  ADD KEY `card_6` (`card_6`),
  ADD KEY `card_7` (`card_7`),
  ADD KEY `card_8` (`card_8`),
  ADD KEY `war_id` (`war_id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- Indexes for table `player_war`
--
ALTER TABLE `player_war`
  ADD PRIMARY KEY (`id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `war_id` (`war_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `standings`
--
ALTER TABLE `standings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `war_id` (`war_id`);

--
-- Indexes for table `war`
--
ALTER TABLE `war`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `arena`
--
ALTER TABLE `arena`
  MODIFY `id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;
--
-- AUTO_INCREMENT for table `decks`
--
ALTER TABLE `decks`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=606;
--
-- AUTO_INCREMENT for table `player_war`
--
ALTER TABLE `player_war`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=387;
--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `standings`
--
ALTER TABLE `standings`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `war`
--
ALTER TABLE `war`
  MODIFY `id` int(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`arena`) REFERENCES `arena` (`arena_id`);

--
-- Constraints for table `decks`
--
ALTER TABLE `decks`
  ADD CONSTRAINT `decks_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `decks_ibfk_2` FOREIGN KEY (`war_id`) REFERENCES `war` (`id`);

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Constraints for table `player_war`
--
ALTER TABLE `player_war`
  ADD CONSTRAINT `player_war_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`),
  ADD CONSTRAINT `player_war_ibfk_2` FOREIGN KEY (`war_id`) REFERENCES `war` (`id`);

--
-- Constraints for table `standings`
--
ALTER TABLE `standings`
  ADD CONSTRAINT `standings_ibfk_1` FOREIGN KEY (`war_id`) REFERENCES `war` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

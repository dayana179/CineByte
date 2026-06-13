-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 06:35 PM
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
-- Database: `cinebyte`
--

-- --------------------------------------------------------

--
-- Table structure for table `journals`
--

CREATE TABLE `journals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tmdb_id` int(11) DEFAULT NULL,
  `youtube_id` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 3,
  `review` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `content_type` varchar(20) DEFAULT 'film',
  `poster_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `journals`
--

INSERT INTO `journals` (`id`, `user_id`, `tmdb_id`, `youtube_id`, `title`, `rating`, `review`, `created_at`, `content_type`, `poster_path`) VALUES
(1, 1, 803796, NULL, 'KPop Demon Hunters', 4, 'the songs are fine ig', '2026-06-11 17:31:03', 'film', NULL),
(2, 1, 1667198, NULL, 'The Amazing Digital Circus: The Last Act', 10, 'good', '2026-06-13 19:56:12', 'film', NULL),
(3, 1, 495764, NULL, 'Birds of Prey (and the Fantabulous Emancipation of One Harley Quinn)', 8, 'fffgf', '2026-06-13 19:58:50', 'film', NULL),
(4, 1, NULL, 'ENbpFikcWoY', 'The Chic Code -Almost Got Arrested With Dr*gs?! Ft. Imran Bard | Episode 315', 6, 'funny\'', '2026-06-13 23:20:31', 'video', 'https://img.youtube.com/vi/ENbpFikcWoY/hqdefault.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `list_movies`
--

CREATE TABLE `list_movies` (
  `id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `poster_path` varchar(500) DEFAULT NULL,
  `release_date` varchar(20) DEFAULT NULL,
  `vote_average` decimal(3,1) DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `list_movies`
--

INSERT INTO `list_movies` (`id`, `list_id`, `tmdb_id`, `title`, `poster_path`, `release_date`, `vote_average`, `added_at`) VALUES
(1, 1, 5559, 'Bee Movie', '/aWe27GmvfVYAd7p0KEtJZWwLWk5.jpg', '2007-10-28', 6.0, '2026-06-13 19:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `theme` varchar(10) DEFAULT 'dark',
  `notifications` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `theme`, `notifications`, `created_at`) VALUES
(1, 'farhahezam', 'farhahezam@gmail.com', '$2y$10$RbtavtCW3DVXdzY.LdaDv.yBn7a5pGXFWeD.31e/yaEX4SKQgopWG', 'dark', 1, '2026-06-11 13:18:04'),
(2, 'dayanacute', 'dayana@gmail.com', '$2y$10$ndTauCj91FQ9.R1Hctosl.Z4wG8fmWMvlYJkLkBDX4gGjCsYu6FRG', 'dark', 1, '2026-06-13 20:05:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_lists`
--

CREATE TABLE `user_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `list_name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_lists`
--

INSERT INTO `user_lists` (`id`, `user_id`, `list_name`, `created_at`) VALUES
(1, 1, 'fave', '2026-06-13 19:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `video_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `youtube_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `watched` tinyint(1) DEFAULT 0,
  `views_tracked` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_opened` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`video_id`, `user_id`, `youtube_id`, `title`, `url`, `watched`, `views_tracked`, `added_at`, `last_opened`) VALUES
(2, 1, 'GNys6qJMthE', 'SEPAYUNG - SHORT FILM', 'https://www.youtube.com/watch?v=GNys6qJMthE', 0, 1, '2026-06-13 11:48:36', '2026-06-13 11:48:36'),
(3, 1, 'rHtt2wAAibY', 'KANTOI SHORT FILM', 'https://www.youtube.com/watch?v=rHtt2wAAibY', 0, 1, '2026-06-13 11:59:18', '2026-06-13 11:59:18'),
(4, 1, 'ENbpFikcWoY', 'The Chic Code -Almost Got Arrested With Dr*gs?! Ft. Imran Bard | Episode 315', 'https://www.youtube.com/watch?v=ENbpFikcWoY', 0, 4, '2026-06-13 14:56:16', '2026-06-13 15:21:38');

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `poster_path` varchar(500) DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  `content_type` varchar(20) DEFAULT 'film',
  `youtube_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `watchlist`
--

INSERT INTO `watchlist` (`id`, `user_id`, `tmdb_id`, `title`, `poster_path`, `added_at`, `content_type`, `youtube_id`) VALUES
(16, 1, 0, 'KANTOI SHORT FILM', 'https://img.youtube.com/vi/rHtt2wAAibY/hqdefault.jpg', '2026-06-13 19:59:31', 'video', 'rHtt2wAAibY');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `journals`
--
ALTER TABLE `journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `list_movies`
--
ALTER TABLE `list_movies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_list_movie` (`list_id`,`tmdb_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_lists`
--
ALTER TABLE `user_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`video_id`);

--
-- Indexes for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_watchlist` (`user_id`,`tmdb_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `journals`
--
ALTER TABLE `journals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `list_movies`
--
ALTER TABLE `list_movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_lists`
--
ALTER TABLE `user_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `video_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `journals`
--
ALTER TABLE `journals`
  ADD CONSTRAINT `journals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `list_movies`
--
ALTER TABLE `list_movies`
  ADD CONSTRAINT `list_movies_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `user_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_lists`
--
ALTER TABLE `user_lists`
  ADD CONSTRAINT `user_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

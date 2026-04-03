-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2026 at 11:17 PM
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
-- Database: `fav`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `conversation_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_id`, `content`, `is_deleted`, `created_at`) VALUES
(1, 1, 3, 'hey gab', 0, '2026-04-01 15:33:28'),
(2, 1, 4, 'hey jean', 0, '2026-04-01 15:33:41'),
(3, 2, 4, 'kys', 0, '2026-04-01 15:33:54'),
(4, 3, 4, 'ah shit', 0, '2026-04-01 15:34:23'),
(5, 1, 3, 'test', 0, '2026-04-01 15:52:16'),
(6, 1, 4, 'test', 0, '2026-04-02 20:18:11'),
(7, 1, 3, 'test', 0, '2026-04-02 20:20:57');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `spot_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL = commentaire racine, sinon reply',
  `content` text NOT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = masqué par modération',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('direct','group') NOT NULL DEFAULT 'direct',
  `name` varchar(191) DEFAULT NULL COMMENT 'Nom du groupe uniquement',
  `avatar` varchar(500) DEFAULT NULL COMMENT 'Avatar du groupe',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `type`, `name`, `avatar`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'direct', NULL, NULL, 3, '2026-04-01 15:33:21', '2026-04-02 20:20:57'),
(2, 'direct', NULL, NULL, 4, '2026-04-01 15:33:50', '2026-04-01 15:33:54'),
(3, 'group', 'Test', NULL, 4, '2026-04-01 15:34:16', '2026-04-01 15:34:23');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_members`
--

CREATE TABLE `conversation_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `conversation_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL COMMENT 'Dernière lecture pour badge non-lus',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_members`
--

INSERT INTO `conversation_members` (`id`, `conversation_id`, `user_id`, `last_read_at`, `joined_at`) VALUES
(1, 1, 3, '2026-04-02 20:20:49', '2026-04-01 15:33:21'),
(2, 1, 4, '2026-04-02 21:01:25', '2026-04-01 15:33:21'),
(3, 2, 4, '2026-04-02 21:01:25', '2026-04-01 15:33:50'),
(4, 2, 1, NULL, '2026-04-01 15:33:50'),
(5, 3, 4, '2026-04-02 21:01:24', '2026-04-01 15:34:16'),
(6, 3, 1, NULL, '2026-04-01 15:34:16'),
(7, 3, 3, '2026-04-01 15:52:08', '2026-04-01 15:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(191) DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `type` enum('private','shared','public') NOT NULL DEFAULT 'public',
  `status` enum('pending','accepted','refused') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `description`, `location`, `event_date`, `type`, `status`, `created_at`, `updated_at`) VALUES
(4, 3, 'redstd', '[end:11:00] dsqsqdsq', 'dsqdsqdsq', '2026-04-09 10:00:00', 'private', 'accepted', '2026-04-02 19:50:42', '2026-04-02 19:50:42'),
(5, 3, 'gdsff', '[end:11:00] sqddsq', 'fqdsdsqdsqdqsdsq', '2026-04-10 10:00:00', 'shared', 'accepted', '2026-04-02 19:50:50', '2026-04-02 19:50:50'),
(6, 3, 'sqsqddsqsqddsq', '[end:11:00] qsddsqqsddsq', 'sqdsdqdsqdsqqds', '2026-04-11 10:00:00', 'public', 'accepted', '2026-04-02 19:51:08', '2026-04-02 19:51:08'),
(7, 3, 'sqd', '[end:11:00] qsd', 'dsq', '2026-04-11 10:00:00', 'private', 'accepted', '2026-04-02 20:01:42', '2026-04-02 20:01:42'),
(8, 3, 'gdsff', '[end:11:00] gab et jean', 'dsqdsqdsq', '2026-04-16 10:00:00', 'shared', 'accepted', '2026-04-02 20:21:48', '2026-04-02 20:21:48'),
(9, 4, 'gdsff', '[end:11:00] gab et jean', 'dsqdsqdsq', '2026-04-16 10:00:00', 'shared', 'accepted', '2026-04-02 20:28:40', '2026-04-02 20:28:40'),
(10, 3, 'redstd', '[end:11:00] sqdsdqf', 'fqdsdsqdsqdqsdsq', '2026-04-17 10:00:00', 'public', 'accepted', '2026-04-02 20:28:54', '2026-04-02 20:28:54'),
(11, 4, 'redstd', '[end:11:00] sqdsdqf', 'fqdsdsqdsqdqsdsq', '2026-04-17 10:00:00', 'public', 'accepted', '2026-04-02 20:29:08', '2026-04-02 20:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `invited_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_participants`
--

INSERT INTO `event_participants` (`id`, `event_id`, `user_id`, `status`, `invited_at`, `responded_at`, `created_at`) VALUES
(1, 8, 4, 'accepted', '2026-04-02 20:21:56', '2026-04-02 20:28:40', '2026-04-02 20:21:56'),
(2, 10, 4, 'accepted', '2026-04-02 20:28:59', '2026-04-02 20:29:08', '2026-04-02 20:28:59');

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int(10) UNSIGNED NOT NULL,
  `follower_id` int(10) UNSIGNED NOT NULL COMMENT 'celui qui suit',
  `followed_id` int(10) UNSIGNED NOT NULL COMMENT 'celui qui est suivi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL COMMENT 'utilisateur qui envoie la demande',
  `receiver_id` int(10) UNSIGNED NOT NULL COMMENT 'utilisateur qui reçoit la demande',
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friendships`
--

INSERT INTO `friendships` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`, `updated_at`) VALUES
(3, 4, 3, 'accepted', '2026-04-02 20:18:47', '2026-04-02 20:19:07');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `spot_id` int(10) UNSIGNED DEFAULT NULL,
  `comment_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'destinataire',
  `actor_id` int(10) UNSIGNED NOT NULL COMMENT 'qui a déclenché l action',
  `type` enum('friend_request','friend_accepted','new_message','event_invitation','event_accepted','event_declined','like_spot','like_comment','comment','reply','follow','mention') NOT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID de l objet lié (friendship_id, conv_id, event_id…)',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `actor_id`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 3, 4, 'friend_request', 3, 1, '2026-04-02 20:18:47'),
(2, 4, 3, 'friend_accepted', 3, 1, '2026-04-02 20:19:07'),
(3, 4, 3, 'event_invitation', 8, 1, '2026-04-02 20:21:56'),
(4, 3, 4, 'event_accepted', 8, 0, '2026-04-02 20:28:40'),
(5, 4, 3, 'event_invitation', 10, 1, '2026-04-02 20:28:59'),
(6, 3, 4, 'event_accepted', 10, 0, '2026-04-02 20:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL COMMENT 'max 191 car. pour index utf8mb4',
  `token` varchar(128) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `reporter_id` int(10) UNSIGNED NOT NULL,
  `spot_id` int(10) UNSIGNED DEFAULT NULL,
  `comment_id` int(10) UNSIGNED DEFAULT NULL,
  `reason` enum('spam','harcelement','contenu inapproprie','fausse information','autre') NOT NULL DEFAULT 'autre',
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'admin qui a traite le signalement',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(128) NOT NULL COMMENT 'token généré avec random_bytes()',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IPv4 / IPv6',
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `spots`
--

CREATE TABLE `spots` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(191) DEFAULT NULL COMMENT 'lieu du spot (ville, pays...)',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'coordonnées GPS',
  `longitude` decimal(11,8) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL COMMENT 'chemin image principale',
  `category` enum('voyage','nature','ville','gastronomie','culture','aventure','autre') NOT NULL DEFAULT 'autre',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `spots`
--

INSERT INTO `spots` (`id`, `user_id`, `title`, `description`, `location`, `latitude`, `longitude`, `image`, `category`, `status`, `views_count`, `created_at`, `updated_at`) VALUES
(1, 3, 'sdq', 'sqd', 'qsd', NULL, NULL, NULL, 'voyage', 'published', 2, '2026-04-01 13:11:50', '2026-04-01 13:40:52');

-- --------------------------------------------------------

--
-- Table structure for table `spot_images`
--

CREATE TABLE `spot_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `spot_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `sort_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `spot_tags`
--

CREATE TABLE `spot_tags` (
  `spot_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `created_at`) VALUES
(1, 'voyage', '2026-04-01 12:52:20'),
(2, 'nature', '2026-04-01 12:52:20'),
(3, 'citytrip', '2026-04-01 12:52:20'),
(4, 'roadtrip', '2026-04-01 12:52:20'),
(5, 'foodie', '2026-04-01 12:52:20'),
(6, 'sunset', '2026-04-01 12:52:20'),
(7, 'montagne', '2026-04-01 12:52:20'),
(8, 'plage', '2026-04-01 12:52:20'),
(9, 'architecture', '2026-04-01 12:52:20'),
(10, 'culture', '2026-04-01 12:52:20'),
(11, 'aventure', '2026-04-01 12:52:20'),
(12, 'caché', '2026-04-01 12:52:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(191) NOT NULL COMMENT 'max 191 car. utf8mb4 = 764 octets < limite 767',
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash — JAMAIS en clair',
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `avatar` varchar(500) DEFAULT NULL COMMENT 'chemin ou URL avatar',
  `bio` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'fr',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = compte banni',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(100) DEFAULT 'Hey, I''m on FAV!'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `avatar`, `bio`, `country`, `language`, `is_active`, `email_verified`, `created_at`, `updated_at`, `status`) VALUES
(1, 'admin', 'admin@fav.fr', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'Administrateur FAV', 'France', 'fr', 1, 1, '2026-04-01 12:52:20', '2026-04-01 12:52:20', 'Hey, I\'m on FAV!'),
(2, 'demo_user', 'user@fav.fr', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NULL, 'Voyageur passionné 🌍', 'France', 'fr', 1, 1, '2026-04-01 12:52:20', '2026-04-01 12:52:20', 'Hey, I\'m on FAV!'),
(3, 'jeanjean', 'jeanjean@jean.com', '$2y$12$VYzm1hfLAuRy6ct8SoSLme03FTUHG4zEh6xnQpoyA6Dk0xeP6P3Yq', 'user', NULL, NULL, 'fr', 'en', 1, 0, '2026-04-01 12:53:09', '2026-04-01 12:53:25', 'jeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjeanjean'),
(4, 'gab', 'gabgab@gab.com', '$2y$12$puLwDjpyX0WrZy/tGZLsVOTjW5UDOKzo5oF5HY0XXF.7Jr8BsTOfS', 'user', NULL, NULL, '', 'en', 1, 0, '2026-04-01 13:46:22', '2026-04-01 13:46:22', 'Hey, I\'m on FAV!');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conv_id` (`conversation_id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_spot_id` (`spot_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `fk_conv_creator` (`created_by`);

--
-- Indexes for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conv_member` (`conversation_id`,`user_id`),
  ADD KEY `fk_cm_user` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_date` (`event_date`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_event_user` (`event_id`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_event_id` (`event_id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_follow` (`follower_id`,`followed_id`),
  ADD KEY `idx_followed_id` (`followed_id`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_friendship` (`sender_id`,`receiver_id`),
  ADD KEY `idx_receiver` (`receiver_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_like_spot` (`user_id`,`spot_id`),
  ADD UNIQUE KEY `uq_like_comment` (`user_id`,`comment_id`),
  ADD KEY `idx_spot_id` (`spot_id`),
  ADD KEY `idx_comment_id` (`comment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_actor_id` (`actor_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reporter` (`reporter_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_reports_spot` (`spot_id`),
  ADD KEY `fk_reports_comment` (`comment_id`),
  ADD KEY `fk_reports_admin` (`reviewed_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `spots`
--
ALTER TABLE `spots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `spot_images`
--
ALTER TABLE `spot_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_spot_id` (`spot_id`);

--
-- Indexes for table `spot_tags`
--
ALTER TABLE `spot_tags`
  ADD PRIMARY KEY (`spot_id`,`tag_id`),
  ADD KEY `fk_spot_tags_tag` (`tag_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tag_name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `conversation_members`
--
ALTER TABLE `conversation_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spots`
--
ALTER TABLE `spots`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `spot_images`
--
ALTER TABLE `spot_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_spot` FOREIGN KEY (`spot_id`) REFERENCES `spots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `fk_conv_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD CONSTRAINT `fk_cm_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD CONSTRAINT `event_participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_participants_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `fk_follows_followed` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `fk_friendship_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_friendship_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_likes_spot` FOREIGN KEY (`spot_id`) REFERENCES `spots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_reports_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_reports_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reports_spot` FOREIGN KEY (`spot_id`) REFERENCES `spots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spots`
--
ALTER TABLE `spots`
  ADD CONSTRAINT `fk_spots_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_images`
--
ALTER TABLE `spot_images`
  ADD CONSTRAINT `fk_spot_images_spot` FOREIGN KEY (`spot_id`) REFERENCES `spots` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spot_tags`
--
ALTER TABLE `spot_tags`
  ADD CONSTRAINT `fk_spot_tags_spot` FOREIGN KEY (`spot_id`) REFERENCES `spots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_spot_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

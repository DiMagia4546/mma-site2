-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02-Mar-2026 às 12:54
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mma_site`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `news_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `location` varchar(100) NOT NULL,
  `main_event` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lutador1` varchar(255) NOT NULL,
  `lutador2` varchar(255) NOT NULL,
  `banner` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `events`
--

INSERT INTO `events` (`id`, `name`, `date`, `location`, `main_event`, `created_at`, `lutador1`, `lutador2`, `banner`) VALUES
(2, 'UFC 326', '2026-03-08', 'Dom, Mar 8 / 2:00 AM GMT\r\nT-Mobile Arena, Las Vegas United States', 'Holloway vs Oliveira 2', '2026-01-19 23:39:52', 'HOLLOWAY_MAX_BMF_07-19.avif', 'OLIVEIRA_CHARLES_10-11.avif', 'uploads/ufc326_banner.webp');

-- --------------------------------------------------------

--
-- Estrutura da tabela `event_fights`
--

CREATE TABLE `event_fights` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `fighter1_name` varchar(100) NOT NULL,
  `fighter2_name` varchar(100) NOT NULL,
  `fighter1_image` varchar(255) NOT NULL,
  `fighter2_image` varchar(255) NOT NULL,
  `fight_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `event_fights`
--

INSERT INTO `event_fights` (`id`, `event_id`, `fighter1_name`, `fighter2_name`, `fighter1_image`, `fighter2_image`, `fight_order`) VALUES
(6, 2, 'Max Holloway', 'Charles Oliveira', 'uploads/HOLLOWAY_MAX_BMF_07-19.avif', 'uploads/OLIVEIRA_CHARLES_10-11.avif', 1),
(8, 2, 'Renato Moicano', 'Brian Ortega', 'uploads/MOICANO_RENATO_06-28.avif', 'uploads/ORTEGA_BRIAN_08-23.avif', 1),
(9, 2, 'Caio Borralho', 'Reinier De Ridder', 'uploads/BORRALHO_CAIO_09-06.avif', 'uploads/DE-RIDDER_REINIER_10-18.avif', 3),
(10, 2, 'Gregory Rodrigues ', 'Bruno Ferreira', 'uploads/RODRIGUES_GREGORY_11-15.avif', 'uploads/FERREIRA_BRUNNO_12-06.avif', 4),
(13, 2, 'Rob Font', 'Raul Rosas JR.', 'uploads/FONT_ROB_09-13.avif', 'uploads/ROSAS_JR_RAUL_03-29.avif', 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fighter_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fighters`
--

CREATE TABLE `fighters` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `weight_class` varchar(50) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `kos` int(11) DEFAULT 0,
  `submissions` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) NOT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `rank_division` varchar(50) DEFAULT NULL,
  `rank_pfp` varchar(50) DEFAULT NULL,
  `upcoming_event` varchar(100) DEFAULT NULL,
  `upcoming_opponent` varchar(100) DEFAULT NULL,
  `upcoming_date` date DEFAULT NULL,
  `is_champion` tinyint(1) DEFAULT 0,
  `age` int(11) DEFAULT 0,
  `height` varchar(10) DEFAULT '',
  `reach` varchar(10) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `fighters`
--

INSERT INTO `fighters` (`id`, `name`, `weight_class`, `nationality`, `wins`, `losses`, `kos`, `submissions`, `created_at`, `image`, `nickname`, `rank_division`, `rank_pfp`, `upcoming_event`, `upcoming_opponent`, `upcoming_date`, `is_champion`, `age`, `height`, `reach`) VALUES
(1, 'Joshua Van', 'Flyweight', 'American', 16, 2, 8, 2, '2026-01-19 15:31:26', 'uploads/VAN_JOSHUA_BELT.avif', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '', ''),
(3, 'Ilia topuria', 'light weight', 'Georgian', 17, 0, 7, 8, '2026-01-19 22:37:45', 'uploads/TOPURIA_ILIA_BELT_10-26.avif', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '', ''),
(4, 'Alex Pereira', 'Light Heavy weight', 'Brazilian', 13, 3, 11, 0, '2026-01-19 22:37:45', 'uploads/PEREIRA_ALEX_BELT_03-08.avif', 'Poatan', '#1 Light Heavyweight', '#3 P4P', NULL, NULL, NULL, 0, 0, '', ''),
(5, 'Petr Yan', 'BantamWeight', 'Russian', 20, 5, 7, 1, '2026-01-19 23:04:22', 'uploads/YAN_PETR_BELT.avif', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '', ''),
(6, 'Khamzat Chimaev', 'MiddleWeight', 'Russian', 15, 0, 6, 6, '2026-01-19 23:07:09', 'uploads/CHIMAEV_KHAMZAT_BELTMOCK.avif', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '', ''),
(7, 'Tom Aspinall', 'HeavyWeight', 'English', 15, 3, 12, 3, '2026-01-19 23:07:09', 'uploads/ASPINALL_TOM_BELT_10-25.avif', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '', '');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fighter_history`
--

CREATE TABLE `fighter_history` (
  `id` int(11) NOT NULL,
  `fighter_id` int(11) NOT NULL,
  `opponent_name` varchar(100) NOT NULL,
  `opponent_image` varchar(255) DEFAULT NULL,
  `event_name` varchar(100) NOT NULL,
  `fight_date` date NOT NULL,
  `result` enum('Win','Loss','Draw') NOT NULL,
  `method` varchar(100) NOT NULL,
  `round_number` int(11) NOT NULL,
  `time` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `fighter_history`
--

INSERT INTO `fighter_history` (`id`, `fighter_id`, `opponent_name`, `opponent_image`, `event_name`, `fight_date`, `result`, `method`, `round_number`, `time`) VALUES
(6, 1, 'Alexandre Pantoja', 'uploads/PANTOJA_ALEXANDRE.webp', 'UFC 323', '2025-12-06', 'Win', 'KO/TKO', 1, '0:26'),
(7, 1, 'Brandon Royval', 'uploads/ROYVAL_BRANDON.webp', 'UFC 317', '2025-06-28', 'Win', 'Decision', 3, '5:00'),
(8, 1, 'Bruno Silva', 'uploads/SILVA_BRUNO.webp', 'UFC 316', '2025-06-07', 'Win', 'KO/TKO', 3, '4:01'),
(12, 5, 'Merab Dvalishvili', 'uploads/DVALISHVILI_MERAB.webp', 'UFC 323', '2025-12-06', 'Win', 'Decision', 5, '5:00'),
(13, 5, 'Marcus McGhee', 'uploads/MCGHEE_MARCUS.webp', 'UFC Fight Night', '2025-07-26', 'Win', 'Decision', 3, '5:00'),
(14, 5, 'Deiveson Figueiredo', 'uploads/FIGUEIREDO_DEIVESON.webp', 'UFC Fight Night', '2024-11-23', 'Win', 'Decision', 5, '5:00'),
(15, 6, 'Dricus Du Plessis', 'uploads/DUPLESSIS_DRICUS.webp', 'UFC 319', '2025-08-16', 'Win', 'Decision', 5, '5:00'),
(16, 6, 'Robert Whittaker', 'uploads/WHITTAKER_ROBERT.webp', 'UFC 308', '2024-10-26', 'Win', 'Submission', 1, '3:34'),
(17, 6, 'Kamaru Usman', 'uploads/USMAN_KAMARU.webp', 'UFC 294', '2023-10-21', 'Win', 'Decision', 3, '5:00'),
(18, 7, 'Curtis Blaydes', 'uploads/BLAYDES_CURTIS.webp', 'UFC 304', '2024-07-27', 'Win', 'KO/TKO', 1, '1:00'),
(19, 7, 'Sergei Pavlovich', 'uploads/PAVLOVICH_SERGEI.webp', 'UFC 295', '2023-11-11', 'Win', 'KO/TKO', 1, '1:09'),
(20, 7, 'Marcin Tybura', 'uploads/TYBURA_MARCIN.webp', 'UFC Fight Night', '2023-07-22', 'Win', 'KO/TKO', 1, '1:13'),
(23, 4, 'Sean Strickland', 'uploads/SEAN_STRICKLAND.webp', 'UFC 277', '2022-07-30', 'Win', 'KO/TKO', 1, '2:36'),
(24, 4, 'Israel Adesanya', 'uploads/ISRAEL_ADESANYA.webp', 'UFC 281', '2022-11-12', 'Win', 'KO/TKO', 5, '2:01'),
(25, 4, 'Israel Adesanya', 'uploads/ISRAEL_ADESANYA.webp', 'UFC 287', '2023-04-08', 'Loss', 'KO/TKO', 2, '4:21'),
(26, 4, 'Jiri Prochazka', 'uploads/JIRI_PROCHAZKA.webp', 'UFC 295', '2023-11-11', 'Win', 'KO/TKO', 2, '4:08'),
(27, 4, 'Jamahal Hill', 'uploads/JAMAHAL_HILL.webp', 'UFC 300', '2024-04-13', 'Win', 'KO/TKO', 1, '3:14'),
(30, 4, 'Magomed Ankalaev', 'uploads/ANKALAEV_MAGOMED.webp', 'UFC 313', '2025-03-15', 'Loss', 'Decision', 5, '5:00'),
(31, 4, 'Magomed Ankalaev', 'uploads/ANKALAEV_MAGOMED.webp', 'UFC 320', '2025-11-22', 'Win', 'KO/TKO', 2, '3:11'),
(35, 3, 'Charles Oliveira', 'uploads/OLIVEIRA_CHARLES.webp', 'UFC 317', '2025-06-28', 'Win', 'KO/TKO', 1, '2:27'),
(36, 3, 'Max Holloway', 'uploads/HOLLOWAY_MAX.webp', 'UFC 308', '2024-10-26', 'Win', 'KO/TKO', 3, '1:34'),
(37, 3, 'Alexander Volkanovski', 'uploads/VOLKANOVSKI_ALEXANDER.webp', 'UFC 298', '2024-02-17', 'Win', 'KO/TKO', 2, '3:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `author_id`, `created_at`) VALUES
(1, 'Jon Jones Returns!', 'Jon Jones is back in the octagon...', 1, '2026-01-19 15:31:26');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `profile_pic`, `created_at`) VALUES
(3, 'Admin', 'admin@mma360.com', '$2y$10$Q0u1WQ8pYtYgYpYpYpYpOe6Qx7Qx7Qx7Qx7Qx7Qx7Qx7Qx7Qx7', 'admin', NULL, '2026-02-27 11:17:40');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_activity`
--

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `event_fights`
--
ALTER TABLE `event_fights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fighter_id` (`fighter_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `fighters`
--
ALTER TABLE `fighters`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `fighter_history`
--
ALTER TABLE `fighter_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fighter_id` (`fighter_id`);

--
-- Índices para tabela `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `event_fights`
--
ALTER TABLE `event_fights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fighters`
--
ALTER TABLE `fighters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `fighter_history`
--
ALTER TABLE `fighter_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`),
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Limitadores para a tabela `event_fights`
--
ALTER TABLE `event_fights`
  ADD CONSTRAINT `event_fights_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`fighter_id`) REFERENCES `fighters` (`id`),
  ADD CONSTRAINT `favorites_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Limitadores para a tabela `fighter_history`
--
ALTER TABLE `fighter_history`
  ADD CONSTRAINT `fighter_history_ibfk_1` FOREIGN KEY (`fighter_id`) REFERENCES `fighters` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

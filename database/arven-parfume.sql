-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Des 2025 pada 20.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `arven-parfume`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 3, 'register', 'User registered successfully', '::1', '2025-12-06 12:37:05'),
(2, 3, 'login', 'Auto login after registration', '::1', '2025-12-06 12:37:05'),
(3, 3, 'logout', 'User logged out', '::1', '2025-12-06 12:37:05'),
(4, 4, 'register', 'User registered successfully', '::1', '2025-12-06 12:37:10'),
(5, 4, 'login', 'Auto login after registration', '::1', '2025-12-06 12:37:10'),
(6, 4, 'login_failed', 'Failed login attempt', '::1', '2025-12-06 12:38:18'),
(7, 4, 'login', 'User logged in successfully', '::1', '2025-12-06 12:38:37'),
(8, 4, 'logout', 'User logged out', '::1', '2025-12-06 12:56:47'),
(9, 4, 'login', 'User logged in successfully', '::1', '2025-12-06 12:57:53'),
(10, 4, 'logout', 'User logged out', '::1', '2025-12-06 13:07:14'),
(11, 5, 'register', 'User registered successfully', '::1', '2025-12-06 13:08:00'),
(12, 5, 'login', 'Auto login after registration', '::1', '2025-12-06 13:08:00'),
(13, 5, 'logout', 'User logged out', '::1', '2025-12-06 13:10:18'),
(14, 4, 'login', 'User logged in successfully', '::1', '2025-12-07 12:00:12'),
(15, 4, 'login', 'User logged in successfully', '::1', '2025-12-08 06:45:28'),
(0, 0, 'register', 'User registered successfully', '::1', '2025-12-09 05:17:38'),
(0, 0, 'login', 'Auto login after registration', '::1', '2025-12-09 05:17:38'),
(0, 0, 'register', 'User registered successfully', '::1', '2025-12-21 18:19:47'),
(0, 0, 'login', 'Auto login after registration', '::1', '2025-12-21 18:19:47'),
(0, 0, 'register', 'User registered successfully', '::1', '2025-12-24 19:25:13'),
(0, 0, 'login', 'Auto login after registration', '::1', '2025-12-24 19:25:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `ip_address`, `status`, `created_at`) VALUES
(0, 'mahayasa', 'mahayasa@gmail.com', 'Aroma', 'AROMA GANTENG', '::1', 'unread', '2025-12-24 19:47:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `is_active`, `email_verified`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'Administrator', 'admin@arven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 1, '2025-12-06 11:58:09', '2025-12-06 11:58:09', NULL),
(2, 'Sample User', 'user@arven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 1, 1, '2025-12-06 11:58:09', '2025-12-06 11:58:09', NULL),
(3, 'Test User', 'test_1765024625200@arven.com', '$2y$10$1OdcMWwzLhtoh0xPRbXRmOH4.NEnBA/ycavpalyP/4R8fQ7z9fPHu', 'user', 1, 0, '2025-12-06 12:37:05', '2025-12-06 12:37:05', '2025-12-06 12:37:05'),
(4, 'Ditio septian robbiansyah', 'septianditio4@gmail.com', '$2y$10$bET84EclMTjZO1W1f.9WwOdcIx8JQtopF/JePKF5vIoSCSigqv3Fi', 'user', 1, 0, '2025-12-06 12:37:10', '2025-12-08 06:45:28', '2025-12-08 06:45:28'),
(5, 'eka rizqi', 'ekarizqiromadhon6@gmail.com', '$2y$10$XK32j5HMjWBuZ4mIdm1Lme/hnRs4oqiDqzsmv1Gd./8W1MtDJW0u6', 'user', 1, 0, '2025-12-24 19:25:13', '2025-12-24 19:25:13', '2025-12-24 19:25:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `ip_address`, `user_agent`, `created_at`, `expires_at`) VALUES
(2, 4, '47290ee49c50a3fc6fa208e39d21911d5eb9b6fcfe0d3a94b894c0156d50925e', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-06 12:37:10', '2025-12-06 13:08:06'),
(6, 4, '783d4cabdfb8759b0e5d6e1799b74d6d77fe73751f4ba1ae54c3f5c2388ee358', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-07 12:00:12', '2025-12-07 12:42:00'),
(7, 4, 'b182197dee88e8e44b87cb585f9fc0ccb0bb3bbfb74a0ee9ab81f4c31703a5ad', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-08 06:45:28', '2026-01-07 07:26:11'),
(0, 0, 'ab78f9f36586fd46655e14bb4ea9b299bfa723884a833853b52a2bd4dc1f0a6a', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-09 05:17:38', '2025-12-09 07:43:06'),
(0, 0, '81915c24effec6cfbacc37f032daefc46304f80724c7a5530b74273d37f9de81', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-21 18:19:47', '2025-12-21 18:57:21'),
(0, 0, '5d542dbca3894410c1ab1c1046ae64b13de68f734fe9b582a6a37f15c2a90e9f', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-24 19:25:13', '2025-12-24 20:00:16');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `user_statistics`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `user_statistics` (
`total_users` bigint(21)
,`total_admins` decimal(22,0)
,`total_users_regular` decimal(22,0)
,`active_users` decimal(22,0)
,`verified_users` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `user_statistics`
--
DROP TABLE IF EXISTS `user_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_statistics`  AS SELECT count(0) AS `total_users`, sum(case when `users`.`role` = 'admin' then 1 else 0 end) AS `total_admins`, sum(case when `users`.`role` = 'user' then 1 else 0 end) AS `total_users_regular`, sum(case when `users`.`is_active` = 1 then 1 else 0 end) AS `active_users`, sum(case when `users`.`email_verified` = 1 then 1 else 0 end) AS `verified_users` FROM `users` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

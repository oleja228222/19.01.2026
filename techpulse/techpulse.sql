-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.0:3306
-- Время создания: Мар 31 2026 г., 16:41
-- Версия сервера: 8.0.43
-- Версия PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `techpulse`
--

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci DEFAULT 'published',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `articles`
--

INSERT INTO `articles` (`id`, `user_id`, `category_id`, `title`, `slug`, `content`, `cover`, `is_premium`, `views`, `status`, `created_at`, `updated_at`) VALUES
(1, 5, 6, 'заг1', 'zag1', 'Я УЖЕ УСТАЛ(((', 'cover_1774955188_55df9b70.jpg', 0, 5, 'published', '2026-03-31 16:06:28', '2026-03-31 16:09:17'),
(2, 6, 1, 'ААААААААААААААААААААА', 'aaaaaaaaaaaaaaaaaaaaa', 'ХАААЙ', 'cover_1774955443_05a2e9e9.jpg', 0, 6, 'published', '2026-03-31 16:10:43', '2026-03-31 16:35:00'),
(3, 6, 3, '123', '123', 'нуууу', 'cover_1774956987_5cdb813e.jpg', 1, 2, 'published', '2026-03-31 16:36:27', '2026-03-31 16:36:42');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 0xF09F9381
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Разработка', 'development', NULL),
(2, 'Кибербезопасность', 'cybersecurity', NULL),
(3, 'Искусственный интеллект', 'ai', NULL),
(4, 'Стартапы', 'startups', NULL),
(5, 'DevOps', 'devops', NULL),
(6, 'Мобильная разработка', 'mobile', NULL),
(7, 'Data Science', 'data-science', NULL),
(8, 'Новости IT', 'it-news', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `article_id`, `content`, `created_at`) VALUES
(2, 6, 2, 'ооо какой страшный', '2026-03-31 16:35:13');

-- --------------------------------------------------------

--
-- Структура таблицы `complaints`
--

CREATE TABLE `complaints` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `reason` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','reviewed','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `likes`
--

CREATE TABLE `likes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `article_id`, `created_at`) VALUES
(1, 6, 2, '2026-03-31 16:34:47'),
(2, 6, 3, '2026-03-31 16:36:29');

-- --------------------------------------------------------

--
-- Структура таблицы `supports`
--

CREATE TABLE `supports` (
  `id` int NOT NULL,
  `supporter_id` int NOT NULL,
  `author_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('created','paid','cancelled','error') COLLATE utf8mb4_unicode_ci DEFAULT 'created',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `supports`
--

INSERT INTO `supports` (`id`, `supporter_id`, `author_id`, `amount`, `payment_id`, `status`, `created_at`, `paid_at`) VALUES
(1, 6, 5, 100.00, NULL, 'error', '2026-03-31 16:10:58', NULL),
(2, 6, 5, 1000.00, NULL, 'error', '2026-03-31 16:11:02', NULL),
(3, 6, 5, 1000.00, NULL, 'error', '2026-03-31 16:19:12', NULL),
(4, 6, 5, 100.00, NULL, 'error', '2026-03-31 16:19:14', NULL),
(5, 6, 5, 100.00, NULL, 'error', '2026-03-31 16:19:16', NULL),
(6, 6, 5, 100.00, NULL, 'error', '2026-03-31 16:19:47', NULL),
(7, 6, 5, 100.00, NULL, 'created', '2026-03-31 16:25:51', NULL),
(8, 6, 5, 300.00, NULL, 'created', '2026-03-31 16:25:52', NULL),
(9, 6, 5, 100.00, NULL, 'created', '2026-03-31 16:25:53', NULL),
(10, 6, 5, 100.00, NULL, 'created', '2026-03-31 16:26:10', NULL),
(11, 6, 5, 100.00, NULL, 'created', '2026-03-31 16:26:11', NULL),
(12, 6, 5, 100.00, NULL, 'created', '2026-03-31 16:27:29', NULL),
(13, 6, 5, 300.00, NULL, 'created', '2026-03-31 16:27:30', NULL),
(14, 6, 5, 300.00, NULL, 'created', '2026-03-31 16:35:26', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `social_vk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_tg` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_code` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_code_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `avatar`, `bio`, `social_vk`, `social_tg`, `social_github`, `is_verified`, `verification_token`, `remember_token`, `reset_code`, `reset_code_expires`, `created_at`, `updated_at`) VALUES
(5, 'admin', 'bobaaa910@gmail.com', '$2y$10$oL5A8XhHIsZr.3SSyGwL/u4ocEci0cNXQenA2VCg3W6mcVOQmwlpS', 'avatar_5_1774952419.jpg', 'Здравствуйте поставьте 5 пожалуйста', '', 'https://t.me/hazewara', '', 1, NULL, NULL, NULL, NULL, '2026-03-31 15:18:32', '2026-03-31 16:09:13'),
(6, 'admin2', 'zarilgo0@gmail.com', '$2y$10$OBKxtGn0BAVpHIZ5wNB9duq.AR7DRNAICVQc8eHikFA31GzIq8Sl6', 'avatar_6_1774955419.jpg', '((((((((((', '', '', '', 1, NULL, NULL, NULL, NULL, '2026-03-31 16:09:35', '2026-03-31 16:10:19');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_category` (`category_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Индексы таблицы `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Индексы таблицы `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Индексы таблицы `supports`
--
ALTER TABLE `supports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_supporter_author` (`supporter_id`,`author_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `supports`
--
ALTER TABLE `supports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `supports`
--
ALTER TABLE `supports`
  ADD CONSTRAINT `supports_ibfk_1` FOREIGN KEY (`supporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supports_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

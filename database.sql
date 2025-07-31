
--
-- Database: `dfw2`
--

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE `blocks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `block_type` varchar(50) DEFAULT 'html',
  `position` enum('left','right','footer','top','bottom','custom') DEFAULT 'right',
  `is_enabled` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `css_class` varchar(255) DEFAULT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`id`, `title`, `content`, `block_type`, `position`, `is_enabled`, `sort_order`, `css_class`, `settings`, `created_at`, `updated_at`) VALUES
(1, 'Welcome', '<p>Welcome to our forum! Join the discussion.</p>', 'html', 'right', 1, 0, NULL, '{}', '2025-07-29 06:11:07', '2025-07-29 06:11:07'),
(2, 'Recent Posts', '', 'recent_posts', 'right', 1, 0, NULL, '{\"limit\": 5}', '2025-07-29 06:11:07', '2025-07-29 06:11:07'),
(3, 'Navigation', '', 'menu', 'left', 1, 0, NULL, '{\"menu_type\": \"sidebar\", \"show_icons\": false}', '2025-07-29 06:11:07', '2025-07-29 06:11:07'),
(4, 'Site Statistics', '', 'user_stats', 'right', 1, 0, NULL, '{}', '2025-07-29 06:11:07', '2025-07-29 06:11:07'),
(5, 'Pages', '', 'pages_list', 'left', 1, 0, NULL, '{\"show_description\": true}', '2025-07-29 06:11:07', '2025-07-29 06:11:07'),
(6, 'Site Info', '<?php phpinfo(); ?>', 'custom_widget', 'left', 1, 0, '', '[]', '2025-07-29 09:47:54', '2025-07-29 09:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `block_cache`
--

CREATE TABLE `block_cache` (
  `cache_key` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `block_positions`
--

CREATE TABLE `block_positions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `block_positions`
--

INSERT INTO `block_positions` (`id`, `name`, `display_name`, `description`, `is_active`) VALUES
(1, 'left', 'Left Sidebar', 'Left sidebar blocks', 1),
(2, 'right', 'Right Sidebar', 'Right sidebar blocks', 1),
(3, 'top', 'Top Banner', 'Top of page blocks', 1),
(4, 'bottom', 'Bottom Banner', 'Bottom of page blocks', 1),
(5, 'footer', 'Footer', 'Footer blocks', 1),
(6, 'custom', 'Custom Position', 'Custom positioned blocks', 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `sort_order`) VALUES
(1, 'General Test', 'General Category', '2025-07-31 08:45:11', 1),
(2, 'Next Category', 'Next Category Description', '2025-07-31 08:48:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `level_name` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `thread_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 2, 'Test Notification', 1, '2025-07-28 11:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `description`, `slug`, `content`, `is_published`, `created_at`) VALUES
(1, 'Test Page', NULL, 'test', '<p><strong>Testing page content</strong></p>', 1, '2025-07-29 12:34:33'),
(2, 'About', NULL, 'about', '<p>This is the about page</p>', 1, '2025-07-29 12:39:58'),
(3, 'Contact', NULL, 'contact', '<p>Contact Us</p>', 1, '2025-07-29 15:52:35');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `thread_id`, `user_id`, `content`, `created_at`) VALUES
(1, 2, 2, 'test reply', '2025-07-28 08:40:49'),
(2, 3, 2, 'test reply', '2025-07-31 05:42:48'),
(3, 3, 2, 'test reply', '2025-07-31 08:30:40'),
(4, 4, 2, 'General Thread Reply', '2025-07-31 08:46:01');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `type` varchar(20) DEFAULT 'string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `type`) VALUES
('site_description', 'Deano\'s Simple CMS', 'string'),
('site_name', 'Deano\'s', 'string'),
('theme', 'default', 'string');

-- --------------------------------------------------------

--
-- Table structure for table `threads`
--

CREATE TABLE `threads` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `threads`
--

INSERT INTO `threads` (`id`, `category_id`, `user_id`, `title`, `content`, `created_at`, `is_pinned`) VALUES
(2, 0, 2, 'Test Post', '# PHP CMS with Forum, Admin Panel, User Roles, Theming, and File Downloads\r\n\r\nA lightweight content management system built with PHP and MySQL featuring:\r\n- User roles (`admin`, `moderator`, `user`)\r\n- Forum with categories, threads, and replies\r\n- Admin area for user and content management\r\n- File upload and download system\r\n- Theme support (with default Bootstrap theme)\r\n- Flash messaging and CSRF protection\r\n\r\n---\r\n\r\n## 🛠 Installation Instructions\r\n\r\n### 1. Clone the Repository\r\n\r\n```bash\r\ngit clone https://github.com/YOUR_USERNAME/php-cms-forum-system.git\r\ncd php-cms-forum-system\r\n```\r\n\r\n### 2. Set Up the Database\r\n\r\n- Import the `database.sql` file using phpMyAdmin or MySQL CLI:\r\n\r\n```bash\r\nmysql -u root -p cms_db < database.sql\r\n```\r\n\r\n- Create a MySQL user with access or update `includes/config.php` to use root.\r\n\r\n### 3. Configure the Project\r\n\r\n- Edit `includes/config.php` with your database credentials.\r\n- Ensure file write access to `/uploads/` and `/downloads/` directories.\r\n\r\n### 4. Start the Server\r\n\r\nUsing PHP’s built-in server:\r\n\r\n```bash\r\nphp -S localhost:8000\r\n```\r\n\r\nThen visit: [http://localhost:8000](http://localhost:8000)\r\n\r\n---\r\n\r\n## 🔐 Default Admin Account\r\n\r\n| Username | Password |\r\n|----------|----------|\r\n| admin    | admin123 |\r\n\r\nMake sure to change this after first login.\r\n\r\n---\r\n\r\n## 📂 Folder Structure\r\n\r\n```\r\n/admin       → Admin dashboard\r\n/auth        → Login, register, logout\r\n/forum       → Forum (categories, threads, replies)\r\n/includes    → Config, DB, auth helpers\r\n/themes      → Theming support\r\n/uploads     → File upload storage\r\n/downloads   → Secure download script\r\n```\r\n\r\n---\r\n\r\n## 📝 License\r\n\r\nMIT', '2025-07-28 07:17:24', 0),
(3, 2, 2, 'Test Thread', 'Test Content', '2025-07-31 05:42:32', 0),
(4, 1, 2, 'General Thread', 'General Thread Comment', '2025-07-31 08:45:48', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','moderator','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'deano', '$2y$10$jKvEIlFMbZKg1BTfK0FHGuqrL.AniRJZGB8iTTf5QPwRNhbZuC3Kq', 'admin', '2025-07-28 07:10:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `block_cache`
--
ALTER TABLE `block_cache`
  ADD PRIMARY KEY (`cache_key`);

--
-- Indexes for table `block_positions`
--
ALTER TABLE `block_positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `thread_id` (`thread_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier_time` (`identifier`,`created_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `threads`
--
ALTER TABLE `threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `block_positions`
--
ALTER TABLE `block_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `threads`
--
ALTER TABLE `threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`thread_id`) REFERENCES `threads` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `threads` (`id`),
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `threads`
--
ALTER TABLE `threads`
  ADD CONSTRAINT `threads_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `threads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
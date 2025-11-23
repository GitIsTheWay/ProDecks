-- ProDecks Complete Database Setup - Advanced Codecks-like System
SET FOREIGN_KEY_CHECKS = 0;

-- جدول کاربران
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `experience` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول Spaces
CREATE TABLE IF NOT EXISTS `spaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT '#667eea',
  `access_type` enum('private','public') DEFAULT 'private',
  `invite_code` varchar(8) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invite_code` (`invite_code`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول اعضای Spaces
CREATE TABLE IF NOT EXISTS `space_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `space_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','admin','member') DEFAULT 'member',
  `joined_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_space_member` (`space_id`,`user_id`),
  KEY `space_id` (`space_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول Decks
CREATE TABLE IF NOT EXISTS `decks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `space_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `color` varchar(7) DEFAULT '#4a5568',
  `position` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `space_id` (`space_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول Cards (با پشتیبانی از Subcards)
CREATE TABLE IF NOT EXISTS `cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deck_id` int(11) NOT NULL,
  `parent_card_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `assignee_id` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('todo','in_progress','done') DEFAULT 'todo',
  `due_date` date DEFAULT NULL,
  `time_estimate` decimal(4,2) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `deck_id` (`deck_id`),
  KEY `parent_card_id` (`parent_card_id`),
  KEY `assignee_id` (`assignee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول پروژه‌ها (برای سازگاری)
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `dev_type` enum('solo','indie','aaa') DEFAULT 'solo',
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول اعضای پروژه (برای سازگاری)
CREATE TABLE IF NOT EXISTS `project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','member') DEFAULT 'member',
  `joined_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول دستاوردها
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `achievement_type` varchar(50) NOT NULL,
  `achieved_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- اضافه کردن Constraints
ALTER TABLE `spaces`
  ADD CONSTRAINT `spaces_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `space_members`
  ADD CONSTRAINT `space_members_ibfk_1` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `space_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `decks`
  ADD CONSTRAINT `decks_ibfk_1` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON DELETE CASCADE;

ALTER TABLE `cards`
  ADD CONSTRAINT `cards_ibfk_1` FOREIGN KEY (`deck_id`) REFERENCES `decks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cards_ibfk_2` FOREIGN KEY (`parent_card_id`) REFERENCES `cards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cards_ibfk_3` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- ایجاد ایندکس‌های بهینه
CREATE INDEX `idx_space_members_space` ON `space_members` (`space_id`);
CREATE INDEX `idx_space_members_user` ON `space_members` (`user_id`);
CREATE INDEX `idx_decks_space` ON `decks` (`space_id`);
CREATE INDEX `idx_cards_deck` ON `cards` (`deck_id`);
CREATE INDEX `idx_cards_parent` ON `cards` (`parent_card_id`);
CREATE INDEX `idx_cards_assignee` ON `cards` (`assignee_id`);
CREATE INDEX `idx_user_achievements_user` ON `user_achievements` (`user_id`);

-- درج داده‌های اولیه
INSERT IGNORE INTO `user_achievements` (`achievement_type`) VALUES 
('first_registration'),
('first_project'),
('first_card'),
('card_completed'),
('level_5'),
('level_10'),
('team_member'),
('space_creator'),
('deck_master'),
('card_expert');

SET FOREIGN_KEY_CHECKS = 1;
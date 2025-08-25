-- Create database first (if not created):
-- CREATE DATABASE `todo_app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `todo_app`;

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `due_date` DATE NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_completed` (`is_completed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

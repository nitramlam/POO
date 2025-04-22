SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS `musculation_db`;
CREATE DATABASE `musculation_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `musculation_db`;

-- --------------------------------------------------------

-- Table `users`
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Données initiales
INSERT INTO `users` (`id`, `name`, `token`, `token_expiry`) VALUES
(1, 'Martin', '4c81aadceffef090090e6fd3457e8379', NULL),
(2, 'Olivia', 'ff77f51ff9083ee9fcec27e989844bfa', NULL);

-- --------------------------------------------------------

-- Table `sessions`
CREATE TABLE `sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

-- Table `exercises`
CREATE TABLE `exercises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `repetitions` int DEFAULT NULL,
  `sets` int DEFAULT NULL,
  `target_weight` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
-- --------------------------------------------------------

-- Table `exercises_sessions`
CREATE TABLE `exercises_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exercise_id` int DEFAULT NULL,
  `session_id` int DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `repetitions` int DEFAULT NULL,
  `sets` int DEFAULT NULL,
  `target_weight` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exercise_id` (`exercise_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `exercises_sessions_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exercises_sessions_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;
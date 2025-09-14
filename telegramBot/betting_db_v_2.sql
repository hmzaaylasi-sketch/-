-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for betting_db
CREATE DATABASE IF NOT EXISTS `betting_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `betting_db`;

-- Dumping structure for table betting_db.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.admins: ~0 rows (approximately)
DELETE FROM `admins`;
INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `created_at`) VALUES
	(1, 'admin', '$2y$10$BMPADlHUt3UspCIDnKeblOpNkYIAsgAbLwgqbrTJpx./nmMw/gufm', '2025-09-12 20:23:39');

-- Dumping structure for table betting_db.horses
CREATE TABLE IF NOT EXISTS `horses` (
  `horse_id` int NOT NULL AUTO_INCREMENT,
  `horse_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int NOT NULL,
  `owner` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trainer` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jockey` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('ذكر','أنثى') COLLATE utf8mb4_unicode_ci DEFAULT 'ذكر',
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`horse_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.horses: ~0 rows (approximately)
DELETE FROM `horses`;
INSERT INTO `horses` (`horse_id`, `horse_name`, `age`, `owner`, `trainer`, `jockey`, `gender`, `color`, `created_at`) VALUES
	(1, 'NADINE', 5, 'Ecole Royale de Cavalerie', 'ADERCHI EL MOSTAFA', 'OTHMANE SEKKOUTI', 'ذكر', '', '2025-09-12 22:16:48');

-- Dumping structure for table betting_db.horse_bets
CREATE TABLE IF NOT EXISTS `horse_bets` (
  `bet_id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `race_id` int NOT NULL,
  `bet_type` enum('win','exacta','trifecta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'win',
  `odds` decimal(5,2) DEFAULT '2.50',
  `bet_numbers` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stake` decimal(10,2) NOT NULL,
  `multiplier` int DEFAULT '1',
  `total_stake` decimal(10,2) NOT NULL DEFAULT '0.00',
  `potential_return` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','won','lost') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `potential_payout` decimal(10,2) DEFAULT '0.00',
  `bet_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bet_id`),
  KEY `user_id` (`user_id`),
  KEY `horse_bets_ibfk_2` (`race_id`),
  CONSTRAINT `horse_bets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `horse_bets_ibfk_2` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.horse_bets: ~0 rows (approximately)
DELETE FROM `horse_bets`;

-- Dumping structure for table betting_db.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `method_id` int NOT NULL AUTO_INCREMENT,
  `method_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_type` enum('crypto','bank','ewallet') COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `wallet_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `memo_tag` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `binance_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `binance_api_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `binance_merchant_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rib_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'MAD',
  PRIMARY KEY (`method_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.payment_methods: ~2 rows (approximately)
DELETE FROM `payment_methods`;
INSERT INTO `payment_methods` (`method_id`, `method_name`, `method_type`, `details`, `wallet_address`, `memo_tag`, `bank_account`, `logo`, `status`, `binance_api_key`, `binance_api_secret`, `binance_merchant_id`, `bank_name`, `account_holder_name`, `account_number`, `rib_key`, `currency`) VALUES
	(1, 'التحويل البنكي', 'bank', 'قم بالإيداع عبر التحويل البنكي إلى الحساب التالي. ثم أرفق إثبات التحويل.', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, 'البنك المغربي للتجارة الخارجية', 'اسمك بالكامل', '123456789012345', '12345678901', 'MAD'),
	(2, 'USDT (TRC20)', 'crypto', 'ارسل USDT إلى المحفظة التالية (شبكة TRC20 فقط)', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'USDT');

-- Dumping structure for table betting_db.races
CREATE TABLE IF NOT EXISTS `races` (
  `race_id` int NOT NULL AUTO_INCREMENT,
  `meeting_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `race_number` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `race_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `distance` int DEFAULT NULL,
  `prize_pool` decimal(12,2) DEFAULT NULL,
  `status` enum('upcoming','running','finished','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'upcoming',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`race_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.races: ~0 rows (approximately)
DELETE FROM `races`;
INSERT INTO `races` (`race_id`, `meeting_code`, `race_number`, `race_name`, `location`, `start_time`, `distance`, `prize_pool`, `status`, `created_at`) VALUES
	(1, 'R1', 'C6', 'Société des courses: الرباط', 'KHEMISSET', '2025-09-13 01:26:00', 0, 12000.00, 'upcoming', '2025-09-12 22:24:26');

-- Dumping structure for table betting_db.race_entries
CREATE TABLE IF NOT EXISTS `race_entries` (
  `entry_id` int NOT NULL AUTO_INCREMENT,
  `race_id` int NOT NULL,
  `horse_number` int NOT NULL,
  `horse_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sar_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jockey` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trainer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origins` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gains` decimal(12,2) DEFAULT NULL,
  `performances` text COLLATE utf8mb4_unicode_ci,
  `odds` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','running','finished','scratched') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `final_position` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`entry_id`),
  KEY `race_id` (`race_id`),
  CONSTRAINT `race_entries_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.race_entries: ~0 rows (approximately)
DELETE FROM `race_entries`;
INSERT INTO `race_entries` (`entry_id`, `race_id`, `horse_number`, `horse_name`, `sar_code`, `color_code`, `weight`, `jockey`, `trainer`, `owner`, `origins`, `gains`, `performances`, `odds`, `status`, `final_position`, `created_at`) VALUES
	(1, 1, 1, 'NADINE', NULL, NULL, NULL, 'OTHMANE SEKKOUTI', 'ADERCHI EL MOSTAFA', 'Ecole Royale de Cavalerie', NULL, NULL, NULL, NULL, 'scheduled', NULL, '2025-09-12 22:24:26');

-- Dumping structure for table betting_db.race_results
CREATE TABLE IF NOT EXISTS `race_results` (
  `id` int NOT NULL AUTO_INCREMENT,
  `race_id` int NOT NULL,
  `horse_number` int DEFAULT NULL,
  `position` int DEFAULT NULL,
  `final_order` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `race_id` (`race_id`,`horse_number`),
  CONSTRAINT `race_results_ibfk_1` FOREIGN KEY (`race_id`) REFERENCES `races` (`race_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.race_results: ~0 rows (approximately)
DELETE FROM `race_results`;

-- Dumping structure for table betting_db.referrals
CREATE TABLE IF NOT EXISTS `referrals` (
  `referral_id` int NOT NULL AUTO_INCREMENT,
  `referrer_id` bigint NOT NULL,
  `referred_id` bigint NOT NULL,
  `referral_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bonus` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`referral_id`),
  KEY `fk_referrer` (`referrer_id`),
  KEY `fk_referred` (`referred_id`),
  CONSTRAINT `fk_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.referrals: ~0 rows (approximately)
DELETE FROM `referrals`;

-- Dumping structure for table betting_db.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `method_id` int NOT NULL,
  `type` enum('deposit','withdraw') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(18,8) NOT NULL,
  `currency` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'USDT',
  `converted_amount` decimal(18,2) NOT NULL,
  `proof_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','processing') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`),
  KEY `method_id` (`method_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`method_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.transactions: ~0 rows (approximately)
DELETE FROM `transactions`;

-- Dumping structure for table betting_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` decimal(10,2) DEFAULT '0.00',
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table betting_db.users: ~0 rows (approximately)
DELETE FROM `users`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

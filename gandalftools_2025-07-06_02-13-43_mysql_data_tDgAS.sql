/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.10-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: gandalftools
-- ------------------------------------------------------
-- Server version	10.11.10-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES
(1,'admin','admin@example.com','$2a$12$.0V6LG2CzypBOoAC.yksFeFgSjenT5x/lT0H0vWJ5Yllis7p83smO');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_api_usage`
--

DROP TABLE IF EXISTS `customer_api_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_api_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `usage_date` date NOT NULL,
  `request_count` int(11) NOT NULL DEFAULT 0,
  `last_request_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_customer_date` (`customer_id`,`usage_date`),
  KEY `idx_customer_api_usage_customer_date` (`customer_id`,`usage_date`),
  CONSTRAINT `customer_api_usage_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=488328 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_api_usage`
--

LOCK TABLES `customer_api_usage` WRITE;
/*!40000 ALTER TABLE `customer_api_usage` DISABLE KEYS */;
INSERT INTO `customer_api_usage` VALUES
(5,1,'2025-06-13',304,'2025-06-13 13:26:40'),
(36,10,'2025-06-13',11,'2025-06-13 12:34:31'),
(320,1,'2025-06-14',75,'2025-06-14 13:53:03'),
(480,10,'2025-06-14',3849,'2025-06-14 16:00:03'),
(8176,10,'2025-06-15',15002,'2025-06-15 13:41:47'),
(30476,6,'2025-06-15',15002,'2025-06-15 11:59:41'),
(68180,10,'2025-06-16',15001,'2025-06-16 15:43:32'),
(98180,1,'2025-06-17',10,'2025-06-16 16:16:05'),
(98198,10,'2025-06-17',15001,'2025-06-16 22:31:08'),
(128198,6,'2025-06-17',5088,'2025-06-17 15:59:58'),
(138372,6,'2025-06-18',9882,'2025-06-17 17:36:12'),
(148617,10,'2025-06-18',15000,'2025-06-18 07:46:44'),
(188132,10,'2025-06-20',14993,'2025-06-19 20:01:58'),
(218116,10,'2025-06-21',15002,'2025-06-21 15:21:16'),
(248118,10,'2025-06-22',11657,'2025-06-21 19:05:32'),
(271430,10,'2025-06-23',14994,'2025-06-22 23:51:55'),
(301416,6,'2025-06-23',449,'2025-06-23 13:48:13'),
(302312,15,'2025-06-24',268,'2025-06-24 04:31:18'),
(302329,10,'2025-06-24',15001,'2025-06-23 22:19:04'),
(305629,1,'2025-06-24',307,'2025-06-24 09:56:45'),
(317551,6,'2025-06-24',1258,'2025-06-24 09:03:00'),
(319401,6,'2025-06-25',2681,'2025-06-25 00:04:30'),
(324761,15,'2025-06-25',6,'2025-06-25 06:20:26'),
(324769,1,'2025-06-25',7,'2025-06-25 06:29:24'),
(324789,7,'2025-06-25',15000,'2025-06-25 13:16:34'),
(354788,10,'2025-06-25',2684,'2025-06-25 15:59:59'),
(360156,10,'2025-06-26',14872,'2025-06-26 15:59:55'),
(384748,6,'2025-06-26',2279,'2025-06-26 06:17:14'),
(389306,15,'2025-06-26',123,'2025-06-26 14:25:05'),
(389429,8,'2025-06-26',130,'2025-06-26 15:40:58'),
(394841,10,'2025-06-27',12413,'2025-06-26 19:50:13'),
(410494,7,'2025-06-27',1828,'2025-06-27 11:15:21'),
(410633,1,'2025-06-27',67,'2025-06-26 18:27:24'),
(419861,8,'2025-06-27',233,'2025-06-27 11:35:43'),
(423923,10,'2025-06-28',9991,'2025-06-27 20:23:51'),
(443905,1,'2025-06-30',21,'2025-06-30 15:31:48'),
(443948,1,'2025-07-01',6,'2025-06-30 16:26:10'),
(443960,8,'2025-07-01',47,'2025-06-30 16:49:01'),
(444054,17,'2025-07-01',10000,'2025-06-30 22:18:01'),
(444055,15,'2025-07-02',1,'2025-07-02 08:49:30'),
(444056,1,'2025-07-03',2,'2025-07-03 15:59:42'),
(444058,1,'2025-07-04',7,'2025-07-03 16:07:26'),
(444065,15,'2025-07-05',1,'2025-07-04 17:36:04'),
(444066,1,'2025-07-05',7,'2025-07-05 01:50:57'),
(444071,10,'2025-07-05',15000,'2025-07-05 00:19:11'),
(459116,19,'2025-07-05',14503,'2025-07-05 10:18:15'),
(488172,1,'2025-07-06',32,'2025-07-05 18:46:31'),
(488175,15,'2025-07-06',2,'2025-07-05 18:00:59'),
(488206,10,'2025-07-06',62,'2025-07-05 19:13:42');
/*!40000 ALTER TABLE `customer_api_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(64) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `daily_request_limit` int(15) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_check_server` varchar(255) NOT NULL DEFAULT 'check.php',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `service_type` varchar(50) NOT NULL DEFAULT 'email_bounce',
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_customers_api_key` (`api_key`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'dadamin','admin',NULL,99999999,1,'card.php','2025-06-12 13:28:53','2025-07-05 18:09:21','card_check'),
(6,'9272f984effd9085','jaupan',NULL,15000,1,'check2.php','2025-06-13 04:57:48','2025-06-13 15:01:15','email_bounce'),
(7,'54b671914a5d666a','luffy',NULL,15000,1,'check2.php','2025-06-13 05:08:58','2025-07-04 18:46:02','email_bounce'),
(8,'a0379a8fd62b5ca2','boalin',NULL,20000,1,'check2.php','2025-06-13 05:09:09','2025-06-30 15:44:24','email_bounce'),
(9,'85d8c96d5fc0b87f','bagus',NULL,10000,1,'check2.php','2025-06-13 05:09:16','2025-06-15 09:16:30','email_bounce'),
(10,'4c851eda1a59a763','kojo',NULL,15000,1,'check2.php','2025-06-13 05:09:36','2025-06-13 11:56:21','email_bounce'),
(15,'e986382370a47610','luffy',NULL,50000,1,'email_provider_check.php','2025-06-23 19:57:05','2025-06-24 04:21:32','email_provider'),
(17,'eac31442fc7fc185','boalin',NULL,10000,1,'check.php','2025-06-30 18:19:19','2025-07-04 18:04:52','email_bounce'),
(19,'d128ae30795f689d','kaptain',NULL,15000,1,'check2.php','2025-07-05 01:35:38','2025-07-05 17:50:15','email_bounce');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_name` (`product_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,'email_bounce','Layanan validasi alamat email secara real-time untuk mengurangi bounce rate dan meningkatkan deliverability.',1500000.00,1,'2025-07-04 19:01:03','2025-07-04 19:02:59'),
(2,'email_provider','Lengkapi data customer Anda dengan informasi demografis dan firmografis untuk analisis yang lebih dalam.',750000.00,1,'2025-07-04 19:01:03','2025-07-04 19:27:36'),
(3,'sms_gateway','Kirim notifikasi, OTP, dan pesan marketing ke customer Anda melalui API SMS yang andal.',1500000.00,1,'2025-07-04 19:01:03','2025-07-05 17:45:24'),
(4,'card_check','to verify card declined or not ( 100000 you will get 150 credit )',100000.00,1,'2025-07-04 19:34:24','2025-07-04 19:34:24'),
(5,'amazon_check','Verifying email registered to amazon',1500000.00,1,'2025-07-04 19:35:35','2025-07-04 19:35:35'),
(6,'spotify_check','Verifying email registered to spotify',800000.00,1,'2025-07-04 19:35:57','2025-07-04 19:35:57'),
(7,'coinbase_check','Verifying an email registered to coinbase',10000000.00,1,'2025-07-05 17:42:21','2025-07-05 17:42:31');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES
('admin_email_notifications','admin@contoh.com'),
('app_name','Gandalf Tools'),
('default_api_limit','15000'),
('maintenance_mode_message','Our services is in maintenance. We will come back soon.'),
('maintenance_mode_status','0');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'gandalftools'
--

--
-- Dumping routines for database 'gandalftools'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-06  2:13:43

-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: laravel
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `announcement_announcement_label`
--

DROP TABLE IF EXISTS `announcement_announcement_label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_announcement_label` (
  `announcement_id` bigint(20) unsigned NOT NULL,
  `announcement_label_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`announcement_id`,`announcement_label_id`),
  KEY `announcement_announcement_label_announcement_label_id_foreign` (`announcement_label_id`),
  CONSTRAINT `announcement_announcement_label_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `announcement_announcement_label_announcement_label_id_foreign` FOREIGN KEY (`announcement_label_id`) REFERENCES `announcement_labels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement_announcement_label`
--

LOCK TABLES `announcement_announcement_label` WRITE;
/*!40000 ALTER TABLE `announcement_announcement_label` DISABLE KEYS */;
INSERT INTO `announcement_announcement_label` VALUES (3,2);
/*!40000 ALTER TABLE `announcement_announcement_label` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcement_labels`
--

DROP TABLE IF EXISTS `announcement_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement_labels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL DEFAULT 'bg-gray-100 text-gray-800',
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_labels_name_unique` (`name`),
  UNIQUE KEY `announcement_labels_slug_unique` (`slug`),
  KEY `announcement_labels_created_by_foreign` (`created_by`),
  CONSTRAINT `announcement_labels_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement_labels`
--

LOCK TABLES `announcement_labels` WRITE;
/*!40000 ALTER TABLE `announcement_labels` DISABLE KEYS */;
INSERT INTO `announcement_labels` VALUES (2,'Health','health','bg-green-100 text-green-800',2,'2026-02-16 02:44:32','2026-02-16 02:44:32');
/*!40000 ALTER TABLE `announcement_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcements_slug_unique` (`slug`),
  KEY `announcements_user_id_foreign` (`user_id`),
  CONSTRAINT `announcements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'New Logo','new-logo','For testing only','announcements/40gDiaC6FcGEzrOhzKhCmYtZSiBDn9r3eCHNfEyq.png','2026-02-03 05:08:51',1,'approved',2,'2026-02-03 05:08:51','2026-02-03 05:08:51',NULL),(2,'dasdsa','dasdsa','dasdsadasfsajiofasopfiaspfiopa','announcements/rUVaW6fKSQyM5qAaSZIB1PqFrmi0i0xbAy9gfVFL.png','2026-02-16 02:36:23',1,'approved',2,'2026-02-16 02:36:23','2026-02-16 02:36:23',NULL),(3,'gfdslkgldsgldskgldsk','gfdslkgldsgldskgldsk','sadasdas','announcements/HuZci7fwKrN0vq5UuF2da1sXpdEkNpjCL2hpPlyR.png','2026-03-05 03:15:02',1,'approved',2,'2026-02-16 02:45:03','2026-03-05 03:15:02',NULL);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `approval_logs`
--

DROP TABLE IF EXISTS `approval_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approval_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `performed_by` bigint(20) unsigned NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_logs_user_id_foreign` (`user_id`),
  KEY `approval_logs_performed_by_foreign` (`performed_by`),
  CONSTRAINT `approval_logs_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `approval_logs`
--

LOCK TABLES `approval_logs` WRITE;
/*!40000 ALTER TABLE `approval_logs` DISABLE KEYS */;
INSERT INTO `approval_logs` VALUES (1,4,'rejected',2,NULL,'2026-02-04 03:15:30','2026-02-04 03:15:30'),(2,5,'approved',2,NULL,'2026-02-04 05:16:04','2026-02-04 05:16:04'),(3,6,'approved',3,NULL,'2026-02-06 03:39:10','2026-02-06 03:39:10'),(4,7,'rejected',2,NULL,'2026-02-09 05:24:00','2026-02-09 05:24:00'),(5,8,'approved',2,NULL,'2026-02-23 20:40:28','2026-02-23 20:40:28'),(6,9,'approved',2,NULL,'2026-02-24 00:30:07','2026-02-24 00:30:07');
/*!40000 ALTER TABLE `approval_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(255) DEFAULT NULL,
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_target_type_target_id_index` (`target_type`,`target_id`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,2,'permissions_updated','User',3,'Updated module permissions for staff adada staff','127.0.0.1','2026-02-16 04:43:34','2026-02-16 04:43:34'),(2,2,'position_updated','User',3,'Changed position from none to Kagawad','127.0.0.1','2026-02-16 10:40:22','2026-02-16 10:40:22'),(3,2,'permissions_updated','User',3,'Updated module permissions for staff adada staff','127.0.0.1','2026-02-16 10:40:29','2026-02-16 10:40:29'),(4,2,'role_updated','User',6,'Changed role from resident to admin','127.0.0.1','2026-02-16 22:50:03','2026-02-16 22:50:03'),(5,2,'role_updated','User',6,'Changed role from admin to resident','127.0.0.1','2026-02-16 22:50:35','2026-02-16 22:50:35'),(6,2,'official_appointed','Official',1,'Appointed admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-17 17:13:47','2026-02-17 17:13:47'),(7,2,'admin_term_ended','User',2,'Admin official term ended/deactivated — position cleared but role preserved (manual review required)','127.0.0.1','2026-02-17 17:29:08','2026-02-17 17:29:08'),(8,2,'official_deactivated','Official',1,'Deactivated admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-17 17:29:08','2026-02-17 17:29:08'),(9,2,'admin_term_ended','User',2,'Admin official term ended/deactivated — position cleared but role preserved (manual review required)','127.0.0.1','2026-02-17 17:29:19','2026-02-17 17:29:19'),(10,2,'official_updated','Official',1,'Updated official record for admin ewan admin','127.0.0.1','2026-02-17 17:29:19','2026-02-17 17:29:19'),(11,2,'official_activated','Official',1,'Reactivated admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-17 17:29:24','2026-02-17 17:29:24'),(12,2,'permissions_updated','User',3,'Updated module permissions for staff adada staff','127.0.0.1','2026-02-20 05:13:45','2026-02-20 05:13:45'),(13,2,'blotter_uploaded','Blotter',1,'Uploaded blotter BLT-2026-00001','127.0.0.1','2026-02-22 08:40:18','2026-02-22 08:40:18'),(14,2,'role_updated','User',4,'Changed role from resident to admin','127.0.0.1','2026-02-23 20:12:29','2026-02-23 20:12:29'),(15,2,'role_updated','User',4,'Changed role from admin to resident','127.0.0.1','2026-02-23 20:12:45','2026-02-23 20:12:45'),(16,2,'role_updated','User',5,'Changed role from resident to admin','127.0.0.1','2026-02-23 20:28:13','2026-02-23 20:28:13'),(17,2,'position_updated','User',5,'Changed position from none to Barangay Secretary','127.0.0.1','2026-02-23 20:28:38','2026-02-23 20:28:38'),(18,2,'registration_status_changed','User',8,'Changed status to approved (bulk)','127.0.0.1','2026-02-23 20:40:28','2026-02-23 20:40:28'),(19,2,'admin_term_ended','User',2,'Admin official term ended/deactivated — position cleared but role preserved (manual review required)','127.0.0.1','2026-02-23 20:52:22','2026-02-23 20:52:22'),(20,2,'official_deactivated','Official',1,'Deactivated admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-23 20:52:22','2026-02-23 20:52:22'),(21,2,'role_updated','User',5,'Changed role from admin to resident','127.0.0.1','2026-02-23 20:53:27','2026-02-23 20:53:27'),(22,2,'official_activated','Official',1,'Reactivated admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-23 21:07:22','2026-02-23 21:07:22'),(23,2,'admin_term_ended','User',2,'Admin official term ended/deactivated — position cleared but role preserved (manual review required)','127.0.0.1','2026-02-23 21:07:38','2026-02-23 21:07:38'),(24,2,'official_deactivated','Official',1,'Deactivated admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-23 21:07:38','2026-02-23 21:07:38'),(25,2,'official_activated','Official',1,'Reactivated admin ewan admin as Barangay Chairman','127.0.0.1','2026-02-23 22:25:57','2026-02-23 22:25:57'),(26,2,'registration_status_changed','User',9,'Changed status to approved (bulk)','127.0.0.1','2026-02-24 00:30:07','2026-02-24 00:30:07'),(27,2,'certificate_approved','CertificateRequest',7,'Approved certificate #7 (Certificate of Indigency)','127.0.0.1','2026-02-24 00:34:14','2026-02-24 00:34:14'),(28,2,'blotter_request_approved','BlotterRequest',1,'Approved blotter request #1','127.0.0.1','2026-02-24 00:44:12','2026-02-24 00:44:12'),(29,2,'blotter_request_released','BlotterRequest',1,'Released blotter request #1','127.0.0.1','2026-02-24 00:44:21','2026-02-24 00:44:21'),(30,2,'certificate_approved','CertificateRequest',8,'Approved certificate #8 (Certificate of Indigency)','127.0.0.1','2026-02-25 00:33:19','2026-02-25 00:33:19'),(31,2,'certificate_released','CertificateRequest',8,'Released certificate #8 (Certificate of Indigency)','127.0.0.1','2026-02-25 00:36:27','2026-02-25 00:36:27'),(32,2,'certificate_approved','CertificateRequest',9,'Approved certificate #9 (Certificate of Indigency)','127.0.0.1','2026-02-25 00:42:40','2026-02-25 00:42:40'),(33,2,'certificate_released','CertificateRequest',9,'Released certificate #9 (Certificate of Indigency)','127.0.0.1','2026-02-25 00:46:33','2026-02-25 00:46:33'),(34,2,'certificate_approved','CertificateRequest',10,'Approved certificate #10 (Barangay Clearance)','127.0.0.1','2026-02-25 02:03:58','2026-02-25 02:03:58'),(35,2,'certificate_released','CertificateRequest',10,'Released certificate #10 (Barangay Clearance)','127.0.0.1','2026-02-25 02:10:36','2026-02-25 02:10:36'),(36,2,'certificate_approved','CertificateRequest',11,'Approved certificate #11 (Residency Certificate)','127.0.0.1','2026-02-25 03:21:27','2026-02-25 03:21:27'),(37,2,'certificate_released','CertificateRequest',7,'Released certificate #7 (Certificate of Indigency)','127.0.0.1','2026-02-26 09:55:04','2026-02-26 09:55:04'),(38,2,'announcement_rejected','Announcement',3,'Rejected announcement: gfdslkgldsgldskgldsk','127.0.0.1','2026-03-02 04:07:28','2026-03-02 04:07:28'),(39,2,'summon_created','Blotter',1,'Generated summon #1 for blotter BLT-2026-00001','127.0.0.1','2026-03-02 22:35:29','2026-03-02 22:35:29'),(40,2,'summon_printed','Blotter',1,'Printed summon #1 for blotter BLT-2026-00001','127.0.0.1','2026-03-02 22:35:36','2026-03-02 22:35:36'),(41,2,'blotter_uploaded','Blotter',2,'Uploaded blotter BLT-2026-00002','127.0.0.1','2026-03-02 22:42:43','2026-03-02 22:42:43'),(42,2,'permissions_updated','User',3,'Updated module permissions for staff adada staff','127.0.0.1','2026-03-03 05:11:37','2026-03-03 05:11:37'),(43,2,'official_slot_assigned','Official',2,'Assigned staff adada staff to Barangay Secretary (slot 1)','127.0.0.1','2026-03-05 03:12:35','2026-03-05 03:12:35'),(44,2,'announcement_approved','Announcement',3,'Approved announcement: gfdslkgldsgldskgldsk','127.0.0.1','2026-03-05 03:15:02','2026-03-05 03:15:02'),(45,2,'role_updated','User',6,'Changed role from resident to staff','127.0.0.1','2026-03-05 04:03:50','2026-03-05 04:03:50'),(46,2,'role_updated','User',6,'Changed role from staff to staff','127.0.0.1','2026-03-05 04:04:16','2026-03-05 04:04:16'),(47,2,'role_updated','User',6,'Changed role from staff to resident','127.0.0.1','2026-03-05 04:04:22','2026-03-05 04:04:22'),(48,2,'family_linked_admin','User',1,'Admin linked justinkim ella abarico to head admin ewan admin (previous head: none).','127.0.0.1','2026-03-05 04:23:20','2026-03-05 04:23:20'),(49,2,'backup_created',NULL,NULL,'Manual database backup triggered by admin','127.0.0.1','2026-03-05 04:53:31','2026-03-05 04:53:31'),(50,2,'family_unlinked_admin','User',1,'Admin unlinked justinkim ella abarico from head admin ewan admin.','127.0.0.1','2026-03-05 10:39:27','2026-03-05 10:39:27'),(51,2,'family_member_added','User',2,'Head admin ewan admin added family member alex admin admin (daughter).','127.0.0.1','2026-03-05 10:44:49','2026-03-05 10:44:49'),(52,2,'family_linked_admin','User',1,'Admin linked justinkim ella abarico to head dsadsa dsa dsa (previous head: none).','127.0.0.1','2026-03-05 11:02:08','2026-03-05 11:02:08'),(53,2,'certificate_template_updated','CertificateRequest',11,'Updated residency template fields for certificate request #11','127.0.0.1','2026-03-06 07:11:51','2026-03-06 07:11:51');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotter_entries`
--

DROP TABLE IF EXISTS `blotter_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotter_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_number` varchar(255) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time NOT NULL,
  `incident_type` varchar(255) NOT NULL,
  `narrative` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `purok_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `investigator_id` bigint(20) unsigned DEFAULT NULL,
  `encoded_by` bigint(20) unsigned NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blotter_entries_blotter_number_unique` (`blotter_number`),
  KEY `blotter_entries_purok_id_foreign` (`purok_id`),
  KEY `blotter_entries_investigator_id_foreign` (`investigator_id`),
  KEY `blotter_entries_encoded_by_foreign` (`encoded_by`),
  KEY `blotter_entries_status_index` (`status`),
  KEY `blotter_entries_incident_type_index` (`incident_type`),
  KEY `blotter_entries_incident_date_index` (`incident_date`),
  CONSTRAINT `blotter_entries_encoded_by_foreign` FOREIGN KEY (`encoded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blotter_entries_investigator_id_foreign` FOREIGN KEY (`investigator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blotter_entries_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotter_entries`
--

LOCK TABLES `blotter_entries` WRITE;
/*!40000 ALTER TABLE `blotter_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `blotter_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotter_parties`
--

DROP TABLE IF EXISTS `blotter_parties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotter_parties` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_entry_id` bigint(20) unsigned NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blotter_parties_blotter_entry_id_index` (`blotter_entry_id`),
  KEY `blotter_parties_role_index` (`role`),
  CONSTRAINT `blotter_parties_blotter_entry_id_foreign` FOREIGN KEY (`blotter_entry_id`) REFERENCES `blotter_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotter_parties`
--

LOCK TABLES `blotter_parties` WRITE;
/*!40000 ALTER TABLE `blotter_parties` DISABLE KEYS */;
/*!40000 ALTER TABLE `blotter_parties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotter_requests`
--

DROP TABLE IF EXISTS `blotter_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotter_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `purpose` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `rejection_reason_code` varchar(60) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blotter_requests_user_id_foreign` (`user_id`),
  KEY `blotter_requests_processed_by_foreign` (`processed_by`),
  KEY `blotter_requests_status_index` (`status`),
  KEY `blotter_requests_blotter_id_user_id_index` (`blotter_id`,`user_id`),
  KEY `blotter_requests_rejection_reason_code_index` (`rejection_reason_code`),
  CONSTRAINT `blotter_requests_blotter_id_foreign` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blotter_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blotter_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotter_requests`
--

LOCK TABLES `blotter_requests` WRITE;
/*!40000 ALTER TABLE `blotter_requests` DISABLE KEYS */;
INSERT INTO `blotter_requests` VALUES (1,1,9,'For hearing','released',2,'2026-02-24 00:44:21',NULL,NULL,'2026-02-24 00:42:50','2026-02-24 00:44:21');
/*!40000 ALTER TABLE `blotter_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotter_revisions`
--

DROP TABLE IF EXISTS `blotter_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotter_revisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_id` bigint(20) unsigned NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(40) NOT NULL DEFAULT 'updated',
  `change_note` text DEFAULT NULL,
  `changed_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changed_fields`)),
  `before_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_data`)),
  `after_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blotter_revisions_changed_by_foreign` (`changed_by`),
  KEY `blotter_revisions_blotter_id_created_at_index` (`blotter_id`,`created_at`),
  CONSTRAINT `blotter_revisions_blotter_id_foreign` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blotter_revisions_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotter_revisions`
--

LOCK TABLES `blotter_revisions` WRITE;
/*!40000 ALTER TABLE `blotter_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `blotter_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blotters`
--

DROP TABLE IF EXISTS `blotters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blotters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_number` varchar(255) NOT NULL,
  `complainant_name` varchar(255) NOT NULL,
  `complainant_user_id` bigint(20) unsigned DEFAULT NULL,
  `incident_date` date NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `handwritten_salaysay_path` varchar(255) DEFAULT NULL,
  `procedure_photo_path` varchar(255) DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `is_uncooperative` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blotters_blotter_number_unique` (`blotter_number`),
  KEY `blotters_uploaded_by_foreign` (`uploaded_by`),
  KEY `blotters_status_index` (`status`),
  KEY `blotters_incident_date_index` (`incident_date`),
  KEY `blotters_complainant_user_id_foreign` (`complainant_user_id`),
  CONSTRAINT `blotters_complainant_user_id_foreign` FOREIGN KEY (`complainant_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blotters_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blotters`
--

LOCK TABLES `blotters` WRITE;
/*!40000 ALTER TABLE `blotters` DISABLE KEYS */;
INSERT INTO `blotters` VALUES (1,'BLT-2026-00001','admin ewan admin',NULL,'2025-08-08','blotters/68TAJPcOO9n3EzOfZHsBv6V98HZ0yEQDq3mtHCG8.png',NULL,NULL,2,'kjo','active',0,'2026-02-22 08:40:18','2026-02-22 08:40:18',NULL),(2,'BLT-2026-00002','Lance De Leon Apay',NULL,'2026-03-02','blotters/bvPoudxfvsl7ltXnr34jiKKM7hsGfy8p4UMhVoqc.png',NULL,NULL,2,'Additional Blotter Details:\n- Complainant Age: 22\n- Complainant Contact: 09516986417\n- Complainant Address: 8 Don Alfredo Street Purok 1\n- Respondent Name: John Daryll Maglantay Vargas\n- Respondent Residence: 10 Don Alfredo Purok 1\n- Witness: Justinkim Abarico\n- Witness Contact: 09000000000\n- Scheduled Hearing Date: 2026-03-12','active',0,'2026-03-02 22:42:43','2026-03-02 22:42:43',NULL);
/*!40000 ALTER TABLE `blotters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificate_requests`
--

DROP TABLE IF EXISTS `certificate_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificate_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `certificate_type` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `extra_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_fields`)),
  `residency_years_text` varchar(255) DEFAULT NULL,
  `certificate_name_override` varchar(255) DEFAULT NULL,
  `certificate_address_override` varchar(255) DEFAULT NULL,
  `certificate_issued_on` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `released_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `certificate_requests_user_id_foreign` (`user_id`),
  KEY `certificate_requests_released_by_foreign` (`released_by`),
  CONSTRAINT `certificate_requests_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `certificate_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificate_requests`
--

LOCK TABLES `certificate_requests` WRITE;
/*!40000 ALTER TABLE `certificate_requests` DISABLE KEYS */;
INSERT INTO `certificate_requests` VALUES (1,1,'Barangay Clearance','for work in mcdonalds',NULL,NULL,NULL,NULL,NULL,'rejected',NULL,NULL,NULL,'2026-01-31 21:48:56','2026-01-31 21:50:05'),(2,1,'Barangay Clearance','for work ako ih',NULL,NULL,NULL,NULL,NULL,'released',NULL,'2026-02-06 05:10:26',2,'2026-01-31 22:07:52','2026-02-06 05:10:26'),(3,1,'Barangay Certificate','asdasdsa',NULL,NULL,NULL,NULL,NULL,'rejected',NULL,NULL,NULL,'2026-01-31 22:08:21','2026-01-31 22:09:14'),(4,2,'Residency Certificate','jo',NULL,NULL,NULL,NULL,NULL,'released',NULL,'2026-02-06 05:10:23',2,'2026-02-06 05:09:57','2026-02-06 05:10:23'),(5,2,'Certificate of Indigency','ojko',NULL,NULL,NULL,NULL,NULL,'approved',NULL,NULL,NULL,'2026-02-06 05:10:50','2026-02-06 05:11:19'),(6,2,'Certificate of Indigency','dsa',NULL,NULL,NULL,NULL,NULL,'rejected','jk',NULL,NULL,'2026-02-09 04:34:37','2026-02-09 07:42:55'),(7,9,'Certificate of Indigency','employment',NULL,NULL,NULL,NULL,NULL,'released',NULL,'2026-02-26 09:55:04',2,'2026-02-24 00:33:15','2026-02-26 09:55:04'),(8,9,'Certificate of Indigency','dasdasdadsa',NULL,NULL,NULL,NULL,NULL,'released',NULL,'2026-02-25 00:36:27',2,'2026-02-25 00:32:35','2026-02-25 00:36:27'),(9,2,'Certificate of Indigency','dsadaa',NULL,NULL,NULL,NULL,NULL,'released',NULL,'2026-02-25 00:46:33',2,'2026-02-25 00:42:30','2026-02-25 00:46:33'),(10,9,'Barangay Clearance','for work','{\"purpose\":\"for work\",\"valid_id_path\":\"certificates\\/ids\\/Ip4HyTTCSbSNjAQPLoy3YYE1jiLwsqnnd2da6xp1.jpg\"}',NULL,NULL,NULL,NULL,'released',NULL,'2026-02-25 02:10:36',2,'2026-02-25 02:03:41','2026-02-25 02:10:36'),(11,2,'Residency Certificate','Job Application','{\"purpose\":\"Job Application\",\"residency_start_year\":\"2024\"}','3 years up to present',NULL,NULL,'2026-03-06','approved',NULL,NULL,NULL,'2026-02-25 03:21:18','2026-03-06 07:11:51');
/*!40000 ALTER TABLE `certificate_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaint_notes`
--

DROP TABLE IF EXISTS `complaint_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaint_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `issue_report_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complaint_notes_user_id_foreign` (`user_id`),
  KEY `complaint_notes_issue_report_id_index` (`issue_report_id`),
  CONSTRAINT `complaint_notes_issue_report_id_foreign` FOREIGN KEY (`issue_report_id`) REFERENCES `issue_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complaint_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaint_notes`
--

LOCK TABLES `complaint_notes` WRITE;
/*!40000 ALTER TABLE `complaint_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaint_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `family_members`
--

DROP TABLE IF EXISTS `family_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `family_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `head_user_id` bigint(20) unsigned NOT NULL,
  `household_id` bigint(20) unsigned NOT NULL,
  `linked_user_id` bigint(20) unsigned DEFAULT NULL,
  `purok_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `age` tinyint(3) unsigned DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `relationship_to_head` varchar(255) DEFAULT NULL,
  `house_no` varchar(255) DEFAULT NULL,
  `street_name` varchar(255) DEFAULT NULL,
  `purok` varchar(255) DEFAULT NULL,
  `resident_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_members_household_id_foreign` (`household_id`),
  KEY `family_members_purok_id_foreign` (`purok_id`),
  KEY `family_members_head_user_id_household_id_index` (`head_user_id`,`household_id`),
  KEY `family_members_relationship_to_head_index` (`relationship_to_head`),
  KEY `family_members_linked_user_id_foreign` (`linked_user_id`),
  CONSTRAINT `family_members_head_user_id_foreign` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_members_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_members_linked_user_id_foreign` FOREIGN KEY (`linked_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `family_members_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_members`
--

LOCK TABLES `family_members` WRITE;
/*!40000 ALTER TABLE `family_members` DISABLE KEYS */;
INSERT INTO `family_members` VALUES (1,2,1,NULL,2,'alex','admin','admin',NULL,'2026-03-05',0,'female',NULL,'daughter','355','araymo','Purok 2','permanent','2026-03-05 10:44:49','2026-03-05 10:44:49',NULL);
/*!40000 ALTER TABLE `family_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearing_reschedules`
--

DROP TABLE IF EXISTS `hearing_reschedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearing_reschedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hearing_id` bigint(20) unsigned NOT NULL,
  `old_hearing_date` date NOT NULL,
  `old_hearing_time` time NOT NULL,
  `new_hearing_date` date NOT NULL,
  `new_hearing_time` time NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hearing_reschedules_hearing_id_foreign` (`hearing_id`),
  CONSTRAINT `hearing_reschedules_hearing_id_foreign` FOREIGN KEY (`hearing_id`) REFERENCES `hearings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearing_reschedules`
--

LOCK TABLES `hearing_reschedules` WRITE;
/*!40000 ALTER TABLE `hearing_reschedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearing_reschedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hearings`
--

DROP TABLE IF EXISTS `hearings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hearings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_id` bigint(20) unsigned NOT NULL,
  `summon_id` bigint(20) unsigned NOT NULL,
  `hearing_date` date NOT NULL,
  `hearing_time` time NOT NULL,
  `lupon_assigned` varchar(255) NOT NULL,
  `complainant_attendance` varchar(255) DEFAULT NULL,
  `respondent_attendance` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'scheduled',
  `result` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hearings_summon_id_unique` (`summon_id`),
  KEY `hearings_blotter_id_status_index` (`blotter_id`,`status`),
  CONSTRAINT `hearings_blotter_id_foreign` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hearings_summon_id_foreign` FOREIGN KEY (`summon_id`) REFERENCES `summons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hearings`
--

LOCK TABLES `hearings` WRITE;
/*!40000 ALTER TABLE `hearings` DISABLE KEYS */;
/*!40000 ALTER TABLE `hearings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `household_head_transfer_logs`
--

DROP TABLE IF EXISTS `household_head_transfer_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `household_head_transfer_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `resident_user_id` bigint(20) unsigned NOT NULL,
  `old_head_user_id` bigint(20) unsigned DEFAULT NULL,
  `new_head_user_id` bigint(20) unsigned DEFAULT NULL,
  `changed_by_user_id` bigint(20) unsigned NOT NULL,
  `action` varchar(30) NOT NULL,
  `reason_code` varchar(50) NOT NULL,
  `reason_details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `household_head_transfer_logs_old_head_user_id_foreign` (`old_head_user_id`),
  KEY `household_head_transfer_logs_new_head_user_id_foreign` (`new_head_user_id`),
  KEY `hhtl_resident_created_idx` (`resident_user_id`,`created_at`),
  KEY `hhtl_actor_created_idx` (`changed_by_user_id`,`created_at`),
  KEY `household_head_transfer_logs_reason_code_index` (`reason_code`),
  CONSTRAINT `household_head_transfer_logs_changed_by_user_id_foreign` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `household_head_transfer_logs_new_head_user_id_foreign` FOREIGN KEY (`new_head_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `household_head_transfer_logs_old_head_user_id_foreign` FOREIGN KEY (`old_head_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `household_head_transfer_logs_resident_user_id_foreign` FOREIGN KEY (`resident_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `household_head_transfer_logs`
--

LOCK TABLES `household_head_transfer_logs` WRITE;
/*!40000 ALTER TABLE `household_head_transfer_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `household_head_transfer_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `household_integrity_runs`
--

DROP TABLE IF EXISTS `household_integrity_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `household_integrity_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `command_name` varchar(120) NOT NULL,
  `trigger_source` varchar(20) NOT NULL DEFAULT 'manual',
  `invalid_head_links` int(10) unsigned NOT NULL DEFAULT 0,
  `orphan_family_members` int(10) unsigned NOT NULL DEFAULT 0,
  `household_issues` int(10) unsigned NOT NULL DEFAULT 0,
  `missing_head_assignment` int(10) unsigned NOT NULL DEFAULT 0,
  `invalid_connection_type` int(10) unsigned NOT NULL DEFAULT 0,
  `broken_member_head_linkage` int(10) unsigned NOT NULL DEFAULT 0,
  `fixes_applied` int(10) unsigned NOT NULL DEFAULT 0,
  `recipients_notified` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'issues_detected',
  `notes` varchar(500) DEFAULT NULL,
  `ran_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hir_status_ran_at_idx` (`status`,`ran_at`),
  KEY `household_integrity_runs_trigger_source_index` (`trigger_source`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `household_integrity_runs`
--

LOCK TABLES `household_integrity_runs` WRITE;
/*!40000 ALTER TABLE `household_integrity_runs` DISABLE KEYS */;
INSERT INTO `household_integrity_runs` VALUES (1,'households:integrity-check','manual',0,0,3,2,0,0,3,2,'fixed_with_remaining','Executed households:sync-links | Notified 2 report managers | Before total: 8 | After total: 5','2026-03-11 15:58:01','2026-03-11 15:58:01','2026-03-11 15:58:01');
/*!40000 ALTER TABLE `household_integrity_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `households`
--

DROP TABLE IF EXISTS `households`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `households` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `head_id` bigint(20) unsigned NOT NULL,
  `purok` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `households_head_id_unique` (`head_id`),
  CONSTRAINT `households_head_id_foreign` FOREIGN KEY (`head_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `households`
--

LOCK TABLES `households` WRITE;
/*!40000 ALTER TABLE `households` DISABLE KEYS */;
INSERT INTO `households` VALUES (1,2,'Purok 2','2026-03-03 07:03:44','2026-03-03 07:03:44'),(2,3,'purok 2','2026-03-03 07:03:44','2026-03-03 07:03:44'),(3,5,'Purok 1','2026-03-03 07:03:44','2026-03-03 07:03:44'),(4,6,'Purok 1','2026-03-03 07:03:44','2026-03-03 07:03:44'),(5,7,'Purok 3','2026-03-03 07:03:44','2026-03-03 07:03:44'),(6,10,'N/A','2026-03-03 07:03:44','2026-03-03 07:03:44');
/*!40000 ALTER TABLE `households` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `issue_reports`
--

DROP TABLE IF EXISTS `issue_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `issue_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `purok_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `issue_reports_user_id_foreign` (`user_id`),
  KEY `issue_reports_purok_id_foreign` (`purok_id`),
  KEY `issue_reports_assigned_to_foreign` (`assigned_to`),
  KEY `issue_reports_category_index` (`category`),
  KEY `issue_reports_status_index` (`status`),
  CONSTRAINT `issue_reports_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `issue_reports_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `issue_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `issue_reports`
--

LOCK TABLES `issue_reports` WRITE;
/*!40000 ALTER TABLE `issue_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `issue_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_activities`
--

DROP TABLE IF EXISTS `login_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email_attempted` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `login_activities_user_id_foreign` (`user_id`),
  CONSTRAINT `login_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_activities`
--

LOCK TABLES `login_activities` WRITE;
/*!40000 ALTER TABLE `login_activities` DISABLE KEYS */;
INSERT INTO `login_activities` VALUES (1,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-11 12:00:08'),(2,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-11 12:48:33'),(3,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-11 12:59:22'),(4,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-13 10:50:46'),(5,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-13 11:29:47'),(6,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-15 12:14:31'),(7,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-15 14:49:20'),(8,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 10:06:51'),(9,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 12:15:51'),(10,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 12:16:12'),(11,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 12:43:45'),(12,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 18:38:50'),(13,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 18:39:39'),(14,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-16 18:40:47'),(15,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-17 06:19:55'),(16,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-17 06:47:39'),(17,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-17 07:02:57'),(18,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-17 07:03:51'),(19,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-17 17:14:27'),(20,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-17 23:35:59'),(21,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 00:27:53'),(22,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 01:31:18'),(23,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 01:38:20'),(24,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 10:36:23'),(25,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 12:42:58'),(26,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 12:43:45'),(27,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 16:01:18'),(28,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-18 17:43:56'),(29,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-19 15:26:41'),(30,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-19 15:27:11'),(31,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 03:48:25'),(32,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 12:21:35'),(33,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 13:13:00'),(34,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 13:13:25'),(35,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 13:13:55'),(36,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 13:34:24'),(37,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-20 17:59:28'),(38,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-21 02:04:03'),(39,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-22 16:38:22'),(40,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:11:48'),(41,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:15:53'),(42,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:17:20'),(43,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:39:55'),(44,8,'lbjromulo@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:41:53'),(45,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:48:17'),(46,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:52:48'),(47,NULL,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','failed','2026-02-24 04:53:51'),(48,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:54:00'),(49,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:57:25'),(50,6,'testing123@testing.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 04:57:42'),(51,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 05:07:11'),(52,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 06:22:30'),(53,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 08:23:31'),(54,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 08:29:46'),(55,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 08:31:31'),(56,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 08:33:54'),(57,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 08:34:48'),(58,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 08:43:20'),(59,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','success','2026-02-24 12:52:17'),(60,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-24 15:27:34'),(61,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-24 15:27:59'),(62,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-24 15:55:19'),(63,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 08:31:39'),(64,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 08:32:48'),(65,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 08:33:37'),(66,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 08:34:42'),(67,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 08:37:31'),(68,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 08:39:05'),(69,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 09:42:20'),(70,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 10:03:52'),(71,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 10:04:15'),(72,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 10:06:19'),(73,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 12:05:11'),(74,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-25 14:34:41'),(75,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-26 04:06:53'),(76,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-26 04:21:11'),(77,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-26 17:37:14'),(78,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-27 05:49:22'),(79,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-27 09:09:36'),(80,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-27 10:28:31'),(81,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-28 09:24:11'),(82,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-28 09:24:30'),(83,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-02-28 09:26:46'),(84,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-02 11:54:17'),(85,10,'superadmin@barangaypaguiruan.local','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-02 16:09:00'),(86,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-02 16:32:29'),(87,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-02 16:33:02'),(88,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-03 06:33:18'),(89,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-03 12:31:33'),(90,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-04 19:43:08'),(91,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 00:37:51'),(92,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 01:12:00'),(93,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 03:11:06'),(94,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 03:22:45'),(95,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 03:30:05'),(96,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 04:23:36'),(97,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 04:24:30'),(98,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 04:32:50'),(99,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 04:42:34'),(100,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 04:50:21'),(101,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 04:58:26'),(102,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 10:14:37'),(103,9,'apay.lance08@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 10:40:10'),(104,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 10:40:32'),(105,1,'justinkim@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-05 11:02:56'),(106,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-06 02:33:03'),(107,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-06 03:40:19'),(108,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-06 07:08:33'),(109,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-07 08:16:41'),(110,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-07 10:35:41'),(111,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-07 13:03:37'),(112,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-10 09:50:46'),(113,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-11 08:48:00'),(114,3,'staff1@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-11 08:50:19'),(115,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-11 14:13:49'),(116,2,'admin@gmail.com','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','success','2026-03-11 15:53:36');
/*!40000 ALTER TABLE `login_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_30_000000_add_role_to_users_table',2),(5,'2026_01_30_000001_create_certificate_requests_table',3),(6,'2026_01_30_000002_create_announcements_table',4),(7,'2026_01_30_000003_create_issue_reports_table',4),(8,'2026_01_30_000004_add_conditional_fields_to_users_table',5),(9,'2026_01_30_000005_add_status_to_users_table',6),(10,'2026_01_30_000006_create_approval_logs_table',6),(11,'2026_01_30_000007_add_image_to_announcements_table',7),(12,'2026_01_30_000008_add_suspension_fields_to_users_table',8),(13,'2026_01_30_000009_create_password_reset_tokens_table',9),(14,'2026_01_30_000010_create_households_table',9),(15,'2026_01_30_000011_add_household_and_demographic_fields_to_users_table',9),(16,'2026_01_30_000012_add_resident_classification_fields_to_users_table',9),(17,'2026_01_30_000013_create_puroks_table',10),(18,'2026_01_30_000014_add_purok_id_to_users_table',11),(19,'2026_01_30_000015_add_release_fields_to_certificate_requests_table',12),(20,'2026_01_30_000016_create_permits_table',13),(21,'2026_01_30_000017_add_document_path_to_permits_table',14),(22,'2026_01_30_000018_create_login_activities_table',15),(23,'2026_01_30_000019_add_head_of_family_id_to_users_table',16),(24,'2026_01_30_000021_enhance_issue_reports_and_create_complaint_notes',17),(25,'2026_01_30_000022_create_blotter_entries_and_blotter_parties_tables',18),(26,'2026_01_30_000022_create_blotters_table',19),(27,'2026_01_30_000023_create_blotter_requests_table',20),(28,'2026_01_30_000024_create_announcement_labels_table',21),(29,'2026_01_30_000025_create_announcement_announcement_label_table',21),(30,'2026_01_30_000026_add_deleted_at_to_announcements_table',21),(31,'2026_01_30_000027_add_status_to_announcements_table',21),(32,'2026_01_30_000028_add_slug_to_announcements_table',22),(33,'2026_01_30_000029_add_color_to_announcement_labels_table',23),(34,'2026_01_30_000030_create_staff_permissions_table',24),(35,'2026_01_30_000031_create_audit_logs_table',25),(36,'2026_01_30_000032_create_positions_table',26),(37,'2026_01_30_000033_create_officials_table',27),(38,'2026_01_30_000034_add_can_manage_registrations_to_staff_permissions_table',28),(39,'2026_02_18_012457_add_photo_to_officials_table',29),(40,'2026_02_18_131759_create_user_notifications_table',30),(41,'2026_02_24_123052_create_summons_table',31),(42,'2026_02_24_124925_add_is_uncooperative_to_blotters_table',31),(43,'2026_02_24_130707_create_hearing_reschedules_table',32),(44,'2026_02_24_130707_create_hearings_table',32),(45,'2026_02_24_132257_add_hearing_fk_to_hearing_reschedules_table',32),(46,'2026_02_25_000001_add_residency_template_fields_to_certificate_requests_table',33),(47,'2026_02_25_000010_add_extra_fields_to_certificate_requests_and_permits_tables',34),(48,'2026_02_26_190001_add_complainant_user_id_to_blotters_table',35),(49,'2026_02_27_020001_create_streets_and_purok_street_tables',36),(50,'2026_02_27_020002_add_sitio_subdivision_to_users_table',36),(51,'2026_03_02_000100_create_sms_templates_table',37),(52,'2026_03_02_000101_create_sms_logs_table',37),(53,'2026_03_02_000102_add_voter_verification_fields_to_users_table',38),(54,'2026_03_03_000103_add_image_fields_to_blotters_table',39),(55,'2026_03_03_000104_add_rejection_reason_code_to_blotter_requests_table',40),(56,'2026_03_03_000105_create_blotter_revisions_table',41),(57,'2026_03_05_090821_add_government_id_fields_to_users_table',42),(58,'2026_03_05_100000_create_family_members_table',43),(59,'2026_03_05_110000_create_resident_merge_logs_table',44),(60,'2026_01_30_000201_add_rejection_reason_fields_to_users_table',45),(61,'2026_03_06_000300_create_registration_alert_runs_table',46),(62,'2026_03_06_000310_add_manual_trigger_fields_to_registration_alert_runs_table',47),(63,'2026_03_06_000320_create_system_settings_table',48),(64,'2026_03_06_000330_add_household_connection_fields_to_users_table',49),(65,'2026_03_06_000340_add_unique_head_id_index_to_households_table',50),(66,'2026_03_06_000350_add_lifecycle_fields_to_family_members_table',50),(67,'2026_03_06_000360_create_household_head_transfer_logs_table',51),(68,'2026_03_06_000370_create_household_integrity_runs_table',52);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officials`
--

DROP TABLE IF EXISTS `officials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `position_id` bigint(20) unsigned NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `officials_user_id_foreign` (`user_id`),
  KEY `officials_position_id_foreign` (`position_id`),
  KEY `officials_is_active_term_end_index` (`is_active`,`term_end`),
  CONSTRAINT `officials_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `officials_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officials`
--

LOCK TABLES `officials` WRITE;
/*!40000 ALTER TABLE `officials` DISABLE KEYS */;
INSERT INTO `officials` VALUES (1,2,1,'2024-06-17','2027-05-20',1,'officials/7gcfXkJZjexYOIAERTMnSYAmtUr6jmYC2TnGluCa.png','2026-02-17 17:13:47','2026-02-23 22:25:57'),(2,3,2,'2024-06-17','2027-05-20',1,NULL,'2026-03-05 03:12:35','2026-03-05 03:12:35');
/*!40000 ALTER TABLE `officials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permits`
--

DROP TABLE IF EXISTS `permits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `permit_type` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `extra_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra_fields`)),
  `document_path` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `released_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permits_user_id_foreign` (`user_id`),
  KEY `permits_released_by_foreign` (`released_by`),
  CONSTRAINT `permits_released_by_foreign` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permits`
--

LOCK TABLES `permits` WRITE;
/*!40000 ALTER TABLE `permits` DISABLE KEYS */;
INSERT INTO `permits` VALUES (1,1,'Business Permit','dsa',NULL,NULL,'released',NULL,'2026-02-09 01:58:14',2,'2026-02-09 01:35:40','2026-02-09 01:58:14'),(2,3,'Business Permit','knk',NULL,'permits/LL7s38hoPZ0CZx6mnFjVeZQLhD0iUDMhG3jtqoVo.png','rejected','Please attach correctly thankyou',NULL,NULL,'2026-02-09 02:48:57','2026-02-09 03:01:08'),(3,2,'Event Permit','njn',NULL,'permits/j34N8m4My9li2yiv0mo2Om2t76rjG7d6rdK8jOOD.png','approved',NULL,NULL,NULL,'2026-02-09 05:08:21','2026-03-05 10:53:19'),(4,2,'Event Permit','pasldpasldp',NULL,'permits/KscjhwNWUVCe9zVXhkhdob3AhreUlOlBQMlqetbG.png','approved',NULL,NULL,NULL,'2026-02-23 20:14:04','2026-02-26 10:22:11');
/*!40000 ALTER TABLE `permits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `positions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `max_seats` smallint(5) unsigned NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `positions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES (1,'Barangay Chairman',1,1,'2026-02-16 04:47:41','2026-02-16 04:47:41'),(2,'Barangay Secretary',1,2,'2026-02-16 04:47:41','2026-02-16 04:47:41'),(3,'Barangay Treasurer',1,3,'2026-02-16 04:47:41','2026-02-16 04:47:41'),(4,'Kagawad',7,5,'2026-02-16 04:47:41','2026-02-27 02:54:34'),(5,'SK Chairman',1,6,'2026-02-16 04:47:41','2026-02-27 02:54:34'),(6,'SK Secretary',1,7,'2026-02-16 04:47:41','2026-02-27 02:54:34'),(7,'SK Treasurer',1,8,'2026-02-16 04:47:41','2026-02-27 02:54:34'),(8,'SK Kagawad',7,9,'2026-02-16 04:47:41','2026-02-27 02:54:34'),(9,'Barangay Investigator',1,4,'2026-02-27 02:54:34','2026-02-27 02:54:34'),(10,'Staff Admin Officer',1,10,'2026-02-27 04:06:59','2026-02-27 04:06:59'),(11,'Staff Records Officer',1,11,'2026-02-27 04:06:59','2026-02-27 04:06:59'),(12,'Staff Public Assistance Officer',1,12,'2026-02-27 04:06:59','2026-02-27 04:06:59'),(13,'Staff Blotter Officer',1,13,'2026-02-27 04:06:59','2026-02-27 04:06:59');
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purok_street`
--

DROP TABLE IF EXISTS `purok_street`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purok_street` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purok_id` bigint(20) unsigned NOT NULL,
  `street_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purok_street_purok_id_street_id_unique` (`purok_id`,`street_id`),
  KEY `purok_street_street_id_foreign` (`street_id`),
  CONSTRAINT `purok_street_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purok_street_street_id_foreign` FOREIGN KEY (`street_id`) REFERENCES `streets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purok_street`
--

LOCK TABLES `purok_street` WRITE;
/*!40000 ALTER TABLE `purok_street` DISABLE KEYS */;
INSERT INTO `purok_street` VALUES (1,2,1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(2,2,2,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(3,1,4,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(4,1,5,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(5,1,6,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(6,3,7,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(7,1,8,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(8,1,9,'2026-02-27 01:52:00','2026-02-27 01:52:00');
/*!40000 ALTER TABLE `purok_street` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `puroks`
--

DROP TABLE IF EXISTS `puroks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `puroks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `puroks_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `puroks`
--

LOCK TABLES `puroks` WRITE;
/*!40000 ALTER TABLE `puroks` DISABLE KEYS */;
INSERT INTO `puroks` VALUES (1,'Purok 1',NULL,1,'2026-02-06 03:34:58','2026-02-06 03:34:58'),(2,'Purok 2',NULL,1,'2026-02-06 03:34:58','2026-02-06 03:34:58'),(3,'Purok 3',NULL,1,'2026-02-06 03:34:58','2026-02-06 03:34:58'),(4,'Purok 4',NULL,1,'2026-02-06 03:34:58','2026-02-06 03:34:58'),(5,'Purok 5',NULL,1,'2026-02-06 03:34:58','2026-02-06 03:34:58'),(6,'Purok 6',NULL,1,'2026-02-06 03:34:58','2026-02-06 03:34:58'),(7,'Purok 7',NULL,0,'2026-02-06 03:34:58','2026-02-06 03:36:10');
/*!40000 ALTER TABLE `puroks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registration_alert_runs`
--

DROP TABLE IF EXISTS `registration_alert_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_alert_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `command_name` varchar(120) NOT NULL,
  `trigger_source` varchar(30) NOT NULL DEFAULT 'scheduled',
  `triggered_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `trigger_reason` varchar(255) DEFAULT NULL,
  `threshold_hours` smallint(5) unsigned NOT NULL DEFAULT 48,
  `overdue_count` int(10) unsigned NOT NULL DEFAULT 0,
  `due_soon_count` int(10) unsigned NOT NULL DEFAULT 0,
  `missing_id_count` int(10) unsigned NOT NULL DEFAULT 0,
  `recipients_targeted` int(10) unsigned NOT NULL DEFAULT 0,
  `recipients_sent` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(40) NOT NULL DEFAULT 'ok',
  `notes` varchar(255) DEFAULT NULL,
  `ran_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `registration_alert_runs_status_ran_at_index` (`status`,`ran_at`),
  KEY `registration_alert_runs_trigger_source_ran_at_index` (`trigger_source`,`ran_at`),
  KEY `registration_alert_runs_triggered_by_user_id_foreign` (`triggered_by_user_id`),
  CONSTRAINT `registration_alert_runs_triggered_by_user_id_foreign` FOREIGN KEY (`triggered_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration_alert_runs`
--

LOCK TABLES `registration_alert_runs` WRITE;
/*!40000 ALTER TABLE `registration_alert_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `registration_alert_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resident_merge_logs`
--

DROP TABLE IF EXISTS `resident_merge_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resident_merge_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `primary_user_id` bigint(20) unsigned NOT NULL,
  `secondary_user_id` bigint(20) unsigned NOT NULL,
  `performed_by` bigint(20) unsigned DEFAULT NULL,
  `tables_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tables_payload`)),
  `primary_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`primary_snapshot`)),
  `secondary_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`secondary_snapshot`)),
  `undone_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resident_merge_logs_secondary_user_id_foreign` (`secondary_user_id`),
  KEY `resident_merge_logs_primary_user_id_secondary_user_id_index` (`primary_user_id`,`secondary_user_id`),
  KEY `resident_merge_logs_performed_by_index` (`performed_by`),
  CONSTRAINT `resident_merge_logs_performed_by_foreign` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resident_merge_logs_primary_user_id_foreign` FOREIGN KEY (`primary_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resident_merge_logs_secondary_user_id_foreign` FOREIGN KEY (`secondary_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resident_merge_logs`
--

LOCK TABLES `resident_merge_logs` WRITE;
/*!40000 ALTER TABLE `resident_merge_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `resident_merge_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('05nzoO5O0rmJXIvYJOJCkMTvxShvHBSRod9z4Gat',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoieEJXVVByblFsYkZzWjhIN09PVWNGbHVEVkZ0RjBBTG9SdzgzTVlrQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZXNpZGVudC9hbm5vdW5jZW1lbnRzIjtzOjU6InJvdXRlIjtzOjI4OiJyZXNpZGVudC5hbm5vdW5jZW1lbnRzLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9',1772781565),('3I96MhmzhLvkbYcmiRZ9JT0g3wlBPySgfZBFZnGw',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYTBIcnNlMzVvd2hGelA3a1Q4NTdXWVc1ZXBFVDVDYW5PaExOQXZURiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9yZXNpZGVudHMvMSI7czo1OiJyb3V0ZSI7czoyMDoiYWRtaW4ucmVzaWRlbnRzLnNob3ciO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=',1773244428),('BBZMaPzYxT8Ht8ANyRdi3wbCx1T0kiKyg40DkMC4',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSVRLamFZb0J6TVlPbk9LNG1STURlRGNQWURneUN2YnhETWtFVEowciI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=',1773219037),('emcW3AM4bwXRG3kilybT6vPPpqBtBURazxxtmk17',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVFFGOVJmVzRVcGprN25HSHdLempLaG1iejdZQnU4SHp1T2tiZnNuQyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjQ2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcmVwb3J0cy9ob3VzZWhvbGRzIjtzOjU6InJvdXRlIjtzOjI0OiJhZG1pbi5yZXBvcnRzLmhvdXNlaG9sZHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=',1772888643),('rf6N8qi4xt8KZ3320gXNSdebFXP8FW9lyDsbx5Fo',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoidnFzUXJHRzZCVEk2VlE5Z3Jka0VvS0NVazAwNVBpVzh1QW9wODdLbSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjU1OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcmVwb3J0cy9ob3VzZWhvbGRzL3RpbWVsaW5lIjtzOjU6InJvdXRlIjtzOjMzOiJhZG1pbi5yZXBvcnRzLmhvdXNlaG9sZHMudGltZWxpbmUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=',1772871614),('rVLRX4plWgIuEWsGYDivbiQBrhFxqpbO37aDR1ZD',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoidFlteWUxZHVCSDVEZVZpa3BLM0hjd3l6anhwRGFpS1ZqYXExMnJDNCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcmVwb3J0cyI7czo1OiJyb3V0ZSI7czoxOToiYWRtaW4ucmVwb3J0cy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==',1772880246),('YmaU3bIzxCY2AnTKxkpeJjR3Rudm5mtBoop13d7k',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiYXllOEtNRDlmUTh0WjZOcWhRZDZYUWNKT2lNS3Q5aHFEY3d0ODh2eiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjU2OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vYmxvdHRlcnMvMS9ldmlkZW5jZS9ldmlkZW5jZSI7czo1OiJyb3V0ZSI7czozMToiYWRtaW4uYmxvdHRlcnMuZXZpZGVuY2UucHJldmlldyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==',1773140787);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_logs`
--

DROP TABLE IF EXISTS `sms_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `template_key` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `provider_response` text DEFAULT NULL,
  `context_type` varchar(50) DEFAULT NULL,
  `context_id` bigint(20) unsigned DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_logs_user_id_foreign` (`user_id`),
  KEY `sms_logs_status_created_at_index` (`status`,`created_at`),
  KEY `sms_logs_template_key_created_at_index` (`template_key`,`created_at`),
  KEY `sms_logs_context_type_context_id_index` (`context_type`,`context_id`),
  CONSTRAINT `sms_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_logs`
--

LOCK TABLES `sms_logs` WRITE;
/*!40000 ALTER TABLE `sms_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_templates`
--

DROP TABLE IF EXISTS `sms_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sms_templates_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_templates`
--

LOCK TABLES `sms_templates` WRITE;
/*!40000 ALTER TABLE `sms_templates` DISABLE KEYS */;
INSERT INTO `sms_templates` VALUES (1,'certificate_released_pickup','Certificate Released (Pickup)','Magandang araw {name}! Ang iyong {request_type} request (#{reference_id}) ay RELEASED at ready for pickup sa {pickup_location}. Dalhin ang valid ID at claim stub. Salamat.',1,'2026-03-02 03:55:26','2026-03-02 03:55:26'),(2,'permit_released_pickup','Permit Released (Pickup)','Magandang araw {name}! Ang iyong {request_type} request (#{reference_id}) ay RELEASED at ready for pickup sa {pickup_location}. Dalhin ang valid ID at claim stub. Salamat.',1,'2026-03-02 03:55:26','2026-03-02 03:55:26');
/*!40000 ALTER TABLE `sms_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_permissions`
--

DROP TABLE IF EXISTS `staff_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `can_manage_registrations` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_blotter` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_announcements` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_complaints` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_reports` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_permissions_user_id_unique` (`user_id`),
  CONSTRAINT `staff_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_permissions`
--

LOCK TABLES `staff_permissions` WRITE;
/*!40000 ALTER TABLE `staff_permissions` DISABLE KEYS */;
INSERT INTO `staff_permissions` VALUES (1,3,1,0,0,0,0,'2026-02-16 04:43:34','2026-03-03 05:11:37');
/*!40000 ALTER TABLE `staff_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `streets`
--

DROP TABLE IF EXISTS `streets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `streets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `streets_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `streets`
--

LOCK TABLES `streets` WRITE;
/*!40000 ALTER TABLE `streets` DISABLE KEYS */;
INSERT INTO `streets` VALUES (1,'mustasa st evegreen homes',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(2,'araymo',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(3,'dasda',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(4,'malabong 1122',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(5,'dsadsada',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(6,'maribles',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(7,'testpurok3',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(8,'dona juaquina',1,'2026-02-27 01:52:00','2026-02-27 01:52:00'),(9,'Don Alfredo',1,'2026-02-27 01:52:00','2026-02-27 01:52:00');
/*!40000 ALTER TABLE `streets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `summons`
--

DROP TABLE IF EXISTS `summons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `summons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blotter_id` bigint(20) unsigned NOT NULL,
  `summon_number` tinyint(3) unsigned NOT NULL,
  `hearing_date` date NOT NULL,
  `hearing_time` time NOT NULL,
  `lupon_assigned` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `summons_blotter_id_summon_number_unique` (`blotter_id`,`summon_number`),
  KEY `summons_blotter_id_status_index` (`blotter_id`,`status`),
  CONSTRAINT `summons_blotter_id_foreign` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `summons`
--

LOCK TABLES `summons` WRITE;
/*!40000 ALTER TABLE `summons` DISABLE KEYS */;
INSERT INTO `summons` VALUES (1,1,1,'2026-03-12','14:35:00','Justinkim abarico','pending','2026-03-02 22:35:29','2026-03-02 22:35:29');
/*!40000 ALTER TABLE `summons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(120) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`),
  KEY `system_settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `system_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `related_id` bigint(20) unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_notifications_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `user_notifications_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_notifications`
--

LOCK TABLES `user_notifications` WRITE;
/*!40000 ALTER TABLE `user_notifications` DISABLE KEYS */;
INSERT INTO `user_notifications` VALUES (1,9,'Certificate Approved','Your Certificate of Indigency request has been approved and is ready for pickup.','certificate',7,0,'2026-02-24 00:34:14','2026-02-24 00:34:14'),(2,9,'Blotter Request Approved','Your blotter record request has been approved.','blotter',1,0,'2026-02-24 00:44:12','2026-02-24 00:44:12'),(3,9,'Blotter Request Released','Your blotter record has been released. You may now claim it at the barangay hall.','blotter',1,0,'2026-02-24 00:44:21','2026-02-24 00:44:21'),(4,9,'Certificate Approved','Your Certificate of Indigency request has been approved and is ready for pickup.','certificate',8,0,'2026-02-25 00:33:19','2026-02-25 00:33:19'),(5,9,'Certificate Released','Your Certificate of Indigency has been released. You may now claim it at the barangay hall.','certificate',8,0,'2026-02-25 00:36:27','2026-02-25 00:36:27'),(6,2,'Certificate Approved','Your Certificate of Indigency request has been approved and is ready for pickup.','certificate',9,1,'2026-02-25 00:42:40','2026-02-25 20:26:29'),(7,2,'Certificate Released','Your Certificate of Indigency has been released. You may now claim it at the barangay hall.','certificate',9,1,'2026-02-25 00:46:33','2026-02-25 20:26:29'),(8,9,'Certificate Approved','Your Barangay Clearance request has been approved and is ready for pickup.','certificate',10,0,'2026-02-25 02:03:58','2026-02-25 02:03:58'),(9,9,'Certificate Released','Your Barangay Clearance has been released. You may now claim it at the barangay hall.','certificate',10,0,'2026-02-25 02:10:36','2026-02-25 02:10:36'),(10,2,'Certificate Approved','Your Residency Certificate request has been approved and is ready for pickup.','certificate',11,1,'2026-02-25 03:21:27','2026-02-25 20:26:23'),(11,9,'Certificate Released','Your Certificate of Indigency has been released. You may now claim it at the barangay hall.','certificate',7,1,'2026-02-26 09:55:04','2026-03-05 03:22:51'),(12,2,'Permit Approved','Your Event Permit permit application has been approved.','permit',4,1,'2026-02-26 10:22:11','2026-03-02 04:30:30'),(13,2,'Permit Approved','Your Event Permit permit application has been approved.','permit',3,0,'2026-03-05 10:53:19','2026-03-05 10:53:19'),(14,2,'Household Integrity Alert','Household integrity run (fixed_with_remaining): invalid_head_links=0, orphan_family_members=0, household_issues=3, missing_head_assignment=2, invalid_connection_type=0, broken_member_head_linkage=0.','announcement',NULL,0,'2026-03-11 15:58:01','2026-03-11 15:58:01'),(15,10,'Household Integrity Alert','Household integrity run (fixed_with_remaining): invalid_head_links=0, orphan_family_members=0, household_issues=3, missing_head_assignment=2, invalid_connection_type=0, broken_member_head_linkage=0.','announcement',NULL,0,'2026-03-11 15:58:01','2026-03-11 15:58:01');
/*!40000 ALTER TABLE `user_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(255) DEFAULT NULL,
  `house_no` varchar(255) NOT NULL,
  `purok` varchar(255) NOT NULL,
  `purok_id` bigint(20) unsigned DEFAULT NULL,
  `street_name` varchar(255) NOT NULL,
  `sitio_subdivision` varchar(255) DEFAULT NULL,
  `contact_number` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `civil_status` varchar(255) NOT NULL,
  `head_of_family` enum('yes','no') NOT NULL,
  `head_of_family_id` bigint(20) unsigned DEFAULT NULL,
  `family_link_status` varchar(255) DEFAULT NULL,
  `head_first_name` varchar(255) DEFAULT NULL,
  `head_middle_name` varchar(255) DEFAULT NULL,
  `head_last_name` varchar(255) DEFAULT NULL,
  `resident_type` enum('permanent','non-permanent') NOT NULL,
  `household_id` bigint(20) unsigned DEFAULT NULL,
  `relationship_to_head` varchar(255) DEFAULT NULL,
  `household_connection_type` varchar(60) DEFAULT NULL,
  `connection_note` varchar(255) DEFAULT NULL,
  `permanent_house_no` varchar(255) DEFAULT NULL,
  `permanent_street` varchar(255) DEFAULT NULL,
  `permanent_barangay` varchar(255) DEFAULT NULL,
  `permanent_city` varchar(255) DEFAULT NULL,
  `permanent_province` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'resident',
  `position_title` varchar(255) DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `rejection_reason_code` varchar(80) DEFAULT NULL,
  `rejection_reason_details` text DEFAULT NULL,
  `is_pwd` tinyint(1) NOT NULL DEFAULT 0,
  `is_senior` tinyint(1) NOT NULL DEFAULT 0,
  `is_registered_voter` tinyint(1) NOT NULL DEFAULT 0,
  `pwd_status` varchar(255) DEFAULT NULL,
  `senior_status` varchar(255) DEFAULT NULL,
  `voter_status` varchar(255) DEFAULT NULL,
  `pwd_proof_path` varchar(255) DEFAULT NULL,
  `senior_proof_path` varchar(255) DEFAULT NULL,
  `voter_proof_path` varchar(255) DEFAULT NULL,
  `government_id_type` varchar(255) DEFAULT NULL,
  `government_id_path` varchar(255) DEFAULT NULL,
  `is_voter` tinyint(1) NOT NULL DEFAULT 0,
  `voter_precinct` varchar(255) DEFAULT NULL,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_household_id_foreign` (`household_id`),
  KEY `users_purok_id_foreign` (`purok_id`),
  KEY `users_head_of_family_id_foreign` (`head_of_family_id`),
  KEY `users_position_id_foreign` (`position_id`),
  KEY `users_rejection_reason_code_index` (`rejection_reason_code`),
  KEY `users_household_connection_type_index` (`household_connection_type`),
  CONSTRAINT `users_head_of_family_id_foreign` FOREIGN KEY (`head_of_family_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_household_id_foreign` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_purok_id_foreign` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'justinkim','ella','abarico',NULL,'321','Purok 1',1,'dsadsada',NULL,'+639683869302',12,'male','2013-10-25','single','no',5,'linked',NULL,NULL,NULL,'permanent',3,NULL,'other','Auto-assigned by integrity check',NULL,NULL,NULL,NULL,NULL,'justinkim@gmail.com',NULL,'$2y$12$GZxqUHpMHBksnEwPcYmD1OLTFXJSuhwPWB2XVgD6Gy/frfym0qwJW','resident',NULL,NULL,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-01-31 04:23:42','2026-03-11 15:58:01'),(2,'admin','ewan','admin',NULL,'355','Purok 2',2,'araymo',NULL,'+639123456789',25,'male','2000-07-14','married','yes',NULL,'linked',NULL,NULL,NULL,'permanent',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'admin@gmail.com',NULL,'$2y$12$iczoEeyWYO4GmCLFkqa1B.vFCjUFGx68lOJttyRmEsVjeNlcov3s6','admin','Barangay Chairman',1,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-01-31 05:35:41','2026-03-03 07:03:44'),(3,'staff','adada','staff',NULL,'355','purok 2',NULL,'dasda',NULL,'09683869302',35,'male','1989-08-02','single','yes',NULL,'linked',NULL,NULL,NULL,'permanent',2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'staff1@gmail.com',NULL,'$2y$12$7XT7Oq5WZsk13VxgRVRgxe7CKZ9n0KZNrtfY6d/Qw0uNiVchbMSdq','staff','Barangay Secretary',2,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-01-31 22:13:53','2026-03-05 03:12:35'),(4,'micka','ella','abkilan',NULL,'355','Purok 1',1,'malabong 1122',NULL,'09683869302',26,'female','1999-08-01','single','no',NULL,NULL,'justinkim','e','abarico','permanent',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'mickaela@gmail.com',NULL,'$2y$12$IDBr2y6GoTaoMBKJuIN0m.5EgfWZZkVTtxt7Km3nszAKr2kwx4Sue','resident',NULL,NULL,'rejected',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-02-04 03:14:55','2026-02-23 20:12:45'),(5,'dsadsa','dsa','dsa',NULL,'321','Purok 1',1,'dsadsada',NULL,'09123456789',23,'female','2004-08-20','single','yes',NULL,'linked',NULL,NULL,NULL,'permanent',3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'asdasd@gmail.com',NULL,'$2y$12$sj28a.BY/Ji6otCVpYBozOgo/dYU78UrQlWsC5YcqkTJCDWD9oiLK','resident',NULL,NULL,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-02-04 05:15:38','2026-03-03 07:03:44'),(6,'test','re','testing',NULL,'1122','Purok 1',1,'maribles',NULL,'09123456789',32,'male','1999-02-02','single','yes',NULL,'linked',NULL,NULL,NULL,'permanent',4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'testing123@testing.com',NULL,'$2y$12$.Wi7UNnzqdRP4wRENxKfWu1f.wfSqArfg8JAsHr9xHb4t5UWE94by','resident',NULL,NULL,'approved',NULL,NULL,0,0,1,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-02-06 03:38:33','2026-03-05 04:04:22'),(7,'test3',NULL,'test',NULL,'123','Purok 3',3,'testpurok3',NULL,'09123456789',24,'male','2001-08-08','single','yes',NULL,'linked',NULL,NULL,NULL,'permanent',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'testpurok3@test.com',NULL,'$2y$12$jCtgFFp9zir1iATGRKQXL.eC4Bm.wLF6S1nrThWprlbZd9oULf22u','resident',NULL,NULL,'rejected',NULL,NULL,1,1,1,'verified','rejected','not_submitted','classification-proofs/pwd/MhAYLYlXnc61zHzCkWvgRZfiY1yj8HrCHyLETI7w.png','classification-proofs/senior/mXwRSAhAPBm0xNQwADhavo9JOroKca4PPePLZmgL.png',NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-02-09 05:21:44','2026-03-03 07:03:44'),(8,'lebron','romulo','james','III','24','Purok 1',1,'dona juaquina',NULL,'+639845848548',35,'other','1990-12-12','single','no',NULL,'pending_link','srfsd','qwfa','james','permanent',NULL,NULL,'other','Auto-assigned by integrity check',NULL,NULL,NULL,NULL,NULL,'lbjromulo@gmail.com',NULL,'$2y$12$4WYO57jRVYQuoCbX66yOZ.8X1xUkNkUk8iqG/.j.7oXYpO6YSJYJy','resident',NULL,NULL,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-02-23 20:39:26','2026-03-11 15:58:01'),(9,'Lance','De Leon','Apay',NULL,'1','Purok 1',1,'Don Alfredo',NULL,'+639516986415',21,'male','2004-10-04','single','no',NULL,'pending_link','Justin','Ella','Abarico','permanent',NULL,NULL,'other','Auto-assigned by integrity check',NULL,NULL,NULL,NULL,NULL,'apay.lance08@gmail.com',NULL,'$2y$12$82sqE7W32sCFXGPXuZA/veeeqN.Mgs0BDin.AFTXCmXgCKW4KnMPy','resident',NULL,NULL,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-02-24 00:29:28','2026-03-11 15:58:01'),(10,'System',NULL,'Administrator',NULL,'N/A','N/A',NULL,'N/A',NULL,'+639000000000',30,'other','1995-01-01','single','yes',NULL,'linked',NULL,NULL,NULL,'permanent',6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'superadmin@barangaypaguiruan.local',NULL,'$2y$12$h8NNA1vTzIinxCej6sboO.LpC6AS00b.viF0OAjg5fIwqb9ISU8wC','super_admin',NULL,NULL,'approved',NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,0,NULL,NULL,'2026-03-02 08:04:18','2026-03-03 07:03:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-12  0:26:13

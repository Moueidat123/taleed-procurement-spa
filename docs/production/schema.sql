
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `app_password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_password_reset_tokens` (
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_users` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('champion','analyst','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_export` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_users_email_unique` (`email`),
  KEY `app_users_organization_id_index` (`organization_id`),
  CONSTRAINT `app_users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_answers` (
  `revision_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` tinyint(1) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`revision_id`,`question_id`),
  KEY `fk_answer_revision_version` (`revision_id`,`framework_version_id`),
  KEY `fk_answer_question_version` (`question_id`,`framework_version_id`),
  CONSTRAINT `fk_answer_question_version` FOREIGN KEY (`question_id`, `framework_version_id`) REFERENCES `framework_questions` (`id`, `framework_version_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_answer_revision_version` FOREIGN KEY (`revision_id`, `framework_version_id`) REFERENCES `assessment_revisions` (`id`, `framework_version_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_cycles` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `opens_at` timestamp NOT NULL,
  `closes_at` timestamp NOT NULL,
  `business_timezone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Riyadh',
  `open_flag` tinyint unsigned GENERATED ALWAYS AS ((case when (`status` = _utf8mb4'open') then 1 else NULL end)) STORED,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_one_open_cycle` (`open_flag`),
  KEY `assessment_cycles_framework_version_id_foreign` (`framework_version_id`),
  CONSTRAINT `assessment_cycles_framework_version_id_foreign` FOREIGN KEY (`framework_version_id`) REFERENCES `framework_versions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_revisions` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assessment_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revision_number` int unsigned NOT NULL,
  `parent_revision_id` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `correction_reason` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lock_version` int unsigned NOT NULL DEFAULT '0',
  `created_by` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted_by` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `open_draft_for` char(26) CHARACTER SET ascii COLLATE ascii_bin GENERATED ALWAYS AS ((case when (`status` = _utf8mb4'draft') then `assessment_id` else NULL end)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_revision_number` (`assessment_id`,`revision_number`),
  UNIQUE KEY `uq_revision_assessment` (`id`,`assessment_id`),
  UNIQUE KEY `uq_revision_version` (`id`,`framework_version_id`),
  UNIQUE KEY `uq_one_open_draft` (`open_draft_for`),
  KEY `assessment_revisions_created_by_foreign` (`created_by`),
  KEY `assessment_revisions_submitted_by_foreign` (`submitted_by`),
  KEY `fk_revision_assessment_version` (`assessment_id`,`framework_version_id`),
  KEY `fk_revision_parent_same_assessment` (`parent_revision_id`,`assessment_id`),
  CONSTRAINT `assessment_revisions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `assessment_revisions_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `app_users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_revision_assessment_version` FOREIGN KEY (`assessment_id`, `framework_version_id`) REFERENCES `assessments` (`id`, `framework_version_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_revision_parent_same_assessment` FOREIGN KEY (`parent_revision_id`, `assessment_id`) REFERENCES `assessment_revisions` (`id`, `assessment_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessments` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cycle_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_submission_id` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assessment_org_cycle` (`organization_id`,`cycle_id`),
  UNIQUE KEY `uq_assessment_version` (`id`,`framework_version_id`),
  KEY `assessments_cycle_id_foreign` (`cycle_id`),
  KEY `assessments_framework_version_id_foreign` (`framework_version_id`),
  KEY `fk_assessment_current_submission` (`current_submission_id`,`id`),
  CONSTRAINT `assessments_cycle_id_foreign` FOREIGN KEY (`cycle_id`) REFERENCES `assessment_cycles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `assessments_framework_version_id_foreign` FOREIGN KEY (`framework_version_id`) REFERENCES `framework_versions` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `assessments_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_assessment_current_submission` FOREIGN KEY (`current_submission_id`, `id`) REFERENCES `assessment_revisions` (`id`, `assessment_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_events` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organization_id` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outcome` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ok',
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_action` (`action`,`created_at`),
  KEY `idx_audit_org` (`organization_id`,`created_at`),
  KEY `idx_audit_actor` (`actor_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cms_password_activation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_password_activation_tokens` (
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cms_password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cms_password_reset_tokens` (
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `email_verification_challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verification_challenges` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `app_user_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `expires_at` timestamp NOT NULL,
  `consumed_at` timestamp NULL DEFAULT NULL,
  `last_sent_at` timestamp NULL DEFAULT NULL,
  `sent_count` smallint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_challenge_user` (`app_user_id`,`consumed_at`),
  CONSTRAINT `email_verification_challenges_app_user_id_foreign` FOREIGN KEY (`app_user_id`) REFERENCES `app_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `framework_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `framework_domains` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_domain_key` (`framework_version_id`,`key`),
  UNIQUE KEY `uq_domain_position` (`framework_version_id`,`position`),
  UNIQUE KEY `uq_domain_version` (`id`,`framework_version_id`),
  CONSTRAINT `framework_domains_framework_version_id_foreign` FOREIGN KEY (`framework_version_id`) REFERENCES `framework_versions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `framework_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `framework_questions` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_question_id` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` tinyint unsigned NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_cell` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_question_source_id` (`framework_version_id`,`source_question_id`),
  UNIQUE KEY `uq_question_position` (`domain_id`,`position`),
  UNIQUE KEY `uq_question_version` (`id`,`framework_version_id`),
  KEY `fk_question_domain_version` (`domain_id`,`framework_version_id`),
  CONSTRAINT `fk_question_domain_version` FOREIGN KEY (`domain_id`, `framework_version_id`) REFERENCES `framework_domains` (`id`, `framework_version_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `framework_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `framework_versions` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `semantic_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_file_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scoring_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interpretations` json NOT NULL,
  `approval_reference` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `framework_versions_semantic_version_unique` (`semantic_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `idempotency_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `idempotency_requests` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idempotency_key` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `response_reference` varchar(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_idempotency_actor_key` (`actor_id`,`operation`,`idempotency_key`),
  CONSTRAINT `idempotency_requests_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `app_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `normalized_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size_band` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `normalized_registration_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authority_confirmed_at` timestamp NULL DEFAULT NULL,
  `authority_confirmed_by` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_test` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_org_registration` (`country_code`,`normalized_registration_id`),
  KEY `idx_org_name_country` (`normalized_name`,`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `outbox_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `outbox_events` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aggregate_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` smallint unsigned NOT NULL DEFAULT '0',
  `available_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_outbox_pending` (`status`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recommendation_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recommendation_actions` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `band` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` tinyint unsigned NOT NULL,
  `source_action_id` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_cell` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_action_position` (`domain_id`,`band`,`position`),
  KEY `fk_action_domain_version` (`domain_id`,`framework_version_id`),
  CONSTRAINT `fk_action_domain_version` FOREIGN KEY (`domain_id`, `framework_version_id`) REFERENCES `framework_domains` (`id`, `framework_version_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_invitations` (
  `id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `normalized_email` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('analyst','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_export` tinyint(1) NOT NULL DEFAULT '0',
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inviter_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `accepted_user_id` char(26) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_invitations_token_hash_unique` (`token_hash`),
  KEY `idx_invite_email` (`normalized_email`),
  KEY `staff_invitations_inviter_id_foreign` (`inviter_id`),
  CONSTRAINT `staff_invitations_inviter_id_foreign` FOREIGN KEY (`inviter_id`) REFERENCES `app_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `submission_domain_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submission_domain_results` (
  `revision_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `framework_version_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `yes_count` tinyint unsigned NOT NULL,
  `score_percent` tinyint unsigned NOT NULL,
  `band` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`revision_id`,`domain_id`),
  KEY `ix_domain_result_portfolio` (`framework_version_id`,`domain_id`,`band`),
  KEY `fk_domain_result_domain_version` (`domain_id`,`framework_version_id`),
  CONSTRAINT `fk_domain_result_domain_version` FOREIGN KEY (`domain_id`, `framework_version_id`) REFERENCES `framework_domains` (`id`, `framework_version_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_domain_result_snapshot` FOREIGN KEY (`revision_id`) REFERENCES `submission_snapshots` (`revision_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `submission_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submission_snapshots` (
  `revision_id` char(26) COLLATE utf8mb4_unicode_ci NOT NULL,
  `overall_yes_count` tinyint unsigned NOT NULL,
  `overall_percent` decimal(5,2) NOT NULL,
  `band` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schema_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scoring_version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json NOT NULL,
  `canonical_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted_at` timestamp NOT NULL,
  PRIMARY KEY (`revision_id`),
  CONSTRAINT `fk_snapshot_revision` FOREIGN KEY (`revision_id`) REFERENCES `assessment_revisions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


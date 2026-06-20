-- bildfie Platform Database Schema
-- MySQL / MariaDB — UTF-8 Unicode
-- Generated for bildfie v1.0 (June 2026)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+03:00";

-- ─────────────────────────────────────────────
--  Reference / lookup tables
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `currencies` (
  `code`    CHAR(3)      NOT NULL,
  `name`    VARCHAR(80)  NOT NULL,
  `symbol`  VARCHAR(10)  NOT NULL DEFAULT '',
  `is_base` TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `currencies` (`code`, `name`, `symbol`, `is_base`) VALUES
  ('KES', 'Kenyan Shilling',     'KES', 1),
  ('USD', 'US Dollar',           '$',   0),
  ('EUR', 'Euro',                '€',   0),
  ('GBP', 'British Pound',       '£',   0),
  ('NGN', 'Nigerian Naira',      'NGN', 0),
  ('TZS', 'Tanzanian Shilling',  'TSh', 0),
  ('UGX', 'Ugandan Shilling',    'USh', 0);


CREATE TABLE IF NOT EXISTS `regions` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `code`          CHAR(5)          NOT NULL,
  `name`          VARCHAR(80)      NOT NULL,
  `currency_code` CHAR(3)          DEFAULT NULL,
  `is_active`     TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `regions_code` (`code`),
  KEY `regions_currency` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `regions` (`id`, `code`, `name`, `currency_code`, `is_active`) VALUES
  (1, 'KE', 'Kenya',         'KES', 1),
  (2, 'TZ', 'Tanzania',      'TZS', 1),
  (3, 'UG', 'Uganda',        'UGX', 1),
  (4, 'RW', 'Rwanda',        NULL,  1),
  (5, 'NG', 'Nigeria',       'NGN', 1),
  (6, 'GH', 'Ghana',         NULL,  1),
  (7, 'ZA', 'South Africa',  NULL,  1),
  (8, 'SA', 'Saudi Arabia',  NULL,  1),
  (9, 'AE', 'UAE',           NULL,  1);


CREATE TABLE IF NOT EXISTS `settings` (
  `key`   VARCHAR(80)  NOT NULL,
  `value` TEXT         DEFAULT NULL,
  `group` VARCHAR(40)  NOT NULL DEFAULT 'general',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `verticals` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(120)    NOT NULL,
  `slug`           VARCHAR(120)    NOT NULL,
  `icon`           VARCHAR(80)     DEFAULT NULL,
  `color`          CHAR(7)         DEFAULT '#1e3a5f',
  `bg`             CHAR(7)         DEFAULT '#eaf0f6',
  `provider_count` INT             NOT NULL DEFAULT 0,
  `sort_order`     SMALLINT        NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `verticals_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `verticals` (`id`, `name`, `slug`, `icon`, `color`, `bg`, `sort_order`) VALUES
  (1, 'Construction & Built Environment', 'construction-built-environment', 'bi-bricks',                '#1e3a5f', '#eaf0f6', 1),
  (2, 'Architecture & Design',            'architecture-design',            'bi-rulers',                '#7c3aed', '#f5f3ff', 2),
  (3, 'MEP Engineering',                  'mep-engineering',                'bi-lightning-charge',      '#b45309', '#fffbeb', 3),
  (4, 'Interiors & Finishing',            'interiors-finishing',            'bi-palette',               '#166534', '#f0fdf4', 4),
  (5, 'Civil Engineering',                'civil-engineering',              'bi-cone-striped',          '#1e40af', '#eff6ff', 5),
  (6, 'Equipment & Machinery',            'equipment-machinery',            'bi-gear-wide-connected',   '#b91c1c', '#fef2f2', 6);


CREATE TABLE IF NOT EXISTS `categories` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `vertical_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(120) NOT NULL,
  `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `categories_vertical` (`vertical_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`vertical_id`, `name`, `sort_order`) VALUES
  -- Construction & Built Environment
  (1, 'General Contractor',       1), (1, 'Site Foreman',         2), (1, 'Mason / Bricklayer',   3), (1, 'Carpenter',             4), (1, 'Steel Fixer',          5),
  -- Architecture & Design
  (2, 'Architect',                1), (2, 'Draughtsman',          2), (2, 'Urban Planner',        3), (2, 'Landscape Architect',   4), (2, 'BIM Specialist',        5),
  -- MEP Engineering
  (3, 'Electrician',              1), (3, 'Plumber',              2), (3, 'HVAC Technician',      3), (3, 'Solar Installer',       4), (3, 'Fire Safety Engineer',  5),
  -- Interiors & Finishing
  (4, 'Interior Designer',        1), (4, 'Painter',              2), (4, 'Tiler',                3), (4, 'Gypsum Specialist',     4), (4, 'Flooring Specialist',   5),
  -- Civil Engineering
  (5, 'Structural Engineer',      1), (5, 'Civil Engineer',       2), (5, 'Quantity Surveyor',    3), (5, 'Geotechnical Engineer',  4), (5, 'Site Engineer',         5),
  -- Equipment & Machinery
  (6, 'Equipment Operator',       1), (6, 'Crane Operator',       2), (6, 'Plant Manager',        3), (6, 'Mechanic / Technician', 4), (6, 'Welder / Fabricator',   5);


-- ─────────────────────────────────────────────
--  Users
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `users` (
  `id`                  BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `public_id`           CHAR(36)         NOT NULL,
  `name`                VARCHAR(160)     NOT NULL,
  `email`               VARCHAR(255)     NOT NULL,
  `phone`               VARCHAR(30)      DEFAULT NULL,
  `password_hash`       VARCHAR(255)     NOT NULL,
  `region_id`           INT UNSIGNED     DEFAULT NULL,
  `is_provider`         TINYINT(1)       NOT NULL DEFAULT 0,
  `is_client`           TINYINT(1)       NOT NULL DEFAULT 1,
  `status`              ENUM('active','pending','suspended','deleted') NOT NULL DEFAULT 'active',
  `email_verified_at`   TIMESTAMP        NULL DEFAULT NULL,
  `created_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email`     (`email`),
  UNIQUE KEY `users_public_id` (`public_id`),
  KEY `users_region`           (`region_id`),
  KEY `users_status`           (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `public_id`, `name`, `email`, `phone`, `password_hash`, `region_id`, `is_provider`, `is_client`, `status`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(3,  '260b0bf1-b2b0-4099-857998be83b5', 'Haydn Amboga',       'ambogahaydn@gmail.com',       '0726104422',    '$2y$10$vH4CBpR3AHk/DDLEbqgtpOm30x5JNXnSaF2vrdkgIxtuoSKEUq4j.', 1,    1, 1, 'active',    '2026-06-10 10:34:16', '2026-06-02 08:53:05', '2026-06-10 10:34:16'),
(33, '501e7102-646e-4bc6-809b1600a462', 'Paul Mongare',       'paulnyabuya20@gmail.com',     '+254703329204', '$2y$10$xhCtRshCmYRqFIj.1RcQ8uvI5mxPHltdZCIRQpQ3JhllEkHCGy3Lu', NULL, 1, 1, 'active',    '2026-06-08 19:45:37', '2026-06-08 13:19:59', '2026-06-08 19:45:37'),
(34, 'bb6abc52-fb74-4253-ab8ef6de41c3', 'Apiud Mokua',        'apiudmokua@gmail.com',        '+254788831519', '$2y$10$c2xbwOERPp/4949IGTUdMu6yemG8mc6TgujYJs9yhSbWi9KH6UdX.', 1,    1, 1, 'active',    '2026-06-08 19:45:35', '2026-06-08 14:22:56', '2026-06-11 17:49:11'),
(35, '9d6220c8-6bee-4359-8c6fc0d67e3a', 'Samson Otwori',      'pardonsamson@gmail.com',      '+254704350532', '$2y$10$8QJ6Iv9q5PVBfuZDgRFZi.zzQmwUszSu/0yJChifJnugUgHMkL6Cy', 1,    1, 1, 'suspended', '2026-06-09 15:28:37', '2026-06-08 18:55:20', '2026-06-13 10:28:22'),
(36, '5d27c3a6-bc9f-4351-9492dbb26a66', 'Frank Lewis',        'lewisfrank2001@gmail.com',    '783749102',     '$2y$10$lfiXWKUQZiUSMPrNADbTqO3BsytTst2cMnbiQas2rBkJaKEY74182', NULL, 1, 1, 'active',    '2026-06-11 15:20:11', '2026-06-11 15:19:41', '2026-06-11 15:23:04'),
(37, '833a9d98-8b33-4a84-a79f54685b36', 'Nathan Amboga',      'haydn.amboga@gmail.com',      '+254726104422', '$2y$10$B6nHW5S1zrM/RuDcSH5Q1OeP9ZAWaT7XvjJpTZ/OAztjPxfgBn0GW', NULL, 1, 1, 'active',    '2026-06-13 09:55:44', '2026-06-13 09:54:27', '2026-06-13 09:58:50'),
(38, '66b140ea-8150-408d-9be88783774c', 'Nyabuga Amboga',     'ambogae@gmail.com',           '+254733405655', '$2y$10$r0CPSR.dBOpqeJ9HY.BxHObMvE/auKQ4Sxr2afIiCZqZVzrnOd8BC', NULL, 1, 1, 'active',    '2026-06-13 10:16:16', '2026-06-13 10:15:15', '2026-06-13 10:20:12'),
(39, '7e73d2bb-797d-4d03-89037139bb7a', 'Remissionary Studio','tellremissionary@gmail.com',  '+254733405655', '$2y$10$ZM7wR6CLJEVUC3pfGznrUeacyLSEDZMbjZO/T/f5aI.MxvnNAZ8Sm', NULL, 1, 1, 'active',    '2026-06-13 10:24:28', '2026-06-13 10:24:01', '2026-06-13 10:26:38');


CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `token`      CHAR(64)        NOT NULL,
  `type`       ENUM('verify','reset') NOT NULL,
  `expires_at` TIMESTAMP       NOT NULL,
  `used_at`    TIMESTAMP       NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_tokens_token` (`token`),
  KEY `auth_tokens_user_type` (`user_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Professional profile tables
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `user_professions` (
  `user_id`           BIGINT UNSIGNED  NOT NULL,
  `trade`             VARCHAR(120)     DEFAULT NULL,
  `title`             VARCHAR(200)     DEFAULT NULL,
  `years_experience`  SMALLINT         DEFAULT NULL,
  `day_rate`          DECIMAL(12,2)    DEFAULT NULL,
  `rate_unit`         VARCHAR(20)      NOT NULL DEFAULT 'day',
  `currency_code`     CHAR(3)          NOT NULL DEFAULT 'KES',
  `availability`      ENUM('available','busy','unavailable') NOT NULL DEFAULT 'available',
  `company_name`      VARCHAR(200)     DEFAULT NULL,
  `nca_number`        VARCHAR(60)      DEFAULT NULL,
  `nca_category`      VARCHAR(80)      DEFAULT NULL,
  `bio`               TEXT             DEFAULT NULL,
  `photo_url`         TEXT             DEFAULT NULL,
  `cover_url`         TEXT             DEFAULT NULL,
  `years_in_business` VARCHAR(40)      DEFAULT NULL,
  `team_size`         VARCHAR(40)      DEFAULT NULL,
  `working_hours`     VARCHAR(120)     DEFAULT NULL,
  `serving_area`      VARCHAR(255)     DEFAULT NULL,
  `website`           VARCHAR(255)     DEFAULT NULL,
  `public_phone`      VARCHAR(30)      DEFAULT NULL,
  `jobs_completed`    VARCHAR(20)      DEFAULT NULL,
  `on_time_pct`       VARCHAR(10)      DEFAULT NULL,
  `repeat_pct`        VARCHAR(10)      DEFAULT NULL,
  `response_time`     VARCHAR(40)      DEFAULT NULL,
  `availability_note` VARCHAR(200)     DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_skills` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `skill`      VARCHAR(120)    NOT NULL,
  `sort_order` SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_skills_user` (`user_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_service_areas` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `area`       VARCHAR(120)    NOT NULL,
  `sort_order` SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_service_areas_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_packages` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `tier`        VARCHAR(60)     DEFAULT NULL,
  `title`       VARCHAR(200)    NOT NULL,
  `price`       VARCHAR(40)     DEFAULT NULL,
  `price_unit`  VARCHAR(40)     DEFAULT NULL,
  `description` TEXT            DEFAULT NULL,
  `delivery`    VARCHAR(80)     DEFAULT NULL,
  `revisions`   VARCHAR(40)     DEFAULT NULL,
  `features`    TEXT            DEFAULT NULL,
  `is_featured` TINYINT(1)      NOT NULL DEFAULT 0,
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_packages_user` (`user_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_portfolio` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `title`       VARCHAR(200)    NOT NULL,
  `category`    VARCHAR(100)    DEFAULT NULL,
  `year`        VARCHAR(10)     DEFAULT NULL,
  `image_url`   TEXT            DEFAULT NULL,
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_portfolio_user` (`user_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_services` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `icon`        VARCHAR(60)     DEFAULT NULL,
  `name`        VARCHAR(200)    NOT NULL,
  `description` TEXT            DEFAULT NULL,
  `rate`        VARCHAR(40)     DEFAULT NULL,
  `rate_unit`   VARCHAR(40)     DEFAULT NULL,
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_services_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_languages` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `language`   VARCHAR(80)     NOT NULL,
  `level`      VARCHAR(40)     DEFAULT NULL,
  `sort_order` SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_languages_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_certifications` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `name`       VARCHAR(200)    NOT NULL,
  `issuer`     VARCHAR(200)    DEFAULT NULL,
  `status`     VARCHAR(60)     DEFAULT NULL,
  `sort_order` SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_certifications_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_work_history` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `role`         VARCHAR(200)    NOT NULL,
  `organization` VARCHAR(200)    DEFAULT NULL,
  `period`       VARCHAR(80)     DEFAULT NULL,
  `description`  TEXT            DEFAULT NULL,
  `sort_order`   SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_work_history_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_education` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `title`       VARCHAR(200)    NOT NULL,
  `institution` VARCHAR(200)    DEFAULT NULL,
  `sort_order`  SMALLINT        NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_education_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `user_preferences` (
  `user_id`          BIGINT UNSIGNED  NOT NULL,
  `currency_code`    CHAR(3)          NOT NULL DEFAULT 'KES',
  `language`         VARCHAR(10)      NOT NULL DEFAULT 'en',
  `timezone`         VARCHAR(60)      NOT NULL DEFAULT 'Africa/Nairobi',
  `notif_bids`       TINYINT(1)       NOT NULL DEFAULT 1,
  `notif_messages`   TINYINT(1)       NOT NULL DEFAULT 1,
  `notif_payments`   TINYINT(1)       NOT NULL DEFAULT 1,
  `notif_milestones` TINYINT(1)       NOT NULL DEFAULT 1,
  `notif_weekly`     TINYINT(1)       NOT NULL DEFAULT 1,
  `notif_promos`     TINYINT(1)       NOT NULL DEFAULT 0,
  `two_factor`       TINYINT(1)       NOT NULL DEFAULT 0,
  `login_alerts`     TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Providers (public marketplace listings)
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `providers` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`      CHAR(36)        NOT NULL,
  `user_id`        BIGINT UNSIGNED NOT NULL,
  `name`           VARCHAR(160)    NOT NULL,
  `headline`       VARCHAR(255)    DEFAULT NULL,
  `vertical_id`    INT UNSIGNED    DEFAULT NULL,
  `bio`            TEXT            DEFAULT NULL,
  `location`       VARCHAR(200)    DEFAULT NULL,
  `region_id`      INT UNSIGNED    DEFAULT NULL,
  `photo_url`      TEXT            DEFAULT NULL,
  `cover_url`      TEXT            DEFAULT NULL,
  `day_rate`       VARCHAR(80)     DEFAULT NULL,
  `is_available`   TINYINT(1)      NOT NULL DEFAULT 1,
  `is_verified`    TINYINT(1)      NOT NULL DEFAULT 0,
  `is_featured`    TINYINT(1)      NOT NULL DEFAULT 0,
  `is_certified`   TINYINT(1)      NOT NULL DEFAULT 0,
  `is_preferred`   TINYINT(1)      NOT NULL DEFAULT 0,
  `is_insured`     TINYINT(1)      NOT NULL DEFAULT 0,
  `status`         ENUM('active','pending','suspended') NOT NULL DEFAULT 'pending',
  `rating`         DECIMAL(3,1)    NOT NULL DEFAULT 0.0,
  `reviews_count`  INT             NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `providers_public_id` (`public_id`),
  KEY `providers_user_id`  (`user_id`),
  KEY `providers_status`   (`status`),
  KEY `providers_rating`   (`rating`),
  KEY `providers_vertical` (`vertical_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `provider_skills` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `provider_id` INT UNSIGNED  NOT NULL,
  `skill`       VARCHAR(120)  NOT NULL,
  `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `provider_skills_provider` (`provider_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `provider_reviews` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`     CHAR(36)        NOT NULL,
  `provider_id`   INT UNSIGNED    NOT NULL,
  `user_id`       BIGINT UNSIGNED DEFAULT NULL,
  `reviewer_name` VARCHAR(120)    NOT NULL DEFAULT 'Anonymous',
  `rating`        TINYINT         NOT NULL DEFAULT 5,
  `project`       VARCHAR(200)    DEFAULT NULL,
  `body`          TEXT            DEFAULT NULL,
  `status`        ENUM('published','pending','removed') NOT NULL DEFAULT 'published',
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pr_public_id`          (`public_id`),
  UNIQUE KEY `pr_provider_reviewer`  (`provider_id`, `user_id`),
  KEY `pr_status`                    (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `provider_engagements` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`    CHAR(36)        NOT NULL,
  `provider_id`  INT UNSIGNED    NOT NULL,
  `project_id`   INT UNSIGNED    DEFAULT NULL,
  `from_user_id` BIGINT UNSIGNED DEFAULT NULL,
  `from_name`    VARCHAR(160)    DEFAULT NULL,
  `type`         ENUM('invite','quote') NOT NULL DEFAULT 'invite',
  `subject`      VARCHAR(255)    DEFAULT NULL,
  `message`      TEXT            DEFAULT NULL,
  `location`     VARCHAR(200)    DEFAULT NULL,
  `budget`       VARCHAR(80)     DEFAULT NULL,
  `needed_by`    DATE            DEFAULT NULL,
  `status`       ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pe_public_id` (`public_id`),
  KEY `pe_provider` (`provider_id`),
  KEY `pe_from_user` (`from_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Wallets
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `wallets` (
  `user_id`       BIGINT UNSIGNED  NOT NULL,
  `balance`       DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `currency_code` CHAR(3)          NOT NULL DEFAULT 'KES',
  `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `type`          ENUM('deposit','withdrawal','payment','refund','payout','fee') NOT NULL,
  `amount`        DECIMAL(14,2)   NOT NULL,
  `balance_after` DECIMAL(14,2)   NOT NULL,
  `status`        ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
  `method`        VARCHAR(60)     DEFAULT NULL,
  `reference`     VARCHAR(80)     DEFAULT NULL,
  `description`   VARCHAR(255)    DEFAULT NULL,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `wt_user_id` (`user_id`),
  KEY `wt_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Projects
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `projects` (
  `id`                         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`                  CHAR(36)        NOT NULL,
  `owner_user_id`              BIGINT UNSIGNED DEFAULT NULL,
  `owner_name`                 VARCHAR(160)    DEFAULT NULL,
  `owner_label`                VARCHAR(60)     NOT NULL DEFAULT 'Owner',
  `name`                       VARCHAR(255)    NOT NULL,
  `customer_name`              VARCHAR(160)    DEFAULT NULL,
  `customer_email`             VARCHAR(255)    DEFAULT NULL,
  `customer_phone`             VARCHAR(30)     DEFAULT NULL,
  `type`                       VARCHAR(80)     DEFAULT NULL,
  `segment`                    VARCHAR(80)     DEFAULT NULL,
  `location`                   VARCHAR(200)    DEFAULT NULL,
  `description`                TEXT            DEFAULT NULL,
  `budget`                     DECIMAL(16,2)   DEFAULT NULL,
  `budget_display`             VARCHAR(80)     DEFAULT NULL,
  `currency`                   CHAR(3)         NOT NULL DEFAULT 'KES',
  `billing_type`               VARCHAR(30)     NOT NULL DEFAULT 'milestone',
  `payment_terms`              VARCHAR(255)    DEFAULT NULL,
  `start_date`                 DATE            DEFAULT NULL,
  `end_date`                   DATE            DEFAULT NULL,
  `est_completion`             DATE            DEFAULT NULL,
  `deadline`                   DATE            DEFAULT NULL,
  `priority`                   VARCHAR(20)     NOT NULL DEFAULT 'normal',
  `phase`                      VARCHAR(60)     NOT NULL DEFAULT 'Planning',
  `progress`                   TINYINT         NOT NULL DEFAULT 0,
  `urgency`                    VARCHAR(20)     NOT NULL DEFAULT 'new',
  `status`                     VARCHAR(30)     NOT NULL DEFAULT 'draft',
  `visibility`                 ENUM('private','public') NOT NULL DEFAULT 'private',
  `trades`                     TEXT            DEFAULT NULL,
  `client_can_comment`         TINYINT(1)      NOT NULL DEFAULT 0,
  `client_can_view_budget`     TINYINT(1)      NOT NULL DEFAULT 0,
  `client_can_view_documents`  TINYINT(1)      NOT NULL DEFAULT 0,
  `require_milestone_approval` TINYINT(1)      NOT NULL DEFAULT 0,
  `use_escrow`                 TINYINT(1)      NOT NULL DEFAULT 0,
  `notify_client_updates`      TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`                 TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                 TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_public_id` (`public_id`),
  KEY `projects_owner`    (`owner_user_id`),
  KEY `projects_status`   (`status`),
  KEY `projects_updated`  (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `project_milestones` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `project_id`   INT UNSIGNED  NOT NULL,
  `title`        VARCHAR(255)  NOT NULL,
  `description`  TEXT          DEFAULT NULL,
  `amount`       DECIMAL(14,2) DEFAULT NULL,
  `due_date`     DATE          DEFAULT NULL,
  `status`       ENUM('upcoming','active','completed') NOT NULL DEFAULT 'upcoming',
  `released`     TINYINT(1)    NOT NULL DEFAULT 0,
  `sort_order`   SMALLINT      NOT NULL DEFAULT 0,
  `completed_at` TIMESTAMP     NULL DEFAULT NULL,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pm_project` (`project_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `project_tasks` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `project_id`   INT UNSIGNED  NOT NULL,
  `parent_id`    INT UNSIGNED  DEFAULT NULL,
  `title`        VARCHAR(255)  NOT NULL,
  `description`  TEXT          DEFAULT NULL,
  `status`       ENUM('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
  `amount`       DECIMAL(14,2) DEFAULT NULL,
  `due_date`     DATE          DEFAULT NULL,
  `is_milestone` TINYINT(1)    NOT NULL DEFAULT 0,
  `released`     TINYINT(1)    NOT NULL DEFAULT 0,
  `sort_order`   INT           NOT NULL DEFAULT 0,
  `completed_at` TIMESTAMP     NULL DEFAULT NULL,
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pt_project`    (`project_id`, `sort_order`),
  KEY `pt_parent`     (`parent_id`),
  KEY `pt_status`     (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `project_task_members` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `task_id`    INT UNSIGNED  NOT NULL,
  `name`       VARCHAR(160)  NOT NULL,
  `role`       VARCHAR(100)  DEFAULT NULL,
  `sort_order` SMALLINT      NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ptm_task` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Sales / Finance
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `documents` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`       CHAR(36)        NOT NULL,
  `user_id`         BIGINT UNSIGNED NOT NULL,
  `doc_type`        ENUM('invoice','estimate','proposal','credit_note') NOT NULL DEFAULT 'invoice',
  `number`          VARCHAR(40)     NOT NULL,
  `client_name`     VARCHAR(160)    NOT NULL DEFAULT 'Client',
  `client_id`       INT UNSIGNED    DEFAULT NULL,
  `client_email`    VARCHAR(255)    DEFAULT NULL,
  `client_phone`    VARCHAR(30)     DEFAULT NULL,
  `subject`         VARCHAR(255)    DEFAULT NULL,
  `project_id`      INT UNSIGNED    DEFAULT NULL,
  `issue_date`      DATE            DEFAULT NULL,
  `due_date`        DATE            DEFAULT NULL,
  `currency`        CHAR(3)         NOT NULL DEFAULT 'KES',
  `subtotal`        DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `tax_rate`        DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
  `tax_amount`      DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `total`           DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `notes`           TEXT            DEFAULT NULL,
  `status`          VARCHAR(30)     NOT NULL DEFAULT 'draft',
  `converted_to_id` INT UNSIGNED    DEFAULT NULL,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documents_public_id` (`public_id`),
  KEY `documents_user_type`  (`user_id`, `doc_type`),
  KEY `documents_status`     (`status`),
  KEY `documents_created`    (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `document_items` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED  NOT NULL,
  `description` VARCHAR(500)  NOT NULL,
  `quantity`    DECIMAL(10,3) NOT NULL DEFAULT 1.000,
  `unit_price`  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `line_total`  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `di_document` (`document_id`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `clients` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`  CHAR(36)        NOT NULL,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `name`       VARCHAR(160)    NOT NULL,
  `email`      VARCHAR(255)    DEFAULT NULL,
  `phone`      VARCHAR(30)     DEFAULT NULL,
  `company`    VARCHAR(200)    DEFAULT NULL,
  `notes`      TEXT            DEFAULT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_public_id` (`public_id`),
  KEY `clients_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`     CHAR(36)        NOT NULL,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `name`          VARCHAR(255)    NOT NULL,
  `sku`           VARCHAR(80)     DEFAULT NULL,
  `item_type`     ENUM('material','hardware','software','service') NOT NULL DEFAULT 'material',
  `unit_price`    DECIMAL(14,2)   NOT NULL DEFAULT 0.00,
  `quantity`      INT             NOT NULL DEFAULT 0,
  `unit`          VARCHAR(30)     DEFAULT NULL,
  `reorder_level` INT             NOT NULL DEFAULT 0,
  `location`      VARCHAR(200)    DEFAULT NULL,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_public_id` (`public_id`),
  KEY `inv_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `contracts` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `public_id`    CHAR(36)        NOT NULL,
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `number`       VARCHAR(40)     NOT NULL,
  `title`        VARCHAR(255)    NOT NULL,
  `counterparty` VARCHAR(200)    NOT NULL DEFAULT 'Counterparty',
  `value`        DECIMAL(16,2)   DEFAULT NULL,
  `currency`     CHAR(3)         NOT NULL DEFAULT 'KES',
  `start_date`   DATE            DEFAULT NULL,
  `end_date`     DATE            DEFAULT NULL,
  `body`         LONGTEXT        DEFAULT NULL,
  `status`       VARCHAR(30)     NOT NULL DEFAULT 'draft',
  `signed_at`    TIMESTAMP       NULL DEFAULT NULL,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contracts_public_id` (`public_id`),
  KEY `contracts_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Companies
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `companies` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `public_id`   CHAR(36)      NOT NULL,
  `name`        VARCHAR(200)  NOT NULL,
  `slug`        VARCHAR(200)  NOT NULL,
  `industry`    VARCHAR(120)  DEFAULT NULL,
  `logo_url`    TEXT          DEFAULT NULL,
  `hq_location` VARCHAR(200)  DEFAULT NULL,
  `region_id`   INT UNSIGNED  DEFAULT NULL,
  `is_verified` TINYINT(1)    NOT NULL DEFAULT 0,
  `status`      ENUM('active','pending','suspended') NOT NULL DEFAULT 'pending',
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_public_id` (`public_id`),
  UNIQUE KEY `companies_slug`      (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `company_specialties` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id`  INT UNSIGNED NOT NULL,
  `specialty`   VARCHAR(120) NOT NULL,
  `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `cs_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `company_members` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `company_id`       INT UNSIGNED    NOT NULL,
  `user_id`          BIGINT UNSIGNED NOT NULL,
  `position`         VARCHAR(120)    DEFAULT NULL,
  `employment_type`  VARCHAR(40)     NOT NULL DEFAULT 'Full-time',
  `is_current`       TINYINT(1)      NOT NULL DEFAULT 1,
  `is_public`        TINYINT(1)      NOT NULL DEFAULT 1,
  `status`           ENUM('pending','affirmed','rejected') NOT NULL DEFAULT 'pending',
  `affirmed_at`      TIMESTAMP       NULL DEFAULT NULL,
  `affirmed_by`      INT             DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cm_company` (`company_id`),
  KEY `cm_user`    (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `company_vacancies` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `company_id`        INT UNSIGNED  NOT NULL,
  `title`             VARCHAR(255)  NOT NULL,
  `description`       TEXT          DEFAULT NULL,
  `employment_type`   VARCHAR(60)   DEFAULT NULL,
  `location`          VARCHAR(200)  DEFAULT NULL,
  `status`            ENUM('open','closed') NOT NULL DEFAULT 'open',
  `applications_count` INT          NOT NULL DEFAULT 0,
  `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cv_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `vacancy_applications` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `vacancy_id`     INT UNSIGNED    NOT NULL,
  `user_id`        BIGINT UNSIGNED NOT NULL,
  `applicant_name` VARCHAR(160)    NOT NULL,
  `applicant_email` VARCHAR(255)   DEFAULT NULL,
  `message`        TEXT            DEFAULT NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `va_vacancy_user` (`vacancy_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Admin: Roles, Departments, Staff, Audit
-- ─────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `roles` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(80)   NOT NULL,
  `level`       VARCHAR(10)   NOT NULL DEFAULT 'L4',
  `description` VARCHAR(255)  DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `level`, `description`) VALUES
  (1, 'Super Admin', 'L1', 'Full platform access — owner level'),
  (2, 'Admin',       'L2', 'Administrative access'),
  (3, 'Manager',     'L3', 'Team management access'),
  (4, 'Staff',       'L4', 'Standard staff access'),
  (5, 'Support',     'L5', 'Customer support access');


CREATE TABLE IF NOT EXISTS `departments` (
  `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments` (`id`, `name`) VALUES
  (1,  'Executive'),
  (2,  'Operations'),
  (3,  'Finance'),
  (4,  'Technical'),
  (5,  'People & HR'),
  (6,  'Marketing'),
  (7,  'Business Dev'),
  (8,  'Support'),
  (9,  'Trust & Safety');


CREATE TABLE IF NOT EXISTS `staff` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `public_id`     CHAR(36)      NOT NULL,
  `name`          VARCHAR(160)  NOT NULL,
  `email`         VARCHAR(255)  NOT NULL,
  `phone`         VARCHAR(30)   DEFAULT NULL,
  `role_id`       INT UNSIGNED  DEFAULT NULL,
  `department_id` INT UNSIGNED  DEFAULT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `photo_url`     TEXT          DEFAULT NULL,
  `cover_url`     TEXT          DEFAULT NULL,
  `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `is_protected`  TINYINT(1)    NOT NULL DEFAULT 0,
  `last_login_at` TIMESTAMP     NULL DEFAULT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_email`     (`email`),
  UNIQUE KEY `staff_public_id` (`public_id`),
  KEY `staff_role`             (`role_id`),
  KEY `staff_department`       (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default super admin: password is Admin@Bildfie2026
INSERT INTO `staff` (`id`, `public_id`, `name`, `email`, `phone`, `role_id`, `department_id`, `password_hash`, `status`, `is_protected`) VALUES
  (1, '00000000-0000-4000-8000-000000000001', 'Bildfie Admin', 'admin@bildfie.com', NULL, 1, 1,
   '$2y$12$LQ8v3lE2ZGkVJFvNKz9aROsGKFvGi2dU9JZVWNbkpYjqU7N7QLKNO',
   'active', 1);


CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `actor_type`  ENUM('staff','member','system') NOT NULL DEFAULT 'system',
  `actor_id`    INT UNSIGNED  DEFAULT NULL,
  `actor_name`  VARCHAR(160)  DEFAULT NULL,
  `action`      VARCHAR(120)  NOT NULL,
  `entity_type` VARCHAR(80)   DEFAULT NULL,
  `entity_id`   VARCHAR(40)   DEFAULT NULL,
  `meta`        TEXT          DEFAULT NULL,
  `ip`          VARCHAR(45)   DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `al_actor`  (`actor_type`, `actor_id`),
  KEY `al_action` (`action`),
  KEY `al_created`(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─────────────────────────────────────────────
--  Foreign Keys
-- ─────────────────────────────────────────────

ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `fk_at_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_professions`
  ADD CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_skills`
  ADD CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_service_areas`
  ADD CONSTRAINT `fk_usa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_packages`
  ADD CONSTRAINT `fk_upkg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_portfolio`
  ADD CONSTRAINT `fk_upf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_services`
  ADD CONSTRAINT `fk_usvc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_languages`
  ADD CONSTRAINT `fk_ul_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_certifications`
  ADD CONSTRAINT `fk_uc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_work_history`
  ADD CONSTRAINT `fk_uwh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_education`
  ADD CONSTRAINT `fk_ue_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_preferences`
  ADD CONSTRAINT `fk_upref_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_w_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `fk_wt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `providers`
  ADD CONSTRAINT `fk_prov_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `provider_skills`
  ADD CONSTRAINT `fk_ps_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

ALTER TABLE `provider_reviews`
  ADD CONSTRAINT `fk_pr_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

ALTER TABLE `provider_engagements`
  ADD CONSTRAINT `fk_pe_provider` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

ALTER TABLE `projects`
  ADD CONSTRAINT `fk_proj_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `project_milestones`
  ADD CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

ALTER TABLE `project_tasks`
  ADD CONSTRAINT `fk_pt_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pt_parent`  FOREIGN KEY (`parent_id`)  REFERENCES `project_tasks` (`id`) ON DELETE CASCADE;

ALTER TABLE `project_task_members`
  ADD CONSTRAINT `fk_ptm_task` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE;

ALTER TABLE `documents`
  ADD CONSTRAINT `fk_doc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `document_items`
  ADD CONSTRAINT `fk_di_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

ALTER TABLE `clients`
  ADD CONSTRAINT `fk_cl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `inventory_items`
  ADD CONSTRAINT `fk_inv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `contracts`
  ADD CONSTRAINT `fk_con_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_role`       FOREIGN KEY (`role_id`)       REFERENCES `roles`       (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_staff_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;


COMMIT;

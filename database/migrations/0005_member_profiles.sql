-- ════════════════════════════════════════════════════════════
-- 0005 · Member self-service — professional profile & preferences
-- Backs the data that was previously hard-coded on Account → Settings.
-- ════════════════════════════════════════════════════════════

-- Professional profile (1:1 with a member)
CREATE TABLE IF NOT EXISTS user_professions (
  user_id BIGINT UNSIGNED NOT NULL,
  trade VARCHAR(80) NULL,
  title VARCHAR(160) NULL,
  years_experience VARCHAR(20) NULL,
  day_rate DECIMAL(12,2) NULL,
  rate_unit VARCHAR(16) NOT NULL DEFAULT 'day',
  currency_code CHAR(3) NOT NULL DEFAULT 'KES',
  availability ENUM('available','busy','arrangement','unavailable') NOT NULL DEFAULT 'available',
  company_name VARCHAR(160) NULL,
  nca_number VARCHAR(40) NULL,
  nca_category VARCHAR(8) NULL,
  bio TEXT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_prof_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Specialisations (many per member)
CREATE TABLE IF NOT EXISTS user_skills (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  skill VARCHAR(80) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_uskill_user (user_id),
  CONSTRAINT fk_uskill_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service areas (many per member)
CREATE TABLE IF NOT EXISTS user_service_areas (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  area VARCHAR(80) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_uarea_user (user_id),
  CONSTRAINT fk_uarea_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Preferences, notifications & security toggles (1:1 with a member)
CREATE TABLE IF NOT EXISTS user_preferences (
  user_id BIGINT UNSIGNED NOT NULL,
  currency_code CHAR(3) NOT NULL DEFAULT 'KES',
  language VARCHAR(20) NOT NULL DEFAULT 'English',
  timezone VARCHAR(48) NOT NULL DEFAULT 'EAT (UTC+3) — Nairobi',
  notif_bids TINYINT(1) NOT NULL DEFAULT 1,
  notif_messages TINYINT(1) NOT NULL DEFAULT 1,
  notif_payments TINYINT(1) NOT NULL DEFAULT 1,
  notif_milestones TINYINT(1) NOT NULL DEFAULT 1,
  notif_weekly TINYINT(1) NOT NULL DEFAULT 0,
  notif_promos TINYINT(1) NOT NULL DEFAULT 0,
  two_factor TINYINT(1) NOT NULL DEFAULT 0,
  login_alerts TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_pref_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

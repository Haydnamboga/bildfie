-- ════════════════════════════════════════════════════════════
-- 0004 · Marketplace — Providers (professionals) + skills
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS providers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  name VARCHAR(120) NOT NULL,
  headline VARCHAR(160) NULL,
  vertical_id BIGINT UNSIGNED NULL,
  bio TEXT NULL,
  location VARCHAR(120) NULL,
  region_id BIGINT UNSIGNED NULL,
  photo_url VARCHAR(255) NULL,
  cover_url VARCHAR(255) NULL,
  day_rate VARCHAR(60) NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0,
  reviews_count INT NOT NULL DEFAULT 0,
  jobs_completed INT NOT NULL DEFAULT 0,
  response_hours INT NULL,
  badge VARCHAR(40) NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','pending','suspended') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_provider_public (public_id),
  KEY idx_provider_vertical (vertical_id),
  KEY idx_provider_status (status, is_featured, sort_order),
  KEY idx_provider_user (user_id),
  CONSTRAINT fk_provider_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL,
  CONSTRAINT fk_provider_vertical FOREIGN KEY (vertical_id) REFERENCES verticals(id) ON DELETE SET NULL,
  CONSTRAINT fk_provider_region   FOREIGN KEY (region_id)   REFERENCES regions(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS provider_skills (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id BIGINT UNSIGNED NOT NULL,
  skill VARCHAR(80) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_pskill_provider (provider_id, sort_order),
  CONSTRAINT fk_pskill_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

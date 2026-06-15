-- ════════════════════════════════════════════════════════════
-- 0017 · Editable, client-facing profile sections (per member)
--   Replaces the hard-coded placeholder content on Account → Profile
--   with real, owner-editable data. All keyed by user_id (same pattern
--   as user_skills / user_service_areas). Reviews already live in
--   provider_reviews (0015) — client-created, not owner-editable.
-- ════════════════════════════════════════════════════════════

-- Service packages (Fiverr-style tiers)
CREATE TABLE IF NOT EXISTS user_packages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  tier VARCHAR(40) NULL,
  title VARCHAR(120) NOT NULL,
  price VARCHAR(60) NULL,
  price_unit VARCHAR(40) NULL,
  description VARCHAR(400) NULL,
  delivery VARCHAR(60) NULL,
  revisions VARCHAR(60) NULL,
  features TEXT NULL,            -- one feature per line (all shown as included)
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pkg_user (user_id, sort_order),
  CONSTRAINT fk_pkg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Portfolio / past projects (with optional cover image)
CREATE TABLE IF NOT EXISTS user_portfolio (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  category VARCHAR(80) NULL,
  year VARCHAR(10) NULL,
  image_url VARCHAR(255) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_folio_user (user_id, sort_order),
  CONSTRAINT fk_folio_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Other services & rates (lighter than packages)
CREATE TABLE IF NOT EXISTS user_services (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  icon VARCHAR(40) NULL,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  rate VARCHAR(60) NULL,
  rate_unit VARCHAR(40) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_svc_user (user_id, sort_order),
  CONSTRAINT fk_svc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Languages spoken
CREATE TABLE IF NOT EXISTS user_languages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  language VARCHAR(60) NOT NULL,
  level VARCHAR(60) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_lang_user (user_id, sort_order),
  CONSTRAINT fk_lang_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Certifications & licences
CREATE TABLE IF NOT EXISTS user_certifications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  issuer VARCHAR(160) NULL,
  status VARCHAR(40) NULL,       -- VERIFIED / CURRENT / PENDING (display label)
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_cert_user (user_id, sort_order),
  CONSTRAINT fk_cert_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Business info — extra columns on the 1:1 professional profile
ALTER TABLE user_professions
  ADD COLUMN IF NOT EXISTS years_in_business VARCHAR(40) NULL,
  ADD COLUMN IF NOT EXISTS team_size         VARCHAR(60) NULL,
  ADD COLUMN IF NOT EXISTS working_hours     VARCHAR(120) NULL,
  ADD COLUMN IF NOT EXISTS serving_area      VARCHAR(160) NULL,
  ADD COLUMN IF NOT EXISTS website           VARCHAR(160) NULL,
  ADD COLUMN IF NOT EXISTS public_phone      VARCHAR(40) NULL;

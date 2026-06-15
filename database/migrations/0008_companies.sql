-- ════════════════════════════════════════════════════════════
-- 0008 · Companies / organizations + employment affirmation
--   Orgs build LinkedIn-style profiles; employees are affirmed.
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS companies (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  name VARCHAR(160) NOT NULL,
  tagline VARCHAR(200) NULL,
  about TEXT NULL,
  industry VARCHAR(100) NULL,
  company_type VARCHAR(60) NULL,       -- Private, Public, NGO, Government, Institution …
  company_size VARCHAR(40) NULL,       -- 1-10, 11-50, 51-200 …
  founded_year SMALLINT NULL,
  hq_location VARCHAR(160) NULL,
  region_id BIGINT UNSIGNED NULL,
  website VARCHAR(200) NULL,
  email VARCHAR(160) NULL,
  phone VARCHAR(32) NULL,
  logo_url VARCHAR(255) NULL,
  cover_url VARCHAR(255) NULL,
  owner_user_id BIGINT UNSIGNED NULL,  -- the bildfie member who manages this page
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','pending','suspended') NOT NULL DEFAULT 'active',
  followers INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_company_public (public_id),
  UNIQUE KEY uq_company_slug (slug),
  KEY idx_company_owner (owner_user_id),
  KEY idx_company_status (status, is_verified),
  CONSTRAINT fk_company_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL,
  CONSTRAINT fk_company_owner  FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS company_specialties (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  specialty VARCHAR(80) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_cspec_company (company_id),
  CONSTRAINT fk_cspec_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employment / affiliation, with the affirmation flow
CREATE TABLE IF NOT EXISTS company_members (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  company_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  position VARCHAR(120) NULL,
  department VARCHAR(80) NULL,
  employment_type VARCHAR(40) NULL,    -- Full-time, Part-time, Contract …
  start_date DATE NULL,
  end_date DATE NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 1,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('pending','affirmed','rejected') NOT NULL DEFAULT 'pending',
  affirmed_at TIMESTAMP NULL,
  affirmed_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_company_member (company_id, user_id, position),
  KEY idx_cm_company (company_id, status),
  KEY idx_cm_user (user_id, status),
  CONSTRAINT fk_cm_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_cm_user    FOREIGN KEY (user_id)    REFERENCES users(id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

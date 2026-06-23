-- ════════════════════════════════════════════════════════════
-- 0010 · Transaction loop — projects + bids
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS projects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  owner_user_id BIGINT UNSIGNED NULL,
  owner_name VARCHAR(160) NULL,
  owner_label VARCHAR(60) NULL,
  name VARCHAR(200) NOT NULL,
  type VARCHAR(60) NULL,
  segment VARCHAR(40) NULL,
  location VARCHAR(160) NULL,
  region_id BIGINT UNSIGNED NULL,
  description TEXT NULL,
  budget_display VARCHAR(80) NULL,
  budget DECIMAL(14,2) NULL,
  duration VARCHAR(60) NULL,
  start_label VARCHAR(60) NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  deadline_label VARCHAR(60) NULL,
  remaining VARCHAR(40) NULL,
  urgency ENUM('hot','new','closing','normal') NOT NULL DEFAULT 'new',
  trades VARCHAR(255) NULL,
  phase VARCHAR(60) NULL,
  progress TINYINT NOT NULL DEFAULT 0,
  status ENUM('draft','open','active','hold','completed','cancelled') NOT NULL DEFAULT 'open',
  visibility ENUM('private','public') NOT NULL DEFAULT 'public',
  bids_count INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_project_public (public_id),
  KEY idx_project_owner (owner_user_id),
  KEY idx_project_feed (visibility, status, created_at),
  CONSTRAINT fk_project_owner  FOREIGN KEY (owner_user_id) REFERENCES users(id)   ON DELETE SET NULL,
  CONSTRAINT fk_project_region FOREIGN KEY (region_id)     REFERENCES regions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bids (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  project_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  bidder_name VARCHAR(160) NULL,
  amount VARCHAR(60) NULL,
  timeline VARCHAR(60) NULL,
  message TEXT NULL,
  status ENUM('submitted','shortlisted','awarded','rejected') NOT NULL DEFAULT 'submitted',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_bid_project (project_id, status),
  KEY idx_bid_user (user_id),
  CONSTRAINT fk_bid_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  CONSTRAINT fk_bid_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

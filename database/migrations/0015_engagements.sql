-- ════════════════════════════════════════════════════════════
-- 0015 · Provider engagements — invites/quotes (leads) + reviews
--   Backs the Hire/Invite, Request-quote and Leave-a-review actions.
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS provider_leads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  provider_id BIGINT UNSIGNED NOT NULL,
  type ENUM('invite','quote') NOT NULL DEFAULT 'invite',
  from_user_id BIGINT UNSIGNED NULL,
  from_name VARCHAR(160) NOT NULL,
  from_email VARCHAR(160) NULL,
  from_phone VARCHAR(40) NULL,
  subject VARCHAR(200) NULL,
  budget VARCHAR(80) NULL,
  timeline VARCHAR(80) NULL,
  message TEXT NULL,
  status ENUM('new','read','responded','closed') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_lead_public (public_id),
  KEY idx_lead_provider (provider_id, status, created_at),
  CONSTRAINT fk_lead_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_lead_user     FOREIGN KEY (from_user_id) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS provider_reviews (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  provider_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  reviewer_name VARCHAR(160) NOT NULL,
  rating TINYINT NOT NULL,
  project VARCHAR(200) NULL,
  body TEXT NULL,
  status ENUM('published','pending','hidden') NOT NULL DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_review_public (public_id),
  KEY idx_review_provider (provider_id, status, created_at),
  CONSTRAINT fk_review_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_review_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

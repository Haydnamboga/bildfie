-- ════════════════════════════════════════════════════════════
-- 0012 · Clients — reusable client directory, linked to documents
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS clients (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(160) NULL,
  phone VARCHAR(40) NULL,
  company VARCHAR(160) NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_client_public (public_id),
  KEY idx_client_user (user_id, name),
  CONSTRAINT fk_client_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link documents to a client record (kept alongside the free-text client_name)
ALTER TABLE documents ADD COLUMN IF NOT EXISTS client_id BIGINT UNSIGNED NULL AFTER client_name;
ALTER TABLE documents ADD INDEX IF NOT EXISTS idx_document_client (client_id);

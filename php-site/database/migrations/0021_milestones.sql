-- ════════════════════════════════════════════════════════════
-- 0021 · Project milestones — stages, values, due dates & payments
--   Powers the "Per milestone" billing model: each milestone carries a
--   value, a due date, a work status, and a payment-release flag (escrow).
--   Completing milestones drives the project's progress automatically.
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS project_milestones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description VARCHAR(600) NULL,
  amount DECIMAL(14,2) NULL,
  due_date DATE NULL,
  status ENUM('upcoming','active','completed') NOT NULL DEFAULT 'upcoming',
  released TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  completed_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ms_project (project_id, sort_order),
  CONSTRAINT fk_ms_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

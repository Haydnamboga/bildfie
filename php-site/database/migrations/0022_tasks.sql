-- ════════════════════════════════════════════════════════════
-- 0022 · Project tasks — hierarchical sections → subtasks, with a
--   team (by role) per task and a payment/milestone per task.
--   Seeded from a default construction work-breakdown on project create.
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS project_tasks (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  project_id BIGINT UNSIGNED NOT NULL,
  parent_id BIGINT UNSIGNED NULL,                 -- NULL = top-level section
  title VARCHAR(200) NOT NULL,
  description VARCHAR(600) NULL,
  status ENUM('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
  amount DECIMAL(14,2) NULL,                       -- payment value for this stage
  due_date DATE NULL,
  is_milestone TINYINT(1) NOT NULL DEFAULT 0,      -- a payment milestone
  released TINYINT(1) NOT NULL DEFAULT 0,          -- payment released / settled
  sort_order INT NOT NULL DEFAULT 0,
  completed_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_task_project (project_id, parent_id, sort_order),
  CONSTRAINT fk_task_project FOREIGN KEY (project_id) REFERENCES projects(id)      ON DELETE CASCADE,
  CONSTRAINT fk_task_parent  FOREIGN KEY (parent_id)  REFERENCES project_tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_task_members (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  task_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  role VARCHAR(60) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_tm_task (task_id, sort_order),
  CONSTRAINT fk_tm_task FOREIGN KEY (task_id) REFERENCES project_tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

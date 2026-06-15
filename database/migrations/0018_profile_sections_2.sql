-- ════════════════════════════════════════════════════════════
-- 0018 · Remaining editable profile sections
--   Work history, education/training, and self-reported performance
--   highlights. Same user_id-keyed pattern as 0017.
-- ════════════════════════════════════════════════════════════

-- Work history (Upwork-style timeline)
CREATE TABLE IF NOT EXISTS user_work_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  role VARCHAR(120) NOT NULL,
  organization VARCHAR(160) NULL,
  period VARCHAR(60) NULL,
  description VARCHAR(600) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_work_user (user_id, sort_order),
  CONSTRAINT fk_work_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Education & training
CREATE TABLE IF NOT EXISTS user_education (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  institution VARCHAR(200) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_edu_user (user_id, sort_order),
  CONSTRAINT fk_edu_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Self-reported performance highlights — extra columns on the professional profile
ALTER TABLE user_professions
  ADD COLUMN IF NOT EXISTS jobs_completed    VARCHAR(20) NULL,
  ADD COLUMN IF NOT EXISTS on_time_pct       VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS repeat_pct        VARCHAR(10) NULL,
  ADD COLUMN IF NOT EXISTS response_time     VARCHAR(60) NULL,
  ADD COLUMN IF NOT EXISTS availability_note VARCHAR(120) NULL;

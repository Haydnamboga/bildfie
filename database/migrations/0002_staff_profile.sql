-- ════════════════════════════════════════════════════════════
-- 0002 · Staff profile — cover photo + protected-account flag
-- ════════════════════════════════════════════════════════════

ALTER TABLE staff
  ADD COLUMN cover_url VARCHAR(255) NULL AFTER photo_url;

-- is_protected = 1 → owner/super account that can never be deleted and is
-- blocked from any destructive ("burn the operation") action.
ALTER TABLE staff
  ADD COLUMN is_protected TINYINT(1) NOT NULL DEFAULT 0 AFTER status;

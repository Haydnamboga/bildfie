-- ════════════════════════════════════════════════════════════
-- 0023 · Link hire/invite/quote engagements to one of the owner's projects
-- ════════════════════════════════════════════════════════════

ALTER TABLE provider_engagements
  ADD COLUMN IF NOT EXISTS project_id BIGINT UNSIGNED NULL AFTER provider_id;

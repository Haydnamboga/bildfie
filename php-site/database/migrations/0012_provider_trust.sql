-- ════════════════════════════════════════════════════════════
-- 0012 · Provider trust signals — admin-awardable badges
--   Shown on professional cards + the public profile; toggled from
--   the admin Users list. ID-verified, featured & available already exist.
-- ════════════════════════════════════════════════════════════

ALTER TABLE providers
  ADD COLUMN IF NOT EXISTS is_certified          TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_insured            TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_background_checked TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_top_rated          TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_escrow             TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_elite              TINYINT(1) NOT NULL DEFAULT 0;

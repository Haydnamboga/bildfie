-- ════════════════════════════════════════════════════════════
-- 0013 · More provider trust badges — each independent & admin-awardable
--   bildfie Choice · Safety Certified · Bonded · Warranty · Tax Compliant
--   · Phone Verified · Fast Responder
-- ════════════════════════════════════════════════════════════

ALTER TABLE providers
  ADD COLUMN IF NOT EXISTS is_preferred       TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_safety_certified TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_bonded          TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_warranty        TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_tax_compliant   TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_phone_verified  TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_fast_responder  TINYINT(1) NOT NULL DEFAULT 0;

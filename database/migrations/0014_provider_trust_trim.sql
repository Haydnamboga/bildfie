-- ════════════════════════════════════════════════════════════
-- 0014 · Trim provider badges — drop the ones we don't use
--   safety / bonded / background-checked / tax-compliant / phone-verified.
--   (is_available stays as a column — it drives the availability dot, not a badge.)
-- ════════════════════════════════════════════════════════════

ALTER TABLE providers
  DROP COLUMN IF EXISTS is_safety_certified,
  DROP COLUMN IF EXISTS is_bonded,
  DROP COLUMN IF EXISTS is_background_checked,
  DROP COLUMN IF EXISTS is_tax_compliant,
  DROP COLUMN IF EXISTS is_phone_verified;

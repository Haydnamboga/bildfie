-- ════════════════════════════════════════════════════════════
-- 0006 · Connect a member's professional profile to the public
--        Providers listing (Offer services → /professionals)
-- ════════════════════════════════════════════════════════════

-- Member-uploaded media for their public listing
ALTER TABLE user_professions
  ADD COLUMN IF NOT EXISTS photo_url VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS cover_url VARCHAR(255) NULL;

-- One provider listing per member (NULLs allowed → seeded providers unaffected)
ALTER TABLE providers
  ADD UNIQUE INDEX IF NOT EXISTS uq_provider_user (user_id);

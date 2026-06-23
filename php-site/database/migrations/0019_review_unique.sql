-- ════════════════════════════════════════════════════════════
-- 0019 · One review per reviewer per provider
--   Adds the unique key review_create() upserts against, so a client
--   editing their review updates it instead of creating duplicates.
--   (user_id is NULL for anonymous reviews — MySQL allows multiple NULLs,
--    and the review API always supplies a signed-in reviewer.)
-- ════════════════════════════════════════════════════════════

ALTER TABLE provider_reviews
  ADD UNIQUE INDEX IF NOT EXISTS uq_review_reviewer (provider_id, user_id);

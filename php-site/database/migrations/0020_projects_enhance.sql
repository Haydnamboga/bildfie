-- ════════════════════════════════════════════════════════════
-- 0020 · Projects enhancement — client, billing, timeline & collaboration
--   Adds the fields the dashboard project workspace needs: a customer,
--   a billing model, real dates, priority, and per-project settings for
--   how the owner and their client collaborate. Also widens the status
--   set to the full delivery lifecycle.
-- ════════════════════════════════════════════════════════════

-- Widen the status lifecycle (superset keeps existing rows valid), then migrate old values.
ALTER TABLE projects
  MODIFY COLUMN status ENUM('draft','open','active','started','in_progress','hold','on_hold','completed','finished','cancelled')
         NOT NULL DEFAULT 'open';
UPDATE projects SET status='in_progress' WHERE status='active';
UPDATE projects SET status='on_hold'     WHERE status='hold';
UPDATE projects SET status='finished'    WHERE status='completed';

-- Client + billing + timeline + workflow fields
ALTER TABLE projects
  ADD COLUMN IF NOT EXISTS customer_name   VARCHAR(160) NULL AFTER name,
  ADD COLUMN IF NOT EXISTS customer_email  VARCHAR(160) NULL AFTER customer_name,
  ADD COLUMN IF NOT EXISTS customer_phone  VARCHAR(40)  NULL AFTER customer_email,
  ADD COLUMN IF NOT EXISTS billing_type    VARCHAR(30)  NOT NULL DEFAULT 'milestone',
  ADD COLUMN IF NOT EXISTS payment_terms   VARCHAR(200) NULL,
  ADD COLUMN IF NOT EXISTS currency        CHAR(3)      NOT NULL DEFAULT 'KES',
  ADD COLUMN IF NOT EXISTS priority        VARCHAR(20)  NOT NULL DEFAULT 'normal',
  ADD COLUMN IF NOT EXISTS est_completion  DATE         NULL,
  ADD COLUMN IF NOT EXISTS deadline        DATE         NULL;

-- Per-project collaboration settings (owner ⇄ client)
ALTER TABLE projects
  ADD COLUMN IF NOT EXISTS client_can_comment        TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS client_can_view_budget    TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS client_can_view_documents TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS require_milestone_approval TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS use_escrow                TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS notify_client_updates     TINYINT(1) NOT NULL DEFAULT 1;

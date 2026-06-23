-- ════════════════════════════════════════════════════════════
-- 0007 · Member wallet — balance + transaction ledger
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS wallets (
  user_id BIGINT UNSIGNED NOT NULL,
  balance DECIMAL(14,2) NOT NULL DEFAULT 0,
  currency_code CHAR(3) NOT NULL DEFAULT 'KES',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallet_transactions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  type ENUM('deposit','withdrawal','payment','payout','refund','fee') NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  balance_after DECIMAL(14,2) NOT NULL,
  status ENUM('completed','pending','failed') NOT NULL DEFAULT 'completed',
  method VARCHAR(40) NULL,
  reference VARCHAR(60) NULL,
  description VARCHAR(200) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_wtx_user (user_id, created_at),
  CONSTRAINT fk_wtx_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

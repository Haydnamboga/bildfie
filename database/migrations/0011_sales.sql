-- ════════════════════════════════════════════════════════════
-- 0011 · Transaction core — sales documents, inventory, contracts
--   • documents      → invoices / estimates / proposals / credit notes (one engine)
--   • document_items → line items for any document
--   • inventory_items→ stock & catalogue items
--   • contracts      → agreements with e-sign lifecycle
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS documents (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  doc_type ENUM('invoice','estimate','proposal','credit_note') NOT NULL,
  number VARCHAR(40) NOT NULL,
  client_name VARCHAR(160) NOT NULL,
  client_email VARCHAR(160) NULL,
  client_phone VARCHAR(40) NULL,
  subject VARCHAR(200) NULL,
  project_id BIGINT UNSIGNED NULL,
  issue_date DATE NULL,
  due_date DATE NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'KES',
  subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL DEFAULT 0,
  notes TEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  converted_to_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_document_public (public_id),
  KEY idx_document_list (user_id, doc_type, created_at),
  KEY idx_document_status (user_id, doc_type, status),
  CONSTRAINT fk_document_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_document_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS document_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  document_id BIGINT UNSIGNED NOT NULL,
  description VARCHAR(255) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(14,2) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_item_document (document_id),
  CONSTRAINT fk_item_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(200) NOT NULL,
  sku VARCHAR(80) NULL,
  item_type ENUM('material','hardware','software','service') NOT NULL DEFAULT 'material',
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  quantity INT NOT NULL DEFAULT 0,
  unit VARCHAR(30) NULL,
  reorder_level INT NOT NULL DEFAULT 0,
  location VARCHAR(120) NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inventory_public (public_id),
  KEY idx_inventory_user (user_id, item_type),
  CONSTRAINT fk_inventory_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contracts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  number VARCHAR(40) NOT NULL,
  title VARCHAR(200) NOT NULL,
  counterparty VARCHAR(160) NOT NULL,
  project_id BIGINT UNSIGNED NULL,
  value DECIMAL(14,2) NULL,
  currency VARCHAR(8) NOT NULL DEFAULT 'KES',
  start_date DATE NULL,
  end_date DATE NULL,
  body TEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  signed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_contract_public (public_id),
  KEY idx_contract_list (user_id, status, created_at),
  CONSTRAINT fk_contract_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_contract_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

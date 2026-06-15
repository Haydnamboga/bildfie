-- ════════════════════════════════════════════════════════════
-- 0009 · Marketplace supply — suppliers + unified listings
--   Backs materials / equipment / transport / facilities pages.
-- ════════════════════════════════════════════════════════════

-- Materials suppliers (vendor cards)
CREATE TABLE IF NOT EXISTS suppliers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  code VARCHAR(8) NOT NULL,
  name VARCHAR(160) NOT NULL,
  location VARCHAR(160) NULL,
  region_id BIGINT UNSIGNED NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0,
  reviews_count INT NOT NULL DEFAULT 0,
  phone VARCHAR(40) NULL,
  delivery_info VARCHAR(200) NULL,
  logo_bg VARCHAR(16) NULL,
  logo_color VARCHAR(16) NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','suspended') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_supplier_public (public_id),
  CONSTRAINT fk_supplier_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Unified listings (materials products, equipment, vehicles, facilities)
CREATE TABLE IF NOT EXISTS listings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  kind ENUM('materials','equipment','transport','facilities') NOT NULL,
  supplier_id BIGINT UNSIGNED NULL,
  title VARCHAR(180) NOT NULL,
  category VARCHAR(100) NULL,
  description TEXT NULL,
  image_url VARCHAR(255) NULL,
  icon VARCHAR(60) NULL,
  badge VARCHAR(80) NULL,
  avail_label VARCHAR(40) NULL,
  price VARCHAR(60) NULL,
  price_secondary VARCHAR(60) NULL,
  price_change VARCHAR(20) NULL,
  rating DECIMAL(2,1) NULL,
  usage_count INT NULL,
  vendor_name VARCHAR(160) NULL,
  location VARCHAR(160) NULL,
  region_id BIGINT UNSIGNED NULL,
  specs TEXT NULL,
  tags VARCHAR(255) NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('active','pending','suspended') NOT NULL DEFAULT 'active',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_listing_public (public_id),
  KEY idx_listing_kind (kind, status, sort_order),
  KEY idx_listing_supplier (supplier_id),
  CONSTRAINT fk_listing_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
  CONSTRAINT fk_listing_region   FOREIGN KEY (region_id)   REFERENCES regions(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

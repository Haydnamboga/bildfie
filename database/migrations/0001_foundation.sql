-- ════════════════════════════════════════════════════════════
-- 0001 · Foundation — Identity, Access & Config
-- ════════════════════════════════════════════════════════════

-- App settings (key/value)
CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(120) NOT NULL,
  `value` TEXT NULL,
  `group` VARCHAR(60) NOT NULL DEFAULT 'general',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currencies
CREATE TABLE IF NOT EXISTS currencies (
  code CHAR(3) NOT NULL,
  name VARCHAR(60) NOT NULL,
  symbol VARCHAR(8) NOT NULL,
  is_base TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Regions / markets
CREATE TABLE IF NOT EXISTS regions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(8) NOT NULL,
  name VARCHAR(80) NOT NULL,
  currency_code CHAR(3) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_region_code (code),
  KEY idx_region_currency (currency_code),
  CONSTRAINT fk_region_currency FOREIGN KEY (currency_code) REFERENCES currencies(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Departments
CREATE TABLE IF NOT EXISTS departments (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(40) NOT NULL,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dept_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles
CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(40) NOT NULL,
  name VARCHAR(120) NOT NULL,
  level VARCHAR(4) NOT NULL DEFAULT 'L4',
  scope VARCHAR(255) NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_role_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions
CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(80) NOT NULL,
  name VARCHAR(160) NOT NULL,
  module VARCHAR(60) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_perm_code (code),
  KEY idx_perm_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role <-> Permission
CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  KEY idx_rp_perm (permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff (back-office / internal users)
CREATE TABLE IF NOT EXISTS staff (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(32) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role_id BIGINT UNSIGNED NULL,
  department_id BIGINT UNSIGNED NULL,
  photo_url VARCHAR(255) NULL,
  status ENUM('active','suspended','invited') NOT NULL DEFAULT 'active',
  onboarded_year SMALLINT NULL,
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_staff_email (email),
  UNIQUE KEY uq_staff_public (public_id),
  KEY idx_staff_role (role_id),
  KEY idx_staff_dept (department_id),
  CONSTRAINT fk_staff_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL,
  CONSTRAINT fk_staff_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users (marketplace members)
CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  phone VARCHAR(32) NULL,
  password_hash VARCHAR(255) NULL,
  region_id BIGINT UNSIGNED NULL,
  is_provider TINYINT(1) NOT NULL DEFAULT 0,
  is_client TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('active','pending','suspended','deleted') NOT NULL DEFAULT 'active',
  email_verified_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_email (email),
  UNIQUE KEY uq_user_public (public_id),
  KEY idx_user_region (region_id),
  CONSTRAINT fk_user_region FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Immutable audit trail
CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_type ENUM('staff','user','system') NOT NULL DEFAULT 'staff',
  actor_id BIGINT UNSIGNED NULL,
  actor_name VARCHAR(120) NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(80) NULL,
  entity_id VARCHAR(64) NULL,
  meta JSON NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_actor (actor_type, actor_id),
  KEY idx_audit_entity (entity_type, entity_id),
  KEY idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ───────── reference seed data ─────────
INSERT IGNORE INTO currencies (code,name,symbol,is_base) VALUES
 ('KES','Kenyan Shilling','KES',1),
 ('TZS','Tanzanian Shilling','TSh',0),
 ('UGX','Ugandan Shilling','USh',0),
 ('NGN','Nigerian Naira','NGN',0),
 ('USD','US Dollar','$',0);

INSERT IGNORE INTO regions (code,name,currency_code) VALUES
 ('KE','Kenya','KES'),('TZ','Tanzania','TZS'),('UG','Uganda','UGX'),
 ('RW','Rwanda',NULL),('NG','Nigeria','NGN'),('GH','Ghana',NULL),('ZA','South Africa',NULL);

INSERT IGNORE INTO departments (code,name) VALUES
 ('exec','Executive'),('ops','Operations'),('finance','Finance'),
 ('tech','Technical'),('people','People & HR'),('marketing','Marketing'),
 ('bizdev','Business Dev'),('support','Support'),('trust','Trust & Safety');

INSERT IGNORE INTO roles (code,name,level,scope,is_system) VALUES
 ('super','Super Admin','L0','Owner — full access',1),
 ('ceo','CEO','L1','Whole business · strategy',1),
 ('coo','COO','L1','Operations, projects & support',1),
 ('cfo','CFO','L1','Financials, revenue & escrow',1),
 ('cto','CTO','L1','Technical, integrations & security',1),
 ('chro','CHRO','L1','People, team & HR',1),
 ('cmo','CMO','L1','Marketing, CRM & growth',1),
 ('bdm','Business Development Mgr','L2','CRM, leads & partnerships',0),
 ('salesmgr','Sales Manager','L2','Sales & revenue',0),
 ('finmgr','Finance Manager','L2','Accounting, invoices & payments',0),
 ('support','Support Lead','L2','Tickets, SLAs & collaboration',0),
 ('agent','Sales Agent','L4','Assigned leads & follow-ups',0);

INSERT IGNORE INTO permissions (code,name,module) VALUES
 ('dashboard.view','View dashboard','dashboard'),
 ('finance.view','View financials','finance'),
 ('finance.payout.approve','Approve payouts','finance'),
 ('trust.moderate','Moderate content','trust'),
 ('users.manage','Manage users','users'),
 ('roles.manage','Manage roles & permissions','system'),
 ('ads.manage','Manage advertising','ads'),
 ('catalog.manage','Manage catalog & verticals','catalog');

INSERT IGNORE INTO settings (`key`,`value`,`group`) VALUES
 ('app_name','bildfie','general'),
 ('app_version','0.0.1','general'),
 ('base_currency','KES','finance'),
 ('take_rate','6.8','finance');

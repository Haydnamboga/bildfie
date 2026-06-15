-- ════════════════════════════════════════════════════════════
-- 0003 · Catalog & Verticals — Services + Learn taxonomy
-- ════════════════════════════════════════════════════════════

-- Service verticals (Construction, Home, Beauty, …)
CREATE TABLE IF NOT EXISTS verticals (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  icon VARCHAR(60) NOT NULL DEFAULT 'bi-grid',
  color VARCHAR(16) NOT NULL DEFAULT '#1e3a5f',
  bg VARCHAR(16) NOT NULL DEFAULT '#eaf0f6',
  href VARCHAR(160) NULL,
  provider_count INT NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vertical_slug (slug),
  KEY idx_vertical_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories / services within a vertical (Plumbers, Architects, …)
CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  vertical_id BIGINT UNSIGNED NOT NULL,
  slug VARCHAR(140) NOT NULL,
  name VARCHAR(140) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_category (vertical_id, slug),
  KEY idx_category_vertical (vertical_id, sort_order),
  CONSTRAINT fk_category_vertical FOREIGN KEY (vertical_id) REFERENCES verticals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learn — academic levels (Early Years, Primary, University, …)
CREATE TABLE IF NOT EXISTS learn_levels (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  emoji VARCHAR(16) NULL,
  age_label VARCHAR(140) NULL,
  gradient VARCHAR(140) NULL,
  resource_count INT NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_level_slug (slug),
  KEY idx_level_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Learn — content types within a level (Homework Help, Past Papers, Thesis, …)
CREATE TABLE IF NOT EXISTS learn_subjects (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  level_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(140) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_subject_level (level_id, sort_order),
  CONSTRAINT fk_subject_level FOREIGN KEY (level_id) REFERENCES learn_levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

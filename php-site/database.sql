-- ============================================================
--  bildfie — MySQL schema + seed data
--  Import this in phpMyAdmin (Import tab) AFTER creating your
--  database, OR run:  mysql -u USER -p DBNAME < database.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ---------- users ----------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(150) NOT NULL,
  role          ENUM('USER','ADMIN','SUPER_ADMIN') NOT NULL DEFAULT 'USER',
  headline      VARCHAR(200) DEFAULT NULL,
  bio           TEXT DEFAULT NULL,
  skills        VARCHAR(500) DEFAULT NULL,
  hourly_rate   DECIMAL(10,2) DEFAULT NULL,
  location      VARCHAR(150) DEFAULT NULL,
  category      VARCHAR(40) DEFAULT NULL,
  pro_level     ENUM('STARTER','VERIFIED','TOP_RATED') NOT NULL DEFAULT 'STARTER',
  is_pro        TINYINT(1) NOT NULL DEFAULT 0,
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- job_posts ----------
CREATE TABLE IF NOT EXISTS job_posts (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  client_id   INT NOT NULL,
  title       VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  category    VARCHAR(40) NOT NULL,
  location    VARCHAR(150) NOT NULL,
  budget_min  DECIMAL(12,2) DEFAULT NULL,
  budget_max  DECIMAL(12,2) DEFAULT NULL,
  due_date    DATE DEFAULT NULL,
  status      ENUM('OPEN','IN_REVIEW','AWARDED','CLOSED') NOT NULL DEFAULT 'OPEN',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_jobs_status (status),
  INDEX idx_jobs_category (category),
  CONSTRAINT fk_jobs_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- proposals ----------
CREATE TABLE IF NOT EXISTS proposals (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  job_post_id  INT NOT NULL,
  pro_id       INT NOT NULL,
  cover_letter TEXT NOT NULL,
  amount       DECIMAL(12,2) NOT NULL,
  timeline     VARCHAR(150) DEFAULT NULL,
  status       ENUM('PENDING','SHORTLISTED','ACCEPTED','REJECTED','WITHDRAWN') NOT NULL DEFAULT 'PENDING',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_job_pro (job_post_id, pro_id),
  CONSTRAINT fk_prop_job FOREIGN KEY (job_post_id) REFERENCES job_posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_prop_pro FOREIGN KEY (pro_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- projects ----------
CREATE TABLE IF NOT EXISTS projects (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  owner_id    INT NOT NULL,
  title       VARCHAR(200) NOT NULL,
  description TEXT DEFAULT NULL,
  status      ENUM('DRAFT','ACTIVE','COMPLETED','CANCELLED') NOT NULL DEFAULT 'ACTIVE',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_proj_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- portfolio_items ----------
CREATE TABLE IF NOT EXISTS portfolio_items (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  title        VARCHAR(200) NOT NULL,
  description  TEXT DEFAULT NULL,
  image_url    VARCHAR(500) DEFAULT NULL,
  category     VARCHAR(40) DEFAULT NULL,
  completed_at DATE DEFAULT NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_port_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- reviews ----------
CREATE TABLE IF NOT EXISTS reviews (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  author_id  INT NOT NULL,
  subject_id INT NOT NULL,
  rating     TINYINT NOT NULL,
  comment    TEXT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rev_author  FOREIGN KEY (author_id)  REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_rev_subject FOREIGN KEY (subject_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Seed: Super Admin account
--  Login:    admin@bildfie.com
--  Password: Admin@Bildfie2026   (change after first login)
-- ============================================================
INSERT INTO users (email, password_hash, full_name, role, email_verified, is_pro, pro_level)
VALUES (
  'admin@bildfie.com',
  '$2y$12$kGTP5O/TofUJx4IAydkSTuzfqeuaadp/T9cq17M1ve11mSic6H3zK',
  'Bildfie Admin',
  'SUPER_ADMIN',
  1, 0, 'TOP_RATED'
) ON DUPLICATE KEY UPDATE email = email;

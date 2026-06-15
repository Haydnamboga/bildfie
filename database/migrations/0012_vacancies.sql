-- ════════════════════════════════════════════════════════════
-- 0012 · Company vacancies + direct applications
--   • company_vacancies   → job openings a company advertises
--   • vacancy_applications → a member's direct application to a vacancy
-- ════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS company_vacancies (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  public_id CHAR(36) NOT NULL,
  company_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  employment_type VARCHAR(40) NOT NULL DEFAULT 'Full-time',
  location VARCHAR(160) NULL,
  salary_display VARCHAR(80) NULL,
  description TEXT NULL,
  status ENUM('open','closed') NOT NULL DEFAULT 'open',
  applications_count INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vacancy_public (public_id),
  KEY idx_vacancy_company (company_id, status),
  CONSTRAINT fk_vacancy_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vacancy_applications (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  vacancy_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  applicant_name VARCHAR(160) NULL,
  applicant_email VARCHAR(160) NULL,
  message TEXT NULL,
  status ENUM('submitted','reviewed','shortlisted','rejected','hired') NOT NULL DEFAULT 'submitted',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_application (vacancy_id, user_id),
  KEY idx_application_user (user_id),
  CONSTRAINT fk_application_vacancy FOREIGN KEY (vacancy_id) REFERENCES company_vacancies(id) ON DELETE CASCADE,
  CONSTRAINT fk_application_user    FOREIGN KEY (user_id)    REFERENCES users(id)             ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

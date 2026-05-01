CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','employee','core','qa','accounts') NOT NULL,
  base_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  commission_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role_active (role, is_active)
);

CREATE TABLE clients (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(140) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tasks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_id BIGINT UNSIGNED NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  assignee_id BIGINT UNSIGNED NOT NULL,
  task_value DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('pending','in_progress','completed','review','deliverable') NOT NULL DEFAULT 'pending',
  submission_link VARCHAR(500) NULL,
  qa_feedback TEXT NULL,
  is_locked TINYINT(1) NOT NULL DEFAULT 0,
  snapshot_percentage DECIMAL(5,2) NULL,
  snapshot_payout DECIMAL(12,2) NULL,
  payout_status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  completed_at TIMESTAMP NULL,
  delivered_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tasks_assignee FOREIGN KEY (assignee_id) REFERENCES users(id),
  CONSTRAINT fk_tasks_client FOREIGN KEY (client_id) REFERENCES clients(id),
  INDEX idx_tasks_status_assignee (status, assignee_id),
  INDEX idx_tasks_payout_status (payout_status)
);

CREATE TABLE payouts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  task_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  payout_type ENUM('salary','commission') NOT NULL,
  payment_status ENUM('pending','released') NOT NULL DEFAULT 'pending',
  released_by BIGINT UNSIGNED NULL,
  released_at TIMESTAMP NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payouts_task FOREIGN KEY (task_id) REFERENCES tasks(id),
  CONSTRAINT fk_payouts_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_payouts_released_by FOREIGN KEY (released_by) REFERENCES users(id),
  INDEX idx_payouts_user_status (user_id, payment_status)
);

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(120) NULL,
  entity_id BIGINT UNSIGNED NULL,
  metadata_json JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id),
  INDEX idx_audit_actor_date (actor_id, created_at)
);

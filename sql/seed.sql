INSERT INTO users (name,email,password_hash,role,salary,commission_pct,is_active) VALUES
('Admin User','admin@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','Admin',0,0,1),
('Employee One','employee@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','Employee',2000,10,1),
('Core Employee','core@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','Core Employee',2500,12,1),
('QA User','qa@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','QA',0,0,1),
('Accounts User','accounts@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','Accounts',0,0,1)
ON DUPLICATE KEY UPDATE
name = VALUES(name),
password_hash = VALUES(password_hash),
role = VALUES(role),
salary = VALUES(salary),
commission_pct = VALUES(commission_pct),
is_active = VALUES(is_active);

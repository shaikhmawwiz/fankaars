INSERT INTO users (name,email,password_hash,role,base_salary,commission_percentage,is_active) VALUES
('Admin User','admin@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','admin',0,0,1),
('Employee One','employee@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','employee',2500,0,1),
('Core Employee','core@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','core',0,12,1),
('QA User','qa@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','qa',0,0,1),
('Accounts User','accounts@example.com','$2y$12$drmF.PuDP0Zg25OEjpN4vOZ8ryj1o3POfzTtc3xxYYoO1MOusX5Z.','accounts',0,0,1)
ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),role=VALUES(role),base_salary=VALUES(base_salary),commission_percentage=VALUES(commission_percentage),is_active=VALUES(is_active);

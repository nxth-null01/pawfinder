CREATE DATABASE IF NOT EXISTS pawfinder_db;
USE pawfinder_db;

DROP TABLE IF EXISTS close_requests;
DROP TABLE IF EXISTS sightings;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  report_type ENUM('missing','found') NOT NULL,
  animal_name VARCHAR(100),
  species VARCHAR(50) NOT NULL,
  breed VARCHAR(100),
  color VARCHAR(100) NOT NULL,
  gender VARCHAR(20) DEFAULT 'Unknown',
  last_seen_date DATE NOT NULL,
  location VARCHAR(255) NOT NULL,
  owner_contact VARCHAR(255),
  description TEXT,
  photo VARCHAR(255),
  status ENUM('active','reunited','closed') DEFAULT 'active',
  is_approved TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sightings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(120) NOT NULL,
  location VARCHAR(255) NOT NULL,
  note TEXT,
  photo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
);

CREATE TABLE close_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(120) NOT NULL,
  result_status ENUM('reunited','closed') NOT NULL DEFAULT 'reunited',
  note TEXT NOT NULL,
  proof_photo VARCHAR(255),
  request_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
);

INSERT INTO users(name,email,password,role) VALUES
('Admin','admin@pawfinder.test','$2y$12$lBgh6.YufqBP02C1yNyjPuBxjHsIouHJq73LKOnT1Z.DAVW/VWJE6','admin'),
('Demo User','user@pawfinder.test','$2y$12$lBgh6.YufqBP02C1yNyjPuBxjHsIouHJq73LKOnT1Z.DAVW/VWJE6','user');

INSERT INTO reports(user_id,report_type,animal_name,species,breed,color,gender,last_seen_date,location,owner_contact,description,photo,status,is_approved) VALUES
(2,'missing','Mochi','Dog','Shih Tzu','White and brown','Male',CURDATE(),'Quezon City near SM North','0917 000 0000 / Messenger: Demo User','Friendly dog wearing a yellow collar.','', 'active',1),
(2,'found','Unknown','Cat','Puspin','Orange tabby','Unknown',CURDATE(),'Barangay Bago Bantay','0917 111 1111','Found near a sari-sari store, looks healthy but scared.','', 'active',1);


-- If you already imported an older PawFinder database, run these instead of re-importing:
-- ALTER TABLE reports ADD COLUMN owner_contact VARCHAR(255) NULL AFTER location;
-- ALTER TABLE sightings ADD COLUMN photo VARCHAR(255) NULL AFTER note;

-- If you already imported an older PawFinder database, run this to add the verification feature:
CREATE TABLE IF NOT EXISTS close_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(120) NOT NULL,
  result_status ENUM('reunited','closed') NOT NULL DEFAULT 'reunited',
  note TEXT NOT NULL,
  proof_photo VARCHAR(255),
  request_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
);

-- PawJect feature upgrade: comments, notifications, and activity timeline
CREATE TABLE IF NOT EXISTS comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  user_id INT NULL,
  name VARCHAR(120) NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(report_id),
  INDEX(user_id)
);

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  report_id INT NULL,
  type VARCHAR(60) NOT NULL DEFAULT 'update',
  title VARCHAR(160) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(user_id),
  INDEX(report_id)
);

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  user_id INT NULL,
  icon VARCHAR(60) DEFAULT 'activity',
  title VARCHAR(160) NOT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(report_id),
  INDEX(user_id)
);

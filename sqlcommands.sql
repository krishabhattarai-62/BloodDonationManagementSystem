CREATE DATABASE blood_donation;
USE blood_donation;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    otp VARCHAR(255) NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    gender ENUM('male', 'female', 'other') NOT NULL,
    age INT NOT NULL,
    eligible TINYINT(1) DEFAULT 0,
    blood_group VARCHAR(5) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'donor') DEFAULT 'donor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (first_name, last_name, contact_number, address, email, gender, age, blood_group, password, role)
VALUES ('Admin', 'Admin', '0000000000', 'System', 'admin@test.com', 'male', 18, NULL, '$2y$10$im58ki/UkcWiaK8uTUAA3eOopYnPEwGAGLHGZ6ueZ1dVOooPF5uRq', 'admin');

CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    patient_name varchar(150) NOT NULL,
    blood_group varchar(5) NOT NULL,
    units int(11) DEFAULT 1,
    hospital varchar(200) DEFAULT NULL,
    contact varchar(20) DEFAULT NULL,
    document varchar(255) DEFAULT NULL,
    status enum('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    donation_date date NOT NULL,
    status enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id)
);
CREATE DATABASE blood_donation;
USE blood_donation;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    address VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    gender ENUM('male', 'female', 'other') NOT NULL,
    age INT NOT NULL,
    blood_group VARCHAR(5) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'donor') DEFAULT 'donor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (first_name, last_name, contact_number, address, email, gender, age, blood_group, password, role)
VALUES ('Admin', 'Admin', '0000000000', 'System', 'admin@test.com', 'male', 18, NULL, '$2y$10$im58ki/UkcWiaK8uTUAA3eOopYnPEwGAGLHGZ6ueZ1dVOooPF5uRq', 'admin');

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    scheduled_date DATE NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
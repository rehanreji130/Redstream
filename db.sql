-- Create Database
CREATE DATABASE blood_availability;
USE blood_availability;

-- Table: Admins
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(50) UNIQUE NOT NULL,
    admin_password VARCHAR(255) NOT NULL -- Store hashed passwords
);

-- Table: Hospitals
CREATE TABLE hospitals (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(100) NOT NULL,
    hospital_email VARCHAR(100) UNIQUE NOT NULL,
    hospital_phone VARCHAR(20) NOT NULL,
    hospital_address TEXT NOT NULL,
    hospital_latitude DECIMAL(10, 8),  -- For geolocation
    hospital_longitude DECIMAL(11, 8), -- For geolocation
    hospital_password VARCHAR(255) NOT NULL, -- Store hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: Recipients
CREATE TABLE recipients (
    recipient_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_name VARCHAR(100) NOT NULL,
    recipient_email VARCHAR(100) UNIQUE NOT NULL,
    recipient_phone VARCHAR(20) NOT NULL,
    recipient_password VARCHAR(255) NOT NULL, -- Store hashed passwords
    recipient_blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    recipient_latitude DECIMAL(10, 8),  -- For geolocation
    recipient_longitude DECIMAL(11, 8), -- For geolocation
    profile_picture VARCHAR(255) DEFAULT 'default_avatar.png', -- Added profile picture column
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: Donors
CREATE TABLE donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    donor_name VARCHAR(100) NOT NULL,
    donor_phone VARCHAR(20) NOT NULL,
    donor_email VARCHAR(100) NOT NULL,
    donor_blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    last_donation_date DATE,
    blood_units DECIMAL(5,2) NOT NULL DEFAULT 0, -- New column added
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE
);

-- Table: Blood Requests
CREATE TABLE blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    hospital_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') NOT NULL,
    request_status ENUM('pending', 'fulfilled', 'canceled') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES recipients(recipient_id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE
);

-- Table: Notifications
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    hospital_id INT NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE
);

CREATE TABLE search_history (
    search_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    blood_type VARCHAR(5) NOT NULL,
    latitude DECIMAL(10, 6) NOT NULL,
    longitude DECIMAL(10, 6) NOT NULL,
    urgency VARCHAR(20) NOT NULL DEFAULT 'normal',
    search_date DATETIME NOT NULL,
    FOREIGN KEY (recipient_id) REFERENCES recipients(recipient_id)
);

-- Add default admin Rehan
INSERT INTO admins (admin_username, admin_password)
VALUES ('Rehan', SHA2('1234', 256));
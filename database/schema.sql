
-- FuelTrackPro — Database Schema
-- Run this file ONCE in phpMyAdmin to set up the database.
-- Go to: http://localhost:8888/phpMyAdmin
-- Click the SQL tab (with NO database selected), paste this, and click Go.

CREATE DATABASE IF NOT EXISTS fueltrackpro_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fueltrackpro_db;

CREATE TABLE IF NOT EXISTS users (
    user_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)                          NOT NULL,
    email        VARCHAR(150)                          NOT NULL UNIQUE,
    password     VARCHAR(255)                          NOT NULL,
    role         ENUM('admin','manager','cashier')     NOT NULL DEFAULT 'cashier',
    hourly_rate  DECIMAL(8,2)                          NOT NULL DEFAULT 0.00,
    status       ENUM('active','inactive')             NOT NULL DEFAULT 'active',
    created_at   DATETIME                              NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Time entries table (clock in / clock out)
CREATE TABLE IF NOT EXISTS time_entries (
    entry_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    clock_in     DATETIME     NOT NULL,
    clock_out    DATETIME     NULL,
    total_hours  DECIMAL(6,2) NULL,
    late_reason VARCHAR(255) NULL,
    status       ENUM('open','closed') NOT NULL DEFAULT 'open',
    CONSTRAINT fk_time_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);


-- Default admin Account
INSERT INTO users (name, email, password, role, hourly_rate, status)
VALUES (
    'Admin User',
    'admin@fueltrackpro.com',
    'admin123',
    'admin',
     0.00,
    'active'
)
ON DUPLICATE KEY UPDATE name = VALUES(name);





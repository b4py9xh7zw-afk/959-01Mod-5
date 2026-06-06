-- Database initialization script
-- This script creates the necessary tables and seeds initial data

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create licenses table with seat management fields
CREATE TABLE IF NOT EXISTS licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_key VARCHAR(100) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    license_type ENUM('floating', 'fixed') DEFAULT 'floating',
    seat_status ENUM('idle', 'borrowed', 'abnormal') DEFAULT 'idle',
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_license_key (license_key),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_license_type (license_type),
    INDEX idx_seat_status (seat_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create seat_borrows table
CREATE TABLE IF NOT EXISTS seat_borrows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    license_id INT NOT NULL,
    department_id INT NOT NULL,
    borrower_id INT NOT NULL,
    approver_id INT NOT NULL,
    purpose TEXT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    actual_return_date DATETIME NULL,
    status ENUM('pending', 'approved', 'active', 'returned', 'expired', 'rejected', 'renew_pending') DEFAULT 'pending',
    business_reason TEXT NULL,
    renew_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_license_id (license_id),
    INDEX idx_department_id (department_id),
    INDEX idx_borrower_id (borrower_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create seat_borrow_history table for audit trail
CREATE TABLE IF NOT EXISTS seat_borrow_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    borrow_id INT NOT NULL,
    action ENUM('created', 'approved', 'borrowed', 'returned', 'renewed', 'rejected', 'expired') NOT NULL,
    operator_id INT NOT NULL,
    remark TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (borrow_id) REFERENCES seat_borrows(id) ON DELETE CASCADE,
    FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_borrow_id (borrow_id),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: User seeding is handled by PHP script (app/scripts/seed_users.php)
-- This ensures correct password hashing. Users will be created on first container startup.
-- Sample licenses will be created by app/scripts/seed_licenses.php after users are created.

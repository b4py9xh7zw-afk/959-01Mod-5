<?php
/**
 * SQLite Database initialization script
 * Creates all necessary tables for SQLite
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    echo "Initializing SQLite database...\n";
    
    $statements = [
        // Users table
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role TEXT DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
        
        // Departments table
        "CREATE TABLE IF NOT EXISTS departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE INDEX IF NOT EXISTS idx_departments_name ON departments(name)",
        
        // Licenses table
        "CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key VARCHAR(100) NOT NULL UNIQUE,
            user_id INTEGER NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            status TEXT DEFAULT 'active',
            license_type TEXT DEFAULT 'floating',
            seat_status TEXT DEFAULT 'idle',
            expires_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE INDEX IF NOT EXISTS idx_licenses_license_key ON licenses(license_key)",
        "CREATE INDEX IF NOT EXISTS idx_licenses_user_id ON licenses(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_licenses_status ON licenses(status)",
        "CREATE INDEX IF NOT EXISTS idx_licenses_license_type ON licenses(license_type)",
        "CREATE INDEX IF NOT EXISTS idx_licenses_seat_status ON licenses(seat_status)",
        
        // Seat borrows table
        "CREATE TABLE IF NOT EXISTS seat_borrows (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_id INTEGER NOT NULL,
            department_id INTEGER NOT NULL,
            borrower_id INTEGER NOT NULL,
            approver_id INTEGER NULL,
            purpose TEXT NOT NULL,
            start_date DATETIME NOT NULL,
            end_date DATETIME NOT NULL,
            actual_return_date DATETIME NULL,
            status TEXT DEFAULT 'pending',
            business_reason TEXT NULL,
            renew_count INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE CASCADE,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
            FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrows_license_id ON seat_borrows(license_id)",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrows_department_id ON seat_borrows(department_id)",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrows_borrower_id ON seat_borrows(borrower_id)",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrows_status ON seat_borrows(status)",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrows_end_date ON seat_borrows(end_date)",
        
        // Seat borrow history table
        "CREATE TABLE IF NOT EXISTS seat_borrow_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            borrow_id INTEGER NOT NULL,
            action TEXT NOT NULL,
            operator_id INTEGER NOT NULL,
            remark TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (borrow_id) REFERENCES seat_borrows(id) ON DELETE CASCADE,
            FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrow_history_borrow_id ON seat_borrow_history(borrow_id)",
        "CREATE INDEX IF NOT EXISTS idx_seat_borrow_history_action ON seat_borrow_history(action)"
    ];
    
    foreach ($statements as $sql) {
        try {
            $db->execute($sql);
            echo "  - Executed: " . substr($sql, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "  - Warning: " . $e->getMessage() . "\n";
        }
    }
    
    // Seed default departments
    echo "\nSeeding default departments...\n";
    $departments = [
        ['name' => '研发部', 'description' => '产品研发团队'],
        ['name' => '测试部', 'description' => '质量测试团队'],
        ['name' => '运维部', 'description' => '系统运维团队'],
        ['name' => '市场部', 'description' => '市场营销团队'],
        ['name' => '财务部', 'description' => '财务会计团队'],
        ['name' => '人事部', 'description' => '人力资源团队'],
    ];
    
    foreach ($departments as $dept) {
        $existing = $db->fetchOne("SELECT id FROM departments WHERE name = :name", [':name' => $dept['name']]);
        if (!$existing) {
            $db->execute(
                "INSERT INTO departments (name, description) VALUES (:name, :description)",
                [':name' => $dept['name'], ':description' => $dept['description']]
            );
            echo "  - Added: {$dept['name']}\n";
        }
    }
    
    echo "\nSQLite database initialized successfully!\n";
    
} catch (Exception $e) {
    echo "Initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}

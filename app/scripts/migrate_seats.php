<?php
/**
 * Database migration for seat management feature
 * Run this script to add seat management tables and fields to existing database
 * Supports both MySQL and SQLite
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance();
    $dbType = getenv('DB_TYPE') ?: 'mysql';
    $isSqlite = $dbType === 'sqlite';
    
    echo "Starting seat management migration (DB type: {$dbType})...\n";

    // Add departments table
    echo "Creating departments table...\n";
    if ($isSqlite) {
        $db->execute("
            CREATE TABLE IF NOT EXISTS departments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_departments_name ON departments(name)");
    } else {
        $db->execute("
            CREATE TABLE IF NOT EXISTS departments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Check and add new columns to licenses table
    echo "Updating licenses table...\n";
    
    if ($isSqlite) {
        $columns = $db->fetchAll("PRAGMA table_info(licenses)");
        $columnNames = array_column($columns, 'name');
        
        if (!in_array('license_type', $columnNames)) {
            $db->execute("ALTER TABLE licenses ADD COLUMN license_type TEXT DEFAULT 'floating'");
            echo "  - Added license_type column\n";
        } else {
            echo "  - license_type column already exists\n";
        }

        if (!in_array('seat_status', $columnNames)) {
            $db->execute("ALTER TABLE licenses ADD COLUMN seat_status TEXT DEFAULT 'idle'");
            echo "  - Added seat_status column\n";
        } else {
            echo "  - seat_status column already exists\n";
        }
    } else {
        $columns = $db->fetchAll("SHOW COLUMNS FROM licenses LIKE 'license_type'");
        if (empty($columns)) {
            $db->execute("ALTER TABLE licenses ADD COLUMN license_type ENUM('floating', 'fixed') DEFAULT 'floating' AFTER status");
            echo "  - Added license_type column\n";
        } else {
            echo "  - license_type column already exists\n";
        }

        $columns = $db->fetchAll("SHOW COLUMNS FROM licenses LIKE 'seat_status'");
        if (empty($columns)) {
            $db->execute("ALTER TABLE licenses ADD COLUMN seat_status ENUM('idle', 'borrowed', 'abnormal') DEFAULT 'idle' AFTER license_type");
            echo "  - Added seat_status column\n";
        } else {
            echo "  - seat_status column already exists\n";
        }
    }

    // Add indexes
    if (!$isSqlite) {
        $db->execute("ALTER TABLE licenses ADD INDEX IF NOT EXISTS idx_license_type (license_type)");
        $db->execute("ALTER TABLE licenses ADD INDEX IF NOT EXISTS idx_seat_status (seat_status)");
    } else {
        $db->execute("CREATE INDEX IF NOT EXISTS idx_licenses_license_type ON licenses(license_type)");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_licenses_seat_status ON licenses(seat_status)");
    }

    // Add seat_borrows table
    echo "Creating seat_borrows table...\n";
    if ($isSqlite) {
        $db->execute("
            CREATE TABLE IF NOT EXISTS seat_borrows (
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
            )
        ");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrows_license_id ON seat_borrows(license_id)");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrows_department_id ON seat_borrows(department_id)");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrows_borrower_id ON seat_borrows(borrower_id)");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrows_status ON seat_borrows(status)");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrows_end_date ON seat_borrows(end_date)");
    } else {
        $db->execute("
            CREATE TABLE IF NOT EXISTS seat_borrows (
                id INT AUTO_INCREMENT PRIMARY KEY,
                license_id INT NOT NULL,
                department_id INT NOT NULL,
                borrower_id INT NOT NULL,
                approver_id INT NULL,
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
                FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL,
                INDEX idx_license_id (license_id),
                INDEX idx_department_id (department_id),
                INDEX idx_borrower_id (borrower_id),
                INDEX idx_status (status),
                INDEX idx_end_date (end_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Add seat_borrow_history table
    echo "Creating seat_borrow_history table...\n";
    if ($isSqlite) {
        $db->execute("
            CREATE TABLE IF NOT EXISTS seat_borrow_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                borrow_id INTEGER NOT NULL,
                action TEXT NOT NULL,
                operator_id INTEGER NOT NULL,
                remark TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (borrow_id) REFERENCES seat_borrows(id) ON DELETE CASCADE,
                FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrow_history_borrow_id ON seat_borrow_history(borrow_id)");
        $db->execute("CREATE INDEX IF NOT EXISTS idx_seat_borrow_history_action ON seat_borrow_history(action)");
    } else {
        $db->execute("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    // Seed default departments
    echo "Seeding default departments...\n";
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
            echo "  - Added department: {$dept['name']}\n";
        } else {
            echo "  - Department already exists: {$dept['name']}\n";
        }
    }

    echo "\nMigration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

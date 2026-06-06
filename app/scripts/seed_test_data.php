<?php
/**
 * Test data seeder for seat borrowing feature
 * This script populates the database with sample data for testing
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/License.php';
require_once __DIR__ . '/../models/SeatBorrow.php';
require_once __DIR__ . '/../models/User.php';

try {
    echo "[" . date('Y-m-d H:i:s') . "] Starting test data seeding...\n";
    
    $db = Database::getInstance();
    $departmentModel = new Department();
    $licenseModel = new License();
    $seatBorrowModel = new SeatBorrow();
    $userModel = new User();
    
    // Get all users
    $users = $userModel->findAll();
    if (empty($users)) {
        echo "[" . date('Y-m-d H:i:s') . "] Please create at least one admin user first.\n";
        exit(1);
    }
    
    $adminUser = null;
    foreach ($users as $user) {
        if ($user['role'] === 'admin') {
            $adminUser = $user;
            break;
        }
    }
    if (!$adminUser) {
        $adminUser = $users[0];
    }
    
    echo "[" . date('Y-m-d H:i:s') . "] Using admin user: {$adminUser['username']} (ID: {$adminUser['id']})\n";
    
    // Create sample departments if they don't exist
    $departments = [
        '研发部' => '负责产品研发和技术创新',
        '测试部' => '负责产品质量保证和测试',
        '运维部' => '负责系统运维和技术支持',
        '产品部' => '负责产品规划和设计',
        '市场部' => '负责市场营销和推广',
        '财务部' => '负责财务管理和审计'
    ];
    
    $existingDepts = $departmentModel->findAll();
    $existingDeptNames = array_column($existingDepts, 'name');
    
    $deptIds = [];
    foreach ($departments as $name => $description) {
        if (!in_array($name, $existingDeptNames)) {
            $deptId = $departmentModel->create([
                'name' => $name,
                'description' => $description
            ]);
            echo "[" . date('Y-m-d H:i:s') . "] Created department: {$name} (ID: {$deptId})\n";
            $deptIds[$name] = $deptId;
        } else {
            foreach ($existingDepts as $dept) {
                if ($dept['name'] === $name) {
                    $deptIds[$name] = $dept['id'];
                    break;
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] Department already exists: {$name} (ID: {$deptIds[$name]})\n";
        }
    }
    
    // Create sample floating licenses
    $products = ['IDE Pro', 'Cloud Service', 'Database Enterprise', 'Analytics Suite', 'Design Studio'];
    $existingLicenses = $licenseModel->findAll();
    
    for ($i = 0; $i < 15; $i++) {
        $productIndex = $i % count($products);
        $licenseType = $i < 12 ? 'floating' : 'fixed';
        $seatStatus = 'idle';
        
        if ($licenseType === 'floating') {
            if ($i < 3) {
                $seatStatus = 'borrowed';
            } elseif ($i === 3) {
                $seatStatus = 'abnormal';
            }
        }
        
        $licenseId = $licenseModel->create([
            'user_id' => $adminUser['id'],
            'product_name' => $products[$productIndex] . ' v' . rand(1, 5) . '.' . rand(0, 9),
            'status' => 'active',
            'license_type' => $licenseType,
            'seat_status' => $seatStatus,
            'expires_at' => date('Y-m-d', strtotime('+' . rand(30, 365) . ' days'))
        ]);
        
        echo "[" . date('Y-m-d H:i:s') . "] Created license: {$products[$productIndex]} (ID: {$licenseId}, Type: {$licenseType}, Status: {$seatStatus})\n";
        
        if ($licenseType === 'floating' && $seatStatus === 'borrowed') {
            $deptNames = array_keys($deptIds);
            $randomDept = $deptNames[array_rand($deptNames)];
            
            $startDate = date('Y-m-d H:i:s', strtotime('-' . rand(1, 7) . ' days'));
            $endDate = date('Y-m-d H:i:s', strtotime('+' . rand(7, 30) . ' days'));
            
            $borrowId = $seatBorrowModel->create([
                'license_id' => $licenseId,
                'department_id' => $deptIds[$randomDept],
                'borrower_id' => $adminUser['id'],
                'approver_id' => $adminUser['id'],
                'purpose' => "{$randomDept}项目开发，需要使用{$products[$productIndex]}进行代码开发和测试工作",
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            echo "[" . date('Y-m-d H:i:s') . "]   -> Created borrow record (ID: {$borrowId}, Department: {$randomDept})\n";
            
            $seatBorrowModel->approve($borrowId, $adminUser['id']);
            echo "[" . date('Y-m-d H:i:s') . "]   -> Approved borrow\n";
        }
    }
    
    // Create a pending borrow request for testing
    $floatingLicenses = $licenseModel->findBySeatStatus('idle', 1, 0);
    if (!empty($floatingLicenses)) {
        $license = $floatingLicenses[0];
        $deptNames = array_keys($deptIds);
        $randomDept = $deptNames[array_rand($deptNames)];
        
        $borrowId = $seatBorrowModel->create([
            'license_id' => $license['id'],
            'department_id' => $deptIds[$randomDept],
            'borrower_id' => $adminUser['id'],
            'approver_id' => $adminUser['id'],
            'purpose' => "{$randomDept}新产品预研，需要临时使用浮动授权进行技术验证",
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'status' => 'pending'
        ]);
        
        echo "[" . date('Y-m-d H:i:s') . "] Created pending borrow request (ID: {$borrowId}, License: {$license['product_name']})\n";
    }
    
    echo "\n[" . date('Y-m-d H:i:s') . "] Test data seeding completed!\n";
    
    // Print summary statistics
    $stats = $seatBorrowModel->getSeatStats();
    echo "\n=== Seat Statistics ===\n";
    echo "Total floating licenses: " . ($stats['total_floating'] ?? 0) . "\n";
    echo "Idle: " . ($stats['idle_count'] ?? 0) . "\n";
    echo "Borrowed: " . ($stats['borrowed_count'] ?? 0) . "\n";
    echo "Abnormal: " . ($stats['abnormal_count'] ?? 0) . "\n";
    
    $pending = $db->fetchAll("SELECT COUNT(*) as count FROM seat_borrows WHERE status = 'pending'");
    echo "\nPending approvals: " . ($pending[0]['count'] ?? 0) . "\n";
    
    echo "\nTo run the cron job for seat expiration:\n";
    echo "  php " . __DIR__ . "/cron_seat_check.php\n";
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

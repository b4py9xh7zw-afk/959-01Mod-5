<?php
/**
 * Test script to verify bug fixes
 * 1. Test that departments are available in the create page
 * 2. Test that regular users can submit borrow requests
 * 3. Test that abnormal seats are not in available list
 */

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/models/SeatBorrow.php';
require_once __DIR__ . '/app/models/Department.php';
require_once __DIR__ . '/app/models/License.php';
require_once __DIR__ . '/app/models/User.php';

echo "=== Testing Bug Fixes ===\n\n";

try {
    $db = Database::getInstance();
    $seatBorrowModel = new SeatBorrow();
    $departmentModel = new Department();
    $licenseModel = new License();
    $userModel = new User();

    // Test 1: Check departments exist
    echo "Test 1: 检查部门数据\n";
    $departments = $departmentModel->findAll(100, 0);
    echo "  找到 " . count($departments) . " 个部门:\n";
    foreach ($departments as $dept) {
        echo "    - " . htmlspecialchars($dept['name']) . " (ID: " . $dept['id'] . ")\n";
    }
    echo "  " . (count($departments) > 0 ? "✅ 通过" : "❌ 失败 - 没有部门数据") . "\n\n";

    // Test 2: Check available floating licenses
    echo "Test 2: 检查可用浮动许可证（排除异常占用）\n";
    $startDate = date('Y-m-d H:i:s');
    $endDate = date('Y-m-d H:i:s', strtotime('+7 days'));
    $availableLicenses = $seatBorrowModel->getAvailableFloatingLicenses($startDate, $endDate);
    
    echo "  找到 " . count($availableLicenses) . " 个可用许可证:\n";
    $hasAbnormal = false;
    foreach ($availableLicenses as $license) {
        echo "    - " . htmlspecialchars($license['product_name']) . " (ID: " . $license['id'] . ", 状态: " . $license['seat_status'] . ")\n";
        if ($license['seat_status'] === 'abnormal') {
            $hasAbnormal = true;
        }
    }
    
    // Check if there are abnormal seat in DB
    $abnormalLicenses = $licenseModel->findBySeatStatus('abnormal', 100, 0);
    echo "  数据库中异常占用的许可证: " . count($abnormalLicenses) . " 个\n";
    
    echo "  " . (!$hasAbnormal ? "✅ 通过 - 异常占用席位没有出现在可用列表" : "❌ 失败 - 异常占用席位出现在可用列表") . "\n\n";

    // Test 3: Test regular user borrow submission
    echo "Test 3: 测试普通用户提交借用申请\n";
    
    // Find regular user
    $users = $userModel->findAll();
    $regularUser = null;
    foreach ($users as $user) {
        if ($user['role'] !== 'admin') {
            $regularUser = $user;
            break;
        }
    }
    
    if (!$regularUser) {
        echo "  ❌ 失败 - 没有找到普通用户\n";
    } else {
        echo "  使用普通用户: " . $regularUser['username'] . " (ID: " . $regularUser['id'] . ")\n";
        
        // Get available license
        if (empty($availableLicenses)) {
            echo "  ❌ 失败 - 没有可用许可证\n";
        } else {
            $license = $availableLicenses[0];
            $department = $departments[0];
            
            echo "  借用许可证: " . $license['product_name'] . " (ID: " . $license['id'] . ")\n";
            echo "  借用部门: " . $department['name'] . " (ID: " . $department['id'] . ")\n";
            
            try {
                $borrowId = $seatBorrowModel->create([
                    'license_id' => $license['id'],
                    'department_id' => $department['id'],
                    'borrower_id' => $regularUser['id'],
                    'approver_id' => null,
                    'purpose' => '测试普通用户提交申请',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'pending'
                ]);
                
                echo "  借用申请创建成功，ID: " . $borrowId . "\n";
                
                // Verify the borrow record
                $borrow = $seatBorrowModel->findById($borrowId);
                echo "  申请状态: " . $borrow['status'] . "\n";
                echo "  审批人ID: " . ($borrow['approver_id'] ?? 'NULL') . "\n";
                
                echo "  " . ($borrow['status'] === 'pending' && $borrow['approver_id'] === null ? "✅ 通过 - 普通用户申请成功提交，状态为待审批" : "❌ 失败 - 申请状态不正确") . "\n";
                
                // Verify seat status should still be idle (not borrowed)
                $licenseAfter = $licenseModel->findById($license['id']);
                echo "  许可证状态: " . $licenseAfter['seat_status'] . "\n";
                echo "  " . ($licenseAfter['seat_status'] === 'idle' ? "✅ 通过 - 待审批时席位状态保持空闲" : "❌ 失败 - 席位状态不正确") . "\n";
            } catch (Exception $e) {
                echo "  ❌ 失败 - 错误: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n=== 所有测试完成 ===\n";

} catch (Exception $e) {
    echo "\n❌ 测试异常: " . $e->getMessage() . "\n";
    exit(1);
}

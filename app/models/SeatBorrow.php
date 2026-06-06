<?php
/**
 * SeatBorrow Model - 席位借用管理
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/License.php';

class SeatBorrow {
    private $db;
    private $licenseModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->licenseModel = new License();
    }
    
    public function create($data) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO seat_borrows (
                license_id, department_id, borrower_id, approver_id, purpose, 
                start_date, end_date, status, business_reason
            ) VALUES (
                :license_id, :department_id, :borrower_id, :approver_id, :purpose,
                :start_date, :end_date, :status, :business_reason
            )";
            
            $params = [
                ':license_id' => $data['license_id'],
                ':department_id' => $data['department_id'],
                ':borrower_id' => $data['borrower_id'],
                ':approver_id' => $data['approver_id'],
                ':purpose' => $data['purpose'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':status' => $data['status'] ?? 'pending',
                ':business_reason' => $data['business_reason'] ?? null
            ];
            
            $this->db->execute($sql, $params);
            $borrowId = $this->db->lastInsertId();
            
            $this->addHistory($borrowId, 'created', $data['approver_id'], '创建借用申请');
            
            if (($data['status'] ?? 'pending') === 'approved') {
                $this->licenseModel->update($data['license_id'], ['seat_status' => 'borrowed']);
                $this->addHistory($borrowId, 'approved', $data['approver_id'], '审批通过');
                $this->addHistory($borrowId, 'borrowed', $data['approver_id'], '席位已借出');
                $this->updateStatus($borrowId, 'active');
            }
            
            $this->db->commit();
            return $borrowId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function findById($id) {
        $sql = "SELECT sb.*, 
                       l.license_key, l.product_name, l.seat_status,
                       d.name as department_name,
                       b.username as borrower_name, b.email as borrower_email,
                       a.username as approver_name, a.email as approver_email
                FROM seat_borrows sb
                LEFT JOIN licenses l ON sb.license_id = l.id
                LEFT JOIN departments d ON sb.department_id = d.id
                LEFT JOIN users b ON sb.borrower_id = b.id
                LEFT JOIN users a ON sb.approver_id = a.id
                WHERE sb.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByLicenseId($licenseId) {
        $sql = "SELECT sb.*, 
                       d.name as department_name,
                       b.username as borrower_name,
                       a.username as approver_name
                FROM seat_borrows sb
                LEFT JOIN departments d ON sb.department_id = d.id
                LEFT JOIN users b ON sb.borrower_id = b.id
                LEFT JOIN users a ON sb.approver_id = a.id
                WHERE sb.license_id = :license_id
                ORDER BY sb.created_at DESC";
        return $this->db->fetchAll($sql, [':license_id' => $licenseId]);
    }
    
    public function findByDepartmentId($departmentId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT sb.*, 
                       l.license_key, l.product_name,
                       d.name as department_name,
                       b.username as borrower_name,
                       a.username as approver_name
                FROM seat_borrows sb
                LEFT JOIN licenses l ON sb.license_id = l.id
                LEFT JOIN departments d ON sb.department_id = d.id
                LEFT JOIN users b ON sb.borrower_id = b.id
                LEFT JOIN users a ON sb.approver_id = a.id
                WHERE sb.department_id = :department_id
                ORDER BY sb.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':department_id' => $departmentId]);
    }
    
    public function findByBorrowerId($borrowerId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT sb.*, 
                       l.license_key, l.product_name,
                       d.name as department_name,
                       b.username as borrower_name,
                       a.username as approver_name
                FROM seat_borrows sb
                LEFT JOIN licenses l ON sb.license_id = l.id
                LEFT JOIN departments d ON sb.department_id = d.id
                LEFT JOIN users b ON sb.borrower_id = b.id
                LEFT JOIN users a ON sb.approver_id = a.id
                WHERE sb.borrower_id = :borrower_id
                ORDER BY sb.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':borrower_id' => $borrowerId]);
    }
    
    public function findAll($limit = 100, $offset = 0, $status = null) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        
        $where = '';
        $params = [];
        if ($status) {
            $where = "WHERE sb.status = :status";
            $params[':status'] = $status;
        }
        
        $sql = "SELECT sb.*, 
                       l.license_key, l.product_name, l.seat_status,
                       d.name as department_name,
                       b.username as borrower_name, b.email as borrower_email,
                       a.username as approver_name, a.email as approver_email
                FROM seat_borrows sb
                LEFT JOIN licenses l ON sb.license_id = l.id
                LEFT JOIN departments d ON sb.department_id = d.id
                LEFT JOIN users b ON sb.borrower_id = b.id
                LEFT JOIN users a ON sb.approver_id = a.id
                {$where}
                ORDER BY sb.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function count($status = null) {
        $sql = "SELECT COUNT(*) as count FROM seat_borrows";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function approve($id, $approverId) {
        $this->db->beginTransaction();
        try {
            $borrow = $this->findById($id);
            if (!$borrow || $borrow['status'] !== 'pending') {
                throw new Exception('借用申请不存在或状态不正确');
            }
            
            $this->updateStatus($id, 'approved');
            $this->licenseModel->update($borrow['license_id'], ['seat_status' => 'borrowed']);
            $this->addHistory($id, 'approved', $approverId, '审批通过');
            
            $this->db->execute(
                "UPDATE seat_borrows SET status = 'active' WHERE id = :id",
                [':id' => $id]
            );
            $this->addHistory($id, 'borrowed', $approverId, '席位已借出');
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function reject($id, $approverId, $reason = '') {
        $this->db->beginTransaction();
        try {
            $borrow = $this->findById($id);
            if (!$borrow || $borrow['status'] !== 'pending') {
                throw new Exception('借用申请不存在或状态不正确');
            }
            
            $this->updateStatus($id, 'rejected');
            $this->addHistory($id, 'rejected', $approverId, $reason ?: '申请被拒绝');
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function returnSeat($id, $operatorId) {
        $this->db->beginTransaction();
        try {
            $borrow = $this->findById($id);
            if (!$borrow || !in_array($borrow['status'], ['active', 'expired'])) {
                throw new Exception('借用记录不存在或状态不正确');
            }
            
            $this->db->execute(
                "UPDATE seat_borrows SET status = 'returned', actual_return_date = CURRENT_TIMESTAMP WHERE id = :id",
                [':id' => $id]
            );
            $this->licenseModel->update($borrow['license_id'], ['seat_status' => 'idle']);
            $this->addHistory($id, 'returned', $operatorId, '席位已归还');
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function requestRenew($id, $newEndDate, $businessReason, $operatorId) {
        $this->db->beginTransaction();
        try {
            $borrow = $this->findById($id);
            if (!$borrow || !in_array($borrow['status'], ['active', 'renew_pending'])) {
                throw new Exception('借用记录不存在或状态不正确');
            }
            
            $this->db->execute(
                "UPDATE seat_borrows SET status = 'renew_pending', end_date = :end_date, business_reason = :business_reason WHERE id = :id",
                [
                    ':id' => $id,
                    ':end_date' => $newEndDate,
                    ':business_reason' => $businessReason
                ]
            );
            $this->addHistory($id, 'renewed', $operatorId, '申请续借：' . $businessReason);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function approveRenew($id, $approverId) {
        $this->db->beginTransaction();
        try {
            $borrow = $this->findById($id);
            if (!$borrow || $borrow['status'] !== 'renew_pending') {
                throw new Exception('续借申请不存在或状态不正确');
            }
            
            $this->db->execute(
                "UPDATE seat_borrows SET status = 'active', renew_count = renew_count + 1 WHERE id = :id",
                [':id' => $id]
            );
            $this->addHistory($id, 'approved', $approverId, '续借审批通过');
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function rejectRenew($id, $approverId, $reason = '') {
        $this->db->beginTransaction();
        try {
            $borrow = $this->findById($id);
            if (!$borrow || $borrow['status'] !== 'renew_pending') {
                throw new Exception('续借申请不存在或状态不正确');
            }
            
            $this->db->execute(
                "UPDATE seat_borrows SET status = 'active' WHERE id = :id",
                [':id' => $id]
            );
            $this->addHistory($id, 'rejected', $approverId, '续借申请被拒绝：' . $reason);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function expireOverdue() {
        $this->db->beginTransaction();
        try {
            $sql = "SELECT id, license_id FROM seat_borrows 
                    WHERE status = 'active' AND end_date < CURRENT_TIMESTAMP";
            $overdue = $this->db->fetchAll($sql);
            
            foreach ($overdue as $borrow) {
                $this->db->execute(
                    "UPDATE seat_borrows SET status = 'expired' WHERE id = :id",
                    [':id' => $borrow['id']]
                );
                $this->licenseModel->update($borrow['license_id'], ['seat_status' => 'abnormal']);
                $this->addHistory($borrow['id'], 'expired', 0, '借用已到期，系统自动标记为异常占用');
            }
            
            $count = count($overdue);
            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function autoRecoverExpired() {
        $this->db->beginTransaction();
        try {
            $gracePeriod = 24 * 3600;
            $thresholdTime = date('Y-m-d H:i:s', time() - $gracePeriod);
            $sql = "SELECT id, license_id FROM seat_borrows 
                    WHERE status = 'expired' 
                    AND end_date < :threshold_time";
            $toRecover = $this->db->fetchAll($sql, [':threshold_time' => $thresholdTime]);
            
            foreach ($toRecover as $borrow) {
                $this->db->execute(
                    "UPDATE seat_borrows SET status = 'returned', actual_return_date = CURRENT_TIMESTAMP WHERE id = :id",
                    [':id' => $borrow['id']]
                );
                $this->licenseModel->update($borrow['license_id'], ['seat_status' => 'idle']);
                $this->addHistory($borrow['id'], 'returned', 0, '超过宽限期，系统自动收回席位');
            }
            
            $count = count($toRecover);
            $this->db->commit();
            return $count;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getSeatStats() {
        $sql = "SELECT 
                    SUM(CASE WHEN seat_status = 'idle' AND license_type = 'floating' THEN 1 ELSE 0 END) as idle_count,
                    SUM(CASE WHEN seat_status = 'borrowed' AND license_type = 'floating' THEN 1 ELSE 0 END) as borrowed_count,
                    SUM(CASE WHEN seat_status = 'abnormal' AND license_type = 'floating' THEN 1 ELSE 0 END) as abnormal_count,
                    SUM(CASE WHEN license_type = 'floating' THEN 1 ELSE 0 END) as total_floating
                FROM licenses";
        return $this->db->fetchOne($sql);
    }
    
    public function getHistory($borrowId) {
        $sql = "SELECT h.*, u.username as operator_name
                FROM seat_borrow_history h
                LEFT JOIN users u ON h.operator_id = u.id
                WHERE h.borrow_id = :borrow_id
                ORDER BY h.created_at DESC";
        return $this->db->fetchAll($sql, [':borrow_id' => $borrowId]);
    }
    
    public function isLicenseAvailable($licenseId, $startDate, $endDate, $excludeBorrowId = null) {
        $sql = "SELECT COUNT(*) as count FROM seat_borrows 
                WHERE license_id = :license_id 
                AND status IN ('pending', 'approved', 'active', 'renew_pending')
                AND start_date <= :end_date 
                AND end_date >= :start_date";
        
        $params = [
            ':license_id' => $licenseId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];
        
        if ($excludeBorrowId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeBorrowId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return ($result['count'] ?? 0) == 0;
    }
    
    public function getAvailableFloatingLicenses($startDate, $endDate) {
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.license_type = 'floating' 
                AND l.status = 'active'
                AND l.id NOT IN (
                    SELECT license_id FROM seat_borrows 
                    WHERE status IN ('pending', 'approved', 'active', 'renew_pending')
                    AND start_date <= :end_date 
                    AND end_date >= :start_date
                )
                ORDER BY l.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
    }
    
    public function getCurrentActiveBorrow($licenseId) {
        $sql = "SELECT sb.*, 
                       d.name as department_name,
                       b.username as borrower_name,
                       b.email as borrower_email
                FROM seat_borrows sb
                LEFT JOIN departments d ON sb.department_id = d.id
                LEFT JOIN users b ON sb.borrower_id = b.id
                WHERE sb.license_id = :license_id 
                AND sb.status IN ('active', 'renew_pending')
                ORDER BY sb.created_at DESC
                LIMIT 1";
        return $this->db->fetchOne($sql, [':license_id' => $licenseId]);
    }
    
    private function updateStatus($id, $status) {
        $this->db->execute(
            "UPDATE seat_borrows SET status = :status WHERE id = :id",
            [':id' => $id, ':status' => $status]
        );
    }
    
    private function addHistory($borrowId, $action, $operatorId, $remark = '') {
        $sql = "INSERT INTO seat_borrow_history (borrow_id, action, operator_id, remark, created_at)
                VALUES (:borrow_id, :action, :operator_id, :remark, CURRENT_TIMESTAMP)";
        $this->db->execute($sql, [
            ':borrow_id' => $borrowId,
            ':action' => $action,
            ':operator_id' => $operatorId,
            ':remark' => $remark
        ]);
    }
}

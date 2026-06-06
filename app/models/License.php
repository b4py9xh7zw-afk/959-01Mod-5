<?php
/**
 * License Model
 */

require_once __DIR__ . '/../config/database.php';

class License {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO licenses (license_key, user_id, product_name, status, license_type, seat_status, expires_at, created_at) 
                VALUES (:license_key, :user_id, :product_name, :status, :license_type, :seat_status, :expires_at, CURRENT_TIMESTAMP)";
        
        $params = [
            ':license_key' => $this->generateLicenseKey(),
            ':user_id' => $data['user_id'],
            ':product_name' => $data['product_name'],
            ':status' => $data['status'] ?? 'active',
            ':license_type' => $data['license_type'] ?? 'floating',
            ':seat_status' => $data['seat_status'] ?? 'idle',
            ':expires_at' => $data['expires_at'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByKey($key) {
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.license_key = :key";
        return $this->db->fetchOne($sql, [':key' => $key]);
    }
    
    public function findByUserId($userId, $limit = 100, $offset = 0) {
        // Ensure limit and offset are integers to prevent SQL injection
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.user_id = :user_id 
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function findAll($limit = 100, $offset = 0) {
        // Ensure limit and offset are integers to prevent SQL injection
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM licenses";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }
    
    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE status = :status";
        $result = $this->db->fetchOne($sql, [':status' => $status]);
        return $result['count'] ?? 0;
    }
    
    public function countByUserId($userId) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE user_id = :user_id";
        $result = $this->db->fetchOne($sql, [':user_id' => $userId]);
        return $result['count'] ?? 0;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['product_name'])) {
            $fields[] = "product_name = :product_name";
            $params[':product_name'] = $data['product_name'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        if (isset($data['expires_at'])) {
            $fields[] = "expires_at = :expires_at";
            $params[':expires_at'] = $data['expires_at'];
        }
        if (isset($data['user_id'])) {
            $fields[] = "user_id = :user_id";
            $params[':user_id'] = $data['user_id'];
        }
        if (isset($data['license_type'])) {
            $fields[] = "license_type = :license_type";
            $params[':license_type'] = $data['license_type'];
        }
        if (isset($data['seat_status'])) {
            $fields[] = "seat_status = :seat_status";
            $params[':seat_status'] = $data['seat_status'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE licenses SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function countBySeatStatus($seatStatus) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE seat_status = :seat_status AND license_type = 'floating'";
        $result = $this->db->fetchOne($sql, [':seat_status' => $seatStatus]);
        return $result['count'] ?? 0;
    }
    
    public function countByLicenseType($licenseType) {
        $sql = "SELECT COUNT(*) as count FROM licenses WHERE license_type = :license_type";
        $result = $this->db->fetchOne($sql, [':license_type' => $licenseType]);
        return $result['count'] ?? 0;
    }
    
    public function findBySeatStatus($seatStatus, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.seat_status = :seat_status 
                AND l.license_type = 'floating'
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':seat_status' => $seatStatus]);
    }
    
    public function findFloatingLicenses($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email,
                       sb.department_name, sb.borrower_name, sb.end_date as borrow_end_date, sb.status as borrow_status
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN (
                    SELECT sb1.license_id, d.name as department_name, 
                           ub.username as borrower_name, sb1.end_date, sb1.status
                    FROM seat_borrows sb1
                    LEFT JOIN departments d ON sb1.department_id = d.id
                    LEFT JOIN users ub ON sb1.borrower_id = ub.id
                    WHERE sb1.status IN ('active', 'renew_pending')
                ) sb ON l.id = sb.license_id
                WHERE l.license_type = 'floating' 
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function findIdleFloatingLicenses($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT l.*, u.username, u.email 
                FROM licenses l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE l.license_type = 'floating' 
                AND l.seat_status = 'idle'
                AND l.status = 'active'
                ORDER BY l.created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function getSeatStatistics() {
        $sql = "SELECT 
                    license_type,
                    seat_status,
                    COUNT(*) as count
                FROM licenses 
                WHERE license_type = 'floating'
                GROUP BY license_type, seat_status
                WITH ROLLUP";
        return $this->db->fetchAll($sql);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM licenses WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    private function generateLicenseKey() {
        return strtoupper(
            substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid(rand(), true)), 0, 8)
        );
    }
    
    public function validate($licenseKey) {
        $license = $this->findByKey($licenseKey);
        if (!$license) {
            return ['valid' => false, 'message' => 'License key not found'];
        }
        
        if ($license['status'] !== 'active') {
            return ['valid' => false, 'message' => 'License is not active'];
        }
        
        if ($license['expires_at'] && strtotime($license['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'License has expired'];
        }
        
        return ['valid' => true, 'license' => $license];
    }
}

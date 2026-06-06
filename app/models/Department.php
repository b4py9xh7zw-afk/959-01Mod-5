<?php
/**
 * Department Model
 */

require_once __DIR__ . '/../config/database.php';

class Department {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO departments (name, description, created_at) 
                VALUES (:name, :description, CURRENT_TIMESTAMP)";
        
        $params = [
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM departments WHERE id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByName($name) {
        $sql = "SELECT * FROM departments WHERE name = :name";
        return $this->db->fetchOne($sql, [':name' => $name]);
    }
    
    public function findAll($limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT * FROM departments ORDER BY name ASC LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }
    
    public function count() {
        $sql = "SELECT COUNT(*) as count FROM departments";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params[':name'] = $data['name'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = :description";
            $params[':description'] = $data['description'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE departments SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->execute($sql, $params);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM departments WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
}

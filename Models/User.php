<?php
use Core\Database;

class User {
    protected $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllUsers() {
        return $this->db->query('SELECT * FROM users ORDER BY created_at DESC')->findAll();
    }

    public function getUserById($id) {
        return $this->db->query('SELECT * FROM users WHERE id = ?', [$id])->find();
    }

    public function updateUserStatus($id, $status) {
        $validStatuses = ['pending', 'active', 'suspended'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid user status");
        }
        return $this->db->query(
            'UPDATE users SET status = ? WHERE id = ?',
            [$status, $id]
        );
    }

    public function updateUserRole($id, $role) {
        $validRoles = ['homeowner', 'admin', 'user'];
        if (!in_array($role, $validRoles)) {
            throw new InvalidArgumentException("Invalid user role");
        }
        return $this->db->query(
            'UPDATE users SET role = ? WHERE id = ?',
            [$role, $id]
        );
    }

    public function deleteUser($id) {
        return $this->db->query('DELETE FROM users WHERE id = ?', [$id]);
    }

    public function searchUsers($query) {
        return $this->db->query(
            'SELECT * FROM users 
             WHERE username LIKE ? OR name LIKE ? OR email LIKE ?
             ORDER BY created_at DESC',
            ["%$query%", "%$query%", "%$query%"]
        )->findAll();
    }
}
<?php
// UserDataset.php
require_once(__DIR__ . '/UserData.php');

use Core\Database;

class UserDataset
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllUsers()
    {
        $rows = $this->db->query('SELECT * FROM users ORDER BY created_at DESC')->findAll();
        return $rows;
    }
    
    public function getAllHomeowners()
    {
        $rows = $this->db->query('SELECT * FROM users where role = ?', [
            'homeowner'
        ])->findAll();
        return $rows;
    }

    public function getUserById($id)
    {
        $row = $this->db->query('SELECT * FROM users WHERE id = ?', [$id])->find();
        if (!$row) {
            return null;
        }
        return new UserData($row);
    }

    public function updateUserStatus($id, $status)
    {
        $validStatuses = ['pending', 'active', 'suspended'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid user status");
        }
        return $this->db->query(
            'UPDATE users SET status = ? WHERE id = ?',
            [$status, $id]
        );
    }

    public function updateUserRole($id, $role)
    {
        $validRoles = ['homeowner', 'admin', 'user'];
        if (!in_array($role, $validRoles)) {
            throw new InvalidArgumentException("Invalid user role");
        }
        return $this->db->query(
            'UPDATE users SET role = ? WHERE id = ?',
            [$role, $id]
        );
    }

    public function deleteUser($id)
    {
        return $this->db->query('DELETE FROM users WHERE id = ?', [$id]);
    }

    public function searchUsers($query)
    {
        $rows = $this->db->query(
            'SELECT * FROM users 
             WHERE username LIKE ? OR name LIKE ? OR email LIKE ?
             ORDER BY created_at DESC',
            ["%$query%", "%$query%", "%$query%"]
        )->findAll();
        return $rows;
    }
    
    public function approveHomeowner($id)
    {
        return $this->db->query(
            'UPDATE users SET is_approved = ? WHERE id = ?',
            [1, $id]
        );
    }
    
    public function getAllPendingHomeowners()
    {
        $rows = $this->db->query(
            'SELECT * FROM users WHERE is_approved = ? and role = ?',
            [0, 'homeowner']
        )->findAll();
        return $rows;
    }

    public function update($id, $data)
    {
        $fields = [];
        $values = [];

        // Dynamically build the query based on provided data
        if (isset($data['username'])) {
            $fields[] = 'username = ?';
            $values[] = $data['username'];
        }
        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $values[] = $data['name'];
        }
        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $values[] = $data['email'];
        }
        if (isset($data['phone'])) {
            $fields[] = 'phone = ?';
            $values[] = $data['phone'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $values[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $values[] = $data['role'];
        }
        if (isset($data['status'])) {
            $fields[] = 'status = ?';
            $values[] = $data['status'];
        }
        if (isset($data['is_approved'])) {
            $fields[] = 'is_approved = ?';
            $values[] = $data['is_approved'];
        }

        // Add the ID to the values array
        $values[] = $id;

        // Build and execute the query
        $query = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        return $this->db->query($query, $values);
    }
    
    public function isHomeownerApproved($id)
    {
        $user = $this->getUserById($id);
        return $user && $user->getRole() === 'homeowner' && $user->getIsApproved() == 1;
    }
    
    public function create($data)
    {
        // Check for required fields
        $requiredFields = ['username', 'name', 'password'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        // Build query based on what fields are present
        $fields = ['username', 'name', 'password'];
        $placeholders = ['?', '?', '?'];
        $values = [$data['username'], $data['name'], $hashedPassword];
        
        // Optional fields
        if (isset($data['email'])) {
            $fields[] = 'email';
            $placeholders[] = '?';
            $values[] = $data['email'];
        }
        
        if (isset($data['phone'])) {
            $fields[] = 'phone';
            $placeholders[] = '?';
            $values[] = $data['phone'];
        }
        
        // Always include role, status, is_approved with defaults if not provided
        $fields[] = 'role';
        $placeholders[] = '?';
        $values[] = $data['role'] ?? 'user';
        
        $fields[] = 'status';
        $placeholders[] = '?';
        $values[] = $data['status'] ?? 'active';
        
        $fields[] = 'is_approved';
        $placeholders[] = '?';
        $values[] = $data['is_approved'] ?? 0;
        
        // Build the SQL
        $sql = 'INSERT INTO users (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $placeholders) . ')';
        
        return $this->db->query($sql, $values);
    }
}
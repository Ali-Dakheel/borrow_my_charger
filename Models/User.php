<?php

use Core\Database;

class User
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllUsers()
    {
        return $this->db->query('SELECT * FROM users ORDER BY created_at DESC')->findAll();
    }
    public function getAllHomeowners()
    {
        return $this->db->query('SELECT * FROM users where role = ?', [
            'homeowner'
        ])->findAll();
    }

    public function getUserById($id)
    {
        return $this->db->query('SELECT * FROM users WHERE id = ?', [$id])->find();
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
        return $this->db->query(
            'SELECT * FROM users 
             WHERE username LIKE ? OR name LIKE ? OR email LIKE ?
             ORDER BY created_at DESC',
            ["%$query%", "%$query%", "%$query%"]
        )->findAll();
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
        return $this->db->query(
            'SELECT * FROM users WHERE is_approved = ? and role = ?',
            [0, 'homeowner']
        )->findAll();
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
}

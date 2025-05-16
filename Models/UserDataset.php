<?php
// UserDataset.php
require_once(__DIR__ . '/UserData.php');

use Core\Database;

/**
 * UserDataset Class
 * 
 * Handles all database operations related to user management including
 * creating, retrieving, updating, and deleting user records.
 */
class UserDataset
{
    /**
     * @var Database Database connection instance
     */
    protected $db;

    /**
     * Constructor
     * 
     * @param Database $db Database connection instance
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Check if a user with the given username already exists
     * 
     * @param string $username The username to check
     * @return array|null Row data if user exists, null otherwise
     */
    public function checkExistingUser($username){
        $row = $this->db->query("SELECT id FROM users WHERE username = ?", [$username])->find();
        return $row;
    }

    /**
     * Get all users from the database
     * 
     * @return array All user records ordered by creation date (newest first)
     */
    public function getAllUsers()
    {
        $rows = $this->db->query('SELECT * FROM users ORDER BY created_at DESC')->findAll();
        return $rows;
    }
    
    /**
     * Get all users with homeowner role
     * 
     * @return array All homeowner user records
     */
    public function getAllHomeowners()
    {
        $rows = $this->db->query('SELECT * FROM users where role = ?', [
            'homeowner'
        ])->findAll();
        return $rows;
    }

    /**
     * Get a specific user by their ID
     * 
     * @param int $id User ID
     * @return UserData|null User data object if found, null otherwise
     */
    public function getUserById($id)
    {
        $row = $this->db->query('SELECT * FROM users WHERE id = ?', [$id])->find();
        if (!$row) {
            return null;
        }
        return new UserData($row);
    }

    /**
     * Update a user's status
     * 
     * @param int $id User ID
     * @param string $status New status ('pending', 'active', or 'suspended')
     * @return mixed Query result
     * @throws InvalidArgumentException If status is invalid
     */
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

    /**
     * Update a user's role
     * 
     * @param int $id User ID
     * @param string $role New role ('homeowner', 'admin', or 'user')
     * @return mixed Query result
     * @throws InvalidArgumentException If role is invalid
     */
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

    /**
     * Delete a user from the database
     * 
     * @param int $id User ID to delete
     * @return mixed Query result
     */
    public function deleteUser($id)
    {
        return $this->db->query('DELETE FROM users WHERE id = ?', [$id]);
    }

    /**
     * Search for users by username, name or email
     * 
     * @param string $query Search term
     * @return array Matching user records
     */
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
    
    /**
     * Approve a homeowner by setting is_approved flag
     * 
     * @param int $id User ID to approve
     * @return mixed Query result
     */
    public function approveHomeowner($id)
    {
        return $this->db->query(
            'UPDATE users SET is_approved = ? WHERE id = ?',
            [1, $id]
        );
    }
    
    /**
     * Get all homeowners pending approval
     * 
     * @return array Pending homeowner records
     */
    public function getAllPendingHomeowners()
    {
        $rows = $this->db->query(
            'SELECT * FROM users WHERE is_approved = ? and role = ?',
            [0, 'homeowner']
        )->findAll();
        return $rows;
    }

    /**
     * Update user data with specified fields
     * 
     * @param int $id User ID to update
     * @param array $data Associative array of fields to update
     * @return mixed Query result
     */
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
    
    /**
     * Check if a homeowner is approved
     * 
     * @param int $id User ID to check
     * @return bool True if the user is an approved homeowner, false otherwise
     */
    public function isHomeownerApproved($id)
    {
        $user = $this->getUserById($id);
        return $user && $user->getRole() === 'homeowner' && $user->getIsApproved() == 1;
    }
    
    /**
     * Create a new user in the database
     * 
     * @param array $data User data including required fields (username, name, password)
     * @return mixed Query result
     * @throws InvalidArgumentException If required fields are missing
     */
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
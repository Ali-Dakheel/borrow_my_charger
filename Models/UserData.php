<?php
// UserData.php
class UserData
{
    protected $id, $username, $name, $password, $role, $status, $isApproved, $createdAt;

    public function __construct($row)
    {
        $this->id = $row['id'] ?? null;
        $this->username = $row['username'] ?? null;
        $this->name = $row['name'] ?? null;
        $this->password = $row['password'] ?? null;
        $this->role = $row['role'] ?? null;
        $this->status = $row['status'] ?? null;
        $this->isApproved = $row['is_approved'] ?? null;
        $this->createdAt = $row['created_at'] ?? null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getPassword()
    {
        return $this->password;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getStatus()
    {
        return $this->status;
    }
    
    public function isApproved()
    {
        return $this->isApproved == 1;
    }
    
    public function getIsApproved()
    {
        return $this->isApproved;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    public function isHomeowner()
    {
        return $this->role === 'homeowner';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function isActive()
    {
        return $this->status === 'active';
    }
    
    // Helper method to convert to array for compatibility with templates
    public function toArray()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'password' => $this->password,
            'role' => $this->role,
            'status' => $this->status,
            'is_approved' => $this->isApproved,
            'created_at' => $this->createdAt
        ];
    }
}
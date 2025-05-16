<?php

/**
 * Class UserData
 *
 * Represents a user entity with authentication, authorization, and status information.
 */
class UserData
{
    /** @var int|null Unique identifier for the user */
    protected $id;

    /** @var string|null Username used for login */
    protected $username;

    /** @var string|null Full name of the user */
    protected $name;

    /** @var string|null Hashed password for authentication */
    protected $password;

    /** @var string|null Role assigned to the user (e.g., admin, homeowner, user) */
    protected $role;

    /** @var string|null Current account status (e.g., active, pending, suspended) */
    protected $status;

    /** @var int|null Approval flag (1 = approved, 0 = not approved) */
    protected $isApproved;

    /** @var string|null Timestamp when the user account was created (Y-m-d H:i:s) */
    protected $createdAt;

    /**
     * UserData constructor.
     *
     * @param array $row Associative array representing a database row for a user.
     */
    public function __construct(array $row)
    {
        $this->id         = $row['id'] ?? null;
        $this->username   = $row['username'] ?? null;
        $this->name       = $row['name'] ?? null;
        $this->password   = $row['password'] ?? null;
        $this->role       = $row['role'] ?? null;
        $this->status     = $row['status'] ?? null;
        $this->isApproved = $row['is_approved'] ?? null;
        $this->createdAt  = $row['created_at'] ?? null;
    }

    /**
     * Get the user ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the username.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Get the full name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    
    /**
     * Get the hashed password.
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Get the user role.
     *
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * Get the account status.
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
    
    /**
     * Check if the user account is approved.
     *
     * @return bool True if approved, false otherwise
     */
    public function isApproved(): bool
    {
        return (int) $this->isApproved === 1;
    }
    
    /**
     * Get the raw approval flag.
     *
     * @return int|null
     */
    public function getIsApproved(): ?int
    {
        return $this->isApproved;
    }

    /**
     * Get the creation timestamp.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
    
    /**
     * Check if the user is a homeowner.
     *
     * @return bool
     */
    public function isHomeowner(): bool
    {
        return $this->role === 'homeowner';
    }

    /**
     * Check if the user is an administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if the user account is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    /**
     * Convert this object to an associative array for template compatibility.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'username'     => $this->username,
            'name'         => $this->name,
            'password'     => $this->password,
            'role'         => $this->role,
            'status'       => $this->status,
            'is_approved'  => $this->isApproved,
            'created_at'   => $this->createdAt,
        ];
    }
}
<?php
require_once(__DIR__ . '/ChargePointData.php');

use Core\Database;

class ChargePointDataset
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Get a charge point by homeowner ID
    public function getByHomeOwnerById($homeownerId)
    {
        $row = $this->db->query(
            'SELECT * FROM charge_points WHERE homeowner_id = ?',
            [$homeownerId]
        )->find();
        
        if (!$row) {
            return null;
        }
        
        return new ChargePointData($row);
    }

    // Create a new charge point
    public function create($data)
    {
        // Check if homeowner already has a charge point
        if ($this->getByHomeOwnerById($data['homeowner_id'])) {
            throw new \Exception("Homeowner can only have one charge point");
        }

        return $this->db->query(
            'INSERT INTO charge_points (homeowner_id, address, postcode, latitude, longitude, price_per_kwh, is_available, image_path) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['homeowner_id'],
                $data['address'],
                $data['postcode'],
                $data['latitude'],
                $data['longitude'],
                $data['price_per_kwh'],
                $data['is_available'],
                $data['image_path']
            ]
        );
    }

    // Update an existing charge point
    public function update($data)
    {
        return $this->db->query(
            "UPDATE charge_points SET 
                homeowner_id = ?,
                address = ?, 
                postcode = ?, 
                latitude = ?, 
                longitude = ?, 
                price_per_kwh = ?, 
                is_available = ?, 
                image_path = ? 
             WHERE id = ?",
            [
                $data['homeowner_id'],
                $data['address'],
                $data['postcode'],
                $data['latitude'],
                $data['longitude'],
                $data['price_per_kwh'],
                $data['is_available'],
                $data['image_path'] ?? null,
                $data['id']
            ]
        );
    }
    
    // Mark a charge point as unavailable
    public function markUnavailable($id)
    {
        return $this->db->query(
            "UPDATE charge_points SET is_available = 0 WHERE id = ?",
            [$id]
        );
    }

    // Get all charge points
    public function getAll()
    {
        // Return array directly for view compatibility
        return $this->db->query(
            "SELECT cp.*, u.username as homeowner_username
             FROM charge_points cp
             JOIN users u ON cp.homeowner_id = u.id"
        )->findAll();
    }

    // Delete a charge point
    public function delete($id)
    {
        return $this->db->query('DELETE FROM charge_points WHERE id = ?', [$id]);
    }
    
    // Get paginated charge points
    public function getPaginated(int $limit, int $offset): array
    {
        // force integers
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

        // Return array directly for view compatibility
        $sql = "
          SELECT cp.*, u.username AS homeowner_username
          FROM charge_points cp
          JOIN users u ON cp.homeowner_id = u.id
          ORDER BY cp.id DESC
          LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->db
            ->query($sql)
            ->findAll();
    }

    // Get the total count of charge points
    public function getTotalCount()
    {
        return $this->db->query(
            "SELECT COUNT(*) as total FROM charge_points"
        )->find()['total'];
    }

    // Get all homeowners with charge points
    public function getAllHomeownersWithChargePoints()
    {
        // Return array directly for view compatibility
        return $this->db->query(
            "SELECT u.id as homeowner_id, u.name as homeowner_name, u.username as homeowner_username, 
                    cp.address as charge_point_address, cp.postcode as charge_point_postcode
             FROM users u
             JOIN charge_points cp ON u.id = cp.homeowner_id
             WHERE u.role = ?",
            ['homeowner']
        )->findAll();
    }

    // Get a charge point by ID
    public function getById($id)
    {
        $row = $this->db->query(
            'SELECT * FROM charge_points WHERE id = ?',
            [$id]
        )->find();
        
        if (!$row) {
            return null;
        }
        
        return new ChargePointData($row);
    }

    // Get charge point with homeowner details by ID
    public function getWithHomeownerById($id)
    {
        $row = $this->db->query(
            "SELECT cp.*, u.username as homeowner_username, u.name as homeowner_name
             FROM charge_points cp
             JOIN users u ON cp.homeowner_id = u.id
             WHERE cp.id = ?",
            [$id]
        )->find();
        
        if (!$row) {
            return null;
        }

        // For methods that need both the core data and joined data, return both
        return [
            'chargePoint' => new ChargePointData($row),
            'details' => $row
        ];
    }
}
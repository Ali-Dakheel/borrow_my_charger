<?php
require_once(__DIR__ . '/ChargePointData.php');

use Core\Database;

/**
 * Class ChargePointDataset
 *
 * Data access layer for charge point records. Provides methods to create, update,
 * delete, and retrieve charge point entities with various scopes and details.
 */
class ChargePointDataset
{
    /**
     * @var Database Database connection instance
     */
    protected $db;

    /**
     * ChargePointDataset constructor.
     *
     * @param Database $db The database connection to use for queries
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieve a charge point for a given homeowner.
     *
     * @param int $homeownerId User ID of the homeowner
     * @return ChargePointData|null Returns a ChargePointData object or null if none exists
     */
    public function getByHomeOwnerById(int $homeownerId)
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

    /**
     * Create a new charge point record.
     *
     * @param array $data Associative array with keys homeowner_id, address, postcode, latitude,
     *                    longitude, price_per_kwh, is_available, image_path
     * @return mixed The result of the insert query
     * @throws Exception If the homeowner already has a charge point
     */
    public function create(array $data)
    {
        // Ensure one charge point per homeowner
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

    /**
     * Update an existing charge point record.
     *
     * @param array $data Associative array with keys id, homeowner_id, address, postcode,
     *                    latitude, longitude, price_per_kwh, is_available, image_path (optional)
     * @return mixed The result of the update query
     */
    public function update(array $data)
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
    
    /**
     * Mark a charge point as unavailable.
     *
     * @param int $id Charge point ID
     * @return mixed The result of the update query
     */
    public function markUnavailable(int $id)
    {
        return $this->db->query(
            "UPDATE charge_points SET is_available = 0 WHERE id = ?",
            [$id]
        );
    }

    /**
     * Retrieve all charge points with homeowner usernames.
     *
     * @return array List of charge point records with homeowner usernames
     */
    public function getAll(): array
    {
        return $this->db->query(
            "SELECT cp.*, u.username as homeowner_username
             FROM charge_points cp
             JOIN users u ON cp.homeowner_id = u.id"
        )->findAll();
    }

    /**
     * Delete a charge point record.
     *
     * @param int $id Charge point ID to delete
     * @return mixed The result of the delete query
     */
    public function delete(int $id)
    {
        return $this->db->query('DELETE FROM charge_points WHERE id = ?', [$id]);
    }
    
    /**
     * Retrieve a paginated list of charge points.
     *
     * @param int $limit  Number of records to retrieve per page
     * @param int $offset Offset index for pagination
     * @return array List of paginated charge point records
     */
    public function getPaginated(int $limit, int $offset): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

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

    /**
     * Get the total count of charge points in the system.
     *
     * @return int Total number of charge points
     */
    public function getTotalCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) as total FROM charge_points"
        )->find()['total'];
    }

    /**
     * Retrieve all homeowners who have at least one charge point.
     *
     * @return array List of homeowners with their charge point addresses and postcodes
     */
    public function getAllHomeownersWithChargePoints(): array
    {
        return $this->db->query(
            "SELECT u.id as homeowner_id, u.name as homeowner_name, u.username as homeowner_username, 
                    cp.address as charge_point_address, cp.postcode as charge_point_postcode
             FROM users u
             JOIN charge_points cp ON u.id = cp.homeowner_id
             WHERE u.role = ?",
            ['homeowner']
        )->findAll();
    }

    /**
     * Retrieve a charge point by its ID.
     *
     * @param int $id Charge point ID
     * @return ChargePointData|null Returns a ChargePointData object or null if none exists
     */
    public function getById(int $id): ?ChargePointData
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

    /**
     * Retrieve charge point and homeowner details by charge point ID.
     *
     * @param int $id Charge point ID
     * @return array|null ['chargePoint' => ChargePointData, 'details' => array] or null if not found
     */
    public function getWithHomeownerById(int $id): ?array
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

        return [
            'chargePoint' => new ChargePointData($row),
            'details'     => $row
        ];
    }
}

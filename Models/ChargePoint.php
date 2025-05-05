<?php

use Core\Database;

class ChargePoint
{
    protected $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    // Fixed function name and key
    public function getByHomeOwnerById($homeownerId)
    {
        return $this->db->query(
            'SELECT * FROM charge_points WHERE homeowner_id = ?',
            [$homeownerId]
        )->find();
    }

    public function create($data)
    {
        // Use the same key: 'homeowner_id'
        if ($this->getByHomeOwnerById($data['homeowner_id'])) {
            throw new \Exception("Homeowner can only have one charge point");
        }

        // Correct number of placeholders: 8, matching the values
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
    public function markUnavailable($id)
    {
        return $this->db->query(
            "UPDATE charge_points SET is_available = 0 WHERE id = ?",
            [$id]
        );
    }

    public function getAll()
    {
        return $this->db->query(
            "SELECT cp.*, u.username as homeowner_username
             FROM charge_points cp
             JOIN users u ON cp.homeowner_id = u.id"
        )->findAll();
    }

    public function delete($id)
    {
        return $this->db->query('DELETE FROM charge_points WHERE id = ?', [$id]);
    }
    public function getPaginated(int $limit, int $offset): array
    {
        // force integers
        $limit  = max(1, $limit);
        $offset = max(0, $offset);
    
        // inline them—no placeholders—so MySQL sees: LIMIT 10 OFFSET 0
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
    
    public function getTotalCount()
    {
        return $this->db->query(
            "SELECT COUNT(*) as total FROM charge_points"
        )->find()['total'];
    }
}

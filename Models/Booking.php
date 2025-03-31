<?php

use Core\Database;

class Booking{
    protected $db;
    public function __construct(Database $db){
        $this->db = $db;
    }

    public function getByRentalUserIdWithDetails($userId) {
        return $this->db->query(
            'SELECT b.*, 
                    cp.address AS charge_point_address,
                    cp.postcode AS charge_point_postcode,
                    cp.price_per_kwh,
                    u.name AS homeowner_name
             FROM bookings b
             JOIN charge_points cp ON b.charge_point_id = cp.id
             JOIN users u ON cp.homeowner_id = u.id
             WHERE b.user_id = ?
             ORDER BY b.start_time DESC',
            [$userId]
        )->findAll();
    }
    public function cancelBooking($id){
        return $this->db->query('UPDATE BOOKINGS SET status=cancelled where id = ?',[
            $id
        ]);
    }
    public function create($data) {
        return $this->db->query(
            'INSERT INTO `bookings`(user_id, charge_point_id, start_time, end_time, total_cost, status)
             VALUES (?,?,?,?,?,?)',
            [
                $data['user_id'],
                $data['charge_point_id'],
                $data['start_time'],
                $data['end_time'],
                $data['total_cost'],
                $data['status'],
            ]
        );
    }
    }
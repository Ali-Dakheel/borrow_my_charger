<?php

use Core\Database;

class Booking{
    protected $db;
    public function __construct(Database $db){
        $this->db = $db;
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
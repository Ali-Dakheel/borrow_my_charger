<?php

use Core\Database;

class Review {
    protected $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            return $this->db->query(
                'INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)',
                [
                    $data['booking_id'],
                    $data['rating'],
                    $data['comment']
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Failed to create review: " . $e->getMessage());
        }
    }
    
    public function getByBookingId($bookingId) {
        try {
            return $this->db->query(
                'SELECT * FROM reviews WHERE booking_id = ?',
                [$bookingId]
            )->find();
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch review: " . $e->getMessage());
        }
    }

    public function update($reviewId, $data) {
        try {
            return $this->db->query(
                'UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ?',
                [
                    $data['rating'],
                    $data['comment'],
                    $reviewId
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Failed to update review: " . $e->getMessage());
        }
    }
}
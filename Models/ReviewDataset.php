<?php
require_once(__DIR__ . '/ReviewData.php');

use Core\Database;

class ReviewsDataset
{
    protected $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function create($bookingId, $rating, $comment)
    {
        try {
            $this->db->query(
                'INSERT INTO reviews (booking_id, rating, comment) VALUES (?, ?, ?)',
                [$bookingId, $rating, $comment]
            );
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to create review: " . $e->getMessage());
        }
    }
    
    public function getByBookingId($bookingId)
    {
        try {
            $row = $this->db->query(
                'SELECT * FROM reviews WHERE booking_id = ?',
                [$bookingId]
            )->find();
            
            if ($row) {
                return new ReviewData($row);
            }
            
            return null;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch review: " . $e->getMessage());
        }
    }

    public function update($reviewId, $rating, $comment)
    {
        try {
            $this->db->query(
                'UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ?',
                [$rating, $comment, $reviewId]
            );
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to update review: " . $e->getMessage());
        }
    }
    
    public function getAllReviews()
    {
        $rows = $this->db->query('SELECT * FROM reviews')->findAll();
        $dataSet = [];
        
        foreach($rows as $row){
            $dataSet[] = new ReviewData($row);
        }
        
        return $dataSet;
    }
    
    public function deleteReview($reviewId)
    {
        try {
            $this->db->query(
                'DELETE FROM reviews WHERE review_id = ?',
                [$reviewId]
            );
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to delete review: " . $e->getMessage());
        }
    }
}
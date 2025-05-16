<?php
require_once(__DIR__ . '/ReviewData.php');

use Core\Database;

/**
 * Class ReviewsDataset
 *
 * Data access layer for reviews. Provides methods to create, retrieve, update,
 * and delete review records associated with bookings.
 */
class ReviewsDataset
{
    /**
     * @var Database Database connection instance
     */
    protected $db;
    
    /**
     * ReviewsDataset constructor.
     *
     * @param Database $db The database connection to use for queries
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new review for a booking.
     *
     * @param int    $bookingId The ID of the booking to review
     * @param int    $rating    Rating value (e.g., 1-5)
     * @param string $comment   Review comment text
     * @return bool True if insert succeeded
     * @throws Exception If the insert fails
     */
    public function create(int $bookingId, int $rating, string $comment): bool
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
    
    /**
     * Retrieve a review by its associated booking ID.
     *
     * @param int $bookingId The ID of the booking whose review to fetch
     * @return ReviewData|null The ReviewData object or null if none exists
     * @throws Exception If the query fails
     */
    public function getByBookingId(int $bookingId): ?ReviewData
    {
        try {
            $row = $this->db->query(
                'SELECT * FROM reviews WHERE booking_id = ?',
                [$bookingId]
            )->find();
            
            return $row ? new ReviewData($row) : null;
        } catch (PDOException $e) {
            throw new Exception("Failed to fetch review: " . $e->getMessage());
        }
    }

    /**
     * Update an existing review.
     *
     * @param int    $reviewId The ID of the review to update
     * @param int    $rating   New rating value
     * @param string $comment  New review comment
     * @return bool True if update succeeded
     * @throws Exception If the update fails
     */
    public function update(int $reviewId, int $rating, string $comment): bool
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
    
    /**
     * Retrieve all reviews in the system.
     *
     * @return ReviewData[] Array of ReviewData objects
     */
    public function getAllReviews(): array
    {
        $rows = $this->db->query('SELECT * FROM reviews')->findAll();
        $dataSet = [];
        
        foreach ($rows as $row) {
            $dataSet[] = new ReviewData($row);
        }
        
        return $dataSet;
    }
    
    /**
     * Delete a review by its ID.
     *
     * @param int $reviewId The ID of the review to delete
     * @return bool True if delete succeeded
     * @throws Exception If the delete fails
     */
    public function deleteReview(int $reviewId): bool
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

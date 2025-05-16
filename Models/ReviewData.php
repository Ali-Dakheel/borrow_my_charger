<?php

/**
 * Class ReviewData
 *
 * Represents a review left by a rental user for a booking, including rating, comment, and timestamp.
 */
class ReviewData
{
    /** @var int|null Unique identifier for the review */
    protected $id;

    /** @var int|null Booking ID associated with this review */
    protected $bookingId;

    /** @var int|null Rating given by the user (1-5) */
    protected $rating;

    /** @var string|null Comment left by the user */
    protected $comment;

    /** @var string|null Timestamp when the review was created (Y-m-d H:i:s) */
    protected $createdAt;

    /**
     * ReviewData constructor.
     *
     * @param array $row Associative array representing a database row for a review.
     */
    public function __construct(array $row)
    {
        $this->id        = $row['review_id'] ?? null;
        $this->bookingId = $row['booking_id'] ?? null;
        $this->rating    = $row['rating'] ?? null;
        $this->comment   = $row['comment'] ?? null;
        $this->createdAt = $row['created_at'] ?? null;
    }

    /**
     * Get the review ID.
     *
     * @return int|null
     */
    public function getReviewId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the booking ID associated with this review.
     *
     * @return int|null
     */
    public function getBookingId(): ?int
    {
        return $this->bookingId;
    }

    /**
     * Get the rating value of the review.
     *
     * @return int|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * Get the comment text of the review.
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    /**
     * Get the timestamp when the review was created.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
}

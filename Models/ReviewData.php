<?php

class ReviewData
{
    protected $id, $bookingId, $rating, $comment, $createdAt;

    public function __construct($row)
    {
        $this->id = $row['review_id'] ?? null;
        $this->bookingId = $row['booking_id'] ?? null;
        $this->rating = $row['rating'] ?? null;
        $this->comment = $row['comment'] ?? null;
        $this->createdAt = $row['created_at'] ?? null;
    }

    public function getReviewId()
    {
        return $this->id;
    }

    public function getBookingId()
    {
        return $this->bookingId;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function getComment()
    {
        return $this->comment;
    }
    
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
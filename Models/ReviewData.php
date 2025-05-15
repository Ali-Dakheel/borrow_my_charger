<?php

class ReviewData
{
    protected $id, $bookingId, $rating, $comment;

    public function __construct($row)
    {
        $this->id = $row['review_id'] ?? null;
        $this->bookingId = $row['booking_id'] ?? null;
        $this->rating = $row['rating'] ?? null;
        $this->comment = $row['comment'] ?? null;
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
}
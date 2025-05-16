<?php

/**
 * Class BookingData
 *
 * Represents a booking record with details about the user, charge point, timing, cost, and status.
 */
class BookingData
{
    protected $id, $userId, $chargePointId, $startTime, $endTime, $totalCost, $status, $createdAt;
    /**
     * BookingData constructor.
     *
     * @param array $row Database row containing booking data.
     */
    public function __construct($row)
    {
        $this->id = $row['id'] ?? null;
        $this->userId = $row['user_id'] ?? null;
        $this->chargePointId = $row['charge_point_id'] ?? null;
        $this->startTime = $row['start_time'] ?? null;
        $this->endTime = $row['end_time'] ?? null;
        $this->totalCost = $row['total_cost'] ?? null;
        $this->status = $row['status'] ?? null;
        $this->createdAt = $row['created_at'] ?? null;
    }

    /**
     * Get the booking ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Get the user ID associated with the booking.
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }
    /**
     * Get the charge point ID associated with the booking.
     *
     * @return int|null
     */

    public function getChargePointId()
    {
        return $this->chargePointId;
    }
    /**
     * Get the start time of the booking.
     *
     * @return string|null
     */

    public function getStartTime()
    {
        return $this->startTime;
    }
    /**
     * Get the end time of the booking.
     *
     * @return string|null
     */
    public function getEndTime()
    {
        return $this->endTime;
    }
    /**
     * Get the total cost of the booking.
     *
     * @return float|null
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }
    /**
     * Get the booking status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }
    /**
     * Get the booking status.
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

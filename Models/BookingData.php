<?php

class BookingData
{
    protected $id, $userId, $chargePointId, $startTime, $endTime, $totalCost, $status, $createdAt;

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
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    public function getChargePointId()
    {
        return $this->chargePointId;
    }
    
    public function getStartTime()
    {
        return $this->startTime;
    }
    
    public function getEndTime()
    {
        return $this->endTime;
    }
    
    public function getTotalCost()
    {
        return $this->totalCost;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
<?php

class ChargePointData
{
    protected $id, $homeownerId, $address, $postcode, $latitude, $longitude, $pricePerKwh, $isAvailable, $imagePath;

    public function __construct($row)
    {
        $this->id = $row['id'] ?? null;
        $this->homeownerId = $row['homeowner_id'] ?? null;
        $this->address = $row['address'] ?? null;
        $this->postcode = $row['postcode'] ?? null;
        $this->latitude = $row['latitude'] ?? null;
        $this->longitude = $row['longitude'] ?? null;
        $this->pricePerKwh = $row['price_per_kwh'] ?? null;
        $this->isAvailable = $row['is_available'] ?? null;
        $this->imagePath = $row['image_path'] ?? null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getHomeownerId()
    {
        return $this->homeownerId;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getPricePerKwh()
    {
        return $this->pricePerKwh;
    }

    public function getIsAvailable()
    {
        return $this->isAvailable;
    }

    public function getImagePath()
    {
        return $this->imagePath;
    }
    
    // Helper method to convert to array for compatibility with templates
    public function toArray()
    {
        return [
            'id' => $this->id,
            'homeowner_id' => $this->homeownerId,
            'address' => $this->address,
            'postcode' => $this->postcode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'price_per_kwh' => $this->pricePerKwh,
            'is_available' => $this->isAvailable,
            'image_path' => $this->imagePath
        ];
    }
}
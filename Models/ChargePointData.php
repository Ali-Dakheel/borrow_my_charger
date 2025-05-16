<?php

/**
 * Class ChargePointData
 *
 * Represents a charge point entity with location, pricing, availability, and image information.
 */
class ChargePointData
{
    /** @var int|null Unique identifier for the charge point */
    protected $id;

    /** @var int|null User ID of the homeowner who owns this charge point */
    protected $homeownerId;

    /** @var string|null Street address of the charge point */
    protected $address;

    /** @var string|null Postal code of the charge point location */
    protected $postcode;

    /** @var float|null Latitude coordinate of the charge point */
    protected $latitude;

    /** @var float|null Longitude coordinate of the charge point */
    protected $longitude;

    /** @var float|null Price per kWh charged at this point */
    protected $pricePerKwh;

    /** @var bool|null Availability status of the charge point */
    protected $isAvailable;

    /** @var string|null File path or URL to the charge point image */
    protected $imagePath;

    /**
     * ChargePointData constructor.
     *
     * @param array $row Associative array representing a database row for a charge point.
     */
    public function __construct(array $row)
    {
        $this->id           = $row['id'] ?? null;
        $this->homeownerId = $row['homeowner_id'] ?? null;
        $this->address      = $row['address'] ?? null;
        $this->postcode     = $row['postcode'] ?? null;
        $this->latitude     = $row['latitude'] ?? null;
        $this->longitude    = $row['longitude'] ?? null;
        $this->pricePerKwh  = $row['price_per_kwh'] ?? null;
        $this->isAvailable  = $row['is_available'] ?? null;
        $this->imagePath    = $row['image_path'] ?? null;
    }

    /**
     * Get the charge point ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the homeowner's user ID.
     *
     * @return int|null
     */
    public function getHomeownerId()
    {
        return $this->homeownerId;
    }

    /**
     * Get the address of the charge point.
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get the postcode of the charge point.
     *
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Get the latitude coordinate.
     *
     * @return float|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Get the longitude coordinate.
     *
     * @return float|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Get the price per kWh.
     *
     * @return float|null
     */
    public function getPricePerKwh()
    {
        return $this->pricePerKwh;
    }

    /**
     * Check if the charge point is available.
     *
     * @return bool|null
     */
    public function getIsAvailable()
    {
        return $this->isAvailable;
    }

    /**
     * Get the path or URL to the charge point's image.
     *
     * @return string|null
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * Convert this object to an associative array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'              => $this->id,
            'homeowner_id'    => $this->homeownerId,
            'address'         => $this->address,
            'postcode'        => $this->postcode,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'price_per_kwh'   => $this->pricePerKwh,
            'is_available'    => $this->isAvailable,
            'image_path'      => $this->imagePath,
        ];
    }
}

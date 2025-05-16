<?php

/**
 * Class ContactMessageData
 *
 * Represents a contact message related to a booking, including sender, recipient, content, and timestamp.
 */
class ContactMessageData
{
    /** @var int|null Unique identifier for the message */
    protected $id;

    /** @var int|null Booking ID associated with this message */
    protected $bookingId;

    /** @var int|null User ID of the sender */
    protected $senderId;

    /** @var int|null User ID of the recipient */
    protected $recipientId;

    /** @var string|null Content of the message */
    protected $message;

    /** @var string|null Timestamp when the message was sent (Y-m-d H:i:s) */
    protected $sentAt;

    /**
     * ContactMessageData constructor.
     *
     * @param array $row Associative array representing a database row for a contact message
     */
    public function __construct(array $row)
    {
        $this->id          = $row['id'] ?? null;
        $this->bookingId   = $row['booking_id'] ?? null;
        $this->senderId    = $row['sender_id'] ?? null;
        $this->recipientId = $row['recipient_id'] ?? null;
        $this->message     = $row['message'] ?? null;
        $this->sentAt      = $row['sent_at'] ?? null;
    }

    /**
     * Get the message ID.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the booking ID associated with this message.
     *
     * @return int|null
     */
    public function getBookingId(): ?int
    {
        return $this->bookingId;
    }

    /**
     * Get the sender's user ID.
     *
     * @return int|null
     */
    public function getSenderId(): ?int
    {
        return $this->senderId;
    }

    /**
     * Get the recipient's user ID.
     *
     * @return int|null
     */
    public function getRecipientId(): ?int
    {
        return $this->recipientId;
    }

    /**
     * Get the message content.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Get the timestamp when the message was sent.
     *
     * @return string|null
     */
    public function getSentAt(): ?string
    {
        return $this->sentAt;
    }

    /**
     * Convert this object to an associative array for template compatibility.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'booking_id'   => $this->bookingId,
            'sender_id'    => $this->senderId,
            'recipient_id' => $this->recipientId,
            'message'      => $this->message,
            'sent_at'      => $this->sentAt,
        ];
    }
}

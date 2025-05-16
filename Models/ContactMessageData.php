<?php
// ContactMessageData.php
class ContactMessageData
{
    protected $id, $bookingId, $senderId, $recipientId, $message, $sentAt;

    public function __construct($row)
    {
        $this->id = $row['id'] ?? null;
        $this->bookingId = $row['booking_id'] ?? null;
        $this->senderId = $row['sender_id'] ?? null;
        $this->recipientId = $row['recipient_id'] ?? null;
        $this->message = $row['message'] ?? null;
        $this->sentAt = $row['sent_at'] ?? null;
        }

    public function getId()
    {
        return $this->id;
    }

    public function getBookingId()
    {
        return $this->bookingId;
    }

    public function getSenderId()
    {
        return $this->senderId;
    }

    public function getRecipientId()
    {
        return $this->recipientId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getSentAt()
    {
        return $this->sentAt;
    }
    
    
    // Helper method to convert to array for compatibility with templates
    public function toArray()
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->bookingId,
            'sender_id' => $this->senderId,
            'recipient_id' => $this->recipientId,
            'message' => $this->message,
            'sent_at' => $this->sentAt,
        ];
    }
}
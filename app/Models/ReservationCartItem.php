<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationCartItem extends Model
{
    protected $fillable = ['cart_id', 'book_id', 'days', 'daily_fee', 'pickup_date', 'pickup_time', 'return_date', 'quantity'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(ReservationCart::class, 'cart_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Calculate days from pickup and return dates
     */
    public function calculateDaysFromDates(): int
    {
        // Lấy từ attributes để tránh vấn đề với Carbon cast
        $pickupDate = $this->attributes['pickup_date'] ?? null;
        $returnDate = $this->attributes['return_date'] ?? null;

        if ($pickupDate && $returnDate) {
            $pickup = new \DateTime($pickupDate);
            $return = new \DateTime($returnDate);
            return max(1, (int)$pickup->diff($return)->days);
        }
        return $this->days ?? 1;
    }

    /**
     * Calculate total price for this item
     */
    public function getTotalPriceAttribute(): float
    {
        $pickupDate = $this->attributes['pickup_date'] ?? null;
        $returnDate = $this->attributes['return_date'] ?? null;

        if (!$pickupDate || !$returnDate) {
            return 0;
        }

        $days = $this->calculateDaysFromDates();
        $quantity = max(1, (int) ($this->quantity ?? 1));
        $dailyFee = (int) ($this->daily_fee ?? 5000);

        return $days * $dailyFee * $quantity;
    }
}

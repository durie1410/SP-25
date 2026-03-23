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
     * Mượn + trả cùng ngày = 1 ngày, mượn hôm nay trả ngày mai = 2 ngày
     */
    public function calculateDaysFromDates(): int
    {
        // Lấy pickup_date - có thể là string, Carbon object, hoặc null
        $pickupDate = $this->pickup_date ?? $this->attributes['pickup_date'] ?? null;
        $returnDate = $this->return_date ?? $this->attributes['return_date'] ?? null;

        if ($pickupDate && $returnDate) {
            // Convert sang string nếu là Carbon object
            $pickupStr = $pickupDate instanceof \Carbon\Carbon ? $pickupDate->format('Y-m-d') : $pickupDate;
            $returnStr = $returnDate instanceof \Carbon\Carbon ? $returnDate->format('Y-m-d') : $returnDate;

            $pickup = new \DateTime($pickupStr);
            $return = new \DateTime($returnStr);
            // Cộng 1 để tính cả ngày mượn
            return max(1, (int)$pickup->diff($return)->days + 1);
        }
        return $this->days ?? 1;
    }

    /**
     * Calculate total price for this item
     */
    public function getTotalPriceAttribute(): float
    {
        // Lấy pickup_date - có thể là string, Carbon object, hoặc null
        $pickupDate = $this->pickup_date ?? $this->attributes['pickup_date'] ?? null;
        $returnDate = $this->return_date ?? $this->attributes['return_date'] ?? null;

        if (!$pickupDate || !$returnDate) {
            return 0;
        }

        $days = $this->calculateDaysFromDates();
        $quantity = max(1, (int) ($this->quantity ?? 1));
        $dailyFee = (int) ($this->daily_fee ?? $this->book?->daily_fee ?? 5000);

        return $days * $dailyFee * $quantity;
    }
}

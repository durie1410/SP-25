<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationCartItem extends Model
{
    protected $fillable = ['cart_id', 'book_id', 'days', 'daily_fee', 'pickup_date', 'return_date', 'quantity'];
    protected $dates = ['pickup_date', 'return_date'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(ReservationCart::class, 'cart_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Calculate total price for this item
     * Total = days * daily_fee * quantity
     */
    public function getTotalPriceAttribute(): float
    {
        return ($this->days ?? 1) * ($this->daily_fee ?? 5000) * ($this->quantity ?? 1);
    }

    /**
     * Calculate days from pickup and return dates
     */
    public function calculateDaysFromDates(): int
    {
        if ($this->pickup_date && $this->return_date) {
            $pickup = new \DateTime($this->pickup_date);
            $return = new \DateTime($this->return_date);
            return max(1, (int)$pickup->diff($return)->days);
        }
        return $this->days ?? 1;
    }
}


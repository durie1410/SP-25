<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationCartItem extends Model
{
    protected $fillable = ['cart_id', 'book_id'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(ReservationCart::class, 'cart_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}

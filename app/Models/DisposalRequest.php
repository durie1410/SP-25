<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'inventory_id',
        'requested_by',
        'reason',
        'type',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}


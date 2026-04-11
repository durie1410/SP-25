<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookDeleteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'inventory_id',
        'borrow_item_id',
        'requested_by',
        'approved_by',
        'status',
        'reason',
        'proof_images',
        'admin_note',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'proof_images' => 'array',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function borrowItem()
    {
        return $this->belongsTo(BorrowItem::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

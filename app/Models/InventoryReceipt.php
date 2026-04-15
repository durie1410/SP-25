<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'book_id',
        'quantity',
        'storage_location',
        'storage_type',
        'unit_price',
        'total_price',
        'supplier',
        'supplier_id',
        'received_by',
        'approved_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'receipt_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryReceiptItem::class, 'receipt_id');
    }

    // Book关系：为了向后兼容，保留book_id关系
    // 如果receipt有items，可以通过items获取多个book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    // 获取第一个item的book（如果有items）
    public function getFirstBookAttribute()
    {
        if ($this->items()->exists()) {
            return $this->items()->first()->book;
        }
        return $this->book;
    }

    // Scope để lấy phiếu đang chờ phê duyệt
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope để lấy phiếu đã phê duyệt
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Tạo số phiếu tự động
    public static function generateReceiptNumber()
    {
        $prefix = 'PNK';
        $date = now()->format('Ymd');
        $lastReceipt = self::where('receipt_number', 'like', $prefix . $date . '%')
            ->orderBy('receipt_number', 'desc')
            ->first();
        
        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $date . $newNumber;
    }
}

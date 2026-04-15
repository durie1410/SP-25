<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function receipts()
    {
        return $this->hasMany(InventoryReceipt::class, 'supplier_id');
    }
}

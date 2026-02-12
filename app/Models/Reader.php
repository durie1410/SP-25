<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reader extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'faculty_id',
        'department_id',
        'ho_ten',
        'email',
        'so_dien_thoai',
        'so_cccd',
        'ngay_sinh',
        'gioi_tinh',
        'dia_chi',
        'tinh_thanh',
        'huyen',
        'xa',
        'so_nha',
        'so_the_doc_gia',
        'ngay_cap_the',
        'ngay_het_han',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_sinh' => 'date',
        'ngay_cap_the' => 'date',
        'ngay_het_han' => 'date',
    ];
public function borrowItems()
{
    return $this->hasManyThrough(BorrowItem::class, Borrow::class);
}
    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function pendingFines()
    {
        return $this->hasMany(Fine::class)->where('status', 'pending');
    }

    public function totalPendingFines()
    {
        return $this->pendingFines()->sum('amount');
    }


    public function activeBorrows()
    {
        return $this->hasMany(Borrow::class)->where('trang_thai', 'Dang muon');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

}

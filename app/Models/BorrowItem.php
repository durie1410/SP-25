<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BorrowItem extends Model
{
    use HasFactory;

    protected $table = 'borrow_items';

    protected $fillable = [
        'borrow_id',
        'book_id',
        'voucher_id',
        'tien_coc',
        'tien_coc_da_thu',
        'tien_coc_da_hoan',
        'ngay_muon',
        'trang_thai_coc',
        'ngay_thu_coc',
        'ngay_hoan_coc',
        'phuong_thuc_thu_coc',
        'phuong_thuc_hoan_coc',
        'ghi_chu_coc',
        'tien_thue',
        'tien_ship',
        'ngay_hen_tra',
        'ngay_tra_thuc_te',
        'trang_thai',
        'borrow_type',
        'so_lan_gia_han',
        'ngay_gia_han_cuoi',
        'inventorie_id',
        'tien_phat',
        'tinh_trang_sach_cuoi',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_muon' => 'datetime',
        'ngay_hen_tra' => 'datetime',
        'ngay_tra_thuc_te' => 'date',
        'ngay_gia_han_cuoi' => 'date',
        'ngay_thu_coc' => 'date',
        'ngay_hoan_coc' => 'date',
        'tien_coc' => 'decimal:2',
        'tien_coc_da_thu' => 'decimal:2',
        'tien_coc_da_hoan' => 'decimal:2',
    ];


    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventorie_id');
    }
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
    public function getDaysRemainingAttribute()
    {
        if (!$this->ngay_hen_tra) {
            return 0;
        }

        $today = Carbon::today(); // hôm nay
        // Đảm bảo ngay_hen_tra là Carbon object
        $ngayHenTra = $this->ngay_hen_tra;
        if (!($ngayHenTra instanceof Carbon)) {
            $ngayHenTra = Carbon::parse($ngayHenTra);
        }

        return $ngayHenTra->diffInDays($today, false);
        // Nếu >=0: còn hạn, <0: quá hạn
    }


    public function borrow()
    {
        return $this->belongsTo(Borrow::class, 'borrow_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function isOverdue()
    {
        if ($this->trang_thai !== 'Dang muon' || !$this->ngay_hen_tra) {
            return false;
        }

        // Đảm bảo ngay_hen_tra là Carbon object
        $ngayHenTra = $this->ngay_hen_tra;
        if (!($ngayHenTra instanceof Carbon)) {
            $ngayHenTra = Carbon::parse($ngayHenTra);
        }

        return $ngayHenTra < now();
    }

    public function canExtend()
    {
        return $this->trang_thai === 'Dang muon' && $this->so_lan_gia_han < 2 && !$this->isOverdue();
    }

    public function extend($days = 7)
    {
        if (!$this->canExtend())
            return false;

        // Đảm bảo ngay_hen_tra là Carbon object
        $ngayHenTra = $this->ngay_hen_tra;
        if (!($ngayHenTra instanceof Carbon)) {
            $ngayHenTra = Carbon::parse($ngayHenTra);
        }

        $this->update([
            'ngay_hen_tra' => $ngayHenTra->copy()->addDays($days),
            'so_lan_gia_han' => $this->so_lan_gia_han + 1,
            'ngay_gia_han_cuoi' => now()->toDateString(),
        ]);
        return true;
    }


    public function payments()
    {
        return $this->hasMany(BorrowPayment::class);
    }

    public function shippingLogs()
    {
        return $this->hasMany(ShippingLog::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function pendingFines()
    {
        return $this->hasMany(Fine::class)->where('status', 'pending');
    }

}

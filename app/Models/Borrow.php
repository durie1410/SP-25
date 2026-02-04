<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $table = 'borrows';

    // ===================================
    // CONSTANTS - 11 Trạng thái đơn mượn
    // ===================================
    const STATUS_DON_HANG_MOI = 'don_hang_moi';
    const STATUS_DANG_CHUAN_BI_SACH = 'dang_chuan_bi_sach';
    const STATUS_CHO_BAN_GIAO_VAN_CHUYEN = 'cho_ban_giao_van_chuyen';
    const STATUS_DANG_GIAO_HANG = 'dang_giao_hang';
    const STATUS_GIAO_HANG_THANH_CONG = 'giao_hang_thanh_cong';
    const STATUS_GIAO_HANG_THAT_BAI = 'giao_hang_that_bai';
    const STATUS_DA_MUON_DANG_LUU_HANH = 'da_muon_dang_luu_hanh';
    const STATUS_CHO_TRA_SACH = 'cho_tra_sach';
    const STATUS_DANG_VAN_CHUYEN_TRA_VE = 'dang_van_chuyen_tra_ve';
    const STATUS_DA_NHAN_VA_KIEM_TRA = 'da_nhan_va_kiem_tra';
    const STATUS_HOAN_TAT_DON_HANG = 'hoan_tat_don_hang';

    // Tình trạng sách
    const CONDITION_BINH_THUONG = 'binh_thuong';
    const CONDITION_HONG_NHE = 'hong_nhe';
    const CONDITION_HONG_NANG = 'hong_nang';
    const CONDITION_MAT_SACH = 'mat_sach';

    protected $fillable = [
        'ten_nguoi_muon',
        'tinh_thanh',
        'huyen',
        'xa',
        'so_nha',
        'so_dien_thoai',
        'reader_id',
        'librarian_id',
        'ngay_muon',
        'trang_thai',
        'trang_thai_chi_tiet', // ✅ thêm trạng thái chi tiết
        'tinh_trang_sach',
        'anh_hoan_tra', // Ảnh minh chứng hoàn trả sách từ khách hàng
        'phi_hong_sach',
        'tien_coc_hoan_tra',
        'tong_tien',
        'tien_coc',
        'tien_thue',
        'tien_ship',
        'voucher_id',
        'ghi_chu',
        // Ghi chú chi tiết theo từng bước
        'ghi_chu_giao_hang',
        'ghi_chu_tra_hang',
        'ghi_chu_kiem_tra',
        'ghi_chu_hoan_coc',
        'ghi_chu_dong_goi',
        'ghi_chu_ban_giao',
        'ghi_chu_that_bai',
        'ghi_chu_yeu_cau_tra',
        // Timestamp cho các bước
        'ngay_xac_nhan',
        'ngay_chuan_bi',
        'ngay_dong_goi_xong',
        'ngay_ban_giao_van_chuyen',
        'ngay_bat_dau_giao',
        'ngay_giao_thanh_cong',
        'ngay_that_bai_giao_hang',
        'ngay_bat_dau_luu_hanh',
        'ngay_yeu_cau_tra_sach',
        'ngay_bat_dau_tra',
        'ngay_nhan_tra',
        'ngay_kiem_tra',
        'ngay_hoan_coc',
        // Xác nhận từ khách hàng
        'customer_confirmed_delivery',
        'customer_confirmed_delivery_at',
        // Ảnh khi khách nhận sách
        'anh_bia_truoc',
        'anh_bia_sau',
        'anh_gay_sach',
        // Từ chối từ khách hàng
        'customer_rejected_delivery',
        'customer_rejected_delivery_at',
        'customer_rejection_reason',
        // Thời gian chờ xác nhận
        'ngay_cho_xac_nhan_nhan',
        // Người xử lý
        'nguoi_chuan_bi_id',
        'nguoi_giao_hang_id',
        'nguoi_kiem_tra_id',
        'nguoi_hoan_coc_id',
        // Thông tin vận chuyển
        'ma_van_don_di',
        'ma_van_don_ve',
        'don_vi_van_chuyen',
    ];

    protected $casts = [
        'ngay_muon' => 'date',
        'anh_hoan_tra' => 'array',
    ];


    // 🔹 Một phiếu mượn có nhiều sách mượn
    public function borrowItems()
    {
        return $this->hasMany(BorrowItem::class, 'borrow_id', 'id');
    }

    // 🔹 Lấy quyển sách đầu tiên (nếu cần hiển thị nhanh)
    public function getBookAttribute()
    {
        // Sử dụng eager-loaded items nếu có, nếu không thì query
        if ($this->relationLoaded('items')) {
            return $this->items->first()?->book;
        }
        return $this->items()->first()?->book;
    }

    // 🔹 Lấy ngày trả thực tế (lấy từ borrow_items - item trả đầu tiên)
    public function getNgayTraThucTeAttribute()
    {
        // Sử dụng eager-loaded items nếu có, nếu không thì query
        if ($this->relationLoaded('items')) {
            return $this->items->first()?->ngay_tra_thuc_te;
        }
        return $this->items()->first()?->ngay_tra_thuc_te;
    }

    // 🔹 Lấy ngày hẹn trả (lấy từ borrow_items - item đang mượn đầu tiên)
    public function getNgayHenTraAttribute()
    {
        // Lấy item đang mượn có ngày hẹn trả sớm nhất
        if ($this->relationLoaded('items')) {
            $activeItem = $this->items->where('trang_thai', 'Dang muon')->sortBy('ngay_hen_tra')->first();
            return $activeItem?->ngay_hen_tra;
        }
        $activeItem = $this->items()->where('trang_thai', 'Dang muon')->orderBy('ngay_hen_tra')->first();
        return $activeItem?->ngay_hen_tra;
    }

    // 🔹 Lấy tất cả sách thông qua bảng trung gian BorrowItem
    public function books()
    {
        return $this->hasManyThrough(
            Book::class,
            BorrowItem::class,
            'borrow_id', // FK của BorrowItem trỏ tới Borrow
            'id',        // PK của Book
            'id',        // PK của Borrow
            'book_id'    // FK của BorrowItem trỏ tới Book
        );
    }

    // 🔹 Một Borrow có thể có nhiều BorrowItem
    public function borrowItem()
    {
        return $this->hasOne(BorrowItem::class);
    }

    // 🔹 Người mượn
    public function reader()
    {
        return $this->belongsTo(Reader::class);
    }

    // 🔹 Thủ thư xử lý
    public function librarian()
    {
        return $this->belongsTo(User::class, 'librarian_id');
    }

    // 🔹 Các khoản phạt
    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function pendingFines()
    {
        return $this->hasMany(Fine::class)->where('status', 'pending');
    }

    public function items()
    {
        return $this->hasMany(BorrowItem::class, 'borrow_id');
    }

    // 🔹 Kiểm tra quá hạn
    public function isOverdue()
    {
        return $this->items()
            ->where('trang_thai', 'Dang muon')
            ->where('ngay_hen_tra', '<', now()->toDateString())
            ->exists();
    }

    // 🔹 Kiểm tra có thể gia hạn không
    public function canExtend()
    {
        $maxExtensions = 2;
        return $this->trang_thai === 'Dang muon' &&
            $this->so_lan_gia_han < $maxExtensions &&
            !$this->isOverdue();
    }

    // 🔹 Gia hạn mượn
    public function extend($days = 7)
    {
        if (!$this->canExtend()) {
            return false;
        }

        // Gia hạn tất cả các item đang mượn
        $this->items()->where('trang_thai', 'Dang muon')->each(function ($item) use ($days) {
            $item->extend($days);
        });

        return true;
    }

    // 🔹 Số ngày quá hạn
    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        $ngayHenTra = $this->ngay_hen_tra;
        if (!$ngayHenTra) {
            return 0;
        }
        return now()->diffInDays($ngayHenTra, false);
    }

    // 🔹 Kiểm tra có thể trả sách
    public function canReturn()
    {
        return $this->trang_thai === 'Dang muon';
    }

    // ✅ Thêm quan hệ voucher
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function recalculateTotals()
    {
        // Tính tổng các khoản
        $this->tien_coc = $this->borrowItems()->sum('tien_coc');
        $this->tien_thue = $this->borrowItems()->sum('tien_thue');
        $this->tien_ship = $this->borrowItems()->sum('tien_ship');

        $tien_phat = $this->borrowItems()->sum('tien_phat');

        // Tổng trước khi áp dụng voucher
        $this->tong_tien = $this->tien_coc + $this->tien_thue + $this->tien_ship + $tien_phat;

        // Nếu có voucher, áp dụng giảm giá
        if ($this->voucher) {
            $voucher = $this->voucher;
            $tongTienTruocGiam = $this->tien_coc + $this->tien_thue + $this->tien_ship + $tien_phat;

            if ($voucher->loai === 'percentage') {
                $discount = ($tongTienTruocGiam * $voucher->gia_tri) / 100;
            } else { // loai = 'fixed'
                $discount = min($voucher->gia_tri, $tongTienTruocGiam);
            }

            $this->tong_tien = max(0, $tongTienTruocGiam - $discount);
        }

        // Lưu lại vào database
        $this->save();
    }


    public function payments()
    {
        return $this->hasMany(BorrowPayment::class);
    }

    public function shippingLogs()
    {
        return $this->hasMany(ShippingLog::class);
    }

    // 🔹 Người chuẩn bị hàng
    public function nguoiChuanBi()
    {
        return $this->belongsTo(User::class, 'nguoi_chuan_bi_id');
    }

    // 🔹 Người giao hàng
    public function nguoiGiaoHang()
    {
        return $this->belongsTo(User::class, 'nguoi_giao_hang_id');
    }

    // 🔹 Người kiểm tra
    public function nguoiKiemTra()
    {
        return $this->belongsTo(User::class, 'nguoi_kiem_tra_id');
    }

    // 🔹 Người hoàn cọc
    public function nguoiHoanCoc()
    {
        return $this->belongsTo(User::class, 'nguoi_hoan_coc_id');
    }

    // ===================================
// HELPER METHODS - Trạng thái
// ===================================

    /**
     * Lấy tất cả trạng thái có thể có
     */
    public static function getAllStatuses()
    {
        return [
            self::STATUS_DON_HANG_MOI,
            self::STATUS_DANG_CHUAN_BI_SACH,
            self::STATUS_CHO_BAN_GIAO_VAN_CHUYEN,
            self::STATUS_DANG_GIAO_HANG,
            self::STATUS_GIAO_HANG_THANH_CONG,
            self::STATUS_GIAO_HANG_THAT_BAI,
            self::STATUS_DA_MUON_DANG_LUU_HANH,
            self::STATUS_CHO_TRA_SACH,
            self::STATUS_DANG_VAN_CHUYEN_TRA_VE,
            self::STATUS_DA_NHAN_VA_KIEM_TRA,
            self::STATUS_HOAN_TAT_DON_HANG,
        ];
    }

    /**
     * Lấy label hiển thị của trạng thái
     */
    public function getStatusLabel()
    {
        $config = config('borrow_status.statuses.' . $this->trang_thai_chi_tiet);
        return $config['label'] ?? $this->trang_thai_chi_tiet;
    }

    /**
     * Lấy màu của trạng thái
     */
    public function getStatusColor()
    {
        $config = config('borrow_status.statuses.' . $this->trang_thai_chi_tiet);
        return $config['color'] ?? 'secondary';
    }

    /**
     * Lấy icon của trạng thái
     */
    public function getStatusIcon()
    {
        $config = config('borrow_status.statuses.' . $this->trang_thai_chi_tiet);
        return $config['icon'] ?? 'fa-question';
    }

    /**
     * Lấy mô tả của trạng thái
     */
    public function getStatusDescription()
    {
        $config = config('borrow_status.statuses.' . $this->trang_thai_chi_tiet);
        return $config['description'] ?? '';
    }

    /**
     * Kiểm tra xem có thể chuyển sang trạng thái mới không
     */
    public function canTransitionTo($newStatus)
    {
        $config = config('borrow_status.statuses.' . $this->trang_thai_chi_tiet);
        $allowedNextStatuses = $config['next_statuses'] ?? [];
        return in_array($newStatus, $allowedNextStatuses);
    }

    /**
     * Lấy danh sách trạng thái tiếp theo có thể chuyển
     */
    public function getNextStatuses()
    {
        $config = config('borrow_status.statuses.' . $this->trang_thai_chi_tiet);
        return $config['next_statuses'] ?? [];
    }

    /**
     * Chuyển sang trạng thái mới
     */
    public function transitionTo($newStatus, $note = null, $userId = null)
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \Exception("Không thể chuyển từ {$this->trang_thai_chi_tiet} sang {$newStatus}");
        }

        $this->trang_thai_chi_tiet = $newStatus;

        // Cập nhật timestamp và người xử lý tương ứng
        switch ($newStatus) {
            case self::STATUS_DANG_CHUAN_BI_SACH:
                $this->ngay_chuan_bi = now();
                $this->nguoi_chuan_bi_id = $userId ?? auth()->id();
                $this->ghi_chu_dong_goi = $note;
                break;

            case self::STATUS_CHO_BAN_GIAO_VAN_CHUYEN:
                $this->ngay_dong_goi_xong = now();
                $this->ghi_chu_ban_giao = $note;
                break;

            case self::STATUS_DANG_GIAO_HANG:
                $this->ngay_bat_dau_giao = now();
                $this->ngay_ban_giao_van_chuyen = now();
                $this->nguoi_giao_hang_id = $userId ?? auth()->id();
                $this->ghi_chu_giao_hang = $note;
                break;

            case self::STATUS_GIAO_HANG_THANH_CONG:
                $this->ngay_giao_thanh_cong = now();
                break;

            case self::STATUS_GIAO_HANG_THAT_BAI:
                $this->ngay_that_bai_giao_hang = now();
                $this->ghi_chu_that_bai = $note;
                break;

            case self::STATUS_DA_MUON_DANG_LUU_HANH:
                $this->ngay_bat_dau_luu_hanh = now();
                // Cập nhật trạng thái tổng thể
                $this->trang_thai = 'Dang muon';
                break;

            case self::STATUS_CHO_TRA_SACH:
                $this->ngay_yeu_cau_tra_sach = now();
                $this->ghi_chu_yeu_cau_tra = $note;
                break;

            case self::STATUS_DANG_VAN_CHUYEN_TRA_VE:
                $this->ngay_bat_dau_tra = now();
                $this->ghi_chu_tra_hang = $note;
                break;

            case self::STATUS_DA_NHAN_VA_KIEM_TRA:
                $this->ngay_nhan_tra = now();
                $this->ngay_kiem_tra = now();
                $this->nguoi_kiem_tra_id = $userId ?? auth()->id();
                $this->ghi_chu_kiem_tra = $note;
                break;

            case self::STATUS_HOAN_TAT_DON_HANG:
                $this->ngay_hoan_coc = now();
                $this->nguoi_hoan_coc_id = $userId ?? auth()->id();
                $this->ghi_chu_hoan_coc = $note;
                // Cập nhật trạng thái tổng thể
                $this->trang_thai = 'Da tra';
                break;
        }

        $this->save();
        return $this;
    }

    /**
     * Kiểm tra đơn có đang trong quá trình vận chuyển không
     */
    public function isInShipping()
    {
        return in_array($this->trang_thai_chi_tiet, [
            self::STATUS_DANG_CHUAN_BI_SACH,
            self::STATUS_CHO_BAN_GIAO_VAN_CHUYEN,
            self::STATUS_DANG_GIAO_HANG,
            self::STATUS_DANG_VAN_CHUYEN_TRA_VE,
        ]);
    }

    /**
     * Kiểm tra đơn có hoàn tất chưa
     */
    public function isCompleted()
    {
        return $this->trang_thai_chi_tiet === self::STATUS_HOAN_TAT_DON_HANG;
    }

    /**
     * Kiểm tra đơn có thất bại không
     */
    public function isFailed()
    {
        return $this->trang_thai_chi_tiet === self::STATUS_GIAO_HANG_THAT_BAI;
    }

    /**
     * Kiểm tra người mượn đang giữ sách
     */
    public function isBookInHand()
    {
        return in_array($this->trang_thai_chi_tiet, [
            self::STATUS_DA_MUON_DANG_LUU_HANH,
            self::STATUS_CHO_TRA_SACH,
        ]);
    }

    /**
     * Tính phí hỏng sách dựa trên tình trạng
     */
    public function calculateDamageFee()
    {
        if (!$this->tinh_trang_sach) {
            return 0;
        }

        $condition = config('borrow_status.book_conditions.' . $this->tinh_trang_sach);
        if (!$condition) {
            return 0;
        }

        $penaltyRate = $condition['penalty_rate'] ?? 0;
        $totalBookValue = $this->tien_coc; // Tiền cọc thường = giá trị sách

        return ($totalBookValue * $penaltyRate) / 100;
    }

    /**
     * Tính tiền cọc hoàn trả
     */
    public function calculateRefundDeposit()
    {
        $damageFee = $this->phi_hong_sach ?: $this->calculateDamageFee();
        return max(0, $this->tien_coc - $damageFee);
    }

    /**
     * Cập nhật tiền hoàn cọc
     */
    public function updateRefundAmount()
    {
        $this->tien_coc_hoan_tra = $this->calculateRefundDeposit();
        $this->save();
        return $this;
    }

}

<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Inventory;
use Carbon\Carbon;

class PricingService
{
    /**
     * Tính phí thuê dựa trên giá sách và số ngày mượn
     * Tính phí thuê cho tất cả sách (không phụ thuộc vào condition)
     * 
     * @param float $bookPrice Giá sách
     * @param int $days Số ngày mượn
     * @param string $condition Tình trạng sách (Moi, Tot, Trung binh, Cu)
     * @return float Phí thuê
     */
    public static function calculateRentalFee($bookPrice, $days, $condition = 'Trung binh')
    {
        // Kiểm tra giá sách hợp lệ
        if ($bookPrice <= 0 || $days <= 0) {
            return 0;
        }
        
        // Tỷ lệ phí thuê mỗi ngày (1% giá sách mỗi ngày)
        // Không phân biệt có thẻ độc giả hay không
        // Tính phí thuê cho tất cả sách, không phân biệt tình trạng (theo config: only_for_new_books = false)
        $dailyRate = config('pricing.rental.daily_rate', config('library.rental_daily_rate', 0.01)); // 1% mỗi ngày
        
        // Tính phí thuê = giá sách * tỷ lệ mỗi ngày * số ngày
        $rentalFee = $bookPrice * $dailyRate * $days;
        
        // Làm tròn theo cấu hình (mặc định làm tròn đến hàng nghìn)
        $roundTo = config('pricing.rental.round_to', 1000);
        return round($rentalFee / $roundTo) * $roundTo;
    }

    /**
     * Tính tiền cọc dựa trên giá sách
     * Tiền cọc = giá sách (1:1)
     * 
     * @param float $bookPrice Giá sách
     * @param string $condition Tình trạng sách (Moi, Tot, Trung binh, Cu)
     * @param string $bookType Loại sách (quy, binh_thuong, tham_khao)
     * @param bool $hasCard Có thẻ độc giả hay không
     * @return float Tiền cọc
     */
    public static function calculateDeposit($bookPrice, $condition, $bookType = 'binh_thuong', $hasCard = false)
    {
        if ($bookPrice <= 0) {
            return 0;
        }

        // Policy: cọc = 70% giá sách, làm tròn đến hàng nghìn
        $depositRate = 0.7;
        $roundTo = 1000;

        $deposit = $bookPrice * $depositRate;
        return round($deposit / $roundTo) * $roundTo;
    }

    /**
     * Tính phí thuê và tiền cọc cho một BorrowItem
     * 
     * @param Book $book
     * @param Inventory $inventory
     * @param Carbon $ngayMuon
     * @param Carbon $ngayHenTra
     * @param bool $hasCard
     * @return array ['tien_thue' => float, 'tien_coc' => float]
     */
    public static function calculateFees(Book $book, Inventory $inventory, $ngayMuon, $ngayHenTra, $hasCard = false)
    {
        $bookPrice = floatval($book->gia ?? 0);
        $condition = $inventory->condition ?? 'Trung binh';
        $bookType = $book->loai_sach ?? 'binh_thuong';
        
        // Tính số ngày mượn
        $days = 1;
        if ($ngayMuon && $ngayHenTra) {
            $ngayMuonCarbon = Carbon::parse($ngayMuon);
            $ngayHenTraCarbon = Carbon::parse($ngayHenTra);
            $days = max(1, $ngayMuonCarbon->diffInDays($ngayHenTraCarbon));
        }
        
        // Nếu inventory không hợp lệ, trả về 0
        if (in_array($inventory->status, ['Hong', 'Dang muon', 'Mat'])) {
            return [
                'tien_thue' => 0,
                'tien_coc' => 0
            ];
        }
        
        // Tính phí thuê (tính cho tất cả sách)
        $tienThue = self::calculateRentalFee($bookPrice, $days, $condition);
        
        // Tính tiền cọc
        $tienCoc = self::calculateDeposit($bookPrice, $condition, $bookType, $hasCard);
        
        return [
            'tien_thue' => $tienThue,
            'tien_coc' => $tienCoc
        ];
    }

    /**
     * Tính phí trả muộn (theo cuốn sách)
     * 
     * @param Carbon $dueDate Ngày hẹn trả
     * @param Carbon|null $returnDate Ngày trả thực tế (null nếu chưa trả)
     * @param int $numberOfBooks Số cuốn sách (mặc định 1)
     * @return float Phí trả muộn
     */
    public static function calculateLateReturnFine($dueDate, $returnDate = null, $numberOfBooks = 1)
    {
        $dueDateCarbon = Carbon::parse($dueDate)->startOfDay();
        $returnDateCarbon = $returnDate ? Carbon::parse($returnDate)->startOfDay() : Carbon::today();

        // Tính số ngày quá hạn
        $daysOverdue = max(0, $dueDateCarbon->diffInDays($returnDateCarbon, false));

        if ($daysOverdue <= 0) {
            return 0;
        }

        // Policy: 3 ngày đầu 5k/ngày/cuốn, từ ngày 4 trở đi 15k/ngày/cuốn
        $fineDay1 = 5000;
        $fineDay2 = 15000;
        $threshold = 3;

        $perBook = $daysOverdue <= $threshold
            ? ($daysOverdue * $fineDay1)
            : (($threshold * $fineDay1) + (($daysOverdue - $threshold) * $fineDay2));

        return round($perBook * max(1, (int) $numberOfBooks));
    }
    
    /**
     * Kiểm tra xem có phải trễ lâu không (sẽ khóa tài khoản)
     * 
     * @param Carbon $dueDate Ngày hẹn trả
     * @param Carbon|null $returnDate Ngày trả thực tế (null nếu chưa trả)
     * @return bool
     */
    public static function isLongOverdue($dueDate, $returnDate = null)
    {
        $dueDateCarbon = Carbon::parse($dueDate);
        $returnDateCarbon = $returnDate ? Carbon::parse($returnDate) : Carbon::now();
        
        $daysOverdue = max(0, $dueDateCarbon->diffInDays($returnDateCarbon, false));
        $longOverdueDays = config('pricing.fines.late_return.long_overdue_days', 15);
        
        return $daysOverdue >= $longOverdueDays;
    }
    
    /**
     * Tính số tiền hoàn lại khi trả sớm
     * 
     * @param float $rentalFee Phí thuê đã trả
     * @param int $daysBorrowed Số ngày đã mượn
     * @param int $daysExpected Số ngày dự kiến mượn
     * @return float Số tiền hoàn lại
     */
    public static function calculateEarlyReturnRefund($rentalFee, $daysBorrowed, $daysExpected)
    {
        // Kiểm tra có được hoàn lại không
        if (!config('pricing.early_return.enabled', true)) {
            return 0;
        }
        
        // Kiểm tra có trả sớm không
        $daysEarly = $daysExpected - $daysBorrowed;
        $minEarlyDays = config('pricing.early_return.min_early_days', 1);
        
        if ($daysEarly < $minEarlyDays) {
            return 0; // Không đủ điều kiện trả sớm
        }
        
        // Policy: Hoàn 30% phần phí thuê tương ứng với số ngày trả sớm
        $refundRate = 0.3;

        // Hoàn lại = (phí thuê / số ngày dự kiến) * số ngày trả sớm * 30%
        $perDay = $daysExpected > 0 ? ($rentalFee / $daysExpected) : 0;
        $refundAmount = $perDay * $daysEarly * $refundRate;

        return round($refundAmount);
    }

    /**
     * Tính phí làm hỏng sách
     * 
     * @param float $bookPrice Giá sách
     * @param string $bookType Loại sách (quy, binh_thuong, tham_khao)
     * @param string $condition Tình trạng sách khi mượn (Moi, Tot, Trung binh, Cu, Hong)
     * @return float Phí làm hỏng
     */
    public static function calculateDamagedBookFine($bookPrice, $bookType, $condition)
    {
        if ($bookPrice <= 0) {
            return 0;
        }
        
        $fineConfig = config('pricing.fines.damaged_book.by_book_type', []);
        
        // Xử lý sách quý
        if ($bookType === 'quy') {
            $rate = $fineConfig['quy']['rate'] ?? 1.0;
            return round($bookPrice * $rate);
        }
        
        // Xử lý sách bình thường và tham khảo
        $typeConfig = $fineConfig[$bookType] ?? $fineConfig['binh_thuong'] ?? null;
        
        if (!$typeConfig || !isset($typeConfig['by_condition'])) {
            // Mặc định 70% nếu không có config
            return round($bookPrice * 0.7);
        }
        
        $rate = $typeConfig['by_condition'][$condition] ?? 0.7;
        return round($bookPrice * $rate);
    }

    /**
     * Tính phí mất sách
     * 
     * @param float $bookPrice Giá sách
     * @param string $bookType Loại sách (quy, binh_thuong, tham_khao)
     * @param string $condition Tình trạng sách khi mượn (Moi, Tot, Trung binh, Cu, Hong)
     * @return float Phí mất sách
     */
    public static function calculateLostBookFine($bookPrice, $bookType, $condition)
    {
        // Phí mất sách tính giống như phí làm hỏng
        return self::calculateDamagedBookFine($bookPrice, $bookType, $condition);
    }
}


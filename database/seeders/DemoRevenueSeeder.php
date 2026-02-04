<?php

namespace Database\Seeders;

use App\Models\Borrow;
use App\Models\BorrowPayment;
use App\Models\Fine;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoRevenueSeeder extends Seeder
{
    /**
     * Tạo dữ liệu demo để test thống kê doanh thu trên Dashboard
     */
    public function run(): void
    {
        $this->command->info('=== TẠO DỮ LIỆU DEMO DOANH THU ===');

        // ============================
        // 1. TẠO BORROW PAYMENTS
        // ============================
        $this->command->info('1. Tạo BorrowPayments...');
        
        // Lấy các borrow hiện có
        $borrows = Borrow::whereIn('trang_thai', ['Dang muon', 'Da tra'])->get();
        
        if ($borrows->isEmpty()) {
            $this->command->warn('Không có Borrow nào! Hãy tạo phiếu mượn trước.');
        } else {
            $paymentCount = 0;
            foreach ($borrows->take(10) as $borrow) {
                // Kiểm tra đã có payment chưa
                if (!BorrowPayment::where('borrow_id', $borrow->id)->exists()) {
                    // Tạo payment tiền cọc
                    if ($borrow->tien_coc > 0) {
                        BorrowPayment::create([
                            'borrow_id' => $borrow->id,
                            'amount' => $borrow->tien_coc,
                            'payment_type' => 'deposit',
                            'payment_method' => 'online',
                            'payment_status' => 'success',
                            'transaction_code' => 'DEP' . $borrow->id . time(),
                            'note' => 'Thanh toán tiền cọc',
                            'created_at' => $borrow->created_at,
                            'updated_at' => $borrow->created_at,
                        ]);
                        $paymentCount++;
                    }
                    
                    // Tạo payment tiền thuê
                    if ($borrow->tien_thue > 0) {
                        BorrowPayment::create([
                            'borrow_id' => $borrow->id,
                            'amount' => $borrow->tien_thue,
                            'payment_type' => 'borrow_fee',
                            'payment_method' => 'online',
                            'payment_status' => 'success',
                            'transaction_code' => 'FEE' . $borrow->id . time(),
                            'note' => 'Thanh toán tiền thuê sách',
                            'created_at' => $borrow->created_at,
                            'updated_at' => $borrow->created_at,
                        ]);
                        $paymentCount++;
                    }
                    
                    // Tạo payment tiền ship
                    if ($borrow->tien_ship > 0) {
                        BorrowPayment::create([
                            'borrow_id' => $borrow->id,
                            'amount' => $borrow->tien_ship,
                            'payment_type' => 'shipping_fee',
                            'payment_method' => 'online',
                            'payment_status' => 'success',
                            'transaction_code' => 'SHIP' . $borrow->id . time(),
                            'note' => 'Thanh toán tiền vận chuyển',
                            'created_at' => $borrow->created_at,
                            'updated_at' => $borrow->created_at,
                        ]);
                        $paymentCount++;
                    }
                }
            }
            $this->command->info("   → Đã tạo {$paymentCount} BorrowPayment records");
        }

        // ============================
        // 2. TẠO PAYMENT HÔM NAY
        // ============================
        $this->command->info('2. Tạo payment hôm nay...');
        
        $borrowToday = Borrow::whereIn('trang_thai', ['Dang muon', 'Da tra'])
            ->whereDoesntHave('payments', function($q) {
                $q->whereDate('created_at', Carbon::today());
            })
            ->first();
        
        if ($borrowToday) {
            BorrowPayment::create([
                'borrow_id' => $borrowToday->id,
                'amount' => 50000,
                'payment_type' => 'borrow_fee',
                'payment_method' => 'online',
                'payment_status' => 'success',
                'transaction_code' => 'TODAY' . time(),
                'note' => 'Demo: Thanh toán hôm nay',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $this->command->info('   → Đã tạo 1 payment 50,000đ hôm nay');
        } else {
            // Tạo từ borrow đầu tiên nếu có
            $anyBorrow = Borrow::first();
            if ($anyBorrow) {
                BorrowPayment::create([
                    'borrow_id' => $anyBorrow->id,
                    'amount' => 75000,
                    'payment_type' => 'borrow_fee',
                    'payment_method' => 'online',
                    'payment_status' => 'success',
                    'transaction_code' => 'TODAY' . time(),
                    'note' => 'Demo: Thanh toán hôm nay',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $this->command->info('   → Đã tạo 1 payment 75,000đ hôm nay');
            }
        }

        // ============================
        // 3. TẠO/CẬP NHẬT FINE ĐÃ THU
        // ============================
        $this->command->info('3. Cập nhật Fines...');
        
        $fines = Fine::where('status', 'pending')->take(3)->get();
        $fineCount = 0;
        
        foreach ($fines as $fine) {
            $fine->update([
                'status' => 'paid',
                'paid_date' => Carbon::now(),
            ]);
            $fineCount++;
        }
        
        // Nếu không có fine pending, tạo mới fine đã paid
        if ($fineCount == 0) {
            $borrow = Borrow::with('borrowItems')->first();
            if ($borrow) {
                $borrowItemId = $borrow->borrowItems->first()?->id ?? $borrow->id;
                Fine::create([
                    'borrow_id' => $borrow->id,
                    'borrow_item_id' => $borrowItemId,
                    'reader_id' => $borrow->reader_id,
                    'amount' => 25000,
                    'type' => 'late_return', // Giá trị enum hợp lệ: late_return, damaged_book, lost_book, other
                    'description' => 'Demo: Phạt trả sách muộn',
                    'status' => 'paid',
                    'due_date' => Carbon::now()->subDays(5),
                    'created_by' => 1,
                ]);
                $fineCount = 1;
                $this->command->info('   → Đã tạo 1 fine mới 25,000đ (đã thu)');
            }
        } else {
            $this->command->info("   → Đã cập nhật {$fineCount} fines thành 'paid'");
        }

        // ============================
        // 4. TỔNG KẾT
        // ============================
        $this->command->newLine();
        $this->command->info('=== TỔNG KẾT DỮ LIỆU ===');
        
        $totalPayments = BorrowPayment::where('payment_status', 'success')->sum('amount');
        $todayPayments = BorrowPayment::where('payment_status', 'success')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');
        $totalFinesPaid = Fine::where('status', 'paid')->sum('amount');
        $totalOrdersPaid = Order::where('payment_status', 'paid')->sum('total_amount');
        
        $this->command->table(
            ['Loại', 'Tổng số', 'Số tiền'],
            [
                ['BorrowPayment (success)', BorrowPayment::where('payment_status', 'success')->count(), number_format($totalPayments) . ' VNĐ'],
                ['BorrowPayment (hôm nay)', BorrowPayment::where('payment_status', 'success')->whereDate('created_at', Carbon::today())->count(), number_format($todayPayments) . ' VNĐ'],
                ['Fine (paid)', Fine::where('status', 'paid')->count(), number_format($totalFinesPaid) . ' VNĐ'],
                ['Order (paid)', Order::where('payment_status', 'paid')->count(), number_format($totalOrdersPaid) . ' VNĐ'],
            ]
        );
        
        $this->command->info('Tổng doanh thu dự kiến: ' . number_format($totalPayments + $totalFinesPaid + $totalOrdersPaid) . ' VNĐ');
        $this->command->info('Doanh thu hôm nay dự kiến: ' . number_format($todayPayments) . ' VNĐ');
        $this->command->newLine();
        $this->command->info('✅ Hãy vào /admin để kiểm tra Dashboard!');
    }
}

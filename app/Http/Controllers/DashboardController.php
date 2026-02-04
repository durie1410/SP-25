<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\BorrowPayment;
use App\Models\Fine;
use App\Models\Reader;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy thống kê tổng quan
        $totalBooks = Book::count();
        $totalBorrowingReaders = Borrow::where('trang_thai', 'Dang muon')->count();
        
        // Thống kê bổ sung
        $totalReservations = 0;
        
        // Thống kê theo thể loại
        $categoryStats = Category::withCount('books')->get();
        $totalCategories = Category::count();
        
        // ========================================
        // DOANH THU TỪ MƯỢN SÁCH (BorrowPayment)
        // Chỉ tính các payment đã thành công
        // ========================================
        $totalRevenueFromBorrows = BorrowPayment::where('payment_status', 'success')->sum('amount');
        $monthlyRevenueFromBorrows = BorrowPayment::where('payment_status', 'success')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
        $todayRevenueFromBorrows = BorrowPayment::where('payment_status', 'success')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');
        
        // Nếu không có BorrowPayment records, fallback tính từ Borrow với status hoàn tất
        if ($totalRevenueFromBorrows == 0) {
            // Tính từ các phiếu mượn đã hoàn tất hoặc đang mượn (đã thanh toán để nhận sách)
            $validBorrowStatuses = ['Dang muon', 'Da tra', 'hoan_tat_don_hang', 'da_muon_dang_luu_hanh'];
            $totalRevenueFromBorrows = Borrow::whereIn('trang_thai', $validBorrowStatuses)
                ->orWhereIn('trang_thai_chi_tiet', $validBorrowStatuses)
                ->sum('tong_tien');
            $monthlyRevenueFromBorrows = Borrow::where(function($q) use ($validBorrowStatuses) {
                    $q->whereIn('trang_thai', $validBorrowStatuses)
                      ->orWhereIn('trang_thai_chi_tiet', $validBorrowStatuses);
                })
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('tong_tien');
            $todayRevenueFromBorrows = Borrow::where(function($q) use ($validBorrowStatuses) {
                    $q->whereIn('trang_thai', $validBorrowStatuses)
                      ->orWhereIn('trang_thai_chi_tiet', $validBorrowStatuses);
                })
                ->whereDate('created_at', Carbon::today())
                ->sum('tong_tien');
        }
        
        // ========================================
        // DOANH THU TỪ ĐẶT/MUA SÁCH (Orders)
        // Chỉ tính các đơn đã thanh toán
        // ========================================
        $totalRevenueFromOrders = Order::where('payment_status', 'paid')->sum('total_amount');
        $monthlyRevenueFromOrders = Order::where('payment_status', 'paid')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_amount');
        $todayRevenueFromOrders = Order::where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');
        
        // ========================================
        // TỔNG HỢP DOANH THU
        // ========================================
        $totalRevenue = $totalRevenueFromBorrows + $totalRevenueFromOrders;
        $monthlyRevenue = $monthlyRevenueFromBorrows + $monthlyRevenueFromOrders;
        $todayRevenue = $todayRevenueFromBorrows + $todayRevenueFromOrders;
        
        // Tính doanh thu tháng trước để so sánh
        $lastMonthBorrowPayments = BorrowPayment::where('payment_status', 'success')
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('amount');
        if ($lastMonthBorrowPayments == 0) {
            $validBorrowStatuses = ['Dang muon', 'Da tra', 'hoan_tat_don_hang', 'da_muon_dang_luu_hanh'];
            $lastMonthBorrowPayments = Borrow::where(function($q) use ($validBorrowStatuses) {
                    $q->whereIn('trang_thai', $validBorrowStatuses)
                      ->orWhereIn('trang_thai_chi_tiet', $validBorrowStatuses);
                })
                ->whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('tong_tien');
        }
        $lastMonthRevenueFromOrders = Order::where('payment_status', 'paid')
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('total_amount');
        $lastMonthRevenue = $lastMonthBorrowPayments + $lastMonthRevenueFromOrders;
        
        // Tính phần trăm tăng/giảm
        $revenueChangePercent = 0;
        if ($lastMonthRevenue > 0) {
            $revenueChangePercent = (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
        } elseif ($monthlyRevenue > 0) {
            $revenueChangePercent = 100;
        }
        
        // ========================================
        // TIỀN PHẠT
        // ========================================
        $totalFinesPaid = Fine::where('status', 'paid')->sum('amount');
        $totalFinesPending = Fine::where('status', 'pending')->sum('amount');
        $totalFinesOverdue = Fine::overdue()->sum('amount');
        
        // DEBUG: Log để kiểm tra
        Log::info('=== DASHBOARD REVENUE DEBUG ===');
        Log::info('BorrowPayment success count: ' . BorrowPayment::where('payment_status', 'success')->count());
        Log::info('BorrowPayment success total: ' . BorrowPayment::where('payment_status', 'success')->sum('amount'));
        Log::info('Borrow count (Dang muon/Da tra): ' . Borrow::whereIn('trang_thai', ['Dang muon', 'Da tra'])->count());
        Log::info('Borrow total (Dang muon/Da tra): ' . Borrow::whereIn('trang_thai', ['Dang muon', 'Da tra'])->sum('tong_tien'));
        Log::info('Order paid count: ' . Order::where('payment_status', 'paid')->count());
        Log::info('Order paid total: ' . Order::where('payment_status', 'paid')->sum('total_amount'));
        Log::info('Fine paid count: ' . Fine::where('status', 'paid')->count());
        Log::info('Fine paid total: ' . Fine::where('status', 'paid')->sum('amount'));
        Log::info('totalRevenue: ' . $totalRevenue);
        Log::info('monthlyRevenue: ' . $monthlyRevenue);
        Log::info('todayRevenue: ' . $todayRevenue);
        Log::info('totalFinesPaid: ' . $totalFinesPaid);
        Log::info('=== END DEBUG ===');
        
        // Thống kê doanh thu theo tháng (12 tháng gần nhất)
        $monthlyRevenueStats = [];
        $validBorrowStatuses = ['Dang muon', 'Da tra', 'hoan_tat_don_hang', 'da_muon_dang_luu_hanh'];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            // Ưu tiên dùng BorrowPayment
            $revenueFromBorrows = BorrowPayment::where('payment_status', 'success')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('amount');
            
            // Fallback nếu không có BorrowPayment
            if ($revenueFromBorrows == 0) {
                $revenueFromBorrows = Borrow::where(function($q) use ($validBorrowStatuses) {
                        $q->whereIn('trang_thai', $validBorrowStatuses)
                          ->orWhereIn('trang_thai_chi_tiet', $validBorrowStatuses);
                    })
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->sum('tong_tien');
            }
            
            $revenueFromOrders = Order::where('payment_status', 'paid')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->sum('total_amount');
            
            // Cộng thêm tiền phạt đã thu trong tháng
            $finesRevenue = Fine::where('status', 'paid')
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->sum('amount');
            
            $monthlyRevenueStats[] = [
                'label' => 'T' . $date->month,
                'month' => $date->month,
                'year' => $year,
                'revenue' => $revenueFromBorrows + $revenueFromOrders + $finesRevenue
            ];
        }
        
        // Thống kê mượn sách theo tháng (12 tháng gần nhất)
        // Hiển thị số phiếu mượn đang ở trạng thái "Dang muon" được tạo trong từng tháng
        $monthlyBorrowStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            // Đếm số phiếu mượn được tạo trong tháng này và VẪN đang ở trạng thái "Dang muon"
            $borrowCount = Borrow::whereYear('ngay_muon', $year)
                ->whereMonth('ngay_muon', $month)
                ->where('trang_thai', 'Dang muon')
                ->count();
            
            $monthlyBorrowStats[] = [
                'label' => 'T' . $date->month,
                'month' => $date->month,
                'year' => $year,
                'count' => $borrowCount
            ];
        }
        
        // Lấy hoạt động gần đây
        $recentActivities = $this->getRecentActivities();
        
        return view('admin.dashboard', compact(
            'totalBooks',
            'totalBorrowingReaders',
            'totalReservations',
            'categoryStats',
            'totalCategories',
            'totalRevenue',
            'monthlyRevenue',
            'todayRevenue',
            'revenueChangePercent',
            'totalRevenueFromBorrows',
            'monthlyRevenueFromBorrows',
            'todayRevenueFromBorrows',
            'totalRevenueFromOrders',
            'monthlyRevenueFromOrders',
            'todayRevenueFromOrders',
            'totalFinesPaid',
            'totalFinesPending',
            'totalFinesOverdue',
            'monthlyRevenueStats',
            'monthlyBorrowStats',
            'recentActivities'
        ));
    }
    
    /**
     * Lấy danh sách hoạt động gần đây
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        // 1. Sách mới được thêm vào hệ thống
        $newBook = Book::orderBy('created_at', 'desc')->first();
        if ($newBook) {
            $activities[] = [
                'type' => 'book_added',
                'icon' => 'fas fa-book',
                'icon_color' => 'var(--primary-color)',
                'bg_color' => 'rgba(0, 255, 153, 0.2)',
                'text_color' => 'var(--primary-color)',
                'title' => 'Sách mới đã được thêm vào hệ thống',
                'description' => $newBook->ten_sach,
                'time' => $newBook->created_at,
                'action_text' => 'Thêm sách',
                'action_url' => route('admin.books.index'),
            ];
        }
        
        // 2. Độc giả mới đã đăng ký
        $newReader = Reader::orderBy('created_at', 'desc')->first();
        if ($newReader) {
            $activities[] = [
                'type' => 'reader_registered',
                'icon' => 'fas fa-user-plus',
                'icon_color' => '#28a745',
                'bg_color' => 'rgba(40, 167, 69, 0.2)',
                'text_color' => '#28a745',
                'title' => 'Độc giả mới đã đăng ký',
                'description' => $newReader->ho_ten,
                'time' => $newReader->created_at,
                'action_text' => 'Đăng ký',
                'action_url' => route('admin.readers.create'),
            ];
        }
        
        // 3. Sách đã được trả về thư viện
        $returnedItem = BorrowItem::where(function($query) {
                $query->whereNotNull('ngay_tra_thuc_te')
                      ->orWhere('trang_thai', '!=', 'Dang muon');
            })
            ->orderBy('updated_at', 'desc')
            ->with('book')
            ->first();
        if ($returnedItem && $returnedItem->book) {
            $activities[] = [
                'type' => 'book_returned',
                'icon' => 'fas fa-exchange-alt',
                'icon_color' => 'var(--secondary-color)',
                'bg_color' => 'rgba(255, 221, 0, 0.2)',
                'text_color' => 'var(--secondary-color)',
                'title' => 'Sách đã được trả về thư viện',
                'description' => $returnedItem->book->ten_sach,
                'time' => $returnedItem->updated_at,
                'action_text' => 'Trả sách',
                'action_url' => route('admin.borrows.index'),
            ];
        }
        
        // 4. Phát hiện sách quá hạn
        $overdueItem = BorrowItem::where('trang_thai', 'Dang muon')
            ->where('ngay_hen_tra', '<', now())
            ->orderBy('ngay_hen_tra', 'asc')
            ->with('book')
            ->first();
        if ($overdueItem && $overdueItem->book) {
            $activities[] = [
                'type' => 'book_overdue',
                'icon' => 'fas fa-exclamation-circle',
                'icon_color' => '#ff6b6b',
                'bg_color' => 'rgba(255, 107, 107, 0.2)',
                'text_color' => '#ff6b6b',
                'title' => 'Phát hiện sách quá hạn',
                'description' => $overdueItem->book->ten_sach,
                'time' => $overdueItem->ngay_hen_tra,
                'action_text' => 'Cảnh báo',
                'action_url' => route('admin.borrows.index'),
            ];
        }
        
        // Sắp xếp theo thời gian (mới nhất trước)
        usort($activities, function($a, $b) {
            return $b['time']->timestamp <=> $a['time']->timestamp;
        });
        
        // Chỉ lấy 4 hoạt động gần nhất
        return array_slice($activities, 0, 4);
    }
}

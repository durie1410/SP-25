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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $overviewStats = $this->getOverviewStats();
        $recentOrders = $this->getRecentOrders();
        $newUsers = $this->getNewUsers();

        $upcomingDueReturns = BorrowItem::with(['borrow.reader', 'book'])
            ->where('trang_thai', 'Dang muon')
            ->whereDate('ngay_hen_tra', '>=', Carbon::today())
            ->whereDate('ngay_hen_tra', '<=', Carbon::today()->copy()->addDays(3))
            ->orderBy('ngay_hen_tra')
            ->limit(5)
            ->get();

        $lowStockBooks = Book::query()
            ->where('so_luong', '<=', 5)
            ->orderBy('so_luong')
            ->orderBy('ten_sach')
            ->limit(5)
            ->get(['id', 'ten_sach', 'so_luong']);

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

        // === THỐNG KÊ SÁCH ===
        $damagedBooksCount = Book::where('trang_thai', 'damaged')->count();
        $damagedBooks = Book::where('trang_thai', 'damaged')
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $outOfStockBooksCount = Book::where('so_luong', '<=', 0)->count();
        $outOfStockBooks = Book::where('so_luong', '<=', 0)
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // === TOP SÁCH MƯỢN NHIỀU NHẤT ===
        $topBorrowedBooks = BorrowItem::select('book_id', DB::raw('COUNT(*) as borrow_count'))
            ->whereNotNull('book_id')
            ->with('book:id,ten_sach,hinh_anh')
            ->groupBy('book_id')
            ->orderByDesc('borrow_count')
            ->limit(5)
            ->get();

        // === THỐNG KÊ LƯỢT MƯỢN ===
        $borrowToday = Borrow::whereDate('created_at', Carbon::today())->count();
        $borrowMonth = Borrow::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        $borrowYear = Borrow::whereYear('created_at', Carbon::now()->year)->count();

        return view('admin.dashboard', array_merge(compact(
            'overviewStats',
            'recentOrders',
            'newUsers',
            'upcomingDueReturns',
            'lowStockBooks',
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
            'recentActivities',
            'damagedBooksCount',
            'damagedBooks',
            'outOfStockBooksCount',
            'outOfStockBooks',
            'topBorrowedBooks',
            'borrowToday',
            'borrowMonth',
            'borrowYear',
        ), [
            'borrowStats' => [
                'today' => $borrowToday,
                'month' => $borrowMonth,
                'year' => $borrowYear,
            ],
        ]));
    }

    public function getOverviewStats(): array
    {
        if (!$this->shouldUseOrderSource()) {
            $borrowDateSql = 'COALESCE(ngay_muon, created_at)';

            $borrowAgg = Borrow::query()
                ->selectRaw('COUNT(*) as total_orders')
                ->selectRaw('COALESCE(SUM(CASE WHEN (trang_thai = "Da tra" OR trang_thai_chi_tiet IN ("' . Borrow::STATUS_DA_NHAN_VA_KIEM_TRA . '", "' . Borrow::STATUS_HOAN_TAT_DON_HANG . '")) THEN tong_tien ELSE 0 END), 0) as total_revenue')
                ->selectRaw('SUM(CASE WHEN DATE(' . $borrowDateSql . ') = CURDATE() THEN 1 ELSE 0 END) as orders_today')
                ->selectRaw('SUM(CASE WHEN YEAR(' . $borrowDateSql . ') = YEAR(CURDATE()) AND MONTH(' . $borrowDateSql . ') = MONTH(CURDATE()) THEN 1 ELSE 0 END) as orders_this_month')
                ->first();

            return [
                'total_users' => User::count(),
                'total_books' => Book::count(),
                'total_orders' => (int) ($borrowAgg->total_orders ?? 0),
                'total_revenue' => (float) ($borrowAgg->total_revenue ?? 0),
                'orders_today' => (int) ($borrowAgg->orders_today ?? 0),
                'orders_this_month' => (int) ($borrowAgg->orders_this_month ?? 0),
            ];
        }

        $orderAgg = Order::query()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END), 0) as total_revenue')
            ->selectRaw('SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as orders_today')
            ->selectRaw('SUM(CASE WHEN YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as orders_this_month')
            ->first();

        return [
            'total_users' => User::count(),
            'total_books' => Book::count(),
            'total_orders' => (int) ($orderAgg->total_orders ?? 0),
            'total_revenue' => (float) ($orderAgg->total_revenue ?? 0),
            'orders_today' => (int) ($orderAgg->orders_today ?? 0),
            'orders_this_month' => (int) ($orderAgg->orders_this_month ?? 0),
        ];
    }

    public function getRecentOrders()
    {
        if (!$this->shouldUseOrderSource()) {
            return Borrow::query()
                ->select([
                    'id',
                    DB::raw('borrow_code as order_number'),
                    DB::raw('ten_nguoi_muon as customer_name'),
                    DB::raw('tong_tien as total_amount'),
                    'ngay_muon',
                    'created_at',
                ])
                ->orderByRaw('COALESCE(ngay_muon, created_at) DESC')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(function ($borrow) {
                    $displayDate = $borrow->ngay_muon ?: $borrow->created_at;
                    $borrow->created_at = $displayDate ? Carbon::parse($displayDate) : $borrow->created_at;
                    return $borrow;
                });
        }

        return Order::query()
            ->with(['user:id,name,email'])
            ->select(['id', 'order_number', 'user_id', 'customer_name', 'total_amount', 'status', 'payment_status', 'created_at'])
            ->latest('created_at')
            ->limit(5)
            ->get();
    }

    public function getNewUsers()
    {
        return User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->latest('created_at')
            ->limit(5)
            ->get();
    }

    private function shouldUseOrderSource(): bool
    {
        return Order::query()->exists();
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
    public function getStats()
{
    return [
        'totalBooks' => 0,
        'remaining' => 0,
        'borrowed' => 0,
        'totalImported' => 0,
        'borrowToday' => 0,
        'borrowMonth' => 0,
        'returnToday' => 0,
        'returnMonth' => 0,
    ];
}
}

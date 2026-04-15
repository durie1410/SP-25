<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Models\BorrowItem;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate, $filter] = $this->resolveDateRange($request);
        $useOrderSource = $this->shouldUseOrderSource();

        $revenueStats = $this->getRevenueStats($startDate, $endDate, $useOrderSource);
        $orderStats = $this->getOrderStats($startDate, $endDate, $useOrderSource);
        $topBooks = $this->getTopBooks($startDate, $endDate);
        $topUsers = $this->getTopUsers($startDate, $endDate);
        $monthComparison = $this->getMonthComparison($useOrderSource);

        return view('admin.statistics.index', [
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dataSourceLabel' => $useOrderSource ? 'orders' : 'borrows',
            'revenueStats' => $revenueStats,
            'orderStats' => $orderStats,
            'topBooks' => $topBooks,
            'topUsers' => $topUsers,
            'monthComparison' => $monthComparison,
            'lineChart' => [
                'labels' => array_keys($revenueStats['daily_revenue']),
                'values' => array_values($revenueStats['daily_revenue']),
            ],
            'barChart' => [
                'labels' => array_keys($orderStats['monthly_orders']),
                'values' => array_values($orderStats['monthly_orders']),
            ],
            'pieChart' => [
                'labels' => $topBooks->pluck('ten_sach')->toArray(),
                'values' => $topBooks->pluck('rent_count')->map(fn ($v) => (int) $v)->toArray(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $format = strtolower((string) $request->input('format', 'csv'));
        $useOrderSource = $this->shouldUseOrderSource();

        $revenueStats = $this->getRevenueStats($startDate, $endDate, $useOrderSource);
        $orderStats = $this->getOrderStats($startDate, $endDate, $useOrderSource);
        $topBooks = $this->getTopBooks($startDate, $endDate, 20);
        $topUsers = $this->getTopUsers($startDate, $endDate, 20);

        $filename = 'statistics_' . now()->format('Ymd_His') . ($format === 'excel' ? '.xls' : '.csv');
        $headers = [
            'Content-Type' => $format === 'excel' ? 'application/vnd.ms-excel; charset=UTF-8' : 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($revenueStats, $orderStats, $topBooks, $topUsers, $format) {
            $delimiter = $format === 'excel' ? "\t" : ',';
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for Excel and Vietnamese text support
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['THONG KE TONG HOP'], $delimiter);
            fputcsv($output, ['Doanh thu theo ngay'], $delimiter);
            foreach ($revenueStats['daily_revenue'] as $day => $value) {
                fputcsv($output, [$day, $value], $delimiter);
            }

            fputcsv($output, [], $delimiter);
            fputcsv($output, ['So don theo thang'], $delimiter);
            foreach ($orderStats['monthly_orders'] as $month => $value) {
                fputcsv($output, [$month, $value], $delimiter);
            }

            fputcsv($output, [], $delimiter);
            fputcsv($output, ['Top sach duoc thue'], $delimiter);
            fputcsv($output, ['Ten sach', 'So luot thue'], $delimiter);
            foreach ($topBooks as $book) {
                fputcsv($output, [$book->ten_sach, (int) $book->rent_count], $delimiter);
            }

            fputcsv($output, [], $delimiter);
            fputcsv($output, ['Top nguoi dung thue'], $delimiter);
            fputcsv($output, ['Nguoi dung', 'So don thue'], $delimiter);
            foreach ($topUsers as $user) {
                fputcsv($output, [$user->ho_ten, (int) $user->rent_count], $delimiter);
            }

            fclose($output);
        }, 200, $headers);
    }

    public function getRevenueStats(Carbon $startDate, Carbon $endDate, bool $useOrderSource = true): array
    {
        if ($useOrderSource) {
            $dailyRows = Order::query()
                ->selectRaw('DATE(created_at) as stat_date, SUM(total_amount) as total')
                ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->where(function ($query) {
                    $query->whereIn('status', ['delivered', 'completed'])
                        ->orWhereIn('payment_status', ['paid', 'completed']);
                })
                ->groupBy('stat_date')
                ->orderBy('stat_date')
                ->get()
                ->keyBy('stat_date');
        } else {
            $borrowDateSql = $this->borrowDateSql();
            $dailyRows = Borrow::query()
                ->selectRaw('DATE(' . $borrowDateSql . ') as stat_date, SUM(tong_tien) as total')
                ->whereBetween(DB::raw($borrowDateSql), [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->groupBy('stat_date')
                ->orderBy('stat_date')
                ->get()
                ->keyBy('stat_date');
        }

        $dailyRevenue = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $key = $cursor->toDateString();
            $dailyRevenue[$key] = isset($dailyRows[$key]) ? (float) $dailyRows[$key]->total : 0;
            $cursor->addDay();
        }

        if ($useOrderSource) {
            $todayRevenue = (float) Order::query()
                ->whereDate('created_at', now()->toDateString())
                ->where(function ($query) {
                    $query->whereIn('status', ['delivered', 'completed'])
                        ->orWhereIn('payment_status', ['paid', 'completed']);
                })
                ->sum('total_amount');

            $monthRevenue = (float) Order::query()
                ->whereBetween('created_at', [now()->copy()->startOfMonth(), now()->copy()->endOfMonth()])
                ->where(function ($query) {
                    $query->whereIn('status', ['delivered', 'completed'])
                        ->orWhereIn('payment_status', ['paid', 'completed']);
                })
                ->sum('total_amount');

            $yearRevenue = (float) Order::query()
                ->whereYear('created_at', now()->year)
                ->where(function ($query) {
                    $query->whereIn('status', ['delivered', 'completed'])
                        ->orWhereIn('payment_status', ['paid', 'completed']);
                })
                ->sum('total_amount');
        } else {
            $borrowDateSql = $this->borrowDateSql();
            $todayRevenue = (float) Borrow::query()
                ->whereDate(DB::raw($borrowDateSql), now()->toDateString())
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->sum('tong_tien');

            $monthRevenue = (float) Borrow::query()
                ->whereBetween(DB::raw($borrowDateSql), [now()->copy()->startOfMonth(), now()->copy()->endOfMonth()])
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->sum('tong_tien');

            $yearRevenue = (float) Borrow::query()
                ->whereYear(DB::raw($borrowDateSql), now()->year)
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->sum('tong_tien');
        }

        return [
            'today' => $todayRevenue,
            'this_month' => $monthRevenue,
            'this_year' => $yearRevenue,
            'range_total' => (float) array_sum($dailyRevenue),
            'daily_revenue' => $dailyRevenue,
        ];
    }

    public function getOrderStats(Carbon $startDate, Carbon $endDate, bool $useOrderSource = true): array
    {
        if ($useOrderSource) {
            $baseQuery = Order::query()
                ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

            $totalOrders = (clone $baseQuery)->count();
            $completedOrders = (clone $baseQuery)->whereIn('status', ['delivered', 'completed'])->count();
            $cancelledOrders = (clone $baseQuery)->whereIn('status', ['cancelled', 'canceled'])->count();

            $monthlyRows = Order::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
                ->whereBetween('created_at', [now()->copy()->subMonths(11)->startOfMonth(), now()->copy()->endOfMonth()])
                ->groupBy('ym')
                ->orderBy('ym')
                ->get()
                ->keyBy('ym');
        } else {
            $borrowDateSql = $this->borrowDateSql();
            $baseQuery = Borrow::query()
                ->whereBetween(DB::raw($borrowDateSql), [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);

            $totalOrders = (clone $baseQuery)->count();
            $completedOrders = (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->count();
            $cancelledOrders = (clone $baseQuery)
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da huy')
                        ->orWhere('trang_thai_chi_tiet', Borrow::STATUS_GIAO_HANG_THAT_BAI);
                })
                ->count();

            $monthlyRows = Borrow::query()
                ->selectRaw("DATE_FORMAT(" . $borrowDateSql . ", '%Y-%m') as ym, COUNT(*) as total")
                ->whereBetween(DB::raw($borrowDateSql), [now()->copy()->subMonths(11)->startOfMonth(), now()->copy()->endOfMonth()])
                ->groupBy('ym')
                ->orderBy('ym')
                ->get()
                ->keyBy('ym');
        }

        $monthlyOrders = [];
        $monthCursor = now()->copy()->subMonths(11)->startOfMonth();
        $monthEnd = now()->copy()->endOfMonth();
        while ($monthCursor->lte($monthEnd)) {
            $key = $monthCursor->format('Y-m');
            $monthlyOrders[$key] = isset($monthlyRows[$key]) ? (int) $monthlyRows[$key]->total : 0;
            $monthCursor->addMonth();
        }

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'completion_rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0,
            'cancel_rate' => $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 2) : 0,
            'monthly_orders' => $monthlyOrders,
        ];
    }

    public function getTopBooks(Carbon $startDate, Carbon $endDate, int $limit = 10)
    {
        $borrowDateSql = $this->borrowDateSql('borrows.');

        return BorrowItem::query()
            ->join('borrows', 'borrows.id', '=', 'borrow_items.borrow_id')
            ->join('books', 'books.id', '=', 'borrow_items.book_id')
            ->whereBetween(DB::raw($borrowDateSql), [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->select([
                'books.id',
                'books.ten_sach',
                DB::raw('COUNT(borrow_items.id) as rent_count'),
            ])
            ->groupBy('books.id', 'books.ten_sach')
            ->orderByDesc('rent_count')
            ->limit($limit)
            ->get();
    }

    public function getTopUsers(Carbon $startDate, Carbon $endDate, int $limit = 10)
    {
        $borrowDateSql = $this->borrowDateSql('borrows.');

        return Borrow::query()
            ->join('readers', 'readers.id', '=', 'borrows.reader_id')
            ->whereBetween(DB::raw($borrowDateSql), [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->select([
                'readers.id',
                'readers.ho_ten',
                DB::raw('COUNT(borrows.id) as rent_count'),
            ])
            ->groupBy('readers.id', 'readers.ho_ten')
            ->orderByDesc('rent_count')
            ->limit($limit)
            ->get();
    }

    protected function getMonthComparison(bool $useOrderSource = true): array
    {
        $thisMonthStart = now()->copy()->startOfMonth();
        $thisMonthEnd = now()->copy()->endOfMonth();
        $lastMonthStart = now()->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->copy()->subMonth()->endOfMonth();

        if ($useOrderSource) {
            $thisMonthRevenue = (float) Order::query()
                ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
                ->where(function ($query) {
                    $query->whereIn('status', ['delivered', 'completed'])
                        ->orWhereIn('payment_status', ['paid', 'completed']);
                })
                ->sum('total_amount');

            $lastMonthRevenue = (float) Order::query()
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->where(function ($query) {
                    $query->whereIn('status', ['delivered', 'completed'])
                        ->orWhereIn('payment_status', ['paid', 'completed']);
                })
                ->sum('total_amount');

            $thisMonthOrders = (int) Order::query()
                ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])
                ->count();

            $lastMonthOrders = (int) Order::query()
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->count();
        } else {
            $borrowDateSql = $this->borrowDateSql();
            $thisMonthRevenue = (float) Borrow::query()
                ->whereBetween(DB::raw($borrowDateSql), [$thisMonthStart, $thisMonthEnd])
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->sum('tong_tien');

            $lastMonthRevenue = (float) Borrow::query()
                ->whereBetween(DB::raw($borrowDateSql), [$lastMonthStart, $lastMonthEnd])
                ->where(function ($query) {
                    $query->where('trang_thai', 'Da tra')
                        ->orWhereIn('trang_thai_chi_tiet', [
                            Borrow::STATUS_DA_NHAN_VA_KIEM_TRA,
                            Borrow::STATUS_HOAN_TAT_DON_HANG,
                        ]);
                })
                ->sum('tong_tien');

            $thisMonthOrders = (int) Borrow::query()
                ->whereBetween(DB::raw($borrowDateSql), [$thisMonthStart, $thisMonthEnd])
                ->count();

            $lastMonthOrders = (int) Borrow::query()
                ->whereBetween(DB::raw($borrowDateSql), [$lastMonthStart, $lastMonthEnd])
                ->count();
        }

        return [
            'this_month_revenue' => $thisMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'revenue_change_percent' => $this->calculateChangePercent($thisMonthRevenue, $lastMonthRevenue),
            'this_month_orders' => $thisMonthOrders,
            'last_month_orders' => $lastMonthOrders,
            'orders_change_percent' => $this->calculateChangePercent($thisMonthOrders, $lastMonthOrders),
        ];
    }

    protected function calculateChangePercent(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    protected function resolveDateRange(Request $request): array
    {
        $filter = $request->input('filter', '30days');
        $now = now();

        switch ($filter) {
            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case '30days':
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'custom':
                $startDate = $request->filled('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : $now->copy()->startOfDay();
                $endDate = $request->filled('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : $now->copy()->endOfDay();
                break;
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $filter = 'today';
                break;
            default:
                // Fallback to 30-day window to avoid empty-looking dashboard when today's data is zero
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $filter = '30days';
                break;
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate, $filter];
    }

    protected function shouldUseOrderSource(): bool
    {
        return Order::query()->exists();
    }

    protected function borrowDateSql(string $prefix = ''): string
    {
        return 'COALESCE(' . $prefix . 'ngay_muon, ' . $prefix . 'created_at)';
    }
}

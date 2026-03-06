<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Book;
use App\Models\Category;
use App\Models\Reader;
use App\Models\Borrow;
use App\Models\BorrowItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Thống kê tổng quan
        $totalBooks = Book::count();
        $totalReaders = Reader::count();
        $totalBorrows = Borrow::count();
        $activeBorrows = Borrow::where('trang_thai', 'Dang muon')->count();
        $overdueBorrows = Borrow::where('trang_thai', 'Dang muon')
            ->whereHas('items', function($q) {
                $q->where('trang_thai', 'Dang muon')
                  ->where('ngay_hen_tra', '<', now()->toDateString());
            })
            ->count();

        // Thống kê theo tháng
        $monthlyStats = $this->getMonthlyStats();
        
        // Top sách được mượn nhiều nhất
        $topBooks = BorrowItem::with('book')
            ->selectRaw('book_id, count(*) as borrow_count')
            ->groupBy('book_id')
            ->orderBy('borrow_count', 'desc')
            ->limit(10)
            ->get();

        // Top độc giả mượn nhiều nhất
        $topReaders = Borrow::with('reader')
            ->selectRaw('reader_id, count(*) as borrow_count')
            ->groupBy('reader_id')
            ->orderBy('borrow_count', 'desc')
            ->limit(10)
            ->get();

        // Thống kê theo thể loại
        $categoryStats = Category::withCount('books')->get();

        return view('admin.reports.index', compact(
            'totalBooks',
            'totalReaders', 
            'totalBorrows',
            'activeBorrows',
            'overdueBorrows',
            'monthlyStats',
            'topBooks',
            'topReaders',
            'categoryStats'
        ));
    }

    public function borrowsReport(Request $request)
    {
        $query = Borrow::with(['reader', 'book', 'librarian']);

        // Lọc theo khoảng thời gian
        if ($request->filled('from_date')) {
            $query->where('ngay_muon', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('ngay_muon', '<=', $request->to_date);
        }

        // Lọc theo trạng thái
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $borrows = $query->orderBy('ngay_muon', 'desc')->paginate(20);

        return view('admin.reports.borrows', compact('borrows'));
    }

    public function readersReport(Request $request)
    {
        $query = Reader::withCount('borrows');

        // Lọc theo trạng thái
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        // Lọc theo giới tính
        if ($request->filled('gioi_tinh')) {
            $query->where('gioi_tinh', $request->gioi_tinh);
        }

        $readers = $query->orderBy('borrows_count', 'desc')->paginate(20);

        return view('admin.reports.readers', compact('readers'));
    }

    public function booksReport(Request $request)
    {
        $query = Book::with(['category'])->withCount('borrows');

        // Lọc theo thể loại
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Lọc theo năm xuất bản
        if ($request->filled('year')) {
            $query->where('nam_xuat_ban', $request->year);
        }

        $books = $query->orderBy('borrows_count', 'desc')->paginate(20);
        $categories = Category::all();

        return view('admin.reports.books', compact('books', 'categories'));
    }

    private function getMonthlyStats()
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'borrows' => Borrow::whereYear('ngay_muon', $date->year)
                    ->whereMonth('ngay_muon', $date->month)
                    ->count(),
                'returns' => BorrowItem::whereYear('ngay_tra_thuc_te', $date->year)
                    ->whereMonth('ngay_tra_thuc_te', $date->month)
                    ->whereNotNull('ngay_tra_thuc_te')
                    ->count(),
            ];
        }
        return $months;
    }

    public function exportCSV()
{
    $fileName = 'borrow_report.csv';

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
    ];

    $callback = function () {

        $file = fopen('php://output', 'w');

        // Header
        fputcsv($file, [
            'Ma muon',
            'Doc gia',
            'Ngay muon',
            'Trang thai'
        ]);

        $borrows = Borrow::with('reader')->get();

        foreach ($borrows as $borrow) {
            fputcsv($file, [
                $borrow->id,
                $borrow->reader->ten ?? '',
                $borrow->ngay_muon,
                $borrow->trang_thai
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
public function exportPDF()
{
    $borrows = Borrow::with('reader')->get();

    $pdf = Pdf::loadView('admin.reports.pdf', compact('borrows'));

    return $pdf->download('borrow_report.pdf');
}
}
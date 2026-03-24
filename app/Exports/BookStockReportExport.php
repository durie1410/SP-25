<?php

namespace App\Exports;

use App\Models\Inventory;
use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class BookStockReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function collection()
    {
        // Lấy tất cả sách từ bảng books (quản lý sách)
        $allBooks = Book::orderBy('ten_sach')->get()->keyBy('id');

        // Lấy thống kê từ inventories (kho) theo từng book_id
        $inventoryStats = Inventory::inStock()
            ->select('book_id', DB::raw('count(*) as total_count'))
            ->selectRaw('sum(case when status = "Dang muon" then 1 else 0 end) as borrowed_count')
            ->selectRaw('sum(case when status = "Thanh ly" then 1 else 0 end) as sold_count')
            ->selectRaw('sum(case when status = "Mat" then 1 else 0 end) as lost_count')
            ->selectRaw('sum(case when (`condition` = "Moi" or `condition` = "Tot") and `condition` != "Hong" and status != "Hong" and status != "Mat" and status != "Thanh ly" and status != "Dang muon" then 1 else 0 end) as new_count')
            ->selectRaw('sum(case when (`condition` = "Cu" or `condition` = "Trung binh") and `condition` != "Hong" and status != "Hong" and status != "Mat" and status != "Thanh ly" and status != "Dang muon" then 1 else 0 end) as old_count')
            ->selectRaw('sum(case when `condition` = "Hong" or status = "Hong" then 1 else 0 end) as damaged_count')
            ->selectRaw('sum(case when (`condition` = "Hong" or status = "Hong") and status not in ("Dang muon", "Thanh ly", "Mat") then 1 else 0 end) as damaged_available_count')
            ->groupBy('book_id')
            ->get()
            ->keyBy('book_id');

        // Kết hợp dữ liệu
        $booksInStock = $allBooks->map(function($book) use ($inventoryStats) {
            $stats = $inventoryStats->get($book->id);

            $total = $stats ? (int)$stats->total_count : 0;
            $borrowed = $stats ? (int)$stats->borrowed_count : 0;
            $sold = $stats ? (int)$stats->sold_count : 0;
            $lost = $stats ? (int)$stats->lost_count : 0;
            $damaged = $stats ? (int)$stats->damaged_count : 0;
            $damagedAvailable = $stats ? (int)$stats->damaged_available_count : 0;

            $available = max(0, $total - $borrowed - $sold - $lost - $damagedAvailable);

            return (object)[
                'book' => $book,
                'total' => $total,
                'available' => $available,
                'borrowed' => $borrowed,
                'sold' => $sold,
                'lost' => $lost,
                'remaining' => max(0, $total - $borrowed),
                'new' => $stats ? (int)$stats->new_count : 0,
                'old' => $stats ? (int)$stats->old_count : 0,
                'damaged' => $damaged,
            ];
        });

        // Thêm sách orphaned
        $orphanedStats = $inventoryStats->filter(function($stat) use ($allBooks) {
            return !$allBooks->has($stat->book_id);
        });

        foreach ($orphanedStats as $stat) {
            $book = Book::find($stat->book_id);
            if ($book) {
                $total = (int)$stat->total_count;
                $borrowed = (int)$stat->borrowed_count;
                $sold = (int)$stat->sold_count;
                $lost = (int)$stat->lost_count;
                $damaged = (int)$stat->damaged_count;
                $damagedAvailable = (int)$stat->damaged_available_count;

                $available = max(0, $total - $borrowed - $sold - $lost - $damagedAvailable);

                $booksInStock->put($book->id, (object)[
                    'book' => $book,
                    'total' => $total,
                    'available' => $available,
                    'borrowed' => $borrowed,
                    'sold' => $sold,
                    'lost' => $lost,
                    'remaining' => max(0, $total - $borrowed),
                    'new' => (int)$stat->new_count,
                    'old' => (int)$stat->old_count,
                    'damaged' => $damaged,
                ]);
            }
        }

        // Lọc, sắp xếp và lấy tất cả (không phân trang)
        return $booksInStock->filter(function($item) {
            return $item->total > 0;
        })->sortBy(function($item) {
            return $item->book->ten_sach ?? '';
        })->values();
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên sách',
            'Tác giả',
            'Tổng số lượng',
            'Còn lại',
            'Có sẵn',
            'Sách mới',
            'Sách cũ',
            'Sách hỏng',
            'Sách mất',
            'Đã mượn',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $item->book->ten_sach ?? 'N/A',
            $item->book->tac_gia ?? 'N/A',
            $item->total,
            $item->remaining,
            $item->available,
            $item->new ?? 0,
            $item->old ?? 0,
            $item->damaged ?? 0,
            $item->lost ?? 0,
            $item->borrowed,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E5E7EB']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 35,
            'C' => 25,
            'D' => 15,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 12,
            'K' => 12,
        ];
    }
}

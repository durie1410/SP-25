<?php

namespace App\Exports;

use App\Http\Controllers\DashboardController;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\InventoryReceipt;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class ReportExport implements FromArray, ShouldAutoSize, WithStyles
{
    //   public function array(): array
    // {
    //     $stats = app(\App\Http\Controllers\DashboardController::class)->getStats();

    //     return [
    //         ['BÁO CÁO KHO THƯ VIỆN'],
    //         ['THỐNG KÊ SÁCH'],
    //         ['Tổng sách trong kho', $stats['totalBooks'] ?? 0],
    //         ['Còn lại trong kho', $stats['remaining'] ?? 0],
    //         ['Đã cho mượn', $stats['borrowed'] ?? 0],
    //         ['Tổng đã nhập', $stats['totalImported'] ?? 0],
    //         [],
    //         ['THỐNG KÊ HOẠT ĐỘNG'],
    //         ['Mượn hôm nay', $stats['borrowToday'] ?? 0],
    //         ['Mượn tháng này', $stats['borrowMonth'] ?? 0],
    //         ['Trả hôm nay', $stats['returnToday'] ?? 0],
    //         ['Trả tháng này', $stats['returnMonth'] ?? 0],
    //     ];
    // }
    public function array(): array
    {
        $stats = app(\App\Http\Controllers\DashboardController::class)->getStats();

        $data = [

            ['I. BÁO CÁO KHO THƯ VIỆN'],
            ['THỐNG KÊ SÁCH'],

            ['Tổng sách trong kho', $stats['totalBooks']],
            ['Còn lại trong kho', $stats['remaining']],
            ['Đã cho mượn', $stats['borrowed']],
            ['Tổng đã nhập', $stats['totalImported']],

            [],

            ['THỐNG KÊ HOẠT ĐỘNG'],

            ['Mượn hôm nay', $stats['borrowToday']],
            ['Mượn tháng này', $stats['borrowMonth']],
            ['Trả hôm nay', $stats['returnToday']],
            ['Trả tháng này', $stats['returnMonth']],

            [],

            ['II. CHI TIẾT SỐ LƯỢNG SÁCH'],
            ['STT', 'Tên sách', 'Tác giả', 'Tổng số lượng', 'Còn lại', 'Có sẵn', 'Sách mới', 'Sách cũ', 'Sách hỏng', 'Sách mất', 'Đã mượn'],



        ];

        $stats = app(\App\Http\Controllers\DashboardController::class)->getStats();

        $books = \App\Models\Book::orderBy('ten_sach', 'asc')->get();
        $stt = 1;

        foreach ($books as $book) {

            $borrowed = \App\Models\BorrowItem::where('book_id', $book->id)
                ->where('trang_thai', '!=', 'Đã trả')
                ->count();

            $remaining = $book->so_luong - $borrowed;

            $data[] = [
                $stt++,
                $book->ten_sach,
                $book->tac_gia,
                $book->so_luong,
                $remaining,
                $remaining,
                $remaining,
                0,
                0,
                0,
                $borrowed
            ];
        }

        if (!empty($data)) {
            $data[] = ['', '', '', '', '', '', '', '', ''];
            $data[] = ['', '', '', '', '', '', '', '', ''];
        }
        $data[] = ['III. DANH SÁCH PHIẾU NHẬP KHO'];

        $data[] = [
            'SỐ PHIẾU',
            'NGÀY NHẬP',
            'SÁCH',
            'SỐ LƯỢNG NHẬP',
            'ĐƠN GIÁ',
            'THÀNH TIỀN',
            'NGƯỜI NHẬP',
            'NGƯỜI PHÊ DUYỆT',
            'NHÀ CUNG CẤP',
        ];

        $items = \App\Models\InventoryReceiptItem::with('receipt', 'book')->get();
        $receipts = InventoryReceipt::with([
            'items.book',
            'receiver',
            'approver',
        ])->get();

        foreach ($receipts as $receipt) {
            $first = true;

            if ($receipt->items->isEmpty()) {
                $data[] = [
                    $receipt->receipt_number,
                    $receipt->receipt_date
                        ? \Carbon\Carbon::parse($receipt->receipt_date)->format('d/m/Y')
                        : '',
                    $receipt->book->ten_sach ?? 'N/V',
                    $receipt->quantity,
                    number_format($receipt->unit_price ?? 0, 0, ',', '.') . ' VND',
                    number_format(($receipt->quantity * ($receipt->unit_price ?? 0)), 0, ',', '.') . ' VND',
                    $receipt->receiver->name ?? '',
                    $receipt->approver->name ?? '',
                    $receipt->supplier_id ?? 'N/A'
                ];
                continue;
            }
        }

        $data[] = ['', '', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', '', ''];

        $stats = app(\App\Http\Controllers\DashboardController::class)->getStats();
        $data[] = ['IV. DANH SÁCH NGƯỜI ĐANG MƯỢN SÁCH TỪ KHO'];

        $data[] = [
            'STT',
            'NGƯỜI MƯỢN',
            'SỐ THẺ',
            'SÁCH',
            'NGÀY MƯỢN',
            'HẠN TRẢ',
            'SỐ NGÀY MƯỢN',
            'THỦ THƯ',
            'TRẠNG THÁI'
        ];


        $borrows = \App\Models\BorrowItem::with([
            'borrow.reader',
            'borrow.librarian',
            'book'
        ])->get();

        $stt = 1;

        foreach ($borrows as $item) {

            $borrow = $item->borrow;

            $borrowDate = $borrow && $borrow->borrow_date
                ? \Carbon\Carbon::parse($borrow->borrow_date)->format('d/m/Y')
                : 'N/A';

            $dueDate = $borrow && $borrow->due_date
                ? \Carbon\Carbon::parse($borrow->due_date)->format('d/m/Y')
                : 'N/A';

            $days = ($borrow && $borrow->borrow_date && $borrow->due_date)
                ? \Carbon\Carbon::parse($borrow->borrow_date)->diffInDays($borrow->due_date)
                : 'N/A';

            $status = 'ĐANG MƯỢN';

            if ($borrow && $borrow->due_date) {
                $over = \Carbon\Carbon::now()->diffInDays($borrow->due_date, false);
                if ($over < 0) {
                    $status = 'QUÁ HẠN (' . abs($over) . ' NGÀY)';
                }
            }

            $data[] = [
                $stt++,
                $borrow->reader->name ?? 'N/A',
                $borrow->reader->card_number ?? 'N/A',
                $item->book->ten_sach ?? 'N/A',
                $borrowDate,
                $dueDate,
                $days !== 'N/A' ? $days . ' ngày' : 'N/A',
                $borrow->librarian->name ?? 'N/A',
                $status
            ];
        }
        return $data;
    }
    public function styles(Worksheet $sheet)
    {
        return [

            1 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ]
            ],

            2 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true
                ]
            ],

            7 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true
                ]
            ],

            12 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 16
                ]
            ],

            // ✅ thêm riêng
            '14:62' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // ✅ ghi đè lại: tên sách + tác giả căn trái
            'B14:B62' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],

            'C14:C62' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],

            13 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 12
                ]
            ],

            65 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 16
                ]
            ],

            66 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 12
                ]
            ],
 '67:114' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // ✅ ghi đè lại: tên sách + tác giả căn trái
            'C67:C114' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],

            115 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 16
                ]
            ],

            116 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 12
                ]
            ],

            // ✅ thêm riêng
            '117:157' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // ✅ ghi đè lại: tên sách + tác giả căn trái
            'D117:D157' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ],

        ];
    }
}

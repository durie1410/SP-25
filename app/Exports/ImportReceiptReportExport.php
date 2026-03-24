<?php

namespace App\Exports;

use App\Models\InventoryReceipt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportReceiptReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return InventoryReceipt::with(['book', 'receiver', 'approver'])
            ->where('storage_type', 'Kho')
            ->where('status', 'approved')
            ->orderBy('receipt_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Số phiếu',
            'Ngày nhập',
            'Tên sách',
            'Số lượng nhập',
            'Đơn giá',
            'Thành tiền',
            'Người nhập',
            'Người phê duyệt',
            'Nhà cung cấp',
        ];
    }

    public function map($receipt): array
    {
        return [
            $receipt->receipt_number,
            $receipt->receipt_date ? $receipt->receipt_date->format('d/m/Y') : 'N/A',
            $receipt->book->ten_sach ?? 'N/A',
            $receipt->quantity,
            $receipt->unit_price ? number_format($receipt->unit_price, 0, ',', '.') . ' VNĐ' : 'N/A',
            $receipt->total_price ? number_format($receipt->total_price, 0, ',', '.') . ' VNĐ' : 'N/A',
            $receipt->receiver->name ?? 'N/A',
            $receipt->approver->name ?? 'N/A',
            $receipt->supplier ?? 'N/A',
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
            'A' => 15,
            'B' => 15,
            'C' => 30,
            'D' => 15,
            'E' => 18,
            'F' => 18,
            'G' => 20,
            'H' => 20,
            'I' => 25,
        ];
    }
}

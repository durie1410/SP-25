<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Publisher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Sheet;

class BookImportTemplateExport extends StringValueBinder implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $categories;
    protected $publishers;
    protected $locations;

    public function __construct($categories, $publishers, $locations)
    {
        $this->categories = $categories;
        $this->publishers = $publishers;
        $this->locations = $locations;
    }

    public function collection()
    {
        // Tạo 3 dòng mẫu minh họa để người dùng biết format
        $rows = [];

        // Dòng 1 - ví dụ hoàn chỉnh
        $rows[] = [
            'Tên sách bắt buộc',                           // ten_sach
            '1',                                             // category_id (ID thể loại)
            '',                                              // nha_xuat_ban_id (ID nhà xuất bản, để trống nếu không có)
            'Tên tác giả bắt buộc',                         // tac_gia
            '2024',                                          // nam_xuat_ban
            'Mô tả sách (tùy chọn)',                        // mo_ta
            '85000',                                         // gia (VNĐ, số)
            '1',                                             // so_luong (số lượng nhập kho)
            'Kệ A',                                          // vi_tri
            'active',                                        // trang_thai (active / inactive)
            'binh_thuong',                                   // loai_sach (binh_thuong / quy / tham_khao)
        ];

        // Dòng 2 - ví dụ tối thiểu
        $rows[] = [
            'Tên sách mẫu 2',
            '1',
            '',
            'Tác giả mẫu 2',
            '2023',
            '',
            '0',
            '1',
            '',
            'active',
            'binh_thuong',
        ];

        // Dòng 3 - sách quý
        $rows[] = [
            'Tên sách mẫu 3',
            '2',
            '',
            'Tác giả mẫu 3',
            '2020',
            'Mô tả sách quý',
            '150000',
            '1',
            'Kho đặc biệt',
            'inactive',
            'quy',
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'ten_sach',
            'category_id',
            'nha_xuat_ban_id',
            'tac_gia',
            'nam_xuat_ban',
            'mo_ta',
            'gia',
            'so_luong',
            'vi_tri',
            'trang_thai',
            'loai_sach',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getParent()->getActiveSheet()->getStyle('A1:K3')->applyFromArray([
            'font' => ['name' => 'Arial', 'size' => 11],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Header row style
        $sheet->getParent()->getActiveSheet()->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D6EFD'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Sample rows style (light blue)
        $sheet->getParent()->getActiveSheet()->getStyle('A2:K3')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7F4FF'],
            ],
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,  // ten_sach
            'B' => 14,  // category_id
            'C' => 20,  // nha_xuat_ban_id
            'D' => 25,  // tac_gia
            'E' => 14,  // nam_xuat_ban
            'F' => 40,  // mo_ta
            'G' => 12,  // gia
            'H' => 12,  // so_luong
            'I' => 20,  // vi_tri
            'J' => 14,  // trang_thai
            'K' => 18,  // loai_sach
        ];
    }
}

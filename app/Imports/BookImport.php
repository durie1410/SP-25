<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Inventory;
use App\Models\InventoryReceipt;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class BookImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];

    public function model(array $row)
    {
        try {
            // Validate required fields
            if (empty($row['ten_sach']) || empty($row['tac_gia']) || empty($row['nam_xuat_ban']) || empty($row['category_id'])) {
                $this->errors[] = 'Dong: Thieu truong bat buoc (ten_sach, tac_gia, nam_xuat_ban, category_id)';
                $this->errorCount++;
                return null;
            }

            // Check if category exists
            $category = Category::find($row['category_id']);
            if (!$category) {
                $line = $this->successCount + $this->errorCount + 1;
                $this->errors[] = "Dong {$line}: The loai ID {$row['category_id']} khong ton tai";
                $this->errorCount++;
                return null;
            }

            // Validate publisher if provided
            $publisherId = null;
            if (!empty($row['nha_xuat_ban_id'])) {
                $publisher = Publisher::find($row['nha_xuat_ban_id']);
                if ($publisher) {
                    $publisherId = $publisher->id;
                }
            }

            // Validate year
            $year = (int) $row['nam_xuat_ban'];
            if ($year < 1900 || $year > (date('Y') + 1)) {
                $line = $this->successCount + $this->errorCount + 1;
                $this->errors[] = "Dong {$line}: Nam xuat ban khong hop le ({$year})";
                $this->errorCount++;
                return null;
            }

            // Validate trang_thai
            $validStatuses = ['active', 'inactive'];
            $trangThai = strtolower(trim($row['trang_thai'] ?? 'active'));
            if (!in_array($trangThai, $validStatuses)) {
                $trangThai = 'active';
            }

            // Validate loai_sach
            $validTypes = ['binh_thuong', 'quy', 'tham_khao'];
            $loaiSach = strtolower(trim($row['loai_sach'] ?? 'binh_thuong'));
            if (!in_array($loaiSach, $validTypes)) {
                $loaiSach = 'binh_thuong';
            }

            $soLuong = max(0, (int) ($row['so_luong'] ?? 1));
            $gia = max(0, (float) ($row['gia'] ?? 0));
            $viTri = $row['vi_tri'] ?? null;

            // Create book
            $book = Book::create([
                'ten_sach' => trim($row['ten_sach']),
                'category_id' => $category->id,
                'nha_xuat_ban_id' => $publisherId,
                'tac_gia' => trim($row['tac_gia']),
                'nam_xuat_ban' => $year,
                'hinh_anh' => null,
                'mo_ta' => trim($row['mo_ta'] ?? ''),
                'gia' => $gia,
                'trang_thai' => $trangThai,
                'danh_gia_trung_binh' => 0,
                'so_luong_ban' => 0,
                'so_luot_xem' => 0,
                'so_luong' => $soLuong,
                'is_featured' => false,
                'loai_sach' => $loaiSach,
            ]);

            // Auto-create pending inventory receipt if quantity > 0
            if ($soLuong > 0) {
                $receiptNumber = 'PNK-' . date('Ymd') . '-' . str_pad($book->id, 4, '0', STR_PAD_LEFT);

                InventoryReceipt::create([
                    'book_id' => $book->id,
                    'receipt_number' => $receiptNumber,
                    'receipt_date' => now(),
                    'quantity' => $soLuong,
                    'unit_price' => $gia,
                    'total_price' => $soLuong * $gia,
                    'supplier' => $publisherId ? Publisher::find($publisherId)?->ten_nha_xuat_ban : 'Khong xac dinh',
                    'storage_location' => $viTri ?: 'Ke A',
                    'storage_type' => 'Kho',
                    'status' => 'pending',
                    'notes' => 'Tao tu nhap Excel',
                    'received_by' => auth()->id(),
                ]);
            }

            $this->successCount++;

            return $book;

        } catch (\Exception $e) {
            $line = $this->successCount + $this->errorCount + 1;
            $this->errors[] = "Dong {$line}: " . $e->getMessage();
            $this->errorCount++;
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'ten_sach' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'nha_xuat_ban_id' => 'nullable|integer|exists:publishers,id',
            'tac_gia' => 'required|string|max:255',
            'nam_xuat_ban' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'mo_ta' => 'nullable|string|max:2000',
            'gia' => 'nullable|numeric|min:0',
            'so_luong' => 'nullable|integer|min:0',
            'trang_thai' => 'nullable|in:active,inactive',
            'loai_sach' => 'nullable|in:binh_thuong,quy,tham_khao',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'ten_sach.required' => 'Ten sach la bat buoc.',
            'category_id.required' => 'The loai (category_id) la bat buoc.',
            'category_id.exists' => 'The loai ID khong ton tai trong he thong.',
            'nha_xuat_ban_id.exists' => 'Nha xuat ban ID khong ton tai trong he thong.',
            'tac_gia.required' => 'Tac gia la bat buoc.',
            'nam_xuat_ban.required' => 'Nam xuat ban la bat buoc.',
            'nam_xuat_ban.integer' => 'Nam xuat ban phai la so.',
        ];
    }

    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }
}

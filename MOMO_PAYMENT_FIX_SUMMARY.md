# 🔧 MoMo Payment Fine - FIX Summary

## 🐛 Vấn đề phát hiện
Khi thanh toán phạt trả sách bằng **Momo**, hệ thống **KHÔNG xử lý tình trạng sách** (hỏng nặng, hỏng nhẹ, mất sách, bình thường) giống như thanh toán **tiền mặt**.

### Triệu chứng:
- ❌ Thanh toán Momo thành công nhưng sách không được phân loại
- ❌ Không tạo yêu cầu xóa cho sách hỏng/mất
- ❌ Sách vẫn ở trạng thái "Đang mượn" thay vì "Đã trả" / "Hỏng" / "Mất"

## 🔍 Nguyên nhân gốc

### 1. Session Context Mismatch
- **MoMo Payment Creation**: Session có `pending_return` với tình trạng sách
- **MoMo IPN Callback**: Server-to-server callback, **KHÔNG có session context**
- **Result**: `session('pending_return')` = NULL/rỗng ❌

### 2. Database Gap
Khi tạo MoMo payment:
```php
// TRƯỚC FIX: KHÔNG lưu tinh_trang_sach_cuoi vào DB
BorrowItem: tinh_trang_sach_cuoi = NULL ❌
```

### 3. IPN Processing Failure
MoMo IPN cố lấy từ session (không có):
```php
// Hỏng: Session context không tồn tại trong IPN
$pendingReturn = session('pending_return'); // = NULL
$condition = $itemData['condition'] ?? 'binh_thuong'; // Lấy từ rỗng
```

### 4. Finalize không xử lý
Hàm `finalizeReturnedItemsAfterFinePayment` lấy từ DB:
```php
$condition = $item->tinh_trang_sach_cuoi; // = NULL/trống
// Không phân loại được sách
```

## ✅ FIX Applied

### Sửa 3 vị trí chính:

#### 1️⃣ `payCashByReader` (Online payment)
**File**: `app/Http/Controllers/FinePaymentController.php` (dòng 236-263)

```php
DB::beginTransaction();

// ✓ LƯU tình trạng sách vào DB TRƯỚC khi tạo MoMo payment
if (!empty($returnItemIds)) {
    foreach ($returnItemIds as $itemId) {
        $itemData = $returnItemsData[$itemId] ?? [];
        $condition = $itemData['condition'] ?? 'binh_thuong';
        BorrowItem::where('id', $itemId)->update([
            'tinh_trang_sach_cuoi' => $condition,  // ✓ LƯU VÀO DB
            'updated_at' => now(),
        ]);
    }
}

// Tạo payment records...
```

#### 2️⃣ `createMomoPaymentByReader` 
**File**: `app/Http/Controllers/FinePaymentController.php` (dòng 566-578)

```php
if (isset($result['payUrl'])) {
    // ✓ LƯU tình trạng sách từ session vào DB
    if (!empty($returnItemIds)) {
        foreach ($returnItemIds as $itemId) {
            $itemData = $returnItemsData[$itemId] ?? [];
            $condition = $itemData['condition'] ?? 'binh_thuong';
            BorrowItem::where('id', $itemId)->update([
                'tinh_trang_sach_cuoi' => $condition,  // ✓ LƯU VÀO DB
                'updated_at' => now(),
            ]);
        }
    }
    // Tạo payment records...
}
```

#### 3️⃣ `momoIpn` - Remove session dependency
**File**: `app/Http/Controllers/FinePaymentController.php` (dòng 705-713)

```php
foreach ($returnItemIds as $itemId) {
    $item = $items->get($itemId);
    if (!$item || !$item->borrow) continue;

    // ✓ ĐỌC từ DB (không phụ thuộc session)
    $condition = trim((string) ($item->tinh_trang_sach_cuoi ?? 'binh_thuong'));
    if (empty($condition)) {
        $condition = 'binh_thuong';
    }
    $today = now();
    // ... xử lý tiếp
}
```

## 📊 Luồng So Sánh

### Thanh toán Tiền mặt ✅
```
1. payCash() / payCashByReader()
   └─ Tính phạt từ session data
   └─ LƯỚI tinh_trang_sach_cuoi vào DB
   
2. Cập nhật Fine status = 'paid'

3. finalizeReturnedItemsAfterFinePayment()
   └─ ĐỌC tinh_trang_sach_cuoi từ DB
   └─ Xử lý 4 tình trạng:
      - binh_thuong → Da tra (kho)
      - hong_nhe/hong_nang → Hong (yêu cầu xóa)
      - mat_sach → Mat sach (yêu cầu xóa)
```

### Thanh toán MoMo (TRƯỚC FIX) ❌
```
1. payCashByReader(online)
   └─ Tạo MoMo payment
   └─ KHÔNG lưu tinh_trang_sach_cuoi ❌
   
2. MoMo IPN callback
   └─ Lấy từ session('pending_return') ❌ (rỗng)
   └─ tinh_trang_sach_cuoi = NULL
   
3. finalizeReturnedItemsAfterFinePayment()
   └─ ĐỌC tinh_trang_sach_cuoi = NULL ❌
   └─ KHÔNG xử lý được tình trạng sách
```

### Thanh toán MoMo (SAU FIX) ✅
```
1. payCashByReader(online)
   └─ LƯỮ tinh_trang_sach_cuoi vào DB ✅
   
2. MoMo IPN callback
   └─ ĐỌC từ DB (không phụ thuộc session) ✅
   └─ tinh_trang_sach_cuoi đã có sẵn
   
3. finalizeReturnedItemsAfterFinePayment()
   └─ ĐỌC tinh_trang_sach_cuoi từ DB ✅
   └─ Xử lý 4 tình trạng (giống tiền mặt)
```

## 🎯 Kết quả

### Trước FIX
| Phương thức | Bình thường | Hỏng nhẹ | Hỏng nặng | Mất sách |
|-------------|-----------|---------|---------|--------|
| Tiền mặt | ✅ | ✅ | ✅ | ✅ |
| Momo | ❌ | ❌ | ❌ | ❌ |

### Sau FIX
| Phương thức | Bình thường | Hỏng nhẹ | Hỏng nặng | Mất sách |
|-------------|-----------|---------|---------|--------|
| Tiền mặt | ✅ | ✅ | ✅ | ✅ |
| Momo | ✅ | ✅ | ✅ | ✅ |

## 📝 Hướng dẫn Test

### Test Case 1: Thanh toán Momo - Sách Bình thường
```
1. Tạo độc giả + Mượn sách
2. Chọn "Trả sách" → Chọn "Bình thường"
3. Thanh toán → Chọn "MoMo"
4. Quét QR → Thanh toán thành công
5. Kiểm tra DB:
   ✓ BorrowItem.trang_thai = 'Da tra' (không phải 'Dang muon')
   ✓ Inventory.status = 'Co san' (quay về kho)
```

### Test Case 2: Thanh toán Momo - Sách Hỏng
```
1. Tạo độc giả + Mượn sách
2. Chọn "Trả sách" → Chọn "Hỏng nặng"
3. Thanh toán → Chọn "MoMo"
4. Quét QR → Thanh toán thành công
5. Kiểm tra DB:
   ✓ BorrowItem.trang_thai = 'Hong' (không phải 'Dang muon')
   ✓ Inventory.status = 'Hong'
   ✓ BookDeleteRequest.status = 'pending' (chờ admin duyệt)
```

### Test Case 3: Thanh toán Momo - Sách Mất
```
1. Tạo độc giả + Mượn sách
2. Chọn "Trả sách" → Chọn "Mất sách"
3. Thanh toán → Chọn "MoMo"
4. Quét QR → Thanh toán thành công
5. Kiểm tra DB:
   ✓ BorrowItem.trang_thai = 'Mat sach' (không phải 'Dang muon')
   ✓ Inventory.status = 'Mat'
   ✓ BookDeleteRequest.status = 'pending' (chờ admin duyệt)
```

## 🔍 Kiểm tra Database

### Sau khi thanh toán MoMo thành công:

```sql
-- Kiểm tra BorrowItem
SELECT id, trang_thai, tinh_trang_sach_cuoi, ngay_tra_thuc_te 
FROM borrow_items 
WHERE id IN (12, 13, 14)
ORDER BY updated_at DESC LIMIT 5;

-- Kỳ vọng:
-- ✓ trang_thai = 'Da tra' / 'Hong' / 'Mat sach' (KHÔNG phải 'Dang muon')
-- ✓ tinh_trang_sach_cuoi = 'binh_thuong' / 'hong_nhe' / 'hong_nang' / 'mat_sach'
-- ✓ ngay_tra_thuc_te = TODAY
```

```sql
-- Kiểm tra Inventory
SELECT id, status, storage_type 
FROM inventory 
WHERE id = 123;

-- Kỳ vọng:
-- ✓ status = 'Co san' (nếu bình thường)
-- ✓ status = 'Hong' (nếu hỏng)
-- ✓ status = 'Mat' (nếu mất)
-- ✓ storage_type = 'Kho' (nếu bình thường)
```

```sql
-- Kiểm tra BookDeleteRequest
SELECT id, inventory_id, status, reason 
FROM book_delete_requests 
WHERE status = 'pending' 
ORDER BY created_at DESC LIMIT 5;

-- Kỳ vọng:
-- ✓ Tạo tự động khi sách hỏng/mất
-- ✓ reason = '[BAO HONG] Sách hỏng khi trả. Item #123'
-- ✓ reason = '[BAO MAT] Mất sách khi trả. Item #124'
```

## 📦 Files Modified

- `app/Http/Controllers/FinePaymentController.php`
  - Line 236-263: Lưu tình trạng sách trong `payCashByReader(online)`
  - Line 566-578: Lưu tình trạng sách trong `createMomoPaymentByReader()`
  - Line 705-713: Đọc từ DB trong `momoIpn()` cho `fine_reader`
  - Xóa: Logic lấy từ session (không ổn định)

## ✨ Benefits

- ✅ MoMo payment = Tiền mặt (xử lý giống nhau)
- ✅ Loại bỏ session dependency trong IPN
- ✅ 4 tình trạng sách được xử lý đầy đủ
- ✅ Tự động tạo yêu cầu xóa cho sách hỏng/mất
- ✅ Giảm lỗi phụ thuộc session trong server-to-server callback

---

**Status**: ✅ **FIXED** - Ready for Testing

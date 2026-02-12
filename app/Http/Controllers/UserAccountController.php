<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasableBook;
use App\Models\Borrow;
use App\Models\Book;
use App\Models\Document;
use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserAccountController extends Controller
{
    /**
     * Hiển thị trang thông tin tài khoản
     */
    public function account()
    {
        $user = auth()->user();
        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        $user->load('reader');

        // Kiểm tra và refresh user để đảm bảo có dữ liệu mới nhất
        $user->refresh();

        return view('account', compact('user'));
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function updateAccount(Request $request)
    {
        try {
            $user = auth()->user();

            // Validation rules
            $validationRules = [
                'phone' => 'nullable|string|max:20',
                'province' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'xa' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'so_cccd' => 'required|string|max:20',
                'ngay_sinh' => 'nullable|date|before:today',
                'gioi_tinh' => 'nullable|in:Nam,Nu,Khac',
            ];

            // Chỉ bắt buộc upload ảnh nếu chưa có ảnh
            if (!$user->cccd_image) {
                $validationRules['cccd_image'] = 'required|image|mimes:jpeg,jpg,png,webp|max:2048';
            } else {
                $validationRules['cccd_image'] = 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048';
            }

            $request->validate($validationRules, [
                'province.required' => 'Vui lòng chọn Tỉnh/Thành phố',
                'district.required' => 'Vui lòng chọn Quận/Huyện',
                'xa.required' => 'Vui lòng chọn Xã/Phường',
                'address.required' => 'Vui lòng nhập số nhà, tên đường',
                'so_cccd.required' => 'Vui lòng nhập số CCCD/CMND',
                'cccd_image.required' => 'Vui lòng upload ảnh CCCD/CMND',
                'cccd_image.image' => 'File phải là ảnh (JPG, PNG, WEBP)',
                'cccd_image.mimes' => 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận: JPG, PNG, WEBP',
                'cccd_image.max' => 'Kích thước ảnh không được vượt quá 2MB',
                'address.max' => 'Địa chỉ không được vượt quá 500 ký tự',
                'province.max' => 'Tỉnh/Thành phố không được vượt quá 255 ký tự',
                'district.max' => 'Quận/Huyện không được vượt quá 255 ký tự',
                'xa.max' => 'Xã/Phường không được vượt quá 255 ký tự',
            ]);

            // Xử lý upload ảnh CCCD
            if ($request->hasFile('cccd_image')) {
                try {
                    // Xóa ảnh cũ nếu có
                    if ($user->cccd_image && Storage::disk('public')->exists($user->cccd_image)) {
                        Storage::disk('public')->delete($user->cccd_image);
                    }

                    // Upload ảnh mới lên Cloudinary
                    $uploadResult = FileUploadService::uploadToCloudinary(
                        $request->file('cccd_image'),
                        'cccd_images'
                    );

                    $user->cccd_image = $uploadResult['url'];
                } catch (\Exception $e) {
                    return redirect()->route('account')
                        ->with('error', 'Lỗi khi upload ảnh CCCD: ' . $e->getMessage())
                        ->withInput();
                }
            }

            // Cập nhật thông tin
            $user->phone = $request->phone ?? null;
            $user->province = $request->province ?? null;
            $user->district = $request->district ?? null;
            $user->xa = $request->xa ?? null;
            $user->address = $request->address ?? null;
            $user->so_cccd = $request->so_cccd ?? null;
            $user->ngay_sinh = $request->ngay_sinh ?? null;
            $user->gioi_tinh = $request->gioi_tinh ?? null;

            if (!$user->save()) {
                return redirect()->route('account')->with('error', 'Không thể cập nhật thông tin. Vui lòng thử lại.');
            }

            // Cập nhật reader nếu có (đồng bộ thông tin)
            if ($user->reader) {
                $reader = $user->reader;
                $reader->ho_ten = $user->name;
                $reader->email = $user->email;
                $reader->so_dien_thoai = $user->phone;
                $reader->so_cccd = $user->so_cccd;
                $reader->ngay_sinh = $user->ngay_sinh;
                $reader->gioi_tinh = $user->gioi_tinh;
                
                // Đồng bộ địa chỉ chi tiết sang reader
                $reader->tinh_thanh = $user->province;
                $reader->huyen = $user->district;
                $reader->xa = $user->xa;
                $reader->so_nha = $user->address;
                
                // Ghép thành địa chỉ đầy đủ
                $fullAddress = collect([$user->address, $user->xa, $user->district, $user->province])
                    ->filter()
                    ->implode(', ');
                $reader->dia_chi = $fullAddress;
                
                $reader->save();
            }

            return redirect()->route('account')->with('success', 'Cập nhật thông tin thành công!');
        } catch (\Exception $e) {
            \Log::error('Update account error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return redirect()->route('account')
                ->with('error', 'Có lỗi xảy ra khi cập nhật thông tin: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị sách đã mua
     */
    public function purchasedBooks()
    {
        $user = auth()->user();

        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        $user->load('reader');

        // Lấy các OrderItem từ các đơn hàng đã thanh toán của user
        $orderItems = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->whereIn('payment_status', ['paid'])
                ->whereIn('status', ['processing', 'shipped', 'delivered']);
        })
            ->with(['purchasableBook', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('account.purchased-books', compact('orderItems'));
    }

    /**
     * Hiển thị sách đang đọc
     */
    public function readingBooks()
    {
        $user = auth()->user();

        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        $user->load('reader');

        // Lấy Reader của user
        $reader = $user->reader;
        $borrowedBooks = collect();

        if ($reader) {
            // Lấy sách đang mượn (Borrow) qua Reader
            $borrowedBooks = Borrow::where('reader_id', $reader->id)
                ->where('trang_thai', 'Dang muon')
                ->with('book')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Lấy sách đã mua (có thể đọc)
        $purchasedBooks = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->whereIn('payment_status', ['paid']);
        })
            ->with('purchasableBook')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('account.reading-books', compact('borrowedBooks', 'purchasedBooks'));
    }

    /**
     * Hiển thị sách đang mượn
     */
    public function borrowedBooks()
    {
        $user = auth()->user();

        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        $user->load('reader');

        // Lấy Reader của user
        $reader = $user->reader;

        // Luôn trả về paginator để tránh lỗi trong view
        if ($reader) {
            // Lấy sách đang mượn (Borrow) qua Reader - bao gồm cả trạng thái chờ xác nhận
            $borrows = Borrow::where('reader_id', $reader->id)
                ->where(function ($query) {
                    $query->where('trang_thai', 'Dang muon')
                        ->orWhere('trang_thai_chi_tiet', 'giao_hang_thanh_cong')
                        ->orWhere('trang_thai_chi_tiet', 'giao_hang_that_bai');
                })
                ->with(['borrowItems.book', 'borrowItems.inventory', 'librarian', 'reader', 'shippingLogs' => function($query) {
                    $query->where('status', 'giao_hang_that_bai')->latest()->first();
                }])
                ->orderBy('ngay_muon', 'desc')
                ->paginate(12);

            // Tính toán lại tien_coc, tien_thue và tien_ship nếu chưa có (từ thông tin sách/inventory)
            foreach ($borrows as $borrow) {
                $needsRecalculate = false;
                
                foreach ($borrow->borrowItems as $item) {
                    // Nếu tien_coc hoặc tien_thue = 0, tính toán lại từ thông tin sách
                    if (($item->tien_coc == 0 || $item->tien_thue == 0) && $item->book && $item->inventory) {
                        $book = $item->book;
                        $inventory = $item->inventory;
                        $hasCard = $reader ? true : false; // Có thẻ độc giả

                        // Sử dụng PricingService để tính phí
                        $fees = \App\Services\PricingService::calculateFees(
                            $book,
                            $inventory,
                            $item->ngay_muon,
                            $item->ngay_hen_tra,
                            $hasCard
                        );

                        $item->tien_coc = $fees['tien_coc'];
                        $item->tien_thue = $fees['tien_thue'];

                        // Lưu lại vào database nếu giá trị đã thay đổi
                        if ($item->isDirty(['tien_coc', 'tien_thue'])) {
                            $item->save();
                            $needsRecalculate = true;
                        }
                    }
                }
                
                // Đảm bảo tien_ship được đồng bộ từ items
                $tienShipFromItems = $borrow->borrowItems()->sum('tien_ship');
                if (($borrow->tien_ship ?? 0) == 0 && $tienShipFromItems > 0) {
                    $borrow->tien_ship = $tienShipFromItems;
                    $needsRecalculate = true;
                }
                
                // Tính lại tổng tiền của borrow nếu cần
                if ($needsRecalculate) {
                    $borrow->recalculateTotals();
                }
            }

            // Reservation model đã bị xóa
            $pendingReservations = collect();
        } else {
            // Trả về paginator rỗng nếu không có reader
            $borrows = Borrow::whereRaw('1 = 0')->paginate(12);
            $pendingReservations = collect();
        }

        return view('account.borrowed-books', compact('borrows', 'reader', 'pendingReservations'));
    }

    /**
     * Hiển thị văn bản đã mua
     */
    public function purchasedDocuments()
    {
        $user = auth()->user();

        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        $user->load('reader');

        // Lấy các văn bản từ OrderItems (giả sử có thể có Document trong OrderItems)
        // Hoặc có thể có bảng riêng cho documents
        $documents = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->whereIn('payment_status', ['paid']);
        })
            ->whereHas('purchasableBook', function ($query) {
                // Có thể filter theo loại document nếu có
            })
            ->with(['purchasableBook', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('account.purchased-documents', compact('documents'));
    }

    /**
     * Hiển thị form đổi mật khẩu
     */
    public function showChangePassword()
    {
        $user = auth()->user();
        // Load relationship reader để sidebar hiển thị "Sách đang mượn" ngay
        $user->load('reader');
        return view('account.change-password');
    }

    /**
     * Xử lý đổi mật khẩu
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('account.change-password')->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Hiển thị thông tin độc giả
     */
    public function readerInfo()
    {
        $user = auth()->user();
        // Load relationship reader với faculty và department
        $user->load(['reader.faculty', 'reader.department']);

        $reader = $user->reader;

        return view('account.reader-info', compact('reader'));
    }
}



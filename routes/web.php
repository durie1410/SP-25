<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicBookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\AdvancedSearchController;
use App\Http\Controllers\AdvancedStatisticsController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\BorrowItemController;
use App\Http\Controllers\ShippingLogController;
use App\Http\Controllers\VnPayController;
use App\Http\Controllers\Admin\InventoryReservationController;
use App\Http\Controllers\ReservationCartController;
use App\Http\Controllers\NotificationBellController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\BookDeleteRequestController;

use App\Http\Controllers\MomoController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\FinePaymentController;
use App\Http\Controllers\FinePaymentsController;

Route::post('/momo/create', [MomoController::class, 'createPayment'])
    ->name('momo.create');

Route::get('/momo/return', [MomoController::class, 'return'])
    ->name('momo.return');

Route::post('/momo/ipn', [MomoController::class, 'ipn'])
    ->name('momo.ipn');

Route::post('/orders/store', [OrderController::class, 'store'])
    ->name('orders.store');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// CSRF Token routes
Route::get('/csrf-token', [App\Http\Controllers\CsrfController::class, 'getToken'])->name('csrf.token');
Route::post('/csrf-refresh', [App\Http\Controllers\CsrfController::class, 'refreshToken'])->name('csrf.refresh');

// Route để fix cột users table - TỰ ĐỘNG CHẠY KHI TRUY CẬP
Route::get('/fix-users-table-columns', function() {
    try {
        $results = [];
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fix Users Table</title><style>body{font-family:Arial;padding:40px;background:#f5f5f5;}.container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}.success{color:#28a745;padding:15px;background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;margin:10px 0;}.error{color:#dc3545;padding:15px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:10px 0;}.info{color:#17a2b8;padding:15px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:5px;margin:10px 0;}pre{background:#f8f9fa;padding:15px;border-radius:5px;overflow-x:auto;}.btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin-top:20px;}</style></head><body><div class="container"><h1>🔧 Sửa lỗi bảng Users</h1>';
        
        // Kiểm tra cột so_cccd để xác định vị trí thêm cột
        $soCccdColumn = DB::select("SHOW COLUMNS FROM `users` WHERE Field = 'so_cccd'");
        $afterColumn = !empty($soCccdColumn) ? 'so_cccd' : 'address';
        
        // Kiểm tra và thêm cột ngay_sinh
        $columns = DB::select("SHOW COLUMNS FROM `users` WHERE Field = 'ngay_sinh'");
        if (empty($columns)) {
            try {
                DB::statement("ALTER TABLE `users` ADD COLUMN `ngay_sinh` DATE NULL AFTER `{$afterColumn}`");
                $results[] = "✓ Đã thêm cột ngay_sinh thành công!";
                $html .= '<div class="success">✓ Đã thêm cột ngay_sinh thành công!</div>';
            } catch (\Exception $e) {
                $results[] = "✗ Lỗi khi thêm ngay_sinh: " . $e->getMessage();
                $html .= '<div class="error">✗ Lỗi khi thêm ngay_sinh: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            $results[] = "✓ Cột ngay_sinh đã tồn tại.";
            $html .= '<div class="info">✓ Cột ngay_sinh đã tồn tại.</div>';
        }
        
        // Kiểm tra và thêm cột gioi_tinh
        $columns = DB::select("SHOW COLUMNS FROM `users` WHERE Field = 'gioi_tinh'");
        if (empty($columns)) {
            try {
                DB::statement("ALTER TABLE `users` ADD COLUMN `gioi_tinh` ENUM('Nam', 'Nu', 'Khac') NULL AFTER `ngay_sinh`");
                $results[] = "✓ Đã thêm cột gioi_tinh thành công!";
                $html .= '<div class="success">✓ Đã thêm cột gioi_tinh thành công!</div>';
            } catch (\Exception $e) {
                $results[] = "✗ Lỗi khi thêm gioi_tinh: " . $e->getMessage();
                $html .= '<div class="error">✗ Lỗi khi thêm gioi_tinh: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            $results[] = "✓ Cột gioi_tinh đã tồn tại.";
            $html .= '<div class="info">✓ Cột gioi_tinh đã tồn tại.</div>';
        }
        
        // Kiểm tra lại
        $allColumns = DB::select("SHOW COLUMNS FROM `users` WHERE Field IN ('ngay_sinh', 'gioi_tinh')");
        
        if (count($allColumns) == 2) {
            $html .= '<div class="success"><h2>✅ HOÀN THÀNH!</h2><p>Cả hai cột đã tồn tại trong bảng users:</p><pre>';
            foreach ($allColumns as $col) {
                $html .= "Cột: {$col->Field}\n";
                $html .= "  Type: {$col->Type}\n";
                $html .= "  Null: {$col->Null}\n\n";
            }
            $html .= '</pre></div>';
        } else {
            $html .= '<div class="error">⚠ Chỉ tìm thấy ' . count($allColumns) . ' cột. Có thể có vấn đề!</div>';
        }
        
        $html .= '<p><a href="/account" class="btn">← Quay lại trang tài khoản</a></p>';
        $html .= '</div></body></html>';
        
        return $html;
        
    } catch (\Exception $e) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title><style>body{font-family:Arial;padding:40px;background:#f5f5f5;}.container{max-width:800px;margin:0 auto;background:white;padding:30px;border-radius:10px;}.error{color:#dc3545;padding:15px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:20px 0;}pre{background:#f8f9fa;padding:15px;border-radius:5px;overflow-x:auto;}</style></head><body><div class="container"><h1>❌ Lỗi</h1><div class="error"><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '<br><br><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '<br><strong>Line:</strong> ' . $e->getLine() . '<br><br><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div></div></body></html>';
        return $html;
    }
});

// Route để fix cột anh_hoan_tra - TỰ ĐỘNG CHẠY KHI TRUY CẬP
Route::get('/fix-anh-hoan-tra-column', function() {
    try {
        $columns = DB::select("SHOW COLUMNS FROM `borrows` WHERE Field = 'anh_hoan_tra'");
        if (empty($columns)) {
            $checkTinhTrang = DB::select("SHOW COLUMNS FROM `borrows` WHERE Field = 'tinh_trang_sach'");
            if (!empty($checkTinhTrang)) {
                // FIX: COMMENT phải đứng trước AFTER trong MySQL
                DB::statement("ALTER TABLE `borrows` ADD COLUMN `anh_hoan_tra` VARCHAR(255) NULL COMMENT 'Ảnh minh chứng hoàn trả sách từ khách hàng' AFTER `tinh_trang_sach`");
            } else {
                DB::statement("ALTER TABLE `borrows` ADD COLUMN `anh_hoan_tra` VARCHAR(255) NULL COMMENT 'Ảnh minh chứng hoàn trả sách từ khách hàng'");
            }
            $result = DB::select("SHOW COLUMNS FROM `borrows` WHERE Field = 'anh_hoan_tra'");
            return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Fix Column</title><style>body{font-family:Arial;padding:40px;background:#f5f5f5;}.container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}.success{color:#28a745;font-size:18px;padding:15px;background:#d4edda;border:1px solid #c3e6cb;border-radius:5px;margin:20px 0;}.btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin-top:20px;}</style></head><body><div class="container"><h1>✅ Đã Fix Thành Công!</h1><div class="success">Cột <strong>anh_hoan_tra</strong> đã được thêm vào bảng <strong>borrows</strong>.</div><p>Bạn có thể quay lại trang trước và thử lại.</p><a href="/account/borrowed-books" class="btn">← Quay lại Sách đang mượn</a></div></body></html>';
        }
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Column Exists</title><style>body{font-family:Arial;padding:40px;background:#f5f5f5;}.container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:10px;}.info{color:#17a2b8;padding:15px;background:#d1ecf1;border:1px solid #bee5eb;border-radius:5px;margin:20px 0;}.btn{display:inline-block;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:5px;margin-top:20px;}</style></head><body><div class="container"><h1>ℹ️ Cột Đã Tồn Tại</h1><div class="info">Cột <strong>anh_hoan_tra</strong> đã có trong bảng <strong>borrows</strong>.</div><a href="/account/borrowed-books" class="btn">← Quay lại Sách đang mượn</a></div></body></html>';
    } catch (\Exception $e) {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title><style>body{font-family:Arial;padding:40px;background:#f5f5f5;}.container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:10px;}.error{color:#dc3545;padding:15px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:20px 0;}</style></head><body><div class="container"><h1>❌ Lỗi</h1><div class="error">' . htmlspecialchars($e->getMessage()) . '</div></div></body></html>';
    }
})->name('fix.anh.hoan.tra');

// Temporary route to add column (REMOVE AFTER FIXING)
Route::get('/admin/fix-add-column-now', function() {
    try {
        $columns = DB::select("SHOW COLUMNS FROM `borrows` WHERE Field = 'anh_hoan_tra'");
        if (empty($columns)) {
            $checkTinhTrang = DB::select("SHOW COLUMNS FROM `borrows` WHERE Field = 'tinh_trang_sach'");
            if (!empty($checkTinhTrang)) {
                // FIX: COMMENT phải đứng trước AFTER trong MySQL
                DB::statement("ALTER TABLE `borrows` ADD COLUMN `anh_hoan_tra` VARCHAR(255) NULL COMMENT 'Ảnh minh chứng hoàn trả sách từ khách hàng' AFTER `tinh_trang_sach`");
            } else {
                DB::statement("ALTER TABLE `borrows` ADD COLUMN `anh_hoan_tra` VARCHAR(255) NULL COMMENT 'Ảnh minh chứng hoàn trả sách từ khách hàng'");
            }
            $result = DB::select("SHOW COLUMNS FROM `borrows` WHERE Field = 'anh_hoan_tra'");
            return response()->json(['success' => true, 'message' => 'Đã thêm cột thành công!', 'column' => $result[0] ?? null], 200, [], JSON_UNESCAPED_UNICODE);
        }
        return response()->json(['success' => true, 'message' => 'Cột đã tồn tại.', 'column' => $columns[0]], 200, [], JSON_UNESCAPED_UNICODE);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500, [], JSON_UNESCAPED_UNICODE);
    }
})->name('fix.add.column');

// Frontend Routes
Route::get('/', [HomeController::class, 'trangchu'])->name('home');
Route::get('/home', function() { return redirect()->route('home'); });
Route::get('/trangchu', [HomeController::class, 'trangchu'])->name('trangchu');
Route::get('/chinh-sach-gia', [HomeController::class, 'pricingPolicy'])->name('pricing.policy');
Route::get('/huong-dan-muon-tra-sach', [HomeController::class, 'borrowReturnGuide'])->name('guide.borrow-return');
// Route hiển thị danh sách sách cho người dùng frontend
Route::get('/books', [PublicBookController::class, 'index'])->name('books.public');
Route::get('/books/{id}', [PublicBookController::class, 'show'])->name('books.show');
Route::get('/diem-sach/{id}', [PublicBookController::class, 'showDiemSach'])->name('diem-sach.show');
Route::get('/tin-tuc/{id}', [PublicBookController::class, 'showTinTuc'])->name('tin-tuc.show');
Route::post('/borrow-book', [HomeController::class, 'borrowBook'])->name('borrow.book')->middleware('auth');

// Reservation cart (Giỏ đặt trước)
Route::prefix('reservation-cart')->name('reservation-cart.')->middleware('auth')->group(function () {
    Route::get('/', [ReservationCartController::class, 'index'])->name('index');
    Route::post('/add', [ReservationCartController::class, 'add'])->name('add');
    Route::post('/remove/{bookId}', [ReservationCartController::class, 'remove'])->name('remove');
    Route::post('/submit', [ReservationCartController::class, 'submit'])->name('submit');
    Route::get('/count', [ReservationCartController::class, 'count'])->name('count');
    Route::post('/update-days/{bookId}', [ReservationCartController::class, 'updateDays'])->name('update-days');
    Route::post('/update-dates/{bookId}', [ReservationCartController::class, 'updateDates'])->name('update-dates');
    Route::post('/update-quantity/{bookId}', [ReservationCartController::class, 'updateQuantity'])->name('update-quantity');
});

// Notification bell (user)
Route::prefix('notifications')->name('notifications.')->middleware('auth')->group(function () {
    Route::get('/count', [NotificationBellController::class, 'count'])->name('count');
    Route::get('/latest', [NotificationBellController::class, 'latest'])->name('latest');
    Route::post('/read', [NotificationBellController::class, 'markRead'])->name('read');
    Route::post('/read-all', [NotificationBellController::class, 'markAllRead'])->name('read-all');
});

// Borrow Cart Routes (disabled - replaced by reservation cart)
Route::prefix('borrow-cart')->group(function () {
    Route::any('{any?}', function () {
        abort(404);
    })->where('any', '.*');
});

// Shopping Cart Routes (for purchasing books)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [App\Http\Controllers\CartController::class, 'index'])->name('index');
});

// Public Categories Route
Route::get('/categories', [CategoryController::class, 'publicIndex'])->name('categories.index');


// Public Comments Routes (for book detail pages)
Route::post('/books/{id}/comments', [CommentController::class, 'storePublic'])->name('books.comments.store')->middleware('auth');

// Order Routes
Route::get('/checkout', [App\Http\Controllers\OrderController::class, 'checkout'])->name('checkout')->middleware('auth');
// Đặt GET routes trước POST để tránh conflict
Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.detail')->middleware('auth');
Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
Route::post('/orders/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancel'])->name('orders.cancel')->middleware('auth');
Route::post('/borrows/{id}/cancel', [App\Http\Controllers\OrderController::class, 'cancelBorrow'])->name('borrows.cancel')->middleware('auth');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // 5 attempts per minute
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1'); // 3 attempts per minute
    
    // Password Reset Routes
    Route::get('/password/reset', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'reset'])->name('password.update');
    
    // Email Verification Routes
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware('throttle:6,1')->name('verification.send');
    
    // Google OAuth Routes
    Route::get('/auth/google', [App\Http\Controllers\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [App\Http\Controllers\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']);

// User Dashboard Route (for authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        // For regular users, redirect to home or books page
        return redirect()->route('home');
    })->name('dashboard');
    
    // Account/Profile Routes
    Route::get('/account', [App\Http\Controllers\UserAccountController::class, 'account'])->name('account');
    Route::put('/account', [App\Http\Controllers\UserAccountController::class, 'updateAccount'])->name('account.update');
    Route::get('/account/borrowed-books', [App\Http\Controllers\UserAccountController::class, 'borrowedBooks'])->name('account.borrowed-books');
    Route::get('/account/reading-books', [App\Http\Controllers\UserAccountController::class, 'readingBooks'])->name('account.reading-books');
    Route::get('/account/purchased-documents', [App\Http\Controllers\UserAccountController::class, 'purchasedDocuments'])->name('account.purchased-documents');
    Route::get('/account/change-password', [App\Http\Controllers\UserAccountController::class, 'showChangePassword'])->name('account.change-password');
    Route::put('/account/change-password', [App\Http\Controllers\UserAccountController::class, 'updatePassword'])->name('account.update-password');
    
    // Wallet Routes
    Route::get('/account/wallet', [App\Http\Controllers\WalletController::class, 'index'])->name('account.wallet');
    Route::get('/account/wallet/transactions', [App\Http\Controllers\WalletController::class, 'transactions'])->name('account.wallet.transactions');
    
    // Customer confirmation routes
    Route::post('/account/borrows/{id}/confirm-delivery', [BorrowController::class, 'customerConfirmDelivery'])->name('account.borrows.confirm-delivery');
    
    // Customer rejection routes
    Route::post('/account/borrows/{id}/reject-delivery', [BorrowController::class, 'customerRejectDelivery'])->name('account.borrows.reject-delivery');
    
    // Customer return request routes
    Route::post('/account/borrows/{id}/request-return', [BorrowController::class, 'customerRequestReturn'])->name('account.borrows.request-return');
    
    // Customer return book routes (hoàn trả sách)
    Route::post('/account/borrows/{id}/return-book', [BorrowController::class, 'customerReturnBook'])->name('account.borrows.return-book');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Route kiểm tra và sửa hoàn tiền - đặt ở đây để tránh conflict với route resource
    Route::get('borrows/check-and-fix-refunds', [BorrowController::class, 'checkAndFixRefunds'])->name('borrows.check-and-fix-refunds.get');
    Route::post('borrows/check-and-fix-refunds', [BorrowController::class, 'checkAndFixRefunds'])->name('borrows.check-and-fix-refunds');
    
    // Resource routes
      Route::resource('categories', CategoryController::class)->middleware('permission:view-categories');
    Route::get('categories-export', [CategoryController::class, 'export'])->name('categories.export')->middleware('permission:view-categories');
    Route::get('categories-print', [CategoryController::class, 'print'])->name('categories.print')->middleware('permission:view-categories');
    Route::get('categories-statistics', [CategoryController::class, 'statistics'])->name('categories.statistics')->middleware('permission:view-categories');
    Route::post('categories-bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulk-action')->middleware('permission:edit-categories');
    Route::post('categories/{id}/move-books', [CategoryController::class, 'moveBooks'])->name('categories.move-books')->middleware('permission:edit-categories');
        Route::resource('authors', App\Http\Controllers\Admin\AuthorController::class)->middleware('permission:view-readers');
      // Vô hiệu hóa create và store - sách mới chỉ được tạo từ quản lý kho
      Route::resource('books', BookController::class)->middleware('permission:view-books')->except(['create', 'store', 'edit', 'update', 'destroy']);
      Route::get('books/create', [BookController::class, 'create'])->name('books.create')->middleware('admin-only');
      Route::post('books', [BookController::class, 'store'])->name('books.store')->middleware('admin-only');
      Route::get('books/{book}/edit', [BookController::class, 'edit'])->name('books.edit')->middleware('permission:edit-books');
      Route::put('books/{book}', [BookController::class, 'update'])->name('books.update')->middleware('permission:edit-books');
      Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy')->middleware('permission:delete-books');
      Route::post('books/{id}/hide', [BookController::class, 'hide'])->name('books.hide')->middleware('permission:edit-books');
      Route::post('books/{id}/unhide', [BookController::class, 'unhide'])->name('books.unhide')->middleware('permission:edit-books');
      Route::post('books/delete-without-inventory', [BookController::class, 'deleteBooksWithoutInventory'])->name('books.delete-without-inventory')->middleware('permission:edit-books');
      Route::post('books/reset-ids', [BookController::class, 'resetIds'])->name('books.reset-ids')->middleware('permission:edit-books');
      Route::resource('purchasable-books', App\Http\Controllers\Admin\PurchasableBookController::class)->middleware('permission:view-books');
      Route::get('books-unified', [App\Http\Controllers\Admin\UnifiedBookController::class, 'index'])->name('books.unified')->middleware('permission:view-books');
      Route::resource('readers', ReaderController::class)->middleware('permission:view-readers');
      Route::get('readers/{id}/renew-card', [ReaderController::class, 'renewCard'])->name('readers.renew-card')->middleware('permission:edit-readers');
      Route::post('readers/{id}/suspend', [ReaderController::class, 'suspend'])->name('readers.suspend')->middleware('permission:edit-readers');
      Route::post('readers/{id}/activate', [ReaderController::class, 'activate'])->name('readers.activate')->middleware('permission:edit-readers');
      Route::get('readers-export', [ReaderController::class, 'export'])->name('readers.export')->middleware('permission:view-readers');
      Route::get('readers-print', [ReaderController::class, 'print'])->name('readers.print')->middleware('permission:view-readers');
      Route::get('readers-statistics', [ReaderController::class, 'statistics'])->name('readers.statistics')->middleware('permission:view-readers');
      Route::post('readers-bulk-action', [ReaderController::class, 'bulkAction'])->name('readers.bulk-action')->middleware('permission:edit-readers');
   
     // Inventory delete requests (Đề xuất xoá/thanh lý) - Staff tạo, Admin duyệt
     Route::get('inventory/delete-requests', [BookDeleteRequestController::class, 'index'])
         ->name('inventory.delete-requests.index')
         ->middleware('admin-only');
     Route::post('inventory/delete-requests', [BookDeleteRequestController::class, 'store'])
         ->name('inventory.delete-requests.store');
     Route::post('inventory/delete-requests/{id}/approve', [BookDeleteRequestController::class, 'approve'])
         ->name('inventory.delete-requests.approve')
         ->middleware('admin-only');
     Route::post('inventory/delete-requests/{id}/reject', [BookDeleteRequestController::class, 'reject'])
         ->name('inventory.delete-requests.reject')
         ->middleware('admin-only');

     // Inventory Reservations (Đặt trước) - Staff + Admin
     Route::prefix('inventory-reservations')->name('inventory-reservations.')->middleware('permission:view-borrows')->group(function () {
         Route::get('/', [InventoryReservationController::class, 'index'])->name('index');
         Route::post('{id}/ready', [InventoryReservationController::class, 'markAsReady'])->name('ready')->middleware('permission:edit-borrows');
         Route::post('{id}/fulfill', [InventoryReservationController::class, 'markAsFulfilled'])->name('fulfill')->middleware('permission:edit-borrows');
         Route::post('{id}/cancel', [InventoryReservationController::class, 'cancel'])->name('cancel')->middleware('permission:edit-borrows');
     });

     Route::resource('borrows', BorrowController::class)
         ->except(['destroy'])
         ->middleware('permission:view-borrows');
      Route::post('borrows/{id}/return', [BorrowController::class, 'return'])->name('borrows.return')->middleware('permission:return-books');
      Route::post('borrows/{id}/extend', [BorrowController::class, 'extend'])->name('borrows.extend')->middleware('permission:edit-borrows');
      Route::get('borrows-dashboard', [App\Http\Controllers\BorrowDashboardController::class, 'index'])->name('borrows.dashboard')->middleware('permission:view-borrows');
      Route::get('borrows-dashboard/export', [App\Http\Controllers\BorrowDashboardController::class, 'export'])->name('borrows.dashboard.export')->middleware('permission:view-reports');
      route::get('borrows/{id}/create-item', [BorrowController::class, 'createItem'])->name('borrows.createitem')->middleware('permission:create-borrows');
      Route::post('borrows/{id}/store-item', [BorrowController::class, 'storeItem'])->name('borrows.storeItem')->middleware('permission:create-borrows');
      Route::put('borrow-items/{id}', [BorrowItemController::class, 'update'])->name('borrowitems.update');
      Route::get('borrow-items/{id}', [BorrowItemController::class, 'show'])->name('borrowitems.show');
      
     Route::post('borrows/{id}/process', [BorrowController::class, 'processBorrow'])->name('borrows.process');
     Route::post('borrows/{id}/approve', [BorrowController::class, 'approve'])->name('borrows.approve')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/confirm-cash-payment', [BorrowController::class, 'confirmCashPayment'])->name('borrows.confirm-cash-payment')->middleware('permission:edit-borrows');

     // ===== 11 TRẠNG THÁI MỚI - Quản lý quy trình vận chuyển =====
     Route::post('borrows/{id}/confirm-order', [BorrowController::class, 'confirmOrder'])->name('borrows.confirm-order')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/complete-packaging', [BorrowController::class, 'completePackaging'])->name('borrows.complete-packaging')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/handover-shipper', [BorrowController::class, 'handoverToShipper'])->name('borrows.handover-shipper')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/confirm-delivery-success', [BorrowController::class, 'confirmDeliverySuccess'])->name('borrows.confirm-delivery-success')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/report-delivery-failed', [BorrowController::class, 'reportDeliveryFailed'])->name('borrows.report-delivery-failed')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/request-return', [BorrowController::class, 'requestReturn'])->name('borrows.request-return')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/confirm-return-shipping', [BorrowController::class, 'confirmReturnShipping'])->name('borrows.confirm-return-shipping')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/confirm-receive-check', [BorrowController::class, 'confirmReceiveAndCheck'])->name('borrows.confirm-receive-check')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/complete-order', [BorrowController::class, 'completeOrder'])->name('borrows.complete-order')->middleware('permission:edit-borrows');
     Route::post('borrows/{id}/refund-cancelled', [BorrowController::class, 'refundCancelledOrder'])->name('borrows.refund-cancelled')->middleware('permission:edit-borrows');
     Route::get('borrows/{id}/status-detail', [BorrowController::class, 'statusDetail'])->name('borrows.status-detail')->middleware('permission:view-borrows');
     // ============================================================

     Route::post('borrow-items/{id}/return', [BorrowController::class, 'returnItem'])->name('borrowitems.return');
      ///////////////////////////////////////////////////////////////////////
      Route::post('borrowitems/{id}/approve', [BorrowItemController::class, 'approve'])
          ->name('borrowitems.approve');
      Route::post('borrowitems/{id}/change-status', 
          [BorrowItemController::class, 'changeStatus']
      )->name('borrowitems.changeStatus');
      Route::post('borrowitems/{id}/lost', [BorrowItemController::class, 'markLost'])
          ->name('borrowitems.lost');
      Route::post('inventory/{id}/return', [InventoryController::class, 'returnToStock'])
           ->name('inventories.return');
      Route::post('borrowitems/{id}/report-damage', 
          [BorrowItemController::class, 'reportDamage']
      )->name('borrowitems.report-damage');
      Route::post('borrowitems/{id}/mark-overdue', 
          [BorrowItemController::class, 'markOverdue']
      )->name('borrowitems.mark-overdue');



/// ships

// Danh sách tất cả logs
Route::get('/shipping-logs', [ShippingLogController::class, 'index'])
    ->name('shipping_logs.index');

// Theo phiếu mượn
Route::get('/shipping-logs/borrow/{id}', [ShippingLogController::class, 'showByBorrow'])
    ->name('shipping_logs.by_borrow');

// Theo từng item của phiếu
Route::get('/shipping-logs/item/{id}', [ShippingLogController::class, 'show'])
    ->name('shipping_logs.show');

// Sửa đơn hàng
Route::get('/shipping-logs/{id}/edit', [ShippingLogController::class, 'edit'])
    ->name('shipping_logs.edit');

// Thêm log mới
Route::post('/shipping-logs/store', [ShippingLogController::class, 'store'])
    ->name('shipping_logs.store');

// Cập nhật trạng thái giao hàng
Route::post('/shipping-logs/{id}/status', [ShippingLogController::class, 'updateStatus'])
    ->name('shipping_logs.update_status');

// Xoá log
Route::delete('/shipping-logs/{id}', [ShippingLogController::class, 'destroy'])
    ->name('shipping_logs.destroy');


Route::resource('vouchers', VoucherController::class);

      Route::get('vouchers/{id}/restore', [VoucherController::class, 'restore'])->name('admin.vouchers.restore');
      Route::delete('vouchers/{id}/force-delete', [VoucherController::class, 'forceDelete'])->name('admin.vouchers.forceDelete');

      
      // Reports routes
      Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('permission:view-reports');
      Route::get('reports/borrows', [ReportController::class, 'borrowsReport'])->name('reports.borrows')->middleware('permission:view-reports');
      Route::get('reports/readers', [ReportController::class, 'readersReport'])->name('reports.readers')->middleware('permission:view-reports');
      Route::get('reports/books', [ReportController::class, 'booksReport'])->name('reports.books')->middleware('permission:view-reports');
      
      // Inventory Reports routes (Báo cáo kho sách)
      Route::prefix('inventory-reports')->name('inventory-reports.')->group(function() {
          Route::get('/', [App\Http\Controllers\InventoryReportController::class, 'index'])->name('index')->middleware('permission:view-reports');
          Route::get('book-statistics', [App\Http\Controllers\InventoryReportController::class, 'bookStatistics'])->name('book-statistics')->middleware('permission:view-reports');
          Route::get('borrow-return', [App\Http\Controllers\InventoryReportController::class, 'borrowReturnReport'])->name('borrow-return')->middleware('permission:view-reports');
          Route::get('import', [App\Http\Controllers\InventoryReportController::class, 'importReport'])->name('import')->middleware('permission:view-reports');
          Route::get('disposal', [App\Http\Controllers\InventoryReportController::class, 'disposalReport'])->name('disposal')->middleware('permission:view-reports');
          Route::get('fine', [App\Http\Controllers\InventoryReportController::class, 'fineReport'])->name('fine')->middleware('permission:view-reports');
          Route::get('late-return', [App\Http\Controllers\InventoryReportController::class, 'lateReturnReport'])->name('late-return')->middleware('permission:view-reports');
      });
      
      // Reviews routes
      Route::resource('reviews', ReviewController::class)->middleware('permission:view-reviews');
      Route::post('comments', [CommentController::class, 'store'])->name('comments.store')->middleware('permission:create-reviews');
      Route::put('comments/{id}', [CommentController::class, 'update'])->name('comments.update')->middleware('permission:edit-reviews');
      Route::delete('comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy')->middleware('permission:delete-reviews');
      Route::post('comments/{id}/like', [CommentController::class, 'like'])->name('comments.like')->middleware('permission:view-reviews');
      Route::post('comments/{id}/approve', [CommentController::class, 'approve'])->name('comments.approve')->middleware('permission:approve-reviews');
      Route::post('comments/{id}/reject', [CommentController::class, 'reject'])->name('comments.reject')->middleware('permission:approve-reviews');
      
      // Fines routes
      Route::resource('fines', FineController::class)->middleware('permission:view-fines');
      Route::post('fines/{id}/mark-paid', [FineController::class, 'markAsPaid'])->name('fines.mark-paid')->middleware('permission:edit-fines');
      Route::post('fines/{id}/waive', [FineController::class, 'waive'])->name('fines.waive')->middleware('permission:waive-fines');
      Route::post('fines/create-late-returns', [FineController::class, 'createLateReturnFines'])->name('fines.create-late-returns')->middleware('permission:create-fines');
      Route::get('fines-report', [FineController::class, 'report'])->name('fines.report')->middleware('permission:view-reports');

    // Fine Payment Routes (Tiền mặt & MoMo cũ)
    Route::post('borrows/{borrow}/fine-pay-cash', [FinePaymentController::class, 'payCash'])->name('borrows.fine-pay-cash');
    Route::post('borrows/{borrow}/fine-momo/create', [FinePaymentController::class, 'createMomoPayment'])->name('borrows.fine-momo.create-payment');

    // Màn hình trả sách theo TÊN khách (tick chọn nhiều sách, nhập tình trạng từng quyển)
    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('returns/process', [ReturnController::class, 'processReturn'])->name('returns.process');

    // Màn hình Thanh toán phạt (lọc theo độc giả)
    Route::get('fine-payments', [FinePaymentsController::class, 'index'])->name('fine-payments.index');
    Route::post('fine-payments/{reader}/pay-cash', [FinePaymentController::class, 'payCashByReader'])->name('fine-payments.pay-cash');
    Route::post('fine-payments/{reader}/momo/create', [FinePaymentController::class, 'createMomoPaymentByReader'])->name('fine-payments.momo.create-payment');

    // MoMo callbacks cho phạt (tách biệt với Order)
    Route::get('borrows/fine-momo/return', [FinePaymentController::class, 'momoReturn'])->name('borrows.fine-momo.return');
    Route::post('borrows/fine-momo/ipn', [FinePaymentController::class, 'momoIpn'])->name('borrows.fine-momo.ipn');
      
      // Reservations routes - Đã xóa (thay bằng giỏ hàng)
      Route::post('fines/{id}/restore', [FineController::class, 'restore'])->name('fines.restore')->middleware('permission:edit-fines');
      // Orders routes (Admin)
      Route::get('orders', [App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
      Route::get('order-edit/{id}', [App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('orders.edit');
      Route::get('orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
      Route::put('orders/{id}', [App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
      
      // Advanced Reports routes
      Route::get('advanced-reports', [App\Http\Controllers\Admin\AdvancedReportController::class, 'index'])->name('advanced-reports.index')->middleware('permission:view-reports');
      Route::get('advanced-reports/dashboard-stats', [App\Http\Controllers\Admin\AdvancedReportController::class, 'dashboardStats'])->name('advanced-reports.dashboard-stats')->middleware('permission:view-reports');
      Route::get('advanced-reports/borrowing-trends', [App\Http\Controllers\Admin\AdvancedReportController::class, 'borrowingTrends'])->name('advanced-reports.borrowing-trends')->middleware('permission:view-reports');
      Route::get('advanced-reports/borrowing-trends-chart', [App\Http\Controllers\Admin\AdvancedReportController::class, 'borrowingTrendsChart'])->name('advanced-reports.borrowing-trends-chart')->middleware('permission:view-reports');
      Route::get('advanced-reports/popular-books', [App\Http\Controllers\Admin\AdvancedReportController::class, 'popularBooks'])->name('advanced-reports.popular-books')->middleware('permission:view-reports');
      Route::get('advanced-reports/active-readers', [App\Http\Controllers\Admin\AdvancedReportController::class, 'activeReaders'])->name('advanced-reports.active-readers')->middleware('permission:view-reports');
      Route::get('advanced-reports/overdue-books', [App\Http\Controllers\Admin\AdvancedReportController::class, 'overdueBooks'])->name('advanced-reports.overdue-books')->middleware('permission:view-reports');
      Route::get('advanced-reports/fine-statistics', [App\Http\Controllers\Admin\AdvancedReportController::class, 'fineStatistics'])->name('advanced-reports.fine-statistics')->middleware('permission:view-reports');
      Route::get('advanced-reports/fine-trends-chart', [App\Http\Controllers\Admin\AdvancedReportController::class, 'fineTrendsChart'])->name('advanced-reports.fine-trends-chart')->middleware('permission:view-reports');
      Route::get('advanced-reports/category-performance', [App\Http\Controllers\Admin\AdvancedReportController::class, 'categoryPerformance'])->name('advanced-reports.category-performance')->middleware('permission:view-reports');
      Route::get('advanced-reports/monthly-report', [App\Http\Controllers\Admin\AdvancedReportController::class, 'monthlyReport'])->name('advanced-reports.monthly-report')->middleware('permission:view-reports');
      Route::get('advanced-reports/yearly-report', [App\Http\Controllers\Admin\AdvancedReportController::class, 'yearlyReport'])->name('advanced-reports.yearly-report')->middleware('permission:view-reports');
      Route::get('advanced-reports/books-by-category', [App\Http\Controllers\Admin\AdvancedReportController::class, 'booksByCategory'])->name('advanced-reports.books-by-category')->middleware('permission:view-reports');
      Route::get('advanced-reports/real-time-stats', [App\Http\Controllers\Admin\AdvancedReportController::class, 'realTimeStats'])->name('advanced-reports.real-time-stats')->middleware('permission:view-reports');
      Route::post('advanced-reports/export', [App\Http\Controllers\Admin\AdvancedReportController::class, 'export'])->name('advanced-reports.export')->middleware('permission:view-reports');
      
      // Bulk Operations routes
      Route::get('bulk-operations', [App\Http\Controllers\Admin\BulkOperationController::class, 'index'])->name('bulk-operations.index')->middleware('permission:manage-bulk-operations');
      Route::post('bulk-operations/books/update', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkUpdateBooks'])->name('bulk-operations.books.update')->middleware('permission:manage-bulk-operations');
      Route::delete('bulk-operations/books/delete', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkDeleteBooks'])->name('bulk-operations.books.delete')->middleware('permission:manage-bulk-operations');
      Route::post('bulk-operations/readers/update', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkUpdateReaders'])->name('bulk-operations.readers.update')->middleware('permission:manage-bulk-operations');
      Route::post('bulk-operations/borrows/extend', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkExtendBorrows'])->name('bulk-operations.borrows.extend')->middleware('permission:manage-bulk-operations');
      Route::post('bulk-operations/borrows/return', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkReturnBooks'])->name('bulk-operations.borrows.return')->middleware('permission:manage-bulk-operations');
      // Route bulk cancel reservations đã xóa (chức năng đặt trước đã bị loại bỏ)
      Route::post('bulk-operations/fines/create', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkCreateFines'])->name('bulk-operations.fines.create')->middleware('permission:manage-bulk-operations');
      Route::post('bulk-operations/categories/update', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkUpdateCategories'])->name('bulk-operations.categories.update')->middleware('permission:manage-bulk-operations');
      Route::post('bulk-operations/books/import', [App\Http\Controllers\Admin\BulkOperationController::class, 'bulkImportBooks'])->name('bulk-operations.books.import')->middleware('permission:manage-bulk-operations');
      Route::get('bulk-operations/books/export', [App\Http\Controllers\Admin\BulkOperationController::class, 'exportBooks'])->name('bulk-operations.books.export')->middleware('permission:manage-bulk-operations');
      Route::get('bulk-operations/stats', [App\Http\Controllers\Admin\BulkOperationController::class, 'getStats'])->name('bulk-operations.stats')->middleware('permission:manage-bulk-operations');
      
      // Advanced Statistics routes
      Route::get('statistics/advanced', [AdvancedStatisticsController::class, 'dashboard'])->name('statistics.advanced.dashboard')->middleware('permission:view-reports');
      Route::get('statistics/advanced/overview', [AdvancedStatisticsController::class, 'overview'])->name('statistics.advanced.overview')->middleware('permission:view-reports');
      Route::get('statistics/advanced/trends', [AdvancedStatisticsController::class, 'trends'])->name('statistics.advanced.trends')->middleware('permission:view-reports');
      Route::get('statistics/advanced/category-stats', [AdvancedStatisticsController::class, 'categoryStats'])->name('statistics.advanced.category-stats')->middleware('permission:view-reports');
      Route::get('statistics/advanced/faculty-stats', [AdvancedStatisticsController::class, 'facultyStats'])->name('statistics.advanced.faculty-stats')->middleware('permission:view-reports');
      Route::get('statistics/advanced/search-stats', [AdvancedStatisticsController::class, 'searchStats'])->name('statistics.advanced.search-stats')->middleware('permission:view-reports');
      Route::get('statistics/advanced/notification-stats', [AdvancedStatisticsController::class, 'notificationStats'])->name('statistics.advanced.notification-stats')->middleware('permission:view-reports');
      Route::get('statistics/advanced/inventory-stats', [AdvancedStatisticsController::class, 'inventoryStats'])->name('statistics.advanced.inventory-stats')->middleware('permission:view-reports');
      
      // Advanced Search routes
      Route::get('advanced-search', [AdvancedSearchController::class, 'index'])->name('advanced-search.index')->middleware('permission:view-books');
      Route::get('search/books', [AdvancedSearchController::class, 'searchBooks'])->name('search.books')->middleware('permission:view-books');
      Route::get('search/readers', [AdvancedSearchController::class, 'searchReaders'])->name('search.readers')->middleware('permission:view-readers');
      Route::get('search/borrows', [AdvancedSearchController::class, 'searchBorrows'])->name('search.borrows')->middleware('permission:view-borrows');
      Route::get('search/global', [AdvancedSearchController::class, 'globalSearch'])->name('search.global')->middleware('permission:view-books');
      Route::get('search/suggestions', [AdvancedSearchController::class, 'getSuggestions'])->name('search.suggestions')->middleware('permission:view-books');
      
      // Autocomplete routes for borrow form
      Route::get('autocomplete/readers', [AdvancedSearchController::class, 'autocompleteReaders'])->name('autocomplete.readers')->middleware('permission:view-readers');
      Route::get('autocomplete/books', [AdvancedSearchController::class, 'autocompleteBooks'])->name('autocomplete.books')->middleware('permission:view-books');
      Route::get('books/{book}/inventories', [AdvancedSearchController::class, 'getBookInventories'])
    ->name('books.inventories')
    ->middleware('permission:view-books'); // hoặc middleware phù hợp

      // Notification routes
      Route::get('notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index')->middleware('permission:view-borrows');
      Route::post('notifications/send-reminders', [App\Http\Controllers\Admin\NotificationController::class, 'sendReminders'])->name('notifications.send-reminders')->middleware('permission:edit-borrows');
      Route::post('notifications/send-custom', [App\Http\Controllers\Admin\NotificationController::class, 'sendCustomReminder'])->name('notifications.send-custom')->middleware('permission:edit-borrows');
      
    // Email Marketing Routes (temporary without permission for testing)
    Route::resource('email-marketing', App\Http\Controllers\Admin\EmailMarketingController::class);
    Route::post('email-marketing/{id}/send', [App\Http\Controllers\Admin\EmailMarketingController::class, 'sendNow'])->name('email-marketing.send');
    Route::post('email-marketing/{id}/schedule', [App\Http\Controllers\Admin\EmailMarketingController::class, 'schedule'])->name('email-marketing.schedule');
    Route::post('email-marketing/{id}/cancel', [App\Http\Controllers\Admin\EmailMarketingController::class, 'cancel'])->name('email-marketing.cancel');
    Route::get('email-marketing/subscribers', [App\Http\Controllers\Admin\EmailMarketingController::class, 'subscribers'])->name('email-marketing.subscribers');
    Route::post('email-marketing/subscribers/add', [App\Http\Controllers\Admin\EmailMarketingController::class, 'addSubscriber'])->name('email-marketing.subscribers.add');
    Route::post('email-marketing/subscribers/{id}/unsubscribe', [App\Http\Controllers\Admin\EmailMarketingController::class, 'unsubscribeSubscriber'])->name('email-marketing.subscribers.unsubscribe');
      
      // Inventory routes
      Route::resource('inventory', InventoryController::class)->except(['destroy'])->middleware('permission:view-books');
      Route::delete('inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy')->middleware('permission:edit-books');
      Route::get('inventory/book/{book_id}/show-all', [InventoryController::class, 'showByBook'])->name('inventory.show-by-book')->middleware('permission:view-books');
      Route::delete('inventory/book/{book_id}/delete-all', [InventoryController::class, 'destroyByBook'])->name('inventory.destroy-by-book')->middleware('permission:edit-books');
      Route::post('inventory/{id}/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer')->middleware('permission:edit-books');
      Route::post('inventory/{id}/repair', [InventoryController::class, 'repair'])->name('inventory.repair')->middleware('permission:edit-books');
      Route::get('inventory-transactions', [InventoryController::class, 'transactions'])->name('inventory.transactions')->middleware('permission:view-books');
      Route::get('inventory-dashboard', [InventoryController::class, 'dashboard'])->name('inventory.dashboard')->middleware('permission:view-books');
      Route::post('inventory/scan-barcode', [InventoryController::class, 'scanBarcode'])->name('inventory.scan-barcode')->middleware('permission:view-books');
      Route::post('inventory/sync-to-homepage', [InventoryController::class, 'syncToHomepage'])->name('inventory.sync-to-homepage')->middleware('permission:edit-books');
      
      // Inventory Receipts (Phiếu nhập kho)
      Route::get('inventory-receipts', [InventoryController::class, 'receipts'])->name('inventory.receipts')->middleware('permission:view-books');
      Route::get('inventory-receipts/create', [InventoryController::class, 'createReceipt'])->name('inventory.receipts.create')->middleware('permission:edit-books');
      Route::post('inventory-receipts', [InventoryController::class, 'storeReceipt'])->name('inventory.receipts.store')->middleware('permission:edit-books');
      Route::get('inventory-receipts/{id}', [InventoryController::class, 'showReceipt'])->name('inventory.receipts.show')->middleware('permission:view-books');
      Route::post('inventory-receipts/{id}/approve', [InventoryController::class, 'approveReceipt'])->name('inventory.receipts.approve')->middleware('permission:edit-books');
      Route::post('inventory-receipts/{id}/reject', [InventoryController::class, 'rejectReceipt'])->name('inventory.receipts.reject')->middleware('permission:edit-books');
      
      // API để lấy danh sách sách cho modal chọn sách
      Route::get('books/api/list', [BookController::class, 'apiList'])->name('books.api.list')->middleware('permission:view-books');
      
      // Display Allocations (Phân bổ trưng bày)
      Route::get('inventory-display-allocations', [InventoryController::class, 'displayAllocations'])->name('inventory.display-allocations')->middleware('permission:view-books');
      Route::get('inventory-display-allocations/create', [InventoryController::class, 'createDisplayAllocation'])->name('inventory.display-allocations.create')->middleware('permission:edit-books');
      Route::post('inventory-display-allocations', [InventoryController::class, 'storeDisplayAllocation'])->name('inventory.display-allocations.store')->middleware('permission:edit-books');
      Route::post('inventory-display-allocations/{id}/return', [InventoryController::class, 'returnFromDisplay'])->name('inventory.display-allocations.return')->middleware('permission:edit-books');
      
      // Inventory Report
      Route::get('inventory-report', [InventoryController::class, 'report'])->name('inventory.report')->middleware('permission:view-books');
      Route::post('inventory-report/sync', [InventoryController::class, 'syncInventoryBorrowItems'])->name('inventory.report.sync')->middleware('permission:view-books');
      Route::post('inventory/sync-data', [InventoryController::class, 'syncData'])->name('inventory.sync-data')->middleware('permission:edit-books');
      
      // Inventory Export/Import
      Route::get('inventory/export', [InventoryController::class, 'export'])->name('inventory.export')->middleware('permission:view-books');
      Route::post('inventory/import', [InventoryController::class, 'import'])->name('inventory.import')->middleware('permission:edit-books');
      Route::post('inventory/import-all-books', [InventoryController::class, 'importAllBooks'])->name('inventory.import-all-books')->middleware('permission:edit-books');
      
      // Admin Management routes
      Route::resource('publishers', App\Http\Controllers\Admin\PublisherController::class)->middleware('permission:view-books');
      Route::post('publishers/{id}/toggle-status', [App\Http\Controllers\Admin\PublisherController::class, 'toggleStatus'])->name('publishers.toggle-status')->middleware('permission:edit-books');
      Route::post('publishers-bulk-action', [App\Http\Controllers\Admin\PublisherController::class, 'bulkAction'])->name('publishers.bulk-action')->middleware('permission:edit-books');
      
      Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class)->middleware('permission:view-readers');
      Route::post('departments/{id}/toggle-status', [App\Http\Controllers\Admin\DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status')->middleware('permission:edit-readers');
      Route::post('departments-bulk-action', [App\Http\Controllers\Admin\DepartmentController::class, 'bulkAction'])->name('departments.bulk-action')->middleware('permission:edit-readers');
      
      Route::resource('faculties', App\Http\Controllers\Admin\FacultyController::class)->middleware('permission:view-readers');
      Route::post('faculties/{id}/toggle-status', [App\Http\Controllers\Admin\FacultyController::class, 'toggleStatus'])->name('faculties.toggle-status')->middleware('permission:edit-readers');
      Route::post('faculties-bulk-action', [App\Http\Controllers\Admin\FacultyController::class, 'bulkAction'])->name('faculties.bulk-action')->middleware('permission:edit-readers');
      
      // User Management routes
      Route::get('user-management', [App\Http\Controllers\Admin\UserManagementController::class, 'dashboard'])->name('user-management.dashboard')->middleware('permission:view-users');
      Route::resource('users', App\Http\Controllers\Admin\UserController::class)->middleware('permission:view-users');
      Route::post('users-bulk-action', [App\Http\Controllers\Admin\UserController::class, 'bulkAction'])->name('users.bulk-action')->middleware('permission:edit-users');
      Route::get('users-export', [App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export')->middleware('permission:view-users');
      
      // Librarian Management routes
      Route::resource('librarians', App\Http\Controllers\Admin\LibrarianController::class)->middleware('permission:view-users');
      Route::post('librarians/{id}/toggle-status', [App\Http\Controllers\Admin\LibrarianController::class, 'toggleStatus'])->name('librarians.toggle-status')->middleware('permission:edit-users');
      Route::post('librarians/{id}/renew-contract', [App\Http\Controllers\Admin\LibrarianController::class, 'renewContract'])->name('librarians.renew-contract')->middleware('permission:edit-users');
      Route::post('librarians-bulk-action', [App\Http\Controllers\Admin\LibrarianController::class, 'bulkAction'])->name('librarians.bulk-action')->middleware('permission:edit-users');
      Route::get('librarians-export', [App\Http\Controllers\Admin\LibrarianController::class, 'export'])->name('librarians.export')->middleware('permission:view-users');
      
      // Backup Management routes
      Route::get('backups', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index')->middleware('permission:manage-backup');
      Route::post('backups/create', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backups.create')->middleware('permission:manage-backup');
      Route::post('backups/restore', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore')->middleware('permission:manage-backup');
      Route::get('backups/download/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download')->middleware('permission:manage-backup');
      Route::delete('backups/delete', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backups.delete')->middleware('permission:manage-backup');
      Route::post('backups/validate', [App\Http\Controllers\Admin\BackupController::class, 'validateBackup'])->name('backups.validate')->middleware('permission:manage-backup');
      Route::get('backups/statistics', [App\Http\Controllers\Admin\BackupController::class, 'statistics'])->name('backups.statistics')->middleware('permission:manage-backup');
      Route::post('backups/schedule', [App\Http\Controllers\Admin\BackupController::class, 'schedule'])->name('backups.schedule')->middleware('permission:manage-backup');
      Route::get('backups/list', [App\Http\Controllers\Admin\BackupController::class, 'list'])->name('backups.list')->middleware('permission:manage-backup');
      
      // System Settings routes
      Route::get('settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index')->middleware('permission:view-settings');
      Route::put('settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update')->middleware('permission:edit-settings');
      Route::post('settings/cache/clear', [App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.cache.clear')->middleware('permission:manage-settings');
      Route::post('settings/database/optimize', [App\Http\Controllers\Admin\SettingsController::class, 'optimizeDatabase'])->name('settings.database.optimize')->middleware('permission:manage-settings');
      
      // Test route without permission middleware
      Route::get('settings/backup/test', [App\Http\Controllers\Admin\SettingsController::class, 'listBackups'])->name('settings.backup.test');
      
      // Banner Management routes
      Route::get('banners', [App\Http\Controllers\Admin\BannerController::class, 'index'])->name('banners.index');
      Route::post('banners/{bannerNumber}/upload', [App\Http\Controllers\Admin\BannerController::class, 'upload'])->name('banners.upload');
      Route::delete('banners/{bannerNumber}/delete', [App\Http\Controllers\Admin\BannerController::class, 'delete'])->name('banners.delete');
      
      // Audit Logs routes
      Route::get('audit-logs', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit-logs.index')->middleware('permission:view-logs');
      Route::get('audit-logs/{auditLog}', [App\Http\Controllers\Admin\AuditController::class, 'show'])->name('audit-logs.show')->middleware('permission:view-logs');
      Route::get('audit-logs-export', [App\Http\Controllers\Admin\AuditController::class, 'export'])->name('audit-logs.export')->middleware('permission:view-logs');
      Route::get('audit-logs-statistics', [App\Http\Controllers\Admin\AuditController::class, 'statistics'])->name('audit-logs.statistics')->middleware('permission:view-logs');
      Route::post('audit-logs/clear-old', [App\Http\Controllers\Admin\AuditController::class, 'clearOld'])->name('audit-logs.clear-old')->middleware('permission:manage-logs');
      Route::get('audit-logs/realtime', [App\Http\Controllers\Admin\AuditController::class, 'realTime'])->name('audit-logs.realtime')->middleware('permission:view-logs');
      
      // Logs & Audit Trail routes
      Route::get('logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index')->middleware('permission:view-logs');
      Route::get('logs/{id}', [App\Http\Controllers\Admin\LogController::class, 'show'])->name('logs.show')->middleware('permission:view-logs');
      Route::get('logs-export', [App\Http\Controllers\Admin\LogController::class, 'export'])->name('logs.export')->middleware('permission:view-logs');
      Route::get('logs/{id}/export', [App\Http\Controllers\Admin\LogController::class, 'exportSingle'])->name('logs.export-single')->middleware('permission:view-logs');
      Route::post('logs/clear-old', [App\Http\Controllers\Admin\LogController::class, 'clearOld'])->name('logs.clear-old')->middleware('permission:manage-logs');
      Route::get('logs/realtime', [App\Http\Controllers\Admin\LogController::class, 'realTime'])->name('logs.realtime')->middleware('permission:view-logs');
});

// VnPay Payment Routes
Route::prefix('vnpay')->name('vnpay.')->middleware('auth')->group(function () {
    // Tạo thanh toán cho phiếu mượn
    Route::post('create-payment', [VnPayController::class, 'createPayment'])->name('create-payment');
    
    // Tạo thanh toán từ giỏ mượn (API)
    Route::post('create-payment-cart', [VnPayController::class, 'createPaymentFromCart'])->name('create-payment-cart');
    
    // Tạo thanh toán từ giỏ đặt trước (reservation cart)
    Route::post('create-payment-reservation', [VnPayController::class, 'createPaymentFromReservationCart'])->name('create-payment-reservation');
});

// VnPay Callback (không cần auth vì VnPay gọi trực tiếp)
Route::get('vnpay/callback', [VnPayController::class, 'callback'])->name('vnpay.callback');


// Debug VnPay Payment URL
Route::get('debug-vnpay-payment', function() {
    $vnpayService = app(\App\Services\VnPayService::class);
    
    $paymentData = [
        'amount' => 100000, // 100k test
        'order_info' => "Test Payment #123",
        'order_id' => 'TEST' . time(),
        'order_type' => 'billpayment'
    ];
    
    $paymentUrl = $vnpayService->createPaymentUrl($paymentData, request());
    
    return response()->json([
        'payment_url' => $paymentUrl,
        'config' => [
            'tmn_code' => config('services.vnpay.tmn_code'),
            'hash_secret_exists' => !empty(config('services.vnpay.hash_secret')),
        ]
    ]);
})->name('debug.vnpay');

// Test VnPay Configuration (Development only - xóa khi production)
Route::get('test-vnpay-config', function() {
    $config = config('services.vnpay');
    
    return response()->json([
        'status' => 'VnPay Configuration Check',
        'tmn_code' => $config['tmn_code'] ?: '❌ CHƯA CẤU HÌNH - Cần thêm VNPAY_TMN_CODE vào .env',
        'hash_secret' => $config['hash_secret'] ? '✅ Đã cấu hình' : '❌ CHƯA CẤU HÌNH - Cần thêm VNPAY_HASH_SECRET vào .env',
        'url' => $config['url'],
        'return_url' => $config['return_url'],
        'version' => $config['version'],
        'command' => $config['command'],
        'curr_code' => $config['curr_code'],
        'locale' => $config['locale'],
        'note' => 'Hãy đảm bảo cả TMN_CODE và HASH_SECRET đều được cấu hình đúng'
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
})->name('test.vnpay.config');

// VNPay Debug Page - Giao diện đẹp để kiểm tra config
Route::get('vnpay-debug', function() {
    return view('vnpay-debug');
})->name('vnpay.debug');

// VNPay Fix Page - Sửa lỗi one-click
Route::get('vnpay-fix', function() {
    return view('vnpay-fix');
})->name('vnpay.fix');

// VNPay Test Callback - Test page
Route::get('vnpay-test', function() {
    return view('vnpay-test-callback');
})->name('vnpay.test');

// VNPay Test Signature - API test
Route::post('vnpay-test-signature', function() {
    $config = config('services.vnpay');
    
    // Tạo test data giống VNPay callback
    $testData = [
        'vnp_Amount' => '10000000',
        'vnp_BankCode' => 'NCB',
        'vnp_CardType' => 'ATM',
        'vnp_OrderInfo' => 'Test Payment',
        'vnp_PayDate' => '20251203103000',
        'vnp_ResponseCode' => '00',
        'vnp_TmnCode' => $config['tmn_code'],
        'vnp_TransactionNo' => '14374354',
        'vnp_TxnRef' => 'TEST' . time(),
    ];
    
    // Tính hash
    ksort($testData);
    $hashData = http_build_query($testData, '', '&');
    $computedHash = hash_hmac('sha512', $hashData, $config['hash_secret']);
    
    return response()->json([
        'config' => [
            'tmn_code' => $config['tmn_code'],
            'hash_secret_length' => strlen($config['hash_secret']),
            'hash_secret_correct' => $config['hash_secret'] === 'LYS57TC0V5NARXASTFT3Y0D50NHNPWEZ',
        ],
        'test_data' => $testData,
        'hash_data' => $hashData,
        'computed_hash' => $computedHash,
        'hash_preview' => substr($computedHash, 0, 20) . '...',
    ]);
})->name('vnpay.test.signature');

// VNPay Clear Session
Route::post('vnpay-clear-session', function() {
    session()->flush();
    session()->regenerate();
    
    return response()->json([
        'success' => true,
        'message' => 'Session cleared successfully'
    ]);
})->name('vnpay.clear.session');

// VNPay Fix Execute - API để sửa lỗi
Route::post('vnpay-fix-execute', function() {
    try {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            return response()->json([
                'success' => false,
                'message' => 'File .env không tồn tại'
            ]);
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES);
        $updated = false;
        $foundTMN = false;
        $foundHash = false;
        $foundURL = false;
        
        foreach ($lines as $key => $line) {
            if (strpos($line, 'VNPAY_TMN_CODE=') === 0) {
                $lines[$key] = 'VNPAY_TMN_CODE=E6I8Z7HX';
                $foundTMN = true;
                $updated = true;
            }
            if (strpos($line, 'VNPAY_HASH_SECRET=') === 0) {
                $lines[$key] = 'VNPAY_HASH_SECRET=LYS57TC0V5NARXASTFT3Y0D50NHNPWEZ';
                $foundHash = true;
                $updated = true;
            }
            if (strpos($line, 'VNPAY_URL=') === 0) {
                $lines[$key] = 'VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
                $foundURL = true;
                $updated = true;
            }
        }
        
        if (!$foundTMN) {
            $lines[] = 'VNPAY_TMN_CODE=E6I8Z7HX';
            $updated = true;
        }
        if (!$foundHash) {
            $lines[] = 'VNPAY_HASH_SECRET=LYS57TC0V5NARXASTFT3Y0D50NHNPWEZ';
            $updated = true;
        }
        if (!$foundURL) {
            $lines[] = 'VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
            $updated = true;
        }
        
        file_put_contents($envFile, implode("\n", $lines));
        
        // Clear cache
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật cấu hình VNPay thành công',
            'details' => "TMN_CODE: E6I8Z7HX\nHASH_SECRET: Đã cấu hình (32 ký tự)\nURL: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html\n\nĐã clear cache thành công!"
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
})->name('vnpay.fix.execute');

// Test Borrow Cart Status (Development only - xóa khi production)
Route::get('test-cart-status', function() {
    if (!auth()->check()) {
        return response()->json(['error' => 'Chưa đăng nhập'], 401, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    $user = auth()->user();
    $reader = $user->reader;
    
    if (!$reader) {
        return response()->json(['error' => 'User này không có thông tin độc giả'], 400, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    $cart = \App\Models\BorrowCart::where('reader_id', $reader->id)->first();
    
    if (!$cart) {
        return response()->json([
            'status' => '❌ Chưa có giỏ sách',
            'reader_id' => $reader->id,
            'reader_name' => $reader->ho_ten,
            'solution' => 'Thêm sách vào giỏ mượn trước'
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    $allItems = $cart->items;
    $selectedItems = $cart->items()->where('is_selected', true)->get();
    
    return response()->json([
        'status' => '✅ Có giỏ sách',
        'cart_id' => $cart->id,
        'reader_id' => $reader->id,
        'reader_name' => $reader->ho_ten,
        'total_items' => $allItems->count(),
        'selected_items' => $selectedItems->count(),
        'items' => $allItems->map(function($item) {
            return [
                'id' => $item->id,
                'book_id' => $item->book_id,
                'book_title' => $item->book->tieu_de ?? 'N/A',
                'quantity' => $item->quantity,
                'is_selected' => $item->is_selected ? '✅ Đã chọn' : '❌ Chưa chọn',
                'borrow_days' => $item->borrow_days,
            ];
        }),
        'note' => $selectedItems->count() === 0 ? '⚠️ Chưa chọn sách nào - Vào giỏ mượn và tick chọn sách' : 'OK - Có thể thanh toán'
    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
})->middleware('auth')->name('test.cart.status');

// Payment Result Pages
Route::prefix('payment')->name('payment.')->middleware('auth')->group(function () {
    Route::get('success/{payment_id}', [VnPayController::class, 'success'])->name('success');
    Route::get('failed', [VnPayController::class, 'failed'])->name('failed');
});

// ============================================================
// ROUTE TẠM THỜI: Thêm cột từ chối nhận sách
// Truy cập: http://quanlythuviennn.test/fix-rejection-columns
// SAU KHI CHẠY XONG, XÓA ĐOẠN NÀY ĐỂ BẢO MẬT!
// ============================================================
Route::get('/fix-rejection-columns', function () {
    // Chỉ cho phép trong môi trường local
    if (app()->environment('production')) {
        abort(403, 'This route is only available in local environment');
    }

    $results = [];
    $errors = [];
    
    try {
        // Kiểm tra bảng borrows
        if (!\Illuminate\Support\Facades\Schema::hasTable('borrows')) {
            $errors[] = '✗ Bảng borrows không tồn tại!';
        } else {
            $results[] = '✓ Bảng borrows đã tồn tại';
            
            // Thêm cột customer_rejected_delivery
            $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM borrows LIKE 'customer_rejected_delivery'");
            if (empty($columns)) {
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE borrows ADD COLUMN customer_rejected_delivery TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Khách hàng đã từ chối nhận sách'");
                    $results[] = '✓ Đã thêm cột customer_rejected_delivery thành công!';
                } catch (\Exception $e) {
                    $errors[] = '✗ Lỗi khi thêm customer_rejected_delivery: ' . $e->getMessage();
                }
            } else {
                $results[] = '✓ Cột customer_rejected_delivery đã tồn tại';
            }
            
            // Thêm cột customer_rejected_delivery_at
            $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM borrows LIKE 'customer_rejected_delivery_at'");
            if (empty($columns)) {
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE borrows ADD COLUMN customer_rejected_delivery_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Thời gian khách hàng từ chối nhận sách'");
                    $results[] = '✓ Đã thêm cột customer_rejected_delivery_at thành công!';
                } catch (\Exception $e) {
                    $errors[] = '✗ Lỗi khi thêm customer_rejected_delivery_at: ' . $e->getMessage();
                }
            } else {
                $results[] = '✓ Cột customer_rejected_delivery_at đã tồn tại';
            }
            
            // Thêm cột customer_rejection_reason
            $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM borrows LIKE 'customer_rejection_reason'");
            if (empty($columns)) {
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE borrows ADD COLUMN customer_rejection_reason TEXT NULL DEFAULT NULL COMMENT 'Lý do khách hàng từ chối nhận sách'");
                    $results[] = '✓ Đã thêm cột customer_rejection_reason thành công!';
                } catch (\Exception $e) {
                    $errors[] = '✗ Lỗi khi thêm customer_rejection_reason: ' . $e->getMessage();
                }
            } else {
                $results[] = '✓ Cột customer_rejection_reason đã tồn tại';
            }
        }
        
        // Kiểm tra lại
        $finalCheck = [];
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_rejected_delivery')) {
            $finalCheck[] = '✓ customer_rejected_delivery: CÓ';
        } else {
            $finalCheck[] = '✗ customer_rejected_delivery: CHƯA CÓ';
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_rejected_delivery_at')) {
            $finalCheck[] = '✓ customer_rejected_delivery_at: CÓ';
        } else {
            $finalCheck[] = '✗ customer_rejected_delivery_at: CHƯA CÓ';
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_rejection_reason')) {
            $finalCheck[] = '✓ customer_rejection_reason: CÓ';
        } else {
            $finalCheck[] = '✗ customer_rejection_reason: CHƯA CÓ';
        }
        
        // Hiển thị kết quả
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sửa lỗi - Thêm cột từ chối nhận sách</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #d82329; margin-bottom: 20px; }
        .success { color: #28a745; margin: 10px 0; }
        .error { color: #dc3545; margin: 10px 0; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2196F3; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Sửa lỗi - Thêm cột từ chối nhận sách</h1>';
        
        if (count($errors) > 0) {
            $html .= '<div class="error"><h3>❌ Lỗi:</h3><ul>';
            foreach ($errors as $error) {
                $html .= '<li>' . $error . '</li>';
            }
            $html .= '</ul></div>';
        }
        
        if (count($results) > 0) {
            $html .= '<div class="success"><h3>✅ Kết quả:</h3><ul>';
            foreach ($results as $result) {
                $html .= '<li>' . $result . '</li>';
            }
            $html .= '</ul></div>';
        }
        
        $html .= '<div class="info"><h3>📋 Kiểm tra cuối cùng:</h3><ul>';
        foreach ($finalCheck as $check) {
            $html .= '<li>' . $check . '</li>';
        }
        $html .= '</ul></div>';
        
        if (count($errors) === 0 && 
            \Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_rejected_delivery') &&
            \Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_rejected_delivery_at') &&
            \Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_rejection_reason')) {
            $html .= '<div class="success">
                <h3>✅ Hoàn tất!</h3>
                <p>Tất cả các cột đã được thêm thành công. Bạn có thể:</p>
                <ol>
                    <li>Làm mới trang <code>/account/borrowed-books</code></li>
                    <li>Thử lại chức năng từ chối nhận sách</li>
                    <li><strong>Xóa route này để bảo mật</strong></li>
                </ol>
            </div>';
        } else {
            $html .= '<div class="warning">
                <h3>⚠️ Có vấn đề!</h3>
                <p>Nếu các cột chưa được thêm, bạn có thể:</p>
                <ol>
                    <li>Chạy file SQL: <code>FIX_REJECTION_COLUMNS.sql</code> trong phpMyAdmin</li>
                    <li>Hoặc chạy migration: <code>php artisan migrate</code></li>
                </ol>
            </div>';
        }
        
        $html .= '<div class="warning">
            <p><strong>⚠️ Lưu ý:</strong> Sau khi sửa xong, vui lòng xóa route <code>/fix-rejection-columns</code> trong file <code>routes/web.php</code> để bảo mật!</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    } catch (\Exception $e) {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lỗi</title>
</head>
<body>
    <h1>Lỗi</h1>
    <p>' . $e->getMessage() . '</p>
    <p>File: ' . $e->getFile() . '</p>
    <p>Line: ' . $e->getLine() . '</p>
</body>
</html>';
    }
});

// ============================================================
// ROUTE TẠM THỜI: Thêm cột xác nhận khách hàng
// Truy cập: http://quanlythuviennn.test/fix-customer-confirmation
// SAU KHI CHẠY XONG, XÓA ĐOẠN NÀY ĐỂ BẢO MẬT!
// ============================================================
Route::get('/fix-customer-confirmation', function () {
    // Chỉ cho phép trong môi trường local
    if (app()->environment('production')) {
        abort(403, 'This route is only available in local environment');
    }

    $results = [];
    $errors = [];
    
    try {
        // Kiểm tra bảng borrows
        if (!\Illuminate\Support\Facades\Schema::hasTable('borrows')) {
            return response('❌ LỖI: Bảng borrows không tồn tại!', 500);
        }
        
        $results[] = '✓ Bảng borrows đã tồn tại';
        
        // Thêm cột customer_confirmed_delivery
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_confirmed_delivery')) {
            $results[] = '✓ Cột customer_confirmed_delivery đã tồn tại';
        } else {
            try {
                \Illuminate\Support\Facades\DB::statement("
                    ALTER TABLE borrows 
                    ADD COLUMN customer_confirmed_delivery TINYINT(1) NOT NULL DEFAULT 0 
                    COMMENT 'Khách hàng đã xác nhận nhận sách'
                ");
                $results[] = '✓ Đã thêm cột customer_confirmed_delivery thành công!';
            } catch (\Exception $e) {
                $errors[] = '✗ Lỗi khi thêm customer_confirmed_delivery: ' . $e->getMessage();
            }
        }
        
        // Thêm cột customer_confirmed_delivery_at
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_confirmed_delivery_at')) {
            $results[] = '✓ Cột customer_confirmed_delivery_at đã tồn tại';
        } else {
            try {
                \Illuminate\Support\Facades\DB::statement("
                    ALTER TABLE borrows 
                    ADD COLUMN customer_confirmed_delivery_at TIMESTAMP NULL DEFAULT NULL 
                    COMMENT 'Thời gian khách hàng xác nhận nhận sách'
                ");
                $results[] = '✓ Đã thêm cột customer_confirmed_delivery_at thành công!';
            } catch (\Exception $e) {
                $errors[] = '✗ Lỗi khi thêm customer_confirmed_delivery_at: ' . $e->getMessage();
            }
        }
        
        // Kiểm tra lại
        $finalCheck = [];
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_confirmed_delivery')) {
            $finalCheck[] = '✓ customer_confirmed_delivery: CÓ';
        } else {
            $finalCheck[] = '✗ customer_confirmed_delivery: CHƯA CÓ';
        }
        
        if (\Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_confirmed_delivery_at')) {
            $finalCheck[] = '✓ customer_confirmed_delivery_at: CÓ';
        } else {
            $finalCheck[] = '✗ customer_confirmed_delivery_at: CHƯA CÓ';
        }
        
        // Hiển thị kết quả
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sửa lỗi - Thêm cột xác nhận khách hàng</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { color: #4CAF50; background: #e8f5e9; padding: 10px; border-left: 4px solid #4CAF50; margin: 10px 0; }
        .error { color: #f44336; background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .button { display: inline-block; background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Sửa lỗi: Thêm cột xác nhận khách hàng</h1>';
        
        if (!empty($results)) {
            $html .= '<h2>Kết quả:</h2>';
            foreach ($results as $result) {
                $html .= '<div class="success">' . htmlspecialchars($result) . '</div>';
            }
        }
        
        if (!empty($errors)) {
            $html .= '<h2>Lỗi:</h2>';
            foreach ($errors as $error) {
                $html .= '<div class="error">' . htmlspecialchars($error) . '</div>';
            }
        }
        
        $html .= '<h2>Kiểm tra cuối cùng:</h2>';
        foreach ($finalCheck as $check) {
            $class = strpos($check, '✓') !== false ? 'success' : 'error';
            $html .= '<div class="' . $class . '">' . htmlspecialchars($check) . '</div>';
        }
        
        $allSuccess = \Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_confirmed_delivery') && 
                     \Illuminate\Support\Facades\Schema::hasColumn('borrows', 'customer_confirmed_delivery_at');
        
        if ($allSuccess) {
            $html .= '<div class="info">
                <h3>✅ HOÀN TẤT!</h3>
                <p>Các cột đã được thêm thành công vào bảng borrows.</p>
                <p>Bây giờ bạn có thể:</p>
                <ul>
                    <li>Làm mới trang web (F5)</li>
                    <li>Lỗi sẽ biến mất</li>
                    <li>Tính năng xác nhận 2 chiều sẽ hoạt động bình thường</li>
                </ul>
                <a href="/account/borrowed-books" class="button">Quay lại trang Sách đang mượn</a>
            </div>';
        } else {
            $html .= '<div class="error">
                <h3>⚠️ Có vấn đề!</h3>
                <p>Một số cột chưa được thêm thành công. Vui lòng kiểm tra lại hoặc chạy SQL trực tiếp trong phpMyAdmin.</p>
            </div>';
        }
        
        $html .= '<div class="info" style="margin-top: 30px; font-size: 12px; color: #666;">
            <p><strong>Lưu ý:</strong> Sau khi sửa xong, vui lòng xóa route này trong file <code>routes/web.php</code> để bảo mật!</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
        
    } catch (\Exception $e) {
        return response('❌ LỖI: ' . $e->getMessage(), 500);
    }
})->name('fix.customer.confirmation');
// ============================================================
// KẾT THÚC ROUTE TẠM THỜI
// ============================================================

// ============================================================
// ROUTE TẠM THỜI: Sửa các đơn đã xác nhận nhưng chưa chuyển trạng thái
// Truy cập: http://quanlythuviennn.test/fix-pending-confirmations
// SAU KHI CHẠY XONG, XÓA ĐOẠN NÀY ĐỂ BẢO MẬT!
// ============================================================
Route::get('/fix-pending-confirmations', function () {
    // Chỉ cho phép trong môi trường local
    if (app()->environment('production')) {
        abort(403, 'This route is only available in local environment');
    }
    
    $results = [];
    $errors = [];
    
    try {
        // Tìm các đơn đã được khách hàng xác nhận nhưng vẫn ở trạng thái giao_hang_thanh_cong
        $pendingBorrows = \App\Models\Borrow::where('customer_confirmed_delivery', true)
            ->where('trang_thai_chi_tiet', 'giao_hang_thanh_cong')
            ->get();
        
        $results[] = 'Tìm thấy ' . $pendingBorrows->count() . ' đơn hàng cần sửa.';
        
        if ($pendingBorrows->isEmpty()) {
            $results[] = '✓ Không có đơn nào cần sửa!';
        } else {
            foreach ($pendingBorrows as $borrow) {
                try {
                    // Chuyển trạng thái trực tiếp
                    $borrow->trang_thai_chi_tiet = 'da_muon_dang_luu_hanh';
                    $borrow->ngay_bat_dau_luu_hanh = $borrow->customer_confirmed_delivery_at ?? now();
                    $borrow->trang_thai = 'Dang muon';
                    $borrow->save();
                    
                    // Cập nhật items
                    $borrow->items()->update([
                        'trang_thai' => 'Dang muon',
                        'ngay_muon' => $borrow->customer_confirmed_delivery_at ?? now(),
                    ]);
                    
                    // Cập nhật ShippingLog
                    foreach ($borrow->shippingLogs as $log) {
                        if ($log->status === 'giao_hang_thanh_cong') {
                            $log->update([
                                'status' => 'da_muon_dang_luu_hanh',
                                'ngay_bat_dau_luu_hanh' => $borrow->customer_confirmed_delivery_at ?? now(),
                            ]);
                        }
                    }
                    
                    $results[] = "✓ Đã chuyển đơn #{$borrow->id} sang trạng thái 'Đã Mượn (Đang Lưu hành)'";
                    
                } catch (\Exception $e) {
                    $errors[] = "✗ Lỗi khi xử lý đơn #{$borrow->id}: " . $e->getMessage();
                }
            }
        }
        
        // Hiển thị kết quả
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sửa các đơn đã xác nhận</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .success { color: #4CAF50; background: #e8f5e9; padding: 10px; border-left: 4px solid #4CAF50; margin: 10px 0; }
        .error { color: #f44336; background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .button { display: inline-block; background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Sửa các đơn đã xác nhận nhưng chưa chuyển trạng thái</h1>';
        
        if (!empty($results)) {
            $html .= '<h2>Kết quả:</h2>';
            foreach ($results as $result) {
                $class = strpos($result, '✓') !== false ? 'success' : 'info';
                $html .= '<div class="' . $class . '">' . htmlspecialchars($result) . '</div>';
            }
        }
        
        if (!empty($errors)) {
            $html .= '<h2>Lỗi:</h2>';
            foreach ($errors as $error) {
                $html .= '<div class="error">' . htmlspecialchars($error) . '</div>';
            }
        }
        
        $html .= '<div class="info" style="margin-top: 30px;">
            <h3>✅ HOÀN TẤT!</h3>
            <p>Đã xử lý ' . $pendingBorrows->count() . ' đơn hàng.</p>
            <p>Bây giờ bạn có thể làm mới trang web và kiểm tra lại.</p>
            <a href="/admin/shipping-logs" class="button">Quay lại trang quản lý đơn hàng</a>
        </div>
        <div class="info" style="margin-top: 20px; font-size: 12px; color: #666;">
            <p><strong>Lưu ý:</strong> Sau khi sửa xong, vui lòng xóa route này trong file <code>routes/web.php</code> để bảo mật!</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
        
    } catch (\Exception $e) {
        return response('❌ LỖI: ' . $e->getMessage(), 500);
    }
})->name('fix.pending.confirmations');
// ============================================================
// KẾT THÚC ROUTE TẠM THỜI
// ============================================================


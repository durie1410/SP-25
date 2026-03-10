<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BorrowApiController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\FacultyApiController;
use App\Http\Controllers\Api\PublisherApiController;
use App\Http\Controllers\Api\InventoryFlowController;
use App\Http\Controllers\Api\PricingController as ApiPricingController;
use App\Http\Controllers\Api\LoanFlowController;
use App\Http\Controllers\Api\ReservationFlowController;
use App\Http\Controllers\Api\UserVerificationController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| API version 1 routes. All routes are prefixed with /api/v1
|
*/

// Public API routes
Route::get('/', [ApiController::class, 'index']);
Route::get('/stats', [ApiController::class, 'stats']);

// Books API
Route::get('/books', [BookApiController::class, 'index']);
Route::get('/books/{id}', [BookApiController::class, 'show']);
Route::get('/books/featured', [BookApiController::class, 'featured']);
Route::get('/books/new', [BookApiController::class, 'newBooks']);
Route::get('/books/good', [BookApiController::class, 'goodBooks']);
Route::get('/books/top-selling', [BookApiController::class, 'topSelling']);
Route::get('/books/most-viewed', [BookApiController::class, 'mostViewed']);
Route::get('/books/recommended', [BookApiController::class, 'recommended']);
Route::get('/books/search', [BookApiController::class, 'search']);
Route::get('/books/available', [BorrowApiController::class, 'getAvailableBooks']);
Route::get('/categories', [BookApiController::class, 'categories']);

// Departments API
Route::get('/departments', [DepartmentApiController::class, 'index']);
Route::get('/departments/active', [DepartmentApiController::class, 'getActive']);
Route::get('/departments/faculty/{facultyId}', [DepartmentApiController::class, 'getByFaculty']);
Route::get('/departments/{id}', [DepartmentApiController::class, 'show']);

// Faculties API
Route::get('/faculties', [FacultyApiController::class, 'index']);
Route::get('/faculties/active', [FacultyApiController::class, 'getActive']);
Route::get('/faculties/with-departments', [FacultyApiController::class, 'getWithDepartments']);
Route::get('/faculties/{id}', [FacultyApiController::class, 'show']);

// Publishers API
Route::get('/publishers', [PublisherApiController::class, 'index']);
Route::get('/publishers/active', [PublisherApiController::class, 'getActive']);
Route::get('/publishers/with-books', [PublisherApiController::class, 'getWithBooks']);
Route::get('/publishers/popular', [PublisherApiController::class, 'popular']);
Route::get('/publishers/recent', [PublisherApiController::class, 'recent']);
Route::get('/publishers/top-books', [PublisherApiController::class, 'topBooks']);
Route::get('/publishers/search', [PublisherApiController::class, 'search']);
Route::get('/publishers/{id}', [PublisherApiController::class, 'show']);

// Authentication API
Route::post('/auth/login', [AuthApiController::class, 'login']);
Route::post('/auth/register', [AuthApiController::class, 'register']);

// Reviews API (sử dụng web authentication)
Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'createReview'])->middleware('web');
Route::get('/books/{bookId}/reviews', [\App\Http\Controllers\ReviewController::class, 'getBookReviews']);

// Favorites API (sử dụng web authentication)
Route::post('/favorites/toggle', [\App\Http\Controllers\Api\BookApiController::class, 'toggleFavorite'])->middleware(['web', 'auth']);

// Borrow API routes (public for mobile app)
Route::prefix('borrows')->group(function () {
    Route::get('/reader', [BorrowApiController::class, 'getReaderBorrows']);
    Route::get('/{id}', [BorrowApiController::class, 'getBorrowDetail']);
    Route::post('/{id}/extend', [BorrowApiController::class, 'extendBorrow']);
    Route::post('/{id}/return', [BorrowApiController::class, 'returnBook']);
    Route::get('/reader/stats', [BorrowApiController::class, 'getReaderStats']);
});

// Inventory flows (warehouse/display)
Route::middleware(['auth:sanctum', 'role:warehouse,admin'])->prefix('inventory')->group(function () {
    Route::post('/intake', [InventoryFlowController::class, 'intake']);
    Route::post('/move', [InventoryFlowController::class, 'move']);
    Route::get('/stock', [InventoryFlowController::class, 'stock']);
});

// Pricing quote for rental & deposit
Route::get('/pricing/quote', [ApiPricingController::class, 'quote']);

// Online loans (guest/member)
Route::prefix('loans')->group(function () {
    Route::post('/', [LoanFlowController::class, 'create']); // public (guest allowed)
    Route::middleware(['auth:sanctum', 'role:librarian,admin'])->group(function () {
        Route::post('/{id}/confirm', [LoanFlowController::class, 'confirm']);
        Route::post('/{id}/pickup', [LoanFlowController::class, 'pickup']);
        Route::post('/{id}/return', [LoanFlowController::class, 'returnBook']);
        Route::post('/{id}/close', [LoanFlowController::class, 'close']);
    });
});

// Seating / reservations
Route::prefix('spaces')->group(function () {
    Route::get('/availability', [ReservationFlowController::class, 'availability']);
});
Route::prefix('reservations')->group(function () {
    Route::post('/', [ReservationFlowController::class, 'create']);
    Route::middleware(['auth:sanctum', 'role:librarian,admin'])->group(function () {
        Route::post('/{id}/check-in', [ReservationFlowController::class, 'checkIn']);
    });
});

// KYC endpoints
Route::post('/users/verify', [UserVerificationController::class, 'submit']);

// Cart API routes (public - supports both authenticated and guest users)
Route::prefix('cart')->group(function () {
    Route::get('/', [\App\Http\Controllers\CartController::class, 'index']);
    Route::post('/add', [\App\Http\Controllers\CartController::class, 'add']);
    Route::put('/update/{id}', [\App\Http\Controllers\CartController::class, 'update']);
    Route::delete('/remove/{id}', [\App\Http\Controllers\CartController::class, 'remove']);
    Route::delete('/clear', [\App\Http\Controllers\CartController::class, 'clear']);
    Route::get('/count', [\App\Http\Controllers\CartController::class, 'count']);
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthApiController::class, 'user']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/reader-profile', [AuthApiController::class, 'readerProfile']);
    
    // Admin only borrow operations
    Route::post('/borrows', [BorrowApiController::class, 'createBorrow'])->middleware('admin');
    
    // KYC endpoints
    Route::get('/users/me/verification', [UserVerificationController::class, 'myStatus']);
    Route::middleware('role:librarian,admin')->group(function () {
        Route::get('/users/{id}/verification', [UserVerificationController::class, 'getStatus']);
        Route::post('/users/{id}/verification/review', [UserVerificationController::class, 'review']);
    });
    
    // Backup API routes (Admin only)
    Route::prefix('backups')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'list']);
        Route::post('/create', [\App\Http\Controllers\Admin\BackupController::class, 'apiCreate']);
        Route::post('/restore', [\App\Http\Controllers\Admin\BackupController::class, 'apiRestore']);
        Route::get('/statistics', [\App\Http\Controllers\Admin\BackupController::class, 'statistics']);
    });
    
    // Advanced Reports API routes (Admin only)
    Route::prefix('reports')->middleware('admin')->group(function () {
        Route::get('/dashboard-stats', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'dashboardStats']);
        Route::get('/borrowing-trends', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'borrowingTrends']);
        Route::get('/popular-books', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'popularBooks']);
        Route::get('/active-readers', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'activeReaders']);
        Route::get('/overdue-books', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'overdueBooks']);
        Route::get('/fine-statistics', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'fineStatistics']);
        Route::get('/category-performance', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'categoryPerformance']);
        Route::get('/monthly-report', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'monthlyReport']);
        Route::get('/yearly-report', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'yearlyReport']);
        Route::get('/real-time-stats', [\App\Http\Controllers\Admin\AdvancedReportController::class, 'realTimeStats']);
        Route::get('/stock', [ReportController::class, 'stock']);
        Route::get('/loans', [ReportController::class, 'loans']);
        Route::get('/utilization', [ReportController::class, 'utilization']);
    });
    
    // Mobile API routes
    Route::prefix('mobile')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\MobileApiController::class, 'dashboard']);
        Route::get('/search-books', [\App\Http\Controllers\Api\MobileApiController::class, 'searchBooks']);
        Route::get('/book/{id}', [\App\Http\Controllers\Api\MobileApiController::class, 'getBookDetails']);
        Route::get('/categories', [\App\Http\Controllers\Api\MobileApiController::class, 'getCategories']);
        Route::get('/borrow-history', [\App\Http\Controllers\Api\MobileApiController::class, 'getBorrowHistory']);
        Route::get('/reservations', [\App\Http\Controllers\Api\MobileApiController::class, 'getReservations']);
        Route::get('/fines', [\App\Http\Controllers\Api\MobileApiController::class, 'getFines']);
        Route::post('/borrow/{id}/extend', [\App\Http\Controllers\Api\MobileApiController::class, 'extendBorrow']);
        Route::post('/reservations', [\App\Http\Controllers\Api\MobileApiController::class, 'createReservation']);
        Route::delete('/reservations/{id}', [\App\Http\Controllers\Api\MobileApiController::class, 'cancelReservation']);
    });
    
    // Real-time Notifications API routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::get('/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('/online', [\App\Http\Controllers\Api\NotificationController::class, 'markOnline']);
        Route::post('/offline', [\App\Http\Controllers\Api\NotificationController::class, 'markOffline']);
        Route::post('/test', [\App\Http\Controllers\Api\NotificationController::class, 'sendTest']);
    });
    
    // Order API routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\OrderController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\OrderController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\OrderController::class, 'show']);
    });
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});





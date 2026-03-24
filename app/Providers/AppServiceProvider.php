<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Borrow;
use App\Models\InventoryReservation;
use App\Observers\BorrowObserver;
use App\Observers\InventoryReservationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Cấu hình pagination view
        Paginator::defaultView('vendor.pagination.bootstrap-5');
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-4');

        // Đăng ký Observer để tự động hoàn tiền khi đơn bị hủy
        Borrow::observe(BorrowObserver::class);

        // Đăng ký Observer để gửi notification khi reservation chuyển sang ready
        // Chỉ dùng cho trường hợp khác (ReturnController, assignNextWaitingReservation)
        // KHÔNG dùng cho markAsReady vì đã có notification trong controller
    }
}

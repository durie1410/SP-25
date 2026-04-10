<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hệ thống trạng thái đơn mượn sách
    |--------------------------------------------------------------------------
    |
    | Định nghĩa 11 trạng thái trong quy trình mượn sách có vận chuyển
    |
    */

    'statuses' => [
        'don_hang_moi' => [
            'value' => 'don_hang_moi',
            'label' => 'Đơn hàng Mới',
            'description' => 'Đơn vừa được tạo, chờ xác nhận.',
            'color' => 'info',
            'icon' => 'fa-file-alt',
            'step' => 1,
            'next_statuses' => ['dang_chuan_bi_sach'],
            'actions' => ['xac_nhan'],
        ],

        'dang_chuan_bi_sach' => [
            'value' => 'dang_chuan_bi_sach',
            'label' => 'Đang Chuẩn bị Sách',
            'description' => 'Đang kiểm tra sách trong kho, đóng gói, và tạo mã vận đơn.',
            'color' => 'warning',
            'icon' => 'fa-box',
            'step' => 2,
            'next_statuses' => ['dang_giao_hang'],
            'actions' => ['hoan_thanh_dong_goi'],
        ],

        'cho_ban_giao_van_chuyen' => [
            'value' => 'cho_ban_giao_van_chuyen',
            'label' => 'Chờ Bàn giao Vận chuyển',
            'description' => 'Sách đã đóng gói xong, chờ đơn vị vận chuyển đến lấy.',
            'color' => 'info',
            'icon' => 'fa-handshake',
            'step' => 3,
            'next_statuses' => ['dang_giao_hang'],
            'actions' => ['ban_giao_shipper'],
        ],

        'dang_giao_hang' => [
            'value' => 'dang_giao_hang',
            'label' => 'Đang Giao hàng',
            'description' => 'Sách đang được vận chuyển đến địa chỉ người mượn.',
            'color' => 'primary',
            'icon' => 'fa-shipping-fast',
            'step' => 4,
            'next_statuses' => ['giao_hang_thanh_cong', 'giao_hang_that_bai'],
            'actions' => ['xac_nhan_giao_thanh_cong', 'bao_giao_that_bai'],
        ],

        'giao_hang_thanh_cong' => [
            'value' => 'giao_hang_thanh_cong',
            'label' => 'Giao hàng Thành công',
            'description' => 'Admin đã đánh dấu giao hàng thành công. Đang chờ khách hàng xác nhận đã nhận sách.',
            'color' => 'warning',
            'icon' => 'fa-check-circle',
            'step' => 5,
            'next_statuses' => ['da_muon_dang_luu_hanh'],
            'actions' => ['cho_xac_nhan_tu_khach_hang'],
        ],

        'giao_hang_that_bai' => [
            'value' => 'giao_hang_that_bai',
            'label' => 'Giao hàng Thất bại',
            'description' => 'Không thể giao hàng, sách đang được chuyển hoàn.',
            'color' => 'danger',
            'icon' => 'fa-times-circle',
            'step' => 6,
            'next_statuses' => ['dang_van_chuyen_tra_ve', 'da_nhan_va_kiem_tra'],
            'actions' => ['chuyen_hoan', 'thu_giao_lai'],
        ],

        'da_muon_dang_luu_hanh' => [
            'value' => 'da_muon_dang_luu_hanh',
            'label' => 'Đã Mượn (Đang Lưu hành)',
            'description' => 'Sách đang ở tay người mượn, theo dõi hạn trả sách.',
            'color' => 'primary',
            'icon' => 'fa-book-reader',
            'step' => 7,
            'next_statuses' => ['cho_tra_sach', 'dang_van_chuyen_tra_ve'],
            'actions' => ['tao_yeu_cau_tra_sach'],
        ],

        'cho_tra_sach' => [
            'value' => 'cho_tra_sach',
            'label' => 'Chờ Trả sách',
            'description' => 'Người mượn đã tạo yêu cầu trả sách.',
            'color' => 'warning',
            'icon' => 'fa-undo',
            'step' => 8,
            'next_statuses' => ['dang_van_chuyen_tra_ve'],
            'actions' => ['gui_tra_sach'],
        ],

        'dang_van_chuyen_tra_ve' => [
            'value' => 'dang_van_chuyen_tra_ve',
            'label' => 'Đang Vận chuyển Trả về',
            'description' => 'Sách đang được vận chuyển ngược lại về thư viện.',
            'color' => 'info',
            'icon' => 'fa-truck',
            'step' => 9,
            'next_statuses' => ['da_nhan_va_kiem_tra'],
            'actions' => ['xac_nhan_nhan_sach'],
        ],

        'da_nhan_va_kiem_tra' => [
            'value' => 'da_nhan_va_kiem_tra',
            'label' => 'Đã Nhận & Kiểm tra',
            'description' => 'Thư viện đã nhận sách trả, đang kiểm tra chất lượng và các khoản phí phạt (nếu có).',
            'color' => 'warning',
            'icon' => 'fa-search',
            'step' => 10,
            'next_statuses' => ['hoan_tat_don_hang'],
            'actions' => ['kiem_tra_sach', 'tinh_phi_phat'],
        ],

        'hoan_tat_don_hang' => [
            'value' => 'hoan_tat_don_hang',
            'label' => 'Hoàn tất Đơn hàng',
            'description' => 'Đơn hàng đã đóng, sách đã nhập kho, mọi giao dịch tài chính đã hoàn tất.',
            'color' => 'success',
            'icon' => 'fa-check-double',
            'step' => 11,
            'next_statuses' => [],
            'actions' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Các trạng thái đặc biệt
    |--------------------------------------------------------------------------
    */

    'special_statuses' => [
        'huy' => [
            'value' => 'huy',
            'label' => 'Đã Hủy',
            'description' => 'Đơn hàng đã bị hủy.',
            'color' => 'secondary',
            'icon' => 'fa-ban',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Màu sắc theo Bootstrap
    |--------------------------------------------------------------------------
    */

    'colors' => [
        'primary' => '#007bff',
        'secondary' => '#6c757d',
        'success' => '#28a745',
        'danger' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8',
        'light' => '#f8f9fa',
        'dark' => '#343a40',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tình trạng sách khi trả
    |--------------------------------------------------------------------------
    */

    'book_conditions' => [
        'binh_thuong' => [
            'value' => 'binh_thuong',
            'label' => 'Bình thường',
            'penalty_rate' => 0, // % giá trị sách
        ],
        'hong_nhe' => [
            'value' => 'hong_nhe',
            'label' => 'Hỏng nhẹ',
            'penalty_rate' => 20,
        ],
        'hong_nang' => [
            'value' => 'hong_nang',
            'label' => 'Hỏng nặng',
            'penalty_rate' => 50,
        ],
        'mat_sach' => [
            'value' => 'mat_sach',
            'label' => 'Mất sách',
            'penalty_rate' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thời gian mặc định
    |--------------------------------------------------------------------------
    */

    'default_times' => [
        'borrow_days' => 14,          // Số ngày mượn mặc định
        'overdue_fine_per_day' => 5000, // Phí phạt quá hạn mỗi ngày (VNĐ)
    ],

    /*
    |--------------------------------------------------------------------------
    | Quy tắc chuyển trạng thái
    |--------------------------------------------------------------------------
    */

    'transition_rules' => [
        // Chỉ admin/thủ thư mới có thể chuyển trạng thái
        'roles_can_update' => ['admin', 'librarian'],

        // Tự động chuyển trạng thái khi có điều kiện
        'auto_transitions' => [
            // Đã loại bỏ auto transition từ giao_hang_thanh_cong -> da_muon_dang_luu_hanh
            // Bây giờ cần xác nhận 2 chiều: Admin xác nhận giao thành công, sau đó Customer xác nhận đã nhận
        ],
    ],
];


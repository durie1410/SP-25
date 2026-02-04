@extends('layouts.admin')

@section('title', 'Báo Cáo Tổng Hợp Kho - Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-boxes" style="color: #22c55e;"></i>
            Báo Cáo Tổng Hợp Kho
        </h1>
        <p class="page-subtitle">Thống kê và báo cáo tổng hợp về kho sách</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <form action="{{ route('admin.inventory.report.sync') }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn đồng bộ hóa dữ liệu kho?\n\n✓ Liên kết Inventory với BorrowItem\n✓ Đồng bộ trạng thái mượn/trả\n✓ Sửa lỗi dữ liệu không khớp');">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-sync-alt"></i>
                Đồng Bộ Kho
            </button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="white-space: pre-line; margin-top: 20px;">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="margin-top: 20px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<!-- Thống kê tổng quan -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">TỔNG SÁCH TRONG KHO</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-book" style="font-size: 22px; color: #3b82f6;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['total_books_in_stock']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách trong kho</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #3b82f6; font-size: 12px; margin-top: auto;">
            <i class="fas fa-arrow-up"></i>
            <span>Tổng số lượng</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">CÒN LẠI TRONG KHO</h6>
            <div style="width: 44px; height: 44px; background: #d1fae5; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-check-circle" style="font-size: 22px; color: #22c55e;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['remaining_in_stock']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Có sẵn: {{ number_format($stats['available_in_stock']) }}</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #22c55e; font-size: 12px; margin-top: auto;">
            <i class="fas fa-check"></i>
            <span>Hoạt động bình thường</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">ĐÃ CHO MƯỢN</h6>
            <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-hand-holding" style="font-size: 22px; color: #f59e0b;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['borrowed_from_stock']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách đang được mượn</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #f59e0b; font-size: 12px; margin-top: auto;">
            <i class="fas fa-arrow-up"></i>
            <span>Đang mượn</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">TỔNG ĐÃ NHẬP</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-file-invoice" style="font-size: 22px; color: #06b6d4;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['total_imported']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">{{ $stats['total_imported_receipts'] }} phiếu nhập</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #06b6d4; font-size: 12px; margin-top: auto;">
            <i class="fas fa-info-circle"></i>
            <span>Tổng đã nhập</span>
        </div>
    </div>
</div>

<!-- Thống kê mượn/trả -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">MƯỢN HÔM NAY</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-book-open" style="font-size: 22px; color: #3b82f6;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ $stats['borrow_stats']['today'] }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách đã mượn hôm nay</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #3b82f6; font-size: 12px; margin-top: auto;">
            <i class="fas fa-calendar-day"></i>
            <span>Hôm nay</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">MƯỢN THÁNG NÀY</h6>
            <div style="width: 44px; height: 44px; background: #d1fae5; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-calendar-alt" style="font-size: 22px; color: #22c55e;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ $stats['borrow_stats']['this_month'] }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách đã mượn tháng này</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #22c55e; font-size: 12px; margin-top: auto;">
            <i class="fas fa-arrow-up"></i>
            <span>Tháng này</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">TRẢ HÔM NAY</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-undo" style="font-size: 22px; color: #06b6d4;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ $stats['borrow_stats']['returned_today'] }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách đã trả hôm nay</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #06b6d4; font-size: 12px; margin-top: auto;">
            <i class="fas fa-calendar-day"></i>
            <span>Hôm nay</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">TRẢ THÁNG NÀY</h6>
            <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-calendar-check" style="font-size: 22px; color: #f59e0b;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ $stats['borrow_stats']['returned_this_month'] }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách đã trả tháng này</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #f59e0b; font-size: 12px; margin-top: auto;">
            <i class="fas fa-arrow-up"></i>
            <span>Tháng này</span>
        </div>
    </div>
</div>

<!-- Thống kê theo tình trạng sách -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">SÁCH MỚI</h6>
            <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-star" style="font-size: 22px; color: #3b82f6;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['new_books']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách tình trạng mới</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #3b82f6; font-size: 12px; margin-top: auto;">
            <i class="fas fa-info-circle"></i>
            <span>Tình trạng mới</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">SÁCH CŨ</h6>
            <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-book" style="font-size: 22px; color: #f59e0b;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['old_books']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách tình trạng cũ</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #f59e0b; font-size: 12px; margin-top: auto;">
            <i class="fas fa-info-circle"></i>
            <span>Tình trạng cũ</span>
        </div>
    </div>
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; position: relative; min-height: 180px; display: flex; flex-direction: column; justify-content: space-between;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <h6 style="font-size: 13px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; line-height: 1.4;">SÁCH HỎNG</h6>
            <div style="width: 44px; height: 44px; background: #fee2e2; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-exclamation-triangle" style="font-size: 22px; color: #ef4444;"></i>
            </div>
        </div>
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h3 style="font-size: 32px; font-weight: 700; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ number_format($stats['damaged_books']) }}</h3>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.4;">Sách bị hỏng</p>
        </div>
        <div style="display: flex; align-items: center; gap: 6px; color: #ef4444; font-size: 12px; margin-top: auto;">
            <i class="fas fa-exclamation-circle"></i>
            <span>Cần xử lý</span>
        </div>
    </div>
</div>

    <!-- Chi tiết theo từng sách -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 25px;">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; padding: 20px 25px 15px 25px;">
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-book" style="color: #22c55e; margin-right: 8px;"></i> Chi Tiết Số Lượng Sách Theo Từng Cuốn
            </h5>
        </div>
        <div class="card-body" style="padding: 25px;">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên sách</th>
                            <th>Tác giả</th>
                            <th>Tổng số lượng</th>
                            <th>Còn lại</th>
                            <th>Có sẵn</th>
                            <th>Sách mới</th>
                            <th>Sách cũ</th>
                            <th>Sách hỏng</th>
                            <th>Sách mất</th>
                            <th>ĐÃ MƯỢN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['books_in_stock'] as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item['book']->ten_sach ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $item['book']->tac_gia ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $item['total'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $item['remaining'] }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $item['available'] }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #3b82f6; color: white;">{{ $item['new'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #f59e0b; color: white;">{{ $item['old'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #ef4444; color: white;">{{ $item['damaged'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #6b7280; color: white;">{{ $item['lost'] ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">{{ $item['borrowed'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Chưa có sách nào trong kho</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danh sách phiếu nhập -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 25px;">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; padding: 20px 25px 15px 25px;">
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-file-invoice" style="color: #22c55e; margin-right: 8px;"></i> Danh Sách Phiếu Nhập Kho
            </h5>
        </div>
        <div class="card-body" style="padding: 25px;">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Số phiếu</th>
                            <th>Ngày nhập</th>
                            <th>Sách</th>
                            <th>Số lượng nhập</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                            <th>Người nhập</th>
                            <th>Người phê duyệt</th>
                            <th>Nhà cung cấp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['import_receipts'] as $receipt)
                            <tr>
                                <td><strong>{{ $receipt->receipt_number }}</strong></td>
                                <td>{{ $receipt->receipt_date->format('d/m/Y') }}</td>
                                <td>{{ $receipt->book->ten_sach ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-primary">{{ $receipt->quantity }}</span>
                                </td>
                                <td>{{ number_format($receipt->unit_price, 0, ',', '.') }} VNĐ</td>
                                <td><strong>{{ number_format($receipt->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                <td>{{ $receipt->receiver->name ?? 'N/A' }}</td>
                                <td>{{ $receipt->approver->name ?? 'N/A' }}</td>
                                <td>{{ $receipt->supplier ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Chưa có phiếu nhập nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danh sách ai đang mượn -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 25px;">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; padding: 20px 25px 15px 25px; display: flex; justify-content: space-between; align-items: center;">
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-user-check" style="color: #22c55e; margin-right: 8px;"></i> Danh Sách Người Đang Mượn Sách Từ Kho
            </h5>
            <form action="{{ route('admin.inventory.report.sync') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" style="background: #22c55e; border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" onclick="return confirm('Bạn có chắc chắn muốn đồng bộ hóa dữ liệu? Hành động này sẽ liên kết Inventory với BorrowItem.');">
                    <i class="fas fa-sync-alt"></i> Đồng Bộ Dữ Liệu
                </button>
            </form>
        </div>
        <div class="card-body" style="padding: 25px;">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Người mượn</th>
                            <th>Số thẻ</th>
                            <th>Sách</th>
                            <th>Ngày mượn</th>
                            <th>Hạn trả</th>
                            <th>Số ngày mượn</th>
                            <th>Thủ thư</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['current_borrows'] as $index => $item)
                            @php
                                $borrowItem = $item->borrow_item ?? null;
                                $isOverdue = $borrowItem && $borrowItem->isOverdue();
                                $daysOverdue = $borrowItem && $isOverdue ? now()->diffInDays($borrowItem->ngay_hen_tra) : 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->reader->ho_ten ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $item->reader->so_the_doc_gia ?? 'N/A' }}</td>
                                <td>{{ $item->book->ten_sach ?? 'N/A' }}</td>
                                <td>{{ $item->ngay_muon ? (is_string($item->ngay_muon) ? \Carbon\Carbon::parse($item->ngay_muon)->format('d/m/Y') : $item->ngay_muon->format('d/m/Y')) : 'N/A' }}</td>
                                <td>
                                    @if($item->ngay_hen_tra)
                                        @php
                                            $ngayHenTra = is_string($item->ngay_hen_tra) ? \Carbon\Carbon::parse($item->ngay_hen_tra) : $item->ngay_hen_tra;
                                        @endphp
                                        @if($isOverdue)
                                            <span class="badge badge-danger">{{ $ngayHenTra->format('d/m/Y') }}</span>
                                        @else
                                            {{ $ngayHenTra->format('d/m/Y') }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($item->ngay_muon)
                                        @php
                                            $ngayMuon = is_string($item->ngay_muon) ? \Carbon\Carbon::parse($item->ngay_muon) : $item->ngay_muon;
                                        @endphp
                                        {{ $ngayMuon->diffInDays(now()) }} ngày
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $item->librarian->name ?? 'N/A' }}</td>
                                <td>
                                    @if($isOverdue)
                                        <span class="badge badge-danger">Quá hạn ({{ $daysOverdue }} ngày)</span>
                                    @else
                                        <span class="badge badge-warning">Đang mượn</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Hiện tại không có ai đang mượn sách từ kho</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danh sách ai đã trả -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 25px;">
        <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; padding: 20px 25px 15px 25px; display: flex; justify-content: space-between; align-items: center;">
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
                <i class="fas fa-user-check" style="color: #22c55e; margin-right: 8px;"></i> Danh Sách Người Đã Trả Sách (Gần Đây)
            </h5>
            <form action="{{ route('admin.inventory.report.sync') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" style="background: #22c55e; border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" onclick="return confirm('Bạn có chắc chắn muốn đồng bộ hóa dữ liệu? Hành động này sẽ liên kết Inventory với BorrowItem (bao gồm cả đã trả).');">
                    <i class="fas fa-sync-alt"></i> Đồng Bộ Dữ Liệu
                </button>
            </form>
        </div>
        <div class="card-body" style="padding: 25px;">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Người trả</th>
                            <th>Số thẻ</th>
                            <th>Sách</th>
                            <th>Ngày mượn</th>
                            <th>Hạn trả</th>
                            <th>Ngày trả thực tế</th>
                            <th>Số ngày mượn</th>
                            <th>Thủ thư</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['returned_borrows'] as $index => $borrow)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $borrow->reader->ho_ten ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $borrow->reader->so_the_doc_gia ?? 'N/A' }}</td>
                                <td>{{ $borrow->book->ten_sach ?? 'N/A' }}</td>
                                <td>{{ $borrow->ngay_muon ? $borrow->ngay_muon->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $borrow->ngay_hen_tra ? $borrow->ngay_hen_tra->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($borrow->ngay_tra_thuc_te)
                                        <span class="badge badge-success">{{ $borrow->ngay_tra_thuc_te->format('d/m/Y') }}</span>
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($borrow->ngay_muon && $borrow->ngay_tra_thuc_te)
                                        {{ $borrow->ngay_muon->diffInDays($borrow->ngay_tra_thuc_te) }} ngày
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $borrow->librarian->name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Chưa có ai trả sách</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

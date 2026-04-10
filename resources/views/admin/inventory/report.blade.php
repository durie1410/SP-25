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
<div id="books-stock-section" class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 25px;">
    <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; padding: 20px 25px 15px 25px; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
            <i class="fas fa-book" style="color: #22c55e; margin-right: 8px;"></i> Chi Tiết Số Lượng Sách Theo Từng Cuốn
        </h5>
        <a href="{{ route('admin.inventory.report.export-book-stock') }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
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
                        <td>{{ ($stats['books_in_stock']->currentPage() - 1) * $stats['books_in_stock']->perPage() + $index + 1 }}</td>
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
        @if($stats['books_in_stock']->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 20px;">
            {{ $stats['books_in_stock']->appends(request()->except('books_page'))->links('vendor.pagination.bootstrap-4-books') }}
        </div>
        @endif
    </div>
</div>

<!-- Danh sách phiếu nhập -->
<div id="import-receipts-section" class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 25px;">
    <div class="card-header" style="border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; margin-bottom: 20px; padding: 20px 25px 15px 25px; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0;">
            <i class="fas fa-file-invoice" style="color: #22c55e; margin-right: 8px;"></i> Danh Sách Phiếu Nhập Kho
        </h5>
        <a href="{{ route('admin.inventory.report.export-import-receipt') }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
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
        @if($stats['import_receipts']->hasPages())
        <div style="display: flex; justify-content: center; margin-top: 20px;">
            {{ $stats['import_receipts']->appends(request()->except('import_page'))->links('vendor.pagination.bootstrap-4-receipts') }}
        </div>
        @endif
    </div>
</div>
@endsection
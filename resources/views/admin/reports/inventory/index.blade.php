@extends('layouts.admin')

@section('title', 'Báo Cáo Kho Sách')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-boxes" style="color: #22c55e;"></i>
            Báo Cáo Kho Sách
        </h1>
        <p class="page-subtitle">Xem và quản lý các báo cáo thống kê kho sách</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 25px;">
    <!-- BC01 - Thống kê số lượng sách -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 220px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <h6 style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">BC01</h6>
                <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-chart-pie" style="font-size: 22px; color: #3b82f6;"></i>
                </div>
            </div>
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">Thống Kê Số Lượng Sách</h5>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.5;">Theo dõi tình trạng kho sách tổng thể và chi tiết theo nhiều tiêu chí (danh mục, tác giả, trạng thái, v.v.).</p>
            <ul style="list-style: none; padding: 0; margin: 0 0 16px 0;">
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Tổng số lượng sách
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Sách có sẵn
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Sách đang mượn
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.inventory-reports.book-statistics') }}" class="btn btn-primary" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
            <i class="fas fa-arrow-right"></i> Xem báo cáo
        </a>
    </div>

    <!-- BC02 - Báo cáo số lượng sách trả và mượn -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 220px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <h6 style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">BC02</h6>
                <div style="width: 44px; height: 44px; background: #d1fae5; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-exchange-alt" style="font-size: 22px; color: #22c55e;"></i>
                </div>
            </div>
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">Báo Cáo Số Lượng Sách Trả Và Mượn</h5>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.5;">Phân tích hoạt động mượn và trả sách theo thời gian, thể hiện tần suất sử dụng thư viện.</p>
            <ul style="list-style: none; padding: 0; margin: 0 0 16px 0;">
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Số lượng sách đã mượn và đã trả trong kỳ
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Tỷ lệ mượn/trả
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.inventory-reports.borrow-return') }}" class="btn btn-success" style="background: #22c55e; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
            <i class="fas fa-arrow-right"></i> Xem báo cáo
        </a>
    </div>

    <!-- BC03 - Báo cáo số lượng sách nhập -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 220px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <h6 style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">BC03</h6>
                <div style="width: 44px; height: 44px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-box-open" style="font-size: 22px; color: #06b6d4;"></i>
                </div>
            </div>
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">Báo Cáo Số Lượng Sách Nhập</h5>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.5;">Theo dõi hoạt động bổ sung tài liệu, kiểm soát số lượng sách mới nhập theo từng tiêu chí (nhà cung cấp, danh mục).</p>
            <ul style="list-style: none; padding: 0; margin: 0 0 16px 0;">
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Tổng số lượng sách nhập
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Phân tích theo nhà cung cấp, tác giả, v.v.
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.inventory-reports.import') }}" class="btn btn-info" style="background: #06b6d4; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
            <i class="fas fa-arrow-right"></i> Xem báo cáo
        </a>
    </div>

    <!-- BC04 - Báo cáo số lượng sách hủy -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 220px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <h6 style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">BC04</h6>
                <div style="width: 44px; height: 44px; background: #fee2e2; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-trash-alt" style="font-size: 22px; color: #ef4444;"></i>
                </div>
            </div>
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">Báo Cáo Số Lượng Sách Hủy</h5>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.5;">Theo dõi việc loại bỏ sách, phân tích lý do hủy (mất, hỏng) để cải thiện quy trình quản lý.</p>
            <ul style="list-style: none; padding: 0; margin: 0 0 16px 0;">
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Tổng số lượng sách hủy
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Phân tích theo lý do hủy, danh mục sách
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.inventory-reports.disposal') }}" class="btn btn-danger" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
            <i class="fas fa-arrow-right"></i> Xem báo cáo
        </a>
    </div>

    <!-- BC05 - Báo cáo tiền phạt -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 220px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <h6 style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">BC05</h6>
                <div style="width: 44px; height: 44px; background: #fef3c7; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-money-bill-wave" style="font-size: 22px; color: #f59e0b;"></i>
                </div>
            </div>
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">Báo Cáo Tiền Phạt</h5>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.5;">Theo dõi hiệu quả thu tiền phạt và phân tích các loại vi phạm, trạng thái thanh toán.</p>
            <ul style="list-style: none; padding: 0; margin: 0 0 16px 0;">
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Tổng số tiền phạt
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Chi tiết khoản phạt, trạng thái thanh toán (Đã/Chưa)
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.inventory-reports.fine') }}" class="btn btn-warning" style="background: #f59e0b; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
            <i class="fas fa-arrow-right"></i> Xem báo cáo
        </a>
    </div>

    <!-- BC06 - Báo cáo sách trả muộn -->
    <div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 220px; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <h6 style="font-size: 14px; font-weight: 700; color: #374151; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">BC06</h6>
                <div style="width: 44px; height: 44px; background: #f3f4f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-clock" style="font-size: 22px; color: #6b7280;"></i>
                </div>
            </div>
            <h5 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 0 0 12px 0;">Báo Cáo Sách Trả Muộn</h5>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0; line-height: 1.5;">Theo dõi các trường hợp trả sách quá hạn, phân tích theo độc giả và thời gian để đánh giá việc tuân thủ quy định.</p>
            <ul style="list-style: none; padding: 0; margin: 0 0 16px 0;">
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Tổng số sách trả muộn
                </li>
                <li style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-check" style="color: #22c55e; font-size: 10px;"></i> Phân tích theo độc giả, phòng mượn
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.inventory-reports.late-return') }}" class="btn btn-secondary" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; margin-top: auto;">
            <i class="fas fa-arrow-right"></i> Xem báo cáo
        </a>
    </div>
</div>

@endsection


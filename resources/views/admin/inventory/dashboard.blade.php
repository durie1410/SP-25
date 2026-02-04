@extends('layouts.admin')

@section('title', 'Dashboard Kho - LibNet Admin')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Dashboard Kho</h1>
    </div>
</div>

<!-- Thống kê tổng quan -->
<div class="row" style="margin-bottom: 25px;">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Tổng số sách</h5>
                <h2>{{ $stats['total_books'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5>Sách trong kho</h5>
                <h2>{{ $stats['books_in_stock'] }}</h2>
                <small>Có sẵn: {{ $stats['available_in_stock'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Sách trưng bày</h5>
                <h2>{{ $stats['books_on_display'] }}</h2>
                <small>Có sẵn: {{ $stats['available_on_display'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>Tổng có sẵn</h5>
                <h2>{{ $stats['available_books'] }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Thống kê chi tiết -->
<div class="row" style="margin-bottom: 25px;">
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Đang mượn</h5>
                <h2>{{ $stats['borrowed_books'] }}</h2>
                <small>Từ kho: {{ $stats['borrowed_from_stock'] }} | Từ trưng bày: {{ $stats['borrowed_from_display'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5>Sách hư hỏng</h5>
                <h2>{{ $stats['damaged_books'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5>Sách mất</h5>
                <h2>{{ $stats['lost_books'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h5>Giao dịch gần đây</h5>
                <h2>{{ $stats['recent_transactions']->count() }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Giao dịch gần đây -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-history"></i> Giao dịch gần đây</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Loại</th>
                        <th>Sách</th>
                        <th>Người thực hiện</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['recent_transactions'] as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-info">{{ $transaction->type }}</span></td>
                            <td>{{ $transaction->inventory->book->ten_sach ?? 'N/A' }}</td>
                            <td>{{ $transaction->performer->name ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Chưa có giao dịch nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


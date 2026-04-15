@extends('layouts.admin')

@section('title', 'Chi tiết nhà cung cấp')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-building"></i> Chi tiết nhà cung cấp</h2>
        <div>
            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <tr>
                    <th style="width:220px;">Tên nhà cung cấp</th>
                    <td>{{ $supplier->name }}</td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td>{{ $supplier->phone ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $supplier->email ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td>{{ $supplier->address ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Số phiếu nhập liên quan</th>
                    <td>{{ $supplier->receipts_count }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection

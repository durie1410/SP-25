@extends('layouts.admin')

@section('title', 'Map nhà cung cấp cũ')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-project-diagram"></i> Map nhà cung cấp cũ</h2>
        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách nhà cung cấp
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <p class="mb-2"><strong>Mục đích:</strong> Gán các giá trị nhà cung cấp cũ trong phiếu nhập sang nhà cung cấp chuẩn (supplier_id).</p>
            <p class="mb-0 text-muted">Chỉ các dòng được chọn nhà cung cấp mới được cập nhật.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.suppliers.legacy-map.apply') }}">
        @csrf
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách tên NCC cũ chưa map ({{ $legacyRows->count() }})</h5>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật mapping
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Tên NCC cũ</th>
                            <th style="width:140px;">Số phiếu</th>
                            <th style="min-width:260px;">Map sang NCC chuẩn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($legacyRows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $row->supplier }}</strong>
                                    <input type="hidden" name="mappings[{{ $index }}][legacy_name]" value="{{ $row->supplier }}">
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $row->receipts_count }}</span>
                                </td>
                                <td>
                                    <select name="mappings[{{ $index }}][supplier_id]" class="form-control">
                                        <option value="">-- Bỏ qua --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Không còn dữ liệu NCC cũ cần map.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
@endsection

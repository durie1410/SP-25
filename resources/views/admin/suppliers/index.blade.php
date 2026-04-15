@extends('layouts.admin')

@section('title', 'Quản lý nhà cung cấp')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck"></i> Quản lý nhà cung cấp</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.suppliers.legacy-map') }}" class="btn btn-outline-primary">
                <i class="fas fa-project-diagram"></i> Map NCC cũ
            </a>
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Thêm nhà cung cấp
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="row g-2">
                <div class="col-md-7">
                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}" placeholder="Tìm theo tên, số điện thoại, email, địa chỉ...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng hợp tác</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="fas fa-search"></i> Tìm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Danh sách nhà cung cấp ({{ $suppliers->total() }})</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Địa chỉ</th>
                        <th>Trạng thái</th>
                        <th>Phiếu nhập</th>
                        <th width="230">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td><strong>{{ $supplier->name }}</strong></td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td>{{ $supplier->email ?: '-' }}</td>
                            <td>{{ $supplier->address ?: '-' }}</td>
                            <td>
                                @if(($supplier->status ?? 'active') === 'active')
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Ngừng hợp tác</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ $supplier->receipts_count }}</span></td>
                            <td>
                                <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.suppliers.toggle-status', $supplier->id) }}" class="d-inline" onsubmit="return confirm('Đổi trạng thái hợp tác của nhà cung cấp này?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-{{ ($supplier->status ?? 'active') === 'active' ? 'secondary' : 'success' }}" title="{{ ($supplier->status ?? 'active') === 'active' ? 'Chuyển sang ngừng hợp tác' : 'Chuyển sang hoạt động' }}">
                                        <i class="fas fa-{{ ($supplier->status ?? 'active') === 'active' ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier->id) }}" class="d-inline" onsubmit="return confirm('Xóa nhà cung cấp này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Chưa có nhà cung cấp nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $suppliers->appends(request()->query())->links('vendor.pagination.admin') }}
    </div>
</div>
@endsection

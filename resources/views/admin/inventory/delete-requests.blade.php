@extends('layouts.admin')

@section('title', 'Yêu cầu xóa sách - Kho')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-trash"></i>
            Yêu cầu xóa sách
        </h1>
        <p class="page-subtitle">Nhân viên gửi yêu cầu, Admin duyệt hoặc từ chối</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i>
            Danh sách yêu cầu
        </h3>
    </div>

    <form method="GET" action="{{ route('admin.inventory.delete-requests.index') }}" style="padding: 20px; display:flex; gap:10px; flex-wrap: wrap;">
        <select name="status" class="form-select" style="min-width: 220px;">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Đã duyệt</option>
            <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Từ chối</option>
        </select>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Lọc</button>
        <a class="btn btn-secondary" href="{{ route('admin.inventory.delete-requests.index') }}"><i class="fas fa-redo"></i> Reset</a>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sách</th>
                    <th>Người yêu cầu</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td><span class="badge badge-info">{{ $req->id }}</span></td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $req->book->ten_sach ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #888;">#{{ $req->book_id }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $req->requester->name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #888;">#{{ $req->requested_by }}</div>
                        </td>
                        <td style="max-width: 360px;">{{ $req->reason ?? '-' }}</td>
                        <td>
                            @if($req->status==='pending')
                                <span class="badge badge-warning">Chờ duyệt</span>
                            @elseif($req->status==='approved')
                                <span class="badge badge-success">Đã duyệt</span>
                            @else
                                <span class="badge badge-danger">Từ chối</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 12px; color:#666;">{{ $req->created_at?->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            @if(auth()->user()->isAdmin())
                                @if($req->status==='pending')
                                    <form method="POST" action="{{ route('admin.inventory.delete-requests.approve', $req->id) }}" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-sm btn-success" type="submit" onclick="return confirm('Duyệt và xóa sách này?');">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.inventory.delete-requests.reject', $req->id) }}" style="display:inline; margin-left:6px;">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Từ chối yêu cầu xóa?');">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 30px; color:#888;">Chưa có yêu cầu nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 20px;">
        {{ $requests->appends(request()->query())->links('vendor.pagination.admin') }}
    </div>
</div>
@endsection

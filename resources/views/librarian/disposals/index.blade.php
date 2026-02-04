@extends('layouts.admin')

@section('title', 'Yêu cầu thanh lý của tôi')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-recycle"></i>
        Danh sách yêu cầu thanh lý
    </h1>
    <p class="page-subtitle">
        Đây là các yêu cầu thanh lý / đề xuất xóa mà bạn đã tạo. Trạng thái sẽ được Admin cập nhật.
    </p>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-list"></i> Yêu cầu đã gửi</h3>
        <a href="{{ route('librarian.disposals.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Tạo yêu cầu mới
        </a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Loại</th>
                    <th>Đối tượng</th>
                    <th>Lý do</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Ngày duyệt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr>
                        <td>{{ $req->id }}</td>
                        <td>
                            @if($req->type === 'book')
                                <span class="badge bg-info">Theo sách</span>
                            @else
                                <span class="badge bg-warning">Theo bản sao</span>
                            @endif
                        </td>
                        <td>
                            @if($req->type === 'book')
                                @if($req->book)
                                    [#{{ $req->book->id }}] {{ $req->book->ten_sach }}
                                @else
                                    <span class="text-muted">Sách đã bị xóa</span>
                                @endif
                            @else
                                @if($req->inventory && $req->inventory->book)
                                    Bản sao #{{ $req->inventory->id }} - {{ $req->inventory->book->ten_sach }}
                                @elseif($req->inventory)
                                    Bản sao #{{ $req->inventory->id }}
                                @else
                                    <span class="text-muted">Bản sao đã bị xóa</span>
                                @endif
                            @endif
                        </td>
                        <td style="max-width: 280px;">
                            {{ $req->reason ?: '—' }}
                        </td>
                        <td>
                            @php
                                $badgeClass = 'bg-secondary';
                                $label = 'Chờ duyệt';
                                if ($req->status === 'approved') {
                                    $badgeClass = 'bg-success';
                                    $label = 'Đã duyệt';
                                } elseif ($req->status === 'rejected') {
                                    $badgeClass = 'bg-danger';
                                    $label = 'Từ chối';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                        </td>
                        <td>{{ $req->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($req->reviewed_at)
                                {{ $req->reviewed_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Chưa có yêu cầu thanh lý nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $requests->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@endsection


@extends('layouts.admin')

@section('title', 'Yêu cầu xóa sách - Kho')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-trash"></i>
            Yêu cầu xóa sách / Báo hỏng
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
        <select name="status" class="form-select" style="min-width: 180px;">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Chờ duyệt</option>
            <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Đã duyệt</option>
            <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Từ chối</option>
        </select>
        <select name="type" class="form-select" style="min-width: 180px;">
            <option value="">-- Tất cả loại --</option>
            <option value="damage" {{ request('type')==='damage' ? 'selected' : '' }}>Báo hỏng</option>
            <option value="lost" {{ request('type')==='lost' ? 'selected' : '' }}>Báo mất</option>
            <option value="delete" {{ request('type')==='delete' ? 'selected' : '' }}>Xóa sách</option>
        </select>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Lọc</button>
        <a class="btn btn-secondary" href="{{ route('admin.inventory.delete-requests.index') }}"><i class="fas fa-redo"></i> Reset</a>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Loại</th>
                    <th>Sách</th>
                    <th>Người yêu cầu</th>
                    <th>Ảnh minh chứng</th>
                    <th>Trạng thái</th>
                    <th>Thời gian</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    @php
                        $isDamage = $req->reason && str_starts_with($req->reason, '[BAO HONG]');
                        $isLost  = $req->reason && str_starts_with($req->reason, '[BAO MAT]');
                        $reasonClean = ($isDamage || $isLost)
                            ? trim(substr($req->reason, strpos($req->reason, ']') + 1))
                            : $req->reason;
                        $bookName = $req->book?->ten_sach ?? $req->inventory?->book?->ten_sach ?? 'Sách đã bị xóa';
                    @endphp
                    <tr>
                        <td><span class="badge badge-info">{{ $req->id }}</span></td>
                        <td>
                            @if($isLost)
                                <span class="badge bg-danger">
                                    <i class="fas fa-eye-slash"></i> Báo mất
                                </span>
                            @elseif($isDamage)
                                <span class="badge badge-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Báo hỏng
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    <i class="fas fa-trash"></i> Xóa sách
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-primary);">{{ $bookName }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $req->requester->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            @php
                                $proofImages = [];

                                $makeProofUrl = function ($img) {
                                    $img = trim((string) $img);
                                    if ($img === '') {
                                        return null;
                                    }

                                    if (preg_match('/^https?:\/\//i', $img)) {
                                        return $img;
                                    }

                                    $normalized = ltrim(str_replace('\\', '/', $img), '/');
                                    if (str_starts_with($normalized, 'storage/')) {
                                        $normalized = substr($normalized, 8);
                                    }

                                    return asset('storage/' . $normalized);
                                };

                                // Ưu tiên ảnh upload trực tiếp trên yêu cầu duyệt xóa.
                                if ($req->proof_images) {
                                    $proofImages = is_array($req->proof_images)
                                        ? $req->proof_images
                                        : (is_string($req->proof_images) ? json_decode($req->proof_images, true) : []);
                                }

                                // Fallback ảnh từ quá trình trả sách.
                                if (empty($proofImages) && $req->borrowItem) {
                                    $raw = $req->borrowItem->return_proof_images;
                                    $proofImages = is_array($raw)
                                        ? $raw
                                        : (is_string($raw) ? json_decode($raw, true) : []);

                                    $extraProofs = [
                                        $req->borrowItem->anh_bia_truoc ?? null,
                                        $req->borrowItem->anh_bia_sau ?? null,
                                        $req->borrowItem->anh_gay_sach ?? null,
                                    ];

                                    $proofImages = array_values(array_filter(array_merge(
                                        is_array($proofImages) ? $proofImages : [],
                                        $extraProofs
                                    )));
                                }

                                $proofUrls = collect(is_array($proofImages) ? $proofImages : [])
                                    ->map($makeProofUrl)
                                    ->filter()
                                    ->unique()
                                    ->values()
                                    ->all();
                            @endphp
                            @if(!empty($proofUrls))
                                <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                    @foreach($proofUrls as $imgUrl)
                                        <img src="{{ $imgUrl }}"
                                             alt="Ảnh minh chứng"
                                             style="width:50px; height:50px; object-fit:cover; border-radius:4px; border:1px solid #ddd; cursor:pointer;"
                                             onclick="window.open('{{ $imgUrl }}', '_blank')">
                                    @endforeach
                                </div>
                            @else
                                <span style="color:#aaa; font-size:12px;">Không có ảnh</span>
                            @endif

                            @if(!empty($reasonClean))
                                <div style="margin-top:8px; font-size:12px; color:#444; line-height:1.4;">
                                    <strong>Lý do:</strong> {{ $reasonClean }}
                                </div>
                            @endif
                        </td>
                        <td>
                          @if($isLost)
    <span class="badge badge-success">Đã duyệt</span>
@elseif($req->status==='pending')
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
@if($req->status==='pending' && !$isLost)    {{-- Duyệt --}}
    <form method="POST" action="{{ route('admin.inventory.delete-requests.approve', $req->id) }}" style="display:inline;">
        @csrf
        <button class="btn btn-sm btn-success" type="submit"
            onclick="return confirm('{{ $isDamage ? 'Xử lý báo hỏng này?' : 'Duyệt và xóa sách này?' }}');">
            <i class="fas fa-check"></i> Duyệt
        </button>
    </form>

    {{-- Nếu KHÔNG phải báo mất thì mới cho từ chối --}}
    @if(!$isLost)
        <form method="POST" action="{{ route('admin.inventory.delete-requests.reject', $req->id) }}" style="display:inline; margin-left:6px;">
            @csrf
            <button class="btn btn-sm btn-danger" type="submit"
                onclick="return confirm('Từ chối yêu cầu này?');">
                <i class="fas fa-times"></i> Từ chối
            </button>
        </form>
    @endif
@endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color:#888;">Chưa có yêu cầu nào</td>
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

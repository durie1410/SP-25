@extends('layouts.admin')

@section('title', 'Quản lý nhà cung cấp')

@section('content')
<div class="container-fluid supplier-page">
    <div class="supplier-hero mb-4">
        <div>
            <h2><i class="fas fa-truck-loading me-2"></i> Quản lý nhà cung cấp</h2>
            <p>Quản trị đối tác cung ứng, theo dõi trạng thái hợp tác và lịch sử phiếu nhập tại một nơi.</p>
        </div>
        <div class="supplier-hero-actions">
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-success btn-hero-primary">
                <i class="fas fa-plus"></i> Thêm nhà cung cấp
            </a>
        </div>
    </div>

    <div class="supplier-stats mb-4">
        <div class="supplier-stat-card">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div>
                <div class="stat-label">Tổng nhà cung cấp</div>
                <div class="stat-value">{{ number_format($totalSuppliers ?? 0) }}</div>
            </div>
        </div>
        <div class="supplier-stat-card">
            <div class="stat-icon stat-green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-label">Đang hợp tác</div>
                <div class="stat-value">{{ number_format($activeSuppliers ?? 0) }}</div>
            </div>
        </div>
        <div class="supplier-stat-card">
            <div class="stat-icon stat-slate"><i class="fas fa-pause-circle"></i></div>
            <div>
                <div class="stat-label">Ngừng hợp tác</div>
                <div class="stat-value">{{ number_format($inactiveSuppliers ?? 0) }}</div>
            </div>
        </div>
        <div class="supplier-stat-card">
            <div class="stat-icon stat-amber"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="stat-label">Đã có phiếu nhập</div>
                <div class="stat-value">{{ number_format($suppliersWithReceipts ?? 0) }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="card supplier-filter-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="row g-2 align-items-end" id="supplierFilterForm">
                <div class="col-lg-6 col-md-12">
                    <label class="form-label mb-1">Tìm kiếm nhanh</label>
                    <input type="text" name="keyword" id="supplierKeyword" class="form-control" value="{{ request('keyword') }}" placeholder="Nhập tên, số điện thoại, email hoặc địa chỉ...">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1">Trạng thái hợp tác</label>
                    <select name="status" id="supplierStatus" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngừng hợp tác</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6 d-flex gap-2">
                    <button class="btn btn-primary flex-fill" type="submit">
                        <i class="fas fa-search"></i> Lọc dữ liệu
                    </button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card supplier-table-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Danh sách nhà cung cấp</h5>
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2" id="supplierResultCount">{{ $suppliers->total() }} kết quả</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 supplier-table">
                <thead>
                    <tr>
                        <th>Nhà cung cấp</th>
                        <th>Liên hệ</th>
                        <th>Địa chỉ</th>
                        <th>Trạng thái</th>
                        <th>Phiếu nhập</th>
                        <th style="width: 250px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="supplierTableBody">
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="supplier-name-wrap">
                                    <span class="supplier-avatar">{{ strtoupper(mb_substr($supplier->name, 0, 1)) }}</span>
                                    <div>
                                        <div class="supplier-name">{{ $supplier->name }}</div>
                                        <small class="text-muted">Mã NCC: #{{ $supplier->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><i class="fas fa-phone text-muted me-1"></i> {{ $supplier->phone ?: '-' }}</div>
                                <div><i class="fas fa-envelope text-muted me-1"></i> {{ $supplier->email ?: '-' }}</div>
                            </td>
                            <td class="supplier-address">{{ $supplier->address ?: '-' }}</td>
                            <td>
                                @if(($supplier->status ?? 'active') === 'active')
                                    <span class="badge bg-success-subtle text-success-emphasis px-3 py-2">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2">Ngừng hợp tác</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis px-3 py-2">{{ number_format($supplier->receipts_count) }}</span>
                            </td>
                            <td>
                                <div class="supplier-actions">
                                    <a href="{{ route('admin.suppliers.show', $supplier->id) }}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                    <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                        <i class="fas fa-pen"></i> Sửa
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
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2"><i class="fas fa-inbox fa-2x"></i></div>
                                <div class="fw-semibold">Không tìm thấy nhà cung cấp phù hợp.</div>
                                <div class="text-muted">Thử điều kiện lọc khác hoặc thêm nhà cung cấp mới.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3" id="supplierPaginationWrapper">
        {{ $suppliers->appends(request()->query())->links('vendor.pagination.admin') }}
    </div>
</div>
@endsection

@push('styles')
<style>
.supplier-page {
    padding-bottom: 8px;
}

.supplier-hero {
    background: linear-gradient(135deg, #0f766e 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 20px 22px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.18);
}

.supplier-hero h2 {
    margin: 0 0 4px;
    font-weight: 700;
}

.supplier-hero p {
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
}

.supplier-hero-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-hero-primary,
.btn-hero-secondary {
    border-radius: 10px;
    font-weight: 600;
}

.supplier-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.supplier-stat-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #cffafe;
    color: #0f766e;
}

.stat-icon.stat-green {
    background: #dcfce7;
    color: #166534;
}

.stat-icon.stat-slate {
    background: #e2e8f0;
    color: #334155;
}

.stat-icon.stat-amber {
    background: #fef3c7;
    color: #92400e;
}

.stat-label {
    font-size: 12px;
    color: #64748b;
}

.stat-value {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.supplier-filter-card,
.supplier-table-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
}

.supplier-table th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.supplier-name-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}

.supplier-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #e0f2fe;
    color: #0369a1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.supplier-name {
    font-weight: 700;
    color: #0f172a;
}

.supplier-address {
    max-width: 280px;
    word-break: break-word;
}

.supplier-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

@media (max-width: 1200px) {
    .supplier-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .supplier-hero {
        padding: 16px;
    }

    .supplier-stats {
        grid-template-columns: 1fr;
    }

    .supplier-hero-actions {
        width: 100%;
    }

    .supplier-hero-actions .btn {
        flex: 1;
    }
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const filterForm = document.getElementById('supplierFilterForm');
    const keywordInput = document.getElementById('supplierKeyword');
    const statusSelect = document.getElementById('supplierStatus');
    const tableBody = document.getElementById('supplierTableBody');
    const resultCount = document.getElementById('supplierResultCount');
    const paginationWrapper = document.getElementById('supplierPaginationWrapper');
    const exportBtn = document.getElementById('supplierExportBtn');

    if (!filterForm || !keywordInput || !statusSelect || !tableBody) {
        return;
    }

    const csrfToken = '{{ csrf_token() }}';
    const searchUrl = '{{ route('admin.suppliers.search.json') }}';
    const exportBaseUrl = '{{ route('admin.suppliers.export.csv') }}';
    const showUrlTemplate = '{{ route('admin.suppliers.show', ['supplier' => '__ID__']) }}';
    const editUrlTemplate = '{{ route('admin.suppliers.edit', ['supplier' => '__ID__']) }}';
    const toggleUrlTemplate = '{{ route('admin.suppliers.toggle-status', ['id' => '__ID__']) }}';
    const deleteUrlTemplate = '{{ route('admin.suppliers.destroy', ['supplier' => '__ID__']) }}';

    let searchTimeout;

    const escapeHtml = (value) => {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const updateExportUrl = () => {
        if (!exportBtn) return;
        const params = new URLSearchParams();
        if (keywordInput.value.trim() !== '') params.set('keyword', keywordInput.value.trim());
        if (statusSelect.value !== '') params.set('status', statusSelect.value);
        const query = params.toString();
        exportBtn.href = query ? `${exportBaseUrl}?${query}` : exportBaseUrl;
    };

    const renderRows = (suppliers) => {
        if (!Array.isArray(suppliers) || suppliers.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="text-muted mb-2"><i class="fas fa-inbox fa-2x"></i></div>
                        <div class="fw-semibold">Không tìm thấy nhà cung cấp phù hợp.</div>
                        <div class="text-muted">Thử điều kiện lọc khác hoặc thêm nhà cung cấp mới.</div>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = suppliers.map((supplier) => {
            const id = Number(supplier.id || 0);
            const name = escapeHtml(supplier.name || '');
            const phone = escapeHtml(supplier.phone || '-');
            const email = escapeHtml(supplier.email || '-');
            const address = escapeHtml(supplier.address || '-');
            const status = String(supplier.status || 'active');
            const isActive = status === 'active';
            const firstChar = name ? name.charAt(0).toUpperCase() : 'N';
            const receiptsCount = Number(supplier.receipts_count || 0).toLocaleString('vi-VN');

            const showUrl = showUrlTemplate.replace('__ID__', String(id));
            const editUrl = editUrlTemplate.replace('__ID__', String(id));
            const toggleUrl = toggleUrlTemplate.replace('__ID__', String(id));
            const deleteUrl = deleteUrlTemplate.replace('__ID__', String(id));

            return `
                <tr>
                    <td>
                        <div class="supplier-name-wrap">
                            <span class="supplier-avatar">${firstChar}</span>
                            <div>
                                <div class="supplier-name">${name}</div>
                                <small class="text-muted">Mã NCC: #${id}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><i class="fas fa-phone text-muted me-1"></i> ${phone}</div>
                        <div><i class="fas fa-envelope text-muted me-1"></i> ${email}</div>
                    </td>
                    <td class="supplier-address">${address}</td>
                    <td>
                        ${isActive
                            ? '<span class="badge bg-success-subtle text-success-emphasis px-3 py-2">Hoạt động</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2">Ngừng hợp tác</span>'}
                    </td>
                    <td><span class="badge bg-info-subtle text-info-emphasis px-3 py-2">${receiptsCount}</span></td>
                    <td>
                        <div class="supplier-actions">
                            <a href="${showUrl}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                            <a href="${editUrl}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                <i class="fas fa-pen"></i> Sửa
                            </a>
                            <form method="POST" action="${toggleUrl}" class="d-inline" onsubmit="return confirm('Đổi trạng thái hợp tác của nhà cung cấp này?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <button type="submit" class="btn btn-sm btn-outline-${isActive ? 'secondary' : 'success'}" title="${isActive ? 'Chuyển sang ngừng hợp tác' : 'Chuyển sang hoạt động'}">
                                    <i class="fas fa-${isActive ? 'pause' : 'play'}"></i>
                                </button>
                            </form>
                            <form method="POST" action="${deleteUrl}" class="d-inline" onsubmit="return confirm('Xóa nhà cung cấp này?')">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const fetchRealtimeSuppliers = async () => {
        const params = new URLSearchParams();
        if (keywordInput.value.trim() !== '') params.set('keyword', keywordInput.value.trim());
        if (statusSelect.value !== '') params.set('status', statusSelect.value);

        try {
            const response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Không thể tải dữ liệu nhà cung cấp.');
            }

            renderRows(payload.suppliers || []);
            if (resultCount) {
                resultCount.textContent = `${Number(payload.total || 0).toLocaleString('vi-VN')} kết quả`;
            }
            if (paginationWrapper) {
                paginationWrapper.style.display = 'none';
            }
            updateExportUrl();
        } catch (error) {
            console.error(error);
        }
    };

    keywordInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchRealtimeSuppliers, 280);
    });

    statusSelect.addEventListener('change', () => {
        fetchRealtimeSuppliers();
    });

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchRealtimeSuppliers();
    });

    updateExportUrl();
})();
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Thêm Sách Vào Kho - LibNet Admin')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-boxes" style="color: #22c55e;"></i>
            Thêm sách vào kho
        </h1>
        <p class="page-subtitle">Nhập thông tin để thêm sách vào kho hoặc trưng bày</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

<div class="card" style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 25px;">
    <form action="{{ route('admin.inventory.store') }}" method="POST">
        @csrf
        
        <!-- Số phiếu và Ngày nhập -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Số phiếu</label>
                    <input type="text" class="form-control" value="{{ $receiptNumber ?? 'Auto-generated' }}" readonly>
                    <small class="form-text text-muted">Số phiếu được tạo tự động</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Ngày nhập <span class="text-danger">*</span></label>
                    <input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <!-- Loại nhập -->
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label class="form-label">Loại nhập <span class="text-danger">*</span></label>
                    <select name="book_input_type" id="book_input_type" class="form-control" required onchange="toggleBookInput()">
                        <option value="existing" {{ old('book_input_type', 'existing') == 'existing' ? 'selected' : '' }}>Chọn sách có sẵn</option>
                        <option value="new" {{ old('book_input_type') == 'new' ? 'selected' : '' }}>Thêm sách mới</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Chọn sách có sẵn -->
        <div id="existing_book_section">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Sách <span class="text-danger">*</span></label>
                        <select name="book_id" id="book_id" class="form-control">
                            <option value="">-- Chọn sách --</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                    {{ $book->ten_sach }} - {{ $book->tac_gia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thêm sách mới -->
        <div id="new_book_section" style="display: none;">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Thông tin:</strong> Nhập thông tin sách mới. Sách sẽ được tự động tạo trong hệ thống khi nhập kho.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tên sách <span class="text-danger">*</span></label>
                        <input type="text" name="ten_sach" id="ten_sach" class="form-control" value="{{ old('ten_sach') }}" placeholder="Nhập tên sách">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Tác giả <span class="text-danger">*</span></label>
                        <input type="text" name="tac_gia" id="tac_gia" class="form-control" value="{{ old('tac_gia') }}" placeholder="Nhập tên tác giả">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Thể loại <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value="">-- Chọn thể loại --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->ten_the_loai }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Nhà xuất bản</label>
                        <select name="nha_xuat_ban_id" id="nha_xuat_ban_id" class="form-control">
                            <option value="">-- Chọn nhà xuất bản --</option>
                            @foreach($publishers as $publisher)
                                <option value="{{ $publisher->id }}" {{ old('nha_xuat_ban_id') == $publisher->id ? 'selected' : '' }}>
                                    {{ $publisher->ten_nha_xuat_ban }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Năm xuất bản</label>
                        <input type="number" name="nam_xuat_ban" id="nam_xuat_ban" class="form-control" value="{{ old('nam_xuat_ban', date('Y')) }}" min="1900" max="{{ date('Y') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Giá (VNĐ)</label>
                        <input type="number" name="gia" id="gia" class="form-control" value="{{ old('gia') }}" min="0" step="1000">
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="mo_ta" id="mo_ta" class="form-control" rows="3" placeholder="Mô tả về sách...">{{ old('mo_ta') }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required>
                    </div>
                </div>
            </div>
        </div>

            <script>
            function toggleBookInput() {
                const inputType = document.getElementById('book_input_type').value;
                const existingSection = document.getElementById('existing_book_section');
                const newSection = document.getElementById('new_book_section');
                
                if (inputType === 'new') {
                    existingSection.style.display = 'none';
                    newSection.style.display = 'block';
                    document.getElementById('book_id').removeAttribute('required');
                    document.getElementById('ten_sach').setAttribute('required', 'required');
                    document.getElementById('tac_gia').setAttribute('required', 'required');
                    document.getElementById('category_id').setAttribute('required', 'required');
                } else {
                    existingSection.style.display = 'block';
                    newSection.style.display = 'none';
                    document.getElementById('book_id').setAttribute('required', 'required');
                    document.getElementById('ten_sach').removeAttribute('required');
                    document.getElementById('tac_gia').removeAttribute('required');
                    document.getElementById('category_id').removeAttribute('required');
                }
            }
            
            // Gọi hàm khi trang load
            document.addEventListener('DOMContentLoaded', function() {
                toggleBookInput();
            });
            </script>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Loại lưu trữ <span class="text-danger">*</span></label>
                    <select name="storage_type" id="storage_type" class="form-control" required onchange="updateLocationOptions()">
                        <option value="Kho" {{ old('storage_type', 'Kho') == 'Kho' ? 'selected' : '' }}>Kho</option>
                        <option value="Trung bay" {{ old('storage_type') == 'Trung bay' ? 'selected' : '' }}>Trưng bày</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Vị trí lưu trữ <span class="text-danger">*</span></label>
                    <div style="display: flex; gap: 5px;">
                        <select name="location" id="location" class="form-control" style="flex: 1;" onchange="handleLocationChange()">
                            <option value="">-- Chọn hoặc nhập mới --</option>
                            <optgroup label="Vị trí trong kho" id="locations_stock_group">
                                @foreach($locationsInStock as $loc)
                                    <option value="{{ $loc['location'] }}" {{ old('location') == $loc['location'] ? 'selected' : '' }}>
                                        {{ $loc['location'] }} ({{ $loc['count'] }} sách)
                                    </option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Vị trí trưng bày" id="locations_display_group">
                                @foreach($locationsOnDisplay as $loc)
                                    <option value="{{ $loc['location'] }}" {{ old('location') == $loc['location'] ? 'selected' : '' }}>
                                        {{ $loc['location'] }} ({{ $loc['count'] }} sách)
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <input type="text" 
                               id="location_input" 
                               class="form-control" 
                               style="flex: 1; display: none;" 
                               placeholder="Nhập vị trí mới..."
                               onblur="saveNewLocation()">
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Chọn vị trí có sẵn hoặc nhập vị trí mới
                    </small>
                </div>
            </div>
        </div>

            <script>
            const locationsInStock = @json($locationsInStock);
            const locationsOnDisplay = @json($locationsOnDisplay);
            
            function updateLocationOptions() {
                const storageType = document.getElementById('storage_type').value;
                const locationSelect = document.getElementById('location');
                const locationInput = document.getElementById('location_input');
                
                // Xóa tất cả options trừ option đầu tiên
                while (locationSelect.options.length > 1) {
                    locationSelect.remove(1);
                }
                
                // Thêm options theo loại lưu trữ
                if (storageType === 'Kho') {
                    locationsInStock.forEach(loc => {
                        const option = document.createElement('option');
                        option.value = loc.location;
                        option.textContent = loc.location + ' (' + loc.count + ' sách)';
                        locationSelect.appendChild(option);
                    });
                } else if (storageType === 'Trung bay') {
                    locationsOnDisplay.forEach(loc => {
                        const option = document.createElement('option');
                        option.value = loc.location;
                        option.textContent = loc.location + ' (' + loc.count + ' sách)';
                        locationSelect.appendChild(option);
                    });
                }
                
                // Thêm option "Nhập vị trí mới"
                const newOption = document.createElement('option');
                newOption.value = '__NEW__';
                newOption.textContent = '➕ Nhập vị trí mới...';
                locationSelect.appendChild(newOption);
            }
            
            function handleLocationChange() {
                const locationSelect = document.getElementById('location');
                const locationInput = document.getElementById('location_input');
                
                if (locationSelect.value === '__NEW__') {
                    locationSelect.style.display = 'none';
                    locationInput.style.display = 'block';
                    locationInput.value = '';
                    locationInput.focus();
                } else {
                    locationInput.style.display = 'none';
                    locationSelect.style.display = 'block';
                }
            }
            
            function saveNewLocation() {
                const locationInput = document.getElementById('location_input');
                const locationSelect = document.getElementById('location');
                
                if (locationInput.value.trim()) {
                    // Tạo option mới và thêm vào select
                    const newValue = locationInput.value.trim();
                    const newOption = document.createElement('option');
                    newOption.value = newValue;
                    newOption.textContent = newValue;
                    newOption.selected = true;
                    
                    // Xóa option "__NEW__" nếu có
                    const newOptionElement = locationSelect.querySelector('option[value="__NEW__"]');
                    if (newOptionElement) {
                        newOptionElement.remove();
                    }
                    
                    // Thêm option mới vào select
                    locationSelect.appendChild(newOption);
                    locationSelect.value = newValue;
                    
                    locationInput.style.display = 'none';
                    locationSelect.style.display = 'block';
                } else {
                    // Nếu không nhập gì, quay lại select
                    locationSelect.value = '';
                    locationInput.style.display = 'none';
                    locationSelect.style.display = 'block';
                }
            }
            
            // Đảm bảo giá trị được gửi đúng khi submit
            document.querySelector('form').addEventListener('submit', function(e) {
                const locationInput = document.getElementById('location_input');
                const locationSelect = document.getElementById('location');
                
                // Nếu đang ở chế độ nhập mới và có giá trị
                if (locationInput.style.display !== 'none' && locationInput.value.trim()) {
                    locationSelect.value = locationInput.value.trim();
                }
            });
            
            // Gọi hàm khi trang load
            document.addEventListener('DOMContentLoaded', function() {
                updateLocationOptions();
            });
            </script>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Giá mua đơn vị (VNĐ)</label>
                    <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price') }}" min="0" step="0.01">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Nhà cung cấp</label>
                    <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Lưu ý:</strong> Sách sẽ được nhập vào kho bởi <strong>{{ auth()->user()->name }}</strong>. 
            Hệ thống sẽ tự động tạo mã vạch và các bản ghi inventory cho số lượng được chọn.
        </div>

        <div class="d-flex justify-content-between" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary" style="background: white; color: #1f2937; border: 1px solid #e5e7eb; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="btn btn-primary" style="background: #22c55e; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-save"></i> Thêm Vào Kho
            </button>
        </div>
    </form>
</div>
@endsection


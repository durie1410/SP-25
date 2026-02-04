@props(['book', 'premium' => false])

@php
    $availableCopies = $book->inventories ? $book->inventories->where('status', 'Co san')->count() : 0;
    $colors = ['#1a5f4d', '#2d6e5a', '#3f7d67', '#518c74', '#639b81', '#75aa8e', '#87b99b', '#99c8a8'];
    $randomColor = $colors[array_rand($colors)];
@endphp

<div class="book-item {{ $premium ? 'premium' : '' }}" 
     data-book-id="{{ $book->id }}"
     data-book-title="{{ $book->ten_sach }}"
     data-book-author="{{ $book->tac_gia }}"
     data-book-genre="{{ $book->category->ten_the_loai ?? 'Chưa phân loại' }}"
     data-book-rating="4.5/5"
     data-book-year="{{ $book->nam_xuat_ban ?? 'N/A' }}"
     data-book-description="{{ Str::limit($book->mo_ta ?? 'Cuốn sách hay về ' . $book->ten_sach, 150) }}"
     data-book-premium="{{ $premium ? 'true' : 'false' }}">
    
    @if($premium)
    <div class="member-badge">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" fill="#FF6B35"/>
            <path d="M12 7l1.545 3.13L17 10.635l-2.5 2.435L15 17l-3-1.575L9 17l.5-3.93L7 10.635l3.455-.505L12 7z" fill="white"/>
        </svg>
        <span>HỘI VIÊN</span>
    </div>
    @endif
    
    <div class="book-cover">
        @if($book->hinh_anh)
            @php
                // Use model accessor to normalize different stored path formats
                $imageUrl = $book->image_url;
                $imageUrl .= (str_contains($imageUrl, '?') ? '&' : '?') . 't=' . ($book->updated_at ? $book->updated_at->timestamp : time());
            @endphp
            <img src="{{ $imageUrl }}" 
                 alt="{{ $book->ten_sach }}" 
                 style="width: 160px; height: 240px; object-fit: cover; border-radius: 8px;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <svg width="160" height="240" viewBox="0 0 160 240" style="display: none;">
                <rect width="160" height="240" fill="{{ $randomColor }}" rx="8"/>
                <text x="50%" y="50%" text-anchor="middle" fill="white" font-size="14" font-family="Poppins" font-weight="600">
                    {{ Str::limit($book->ten_sach, 30) }}
                </text>
            </svg>
        @else
            <svg width="160" height="240" viewBox="0 0 160 240">
                <rect width="160" height="240" fill="{{ $randomColor }}" rx="8"/>
                <text x="50%" y="50%" text-anchor="middle" fill="white" font-size="14" font-family="Poppins" font-weight="600">
                    {{ Str::limit($book->ten_sach, 30) }}
                </text>
            </svg>
        @endif
    </div>
    
    <p style="font-weight: 600;">{{ Str::limit($book->ten_sach, 40) }}</p>
    @if($book->tac_gia)
        <p style="font-size: 0.85em; color: #666; margin-top: 4px;">{{ $book->tac_gia }}</p>
    @endif
</div>











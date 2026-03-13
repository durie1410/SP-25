<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chính sách giá mượn sách
    |--------------------------------------------------------------------------
    |
    | File này chứa các cấu hình về chính sách giá cho dịch vụ mượn sách
    |
    */

    // Phí thuê sách
    'rental' => [
        // Tỷ lệ phí thuê mỗi ngày (tính theo % giá sách)
        'daily_rate' => env('RENTAL_DAILY_RATE', 0.01), // 1% mỗi ngày
        
        // Tỷ lệ phí thuê cho người có thẻ độc giả (nếu có)
        'daily_rate_with_card' => env('RENTAL_DAILY_RATE_WITH_CARD', 0.005), // 0.5% mỗi ngày
        
        // Áp dụng phí thuê cho tất cả sách (không phân biệt tình trạng)
        'only_for_new_books' => false,
        
        // Điều kiện sách được tính phí thuê (null = tất cả sách)
        'eligible_condition' => null, // Áp dụng cho tất cả sách
        
        // Mô tả chi tiết
        'description_detail' => 'Phí thuê được tính dựa trên giá sách và số ngày mượn, áp dụng cho tất cả sách không phân biệt tình trạng (Mới, Tốt, Trung bình, Cũ).',
        
        // Công thức tính
        'formula' => 'Phí thuê = Giá sách × 1% × Số ngày mượn',
        
        // Làm tròn
        'round_to' => 1000, // Làm tròn đến hàng nghìn
    ],

    // Tiền cọc
    'deposit' => [
        // Tiền cọc = giá sách (1:1)
        'rate' => 1.0, // 100% giá sách
        
        // Tỷ lệ tiền cọc theo tình trạng sách (nếu muốn thay đổi)
        'by_condition' => [
            'Moi' => 1.0,        // 100% giá sách
            'Tot' => 1.0,         // 100% giá sách
            'Trung binh' => 1.0,  // 100% giá sách
            'Cu' => 1.0,          // 100% giá sách
        ],
        
        // Tỷ lệ tiền cọc cho người có thẻ độc giả (nếu có)
        'with_card_discount' => 0.0, // Không giảm
        
        // Mô tả chi tiết
        'description_detail' => 'Tiền cọc bằng 100% giá sách, được thu khi mượn sách. Tiền cọc sẽ được hoàn trả đầy đủ khi trả sách đúng hạn và sách còn nguyên vẹn, không bị hư hỏng.',
        
        // Công thức tính
        'formula' => 'Tiền cọc = Giá sách × 100%',
        
        // Điều kiện hoàn cọc
        'refund_conditions' => [
            'Trả sách đúng hạn',
            'Sách còn nguyên vẹn, không bị hư hỏng',
            'Không mất trang, không viết vẽ',
            'Hoàn cọc trong vòng 3-5 ngày làm việc',
        ],
    ],

    // Phí vận chuyển
    'shipping' => [
        // Địa chỉ thư viện (điểm xuất phát)
        'library_address' => env('LIBRARY_ADDRESS', 'Cao đẳng FPT Polytechnic Hà Nội, Tòa nhà FPT Polytechnic, P. Trịnh Văn Bô, Xuân Phương, Nam Từ Liêm, Hà Nội, Việt Nam'),
        
        // Phí vận chuyển mặc định (VNĐ)
        'default_fee' => env('SHIPPING_DEFAULT_FEE', 20000), // 20,000 VNĐ/đơn (mặc định)
        
        // Số km miễn phí
        'free_km' => env('SHIPPING_FREE_KM', 5),
        
        // Giá mỗi km (VNĐ) sau khi hết km miễn phí
        'price_per_km' => env('SHIPPING_PRICE_PER_KM', 5000), // 5.000₫/km từ km thứ 6 trở đi
        
        // Phí vận chuyển tối thiểu
        'min_fee' => env('SHIPPING_DEFAULT_FEE', 20000), // Tối thiểu = phí mặc định
        
        // Phí vận chuyển tối đa (nếu có)
        'max_fee' => null,
    ],

    // Phí phạt
    'fines' => [
        // Phí trả muộn
        'late_return' => [
            // Phí mỗi ngày trả muộn (VNĐ) - tính theo cuốn sách
            'min_daily_rate_per_book' => env('LATE_RETURN_MIN_RATE', 3000), // 3,000 VNĐ/ngày/cuốn
            'max_daily_rate_per_book' => env('LATE_RETURN_MAX_RATE', 5000), // 5,000 VNĐ/ngày/cuốn
            'default_daily_rate_per_book' => env('LATE_RETURN_DEFAULT_RATE', 4000), // 4,000 VNĐ/ngày/cuốn (mặc định)
            
            // Phí mỗi ngày trả muộn (VNĐ) - DEPRECATED: chỉ dùng khi không có per_book
            'daily_rate' => env('LATE_RETURN_DAILY_RATE', 4000), // 4,000 VNĐ/ngày/cuốn
            
            // Thời gian chờ trước khi tính phạt (ngày)
            'grace_period' => 0, // Tính phạt ngay từ ngày đầu tiên quá hạn
            
            // Số ngày trễ để coi là "trễ lâu" (sẽ khóa tài khoản)
            'long_overdue_days' => env('LONG_OVERDUE_DAYS', 15), // 15 ngày trễ = trễ lâu
            
            // Mô tả
            'description' => 'Phí trả muộn được tính từ ngày đầu tiên quá hạn, mỗi ngày quá hạn sẽ bị phạt 3,000 - 5,000 VNĐ/cuốn.',
            
            // Công thức tính
            'formula' => 'Phí trả muộn = Số ngày quá hạn × (3,000 - 5,000 VNĐ/ngày/cuốn)',
            
            // Ví dụ
            'example' => 'Nếu trả muộn 3 ngày với 2 cuốn sách: 3 ngày × 4,000 VNĐ/ngày × 2 cuốn = 24,000 VNĐ',
            
            // Lưu ý
            'note' => 'Phí trả muộn được tính từ ngày đầu tiên quá hạn, không có thời gian miễn phí. Trễ quá 15 ngày sẽ bị khóa tài khoản.',
        ],
        
        // Phí làm hỏng sách
        'damaged_book' => [
            // Tỷ lệ phạt theo loại sách và tình trạng
            'by_book_type' => [
                'quy' => [
                    'rate' => 1.0, // 100% giá sách (sách quý)
                    'description' => 'Sách quý: Phạt 100% giá sách',
                    'formula' => 'Phí = Giá sách × 100%',
                ],
                'binh_thuong' => [
                    'by_condition' => [
                        'Moi' => 0.8,      // 80% giá sách
                        'Tot' => 0.8,      // 80% giá sách
                        'Trung binh' => 0.7, // 70% giá sách
                        'Cu' => 0.7,       // 70% giá sách
                        'Hong' => 0.7,     // 70% giá sách
                    ],
                    'description' => 'Sách bình thường: Phạt 80% giá sách (nếu mới/tốt) hoặc 70% (nếu trung bình/cũ)',
                    'formula' => 'Phí = Giá sách × 80% (mới/tốt) hoặc × 70% (trung bình/cũ)',
                ],
                'tham_khao' => [
                    'by_condition' => [
                        'Moi' => 0.8,      // 80% giá sách
                        'Tot' => 0.8,      // 80% giá sách
                        'Trung binh' => 0.7, // 70% giá sách
                        'Cu' => 0.7,       // 70% giá sách
                        'Hong' => 0.7,     // 70% giá sách
                    ],
                    'description' => 'Sách tham khảo: Phạt 80% giá sách (nếu mới/tốt) hoặc 70% (nếu trung bình/cũ)',
                    'formula' => 'Phí = Giá sách × 80% (mới/tốt) hoặc × 70% (trung bình/cũ)',
                ],
            ],
            'description' => 'Phí làm hỏng sách được tính dựa trên loại sách và tình trạng sách khi mượn.',
            'note' => 'Mức phạt được xác định dựa trên mức độ hư hỏng và loại sách. Sách quý luôn phạt 100% giá sách.',
        ],
        
        // Phí mất sách
        'lost_book' => [
            // Tỷ lệ phạt theo loại sách và tình trạng (giống như làm hỏng)
            'by_book_type' => [
                'quy' => [
                    'rate' => 1.0, // 100% giá sách (sách quý)
                    'description' => 'Sách quý: Phạt 100% giá sách',
                    'formula' => 'Phí = Giá sách × 100%',
                ],
                'binh_thuong' => [
                    'by_condition' => [
                        'Moi' => 0.8,      // 80% giá sách
                        'Tot' => 0.8,      // 80% giá sách
                        'Trung binh' => 0.7, // 70% giá sách
                        'Cu' => 0.7,       // 70% giá sách
                        'Hong' => 0.7,     // 70% giá sách
                    ],
                    'description' => 'Sách bình thường: Phạt 80% giá sách (nếu mới/tốt) hoặc 70% (nếu trung bình/cũ)',
                    'formula' => 'Phí = Giá sách × 80% (mới/tốt) hoặc × 70% (trung bình/cũ)',
                ],
                'tham_khao' => [
                    'by_condition' => [
                        'Moi' => 0.8,      // 80% giá sách
                        'Tot' => 0.8,      // 80% giá sách
                        'Trung binh' => 0.7, // 70% giá sách
                        'Cu' => 0.7,       // 70% giá sách
                        'Hong' => 0.7,     // 70% giá sách
                    ],
                    'description' => 'Sách tham khảo: Phạt 80% giá sách (nếu mới/tốt) hoặc 70% (nếu trung bình/cũ)',
                    'formula' => 'Phí = Giá sách × 80% (mới/tốt) hoặc × 70% (trung bình/cũ)',
                ],
            ],
            'description' => 'Phí mất sách được tính dựa trên loại sách và tình trạng sách khi mượn.',
            'note' => 'Khi mất sách, độc giả phải bồi thường theo mức phạt tương ứng với loại sách và tình trạng sách khi mượn. Sách quý luôn phạt 100% giá sách.',
        ],
        
        // Thời hạn thanh toán phạt
        'payment_deadline_days' => env('FINE_PAYMENT_DEADLINE_DAYS', 30), // 30 ngày
    ],

    // Các quy định khác
    'rules' => [
        // Số ngày mượn tối thiểu
        'min_borrow_days' => env('MIN_BORROW_DAYS', 1), // 1 ngày
        
        // Số ngày mượn tối đa
        'max_borrow_days' => env('MAX_BORROW_DAYS', 14), // 14 ngày
        
        // Số ngày mượn mặc định
        'default_borrow_days' => env('DEFAULT_BORROW_DAYS', 14), // 14 ngày
        
        // Có cho phép gia hạn không
        'allow_extend' => true,
        
        // Số lần gia hạn tối đa
        'max_extend_times' => 2,
        
        // Số ngày gia hạn mỗi lần
        'extend_days' => 7,
    ],
    
    // Chính sách trả sớm
    'early_return' => [
        // Có cho phép trả sớm không
        'enabled' => true,
        
        // Tỷ lệ hoàn lại phí thuê khi trả sớm (%)
        'min_refund_rate' => env('EARLY_RETURN_MIN_REFUND', 0.20), // 20%
        'max_refund_rate' => env('EARLY_RETURN_MAX_REFUND', 0.30), // 30%
        'default_refund_rate' => env('EARLY_RETURN_DEFAULT_REFUND', 0.25), // 25% (mặc định)
        
        // Số ngày trả sớm tối thiểu để được hoàn lại
        'min_early_days' => 1, // Trả sớm ít nhất 1 ngày
        
        // Mô tả
        'description' => 'Nếu bạn trả sách sớm hơn thời hạn, bạn sẽ được hoàn lại 20% - 30% phí thuê vào ví của mình.',
    ],

    // Mô tả chính sách (hiển thị cho người dùng)
    'description' => [
        'rental' => 'Phí thuê sách được tính theo số ngày mượn. Áp dụng cho tất cả sách (1% giá sách mỗi ngày).',
        'deposit' => 'Tiền cọc bằng 100% giá sách, sẽ được hoàn trả khi trả sách đúng hạn và trong tình trạng tốt.',
        'shipping' => 'Miễn phí vận chuyển 5km đầu. Từ km thứ 6 trở đi, mỗi km thêm 5,000 VNĐ.',
        'late_return' => 'Phí trả muộn: 5,000 VNĐ/ngày, tính từ ngày đầu tiên quá hạn. Không có thời gian miễn phí.',
        'damaged_book' => 'Phí làm hỏng sách: Sách quý phạt 100% giá sách. Sách bình thường/tham khảo: 80% (nếu mới/tốt) hoặc 70% (nếu trung bình/cũ).',
        'lost_book' => 'Phí mất sách: Sách quý phạt 100% giá sách. Sách bình thường/tham khảo: 80% (nếu mới/tốt) hoặc 70% (nếu trung bình/cũ).',
    ],
    
    // Thông tin liên hệ và hỗ trợ
    'support' => [
        'contact' => 'Nếu có thắc mắc về chính sách giá, vui lòng liên hệ với thư viện để được hỗ trợ.',
        'update_date' => 'Chính sách giá có thể được cập nhật theo thời gian. Vui lòng kiểm tra thường xuyên.',
    ],
];

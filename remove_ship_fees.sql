-- Xóa tiền ship khỏi tất cả đơn mượn
UPDATE borrows SET tien_ship = 0;
UPDATE borrow_items SET tien_ship = 0;

-- Cập nhật lại tổng tiền (chỉ còn tiền cọc + tiền thuê, ko ship)
UPDATE borrows SET tong_tien = COALESCE(tien_coc, 0) + COALESCE(tien_thue, 0) + COALESCE(tien_ship, 0);

-- ===============================================
-- SQL: PHÂN QUYỀN STAFF
-- ===============================================
-- Sử dụng khi muốn gán quyền trực tiếp qua database

-- ===============================================
-- 1. Tạo STAFF role nếu chưa có
-- ===============================================
INSERT INTO roles (name, guard_name, created_at, updated_at) 
VALUES ('staff', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE name = name;

-- ===============================================
-- 2. Gán permissions cho STAFF role
-- ===============================================
-- Lấy role_id của staff (thường là 2)
SET @staff_role_id = (SELECT id FROM roles WHERE name = 'staff' LIMIT 1);
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'admin' LIMIT 1);

-- Xóa các permission cũ của staff (nếu có)
DELETE FROM role_has_permissions 
WHERE role_id = @staff_role_id;

-- Thêm permissions cho staff
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, @staff_role_id FROM permissions p
WHERE p.name IN (
    -- Dashboard
    'view-dashboard',
    
    -- Books - Xem, tạo, sửa
    'view-books',
    'create-books',
    'edit-books',
    
    -- Categories - Chỉ xem
    'view-categories',
    
    -- Borrows/Orders - Quản lý đơn hàng
    'view-borrows',
    'create-borrows',
    'edit-borrows',
    'return-books',
    
    -- Reservations
    'view-reservations',
    'confirm-reservations',
    
    -- Readers - Xem
    'view-readers',
    
    -- Reports
    'view-reports',
    'export-reports',
    
    -- Notifications
    'view-notifications',
    
    -- Reviews
    'view-reviews',
    'approve-reviews'
);

-- ===============================================
-- 3. Tạo STAFF USER nếu chưa có
-- ===============================================
INSERT INTO users (name, email, password, role, email_verified_at, created_at, updated_at)
VALUES ('Nhân viên', 'staff@library.com', '$2y$10$abcdefghijklmnopqrstuvwxyz', 'staff', NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    name = 'Nhân viên',
    role = 'staff';

-- ===============================================
-- 4. Gán staff role cho staff user
-- ===============================================
SET @staff_user_id = (SELECT id FROM users WHERE email = 'staff@library.com' LIMIT 1);

-- Xóa các role cũ
DELETE FROM model_has_roles 
WHERE model_type = 'App\\\\Models\\\\User' AND model_id = @staff_user_id;

-- Thêm staff role
INSERT INTO model_has_roles (role_id, model_type, model_id)
VALUES (@staff_role_id, 'App\\Models\\User', @staff_user_id)
ON DUPLICATE KEY UPDATE role_id = role_id;

-- ===============================================
-- 5. Kiểm tra kết quả
-- ===============================================

-- Xem staff role có những permissions nào
SELECT 
    p.name as permission_name,
    p.id,
    r.name as role_name
FROM role_has_permissions rhp
JOIN permissions p ON rhp.permission_id = p.id
JOIN roles r ON rhp.role_id = r.id
WHERE r.name = 'staff'
ORDER BY p.name;

-- Xem staff user có những roles nào
SELECT 
    u.name,
    u.email,
    u.role,
    r.name as role_name
FROM model_has_roles mhr
JOIN users u ON mhr.model_id = u.id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'staff@library.com'
ORDER BY u.name;

-- ===============================================
-- GÁN STAFF ROLE CHO USER CỤ THỀ
-- ===============================================
-- Cách dùng: Thay đổi email hoặc ID của user cần gán

-- Ví dụ 1: Gán cho user có email
-- SET @user_id = (SELECT id FROM users WHERE email = 'user_email@example.com' LIMIT 1);
-- INSERT INTO model_has_roles (role_id, model_type, model_id)
-- VALUES (@staff_role_id, 'App\\Models\\User', @user_id)
-- ON DUPLICATE KEY UPDATE role_id = role_id;
-- UPDATE users SET role = 'staff' WHERE id = @user_id;

-- Ví dụ 2: Gán cho user có ID = 2
-- INSERT INTO model_has_roles (role_id, model_type, model_id)
-- VALUES (@staff_role_id, 'App\\Models\\User', 2)
-- ON DUPLICATE KEY UPDATE role_id = role_id;
-- UPDATE users SET role = 'staff' WHERE id = 2;

-- ===============================================
-- LOẠI BỎ STAFF ROLE KHỎI USER
-- ===============================================
-- Cách dùng: Thay đổi email hoặc ID của user cần loại bỏ

-- Ví dụ: Loại bỏ staff role khỏi user có email
-- SET @user_id = (SELECT id FROM users WHERE email = 'user_email@example.com' LIMIT 1);
-- DELETE FROM model_has_roles 
-- WHERE model_type = 'App\\Models\\User' AND model_id = @user_id;
-- UPDATE users SET role = 'user' WHERE id = @user_id;

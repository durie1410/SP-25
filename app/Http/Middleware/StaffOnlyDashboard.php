<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffOnlyDashboard
{
    /**
     * Staff chỉ được vào ADMIN PAGES
     * Block staff khỏi tất cả user pages (home, books, account, etc)
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Nếu là admin, cho qua bình thường
        if ($user && $user->isAdmin()) {
            return $next($request);
        }
        
        // Nếu là staff
        if ($user && $user->isStaff()) {
            $path = $request->path();
            
            // Block staff khỏi các trang quản lý người dùng
            $blockedPaths = [
                'admin/user-management',
                'admin/users',
                'admin/librarians',
            ];
            
            foreach ($blockedPaths as $blockedPath) {
                if (strpos($path, $blockedPath) === 0) {
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Bạn không có quyền thực hiện hành động này. (Yêu cầu: view-users)');
                }
            }
            
            // Cho phép vào admin pages khác
            if (strpos($path, 'admin') === 0) {
                return $next($request);
            }
            
            // Cho phép logout
            if ($path === 'logout') {
                return $next($request);
            }
            
            // Block tất cả các trang khác (home, books, account, etc)
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Nhân viên chỉ được truy cập Admin Dashboard.');
        }
        
        // User thường - cho qua bình thường
        return $next($request);
    }
}


<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnlyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = auth()->user();

        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền truy cập trang này. Chỉ quản trị viên mới có quyền truy cập.');
        }

        return $next($request);
    }
}

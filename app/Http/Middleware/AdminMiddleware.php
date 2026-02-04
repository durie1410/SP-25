<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    /**
     * Handle an incoming request.
     * Chỉ cho phép admin truy cập
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = auth()->user();
        
        // Cho phép admin và staff truy cập
        if (!$user->isAdmin() && !$user->isStaff()) {
            abort(403, 'Bạn không có quyền truy cập trang này. Chỉ quản trị viên và nhân viên mới có quyền truy cập.');
        }

        return $next($request);
    }
}

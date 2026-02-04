<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LibrarianMiddleware
{
    /**
     * Allow only users with librarian role.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = auth()->user();

        $isLibrarian = false;
        if (method_exists($user, 'hasRole')) {
            $isLibrarian = $user->hasRole('librarian');
        }
        $isLibrarian = $isLibrarian || (($user->role ?? null) === 'librarian');

        if (!$isLibrarian) {
            abort(403, 'Bạn không có quyền truy cập. Chỉ Thủ thư (Librarian) mới được phép.');
        }

        return $next($request);
    }
}


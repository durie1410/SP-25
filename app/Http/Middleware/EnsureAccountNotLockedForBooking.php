<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAccountNotLockedForBooking
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isLocked()) {
            $message = 'Tài khoản của bạn đã bị khóa do vi phạm quy định của LibNet. Vui lòng liên hệ admin để được mở khóa.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 423);
            }

            return redirect()->back()->with('error', $message);
        }

        return $next($request);
    }
}

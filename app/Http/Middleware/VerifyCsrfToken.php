<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */

    protected $except = [
        'gemini-chat/*',
        '/momo/notify',
        '/momo/ipn',
        '/borrows/momo/ipn',
        '/admin/borrows/fine-momo/ipn',
    ];
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProdi
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('prodi')->check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            // Redirect to listings page with error message
            session()->flash('error', 'Chỉ Admin mới có quyền truy cập trang này!');
            return redirect()->route('listings');
        }

        return $next($request);
    }
}

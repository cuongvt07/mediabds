<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || ! $user->isAdmin()) {
            session()->flash('error', 'Chỉ Admin mới có quyền truy cập trang này!');
            return redirect()->route('site.home');
        }

        return $next($request);
    }
}

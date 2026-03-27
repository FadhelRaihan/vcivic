<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Jika user belum login atau role-nya tidak sesuai dengan yang diminta, tolak!
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403, 'Akses Ditolak: Halaman ini khusus untuk ' . ucfirst($role));
        }

        return $next($request);
    }
}
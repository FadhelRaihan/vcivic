<?php
/**
 * Middleware custom untuk membatasi akses jalur rute hanya untuk tipe role tertentu (admin, dosen, mahasiswa).
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Memproses intersepsi (cegat) request masuk sebelum mencapai controller; validasi kecocokan role.
     * Input: Request asal, Closure chain berikutnya, role (string target). Output: Response dilanjut/abort 403.
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
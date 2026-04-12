<?php
/**
 * Response kustom untuk logout agar langsung redirect ke halaman login tanpa melewati redirect chain default.
 */

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Mengarahkan user yang baru logout langsung ke halaman login.
     * Menghindari redirect chain default: /logout → / → /dashboard → /login.
     */
    public function toResponse($request)
    {
        return redirect('/login');
    }
}

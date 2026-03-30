<?php
/**
 * Middleware dasar Inertia.js untuk membagikan data data (seperti pesan flash, user login) ke komponen Vue Frontend.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Menentukan versi cache/aset saat ini agar frontend memuat ulang file JS jika ada perubahan.
     * Input: Request objek. Output: String hash versi aset.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Tentukan dan kirim ('share') properti global apa saja yang selalu tersedia di setiap file Vue secara default.
     * Input: Objek Request. Output: Array berisi fungsi/variabel yang disebar ke semua page inertia.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}

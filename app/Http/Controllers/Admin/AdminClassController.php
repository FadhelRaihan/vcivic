<?php
/**
 * Controller ini berfungsi untuk mengelola data kelas oleh admin, seperti melihat daftar kelas dan dosen terkait.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminClassController extends Controller
{
    /**
     * Menampilkan daftar semua kelas beserta dosen pengajarnya dengan fitur fitur pencarian.
     * Input: Request object. Output: Inertia response (Render halaman Admin/Classes).
     */
    public function index(Request $request)
    {
        $query = Team::where('is_template', false)->with('owner')->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('join_code', 'like', "%{$search}%")
                  ->orWhereHas('owner', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%");
                  });
        }

        $classes = $query->get();

        $dosens = User::where('role', 'dosen')->orderBy('username', 'asc')->get();

        return Inertia::render('Admin/Classes', [
            'classes' => $classes,
            'dosens' => $dosens,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Menyimpan data kelas baru ke dalam sistem dan menghasilkan kode gabung (join code) otomatis.
     * Input: Request form kelas. Output: Redirect dengan status sukses.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        Team::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'personal_team' => false,
            'join_code' => strtoupper(Str::random(6))
        ]);

        return back()->with('success', 'Kelas berhasil dibuat.');
    }

    /**
     * Memperbarui detail kelas yang sudah ada, seperti nama kelas dan dosen penanggung jawab.
     * Input: Request dan Object Team (kelas). Output: Redirect dengan peringatan sukses.
     */
    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $team->update([
            'name' => $request->name,
            'user_id' => $request->user_id,
        ]);

        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Menghapus spesifik kelas secara permanen dari sistem.
     * Input: Object Team. Output: Redirect dengan status sukses.
     */
    public function destroy(Team $team)
    {
        $team->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
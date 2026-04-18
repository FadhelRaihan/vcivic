<?php
/**
 * Controller untuk mengelola data pertemuan secara global oleh role Admin.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Team; // Untuk daftar pilihan kelas
use Inertia\Inertia;

class AdminMeetingController extends Controller
{
    /**
     * Menampilkan daftar semua pertemuan dengan informasi kelas serta menyediakan pencarian.
     * Input: Request object opsional pencarian. Output: Render komponen halaman Admin/Meetings.
     */
    public function index(Request $request)
    {
        // 1. Ambil data pertemuan yang bukan bagian dari template kurikulum
        $query = Meeting::whereHas('team', function($q) {
            $q->where('is_template', false);
        })->with('team')->orderBy('meeting_number', 'asc');

        // Fitur Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('meeting_number', 'like', "%{$search}%")
                    ->orWhereHas('team', function ($teamQuery) use ($search) {
                        $teamQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $meetings = $query->get();

        // 2. Ambil semua kelas (kecuali template) untuk pilihan di dropdown form Create
        $classes = Team::where('is_template', false)->orderBy('name', 'asc')->get();

        return Inertia::render('Admin/Meetings', [
            'meetings' => $meetings,
            'classes' => $classes,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Menyimpan data pertemuan baru yang ditambahkan oleh admin untuk kelas tertentu.
     * Input: Request HTTP form meeting. Output: Redirect dengan notifikasi berhasil.
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'meeting_number' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Meeting::create([
            'team_id' => $request->team_id,
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pertemuan berhasil dibuat.');
    }

    /**
     * Memperbarui detail informasi pertemuan seperti urutan, judul, dan deskripsi.
     * Input: Request HTTP form edit dan ID Meeting. Output: Redirect ke halaman asal dengan sukses.
     */
    public function update(Request $request, Meeting $meeting)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'meeting_number' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $meeting->update([
            'team_id' => $request->team_id,
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pertemuan berhasil diperbarui.');
    }

    /**
     * Menghapus secara permanen data pertemuan (beserta content/discuss secara relasional).
     * Input: ID Meeting yang akan dihapus. Output: Pesan berhasil via redirect back.
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return back()->with('success', 'Pertemuan berhasil dihapus.');
    }
}
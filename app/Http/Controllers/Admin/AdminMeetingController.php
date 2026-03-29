<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Team; // Untuk daftar pilihan kelas
use Inertia\Inertia;

class AdminMeetingController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data pertemuan beserta nama kelasnya
        $query = Meeting::with('team')->latest();

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

        // 2. Ambil semua kelas untuk pilihan di dropdown form Create
        $classes = Team::orderBy('name', 'asc')->get();

        return Inertia::render('Admin/Meetings', [
            'meetings' => $meetings,
            'classes' => $classes,
            'filters' => $request->only('search')
        ]);
    }

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

    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return back()->with('success', 'Pertemuan berhasil dihapus.');
    }
}
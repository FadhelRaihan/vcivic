<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $classes = $request->user()->teams()->wherePivot('status', 'approved')->get();
        
        return Inertia::render('Mahasiswa/Dashboard', ['classes' => $classes]);
    }

    public function joinClass(Request $request)
    {
        $request->validate(['join_code' => 'required|string']);

        $team = Team::where('join_code', $request->join_code)->first();

        if (!$team) {
            return back()->withErrors(['join_code' => 'Kode kelas tidak ditemukan.']);
        }

        if ($team->users()->where('user_id', $request->user()->id)->exists()) {
            return back()->withErrors(['join_code' => 'Anda sudah terdaftar di kelas ini.']);
        }
        $team->users()->attach($request->user()->id, ['status' => 'pending']);

        return back()->with('success', 'Berhasil meminta bergabung. Silakan tunggu persetujuan dosen.');
    }

    public function showMeeting(Meeting $meeting)
    {
        $meeting->load(['contents', 'discussions.user', 'discussions.replies.user']);

        return Inertia::render('Mahasiswa/Meetings/Show', [
            'meeting' => $meeting
        ]);
    }
}
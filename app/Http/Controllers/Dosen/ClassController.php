<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = Team::where('user_id', $request->user()->id)->latest()->get();
        return Inertia::render('Dosen/Classes/Index', ['classes' => $classes]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Team::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'personal_team' => false,
            'join_code' => strtoupper(Str::random(6))
        ]);

        return back()->with('success', 'Kelas berhasil dibuat.');
    }

    public function update(Request $request, Team $team)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $team->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Nama kelas berhasil diperbarui.');
    }

    public function manage(Team $team)
    {
        $team->load(['meetings.contents']);

        return Inertia::render('Dosen/Classes/Manage', [
            'team' => $team
        ]);
    }

    public function show(Team $team)
    {
        $team->load([
            'meetings.contents',
            'meetings.discussions.user',
            'meetings.discussions.replies.user',
            'users'
        ]);

        return Inertia::render('Dosen/Classes/Show', [
            'team' => $team
        ]);
    }

    public function approveStudent(Team $team, $user_id)
    {
        $team->users()->updateExistingPivot($user_id, ['status' => 'approved']);
        return back()->with('success', 'Mahasiswa berhasil disetujui masuk ke kelas.');
    }
}
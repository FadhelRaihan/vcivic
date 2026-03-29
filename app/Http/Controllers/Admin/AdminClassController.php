<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;

class AdminClassController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with('owner')->latest();

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

    public function destroy(Team $team)
    {
        $team->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
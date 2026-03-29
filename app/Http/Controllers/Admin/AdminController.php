<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'mahasiswa' => User::where('role', 'mahasiswa')->count(),
                'dosen' => User::where('role', 'dosen')->count(),
                'kelas' => Team::count(),
            ]
        ]);
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->get();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:dosen,mahasiswa,admin',
            'nim_nip' => 'nullable|string|max:50|unique:users,nim_nip',
        ], [
            'name.unique' => 'Nama/Username ini sudah terdaftar.',
            'nim_nip.unique' => 'NIM/NIP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.'
        ]);

        User::create([
            'username' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'nim_nip' => $request->nim_nip,
        ]);

        return back()->with('success', 'Pengguna berhasil didaftarkan.');
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:dosen,mahasiswa,admin',
            'nim_nip' => 'required|string|max:50|unique:users,nim_nip,' . $user->id,
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
        }

        $request->validate($rules, [
            'name.unique' => 'Nama/Username ini sudah terdaftar.',
            'nim_nip.unique' => 'NIM/NIP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.'
        ]);

        $data = [
            'username' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'nim_nip' => $request->nim_nip,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
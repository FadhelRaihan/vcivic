<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/portal-admin', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return Inertia::render('Auth/AdminLogin');
})->name('admin.login.view');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $role = $request->user()->role;
        if ($role === 'admin')
            return redirect()->route('admin.dashboard');
        if ($role === 'dosen')
            return redirect()->route('dosen.classes.index');
        return redirect()->route('mahasiswa.dashboard');
    })->name('dashboard');


    // ==========================================
    // ADMIN
    // ==========================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // --- DASHBOARD OVERVIEW ---
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

        // --- MANAJEMEN PENGGUNA --- 
        Route::get('/users', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'destroy'])->name('users.destroy');

        // --- MANAJEMEN KELAS ---
        Route::get('/classes', [\App\Http\Controllers\Admin\AdminClassController::class, 'index'])->name('classes.index');
        Route::post('/classes', [\App\Http\Controllers\Admin\AdminClassController::class, 'store'])->name('classes.store');
        Route::put('/classes/{team}', [\App\Http\Controllers\Admin\AdminClassController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{team}', [\App\Http\Controllers\Admin\AdminClassController::class, 'destroy'])->name('classes.destroy');

        // --- MANAJEMEN PERTEMUAN ---
        Route::get('/meetings', [\App\Http\Controllers\Admin\AdminMeetingController::class, 'index'])->name('meetings.index');
        Route::post('/meetings', [\App\Http\Controllers\Admin\AdminMeetingController::class, 'store'])->name('meetings.store');
        Route::put('/meetings/{meeting}', [\App\Http\Controllers\Admin\AdminMeetingController::class, 'update'])->name('meetings.update');
        Route::delete('/meetings/{meeting}', [\App\Http\Controllers\Admin\AdminMeetingController::class, 'destroy'])->name('meetings.destroy');
    });


    // ==========================================
    // DOSEN
    // ==========================================
    Route::middleware(['role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        // CRUD Kelas (Teams)
        Route::get('/classes', [\App\Http\Controllers\Dosen\ClassController::class, 'index'])->name('classes.index');
        Route::post('/classes', [\App\Http\Controllers\Dosen\ClassController::class, 'store'])->name('classes.store');
        Route::put('/classes/{team}', [\App\Http\Controllers\Dosen\ClassController::class, 'update'])->name('classes.update');

        // Edit Kelas 
        Route::get('/classes/{team}/manage', [\App\Http\Controllers\Dosen\ClassController::class, 'manage'])->name('classes.manage');

        // Lihat Kelas
        Route::get('/classes/{team}/show', [\App\Http\Controllers\Dosen\ClassController::class, 'show'])->name('classes.show');

        // Acc Mahasiswa yang masuk via kode
        Route::post('/classes/{team}/approve/{user_id}', [\App\Http\Controllers\Dosen\ClassController::class, 'approveStudent'])->name('classes.approve');

        // CRUD Pertemuan di dalam Kelas
        Route::post('/classes/{team}/meetings', [\App\Http\Controllers\Dosen\MeetingController::class, 'store'])->name('meetings.store');
        Route::put('/classes/{team}/meetings/{meeting}', [\App\Http\Controllers\Dosen\MeetingController::class, 'update'])->name('meetings.update');
        Route::get('/classes/{team}/meetings/{meeting}', [\App\Http\Controllers\Dosen\MeetingController::class, 'show'])->name('meetings.show');
        Route::post('/classes/{team}/meetings/{meeting}/contents', [\App\Http\Controllers\Dosen\MeetingController::class, 'storeContent'])->name('meetings.contents.store');
        Route::post('/classes/{team}/meetings/{meeting}/contents/{content}', [\App\Http\Controllers\Dosen\MeetingController::class, 'updateContent'])->name('meetings.contents.update');
        Route::delete('/classes/{team}/meetings/{meeting}/contents/{content}', [\App\Http\Controllers\Dosen\MeetingController::class, 'destroyContent'])->name('meetings.contents.destroy');
        Route::delete('/classes/{team}/meetings/{meeting}', [\App\Http\Controllers\Dosen\MeetingController::class, 'destroy'])->name('meetings.destroy');
    });


    // ==========================================
    // MAHASISWA
    // ==========================================
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        // Dashboard utama (Lihat kelas yang diikuti)
        Route::get('/dashboard', [\App\Http\Controllers\Mahasiswa\StudentController::class, 'index'])->name('dashboard');

        // Join kelas pakai kode
        Route::post('/join-class', [\App\Http\Controllers\Mahasiswa\StudentController::class, 'joinClass'])->name('join');

        // Lihat isi pertemuan
        Route::get('/meetings/{meeting}', [\App\Http\Controllers\Mahasiswa\StudentController::class, 'showMeeting'])->name('meetings.show');
    });


    // ==========================================
    // DISKUSI
    // ==========================================
    Route::post('/meetings/{meeting}/discussions', [\App\Http\Controllers\DiscussionController::class, 'store'])->name('discussions.store');
    Route::post('/discussions/{discussion}/replies', [\App\Http\Controllers\DiscussionController::class, 'storeReply'])->name('discussions.replies.store');
    Route::delete('/discussions/{discussion}', [\App\Http\Controllers\DiscussionController::class, 'destroy'])->name('discussions.destroy');
});

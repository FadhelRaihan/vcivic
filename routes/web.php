<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

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
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminController::class, 'store'])->name('users.store'); // Untuk mendaftarkan dosen/mahasiswa
    });


    // ==========================================
    // DOSEN
    // ==========================================
    Route::middleware(['role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        // CRUD Kelas (Teams)
        Route::get('/classes', [\App\Http\Controllers\Dosen\ClassController::class, 'index'])->name('classes.index');
        Route::post('/classes', [\App\Http\Controllers\Dosen\ClassController::class, 'store'])->name('classes.store');
        Route::get('/classes/{team}/manage', [\App\Http\Controllers\Dosen\ClassController::class, 'manage'])->name('classes.manage');

        // Acc Mahasiswa yang masuk via kode
        Route::post('/classes/{team}/approve/{user_id}', [\App\Http\Controllers\Dosen\ClassController::class, 'approveStudent'])->name('classes.approve');

        // CRUD Pertemuan di dalam Kelas
        Route::post('/classes/{team}/meetings', [\App\Http\Controllers\Dosen\MeetingController::class, 'store'])->name('meetings.store');

        Route::get('/classes/{team}/meetings/{meeting}', [\App\Http\Controllers\Dosen\MeetingController::class, 'show'])->name('meetings.show');
        Route::post('/classes/{team}/meetings/{meeting}/contents', [\App\Http\Controllers\Dosen\MeetingController::class, 'storeContent'])->name('meetings.contents.store');
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

});

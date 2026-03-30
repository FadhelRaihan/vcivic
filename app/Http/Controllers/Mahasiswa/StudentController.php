<?php
/**
 * Controller utama bagi role Mahasiswa untuk mengelola kelas, mencari kelas, belajar, dan mengerjakan kuis.
 */

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\Quiz;
use App\Models\StudentGrade;
use Inertia\Inertia;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Menampilkan dashboard utama Mahasiswa beserta daftar kelas-kelas yang telah/sedang mereka ikuti.
     * Input: Request HTTP user auth. Output: Component Render `Mahasiswa/Dashboard`.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $teams = $user->teams()->withPivot('status')->latest()->get();

        return Inertia::render('Mahasiswa/Dashboard', [
            'teams' => $teams
        ]);
    }

    /**
     * Mengirimkan permintaan bergabung ke kelas menggunakan kode unik 'join_code'.
     * Input: Request form (join_code) dari mahasiswa. Output: Status respon ditolak/berhasil menunggu acc.
     */
    public function joinClass(Request $request)
    {
        $request->validate(['join_code' => 'required|string']);
        $team = Team::where('join_code', strtoupper($request->join_code))->first();

        if (!$team) {
            return back()->withErrors(['join_code' => 'Kode kelas tidak ditemukan.']);
        }
        if ($team->users()->where('user_id', auth()->id())->exists()) {
            return back()->withErrors(['join_code' => 'Anda sudah bergabung atau sedang menunggu persetujuan.']);
        }

        $team->users()->attach(auth()->id(), [
            'id' => Str::uuid(), 
            'status' => 'pending'
        ]);
        return back()->with('success', 'Berhasil meminta bergabung. Menunggu persetujuan Dosen.');
    }

    /**
     * Menampilkan detail silabus pertemuan kelas dan menghitung total progress belajar mahasiswa saat itu.
     * Input: Request object untuk validasi status dan Objek Kelas (Team). Output: Tampilan info struktur kelas.
     */
    public function showClass(Request $request, Team $team)
    {
        $user = $request->user();

        $membership = $team->users()->where('user_id', $user->id)->first();
        if (!$membership) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }

        if ($membership->membership->status === 'pending') {
            return Inertia::render('Mahasiswa/WaitingApproval', ['team' => $team]);
        }

        $team->load([
            'meetings' => function($query) { $query->orderBy('meeting_number', 'asc'); },
            'meetings.contents',
            'meetings.quiz'
        ]);

        $totalMeetings = $team->meetings->count();
        $quizIds = $team->meetings->pluck('quiz.id')->filter();
        
        $completedQuizIds = StudentGrade::where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds)
            ->pluck('quiz_id')
            ->toArray();

        $completedMeetings = count($completedQuizIds);
        $progressPercentage = $totalMeetings > 0 ? round(($completedMeetings / $totalMeetings) * 100) : 0;

        return Inertia::render('Mahasiswa/Classes/Show', [
            'team' => $team,
            'progress' => [
                'percentage' => $progressPercentage,
                'completed' => $completedMeetings,
                'total' => $totalMeetings
            ],
            'completedQuizzes' => $completedQuizIds
        ]);
    }

    /**
     * Menampilkan materi pembelajaran (konten, diskusi, kuis aktif) untuk suatu pertemuan spesifik.
     * Input: Objek Model 'Meeting' target. Output: Render Vue Mahasiswa/Meetings/Show.
     */
    public function showMeeting(Meeting $meeting)
    {
        $meeting->load([
            'contents', 
            'discussions' => function($q) { $q->oldest(); },
            'discussions.user',
            'quiz.questions'
        ]);

        $grade = null;
        if ($meeting->quiz) {
            $grade = StudentGrade::where('user_id', auth()->id())->where('quiz_id', $meeting->quiz->id)->first();
        }

        return Inertia::render('Mahasiswa/Meetings/Show', [
            'meeting' => $meeting,
            'grade' => $grade
        ]);
    }

    /**
     * Memproses logika perhitungan penilaian dan memvalidasi jawaban kuis pilihan ganda mahasiswa.
     * Input: Array jawaban kuis dari Request, entity Quiz target. Output: Insert skor -> Return Notif Back.
     */
    public function submitQuiz(Request $request, Meeting $meeting, Quiz $quiz)
    {
        $request->validate(['answers' => 'required|array']);
        $exists = StudentGrade::where('user_id', auth()->id())->where('quiz_id', $quiz->id)->exists();
        if ($exists) return back()->with('error', 'Anda sudah mengerjakan kuis ini.');

        $questions = $quiz->questions;
        $totalQuestions = $questions->count();
        $correctAnswers = 0;

        foreach ($questions as $question) {
            $studentAnswer = $request->answers[$question->id] ?? null;
            if ($studentAnswer === $question->correct_answer) $correctAnswers++;
        }

        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;

        StudentGrade::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'score' => $score
        ]);

        return back()->with('success', 'Kuis berhasil diselesaikan! Nilai Anda: ' . $score);
    }
}
<?php
/**
 * Controller untuk Dosen mengelola kelas-kelas (Teams) yang mereka ajar.
 */

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    /**
     * Menampilkan seluruh kelas di mana dosen login sebagai pemilik/pengajar.
     * Input: Request HTTP terkait user. Output: View Dosen/Classes/Index.
     */
    public function index(Request $request)
    {
        $classes = Team::where('user_id', $request->user()->id)->latest()->get();
        return Inertia::render('Dosen/Classes/Index', ['classes' => $classes]);
    }

    /**
     * Dosen membuat kelas baru dengan men-generate 'join code' otomatis bagi mahasiswa.
     * Input: Request dengan form nama kelas. Output: Redirect status keberhasilan buat kelas.
     */
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

    /**
     * Menyimpan pembaruan profil atau nama dari kelas tersebut.
     * Input: Data request berupa nama kelas. Output: Mengarahkan kembali pada page list/manage kelas.
     */
    public function update(Request $request, Team $team)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $team->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Nama kelas berhasil diperbarui.');
    }

    /**
     * Menampilkan halaman manajemen pertemuan dan konten di dalam satu kelas spesifik.
     * Input: Objek Team/Kelas. Output: Merender Page Dosen/Classes/Manage dengan relasi meeting.
     */
    public function manage(Team $team)
    {
        $team->load(['meetings.contents']);

        return Inertia::render('Dosen/Classes/Manage', [
            'team' => $team
        ]);
    }

    /**
     * Melihat detail kelas termasuk daftar anggota mahasiswa dan nilai-nilai kuis mereka.
     * Input: Team entitas (Kelas). Output: Merender View Dosen/Classes/Show.
     */
    public function show(Team $team)
    {
        $team->load([
            'meetings.contents',
            'meetings.quiz.studentGrades',
            'users'
        ]);

        return Inertia::render('Dosen/Classes/Show', [
            'team' => $team
        ]);
    }

    /**
     * Menyetujui pendaftaran mahasiswa ke dalam sebuah kelas berdasarkan permintaan gabung.
     * Input: Team, ID Mahasiswa tujuan. Output: Mengembalikan rute lama (refresh).
     */
    public function approveStudent(Team $team, $user_id)
    {
        $team->users()->updateExistingPivot($user_id, ['status' => 'approved']);
        return back()->with('success', 'Mahasiswa berhasil disetujui masuk ke kelas.');
    }

    /**
     * Mengekspor/mengunduh keseluruhan rekap nilai tugas mahasiswa di kelas menjadi file Excel.
     * Input: Entitas Team spesifik. Output: Respon file attachment (vnd.ms-excel).
     */
    public function exportRekap(Team $team)
    {
        $team->load([
            'meetings.quiz.studentGrades',
            'users'
        ]);

        $activeStudents = $team->users->where('membership.status', 'approved');

        $fileName = 'Rekap_Nilai_' . Str::slug($team->name) . '_' . date('Ymd') . '.xls';

        $headers = array(
            "Content-type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function () use ($activeStudents, $team) {
            echo '<html><head><meta charset="utf-8"></head><body>';

            echo '<table border="0">';
            echo '<tr><td colspan="5" style="font-size: 16px; font-weight: bold;">REKAPITULASI NILAI KELAS</td></tr>';
            echo '<tr><td colspan="5">Mata Kuliah: <b>' . $team->name . '</b></td></tr>';
            echo '<tr><td colspan="5">Tanggal Unduh: ' . date('d M Y, H:i') . ' WIB</td></tr>';
            echo '<tr><td colspan="5"></td></tr>';
            echo '</table>';

            echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';

            echo '<thead><tr>';
            $headerStyle = 'background-color: #194872; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000000;';
            echo '<th style="' . $headerStyle . '">No</th>';
            echo '<th style="' . $headerStyle . '">NIM/NIP</th>';
            echo '<th style="' . $headerStyle . '">Nama Mahasiswa</th>';

            foreach ($team->meetings as $meeting) {
                echo '<th style="' . $headerStyle . '">Pertemuan ' . $meeting->meeting_number . '</th>';
            }
            echo '<th style="' . $headerStyle . '">Rata-rata</th>';
            echo '</tr></thead>';

            echo '<tbody>';
            $no = 1;
            foreach ($activeStudents as $student) {
                echo '<tr>';
                echo '<td style="text-align: center; border: 1px solid #000000;">' . $no++ . '</td>';
                echo '<td style="text-align: center; border: 1px solid #000000; mso-number-format:\'@\';">' . ($student->nim_nip ?? '-') . '</td>';
                echo '<td style="border: 1px solid #000000;">' . $student->username . '</td>';

                $totalScore = 0;
                $totalQuizzes = 0;

                foreach ($team->meetings as $meeting) {
                    if ($meeting->quiz) {
                        $totalQuizzes++;
                        $grade = $meeting->quiz->studentGrades->where('user_id', $student->id)->first();
                        if ($grade) {
                            echo '<td style="text-align: center; border: 1px solid #000000;">' . $grade->score . '</td>';
                            $totalScore += $grade->score;
                        } else {
                            echo '<td style="text-align: center; color: #f97316; font-weight: bold; border: 1px solid #000000;">Belum</td>';
                        }
                    } else {
                        echo '<td style="text-align: center; color: #94a3b8; border: 1px solid #000000;">-</td>';
                    }
                }

                $avg = $totalQuizzes > 0 ? round($totalScore / $totalQuizzes) : '-';
                echo '<td style="text-align: center; font-weight: bold; border: 1px solid #000000; background-color: #f8fafc;">' . $avg . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';

            echo '</body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
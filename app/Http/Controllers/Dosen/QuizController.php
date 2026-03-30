<?php
/**
 * Controller untuk Dosen mengelola kuis, pengaturan KKM, dan bank soal setiap pertemuan.
 */

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Inertia\Inertia;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Menampilkan halaman manajemen Kuis dan daftar pertanyaan untuk dosen.
     * Input: Objek Kelas dan Pertemuan target. Output: Page Dashboard Manajemen Kuis.
     */
    public function manage(Team $team, Meeting $meeting)
    {
        $quiz = $meeting->quiz()->with('questions')->first();

        return Inertia::render('Dosen/Quizzes/Manage', [
            'team' => $team,
            'meeting' => $meeting,
            'quiz' => $quiz
        ]);
    }

    /**
     * Membuat pengaturan kuis baru atau memperbarui jika sebelumnya sudah ada (seperti judul dan passing grade).
     * Input: Request payload dan ID meeting relasional. Output: Status redirect dengan sukses simpan.
     */
    public function storeOrUpdate(Request $request, Team $team, Meeting $meeting)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'passing_grade' => 'required|integer|min:0|max:100',
        ]);

        $meeting->quiz()->updateOrCreate(
            ['meeting_id' => $meeting->id],
            [
                'title' => $request->title,
                'passing_grade' => $request->passing_grade
            ]
        );

        return back()->with('success', 'Pengaturan Kuis berhasil disimpan.');
    }

    /**
     * Menambahkan butir pertanyaan/soal baru berserta pilihan jawaban ganda ke dalam kuis.
     * Input: Request form pertanyaan, pilihan abcd, jawaban benar. Output: Status simpan berhasil.
     */
    public function storeQuestion(Request $request, Team $team, Meeting $meeting, Quiz $quiz)
    {
        $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);

        $quiz->questions()->create($request->all());

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    /**
     * Menghapus secara permanen spesifik soal pilihan ganda dari kuis tersebut.
     * Input: Model QuizQuestion ID dari parameter route. Output: Status route back setelah dihapus.
     */
    public function destroyQuestion(Team $team, Meeting $meeting, Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Mengunduh secara langsung rekap nilai khusus untuk kuis di pertemuan tertentu dalam bentuk Excel.
     * Input: Object model target team dan meeting. Output: Stream/Download resource .xls.
     */
    public function exportGrades(Team $team, Meeting $meeting)
    {
        $meeting->load(['quiz.studentGrades.user']);
        
        $quiz = $meeting->quiz;
        $grades = $quiz ? $quiz->studentGrades : collect([]);

        $fileName = 'Nilai_Kuis_Pertemuan_' . $meeting->meeting_number . '_' . Str::slug($team->name) . '_' . date('Ymd') . '.xls';

        $headers = array(
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function() use($grades, $meeting, $quiz, $team) {
            echo '<html><head><meta charset="utf-8"></head><body>';
            
            echo '<table border="0">';
            echo '<tr><td colspan="6" style="font-size: 16px; font-weight: bold;">REKAPITULASI NILAI KUIS PERTEMUAN ' . $meeting->meeting_number . '</td></tr>';
            echo '<tr><td colspan="6">Mata Kuliah: <b>' . $team->name . '</b></td></tr>';
            echo '<tr><td colspan="6">Materi: ' . $meeting->title . '</td></tr>';
            echo '<tr><td colspan="6">KKM (Nilai Lulus): <b>' . ($quiz ? $quiz->passing_grade : '-') . '</b></td></tr>';
            echo '<tr><td colspan="6">Tanggal Unduh: ' . date('d M Y, H:i') . ' WIB</td></tr>';
            echo '<tr><td colspan="6"></td></tr>';
            echo '</table>';

            echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
            echo '<thead><tr>';
            $headerStyle = 'background-color: #194872; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000000;';
            echo '<th style="' . $headerStyle . '">No</th>';
            echo '<th style="' . $headerStyle . '">NIM/NIP</th>';
            echo '<th style="' . $headerStyle . '">Nama Mahasiswa</th>';
            echo '<th style="' . $headerStyle . '">Waktu Submit</th>';
            echo '<th style="' . $headerStyle . '">Nilai</th>';
            echo '<th style="' . $headerStyle . '">Status</th>';
            echo '</tr></thead>';

            echo '<tbody>';
            if ($grades->count() == 0) {
                 echo '<tr><td colspan="6" style="text-align: center; font-style: italic;">Belum ada mahasiswa yang mengerjakan kuis ini.</td></tr>';
            } else {
                $no = 1;
                foreach ($grades as $grade) {
                    echo '<tr>';
                    echo '<td style="text-align: center; border: 1px solid #000000;">' . $no++ . '</td>';
                    echo '<td style="text-align: center; border: 1px solid #000000; mso-number-format:\'@\';">' . ($grade->user->nim_nip ?? '-') . '</td>';
                    echo '<td style="border: 1px solid #000000;">' . $grade->user->username . '</td>';
                    echo '<td style="text-align: center; border: 1px solid #000000;">' . date('d M Y H:i', strtotime($grade->created_at)) . '</td>';
                    
                    $isPassed = $grade->score >= $quiz->passing_grade;
                    $scoreColor = $isPassed ? '#16a34a' : '#ef4444';
                    $statusText = $isPassed ? 'Lulus' : 'Remedial';
                    
                    echo '<td style="text-align: center; font-weight: bold; color: ' . $scoreColor . '; border: 1px solid #000000;">' . $grade->score . '</td>';
                    echo '<td style="text-align: center; font-weight: bold; color: ' . $scoreColor . '; border: 1px solid #000000;">' . $statusText . '</td>';
                    echo '</tr>';
                }
            }
            echo '</tbody></table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
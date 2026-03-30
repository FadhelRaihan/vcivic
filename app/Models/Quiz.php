<?php
/**
 * Model Quiz mewakili ketersediaan tes pilihan ganda (soal evaluasi) untuk setiap pertemuan pembelajaran.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Quiz extends Model
{
    use HasUuids;
    
    protected $guarded = [];

    /**
     * Relasi kuis merujuk kembali kepada pertemuan (Meeting) tempat ia diadakan.
     * Input: -. Output: BelongsTo relasi ke kelas Meeting.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Mendapatkan daftar semua butir soal pertanyaan (QuizQuestion) dari kuis ini.
     * Input: -. Output: Hasil relasi HasMany ke model QuizQuestion.
     */
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    /**
     * Mendapatkan riwayat nilai/skor penyelesaian kuis oleh semua mahasiswa yang pernah mengerjakannya.
     * Input: -. Output: Hasil relasi HasMany ke model StudentGrade.
     */
    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class);
    }
}
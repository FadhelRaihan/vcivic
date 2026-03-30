<?php
/**
 * Model StudentGrade berfungsi menyimpan nilai akhir (skor) dari kuis yang telah dikerjakan mahasiswa.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentGrade extends Model
{
    use HasUuids;
    
    protected $guarded = [];

    /**
     * Relasi ke entitas pengguna/mahasiswa pemilik skor kuis ini.
     * Input: -. Output: Relasi BelongsTo ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi identifikasi Kuis mana yang dikerjakan oleh mahasiswa tersebut.
     * Input: -. Output: Relasi BelongsTo ke Quiz.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
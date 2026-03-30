<?php
/**
 * Model QuizQuestion merupakan struktur butir soal spesifik beserta jawaban benarnya di dalam satu kuis.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuizQuestion extends Model
{
    use HasUuids;
    
    protected $guarded = [];

    /**
     * Mendeklarasikan bahwa soal ini merupakan bagian dari suatu Kuis.
     * Input: -. Output: Relasi BelongsTo ke Quiz.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
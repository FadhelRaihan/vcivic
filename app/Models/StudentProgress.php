<?php
/**
 * Model untuk melacak persentase perkembangan belajar mahasiswa (saat ini belum diimplementasikan penuh).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentProgress extends Model
{
    use HasUuids;
    
    protected $guarded = [];
}
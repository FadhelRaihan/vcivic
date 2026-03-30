<?php
/**
 * Model Discussion merepresentasikan balasan atau pesan forum obrolan di dalam pertemuan/kelas.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Discussion extends Model
{
    use HasUuids;
    protected $guarded = [];

    /**
     * Relasi diskusi dimiliki oleh satu pengguna/mahasiswa/dosen.
     * Input: -. Output: Relasi BelongsTo ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi mandiri (self-referencing) untuk mendapatkan semua balasan ke pesan diskusi ini.
     * Input: -. Output: Relasi HasMany ke Discussion.
     */
    public function replies()
    {
        return $this->hasMany(Discussion::class, 'parent_id')->with('user');
    }

    /**
     * Mengambil komentar induk (parent) dari sebuah balasan diskusi.
     * Input: -. Output: Relasi BelongsTo ke Discussion.
     */
    public function parent()
    {
        return $this->belongsTo(Discussion::class, 'parent_id');
    }
}
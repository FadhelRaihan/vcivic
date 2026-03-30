<?php
/**
 * Model Meeting merepresentasikan silabus/pertemuan per bab di dalam satu kelas yang sama.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Meeting extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $fillable = ['team_id', 'meeting_number', 'title', 'description'];

    /**
     * Relasi pertemuan terkait dengan satu Kelas (Team) secara spesifik.
     * Input: -. Output: Relasi BelongsTo ke Team.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relasi untuk semua percakapan induk (tanpa parent) topik forum diskusi di pertemuan ini.
     * Input: -. Output: Relasi HasMany ke Discussion.
     */
    public function discussions()
    {
        return $this->hasMany(Discussion::class)->whereNull('parent_id');
    }

    /**
     * Relasi lampiran pelajaran (pdf, link, video, dll) pada pertemuan ini.
     * Input: -. Output: Relasi HasMany ke MeetingContent.
     */
    public function contents()
    {
        return $this->hasMany(MeetingContent::class);
    }

    /**
     * Relasi kuis evaluasi yang tersedia di satu pertemuan (One-to-One).
     * Input: -. Output: Relasi HasOne ke Quiz.
     */
    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }
}

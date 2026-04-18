<?php
/**
 * Model MeetingContent merepresentasikan baris aset file/link pembelajaran untuk suatu Pertemuan.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MeetingContent extends Model
{
    use HasUuids;
 
    protected $touches = ['meeting'];

    protected $fillable = ['meeting_id', 'type', 'file_url', 'title', 'template_content_id'];

    /**
     * Mendeklarasikan bahwa konten file ini dimiliki mutlak oleh suatu Pertemuan spesifik.
     * Input: -. Output: BelongsTo relationship ke Meeting.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}

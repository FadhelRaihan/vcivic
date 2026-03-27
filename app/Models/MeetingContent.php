<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MeetingContent extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}

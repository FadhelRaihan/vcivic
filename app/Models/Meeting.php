<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Meeting extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function discussions()
    {
        return $this->hasMany(Discussion::class)->whereNull('parent_id');
    }

    public function contents()
    {
        return $this->hasMany(MeetingContent::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }
}

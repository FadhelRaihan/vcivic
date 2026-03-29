<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Meeting extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $fillable = ['team_id', 'meeting_number', 'title', 'description'];

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

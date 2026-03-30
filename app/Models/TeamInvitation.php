<?php
/**
 * Model kustom untuk unduhan pengguna masuk ke dalam kelas berbasis Jetstream Invitation.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation as JetstreamTeamInvitation;

class TeamInvitation extends JetstreamTeamInvitation
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Relasi untuk mengambil entitas Kelas (Team) dari undangan saat ini.
     * Input: -. Output: Relasi BelongsTo.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::teamModel());
    }
}

<?php
/**
 * Model Team (bawaan Jetstream) di-rename secara logika menjadi `Kelas` dalam sistem e-learning ini.
 */

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'personal_team',
        'join_code',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    /**
     * Kelas ini memiliki banyak Pertemuan (Meeting) pengajaran.
     * Input: -. Output: Relasi HasMany ke model Meeting.
     */
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Daftar semua anggota mahasiswa yang bergabung dalam Kelas ini.
     * Input: -. Output: Relasi BelongsToMany ke User melalui pivot tabel `team_user`.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps()
                    ->as('membership');
    }
}

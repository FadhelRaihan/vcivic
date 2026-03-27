<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        $this->call([
            MeetingSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Dosen VCivic',
            'email' => 'dosen@vcivic.id',
            'password' => bcrypt('password'),
            'role' => 'dosen',
            'nim_nip' => '198001012005011001'
        ]);

        User::factory()->create([
            'name' => 'Mahasiswa Andi',
            'email' => 'andi@mahasiswa.id',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
            'nim_nip' => '123456789'
        ]);
    }
}

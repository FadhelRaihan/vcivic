<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meeting;
use App\Models\MeetingContent;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meetings = [
            [
                'meeting_number' => 1,
                'title' => 'The Why: Mengapa Kita Belajar PKn di Era Digital?',
                'description' => 'Pendidikan Kewarganegaraan: Konsep, Landasan, dan Urgensi',
            ],
            [
                'meeting_number' => 2,
                'title' => 'Reality Check: Menghadapi Realitas Netizen & Disrupsi Informasi',
                'description' => 'Dinamika dan Tantangan Pendidikan Kewarganegaraan',
            ],
            [
                'meeting_number' => 3,
                'title' => 'Rules of The Game: Siapa yang Bikin Aturan Main di Negara Ini?',
                'description' => 'Warga Negara, Negara, dan Konstitusi',
            ],
            [
                'meeting_number' => 4,
                'title' => 'Walk the Talk: Gak Cuma Teori, Ini Cara Jadi Warga Negara yang Taat',
                'description' => 'Nilai, Norma, dan Perilaku Konstitusional Warga Negara',
            ],
            [
                'meeting_number' => 5,
                'title' => 'Finding Our Roots: Merawat Jati Diri di Tengah Gempuran Tren Global',
                'description' => 'Identitas Nasional dan Kebhinekaan Indonesia',
            ],
            [
                'meeting_number' => 6,
                'title' => 'United or Divided? Mengurai Bahaya Fanatisme & Politik Identitas',
                'description' => 'Integrasi Nasional, Kohesi Sosial, dan Politik Identitas',
            ],
            [
                'meeting_number' => 7,
                'title' => 'Voice of the People: Membangun Nalar Kritis Demokrasi ala Indonesia',
                'description' => 'Demokrasi Pancasila dan Budaya Demokrasi',
            ],
            [
                'meeting_number' => 8,
                'title' => 'Ujian Tengah Semester (UTS)',
                'description' => 'Evaluasi Pemahaman Fundamental Bernegara',
            ],
            [
                'meeting_number' => 9,
                'title' => 'Justice Served: Membedah Keadilan Hukum (Tajam ke Atas atau ke Bawah?)',
                'description' => 'Negara Hukum dan Keadilan',
            ],
            [
                'meeting_number' => 10,
                'title' => 'Humanity First: Mengawal Hak Asasi di Ruang Nyata dan Ruang Siber',
                'description' => 'Hak Asasi Manusia dalam Negara Demokrasi',
            ],
            [
                'meeting_number' => 11,
                'title' => 'The Big Picture: Melihat Posisi Silang Indonesia di Peta Dunia',
                'description' => 'Wawasan Nusantara dan Geopolitik Indonesia',
            ],
            [
                'meeting_number' => 12,
                'title' => 'Survival Mode: Dari Ancaman Militer Hingga Cyber-Attack',
                'description' => 'Ketahanan Nasional dan Tantangan Keamanan Kontemporer',
            ],
            [
                'meeting_number' => 13,
                'title' => 'Call of Duty: Aksi Nyata Bela Negara (Bukan Cuma Angkat Senjata!)',
                'description' => 'Bela Negara dan Tanggung Jawab Kewargaan',
            ],
            [
                'meeting_number' => 14,
                'title' => 'Smart Netizen: Agent of Change vs Keyboard Warrior',
                'description' => 'Mahasiswa, Kewargaan Aktif, dan Kewargaan Digital',
            ],
            [
                'meeting_number' => 15,
                'title' => 'Make an Impact: Saatnya Bikin Perubahan Nyata!',
                'description' => 'Proyek Kewargaan dan Pembelajaran Transformatif',
            ],
            [
                'meeting_number' => 16,
                'title' => 'Ujian Akhir Semester (UAS)',
                'description' => 'Validasi Kompetensi Kewarganegaraan & Literasi Digital',
            ],
        ];

        foreach ($meetings as $meetingData) {
            $meeting = Meeting::create($meetingData);

            if (!in_array($meeting->meeting_number, [8, 16])) {
                
                // Dummy Materi PDF
                MeetingContent::create([
                    'meeting_id' => $meeting->id, 
                    'type'       => 'pdf', 
                    'title'      => 'Modul Bacaan Pertemuan ' . $meeting->meeting_number,
                    'file_url'   => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 
                ]);

                // Dummy PPT (Misal dosen upload PPT yang sudah di-export ke PDF agar mudah dibaca di web)
                MeetingContent::create([
                    'meeting_id' => $meeting->id,
                    'type'       => 'ppt',
                    'title'      => 'Slide Presentasi Pertemuan ' . $meeting->meeting_number,
                    'file_url'   => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
                ]);

                // Dummy Video Animasi
                MeetingContent::create([
                    'meeting_id' => $meeting->id,
                    'type'       => 'video',
                    'title'      => 'Video Pembelajaran Pertemuan ' . $meeting->meeting_number,
                    'file_url'   => 'https://www.youtube.com/embed/dQw4w9WgXcQ', 
                ]);
            }
        }
    }
}
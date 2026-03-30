<?php
/**
 * Controller untuk Dosen membuat dan mengelola pertemuan beserta materi/file pelajarannya di sebuah kelas.
 */

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\MeetingContent;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class MeetingController extends Controller
{
    /**
     * Menambahkan topik pertemuan baru dengan memvalidasi duplikasi pertemuan ke-N.
     * Input: Memuat 'meeting_number', referensi Team/Kelas. Output: Kembali dengan state sukses.
     */
    public function store(Request $request, Team $team)
    {
        $request->validate([
            'meeting_number' => [
                'required',
                'integer',
                'min:1',
                'max:16',
                Rule::unique('meetings')->where(function ($query) use ($team) {
                    return $query->where('team_id', $team->id);
                })
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'meeting_number.unique' => "Pertemuan ke-{$request->meeting_number} sudah dibuat sebelumnya. Gunakan nomor lain."
        ]);

        Meeting::create([
            'team_id' => $team->id,
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pertemuan berhasil ditambahkan.');
    }

    /**
     * Memperbarui detail (judul, angka ke-, deskripsi) pertemuan di dalam kelas Dosen.
     * Input: Request payload dan model Team/Meeting. Output: Status perubahan berhasil.
     */
    public function update(Request $request, Team $team, Meeting $meeting)
    {
        $request->validate([
            'meeting_number' => [
                'required',
                'integer',
                'min:1',
                'max:16',
                Rule::unique('meetings')->where(function ($query) use ($team) {
                    return $query->where('team_id', $team->id);
                })->ignore($meeting->id)
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ], [
            'meeting_number.unique' => "Pertemuan ke-{$request->meeting_number} sudah ada."
        ]);

        $meeting->update([
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pertemuan berhasil diperbarui.');
    }

    /**
     * Menampilkan halaman detail materi dari sebuah pertemuan beserta forum diskusi dan skor kuis-nya.
     * Input: Entitas objek kelas (Team) dan pertemuan (Meeting). Output: Dosen/Meetings/Show View.
     */
    public function show(Team $team, Meeting $meeting)
    {
        $meeting->load([
            'contents',
            'discussions' => function ($query) {
                $query->oldest();
            },
            'discussions.user',
            // 'discussions.parent.user'
            'quiz.studentGrades.user'
        ]);

        return Inertia::render('Dosen/Meetings/Show', [
            'team' => $team,
            'meeting' => $meeting
        ]);
    }

    /**
     * Menambahkan lampiran konten materi (bisa berupa url, pdf, video) ke dalam aktivitas pertemuan.
     * Input: Form konten materi. Output: Status sukses via Flash session back.
     */
    public function storeContent(Request $request, Team $team, Meeting $meeting)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video,link',
            'title' => 'required|string|max:255',
            'file_url' => 'nullable|url',
            'file_upload' => 'nullable|file|max:20480',
        ]);

        $fileUrl = $request->file_url;

        if ($request->hasFile('file_upload')) {
            $path = $request->file('file_upload')->store('meeting_contents', 'public');
            $fileUrl = asset('storage/' . $path);
        }

        MeetingContent::create([
            'meeting_id' => $meeting->id,
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $fileUrl,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Menghapus struktur data pertemuan secara seutuhnya untuk kelas.
     * Input: Model Team dan Meeting aktif. Output: Kembali dengan peringatan terhapus.
     */
    public function destroy(Team $team, Meeting $meeting)
    {
        $meeting->delete();
        return back()->with('success', 'Pertemuan dihapus.');
    }

    /**
     * Mengatur ulang atau memperbarui file materi jika ada unggahan jenis file terbaru.
     * Input: Data tipe, judul, file payload, Content spesifik. Output: Notif redirect.
     */
    public function updateContent(Request $request, Team $team, Meeting $meeting, MeetingContent $content)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video,link',
            'title' => 'required|string|max:255',
            'file_url' => 'nullable|url',
            'file_upload' => 'nullable|file|max:20480',
        ]);

        $fileUrl = $request->file_url ?: $content->file_url;

        if ($request->hasFile('file_upload')) {
            if ($content->file_url && str_contains($content->file_url, 'storage/meeting_contents')) {
                $oldPath = str_replace(asset('storage/'), '', $content->file_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('file_upload')->store('meeting_contents', 'public');
            $fileUrl = asset('storage/' . $path);
        }

        $content->update([
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $fileUrl,
        ]);

        return back()->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Menghapus record dan file fisik suatu konten di direktori penyimpanan lokal public/storage.
     * Input: Objek Model dan lampiran lokal. Output: Refresh current tab status sukses.
     */
    public function destroyContent(Team $team, Meeting $meeting, MeetingContent $content)
    {
        if ($content->file_url && str_contains($content->file_url, 'storage/meeting_contents')) {
            $oldPath = str_replace(asset('storage/'), '', $content->file_url);
            Storage::disk('public')->delete($oldPath);
        }

        $content->delete();
        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
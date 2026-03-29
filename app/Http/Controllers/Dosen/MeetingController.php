<?php

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
    public function store(Request $request, Team $team)
    {
        $request->validate([
            'meeting_number' => [
                'required',
                'integer',
                'min:1',
                'max:16',
                // Validasi agar tidak ada nomor pertemuan yang sama di 1 kelas
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

    // FUNGSI BARU: Edit Pertemuan
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

    public function show(Team $team, Meeting $meeting)
    {
        $meeting->load([
            'contents',
            'discussions' => function ($query) {
                $query->oldest();
            },
            'discussions.user',
            'discussions.parent.user'
        ]);

        return Inertia::render('Dosen/Meetings/Show', [
            'team' => $team,
            'meeting' => $meeting
        ]);
    }

    public function storeContent(Request $request, Team $team, Meeting $meeting)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video,link',
            'title' => 'required|string|max:255',
            'file_url' => 'nullable|url',
            'file_upload' => 'nullable|file|max:20480', // Maksimal upload 20MB
        ]);

        $fileUrl = $request->file_url;

        // Jika Dosen memilih Upload File Fisik
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

    public function destroy(Team $team, Meeting $meeting)
    {
        $meeting->delete();
        return back()->with('success', 'Pertemuan dihapus.');
    }

    // --- FUNGSI UPDATE MATERI ---
    public function updateContent(Request $request, Team $team, Meeting $meeting, MeetingContent $content)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video,link',
            'title' => 'required|string|max:255',
            'file_url' => 'nullable|url',
            'file_upload' => 'nullable|file|max:20480',
        ]);

        $fileUrl = $request->file_url ?: $content->file_url;

        // Jika Dosen mengunggah file baru
        if ($request->hasFile('file_upload')) {
            // Hapus file lama jika ada di storage
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

    // --- FUNGSI HAPUS MATERI ---
    public function destroyContent(Team $team, Meeting $meeting, MeetingContent $content)
    {
        // Hapus file fisik dari storage (jika itu file upload)
        if ($content->file_url && str_contains($content->file_url, 'storage/meeting_contents')) {
            $oldPath = str_replace(asset('storage/'), '', $content->file_url);
            Storage::disk('public')->delete($oldPath);
        }

        $content->delete();
        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
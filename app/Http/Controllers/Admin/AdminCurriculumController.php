<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\MeetingContent;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AdminCurriculumController extends Controller
{
    /**
     * Get the master template team.
     */
    private function getTemplate()
    {
        return Team::where('is_template', true)->firstOrFail();
    }

    /**
     * Show the curriculum management page.
     */
    public function manage()
    {
        $template = $this->getTemplate();
        $template->load(['meetings' => function($query) {
            $query->orderBy('meeting_number', 'asc')->with('contents');
        }]);

        return Inertia::render('Admin/Curriculum/Manage', [
            'template' => $template
        ]);
    }

    /**
     * Store a new meeting in the template.
     */
    public function storeMeeting(Request $request)
    {
        $template = $this->getTemplate();

        $request->validate([
            'meeting_number' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Meeting::create([
            'team_id' => $template->id,
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pertemuan Master berhasil ditambahkan.');
    }

    /**
     * Update a meeting in the template.
     */
    public function updateMeeting(Request $request, Meeting $meeting)
    {
        $request->validate([
            'meeting_number' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $meeting->update($request->only('meeting_number', 'title', 'description'));

        return back()->with('success', 'Pertemuan Master berhasil diperbarui.');
    }

    /**
     * Delete a meeting from the template.
     */
    public function destroyMeeting(Meeting $meeting)
    {
        $meeting->delete();
        return back()->with('success', 'Pertemuan Master berhasil dihapus.');
    }

    /**
     * Store content for a meeting in the template.
     */
    public function storeContent(Request $request, Meeting $meeting)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video,infografis,link,text',
            'title' => 'required|string|max:255',
            'file_url' => 'nullable|string',
            'file_upload' => 'nullable|file|max:20480',
            'content' => 'nullable|string',
        ]);

        $fileUrl = null;

        if ($request->type === 'text') {
            $fileUrl = $request->input('content') ?: $request->input('file_url');
        } elseif ($request->filled('file_url')) {
            $fileUrl = $request->file_url;
        } elseif ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('meeting_contents', $filename, 'supabase');

            if ($path) {
                $endpoint = config('filesystems.disks.supabase.endpoint');
                $bucket = config('filesystems.disks.supabase.bucket');
                $baseUrl = str_replace('/s3', '/object/public/' . $bucket . '/', $endpoint);
                $fileUrl = $baseUrl . $path;
            }
        }

        MeetingContent::create([
            'meeting_id' => $meeting->id,
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $fileUrl,
        ]);

        return back()->with('success', 'Materi Master berhasil ditambahkan.');
    }

    /**
     * Update content in the template.
     */
    public function updateContent(Request $request, Meeting $meeting, MeetingContent $content)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video,infografis,link,text',
            'title' => 'required|string|max:255',
            'file_url' => 'nullable|string',
            'file_upload' => 'nullable|file|max:20480',
            'content' => 'nullable|string',
        ]);

        $fileUrl = $content->file_url;

        if ($request->type === 'text') {
            $fileUrl = $request->input('content') ?: $request->input('file_url');
        } elseif ($request->filled('file_url')) {
            $fileUrl = $request->file_url;
        } elseif ($request->hasFile('file_upload')) {
            // Hapus file lama jika ada di Supabase
            if ($content->file_url && str_contains($content->file_url, 'supabase.co')) {
                $oldPath = explode('meeting_contents/', $content->file_url)[1] ?? null;
                if ($oldPath) {
                    Storage::disk('supabase')->delete('meeting_contents/' . $oldPath);
                }
            }

            $file = $request->file('file_upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('meeting_contents', $filename, 'supabase');

            if ($path) {
                $endpoint = config('filesystems.disks.supabase.endpoint');
                $bucket = config('filesystems.disks.supabase.bucket');
                $baseUrl = str_replace('/s3', '/object/public/' . $bucket . '/', $endpoint);
                $fileUrl = $baseUrl . $path;
            }
        }

        $content->update([
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $fileUrl,
        ]);

        return back()->with('success', 'Materi Master berhasil diperbarui.');
    }

    /**
     * Delete content from the template.
     */
    public function destroyContent(Meeting $meeting, MeetingContent $content)
    {
        if ($content->file_url) {
            if (str_contains($content->file_url, 'storage/meeting_contents')) {
                $oldPath = explode('storage/', $content->file_url)[1] ?? null;
                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            } elseif (str_contains($content->file_url, 'supabase.co')) {
                $oldPath = explode('meeting_contents/', $content->file_url)[1] ?? null;
                if ($oldPath) {
                    Storage::disk('supabase')->delete('meeting_contents/' . $oldPath);
                }
            }
        }

        $content->delete();
        return back()->with('success', 'Materi Master berhasil dihapus.');
    }
}

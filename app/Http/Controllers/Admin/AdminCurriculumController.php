<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\MeetingContent;
use Inertia\Inertia;

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
            'type' => 'required|in:video,pdf,link,infografis,text',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        MeetingContent::create([
            'meeting_id' => $meeting->id,
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $request->input('content'),
        ]);

        return back()->with('success', 'Materi Master berhasil ditambahkan.');
    }

    /**
     * Update content in the template.
     */
    public function updateContent(Request $request, MeetingContent $content)
    {
        $request->validate([
            'type' => 'required|in:video,pdf,link,infografis,text',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $content->update([
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $request->input('content'),
        ]);

        return back()->with('success', 'Materi Master berhasil diperbarui.');
    }

    /**
     * Delete content from the template.
     */
    public function destroyContent(MeetingContent $content)
    {
        $content->delete();
        return back()->with('success', 'Materi Master berhasil dihapus.');
    }
}

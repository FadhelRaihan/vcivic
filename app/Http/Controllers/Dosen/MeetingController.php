<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Meeting;
use App\Models\MeetingContent;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function store(Request $request, Team $team)
    {
        $request->validate([
            'meeting_number' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Meeting::create([
            'team_id' => $team->id,
            'meeting_number' => $request->meeting_number,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pertemuan berhasil ditambahkan ke kelas.');
    }

    public function show(Team $team, Meeting $meeting)
    {
        $meeting->load(['contents', 'discussions.user', 'discussions.replies.user']);

        return Inertia::render('Dosen/Meetings/Show', [
            'team' => $team,
            'meeting' => $meeting
        ]);
    }

    public function storeContent(Request $request, Team $team, Meeting $meeting)
    {
        $request->validate([
            'type' => 'required|in:pdf,ppt,video',
            'title' => 'required|string|max:255',
            'file_url' => 'required|url',
        ]);

        MeetingContent::create([
            'meeting_id' => $meeting->id,
            'type' => $request->type,
            'title' => $request->title,
            'file_url' => $request->file_url,
        ]);

        return back()->with('success', 'Materi berhasil ditambahkan.');
    }

    public function destroy(Team $team, Meeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('dosen.classes.manage', $team->id)->with('success', 'Pertemuan berhasil dihapus.');
    }
}
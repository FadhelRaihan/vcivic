<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Discussion;

class DiscussionController extends Controller
{
    public function store(Request $request, Meeting $meeting)
    {
        $request->validate([
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:discussions,id'
        ]);

        Discussion::create([
            'meeting_id' => $meeting->id,
            'user_id' => $request->user()->id,
            'parent_id' => $request->parent_id,
            'body' => $request->body
        ]);

        return back()->with('success', 'Diskusi berhasil dikirim.');
    }
}
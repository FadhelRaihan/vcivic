<?php
/**
 * File controller yang menangani logika penyimpanan dan interaksi diskusi di dalam kelas/pertemuan.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Discussion;

class DiscussionController extends Controller
{
    /**
     * Menyimpan data diskusi baru ke dalam target meeting tertentu.
     * Input: Request $request, $meetingId (int). Output: Redirect back.
     */
    public function store(Request $request, $meetingId)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        $meeting = Meeting::find($meetingId);
        if ($meeting) {
            $meeting->discussions()->create([
                'user_id' => auth()->id(),
                'body' => $request->body,
                'parent_id' => null,
            ]);
        }
        return back();
    }

    // public function storeReply(Request $request, $discussionId)
    // {
    //     $request->validate([
    //         'body' => 'required|string|max:1000',
    //     ]);

    //     $parent = Discussion::find($discussionId);

    //     if ($parent) {
    //         Discussion::create([
    //             'user_id' => auth()->id(),
    //             'meeting_id' => $parent->meeting_id,
    //             'parent_id' => $parent->id,
    //             'body' => $request->body,
    //         ]);
    //     }

    //     return back();
    // }

    // public function destroy($discussionId)
    // {
    //     $discussion = Discussion::find($discussionId);
    //     // Hapus hanya jika pesannya masih ada
    //     if ($discussion) {
    //         $discussion->delete();
    //     }
    //     return back();
    // }
}
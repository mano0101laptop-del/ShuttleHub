<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // Admin: list + create form in one screen
    public function index()
    {
        $announcements = Announcement::with('creator')->latest()->paginate(15);
        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'required|string|max:2000',
            'audience' => 'required|string|in:passengers,drivers,all',
        ]);

        Announcement::create([
            'title'      => $request->title,
            'body'       => $request->body,
            'audience'   => $request->audience,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement published.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Announcement removed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('creator')->latest()->paginate(15);
        $canManage = Auth::user()->role === 'incharge';

        return view('announcements.index', compact('announcements', 'canManage'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateAnnouncement($request);

        Announcement::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement published.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $announcement->update($this->validateAnnouncement($request));

        return redirect()->route('announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('announcements.index')->with('success', 'Announcement removed.');
    }

    private function validateAnnouncement(Request $request): array
    {
        return $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'required|string|max:2000',
            'audience' => 'required|string|in:passengers,drivers,all',
        ]);
    }
}

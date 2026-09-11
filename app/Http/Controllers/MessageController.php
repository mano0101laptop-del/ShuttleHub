<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Driver-role users land straight in their own thread.
     * Admin/incharge users see a list of driver threads to pick from.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'driver') {
            return $this->thread($user->id);
        }

        $driverUsers = User::where('role', 'driver')
            ->withCount(['driverMessages as unread_count' => function ($q) {
                $q->whereNull('read_at')->whereColumn('sender_id', 'driver_user_id');
            }])
            ->with(['driver', 'driverMessages' => fn ($q) => $q->latest()->limit(1)])
            ->orderBy('name')
            ->get();

        return view('messages.admin-index', compact('driverUsers'));
    }

    /** Open (and mark read) the thread belonging to a specific driver user. */
    public function thread(int $driverUserId)
    {
        $user = Auth::user();

        if ($user->role === 'driver' && $user->id !== $driverUserId) {
            abort(403);
        }

        $driverUser = User::where('role', 'driver')->with('driver')->findOrFail($driverUserId);

        $messages = Message::where('driver_user_id', $driverUserId)
            ->with('sender')
            ->oldest()
            ->get();

        // Mark incoming messages (not sent by the current viewer) as read
        Message::where('driver_user_id', $driverUserId)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.thread', compact('messages', 'driverUser'));
    }

    public function store(Request $request, int $driverUserId)
    {
        $user = Auth::user();

        if ($user->role === 'driver' && $user->id !== $driverUserId) {
            abort(403);
        }
        if (!in_array($user->role, ['driver', 'admin', 'incharge'], true)) {
            abort(403);
        }

        $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Message::create([
            'driver_user_id' => $driverUserId,
            'sender_id'      => $user->id,
            'body'           => $request->body,
        ]);

        return redirect()->back()->with('success', 'Message sent.');
    }
}

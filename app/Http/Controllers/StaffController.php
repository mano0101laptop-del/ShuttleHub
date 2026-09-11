<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $incharges = User::where('role', 'incharge')->latest()->paginate(15);
        return view('staff.index', compact('incharges'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'incharge',
        ]);

        return redirect()->route('staff.index')->with('success', 'Transport In-Charge added successfully.');
    }

    public function show(User $user)
    {
        $this->ensureIncharge($user);
        return view('staff.show', ['incharge' => $user]);
    }

    public function edit(User $user)
    {
        $this->ensureIncharge($user);
        return view('staff.edit', ['incharge' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureIncharge($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return redirect()->route('staff.index')->with('success', 'Transport In-Charge updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->ensureIncharge($user);
        $user->delete();

        return redirect()->route('staff.index')->with('success', 'Transport In-Charge account removed.');
    }

    private function ensureIncharge(User $user): void
    {
        abort_unless($user->role === 'incharge', 404);
    }
}

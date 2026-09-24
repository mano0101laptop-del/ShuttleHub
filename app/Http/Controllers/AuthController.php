<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Stop;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials. Please try again.'])->withInput();
    }

    public function showRegister()
    {
        // Route + Pickup Stop are chosen from the routes/stops an admin has
        // actually set up, so applicants can't request a route or stop that
        // doesn't exist. Stops are eager-loaded so the form's JS can populate
        // the Pickup Stop dropdown as soon as a route is selected, without an
        // extra request.
        $routes = Route::with('Stops')->where('status', 'Active')->orderBy('name')->get();

        return view('auth.register', compact('routes'));
    }

    public function register(Request $request)
    {
        // SECURITY: the public registration form must only ever be able to create
        // a passenger account. Staff roles (admin/incharge/driver) must be
        // created by an existing admin (see UserSeeder for initial staff accounts).
        // Previously this endpoint accepted a client-supplied `role` field allowing
        // anyone to self-register as `admin` — that field is intentionally not
        // trusted or read from the request here.
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users',
            'password'           => 'required|string|min:6|confirmed',
            'roll'               => 'required|string|unique:passengers,roll',
            'contact_number'     => 'required|string|max:20',
            'passenger_type'     => 'required|string|in:Student,Teacher,Staff',
            'route_id'           => 'required|exists:routes,id',
            'stop_id'            => 'required|exists:stops,id',
            'address'            => 'required|string|max:500',
            'emergency_contact'  => 'required|string|max:20',
            'confirm'            => 'accepted',
        ]);

        // The selected stop must actually belong to the selected route —
        // mirrors the same safeguard used by the admin "Register Passenger" form.
        $stop = Stop::findOrFail($validated['stop_id']);
        if ((int) $stop->route_id !== (int) $validated['route_id']) {
            throw ValidationException::withMessages([
                'stop_id' => 'The selected pickup stop must belong to the selected route.',
            ]);
        }

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'passenger',
        ]);

        Passenger::create([
            'user_id'            => $user->id,
            'name'               => $validated['name'],
            'roll'               => $validated['roll'],
            'contact_number'     => $validated['contact_number'],
            'passenger_type'     => $validated['passenger_type'],
            'address'            => $validated['address'],
            'emergency_contact'  => $validated['emergency_contact'],
            'route_id'           => $validated['route_id'],
            'stop_id'            => $stop->id,
            'stop'               => $stop->name,
            'status'             => 'Inactive',           // inactive until approved
            'approval_status'    => 'pending',            // awaiting admin review
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Your transport application has been submitted! Please wait for admin approval.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

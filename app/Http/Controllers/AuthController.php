<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Passenger;
use App\Models\Route;

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
        // Preferred Stop is no longer free text — it's chosen from the pickup
        // points ("from" locations) of the routes an admin has actually set up,
        // so applicants can't request a stop that doesn't exist on any route.
        $routes = Route::where('status', 'Active')->orderBy('name')->get(['id', 'name', 'from', 'to']);

        return view('auth.register', compact('routes'));
    }

    public function register(Request $request)
    {
        // SECURITY: the public registration form must only ever be able to create
        // a passenger account. Staff roles (admin/incharge/driver/scanner) must be
        // created by an existing admin (see UserSeeder for initial staff accounts).
        // Previously this endpoint accepted a client-supplied `role` field allowing
        // anyone to self-register as `admin` — that field is intentionally not
        // trusted or read from the request here.
        // The dropdown's blank/placeholder option submits an empty string, not
        // null — normalize it so `nullable` actually skips the in: check below.
        if ($request->stop === '') {
            $request->merge(['stop' => null]);
        }

        // Only stops that exist on an active, admin-created route are valid —
        // mirrors the options rendered in the Preferred Stop dropdown.
        $validStops = Route::where('status', 'Active')->pluck('from')->unique()->values()->all();

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users',
            'password'          => 'required|string|min:6|confirmed',
            'department'        => 'required|string|max:255',
            'stop'              => ['nullable', 'string', 'max:255', Rule::in($validStops)],
            'fingerprint_data'  => 'required|string|min:6|max:20',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'passenger',
        ]);

        Passenger::create([
            'user_id'              => $user->id,
            'name'                 => $request->name,
            'roll'                 => 'REG-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
            'department'           => $request->department,
            'stop'                 => $request->stop,
            'status'               => 'Inactive',           // inactive until approved
            'approval_status'      => 'pending',            // awaiting admin review
            'route_id'             => null,
            'qr_token'             => Passenger::generateQrToken(),
            'fingerprint_hash'     => Hash::make($request->fingerprint_data),
            'fingerprint_enrolled' => true,
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

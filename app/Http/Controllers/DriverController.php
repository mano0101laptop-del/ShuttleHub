<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::with('vehicle', 'user')->latest()->paginate(15)->withQueryString();
        return view('drivers.index', compact('drivers'));
    }

    // driver profile — photo, CNIC, assigned vehicle/route, complaints 
    public function show(Driver $driver)
    {
        $driver->load('vehicle.route.Stops', 'user', 'complaintsAgainst.passenger');
        return view('drivers.show', compact('driver'));
    }

    public function create()
    {
        $vehicles = Vehicle::whereDoesntHave('driver')->get();
        return view('drivers.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'required|string|max:20',
            'license'             => ['required', 'string', Rule::unique('drivers', 'license')->whereNull('deleted_at')],
            'cnic'                => ['required', 'string', 'max:20', Rule::unique('drivers', 'cnic')->whereNull('deleted_at')],
            'dob'                 => 'nullable|date',
            'address'             => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255|unique:users,email',
            'experience'          => 'nullable|string',
            'status'              => 'required|string|in:Active,Inactive',
            'vehicle_id'          => 'nullable|exists:vehicles,id',
            'photo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cnic_front'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cnic_back'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'reference_name'      => 'nullable|string|max:255',
            'reference_phone'     => 'nullable|string|max:20',
            'reference_relation'  => 'nullable|string|max:100',
            'reference_address'   => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'phone', 'license', 'cnic', 'dob', 'address', 'email',
            'experience', 'status', 'vehicle_id',
            'reference_name', 'reference_phone', 'reference_relation', 'reference_address',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('drivers/photos', 'public');
        }
       
        if ($request->hasFile('cnic_front')) {
            $data['cnic_front_path'] = $request->file('cnic_front')->store('drivers/cnic', 'local');
        }
        if ($request->hasFile('cnic_back')) {
            $data['cnic_back_path'] = $request->file('cnic_back')->store('drivers/cnic', 'local');
        }

     
        $loginEmail = $request->filled('email')
            ? $request->email
            : Str::slug($request->name) . '-' . Str::random(4) . '@drivers.shuttlehub.local';

        $plainPassword = Str::password(10, symbols: false);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $loginEmail,
            'password' => Hash::make($plainPassword),
            'role'     => 'driver',
        ]);

        $data['user_id'] = $user->id;

        $driver = Driver::create($data);

        return redirect()->route('drivers.index')->with('success', 'Driver added successfully! Login credentials issued below.')
            ->with('new_driver_credentials', [
                'name'     => $driver->name,
                'email'    => $loginEmail,
                'password' => $plainPassword,
            ]);
    }

    public function edit(Driver $driver)
    {
        $vehicles = Vehicle::whereDoesntHave('driver')->orWhere('id', $driver->vehicle_id)->get();
        return view('drivers.edit', compact('driver', 'vehicles'));
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'required|string|max:20',
            'license'             => ['required', 'string', Rule::unique('drivers', 'license')->ignore($driver->id)->whereNull('deleted_at')],
            'cnic'                => ['required', 'string', 'max:20', Rule::unique('drivers', 'cnic')->ignore($driver->id)->whereNull('deleted_at')],
            'dob'                 => 'nullable|date',
            'address'             => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255|unique:users,email,' . ($driver->user_id ?? 'NULL'),
            'experience'          => 'nullable|string',
            'status'              => 'required|string|in:Active,Inactive',
            'vehicle_id'          => 'nullable|exists:vehicles,id',
            'photo'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cnic_front'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'cnic_back'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'reference_name'      => 'nullable|string|max:255',
            'reference_phone'     => 'nullable|string|max:20',
            'reference_relation'  => 'nullable|string|max:100',
            'reference_address'   => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'name', 'phone', 'license', 'cnic', 'dob', 'address', 'email',
            'experience', 'status', 'vehicle_id',
            'reference_name', 'reference_phone', 'reference_relation', 'reference_address',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('drivers/photos', 'public');
        }
        if ($request->hasFile('cnic_front')) {
            $data['cnic_front_path'] = $request->file('cnic_front')->store('drivers/cnic', 'local');
        }
        if ($request->hasFile('cnic_back')) {
            $data['cnic_back_path'] = $request->file('cnic_back')->store('drivers/cnic', 'local');
        }

        $driver->update($data);

        if ($driver->user) {
            $driver->user->update([
                'name'  => $request->name,
                'email' => $request->filled('email') ? $request->email : $driver->user->email,
            ]);
        }

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully!');
    }

    //  reset a driver password 
    public function resetPassword(Driver $driver)
    {
        if (!$driver->user) {
            return redirect()->back()->with('error', 'This driver has no linked login account.');
        }

        $plainPassword = Str::password(10, symbols: false);
        $driver->user->update(['password' => Hash::make($plainPassword)]);

        return redirect()->route('drivers.index')->with('success', "Password reset for {$driver->name}.")
            ->with('new_driver_credentials', [
                'name'     => $driver->name,
                'email'    => $driver->user->email,
                'password' => $plainPassword,
            ]);
    }

    public function destroy(Driver $driver)
    {
        $user = $driver->user;
        $driver->delete();


        if ($user) {
            $user->delete();
        }

        return redirect()->route('drivers.index')->with('success', 'Driver and linked login removed successfully!');
    }

    //  Driver self-service: change their own portal password 
    public function updateOwnPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('dashboard')->with('success', 'Password changed successfully.');
    }

    
    public function updateOwnPhoto(Request $request)
    {
        $driver = Driver::where('user_id', $request->user()->id)->firstOrFail();

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'photo.required' => 'Please choose a photo to upload.',
            'photo.image'    => 'The file must be an image.',
            'photo.mimes'    => 'Only JPG, PNG, or WEBP images are allowed.',
            'photo.max'      => 'The image must be smaller than 4MB.',
        ]);

        
        if ($driver->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($driver->photo_path);
        }

        $driver->update([
            'photo_path' => $request->file('photo')->store('drivers/photos', 'public'),
        ]);

        return redirect()->route('dashboard')->with('success', 'Profile picture updated successfully!');
    }

    public function showCnic(Driver $driver, string $side)
    {
        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $path = $side === 'front' ? $driver->cnic_front_path : $driver->cnic_back_path;
        abort_if(!$path, 404);
        abort_unless(\Illuminate\Support\Facades\Storage::disk('local')->exists($path), 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
    }
}

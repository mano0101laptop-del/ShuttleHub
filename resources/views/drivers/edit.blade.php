@extends('layout.app')
@section('title', 'Edit Driver — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Edit Driver">
      <a href="{{ route('drivers.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:760px;">
        <div class="card-header"><h3>Edit: {{ $driver->name }}</h3></div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>
          @endif

          @if($driver->user)
            <div class="alert-info mb-3" style="display:flex;align-items:center;gap:10px;">
              <i class="fas fa-key"></i>
              <div style="flex:1;">Portal login: <strong>{{ $driver->user->email }}</strong></div>
              <form method="POST" action="{{ route('drivers.reset-password', $driver) }}" onsubmit="return confirm('Reset this driver\'s password? The new password will be shown once.');">
                @csrf
                <button type="submit" class="btn btn-S btn-sm"><i class="fas fa-rotate"></i> Reset Password</button>
              </form>
            </div>
          @endif

          <form method="POST" action="{{ route('drivers.update', $driver) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <h4 style="margin:0 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Personal Information</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">Full Name *</label>
                <div class="lf-input-wrap"><i class="fas fa-user"></i>
                  <input class="lf-input" type="text" name="name" value="{{ old('name', $driver->name) }}" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Phone *</label>
                <div class="lf-input-wrap"><i class="fas fa-phone"></i>
                  <input class="lf-input" type="text" name="phone" value="{{ old('phone', $driver->phone) }}" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Date of Birth</label>
                <div class="lf-input-wrap"><i class="fas fa-cake-candles"></i>
                  <input class="lf-input" type="date" name="dob" value="{{ old('dob', optional($driver->dob)->format('Y-m-d')) }}">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Login Email</label>
                <div class="lf-input-wrap"><i class="fas fa-envelope"></i>
                  <input class="lf-input" type="email" name="email" value="{{ old('email', $driver->email) }}">
                </div>
              </div>
              <div class="lf-group" style="grid-column:1 / -1;">
                <label class="lf-label">Address</label>
                <div class="lf-input-wrap"><i class="fas fa-location-dot"></i>
                  <input class="lf-input" type="text" name="address" value="{{ old('address', $driver->address) }}">
                </div>
              </div>
            </div>

            <h4 style="margin:8px 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">License &amp; CNIC</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">License No *</label>
                <div class="lf-input-wrap"><i class="fas fa-id-card"></i>
                  <input class="lf-input" type="text" name="license" value="{{ old('license', $driver->license) }}" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">CNIC No *</label>
                <div class="lf-input-wrap"><i class="fas fa-id-badge"></i>
                  <input class="lf-input" type="text" name="cnic" value="{{ old('cnic', $driver->cnic) }}" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Experience</label>
                <div class="lf-input-wrap"><i class="fas fa-clock"></i>
                  <input class="lf-input" type="text" name="experience" value="{{ old('experience', $driver->experience) }}">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Status *</label>
                <div class="lf-input-wrap"><i class="fas fa-circle-dot"></i>
                  <select class="lf-input" name="status" required>
                    <option value="Active"   {{ old('status',$driver->status)=='Active'   ? 'selected':'' }}>Active</option>
                    <option value="Inactive" {{ old('status',$driver->status)=='Inactive' ? 'selected':'' }}>Inactive</option>
                  </select>
                </div>
              </div>
            </div>

            <h4 style="margin:8px 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Photo &amp; Documents</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">Driver Photo</label>
                @if($driver->photo_path)
                  <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($driver->photo_path) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;margin-bottom:6px;display:block;">
                @endif
                <div class="lf-input-wrap"><i class="fas fa-camera"></i>
                  <input class="lf-input" type="file" name="photo" accept="image/*">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">CNIC — Front</label>
                @if($driver->cnic_front_path)
                  <img src="{{ route('drivers.cnic', ['driver' => $driver, 'side' => 'front']) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;margin-bottom:6px;display:block;">
                @endif
                <div class="lf-input-wrap"><i class="fas fa-id-card"></i>
                  <input class="lf-input" type="file" name="cnic_front" accept="image/*">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">CNIC — Back</label>
                @if($driver->cnic_back_path)
                  <img src="{{ route('drivers.cnic', ['driver' => $driver, 'side' => 'back']) }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;margin-bottom:6px;display:block;">
                @endif
                <div class="lf-input-wrap"><i class="fas fa-id-card"></i>
                  <input class="lf-input" type="file" name="cnic_back" accept="image/*">
                </div>
              </div>
            </div>

            <h4 style="margin:8px 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Reference Person</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">Reference Name</label>
                <div class="lf-input-wrap"><i class="fas fa-user-group"></i>
                  <input class="lf-input" type="text" name="reference_name" value="{{ old('reference_name', $driver->reference_name) }}">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Reference Phone</label>
                <div class="lf-input-wrap"><i class="fas fa-phone"></i>
                  <input class="lf-input" type="text" name="reference_phone" value="{{ old('reference_phone', $driver->reference_phone) }}">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Relationship</label>
                <div class="lf-input-wrap"><i class="fas fa-people-arrows"></i>
                  <input class="lf-input" type="text" name="reference_relation" value="{{ old('reference_relation', $driver->reference_relation) }}">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Reference Address</label>
                <div class="lf-input-wrap"><i class="fas fa-location-dot"></i>
                  <input class="lf-input" type="text" name="reference_address" value="{{ old('reference_address', $driver->reference_address) }}">
                </div>
              </div>
            </div>

            <h4 style="margin:8px 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Assignment</h4>
            <div class="lf-group">
              <label class="lf-label">Assign Vehicle</label>
              <div class="lf-input-wrap"><i class="fas fa-bus"></i>
                <select class="lf-input" name="vehicle_id">
                  <option value="">— None —</option>
                  @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ old('vehicle_id',$driver->vehicle_id)==$v->id ? 'selected':'' }}>{{ $v->number }} ({{ $v->type }})</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px;">
              <button type="submit" class="btn btn-P" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Update Driver</button>
              <a href="{{ route('drivers.index') }}" class="btn btn-G" style="flex:1;justify-content:center;">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

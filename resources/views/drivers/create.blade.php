@extends('layout.app')
@section('title', 'Add Driver — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Add Driver">
      <a href="{{ route('drivers.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:760px;">
        <div class="card-header"><h3>Driver Details</h3></div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>
          @endif
          <div class="alert-info mb-3">
            <i class="fas fa-circle-info"></i>
            <div>Saving this form automatically creates a driver portal login. The generated username &amp; password will be shown once on the next screen — share them with the driver right away.</div>
          </div>
          <form method="POST" action="{{ route('drivers.store') }}" enctype="multipart/form-data">
            @csrf

            <h4 style="margin:0 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Personal Information</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">Full Name *</label>
                <div class="lf-input-wrap"><i class="fas fa-user"></i>
                  <input class="lf-input" type="text" name="name" value="{{ old('name') }}" placeholder="Driver name" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Phone *</label>
                <div class="lf-input-wrap"><i class="fas fa-phone"></i>
                  <input class="lf-input" type="text" name="phone" value="{{ old('phone') }}" placeholder="0300-0000000" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Date of Birth</label>
                <div class="lf-input-wrap"><i class="fas fa-cake-candles"></i>
                  <input class="lf-input" type="date" name="dob" value="{{ old('dob') }}">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Login Email (optional)</label>
                <div class="lf-input-wrap"><i class="fas fa-envelope"></i>
                  <input class="lf-input" type="email" name="email" value="{{ old('email') }}" placeholder="Leave blank to auto-generate">
                </div>
              </div>
              <div class="lf-group" style="grid-column:1 / -1;">
                <label class="lf-label">Address</label>
                <div class="lf-input-wrap"><i class="fas fa-location-dot"></i>
                  <input class="lf-input" type="text" name="address" value="{{ old('address') }}" placeholder="Current residential address">
                </div>
              </div>
            </div>

            <h4 style="margin:8px 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">License &amp; CNIC</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">License No *</label>
                <div class="lf-input-wrap"><i class="fas fa-id-card"></i>
                  <input class="lf-input" type="text" name="license" value="{{ old('license') }}" placeholder="e.g. LHR-123456" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">CNIC No *</label>
                <div class="lf-input-wrap"><i class="fas fa-id-badge"></i>
                  <input class="lf-input" type="text" name="cnic" value="{{ old('cnic') }}" placeholder="e.g. 35202-1234567-1" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Experience</label>
                <div class="lf-input-wrap"><i class="fas fa-clock"></i>
                  <input class="lf-input" type="text" name="experience" value="{{ old('experience') }}" placeholder="e.g. 5 Years">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Status *</label>
                <div class="lf-input-wrap"><i class="fas fa-circle-dot"></i>
                  <select class="lf-input" name="status" required>
                    <option value="Active"   {{ old('status')=='Active'   ? 'selected':'' }}>Active</option>
                    <option value="Inactive" {{ old('status')=='Inactive' ? 'selected':'' }}>Inactive</option>
                  </select>
                </div>
              </div>
            </div>

            <h4 style="margin:8px 0 12px;color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:.08em;">Photo &amp; Documents</h4>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 16px;">
              <div class="lf-group">
                <label class="lf-label">Driver Photo</label>
                <div class="lf-input-wrap"><i class="fas fa-camera"></i>
                  <input class="lf-input" type="file" name="photo" accept="image/*">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">CNIC — Front</label>
                <div class="lf-input-wrap"><i class="fas fa-id-card"></i>
                  <input class="lf-input" type="file" name="cnic_front" accept="image/*">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">CNIC — Back</label>
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
                  <input class="lf-input" type="text" name="reference_name" value="{{ old('reference_name') }}" placeholder="Full name">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Reference Phone</label>
                <div class="lf-input-wrap"><i class="fas fa-phone"></i>
                  <input class="lf-input" type="text" name="reference_phone" value="{{ old('reference_phone') }}" placeholder="0300-0000000">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Relationship</label>
                <div class="lf-input-wrap"><i class="fas fa-people-arrows"></i>
                  <input class="lf-input" type="text" name="reference_relation" value="{{ old('reference_relation') }}" placeholder="e.g. Brother, Friend">
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Reference Address</label>
                <div class="lf-input-wrap"><i class="fas fa-location-dot"></i>
                  <input class="lf-input" type="text" name="reference_address" value="{{ old('reference_address') }}" placeholder="Address">
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
                    <option value="{{ $v->id }}" {{ old('vehicle_id')==$v->id ? 'selected':'' }}>{{ $v->number }} ({{ $v->type }})</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:20px;">
              <button type="submit" class="btn btn-P" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Save Driver &amp; Issue Login</button>
              <a href="{{ route('drivers.index') }}" class="btn btn-G" style="flex:1;justify-content:center;">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

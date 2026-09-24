@extends('layout.app')
@section('title', 'Register Passenger — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Register Passenger">
      <a href="{{ route('passengers.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:980px;margin:0 auto;">
        <div class="card-header"><h3><i class="fas fa-user-plus" style="color:var(--color-primary);margin-right:7px;"></i>Passenger & Transport Form</h3></div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>
          @endif

          <form method="POST" action="{{ route('passengers.store') }}" id="passenger-form" enctype="multipart/form-data">
            @csrf

            <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);margin-bottom:12px;">Passenger Information</div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
              <div class="lf-group"><label class="lf-label">Full Name *</label><div class="lf-input-wrap"><i class="fas fa-user"></i><input class="lf-input" name="name" value="{{ old('name') }}" required></div></div>
              <div class="lf-group"><label class="lf-label">College ID *</label><div class="lf-input-wrap"><i class="fas fa-id-badge"></i><input class="lf-input" name="roll" value="{{ old('roll') }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Contact Number *</label><div class="lf-input-wrap"><i class="fas fa-phone"></i><input class="lf-input" type="tel" name="contact_number" value="{{ old('contact_number') }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Passenger Type *</label><div class="lf-input-wrap"><i class="fas fa-user-tag"></i><select class="lf-input" name="passenger_type" required><option value="" disabled {{ old('passenger_type') ? '' : 'selected' }}>Student / Teacher / Staff</option><option value="Student" {{ old('passenger_type')==='Student'?'selected':'' }}>Student</option><option value="Teacher" {{ old('passenger_type')==='Teacher'?'selected':'' }}>Teacher</option><option value="Staff" {{ old('passenger_type')==='Staff'?'selected':'' }}>Staff</option></select></div></div>
              <div class="lf-group"><label class="lf-label">Profile Photo</label><div class="lf-input-wrap"><i class="fas fa-camera"></i><input class="lf-input" type="file" name="photo" accept="image/png,image/jpeg,image/webp"></div><div style="font-size:11px;color:var(--color-text-faint);margin-top:4px;">Shown on the passenger dashboard and printed on the transport card. JPG, PNG or WEBP up to 4MB.</div></div>
              <div class="lf-group"><label class="lf-label">Department</label><div class="lf-input-wrap"><i class="fas fa-building-columns"></i><input class="lf-input" name="department" value="{{ old('department') }}"></div></div>
              <div class="lf-group"><label class="lf-label">Status *</label><div class="lf-input-wrap"><i class="fas fa-circle-dot"></i><select class="lf-input" name="status" required><option value="Active" {{ old('status','Active')==='Active'?'selected':'' }}>Active</option><option value="Inactive" {{ old('status')==='Inactive'?'selected':'' }}>Inactive</option></select></div></div>
              <div class="lf-group"><label class="lf-label">Address *</label><div class="lf-input-wrap"><i class="fas fa-house"></i><input class="lf-input" name="address" value="{{ old('address') }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Emergency Contact *</label><div class="lf-input-wrap"><i class="fas fa-phone-volume"></i><input class="lf-input" type="tel" name="emergency_contact" value="{{ old('emergency_contact') }}" required></div></div>
            </div>

            <div style="margin:22px 0 12px;padding-top:18px;border-top:1px solid var(--color-border);">
              <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Transport Assignment</div>
              <div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Connect this passenger with their route, stop, driver, bus and normal pickup/drop timings.</div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
              <div class="lf-group">
                <label class="lf-label">Route</label>
                <div class="lf-input-wrap"><i class="fas fa-route"></i>
                  <select class="lf-input" name="route_id" id="route-select">
                    <option value="">— Not assigned yet —</option>
                    @foreach($routes as $r)
                      <option value="{{ $r->id }}" {{ (string)old('route_id')===(string)$r->id?'selected':'' }}>{{ $r->name }} · {{ $r->from }} → {{ $r->to }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Stop</label>
                <div class="lf-input-wrap"><i class="fas fa-location-dot"></i><select class="lf-input" name="stop_id" id="stop-select"><option value="">— Select a route first —</option></select></div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Driver</label>
                <div class="lf-input-wrap"><i class="fas fa-user-tie"></i><select class="lf-input" name="driver_id" id="driver-select"><option value="">— Not assigned —</option>@foreach($drivers as $d)<option value="{{ $d->id }}" {{ (string)old('driver_id')===(string)$d->id?'selected':'' }}>{{ $d->name }} · {{ $d->phone }}</option>@endforeach</select></div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Bus / Vehicle</label>
                <div class="lf-input-wrap"><i class="fas fa-bus"></i><select class="lf-input" name="vehicle_id" id="vehicle-select"><option value="">— Not assigned —</option>@foreach($vehicles as $v)<option value="{{ $v->id }}" {{ (string)old('vehicle_id')===(string)$v->id?'selected':'' }}>{{ $v->number }} · {{ $v->type }}</option>@endforeach</select></div>
              </div>
              <div class="lf-group"><label class="lf-label">Pickup Time</label><div class="lf-input-wrap"><i class="fas fa-clock"></i><input class="lf-input" type="time" name="pickup_time" id="pickup-time" value="{{ old('pickup_time') }}"></div></div>
              <div class="lf-group"><label class="lf-label">Drop-off Time</label><div class="lf-input-wrap"><i class="fas fa-clock"></i><input class="lf-input" type="time" name="dropoff_time" value="{{ old('dropoff_time') }}"></div></div>
            </div>

            <div style="display:flex;gap:12px;margin-top:22px;">
              <button class="btn btn-P" type="submit" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Register Passenger</button>
              <a href="{{ route('passengers.index') }}" class="btn btn-G" style="flex:1;justify-content:center;">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
@php
  $transportRouteData = $routes->mapWithKeys(function ($r) {
    return [$r->id => [
      'vehicle_id' => $r->vehicle_id,
      'driver_id' => $r->vehicle?->driver?->id,
      'stops' => $r->Stops->map(function ($s) {
        return ['id' => $s->id, 'name' => $s->name, 'eta' => $s->eta];
      })->values(),
    ]];
  });
@endphp
const routeData = {!! $transportRouteData->toJson() !!};
const oldStopId = @json(old('stop_id'));
const hadOldDriver = @json(old('driver_id') !== null);
const hadOldVehicle = @json(old('vehicle_id') !== null);

function populateTransportDefaults(applyDefaults = false) {
  const routeId = document.getElementById('route-select').value;
  const stop = document.getElementById('stop-select');
  const data = routeData[routeId] || null;
  stop.innerHTML = '<option value="">— Select stop —</option>';
  if (!data) { stop.innerHTML = '<option value="">— Select a route first —</option>'; return; }
  data.stops.forEach(s => {
    const opt = document.createElement('option'); opt.value=s.id; opt.textContent=s.name;
    if (String(oldStopId||'')===String(s.id)) opt.selected=true;
    stop.appendChild(opt);
  });
  if (applyDefaults) {
    if (!hadOldDriver && data.driver_id) document.getElementById('driver-select').value = data.driver_id;
    if (!hadOldVehicle && data.vehicle_id) document.getElementById('vehicle-select').value = data.vehicle_id;
  }
}
document.getElementById('route-select').addEventListener('change', () => populateTransportDefaults(true));
document.addEventListener('DOMContentLoaded', () => populateTransportDefaults(true));
</script>
@endsection

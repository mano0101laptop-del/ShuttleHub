@extends('layout.app')
@section('title', 'Edit Passenger — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Edit Passenger"><a href="{{ route('passengers.show', $passenger) }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a></x-topbar>
    <div class="content">
      <div class="card" style="max-width:980px;margin:0 auto;">
        <div class="card-header"><h3><i class="fas fa-user-pen" style="color:var(--color-primary);margin-right:7px;"></i>{{ $passenger->name }} · Transport Form</h3></div>
        <div class="card-body">
          @if($errors->any())<div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>@endif
          <form method="POST" action="{{ route('passengers.update', $passenger) }}" id="passenger-form" enctype="multipart/form-data">@csrf @method('PUT')
            <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);margin-bottom:12px;">Passenger Information</div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
              <div class="lf-group"><label class="lf-label">Full Name *</label><div class="lf-input-wrap"><i class="fas fa-user"></i><input class="lf-input" name="name" value="{{ old('name',$passenger->name) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">College ID *</label><div class="lf-input-wrap"><i class="fas fa-id-badge"></i><input class="lf-input" name="roll" value="{{ old('roll',$passenger->roll) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Contact Number *</label><div class="lf-input-wrap"><i class="fas fa-phone"></i><input class="lf-input" type="tel" name="contact_number" value="{{ old('contact_number',$passenger->contact_number) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Passenger Type *</label><div class="lf-input-wrap"><i class="fas fa-user-tag"></i><select class="lf-input" name="passenger_type" required><option value="" disabled {{ old('passenger_type',$passenger->passenger_type) ? '' : 'selected' }}>Student / Teacher / Staff</option><option value="Student" {{ old('passenger_type',$passenger->passenger_type)==='Student'?'selected':'' }}>Student</option><option value="Teacher" {{ old('passenger_type',$passenger->passenger_type)==='Teacher'?'selected':'' }}>Teacher</option><option value="Staff" {{ old('passenger_type',$passenger->passenger_type)==='Staff'?'selected':'' }}>Staff</option></select></div></div>
              <div class="lf-group">
                <label class="lf-label">Profile Photo</label>
                @if($passenger->photo_path)
                  <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($passenger->photo_path) }}" alt="{{ $passenger->name }}" style="width:64px;height:64px;object-fit:cover;border-radius:10px;border:2px solid var(--color-primary-soft);margin-bottom:7px;display:block;">
                @endif
                <div class="lf-input-wrap"><i class="fas fa-camera"></i><input class="lf-input" type="file" name="photo" accept="image/png,image/jpeg,image/webp"></div>
                <div style="font-size:11px;color:var(--color-text-faint);margin-top:4px;">Upload a new image only if you want to replace the current card photo.</div>
              </div>
              <div class="lf-group"><label class="lf-label">Department</label><div class="lf-input-wrap"><i class="fas fa-building-columns"></i><input class="lf-input" name="department" value="{{ old('department',$passenger->department) }}"></div></div>
              <div class="lf-group"><label class="lf-label">Status *</label><div class="lf-input-wrap"><i class="fas fa-circle-dot"></i><select class="lf-input" name="status" required><option value="Active" {{ old('status',$passenger->status)==='Active'?'selected':'' }}>Active</option><option value="Inactive" {{ old('status',$passenger->status)==='Inactive'?'selected':'' }}>Inactive</option></select></div></div>
              <div class="lf-group"><label class="lf-label">Address *</label><div class="lf-input-wrap"><i class="fas fa-house"></i><input class="lf-input" name="address" value="{{ old('address',$passenger->address) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Emergency Contact *</label><div class="lf-input-wrap"><i class="fas fa-phone-volume"></i><input class="lf-input" type="tel" name="emergency_contact" value="{{ old('emergency_contact',$passenger->emergency_contact) }}" required></div></div>
            </div>

            <div style="margin:22px 0 12px;padding-top:18px;border-top:1px solid var(--color-border);"><div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Transport Assignment</div><div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Route, stop, driver, bus and the passenger's normal pickup/drop schedule.</div></div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
              <div class="lf-group"><label class="lf-label">Route</label><div class="lf-input-wrap"><i class="fas fa-route"></i><select class="lf-input" name="route_id" id="route-select"><option value="">— Not assigned yet —</option>@foreach($routes as $r)<option value="{{ $r->id }}" {{ (string)old('route_id',$passenger->route_id)===(string)$r->id?'selected':'' }}>{{ $r->name }} · {{ $r->from }} → {{ $r->to }}</option>@endforeach</select></div></div>
              <div class="lf-group"><label class="lf-label">Stop</label><div class="lf-input-wrap"><i class="fas fa-location-dot"></i><select class="lf-input" name="stop_id" id="stop-select"><option value="">— Select a route first —</option></select></div></div>
              <div class="lf-group"><label class="lf-label">Driver</label><div class="lf-input-wrap"><i class="fas fa-user-tie"></i><select class="lf-input" name="driver_id" id="driver-select"><option value="">— Not assigned —</option>@foreach($drivers as $d)<option value="{{ $d->id }}" {{ (string)old('driver_id',$passenger->driver_id)===(string)$d->id?'selected':'' }}>{{ $d->name }} · {{ $d->phone }}</option>@endforeach</select></div></div>
              <div class="lf-group"><label class="lf-label">Bus / Vehicle</label><div class="lf-input-wrap"><i class="fas fa-bus"></i><select class="lf-input" name="vehicle_id" id="vehicle-select"><option value="">— Not assigned —</option>@foreach($vehicles as $v)<option value="{{ $v->id }}" {{ (string)old('vehicle_id',$passenger->vehicle_id)===(string)$v->id?'selected':'' }}>{{ $v->number }} · {{ $v->type }}</option>@endforeach</select></div></div>
              <div class="lf-group"><label class="lf-label">Pickup Time</label><div class="lf-input-wrap"><i class="fas fa-clock"></i><input class="lf-input" type="time" name="pickup_time" value="{{ old('pickup_time',$passenger->pickup_time) }}"></div></div>
              <div class="lf-group"><label class="lf-label">Drop-off Time</label><div class="lf-input-wrap"><i class="fas fa-clock"></i><input class="lf-input" type="time" name="dropoff_time" value="{{ old('dropoff_time',$passenger->dropoff_time) }}"></div></div>
            </div>

            <div style="display:flex;gap:12px;margin-top:22px;"><button class="btn btn-P" type="submit" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Update Passenger</button><a href="{{ route('passengers.index') }}" class="btn btn-G" style="flex:1;justify-content:center;">Cancel</a></div>
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
const selectedStopId = @json(old('stop_id',$passenger->stop_id));
function populateStops(applyDefaults=false){
  const routeId=document.getElementById('route-select').value, stop=document.getElementById('stop-select'), data=routeData[routeId]||null;
  stop.innerHTML='<option value="">— Select stop —</option>';
  if(!data){ stop.innerHTML='<option value="">— Select a route first —</option>'; return; }
  data.stops.forEach(s=>{const o=document.createElement('option');o.value=s.id;o.textContent=s.name;if(String(selectedStopId||'')===String(s.id))o.selected=true;stop.appendChild(o);});
  if(applyDefaults){ if(data.driver_id)document.getElementById('driver-select').value=data.driver_id; if(data.vehicle_id)document.getElementById('vehicle-select').value=data.vehicle_id; }
}
document.getElementById('route-select').addEventListener('change',()=>populateStops(true));
document.addEventListener('DOMContentLoaded',()=>populateStops(false));
</script>
@endsection

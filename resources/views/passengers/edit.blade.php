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
          <form method="POST" action="{{ route('passengers.update', $passenger) }}" id="student-form">@csrf @method('PUT')
            <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);margin-bottom:12px;">Passenger Information</div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
              <div class="lf-group"><label class="lf-label">Full Name *</label><div class="lf-input-wrap"><i class="fas fa-user"></i><input class="lf-input" name="name" value="{{ old('name',$passenger->name) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Roll No *</label><div class="lf-input-wrap"><i class="fas fa-id-badge"></i><input class="lf-input" name="roll" value="{{ old('roll',$passenger->roll) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Department *</label><div class="lf-input-wrap"><i class="fas fa-building-columns"></i><input class="lf-input" name="department" value="{{ old('department',$passenger->department) }}" required></div></div>
              <div class="lf-group"><label class="lf-label">Status *</label><div class="lf-input-wrap"><i class="fas fa-circle-dot"></i><select class="lf-input" name="status" required><option value="Active" {{ old('status',$passenger->status)==='Active'?'selected':'' }}>Active</option><option value="Inactive" {{ old('status',$passenger->status)==='Inactive'?'selected':'' }}>Inactive</option></select></div></div>
            </div>

            <div style="margin:22px 0 12px;padding-top:18px;border-top:1px solid var(--color-border);"><div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Transport Assignment</div><div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Route, stop, driver, bus and the passenger's normal pickup/drop schedule.</div></div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
              <div class="lf-group"><label class="lf-label">Route</label><div class="lf-input-wrap"><i class="fas fa-route"></i><select class="lf-input" name="route_id" id="route-select"><option value="">— Not assigned yet —</option>@foreach($routes as $r)<option value="{{ $r->id }}" {{ (string)old('route_id',$passenger->route_id)===(string)$r->id?'selected':'' }}>{{ $r->name }} · {{ $r->from }} → {{ $r->to }}</option>@endforeach</select></div></div>
              <div class="lf-group"><label class="lf-label">Stop</label><div class="lf-input-wrap"><i class="fas fa-location-dot"></i><select class="lf-input" name="route_stop_id" id="stop-select"><option value="">— Select a route first —</option></select></div></div>
              <div class="lf-group"><label class="lf-label">Driver</label><div class="lf-input-wrap"><i class="fas fa-user-tie"></i><select class="lf-input" name="driver_id" id="driver-select"><option value="">— Not assigned —</option>@foreach($drivers as $d)<option value="{{ $d->id }}" {{ (string)old('driver_id',$passenger->driver_id)===(string)$d->id?'selected':'' }}>{{ $d->name }} · {{ $d->phone }}</option>@endforeach</select></div></div>
              <div class="lf-group"><label class="lf-label">Bus / Vehicle</label><div class="lf-input-wrap"><i class="fas fa-bus"></i><select class="lf-input" name="vehicle_id" id="vehicle-select"><option value="">— Not assigned —</option>@foreach($vehicles as $v)<option value="{{ $v->id }}" {{ (string)old('vehicle_id',$passenger->vehicle_id)===(string)$v->id?'selected':'' }}>{{ $v->number }} · {{ $v->type }}</option>@endforeach</select></div></div>
              <div class="lf-group"><label class="lf-label">Pickup Time</label><div class="lf-input-wrap"><i class="fas fa-clock"></i><input class="lf-input" type="time" name="pickup_time" value="{{ old('pickup_time',$passenger->pickup_time) }}"></div></div>
              <div class="lf-group"><label class="lf-label">Drop-off Time</label><div class="lf-input-wrap"><i class="fas fa-clock"></i><input class="lf-input" type="time" name="dropoff_time" value="{{ old('dropoff_time',$passenger->dropoff_time) }}"></div></div>
            </div>

            <div style="margin:22px 0 12px;padding-top:18px;border-top:1px solid var(--color-border);"><div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Security PIN Reset</div><div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Leave blank to keep the current PIN.</div></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:16px;border:1px solid rgba(124,58,237,.2);background:rgba(124,58,237,.05);border-radius:12px;">
              <div class="lf-group" style="margin:0;"><label class="lf-label">New Security PIN</label><div class="lf-input-wrap"><i class="fas fa-key"></i><input class="lf-input" type="password" name="fingerprint_data" id="pin" minlength="6" maxlength="20"></div></div>
              <div class="lf-group" style="margin:0;"><label class="lf-label">Confirm New PIN</label><div class="lf-input-wrap"><i class="fas fa-key"></i><input class="lf-input" type="password" id="pin-confirm" minlength="6" maxlength="20"></div></div>
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
      'stops' => $r->routeStops->map(function ($s) {
        return ['id' => $s->id, 'name' => $s->name, 'eta' => $s->eta];
      })->values(),
    ]];
  });
@endphp
const routeData = {!! $transportRouteData->toJson() !!};
const selectedStopId = @json(old('route_stop_id',$passenger->route_stop_id));
function populateStops(applyDefaults=false){
  const routeId=document.getElementById('route-select').value, stop=document.getElementById('stop-select'), data=routeData[routeId]||null;
  stop.innerHTML='<option value="">— Select stop —</option>';
  if(!data){ stop.innerHTML='<option value="">— Select a route first —</option>'; return; }
  data.stops.forEach(s=>{const o=document.createElement('option');o.value=s.id;o.textContent=s.name;if(String(selectedStopId||'')===String(s.id))o.selected=true;stop.appendChild(o);});
  if(applyDefaults){ if(data.driver_id)document.getElementById('driver-select').value=data.driver_id; if(data.vehicle_id)document.getElementById('vehicle-select').value=data.vehicle_id; }
}
document.getElementById('route-select').addEventListener('change',()=>populateStops(true));
document.addEventListener('DOMContentLoaded',()=>populateStops(false));
document.getElementById('student-form').addEventListener('submit',function(e){const p=document.getElementById('pin').value,c=document.getElementById('pin-confirm').value;if(p&&p!==c){e.preventDefault();alert('New Security PINs do not match.');}});
</script>
@endsection

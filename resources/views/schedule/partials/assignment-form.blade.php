@php
  $editing = isset($schedule) && $schedule;
  $existingStops = $editing ? $schedule->stops->keyBy('stop_id') : collect();
  $existingPassengers = $editing ? $schedule->passengerAssignments->keyBy('passenger_id') : collect();
  $oldSelectedStops = old('selected_stop_ids');
  $oldSelectedPassengers = old('passenger_ids');
@endphp

@if($errors->any())
  <div class="alert-err mb-3">
    @foreach($errors->all() as $error)<div><i class="fas fa-circle-exclamation"></i> {{ $error }}</div>@endforeach
  </div>
@endif

<form method="POST" action="{{ $editing ? route('schedule.update', $schedule) : route('schedule.store') }}">
  @csrf
  @if($editing) @method('PUT') @endif
  <input type="hidden" name="route_id" value="{{ $route->id }}">

  <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
    <div class="lf-group">
      <label class="lf-label">Status *</label>
      <div class="lf-input-wrap"><i class="fas fa-circle-dot"></i>
        <select class="lf-input" name="status" required>
          @foreach(['Scheduled','In Progress','Completed','Cancelled'] as $status)
            <option value="{{ $status }}" {{ old('status', $editing ? $schedule->status : 'Scheduled') === $status ? 'selected' : '' }}>{{ $status }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="lf-group">
      <label class="lf-label">Driver *</label>
      <div class="lf-input-wrap"><i class="fas fa-user-tie"></i>
        <select class="lf-input" name="driver_id" required>
          <option value="">— Select driver —</option>
          @foreach($drivers as $driver)
            <option value="{{ $driver->id }}" {{ (string)old('driver_id', $editing ? $schedule->driver_id : $route->vehicle?->driver?->id) === (string)$driver->id ? 'selected' : '' }}>
              {{ $driver->name }} · {{ $driver->phone }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="lf-group">
      <label class="lf-label">Bus / Vehicle *</label>
      <div class="lf-input-wrap"><i class="fas fa-bus"></i>
        <select class="lf-input" name="vehicle_id" required>
          <option value="">— Select bus —</option>
          @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}" {{ (string)old('vehicle_id', $editing ? $schedule->vehicle_id : $route->vehicle_id) === (string)$vehicle->id ? 'selected' : '' }}>
              {{ $vehicle->number }} · {{ $vehicle->type }} · Capacity {{ $vehicle->capacity }}
            </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="lf-group">
      <label class="lf-label">Overall Departure Time</label>
      <div class="lf-input-wrap"><i class="fas fa-clock"></i>
        <input class="lf-input" type="time" name="estimated_departure_time" value="{{ old('estimated_departure_time', $editing ? $schedule->estimated_departure_time : '') }}">
      </div>
    </div>
  </div>

  <div class="lf-group">
    <label class="lf-label">Route</label>
    <div style="padding:14px 16px;border:1px solid var(--color-border);border-radius:12px;background:rgba(124,58,237,.04);display:flex;align-items:center;gap:12px;">
      <div style="width:38px;height:38px;border-radius:10px;background:rgba(124,58,237,.12);display:grid;place-items:center;color:var(--color-primary);"><i class="fas fa-route"></i></div>
      <div>
        <div style="font-weight:700;">{{ $route->name }}</div>
        <div style="font-size:12px;color:var(--color-text-muted);">{{ $route->from }} → {{ $route->to }}</div>
      </div>
    </div>
  </div>

  <div style="margin:24px 0 10px;display:flex;justify-content:space-between;align-items:end;gap:12px;">
    <div>
      <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Route Stops & Timings</div>
      <div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Choose the stops used by this Schedule and set pickup/drop-off time for each stop.</div>
    </div>
    <span class="badge badge-B">{{ $route->Stops->count() }} route stops</span>
  </div>

  <div style="border:1px solid var(--color-border);border-radius:12px;overflow:hidden;margin-bottom:22px;">
    @forelse($route->Stops as $stop)
      @php
        $existingStop = $existingStops->get($stop->id);
        $stopChecked = is_array($oldSelectedStops)
          ? in_array((string)$stop->id, array_map('strval', $oldSelectedStops), true)
          : ($editing ? (bool)$existingStop : true);
      @endphp
      <div style="display:grid;grid-template-columns:44px minmax(160px,1.5fr) minmax(130px,1fr) minmax(130px,1fr);gap:10px;align-items:center;padding:12px 14px;{{ !$loop->last ? 'border-bottom:1px solid var(--color-border);' : '' }}">
        <label style="display:grid;place-items:center;cursor:pointer;">
          <input type="checkbox" name="selected_stop_ids[]" value="{{ $stop->id }}" {{ $stopChecked ? 'checked' : '' }}>
        </label>
        <div>
          <div style="font-weight:700;font-size:13px;"><i class="fas fa-location-dot" style="color:var(--color-primary);margin-right:6px;"></i>{{ $stop->name }}</div>
          <div style="font-size:11px;color:var(--color-text-faint);">Stop {{ $stop->sequence }}</div>
        </div>
        <div>
          <label class="lf-label" style="font-size:10px;">Pickup</label>
          <input class="lf-input" type="time" name="stop_pickup_time[{{ $stop->id }}]" value="{{ old('stop_pickup_time.'.$stop->id, $existingStop?->pickup_time ?? $existingStop?->estimated_time ?? $stop->eta) }}">
        </div>
        <div>
          <label class="lf-label" style="font-size:10px;">Drop-off</label>
          <input class="lf-input" type="time" name="stop_dropoff_time[{{ $stop->id }}]" value="{{ old('stop_dropoff_time.'.$stop->id, $existingStop?->dropoff_time) }}">
        </div>
      </div>
    @empty
      <div style="padding:18px;color:var(--color-warning);font-size:13px;"><i class="fas fa-triangle-exclamation"></i> This route has no stops. Add stops to the route before creating a Schedule.</div>
    @endforelse
  </div>

  <div style="margin:24px 0 10px;display:flex;justify-content:space-between;align-items:end;gap:12px;">
    <div>
      <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Passengers Assigned</div>
      <div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Select passengers, their stop, and optional passenger-specific pickup/drop time.</div>
    </div>
    <button type="button" class="btn btn-S btn-sm" onclick="toggleRoutePassengers(true)"><i class="fas fa-check-double"></i> Select Route Passengers</button>
  </div>

  <div style="border:1px solid var(--color-border);border-radius:12px;overflow:auto;max-height:430px;margin-bottom:22px;">
    <table class="tbl" style="min-width:850px;margin:0;">
      <thead style="position:sticky;top:0;z-index:2;">
        <tr><th style="width:48px;">Use</th><th>Passenger</th><th>Default Route</th><th>Stop</th><th>Pickup</th><th>Drop-off</th></tr>
      </thead>
      <tbody>
        @forelse($passengers as $passenger)
          @php
            $existingPassenger = $existingPassengers->get($passenger->id);
            $passengerChecked = is_array($oldSelectedPassengers)
              ? in_array((string)$passenger->id, array_map('strval', $oldSelectedPassengers), true)
              : ($editing ? (bool)$existingPassenger : (int)$passenger->route_id === (int)$route->id);
            $defaultStopId = $existingPassenger?->stop_id ?? ((int)$passenger->route_id === (int)$route->id ? $passenger->stop_id : null);
            $passengerPickup = $existingPassenger?->pickup_time ?? ((int)$passenger->route_id === (int)$route->id ? $passenger->pickup_time : null);
            $passengerDropoff = $existingPassenger?->dropoff_time ?? ((int)$passenger->route_id === (int)$route->id ? $passenger->dropoff_time : null);
          @endphp
          <tr data-route-passenger="{{ (int)$passenger->route_id === (int)$route->id ? '1' : '0' }}">
            <td><input class="passenger-check" type="checkbox" name="passenger_ids[]" value="{{ $passenger->id }}" {{ $passengerChecked ? 'checked' : '' }}></td>
            <td>
              <div style="font-weight:700;">{{ $passenger->name }}</div>
              <div style="font-size:11px;color:var(--color-text-faint);">{{ $passenger->roll }} · {{ $passenger->department }}</div>
            </td>
            <td style="font-size:12px;">{{ $passenger->route?->name ?? 'Unassigned' }}</td>
            <td>
              <select class="lf-input" name="passenger_stop[{{ $passenger->id }}]" style="min-width:150px;">
                <option value="">— Stop —</option>
                @foreach($route->Stops as $stop)
                  <option value="{{ $stop->id }}" {{ (string)old('passenger_stop.'.$passenger->id, $defaultStopId) === (string)$stop->id ? 'selected' : '' }}>{{ $stop->name }}</option>
                @endforeach
              </select>
            </td>
            <td><input class="lf-input" type="time" name="passenger_pickup_time[{{ $passenger->id }}]" value="{{ old('passenger_pickup_time.'.$passenger->id, $passengerPickup) }}"></td>
            <td><input class="lf-input" type="time" name="passenger_dropoff_time[{{ $passenger->id }}]" value="{{ old('passenger_dropoff_time.'.$passenger->id, $passengerDropoff) }}"></td>
          </tr>
        @empty
          <tr class="empty-row"><td colspan="6">No active, approved passengers are available.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="lf-group">
    <label class="lf-label">Schedule Notes</label>
    <div class="lf-input-wrap" style="align-items:flex-start;"><i class="fas fa-note-sticky" style="margin-top:10px;"></i>
      <textarea class="lf-input" name="notes" rows="3" placeholder="Traffic note, bus change, driver instruction, etc.">{{ old('notes', $editing ? $schedule->notes : '') }}</textarea>
    </div>
  </div>

  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px;">
    <button type="submit" class="btn btn-P" style="flex:1;min-width:200px;justify-content:center;"><i class="fas fa-floppy-disk"></i> {{ $editing ? 'Update Schedule' : 'Create Schedule' }}</button>
    <a href="{{ route('schedule.index') }}" class="btn btn-G" style="flex:1;min-width:150px;justify-content:center;">Cancel</a>
  </div>
</form>

<script>
function toggleRoutePassengers(checked) {
  document.querySelectorAll('tr[data-route-passenger="1"] .passenger-check').forEach(function (el) {
    el.checked = checked;
  });
}
</script>

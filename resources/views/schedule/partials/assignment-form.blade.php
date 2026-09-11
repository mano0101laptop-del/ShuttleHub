@php
  $editing = isset($assignment) && $assignment;
  $existingStops = $editing ? $assignment->stops->keyBy('route_stop_id') : collect();
  $existingStudents = $editing ? $assignment->passengerAssignments->keyBy('passenger_id') : collect();
  $oldSelectedStops = old('selected_stop_ids');
  $oldSelectedStudents = old('passenger_ids');
@endphp

@if($errors->any())
  <div class="alert-err mb-3">
    @foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach
  </div>
@endif

<form method="POST" action="{{ $editing ? route('schedule.update', $assignment) : route('schedule.store') }}">
  @csrf
  @if($editing) @method('PUT') @endif
  <input type="hidden" name="route_id" value="{{ $route->id }}">

  <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
    <div class="lf-group">
      <label class="lf-label">Schedule Date *</label>
      <div class="lf-input-wrap"><i class="fas fa-calendar-day"></i>
        <input class="lf-input" type="date" name="date" value="{{ old('date', $editing ? $assignment->date->toDateString() : $date->toDateString()) }}" required>
      </div>
    </div>
    <div class="lf-group">
      <label class="lf-label">Status *</label>
      <div class="lf-input-wrap"><i class="fas fa-circle-dot"></i>
        <select class="lf-input" name="status" required>
          @foreach(['Scheduled','In Progress','Completed','Cancelled'] as $status)
            <option value="{{ $status }}" {{ old('status', $editing ? $assignment->status : 'Scheduled') === $status ? 'selected' : '' }}>{{ $status }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="lf-group">
      <label class="lf-label">Driver *</label>
      <div class="lf-input-wrap"><i class="fas fa-user-tie"></i>
        <select class="lf-input" name="driver_id" required>
          <option value="">— Select driver —</option>
          @foreach($drivers as $d)
            <option value="{{ $d->id }}" {{ (string)old('driver_id', $editing ? $assignment->driver_id : $route->vehicle?->driver?->id) === (string)$d->id ? 'selected' : '' }}>
              {{ $d->name }} · {{ $d->phone }}
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
          @foreach($vehicles as $v)
            <option value="{{ $v->id }}" {{ (string)old('vehicle_id', $editing ? $assignment->vehicle_id : $route->vehicle_id) === (string)$v->id ? 'selected' : '' }}>
              {{ $v->number }} · {{ $v->type }} · Capacity {{ $v->capacity }}
            </option>
          @endforeach
        </select>
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

  <div class="lf-group">
    <label class="lf-label">Overall Departure Time</label>
    <div class="lf-input-wrap"><i class="fas fa-clock"></i>
      <input class="lf-input" type="time" name="estimated_departure_time" value="{{ old('estimated_departure_time', $editing ? $assignment->estimated_departure_time : '') }}">
    </div>
  </div>

  <div style="margin:24px 0 10px;display:flex;justify-content:space-between;align-items:end;gap:12px;">
    <div>
      <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Route Stops & Timings</div>
      <div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Choose the stops operated today and set pickup/drop-off time for each stop.</div>
    </div>
    <span class="badge badge-B">{{ $route->routeStops->count() }} route stops</span>
  </div>

  <div style="border:1px solid var(--color-border);border-radius:12px;overflow:hidden;margin-bottom:22px;">
    @forelse($route->routeStops as $stop)
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
      <div style="padding:18px;color:var(--color-warning);font-size:13px;"><i class="fas fa-triangle-exclamation"></i> This route has no stops. Add stops to the route before creating a schedule.</div>
    @endforelse
  </div>

  <div style="margin:24px 0 10px;display:flex;justify-content:space-between;align-items:end;gap:12px;">
    <div>
      <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);">Passengers Assigned Today</div>
      <div style="font-size:12px;color:var(--color-text-faint);margin-top:4px;">Select passengers, their stop, and optional passenger-specific pickup/drop time.</div>
    </div>
    <button type="button" class="btn btn-S btn-sm" onclick="toggleRouteStudents(true)"><i class="fas fa-check-double"></i> Select Route Passengers</button>
  </div>

  <div style="border:1px solid var(--color-border);border-radius:12px;overflow:auto;max-height:430px;margin-bottom:22px;">
    <table class="tbl" style="min-width:850px;margin:0;">
      <thead style="position:sticky;top:0;z-index:2;">
        <tr><th style="width:48px;">Use</th><th>Passenger</th><th>Default Route</th><th>Stop</th><th>Pickup</th><th>Drop-off</th></tr>
      </thead>
      <tbody>
        @forelse($students as $student)
          @php
            $existingStudent = $existingStudents->get($student->id);
            $studentChecked = is_array($oldSelectedStudents)
              ? in_array((string)$student->id, array_map('strval', $oldSelectedStudents), true)
              : ($editing ? (bool)$existingStudent : (int)$student->route_id === (int)$route->id);
            $defaultStopId = $existingStudent?->route_stop_id ?? ((int)$student->route_id === (int)$route->id ? $student->route_stop_id : null);
            $studentPickup = $existingStudent?->pickup_time ?? ((int)$student->route_id === (int)$route->id ? $student->pickup_time : null);
            $studentDropoff = $existingStudent?->dropoff_time ?? ((int)$student->route_id === (int)$route->id ? $student->dropoff_time : null);
          @endphp
          <tr data-route-student="{{ (int)$student->route_id === (int)$route->id ? '1' : '0' }}">
            <td><input class="student-check" type="checkbox" name="passenger_ids[]" value="{{ $student->id }}" {{ $studentChecked ? 'checked' : '' }}></td>
            <td>
              <div style="font-weight:700;">{{ $student->name }}</div>
              <div style="font-size:11px;color:var(--color-text-faint);">{{ $student->roll }} · {{ $student->department }}</div>
            </td>
            <td style="font-size:12px;">{{ $student->route?->name ?? 'Unassigned' }}</td>
            <td>
              <select class="lf-input" name="student_stop[{{ $student->id }}]" style="min-width:150px;">
                <option value="">— Stop —</option>
                @foreach($route->routeStops as $stop)
                  <option value="{{ $stop->id }}" {{ (string)old('student_stop.'.$student->id, $defaultStopId) === (string)$stop->id ? 'selected' : '' }}>{{ $stop->name }}</option>
                @endforeach
              </select>
            </td>
            <td><input class="lf-input" type="time" name="student_pickup_time[{{ $student->id }}]" value="{{ old('student_pickup_time.'.$student->id, $studentPickup) }}"></td>
            <td><input class="lf-input" type="time" name="student_dropoff_time[{{ $student->id }}]" value="{{ old('student_dropoff_time.'.$student->id, $studentDropoff) }}"></td>
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
      <textarea class="lf-input" name="notes" rows="3" placeholder="Traffic note, temporary bus change, driver instruction, etc.">{{ old('notes', $editing ? $assignment->notes : '') }}</textarea>
    </div>
  </div>

  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px;">
    <button type="submit" class="btn btn-P" style="flex:1;min-width:200px;justify-content:center;"><i class="fas fa-floppy-disk"></i> {{ $editing ? 'Update Schedule' : 'Create Schedule' }}</button>
    <a href="{{ route('schedule.index', ['date' => $editing ? $assignment->date->toDateString() : $date->toDateString()]) }}" class="btn btn-G" style="flex:1;min-width:150px;justify-content:center;">Cancel</a>
  </div>
</form>

<script>
function toggleRouteStudents(checked) {
  document.querySelectorAll('tr[data-route-student="1"] .student-check').forEach(function (el) {
    el.checked = checked;
  });
}
</script>

@extends('layout.app')
@section('title', 'Daily Assignments — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Daily Assignments">
      <a href="{{ route('daily-assignments.create', ['date' => $date->toDateString()]) }}" class="btn btn-P"><i class="fas fa-plus"></i> New Assignment</a>
    </x-topbar>

    <div class="content">
      @if(session('success')) <div class="alert-success">{{ session('success') }}</div> @endif
      @if(session('error')) <div class="alert-err">{{ session('error') }}</div> @endif

      <div class="card mb-3">
        <div class="card-body" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <form method="GET" action="{{ route('daily-assignments.index') }}" style="display:flex;align-items:center;gap:10px;">
            <label class="lf-label" style="margin:0;">Schedule Date</label>
            <input class="lf-input" type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()" style="max-width:190px;">
          </form>
          <a href="{{ route('daily-assignments.index', ['date' => today()->toDateString()]) }}" class="btn btn-S btn-sm"><i class="fas fa-calendar-day"></i> Today</a>
          <div style="margin-left:auto;font-size:13px;color:var(--color-text-muted);">{{ $date->format('l, d M Y') }} · <strong>{{ $assignments->count() }}</strong> assignments</div>
        </div>
      </div>

      @forelse($assignments as $a)
        @php
          $badge = $a->status === 'Completed' ? 'badge-B' : ($a->status === 'Cancelled' ? 'badge-ERR' : ($a->status === 'In Progress' ? 'badge-W' : 'badge-G'));
        @endphp
        <div class="card mb-3">
          <div class="card-header" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div>
              <h3 style="margin:0;"><i class="fas fa-route" style="color:var(--color-primary);margin-right:7px;"></i>{{ $a->route?->name ?? 'Route removed' }}</h3>
              <div style="font-size:11px;color:var(--color-text-faint);margin-top:4px;">{{ $a->route?->from }} → {{ $a->route?->to }}</div>
            </div>
            <span class="badge {{ $badge }}" style="margin-left:auto;">{{ $a->status }}</span>
            <a href="{{ route('daily-assignments.edit', $a) }}" class="btn btn-S btn-sm"><i class="fas fa-pen"></i> Edit</a>
            <form method="POST" action="{{ route('daily-assignments.destroy', $a) }}" onsubmit="return confirm('Remove this daily assignment?')">
              @csrf @method('DELETE')
              <button class="btn btn-ERR btn-sm" type="submit"><i class="fas fa-trash"></i></button>
            </form>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(5,minmax(120px,1fr));gap:10px;margin-bottom:16px;">
              <div class="tile"><div class="tile-lbl">Driver</div><div class="tile-val"><i class="fas fa-user-tie" style="color:var(--color-primary);margin-right:5px;"></i>{{ $a->driver?->name ?? '—' }}</div></div>
              <div class="tile"><div class="tile-lbl">Bus / Vehicle</div><div class="tile-val"><i class="fas fa-bus" style="color:var(--color-primary);margin-right:5px;"></i>{{ $a->vehicle?->number ?? '—' }}</div></div>
              <div class="tile"><div class="tile-lbl">Students</div><div class="tile-val">{{ $a->passengerAssignments->count() }}</div></div>
              <div class="tile"><div class="tile-lbl">Departure</div><div class="tile-val">{{ $a->estimated_departure_time ?? '—' }}</div></div>
              <div class="tile"><div class="tile-lbl">Date</div><div class="tile-val">{{ $a->date->format('d M Y') }}</div></div>
            </div>

            <div style="display:grid;grid-template-columns:minmax(280px,1fr) minmax(320px,1.4fr);gap:16px;">
              <div style="border:1px solid var(--color-border);border-radius:12px;padding:14px;">
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:10px;"><i class="fas fa-location-dot" style="color:var(--color-primary);margin-right:5px;"></i>Stops & Timings</div>
                @forelse($a->stops->sortBy(fn($s) => $s->routeStop?->sequence ?? 999) as $stopRow)
                  <div style="display:grid;grid-template-columns:1fr auto;gap:12px;padding:7px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--color-border);' : '' }}">
                    <div style="font-size:13px;font-weight:600;">{{ $stopRow->routeStop?->name ?? 'Removed stop' }}</div>
                    <div style="font-size:11px;color:var(--color-text-muted);text-align:right;">Pickup {{ $stopRow->pickup_time ?? $stopRow->estimated_time ?? '—' }}<br>Drop {{ $stopRow->dropoff_time ?? '—' }}</div>
                  </div>
                @empty
                  <div style="font-size:12px;color:var(--color-text-faint);">No stops selected.</div>
                @endforelse
              </div>

              <div style="border:1px solid var(--color-border);border-radius:12px;padding:14px;overflow:auto;">
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:10px;"><i class="fas fa-users" style="color:var(--color-primary);margin-right:5px;"></i>Assigned Students</div>
                <table class="tbl" style="min-width:560px;margin:0;">
                  <thead><tr><th>Student</th><th>Stop</th><th>Pickup</th><th>Drop-off</th></tr></thead>
                  <tbody>
                    @forelse($a->passengerAssignments as $pa)
                      <tr>
                        <td><strong>{{ $pa->passenger?->name ?? 'Removed student' }}</strong><div style="font-size:10px;color:var(--color-text-faint);">{{ $pa->passenger?->roll }}</div></td>
                        <td>{{ $pa->routeStop?->name ?? $pa->passenger?->stop ?? '—' }}</td>
                        <td>{{ $pa->pickup_time ?? '—' }}</td>
                        <td>{{ $pa->dropoff_time ?? '—' }}</td>
                      </tr>
                    @empty
                      <tr class="empty-row"><td colspan="4">No students assigned.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            @if($a->notes)
              <div style="margin-top:12px;padding:10px 12px;border-radius:10px;background:rgba(124,58,237,.05);font-size:12px;color:var(--color-text-muted);"><i class="fas fa-note-sticky" style="color:var(--color-primary);margin-right:5px;"></i>{{ $a->notes }}</div>
            @endif
          </div>
        </div>
      @empty
        <div class="card"><div class="card-body" style="text-align:center;padding:42px;"><i class="fas fa-calendar-xmark" style="font-size:34px;color:var(--color-text-faint);margin-bottom:12px;"></i><h3>No assignments for this date</h3><p style="color:var(--color-text-muted);font-size:13px;margin:6px 0 16px;">Create the daily driver, bus, route, stops, student and timing plan.</p><a href="{{ route('daily-assignments.create', ['date' => $date->toDateString()]) }}" class="btn btn-P"><i class="fas fa-plus"></i> Create Assignment</a></div></div>
      @endforelse

      @if($unassignedRoutes->count())
        <div class="card mt-3">
          <div class="card-header"><h3><i class="fas fa-triangle-exclamation" style="color:var(--color-warning);margin-right:6px;"></i>Routes Still Unassigned</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
            @foreach($unassignedRoutes as $r)
              <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--color-border);">
                <div><strong>{{ $r->name }}</strong><div style="font-size:11px;color:var(--color-text-faint);">{{ $r->from }} → {{ $r->to }} · Default {{ $r->vehicle?->number ?? 'no bus' }} / {{ $r->vehicle?->driver?->name ?? 'no driver' }}</div></div>
                <a href="{{ route('daily-assignments.create', ['date' => $date->toDateString(), 'route_id' => $r->id]) }}" class="btn btn-S btn-sm"><i class="fas fa-plus"></i> Assign</a>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

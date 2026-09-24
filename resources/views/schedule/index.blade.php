@extends('layout.app')
@section('title', 'Schedule — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Schedule">
      @if(Auth::user()->isIncharge())
      <a href="{{ route('schedule.create') }}" class="btn btn-P"><i class="fas fa-plus"></i> New Schedule</a>
      @endif
    </x-topbar>

    <div class="content">
      @if(session('success')) <div class="alert-success">{{ session('success') }}</div> @endif
      @if(session('error')) <div class="alert-err">{{ session('error') }}</div> @endif

      <div class="card mb-3">
        <div class="card-body" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <div>
            <div style="font-weight:700;color:var(--color-text);">Persistent Transport Schedule</div>
            <div style="font-size:12px;color:var(--color-text-muted);margin-top:3px;">The saved Schedule remains active every day until the Transport Incharge manually updates it. Admin has view-only access.</div>
          </div>
          <div style="margin-left:auto;font-size:13px;color:var(--color-text-muted);"><strong>{{ $schedules->count() }}</strong> route schedule(s)</div>
        </div>
      </div>

      @forelse($schedules as $schedule)
        @php
          $badge = $schedule->status === 'Completed' ? 'badge-B' : ($schedule->status === 'Cancelled' ? 'badge-ERR' : ($schedule->status === 'In Progress' ? 'badge-W' : 'badge-G'));
        @endphp
        <div class="card mb-3">
          <div class="card-header" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div>
              <h3 style="margin:0;"><i class="fas fa-route" style="color:var(--color-primary);margin-right:7px;"></i>{{ $schedule->route?->name ?? 'Route removed' }}</h3>
              <div style="font-size:11px;color:var(--color-text-faint);margin-top:4px;">{{ $schedule->route?->from }} → {{ $schedule->route?->to }}</div>
            </div>
            <span class="badge {{ $badge }}" style="margin-left:auto;">{{ $schedule->status }}</span>
            @if(Auth::user()->isIncharge())
            <a href="{{ route('schedule.edit', $schedule) }}" class="btn btn-S btn-sm"><i class="fas fa-pen"></i> Edit</a>
            <form method="POST" action="{{ route('schedule.destroy', $schedule) }}" onsubmit="return confirm('Remove this Schedule?')">
              @csrf @method('DELETE')
              <button class="btn btn-ERR btn-sm" type="submit"><i class="fas fa-trash"></i></button>
            </form>
            @endif
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:10px;margin-bottom:16px;">
              <div class="tile"><div class="tile-lbl">Driver</div><div class="tile-val"><i class="fas fa-user-tie" style="color:var(--color-primary);margin-right:5px;"></i>{{ $schedule->driver?->name ?? '—' }}</div></div>
              <div class="tile"><div class="tile-lbl">Bus / Vehicle</div><div class="tile-val"><i class="fas fa-bus" style="color:var(--color-primary);margin-right:5px;"></i>{{ $schedule->vehicle?->number ?? '—' }}</div></div>
              <div class="tile"><div class="tile-lbl">Passengers</div><div class="tile-val">{{ $schedule->passengerAssignments->count() }}</div></div>
              <div class="tile"><div class="tile-lbl">Departure</div><div class="tile-val">{{ $schedule->estimated_departure_time ?? '—' }}</div></div>
            </div>

            <div style="display:grid;grid-template-columns:minmax(280px,1fr) minmax(320px,1.4fr);gap:16px;">
              <div style="border:1px solid var(--color-border);border-radius:12px;padding:14px;">
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:10px;"><i class="fas fa-location-dot" style="color:var(--color-primary);margin-right:5px;"></i>Stops & Timings</div>
                @forelse($schedule->stops->sortBy(fn($stop) => $stop->Stop?->sequence ?? 999) as $stopRow)
                  <div style="display:grid;grid-template-columns:1fr auto;gap:12px;padding:7px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--color-border);' : '' }}">
                    <div style="font-size:13px;font-weight:600;">{{ $stopRow->Stop?->name ?? 'Removed stop' }}</div>
                    <div style="font-size:11px;color:var(--color-text-muted);text-align:right;">Pickup {{ $stopRow->pickup_time ?? $stopRow->estimated_time ?? '—' }}<br>Drop {{ $stopRow->dropoff_time ?? '—' }}</div>
                  </div>
                @empty
                  <div style="font-size:12px;color:var(--color-text-faint);">No stops selected.</div>
                @endforelse
              </div>

              <div style="border:1px solid var(--color-border);border-radius:12px;padding:14px;overflow:auto;">
                <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--color-text-muted);margin-bottom:10px;"><i class="fas fa-users" style="color:var(--color-primary);margin-right:5px;"></i>Assigned Passengers</div>
                <table class="tbl" style="min-width:560px;margin:0;">
                  <thead><tr><th>Passenger</th><th>Stop</th><th>Pickup</th><th>Drop-off</th></tr></thead>
                  <tbody>
                    @forelse($schedule->passengerAssignments as $passengerAssignment)
                      <tr>
                        <td><strong>{{ $passengerAssignment->passenger?->name ?? 'Removed passenger' }}</strong><div style="font-size:10px;color:var(--color-text-faint);">{{ $passengerAssignment->passenger?->roll }}</div></td>
                        <td>{{ $passengerAssignment->Stop?->name ?? $passengerAssignment->passenger?->stop ?? '—' }}</td>
                        <td>{{ $passengerAssignment->pickup_time ?? '—' }}</td>
                        <td>{{ $passengerAssignment->dropoff_time ?? '—' }}</td>
                      </tr>
                    @empty
                      <tr class="empty-row"><td colspan="4">No passengers assigned.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            @if($schedule->notes)
              <div style="margin-top:12px;padding:10px 12px;border-radius:10px;background:rgba(124,58,237,.05);font-size:12px;color:var(--color-text-muted);"><i class="fas fa-note-sticky" style="color:var(--color-primary);margin-right:5px;"></i>{{ $schedule->notes }}</div>
            @endif
          </div>
        </div>
      @empty
        <div class="card"><div class="card-body" style="text-align:center;padding:42px;"><i class="fas fa-calendar-xmark" style="font-size:34px;color:var(--color-text-faint);margin-bottom:12px;"></i><h3>No Schedule configured</h3><p style="color:var(--color-text-muted);font-size:13px;margin:6px 0 16px;">{{ Auth::user()->isIncharge() ? 'Create the driver, bus, route, stops, passenger and timing plan. It will remain active until manually updated.' : 'The Transport Incharge has not configured a Schedule yet.' }}</p>@if(Auth::user()->isIncharge())<a href="{{ route('schedule.create') }}" class="btn btn-P"><i class="fas fa-plus"></i> Create Schedule</a>@endif</div></div>
      @endforelse

      @if($unassignedRoutes->count())
        <div class="card mt-3">
          <div class="card-header"><h3><i class="fas fa-triangle-exclamation" style="color:var(--color-warning);margin-right:6px;"></i>Routes Without a Schedule</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
            @foreach($unassignedRoutes as $route)
              <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid var(--color-border);">
                <div><strong>{{ $route->name }}</strong><div style="font-size:11px;color:var(--color-text-faint);">{{ $route->from }} → {{ $route->to }} · Default {{ $route->vehicle?->number ?? 'no bus' }} / {{ $route->vehicle?->driver?->name ?? 'no driver' }}</div></div>
                @if(Auth::user()->isIncharge())
                <a href="{{ route('schedule.create', ['route_id' => $route->id]) }}" class="btn btn-S btn-sm"><i class="fas fa-plus"></i> Configure</a>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

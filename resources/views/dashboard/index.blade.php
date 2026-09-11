@extends('layout.app')
@section('title', 'Dashboard — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Dashboard" />

    <div class="content">

      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      {{-- Pending Requests Alert --}}
      @if(($stats['pending_requests'] ?? 0) > 0)
      <div class="alert-warning">
        <i class="fas fa-hourglass-half"></i>
        <div>
          <strong>{{ $stats['pending_requests'] }} transport application(s) awaiting approval.</strong>
          <span style="opacity:.8;font-weight:400;">Review them in the Passengers section.</span>
        </div>
        <a href="{{ route('passengers.index') }}" class="btn btn-sm alert-action" style="background:transparent;border:1px solid rgba(217,119,6,.4);color:var(--color-warning);">
          Review Now <i class="fas fa-arrow-right"></i>
        </a>
      </div>
      @endif

      {{-- Pending Fee Payments Alert --}}
      @if(($stats['pending_fee_payments'] ?? 0) > 0)
      <div class="alert-warning">
        <i class="fas fa-money-bill-wave"></i>
        <div>
          <strong>{{ $stats['pending_fee_payments'] }} transport fee payment(s) awaiting approval.</strong>
          <span style="opacity:.8;font-weight:400;">Review them in the Transport Fee section.</span>
        </div>
        <a href="{{ route('fee-payments.index') }}" class="btn btn-sm alert-action" style="background:transparent;border:1px solid rgba(217,119,6,.4);color:var(--color-warning);">
          Review Now <i class="fas fa-arrow-right"></i>
        </a>
      </div>
      @endif

      {{-- Pending Cancellation Requests Alert --}}
      @if(($stats['pending_cancellations'] ?? 0) > 0)
      <div class="alert-warning">
        <i class="fas fa-ban"></i>
        <div>
          <strong>{{ $stats['pending_cancellations'] }} transport cancellation request(s) awaiting review.</strong>
          <span style="opacity:.8;font-weight:400;">Review them in the Passengers section.</span>
        </div>
        <a href="{{ route('passengers.index') }}" class="btn btn-sm alert-action" style="background:transparent;border:1px solid rgba(217,119,6,.4);color:var(--color-warning);">
          Review Now <i class="fas fa-arrow-right"></i>
        </a>
      </div>
      @endif

      {{-- Open Complaints/Feedback Alert --}}
      @if(($stats['open_complaints'] ?? 0) > 0)
      <div class="alert-info">
        <i class="fas fa-comment-dots"></i>
        <div>
          <strong>{{ $stats['open_complaints'] }} complaint/feedback submission(s) awaiting a response.</strong>
        </div>
        <a href="{{ route('complaints.index') }}" class="btn btn-sm alert-action" style="background:transparent;border:1px solid rgba(37,99,235,.4);color:var(--color-info);">
          Review Now <i class="fas fa-arrow-right"></i>
        </a>
      </div>
      @endif

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-tile stat-tile-blue">
          <div class="stat-tile-lbl"><i class="fas fa-bus"></i> Total Vehicles</div>
          <div class="stat-tile-val">{{ $stats['total_vehicles'] }}</div>
        </div>
        <div class="stat-tile stat-tile-pink">
          <div class="stat-tile-lbl"><i class="fas fa-steering-wheel"></i> Active Drivers</div>
          <div class="stat-tile-val">{{ $stats['active_drivers'] }}</div>
        </div>
        <div class="stat-tile stat-tile-orange">
          <div class="stat-tile-lbl"><i class="fas fa-users"></i> Active Passengers</div>
          <div class="stat-tile-val">{{ $stats['total_passengers'] }}</div>
        </div>
        <div class="stat-tile stat-tile-purple">
          <div class="stat-tile-lbl"><i class="fas fa-route"></i> Active Routes</div>
          <div class="stat-tile-val">{{ $stats['active_routes'] }}</div>
        </div>
        @if(($stats['pending_requests'] ?? 0) > 0)
        <div class="stat-card" style="border-color:rgba(217,119,6,.3);">
          <div class="stat-icon" style="background:var(--color-warning-soft);color:var(--color-warning);">
            <i class="fas fa-hourglass-half"></i>
          </div>
          <div class="stat-info">
            <div class="stat-val">{{ $stats['pending_requests'] }}</div>
            <div class="stat-lbl">Pending Requests</div>
          </div>
        </div>
        @endif
        @if(($stats['pending_fee_payments'] ?? 0) > 0)
        <div class="stat-card" style="border-color:rgba(217,119,6,.3);">
          <div class="stat-icon" style="background:var(--color-warning-soft);color:var(--color-warning);">
            <i class="fas fa-money-bill-wave"></i>
          </div>
          <div class="stat-info">
            <div class="stat-val">{{ $stats['pending_fee_payments'] }}</div>
            <div class="stat-lbl">Pending Fee Payments</div>
          </div>
        </div>
        @endif
      </div>

      <!-- TODAY'S SCHEDULE -->
      @if(in_array(Auth::user()->role, ['admin', 'incharge']))
      <div class="card mt-4">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
          <h3><i class="fas fa-calendar-day" style="color:var(--color-warning);margin-right:6px;"></i>Today's Schedule ({{ \Carbon\Carbon::today()->format('d M Y') }})</h3>
          <a href="{{ route('schedule.index') }}" class="btn btn-S btn-sm">
            <i class="fas fa-list"></i> Manage Schedule
          </a>
        </div>
        <div class="card-body">
          @if($unassignedRoutesToday > 0)
            <div class="alert-warning" style="margin-bottom:14px;">
              <i class="fas fa-triangle-exclamation"></i>
              <div><strong>{{ $unassignedRoutesToday }} active route(s)</strong> still have no day-specific assignment for today — they'll run on their default driver/vehicle/timing.</div>
            </div>
          @endif

          <table class="tbl">
            <thead>
              <tr>
                <th>Route</th><th>Driver</th><th>Vehicle</th><th>Est. Passengers</th><th>Departure</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($todaysAssignments as $a)
              <tr>
                <td>{{ $a->route->name ?? '—' }}</td>
                <td>{{ $a->driver?->name ?? '— (default)' }}</td>
                <td>{{ $a->vehicle?->number ?? '— (default)' }}</td>
                <td>{{ $a->estimated_passengers ?? '—' }}</td>
                <td>{{ $a->estimated_departure_time ?? '—' }}</td>
                <td><span class="badge {{ $a->status=='Scheduled' ? 'badge-G' : ($a->status=='Completed' ? 'badge-B' : 'badge-ERR') }}">{{ $a->status }}</span></td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="6">No day-specific assignments set yet for today.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @endif

      <!-- RECENT ACTIVITY -->
      <div class="card mt-4">
        <div class="card-header"><h3>Recent Activity</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr>
                <th>#</th>
                <th>Event</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Time</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recent_activity as $a)
              <tr>
                <td>{{ $a['id'] }}</td>
                <td>{{ $a['event'] }}</td>
                <td>{{ $a['vehicle'] }}</td>
                <td>{{ $a['driver'] }}</td>
                <td>{{ $a['time'] }}</td>
                <td>
                  <span class="badge {{ $a['status']=='Active' ? 'badge-G' : ($a['status']=='Done' ? 'badge-B' : 'badge-ERR') }}">
                    {{ $a['status'] }}
                  </span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

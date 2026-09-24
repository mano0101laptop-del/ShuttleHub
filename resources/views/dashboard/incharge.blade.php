@extends('layout.app')
@section('title', 'Incharge Dashboard — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Incharge Dashboard" />

    <div class="content">

      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      <p style="font-size:13px;color:rgba(30,27,46,.55);margin-bottom:16px;">
        Day-to-day transport operations. System configuration, enrollment approvals, payment controls, publishing announcements, complaints, and staff management are handled by Admin — you can view published announcements below.
      </p>

      {{-- Quick actions --}}
      <div class="stats-grid" style="margin-bottom:4px;">
        <a href="{{ route('schedule.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(217,119,6,.15);color:#D97706;"><i class="fas fa-calendar-check"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Schedule</div><div class="stat-lbl">Maintain the active transport Schedule</div></div>
        </a>
        <a href="{{ route('passengers.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(37,99,235,.15);color:#2563EB;"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Passengers</div><div class="stat-lbl">Read-only passenger lookup</div></div>
        </a>
        <a href="{{ route('messages.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-comments"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Driver Messages</div><div class="stat-lbl">Coordinate with drivers</div></div>
        </a>
        <a href="{{ route('announcements.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(124,58,237,.15);color:#7C3AED;"><i class="fas fa-bullhorn"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Announcements</div><div class="stat-lbl">View-only — published by Admin</div></div>
        </a>
      </div>

      {{-- Operational snapshot --}}
      <div class="stats-grid mt-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(37,99,235,.15);color:#2563EB;"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['passengers'] }}</div><div class="stat-lbl">Active Passengers</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-route"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['routes'] }}</div><div class="stat-lbl">Active Routes</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(217,119,6,.15);color:#D97706;"><i class="fas fa-calendar-check"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['schedules'] }}</div><div class="stat-lbl">Schedules Set</div></div>
        </div>
      </div>

      {{-- Current Schedule --}}
      <div class="card mt-4">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
          <h3>Current Schedule</h3>
          <a href="{{ route('schedule.index') }}" class="btn btn-S" style="font-size:12px;">Open Schedule <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>Route</th><th>Driver</th><th>Vehicle</th><th>Departure</th><th>Status</th></tr>
            </thead>
            <tbody>
              @forelse($schedules as $schedule)
              <tr>
                <td>{{ $schedule->route->name ?? '—' }}</td>
                <td>{{ $schedule->driver?->name ?? '—' }}</td>
                <td>{{ $schedule->vehicle?->number ?? '—' }}</td>
                <td>{{ $schedule->estimated_departure_time ?? '—' }}</td>
                <td><span class="badge {{ $schedule->status=='Scheduled' ? 'badge-G' : ($schedule->status=='Completed' ? 'badge-B' : 'badge-ERR') }}">{{ $schedule->status }}</span></td>
              </tr>
              @empty
              <tr><td colspan="5" style="text-align:center;color:rgba(30,27,46,.55);">No Schedule has been configured yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Recent announcements (view-only; Admin publishes) --}}
      <div class="card mt-4">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
          <h3><i class="fas fa-bullhorn" style="color:#7C3AED;margin-right:6px;"></i>Recent Announcements</h3>
          <a href="{{ route('announcements.index') }}" class="btn btn-S" style="font-size:12px;">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body">
          @forelse($announcements as $announcement)
          <div style="padding:10px 0;border-bottom:1px solid rgba(30,27,46,.08);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
              <div style="font-weight:600;font-size:13px;color:var(--color-text);">{{ $announcement->title }}</div>
              <span class="badge badge-B" style="flex-shrink:0;">{{ ucfirst($announcement->audience) }}</span>
            </div>
            <div style="font-size:12px;color:var(--color-text-muted);margin-top:2px;">{{ \Illuminate\Support\Str::limit($announcement->body, 120) }}</div>
            <div style="font-size:11px;color:var(--color-text-faint);margin-top:4px;">{{ $announcement->created_at->format('d M Y') }}</div>
          </div>
          @empty
          <div style="text-align:center;color:rgba(30,27,46,.55);padding:14px 0;">No announcements yet.</div>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

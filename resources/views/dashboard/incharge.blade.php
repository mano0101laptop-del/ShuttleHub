@extends('layout.app')
@section('title', 'Dashboard — TM Service')
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
        Ground operations for today. Fleet setup, transport fee approvals, and enrollment
        decisions are handled by Admin — you're set up to run attendance, passenger
        lookups, and complaints.
      </p>

      {{-- Open Complaints Alert --}}
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

      {{-- Cancellation requests are informational here — only Admin can approve/reject them --}}
      @if(($stats['pending_cancellations'] ?? 0) > 0)
      <div class="alert-warning">
        <i class="fas fa-ban"></i>
        <div>
          <strong>{{ $stats['pending_cancellations'] }} cancellation request(s) waiting on Admin.</strong>
          <span style="opacity:.8;font-weight:400;">You can view them; only Admin can approve or reject.</span>
        </div>
      </div>
      @endif

      {{-- Quick actions --}}
      <div class="stats-grid" style="margin-bottom:4px;">
        <a href="{{ route('attendance.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(124,58,237,.15);color:#7C3AED;"><i class="fas fa-camera"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Attendance</div><div class="stat-lbl">Camera / Manual / PIN / QR Pass</div></div>
        </a>
        <a href="{{ route('passengers.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(37,99,235,.15);color:#2563EB;"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Passengers</div><div class="stat-lbl">Look up, edit stop/route, reset PIN</div></div>
        </a>
        <a href="{{ route('complaints.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(37,99,235,.15);color:#2563EB;"><i class="fas fa-comment-dots"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Complaints</div><div class="stat-lbl">First-line response</div></div>
        </a>
        <a href="{{ route('messages.index') }}" class="stat-card" style="text-decoration:none;">
          <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-comments"></i></div>
          <div class="stat-info"><div class="stat-val" style="font-size:15px;">Driver Messages</div><div class="stat-lbl">Coordinate with drivers</div></div>
        </a>
      </div>

      {{-- Today's attendance numbers --}}
      <div class="stats-grid mt-4">
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-user-check"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['present'] }}</div><div class="stat-lbl">Present Today</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(220,38,38,.15);color:#DC2626;"><i class="fas fa-user-xmark"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['absent'] }}</div><div class="stat-lbl">Absent Today</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(124,58,237,.15);color:#7C3AED;"><i class="fas fa-qrcode"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['qr_scans'] }}</div><div class="stat-lbl">QR Scans</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(217,119,6,.15);color:#D97706;"><i class="fas fa-shield-halved"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['pin_verifies'] }}</div><div class="stat-lbl">Security PIN Verifies</div></div>
        </div>
      </div>

      {{-- Recent attendance --}}
      <div class="card mt-4">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
          <h3>Recent Attendance</h3>
          <a href="{{ route('attendance.index') }}" class="btn btn-S" style="font-size:12px;">Open Attendance <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Name</th><th>Roll</th><th>Route</th><th>Time</th><th>Method</th><th>Status</th></tr>
            </thead>
            <tbody>
              @forelse($recent_attendance as $a)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $a->passenger->name ?? '—' }}</td>
                <td style="font-family:monospace;font-size:11px;">{{ $a->passenger->roll ?? '—' }}</td>
                <td>{{ $a->passenger->route->name ?? '—' }}</td>
                <td>{{ $a->time ? \Carbon\Carbon::parse($a->time)->format('h:i A') : '—' }}</td>
                <td style="font-size:11px;text-transform:capitalize;">{{ $a->method }}</td>
                <td><span class="badge {{ $a->status=='Present' ? 'badge-G' : 'badge-ERR' }}">{{ $a->status }}</span></td>
              </tr>
              @empty
              <tr><td colspan="7" style="text-align:center;color:rgba(30,27,46,.55);">No attendance records yet today.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

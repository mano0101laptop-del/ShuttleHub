@php
  $roleMeta = [
    'admin'     => ['icon' => 'fa-shield-halved',  'label' => 'Administrator'],
    'incharge'  => ['icon' => 'fa-user-tie',       'label' => 'Incharge'],
    'driver'    => ['icon' => 'fa-user-tie',       'label' => 'Driver'],
    'scanner'   => ['icon' => 'fa-qrcode',         'label' => 'QR Scanner'],
    'passenger' => ['icon' => 'fa-person-seat',    'label' => 'Passenger'],
  ][Auth::user()->role ?? 'admin'] ?? ['icon' => 'fa-user', 'label' => ucfirst(Auth::user()->role ?? 'User')];

  $role = Auth::user()->role ?? 'admin';
  $canManageFleet     = in_array($role, ['admin', 'incharge']);
  $canMarkAttendance  = in_array($role, ['incharge', 'scanner']);
  $canViewAttendanceReport = in_array($role, ['admin', 'incharge']);
@endphp
<aside class="sb" id="sidebar">
  <button class="sb-close" id="sb-close" aria-label="Close menu"><i class="fas fa-xmark"></i></button>

  <div class="sb-logo">
    <div class="sb-logo-icon"><i class="fas fa-circle-nodes"></i></div>
    <div class="sb-logo-text"><span>Shuttle</span> Hub</div>
  </div>
  <div class="sb-role-badge">
    <i class="fas {{ $roleMeta['icon'] }}"></i>
    <span>{{ $roleMeta['label'] }}</span>
  </div>
  <nav class="sb-nav">
    @if($role === 'scanner')
      <a href="{{ route('scanner.index') }}" class="sb-link {{ request()->is('scanner*') ? 'active' : '' }}">
        <i class="fas fa-camera"></i> Scanner Terminal
      </a>
    @else
      <a href="{{ route('dashboard') }}" class="sb-link {{ request()->is('dashboard') ? 'active' : '' }}">
        <i class="fas {{ $role === 'driver' ? 'fa-user-tie' : 'fa-gauge' }}"></i> {{ $role === 'driver' ? 'My Assignments' : 'Dashboard' }}
      </a>
    @endif

    @if($canManageFleet)
      <a href="{{ route('tmsroutes.index') }}" class="sb-link {{ request()->is('tmsroutes*') ? 'active' : '' }}">
        <i class="fas fa-route"></i> Routes
      </a>
      <a href="{{ route('schedule.index') }}" class="sb-link {{ request()->is('schedule*') ? 'active' : '' }}">
        <i class="fas fa-calendar-day"></i> Schedule
      </a>
      <a href="{{ route('vehicles.index') }}" class="sb-link {{ request()->is('vehicles*') ? 'active' : '' }}">
        <i class="fas fa-bus"></i> Buses & Vehicles
      </a>
      <a href="{{ route('drivers.index') }}" class="sb-link {{ request()->is('drivers*') ? 'active' : '' }}">
        <i class="fas fa-steering-wheel"></i> Drivers
      </a>
      <a href="{{ route('passengers.index') }}" class="sb-link {{ request()->is('passengers*') ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i> Passengers
      </a>
      <a href="{{ route('fee-payments.index') }}" class="sb-link {{ request()->is('fee-payments*') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i> Transport Fee
      </a>
      <a href="{{ route('announcements.index') }}" class="sb-link {{ request()->is('announcements*') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i> Announcements
      </a>
      <a href="{{ route('complaints.index') }}" class="sb-link {{ request()->is('complaints*') ? 'active' : '' }}">
        <i class="fas fa-comment-dots"></i> Complaints
      </a>
      <a href="{{ route('messages.index') }}" class="sb-link {{ request()->is('messages*') ? 'active' : '' }}">
        <i class="fas fa-comments"></i> Driver Messages
      </a>
    @endif

    @if($role === 'passenger')
      <a href="{{ route('fee.my') }}" class="sb-link {{ request()->is('my-fee*') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i> My Transport Fee
      </a>
      <a href="{{ route('complaints.create') }}" class="sb-link {{ request()->is('complaints*') ? 'active' : '' }}">
        <i class="fas fa-comment-dots"></i> Complaints & Feedback
      </a>
    @endif

    @if($role === 'driver')
      <a href="{{ route('messages.index') }}" class="sb-link {{ request()->is('messages*') ? 'active' : '' }}">
        <i class="fas fa-comments"></i> Messages
      </a>
    @endif

    @if($canMarkAttendance && $role !== 'scanner')
      <a href="{{ route('attendance.index') }}" class="sb-link {{ request()->is('attendance*') ? 'active' : '' }}">
        <i class="fas fa-qrcode"></i> Attendance
      </a>
    @endif

    @if($canViewAttendanceReport)
      <a href="{{ route('reports.attendance') }}" class="sb-link {{ request()->is('reports*') ? 'active' : '' }}">
        <i class="fas fa-chart-column"></i> Attendance Report
      </a>
    @endif

    @if($role === 'admin')
      <a href="{{ route('staff.index') }}" class="sb-link {{ request()->is('staff*') ? 'active' : '' }}">
        <i class="fas fa-user-shield"></i> Transport In-Charges
      </a>
    @endif
  </nav>
  <div class="sb-user">
    <div class="sb-user-card">
      <div class="sb-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
      <div>
        <div class="sb-uname">{{ Auth::user()->name ?? 'User' }}</div>
        <div class="sb-urole">{{ $roleMeta['label'] }}</div>
      </div>
      <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Sign out of Shuttle Hub?');">
        @csrf
        <button type="submit" class="sb-logout" title="Sign Out"><i class="fas fa-right-from-bracket"></i></button>
      </form>
    </div>
  </div>
</aside>
<div class="sb-scrim" id="sb-scrim"></div>

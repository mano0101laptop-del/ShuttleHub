@php
  $roleMeta = [
    'admin'     => ['icon' => 'fa-shield-halved',  'label' => 'Administrator'],
    'incharge'  => ['icon' => 'fa-user-tie',       'label' => 'Incharge'],
    'driver'    => ['icon' => 'fa-user-tie',       'label' => 'Driver'],
    'passenger' => ['icon' => 'fa-person-seat',    'label' => 'Passenger'],
  ][Auth::user()->role ?? 'admin'] ?? ['icon' => 'fa-user', 'label' => ucfirst(Auth::user()->role ?? 'User')];

  $role = Auth::user()->role ?? 'admin';
  $canViewOperations  = in_array($role, ['admin', 'incharge']);
  $passengerProfile = $role === 'passenger' ? Auth::user()->passenger : null;
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
    <a href="{{ route('dashboard') }}" class="sb-link {{ request()->is('dashboard') ? 'active' : '' }}">
      <i class="fas {{ $role === 'driver' ? 'fa-user-tie' : 'fa-gauge' }}"></i> {{ $role === 'driver' ? 'My Schedule' : 'Dashboard' }}
    </a>

    @if($canViewOperations)
      <a href="{{ route('tmsroutes.index') }}" class="sb-link {{ request()->is('tmsroutes*') ? 'active' : '' }}">
        <i class="fas fa-route"></i> Routes
      </a>
      <a href="{{ route('schedule.index') }}" class="sb-link {{ request()->is('schedule*') ? 'active' : '' }}">
        <i class="fas fa-calendar-check"></i> Schedule
      </a>
  
      <a href="{{ route('vehicles.index') }}" class="sb-link {{ request()->is('vehicles*') ? 'active' : '' }}">
        <i class="fas fa-bus"></i> Buses & Vehicles
      </a>
      <a href="{{ route('drivers.index') }}" class="sb-link {{ request()->is('drivers*') ? 'active' : '' }}">
        <i class="fas fa-user-tie"></i> Drivers
      </a>
      <a href="{{ route('passengers.index') }}" class="sb-link {{ request()->is('passengers*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> Passengers
      </a>
      <a href="{{ route('fee-payments.index') }}" class="sb-link {{ request()->is('fee-payments*') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i> Transport Fee
      </a>
      {{-- Announcements: Admin manages, Incharge has read-only view access. --}}
      <a href="{{ route('announcements.index') }}" class="sb-link {{ request()->is('announcements*') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i> Announcements
      </a>
      @if($role === 'admin')
      <a href="{{ route('complaints.index') }}" class="sb-link {{ request()->is('complaints*') ? 'active' : '' }}">
        <i class="fas fa-comment-dots"></i> Complaints
      </a>
      @endif
      <a href="{{ route('messages.index') }}" class="sb-link {{ request()->is('messages*') ? 'active' : '' }}">
        <i class="fas fa-comments"></i> Driver Messages
      </a>
    @endif

    @if($role === 'passenger')
      @if($passengerProfile)
      <a href="{{ route('passengers.show', $passengerProfile) }}" class="sb-link {{ request()->routeIs('passengers.show') || request()->routeIs('passengers.transport-card') ? 'active' : '' }}">
        <i class="fas fa-id-card"></i> My Profile & Card
      </a>
      @endif
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

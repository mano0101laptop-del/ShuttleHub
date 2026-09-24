@extends('layout.app')
@section('title', 'Passenger Transport Registration — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-register">
  <div class="auth-shell" style="max-width: 1040px;">

    <div class="auth-form-side" style="padding: 44px 56px;">
      <div class="auth-brand-row">
        <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
        <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
      </div>

      <h1 style="font-size:26px;">Passenger Transport Registration</h1>
      <p class="sub" style="margin-bottom:20px;">Fill in your details to request a transport pass</p>

      @if($errors->any())
        <div class="rd-err">
          @foreach($errors->all() as $e)
            <div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('register.post') }}" id="reg-form">
        @csrf
        {{-- Always passenger for public signup --}}
        <input type="hidden" name="role" value="passenger">

        <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);margin:4px 0 12px;">Passenger Information</div>

        <div class="form-grid">
          <div class="rd-wrap form-grid--full">
            <label class="rd-label">Full Name *</label>
            <div style="position:relative;">
              <i class="fas fa-user"></i>
              <input class="rd-input" type="text" name="name"
                     value="{{ old('name') }}" placeholder="Enter Full Name" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">College ID *</label>
            <div style="position:relative;">
              <i class="fas fa-id-badge"></i>
              <input class="rd-input" type="text" name="roll"
                     value="{{ old('roll') }}" placeholder="Enter College ID" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Contact Number *</label>
            <div style="position:relative;">
              <i class="fas fa-phone"></i>
              <input class="rd-input" type="tel" name="contact_number"
                     value="{{ old('contact_number') }}" placeholder="Enter Contact Number" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Passenger Type *</label>
            <div style="position:relative;">
              <i class="fas fa-user-tag"></i>
              <select class="rd-input" name="passenger_type" required>
                <option value="" disabled {{ old('passenger_type') ? '' : 'selected' }}>Student / Teacher / Staff</option>
                <option value="Student" {{ old('passenger_type')==='Student'?'selected':'' }}>Student</option>
                <option value="Teacher" {{ old('passenger_type')==='Teacher'?'selected':'' }}>Teacher</option>
                <option value="Staff" {{ old('passenger_type')==='Staff'?'selected':'' }}>Staff</option>
              </select>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Select Route *</label>
            <div style="position:relative;">
              <i class="fas fa-route"></i>
              <select class="rd-input" name="route_id" id="route-select" required>
                <option value="" disabled {{ old('route_id') ? '' : 'selected' }}>Select Route</option>
                @forelse($routes as $route)
                  <option value="{{ $route->id }}" {{ (string) old('route_id') === (string) $route->id ? 'selected' : '' }}>
                    {{ $route->name }} · {{ $route->from }} → {{ $route->to }}
                  </option>
                @empty
                  <option value="" disabled>No routes available yet</option>
                @endforelse
              </select>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Pickup Stop *</label>
            <div style="position:relative;">
              <i class="fas fa-location-dot"></i>
              <select class="rd-input" name="stop_id" id="stop-select" required>
                <option value="">— Select a route first —</option>
              </select>
            </div>
          </div>

          <div class="rd-wrap form-grid--full">
            <label class="rd-label">Address *</label>
            <div style="position:relative;">
              <i class="fas fa-house"></i>
              <input class="rd-input" type="text" name="address"
                     value="{{ old('address') }}" placeholder="Enter Address" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Emergency Contact *</label>
            <div style="position:relative;">
              <i class="fas fa-phone-volume"></i>
              <input class="rd-input" type="tel" name="emergency_contact"
                     value="{{ old('emergency_contact') }}" placeholder="Enter Emergency Contact" required>
            </div>
          </div>
        </div>

        <div style="font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-muted);margin:22px 0 12px;padding-top:18px;border-top:1px solid var(--color-border);">Account Details</div>

        <div class="form-grid">
          <div class="rd-wrap form-grid--full">
            <label class="rd-label">Email Address *</label>
            <div style="position:relative;">
              <i class="fas fa-envelope"></i>
              <input class="rd-input" type="email" name="email"
                     value="{{ old('email') }}" placeholder="you@example.com" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Password *</label>
            <div style="position:relative;" id="wrap-password">
              <i class="fas fa-lock"></i>
              <input class="rd-input" type="password" name="password"
                     id="reg-password"
                     placeholder="Min 6 characters" required minlength="6">
            </div>
          </div>

          <div class="rd-wrap" id="wrap-password-confirm">
            <label class="rd-label">Confirm Password *</label>
            <div style="position:relative;">
              <i class="fas fa-lock"></i>
              <input class="rd-input" type="password" name="password_confirmation"
                     id="reg-password-confirm"
                     placeholder="Repeat password" required minlength="6"
                     oninput="checkPasswords()">
            </div>
            <div id="pwd-status" class="pwd-status"></div>
          </div>
        </div>

        <label style="display:flex;align-items:flex-start;gap:8px;margin-top:18px;font-size:12.5px;color:var(--color-text-muted);cursor:pointer;">
          <input type="checkbox" name="confirm" value="1" required style="margin-top:2px;">
          <span>I confirm that the information provided is correct.</span>
        </label>

        <button type="submit" class="btn-login" style="margin-top:20px;">
          <i class="fas fa-paper-plane"></i> Submit Registration
        </button>
      </form>

      <div class="reg-link">
        Already have an account? <a href="{{ route('login') }}">Sign In →</a>
      </div>
    </div>

    <div class="auth-visual-side">
      <div class="auth-visual-content">
        <h2>Join Shuttle Hub</h2>
        <p>Apply once and your application goes straight to the transport office for review — no paperwork, no queues.</p>
        <div class="auth-feature-list">
          <div class="auth-feature"><i class="fas fa-bolt"></i> Fast, one-page application</div>
          <div class="auth-feature"><i class="fas fa-shield-halved"></i> Your password is securely hashed</div>
          <div class="auth-feature"><i class="fas fa-bell"></i> Track your approval status live</div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function checkPasswords() {
  const p = document.getElementById('reg-password').value;
  const c = document.getElementById('reg-password-confirm').value;
  const s = document.getElementById('pwd-status');
  if (!c) { s.textContent = ''; return; }
  if (p === c && p.length >= 6) {
    s.innerHTML = '<i class="fas fa-circle-check" style="color:#16A34A"></i> Passwords match.';
    s.style.color = '#16A34A';
  } else {
    s.innerHTML = '<i class="fas fa-circle-xmark" style="color:#DC2626"></i> Passwords do not match.';
    s.style.color = '#DC2626';
  }
}

document.getElementById('reg-form').addEventListener('submit', function(e) {
  const p  = document.getElementById('reg-password').value;
  const pc = document.getElementById('reg-password-confirm').value;

  if (p !== pc) {
    e.preventDefault();
    alert('Passwords do not match. Please re-enter.');
    document.getElementById('reg-password-confirm').focus();
  }
});

// Route → Pickup Stop cascading dropdown (same pattern used on the admin
// "Register Passenger" form) so applicants can only pick a stop that
// actually belongs to the route they selected.
@php
  $routeStopData = $routes->mapWithKeys(function ($route) {
    return [$route->id => $route->Stops->map(function ($stop) {
      return ['id' => $stop->id, 'name' => $stop->name];
    })->values()];
  });
@endphp
const routeStopData = @json($routeStopData);
const oldStopId = @json(old('stop_id'));

function populateStops() {
  const routeId = document.getElementById('route-select').value;
  const stopSelect = document.getElementById('stop-select');
  const stops = routeStopData[routeId] || null;

  stopSelect.innerHTML = '';

  if (!stops || !stops.length) {
    stopSelect.innerHTML = '<option value="">— Select a route first —</option>';
    return;
  }

  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Select Pickup Stop';
  placeholder.disabled = true;
  stopSelect.appendChild(placeholder);

  stops.forEach(function (stop) {
    const opt = document.createElement('option');
    opt.value = stop.id;
    opt.textContent = stop.name;
    if (String(oldStopId || '') === String(stop.id)) opt.selected = true;
    stopSelect.appendChild(opt);
  });

  if (!String(oldStopId || '')) placeholder.selected = true;
}

document.getElementById('route-select').addEventListener('change', populateStops);
document.addEventListener('DOMContentLoaded', populateStops);
</script>
@endsection

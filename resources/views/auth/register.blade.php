@extends('layout.app')
@section('title', 'Apply for Transport — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-register">
  <div class="auth-shell" style="max-width: 1040px;">

    <div class="auth-form-side" style="padding: 44px 56px;">
      <div class="auth-brand-row">
        <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
        <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
      </div>

      <h1 style="font-size:26px;">Apply for Transport</h1>
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

        <div class="form-grid">
          <div class="rd-wrap form-grid--full">
            <label class="rd-label">Full Name *</label>
            <div style="position:relative;">
              <i class="fas fa-user"></i>
              <input class="rd-input" type="text" name="name"
                     value="{{ old('name') }}" placeholder="Your full name" required>
            </div>
          </div>

          <div class="rd-wrap form-grid--full">
            <label class="rd-label">Email Address *</label>
            <div style="position:relative;">
              <i class="fas fa-envelope"></i>
              <input class="rd-input" type="email" name="email"
                     value="{{ old('email') }}" placeholder="you@example.com" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Department / Class *</label>
            <div style="position:relative;">
              <i class="fas fa-building-columns"></i>
              <input class="rd-input" type="text" name="department"
                     value="{{ old('department') }}" placeholder="e.g. CS — Sem 4" required>
            </div>
          </div>

          <div class="rd-wrap">
            <label class="rd-label">Preferred Stop <span style="opacity:.5;">(optional)</span></label>
            <div class="rd-select" id="stop-select">
              <button type="button" class="rd-select-trigger" id="stop-select-trigger">
                <i class="fas fa-map-pin"></i>
                <span class="rd-select-value placeholder" id="stop-select-value">Select your pickup stop</span>
                <i class="fas fa-chevron-down rd-select-caret"></i>
              </button>
              <div class="rd-select-panel" id="stop-select-panel">
                <div class="rd-select-option" data-value="" data-label="No preference">
                  <span class="rd-select-option-main">— No preference —</span>
                </div>
                @forelse($routes as $route)
                  <div class="rd-select-option" data-value="{{ $route->from }}" data-label="{{ $route->from }}">
                    <span class="rd-select-option-main">{{ $route->from }}</span>
                    <span class="rd-select-option-sub">{{ $route->name }}</span>
                  </div>
                @empty
                  <div class="rd-select-option rd-select-option--disabled">
                    <span class="rd-select-option-main">No routes available yet</span>
                  </div>
                @endforelse
              </div>
              <input type="hidden" name="stop" id="stop-input" value="{{ old('stop') }}">
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

        {{-- Security PIN --}}
        <div class="fp-box">
          <div class="fp-box-head">
            <i class="fas fa-fingerprint"></i>
            <div>
              <div class="t">Attendance PIN</div>
              <div class="d">Used to verify your identity when marking attendance.</div>
            </div>
          </div>

          <div class="form-grid">
            <div class="rd-wrap" style="margin-bottom:0;">
              <label class="rd-label">Security PIN *</label>
              <div style="position:relative;">
                <i class="fas fa-fingerprint"></i>
                <input class="rd-input" type="password" id="fp-pin" name="fingerprint_data"
                       placeholder="6–20 characters" minlength="6" maxlength="20"
                       oninput="checkFp()" required>
              </div>
            </div>

            <div class="rd-wrap" style="margin-bottom:0;">
              <label class="rd-label">Confirm PIN *</label>
              <div style="position:relative;">
                <i class="fas fa-fingerprint"></i>
                <input class="rd-input" type="password" id="fp-pin-confirm"
                       placeholder="Re-enter PIN" minlength="6" maxlength="20"
                       oninput="checkFp()" required>
              </div>
            </div>
          </div>
          <div id="reg-fp-status" class="pwd-status" style="margin-top:10px;"></div>
        </div>

        <button type="submit" class="btn-login" style="margin-top:20px;">
          <i class="fas fa-paper-plane"></i> Submit Application
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
          <div class="auth-feature"><i class="fas fa-shield-halved"></i> Your PIN is securely hashed</div>
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

function checkFp() {
  const p = document.getElementById('fp-pin').value;
  const c = document.getElementById('fp-pin-confirm').value;
  const s = document.getElementById('reg-fp-status');
  if (!c) { s.textContent = ''; return; }
  if (p === c && p.length >= 6) {
    s.innerHTML = '<i class="fas fa-circle-check" style="color:#16A34A"></i> PINs match.';
    s.style.color = '#16A34A';
  } else {
    s.innerHTML = '<i class="fas fa-circle-xmark" style="color:#DC2626"></i> PINs do not match.';
    s.style.color = '#DC2626';
  }
}

document.getElementById('reg-form').addEventListener('submit', function(e) {
  const p  = document.getElementById('reg-password').value;
  const pc = document.getElementById('reg-password-confirm').value;
  const fp = document.getElementById('fp-pin').value;
  const fc = document.getElementById('fp-pin-confirm').value;

  if (p !== pc) {
    e.preventDefault();
    alert('Passwords do not match. Please re-enter.');
    document.getElementById('reg-password-confirm').focus();
    return;
  }
  if (fp !== fc) {
    e.preventDefault();
    alert('Security PINs do not match. Please re-enter.');
    document.getElementById('fp-pin-confirm').focus();
  }
});

(function () {
  const wrap    = document.getElementById('stop-select');
  if (!wrap) return;

  const trigger = document.getElementById('stop-select-trigger');
  const panel   = document.getElementById('stop-select-panel');
  const valueEl = document.getElementById('stop-select-value');
  const input   = document.getElementById('stop-input');
  const options = Array.from(panel.querySelectorAll('.rd-select-option:not(.rd-select-option--disabled)'));

  function close() { wrap.classList.remove('open'); }
  function open()  { wrap.classList.add('open'); }

  function select(opt) {
    const val = opt.dataset.value;
    input.value = val;
    valueEl.textContent = opt.dataset.label;
    valueEl.classList.toggle('placeholder', val === '');
    options.forEach(o => o.classList.remove('active'));
    opt.classList.add('active');
  }

  trigger.addEventListener('click', function (e) {
    e.stopPropagation();
    wrap.classList.contains('open') ? close() : open();
  });

  options.forEach(function (opt) {
    opt.addEventListener('click', function () {
      select(opt);
      close();
    });
  });

  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) close();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') close();
  });

  // Re-apply the previously submitted value after a failed validation redirect.
  if (input.value) {
    const match = options.find(o => o.dataset.value === input.value);
    if (match) select(match);
  }
})();
</script>
@endsection

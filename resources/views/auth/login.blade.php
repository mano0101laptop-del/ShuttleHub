@extends('layout.app')
@section('title', 'Login — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-login">
  <div class="auth-shell">

    <div class="auth-form-side">
      <div class="auth-brand-row">
        <div class="auth-brand-icon"><i class="fas fa-bus"></i></div>
        <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
      </div>

      <h1>Welcome to Your Hub</h1>
      <p class="sub">Sign in to your Shuttle Hub account</p>

      @if($errors->has('email'))
        <div class="login-err show">
          <i class="fas fa-circle-exclamation"></i> {{ $errors->first('email') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <div class="lf-group">
          <label class="lf-label" for="login-email">Email Address</label>
          <div class="lf-input-wrap">
            <i class="fas fa-envelope"></i>
            <input class="lf-input" type="email" id="login-email" name="email"
                   placeholder="you@shuttlehub.edu" value="{{ old('email') }}" required autofocus>
          </div>
        </div>

<div class="lf-group">
    <label class="lf-label" for="login-pass">Password</label>

    <div class="lf-input-wrap password-input-wrap">

        <i class="fas fa-lock password-lock-icon"></i>

        <input class="lf-input"
               type="password"
               id="login-pass"
               name="password"
               placeholder="••••••••"
               required>

        <button type="button"
                class="password-toggle"
                onclick="togglePassword('login-pass', this)"
                aria-label="Show password">
            <i class="fas fa-eye-slash"></i>
        </button>

    </div>
</div>

        <label class="auth-remember">
          <input type="checkbox" name="remember"> Remember me
        </label>

        <button type="submit" class="btn-login">
          <i class="fas fa-arrow-right-to-bracket"></i> Sign In
        </button>
      </form>

      <div class="reg-link">
        New here? <a href="{{ route('register') }}">Apply for Transport →</a>
      </div>
    </div>

    <div class="auth-visual-side">
      <div class="auth-visual-content">
        <h2>Let’s Get Moving!</h2>
        <p>Everything in one place</p>
        <div class="shuttle-flow">

  <div class="flow-step">
    <div class="flow-icon">
      <i class="fas fa-map-location-dot"></i>
    </div>
    <div class="flow-text">
      <strong>Route</strong>
      <span>Plan your journey</span>
    </div>
    <div class="flow-number">01</div>
  </div>

  <div class="flow-connector">
    <span></span>
  </div>

  <div class="flow-step">
    <div class="flow-icon">
      <i class="fas fa-bus"></i>
    </div>
    <div class="flow-text">
      <strong>Ride</strong>
      <span>Assigned vehicle &amp; driver</span>
    </div>
    <div class="flow-number">02</div>
  </div>

  <div class="flow-connector">
    <span></span>
  </div>

  <div class="flow-step">
    <div class="flow-icon">
      <i class="fas fa-money-bill-wave"></i>
    </div>
    <div class="flow-text">
      <strong>Pay</strong>
      <span>Simple monthly transport fee</span>
    </div>
    <div class="flow-number">03</div>
  </div>

</div>
      </div>
    </div>

  </div>
</div>
<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');

    if (input.type === 'password') {
        // Password is hidden → show it
        input.type = 'text';

        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');

        button.setAttribute('aria-label', 'Hide password');
    } else {
        // Password is visible → hide it
        input.type = 'password';

        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');

        button.setAttribute('aria-label', 'Show password');
    }
}
</script>

@endsection


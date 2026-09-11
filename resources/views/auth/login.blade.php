@extends('layout.app')
@section('title', 'Login — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-login">
  <div class="auth-shell">

    <div class="auth-form-side">
      <div class="auth-brand-row">
        <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
        <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
      </div>

      <h1>Hello!</h1>
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
          <div class="lf-input-wrap">
            <i class="fas fa-lock"></i>
            <input class="lf-input" type="password" id="login-pass" name="password"
                   placeholder="••••••••" required>
          </div>
        </div>
        <label class="auth-remember">
          <input type="checkbox" name="remember"> Remember me
        </label>
        <button type="submit" class="btn-login">
          <i class="fas fa-arrow-right-to-bracket"></i> Sign In
        </button>
      </form>

      <div class="reg-link">New here? <a href="{{ route('register') }}">Apply for Transport →</a></div>
    </div>

    <div class="auth-visual-side">
      <div class="auth-visual-content">
        <h2>Welcome Back!</h2>
        <p>One account for every role on campus — track your shuttle, mark attendance, and manage the whole fleet from a single dashboard.</p>
        <div class="auth-feature-list">
          <div class="auth-feature"><i class="fas fa-route"></i> Live routes &amp; vehicle assignments</div>
          <div class="auth-feature"><i class="fas fa-qrcode"></i> QR &amp; PIN based attendance</div>
          <div class="auth-feature"><i class="fas fa-route"></i> Live routes & multi-stop tracking</div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

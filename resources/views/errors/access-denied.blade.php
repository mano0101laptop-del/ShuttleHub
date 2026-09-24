@extends('layout.app')
@section('title', 'Login Required — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card access-notice-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>

    <div class="error-icon error-icon-warning">
      <i class="fas fa-lock"></i>
    </div>

    @guest
      <div class="error-code">LOGIN REQUIRED</div>
      <h1>You don’t have access to this page</h1>
      <p>Please login to your Shuttle Hub account to continue.</p>
      <div class="error-actions">
        <a href="{{ route('login') }}" class="error-btn error-btn-primary">
          <i class="fas fa-arrow-right-to-bracket"></i> Login
        </a>
      </div>
    @else
      <div class="error-code">ACCESS RESTRICTED</div>
      <h1>You don’t have access to this page</h1>
      <p>Your account is signed in, but this page is not available for your role.</p>
      <div class="error-actions">
        <a href="{{ route('dashboard') }}" class="error-btn error-btn-primary">
          <i class="fas fa-house"></i> Go to Dashboard
        </a>
      </div>
    @endguest
  </div>
</div>
@endsection

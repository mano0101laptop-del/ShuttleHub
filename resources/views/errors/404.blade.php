@extends('layout.app')
@section('title', '404 Page Not Found — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-info"><i class="fas fa-map-location-dot"></i></div>
    <div class="error-code">404</div>
    <h1>Page Not Found</h1>
    <p>The page you’re looking for doesn’t exist, may have moved, or is no longer available.</p>
    <div class="error-actions">
      @auth
        <a href="{{ route('dashboard') }}" class="error-btn error-btn-primary"><i class="fas fa-house"></i> Go to Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="error-btn error-btn-primary"><i class="fas fa-arrow-right-to-bracket"></i> Login</a>
      @endauth
      <button type="button" class="error-btn error-btn-secondary" onclick="history.back()"><i class="fas fa-arrow-left"></i> Go Back</button>
    </div>
  </div>
</div>
@endsection

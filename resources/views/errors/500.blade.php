@extends('layout.app')
@section('title', '500 Server Error — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-danger"><i class="fas fa-triangle-exclamation"></i></div>
    <div class="error-code">500</div>
    <h1>Something Went Wrong</h1>
    <p>An unexpected server error occurred. Please retry the page or return to Shuttle Hub.</p>
    <div class="error-actions">
      <button type="button" class="error-btn error-btn-primary" onclick="location.reload()"><i class="fas fa-rotate-right"></i> Retry</button>
      @auth
        <a href="{{ route('dashboard') }}" class="error-btn error-btn-secondary"><i class="fas fa-house"></i> Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="error-btn error-btn-secondary"><i class="fas fa-arrow-right-to-bracket"></i> Login</a>
      @endauth
    </div>
  </div>
</div>
@endsection

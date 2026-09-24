@extends('layout.app')
@section('title', '419 Session Expired — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-warning"><i class="fas fa-clock-rotate-left"></i></div>
    <div class="error-code">419</div>
    <h1>Session Expired</h1>
    <p>Your session has expired for security. Please login again and retry your action.</p>
    <div class="error-actions">
      <a href="{{ route('login') }}" class="error-btn error-btn-primary"><i class="fas fa-arrow-right-to-bracket"></i> Login Again</a>
    </div>
  </div>
</div>
@endsection

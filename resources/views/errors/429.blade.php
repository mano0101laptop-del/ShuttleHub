@extends('layout.app')
@section('title', '429 Too Many Requests — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-warning"><i class="fas fa-gauge-high"></i></div>
    <div class="error-code">429</div>
    <h1>Too Many Requests</h1>
    <p>Too many requests were made in a short period. Please try again shortly.</p>
    <div class="error-actions">
      <button type="button" class="error-btn error-btn-primary" onclick="location.reload()"><i class="fas fa-rotate-right"></i> Try Again</button>
      @auth
        <a href="{{ route('dashboard') }}" class="error-btn error-btn-secondary"><i class="fas fa-house"></i> Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="error-btn error-btn-secondary"><i class="fas fa-arrow-right-to-bracket"></i> Login</a>
      @endauth
    </div>
  </div>
</div>
@endsection

@extends('layout.app')
@section('title', '403 Access Denied — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-danger"><i class="fas fa-shield-halved"></i></div>
    <div class="error-code">403</div>
    <h1>Access Denied</h1>
    <p>You don’t have permission to access this page.</p>
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

@extends('layout.app')
@section('title', '405 Action Not Allowed — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-danger"><i class="fas fa-ban"></i></div>
    <div class="error-code">405</div>
    <h1>Action Not Allowed</h1>
    <p>This request method is not supported for the page you are trying to access.</p>
    <div class="error-actions">
      <button type="button" class="error-btn error-btn-primary" onclick="history.back()"><i class="fas fa-arrow-left"></i> Go Back</button>
      @auth
        <a href="{{ route('dashboard') }}" class="error-btn error-btn-secondary"><i class="fas fa-house"></i> Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="error-btn error-btn-secondary"><i class="fas fa-arrow-right-to-bracket"></i> Login</a>
      @endauth
    </div>
  </div>
</div>
@endsection

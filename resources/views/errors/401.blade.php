@extends('layout.app')
@section('title', '401 Authentication Required — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-warning"><i class="fas fa-user-lock"></i></div>
    <div class="error-code">401</div>
    <h1>Authentication Required</h1>
    <p>Please login to your Shuttle Hub account before accessing this page.</p>
    <div class="error-actions">
      <a href="{{ route('login') }}" class="error-btn error-btn-primary"><i class="fas fa-arrow-right-to-bracket"></i> Login</a>
    </div>
  </div>
</div>
@endsection

@extends('layout.app')
@section('title', '503 Service Unavailable — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-info"><i class="fas fa-screwdriver-wrench"></i></div>
    <div class="error-code">503</div>
    <h1>Service Temporarily Unavailable</h1>
    <p>Shuttle Hub is temporarily unavailable. Please try again shortly.</p>
    <div class="error-actions">
      <button type="button" class="error-btn error-btn-primary" onclick="location.reload()"><i class="fas fa-rotate-right"></i> Try Again</button>
    </div>
  </div>
</div>
@endsection

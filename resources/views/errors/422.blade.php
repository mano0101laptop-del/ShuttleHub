@extends('layout.app')
@section('title', '422 Unable to Process Request — Shuttle Hub')
@section('content')
<div class="error-screen">
  <div class="error-card">
    <div class="error-brand">
      <div class="auth-brand-icon"><i class="fas fa-circle-nodes"></i></div>
      <div class="auth-brand-name"><span>Shuttle</span> Hub</div>
    </div>
    <div class="error-icon error-icon-warning"><i class="fas fa-circle-exclamation"></i></div>
    <div class="error-code">422</div>
    <h1>Unable to Process Request</h1>
    <p>Some submitted information could not be processed. Please go back, review the details, and try again.</p>
    <div class="error-actions">
      <button type="button" class="error-btn error-btn-primary" onclick="history.back()"><i class="fas fa-arrow-left"></i> Go Back</button>
      @auth
        <a href="{{ route('dashboard') }}" class="error-btn error-btn-secondary"><i class="fas fa-house"></i> Dashboard</a>
      @endauth
    </div>
  </div>
</div>
@endsection

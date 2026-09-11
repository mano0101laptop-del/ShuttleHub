@extends('layout.app')
@section('title', 'Add Transport In-Charge — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Add Transport In-Charge"><a href="{{ route('staff.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a></x-topbar>
    <div class="content">
      <div class="card" style="max-width:680px;margin:0 auto;">
        <div class="card-header"><h3><i class="fas fa-user-tie" style="color:var(--color-primary);margin-right:6px;"></i>New In-Charge Account</h3></div>
        <div class="card-body">
          @if($errors->any())<div class="alert-err mb-3">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
          <form method="POST" action="{{ route('staff.store') }}">@csrf
            <div class="lf-group"><label class="lf-label">Full Name *</label><div class="lf-input-wrap"><i class="fas fa-user"></i><input class="lf-input" name="name" value="{{ old('name') }}" required></div></div>
            <div class="lf-group"><label class="lf-label">Email *</label><div class="lf-input-wrap"><i class="fas fa-envelope"></i><input class="lf-input" type="email" name="email" value="{{ old('email') }}" required></div></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div class="lf-group"><label class="lf-label">Password *</label><div class="lf-input-wrap"><i class="fas fa-lock"></i><input class="lf-input" type="password" name="password" minlength="8" required></div></div>
              <div class="lf-group"><label class="lf-label">Confirm Password *</label><div class="lf-input-wrap"><i class="fas fa-lock"></i><input class="lf-input" type="password" name="password_confirmation" minlength="8" required></div></div>
            </div>
            <button class="btn btn-P" style="width:100%;justify-content:center;" type="submit"><i class="fas fa-user-plus"></i> Create In-Charge</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

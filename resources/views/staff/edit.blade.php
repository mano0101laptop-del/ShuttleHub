@extends('layout.app')
@section('title', 'Edit Transport In-Charge — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Edit Transport In-Charge"><a href="{{ route('staff.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a></x-topbar>
    <div class="content">
      <div class="card" style="max-width:680px;margin:0 auto;">
        <div class="card-header"><h3>{{ $incharge->name }}</h3></div>
        <div class="card-body">
          @if($errors->any())<div class="alert-err mb-3">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
          <form method="POST" action="{{ route('staff.update', $incharge) }}">@csrf @method('PUT')
            <div class="lf-group"><label class="lf-label">Full Name *</label><div class="lf-input-wrap"><i class="fas fa-user"></i><input class="lf-input" name="name" value="{{ old('name', $incharge->name) }}" required></div></div>
            <div class="lf-group"><label class="lf-label">Email *</label><div class="lf-input-wrap"><i class="fas fa-envelope"></i><input class="lf-input" type="email" name="email" value="{{ old('email', $incharge->email) }}" required></div></div>
            <div style="padding:12px 14px;border-radius:10px;background:rgba(124,58,237,.06);font-size:12px;color:var(--color-text-muted);margin-bottom:14px;">Leave password fields blank to keep the current password.</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div class="lf-group"><label class="lf-label">New Password</label><div class="lf-input-wrap"><i class="fas fa-lock"></i><input class="lf-input" type="password" name="password" minlength="8"></div></div>
              <div class="lf-group"><label class="lf-label">Confirm Password</label><div class="lf-input-wrap"><i class="fas fa-lock"></i><input class="lf-input" type="password" name="password_confirmation" minlength="8"></div></div>
            </div>
            <button class="btn btn-P" style="width:100%;justify-content:center;" type="submit"><i class="fas fa-save"></i> Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

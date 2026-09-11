@extends('layout.app')
@section('title', 'Incharge Profile — {{ $incharge->name }}')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Transport In-Charge Profile">
      <a href="{{ route('staff.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> All In-Charges</a>
    </x-topbar>

    <div class="content">
      <div class="card" style="max-width:520px;text-align:center;overflow:hidden;padding:0;">
        <div style="background:linear-gradient(135deg,#7C3AED,#A78BFA);padding:28px 16px;">
          <div style="width:88px;height:88px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin:0 auto;">
            <i class="fas fa-user-tie" style="font-size:32px;color:#fff;"></i>
          </div>
          <div style="font-size:18px;font-weight:800;color:#fff;margin-top:12px;">{{ $incharge->name }}</div>
          <span class="badge" style="margin-top:6px;background:rgba(255,255,255,.22);color:#fff;">Transport In-Charge</span>
        </div>
        <div style="padding:18px 24px;text-align:left;">
          <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
            <span style="font-size:12px;color:rgba(30,27,46,.65);">Email</span>
            <span style="font-size:12px;font-weight:600;">{{ $incharge->email }}</span>
          </div>
          <div style="display:flex;justify-content:space-between;">
            <span style="font-size:12px;color:rgba(30,27,46,.65);">Account Created</span>
            <span style="font-size:12px;font-weight:600;">{{ $incharge->created_at->format('d M Y') }}</span>
          </div>
        </div>
        <div style="border-top:1px solid rgba(30,27,46,.08);padding:14px 24px;display:flex;gap:10px;">
          <a href="{{ route('staff.edit', $incharge) }}" class="btn btn-S" style="flex:1;justify-content:center;font-size:12px;"><i class="fas fa-pen"></i> Edit Account</a>
          <form method="POST" action="{{ route('staff.destroy', $incharge) }}" style="flex:1;" onsubmit="return confirm('Remove {{ $incharge->name }}\'s Transport Incharge account? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ERR" style="flex:1;justify-content:center;font-size:12px;">
              <i class="fas fa-trash"></i> Remove Account
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

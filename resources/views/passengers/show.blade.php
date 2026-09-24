@extends('layout.app')
@section('title', 'Passenger Profile — {{ $passenger->name }}')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Passenger Profile & Card">
      @if(in_array(Auth::user()->role, ['admin','incharge']))
        <a href="{{ route('passengers.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> All Passengers</a>
      @else
        <a href="{{ route('dashboard') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Dashboard</a>
      @endif
    </x-topbar>

    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert-err mb-3">
          @foreach($errors->all() as $error)
            <div><i class="fas fa-circle-exclamation"></i> {{ $error }}</div>
          @endforeach
        </div>
      @endif

      <div class="detail-grid">

        {{-- ── Left: Passenger Card ── --}}
        <div>
          <div class="card" style="text-align:center;padding:0;overflow:hidden;">
            {{-- Card header strip --}}
            <div style="background:linear-gradient(135deg,#7C3AED,#A78BFA);padding:20px 16px 16px;position:relative;">
              <div style="font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:6px;">Transport Pass</div>
              <div style="display:flex;justify-content:center;margin:6px 0 10px;">
                @if(Auth::user()->role === 'passenger')
                  <form id="profile-photo-form" method="POST" action="{{ route('passengers.updateOwnPhoto') }}" enctype="multipart/form-data" style="margin:0;">
                    @csrf
                    <label for="profile-photo-input" title="Change profile photo" style="width:92px;height:108px;border-radius:10px;border:3px solid rgba(255,255,255,.8);background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;cursor:pointer;box-shadow:0 8px 20px rgba(38,18,80,.18);">
                      @if($passenger->photo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($passenger->photo_path) }}" alt="{{ $passenger->name }}" style="width:100%;height:100%;object-fit:cover;">
                      @else
                        <span style="font-size:30px;font-weight:800;color:#fff;">{{ strtoupper(substr($passenger->name,0,1)) }}</span>
                      @endif
                      <span style="position:absolute;right:5px;bottom:5px;width:24px;height:24px;border-radius:50%;background:#fff;color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:10px;box-shadow:0 2px 8px rgba(0,0,0,.2);"><i class="fas fa-camera"></i></span>
                    </label>
                    <input id="profile-photo-input" type="file" name="photo" accept="image/png,image/jpeg,image/webp" style="display:none;" onchange="document.getElementById('profile-photo-form').submit()">
                  </form>
                @else
                  <div style="width:92px;height:108px;border-radius:10px;border:3px solid rgba(255,255,255,.8);background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;overflow:hidden;box-shadow:0 8px 20px rgba(38,18,80,.18);">
                    @if($passenger->photo_path)
                      <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($passenger->photo_path) }}" alt="{{ $passenger->name }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                      <span style="font-size:30px;font-weight:800;color:#fff;">{{ strtoupper(substr($passenger->name,0,1)) }}</span>
                    @endif
                  </div>
                @endif
              </div>
              <div style="font-size:20px;font-weight:800;color:#fff;">{{ $passenger->name }}</div>
              <div style="font-size:13px;color:rgba(255,255,255,.7);margin-top:2px;">{{ $passenger->roll }}</div>
              @if(Auth::user()->role === 'passenger')
                <div style="font-size:10px;color:rgba(255,255,255,.75);margin-top:5px;"><i class="fas fa-camera"></i> Click photo to upload or replace</div>
              @endif
              <div style="position:absolute;top:12px;right:14px;">
                <span class="badge {{ $passenger->status=='Active' ? 'badge-G' : 'badge-ERR' }}" style="font-size:10px;">{{ $passenger->status }}</span>
              </div>
            </div>

            {{-- Info rows --}}
            <div style="padding:20px;text-align:left;">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">College ID</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->roll }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Passenger Type</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->passenger_type ?? '—' }}</span>
              </div>
              @if($passenger->department)
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Department</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->department }}</span>
              </div>
              @endif
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Contact Number</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->contact_number ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Emergency Contact</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->emergency_contact ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Address</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;text-align:right;max-width:60%;">{{ $passenger->address ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Route</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->route->name ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Stop</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->Stop?->name ?? $passenger->stop ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Vehicle</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->assignedVehicle?->number ?? $passenger->route?->vehicle?->number ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-top:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Driver</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->assignedDriver?->name ?? $passenger->route?->vehicle?->driver?->name ?? '—' }}</span>
              </div>
              @if($passenger->pickup_time || $passenger->dropoff_time)
              <div style="display:flex;justify-content:space-between;margin-top:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Pickup / Drop-off</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;"><i class="fas fa-clock" style="color:var(--color-warning);"></i> {{ $passenger->pickup_time ?? '—' }} / {{ $passenger->dropoff_time ?? '—' }}</span>
              </div>
              @endif
              @if($passenger->route && $passenger->route->Stops->count())
              <div style="margin-top:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Route Stops</span>
                <div style="font-size:12px;color:var(--color-text);font-weight:600;margin-top:4px;">
                  {{ $passenger->route->Stops->pluck('name')->implode(' → ') }}
                </div>
              </div>
              @endif
            </div>

            {{-- Actions --}}
            <div style="border-top:1px solid rgba(30,27,46,.08);padding:14px 20px;display:flex;gap:10px;">
              @if(Auth::user()->role === 'admin')
                <a href="{{ route('passengers.edit', $passenger) }}" class="btn btn-S" style="flex:1;justify-content:center;font-size:12px;">
                  <i class="fas fa-edit"></i> Edit
                </a>
              @endif
              @if($passenger->isApproved())
                <a href="{{ route('passengers.transport-card', $passenger) }}" class="btn btn-P" style="flex:1;justify-content:center;font-size:12px;">
                  <i class="fas fa-download"></i> Digital Card
                </a>
              @endif
            </div>
          </div>
        </div>

        {{-- ── Right: Fee status ── --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

          {{-- Transport Fee status --}}
          @php $feeActive = $passenger->activeFeePayment(); $feePending = $passenger->pendingFeePayment(); @endphp
          <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
              <h3><i class="fas fa-receipt" style="color:var(--color-warning);margin-right:6px;"></i>Transport Fee</h3>
              @if($feeActive)
                <span class="badge badge-G">Active until {{ $feeActive->valid_until->format('d M Y') }}</span>
              @elseif($feePending)
                <span class="badge badge-W">Pending Review ({{ $feePending->monthLabel() }})</span>
              @else
                <span class="badge badge-ERR">No Active Pass</span>
              @endif
            </div>
            @if(in_array(Auth::user()->role, ['admin', 'incharge']))
              <div class="card-body">
                <a href="{{ route('fee-payments.index') }}" class="btn btn-S btn-sm">
                  <i class="fas fa-money-bill-wave"></i> {{ Auth::user()->role === 'admin' ? 'Manage Transport Fee' : 'View Transport Fee' }}
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

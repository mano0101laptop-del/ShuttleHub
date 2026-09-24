@extends('layout.app')
@section('title', 'Driver Profile — {{ $driver->name }}')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Driver Profile">
      <a href="{{ route('drivers.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> All Drivers</a>
    </x-topbar>

    <div class="content">
      <div class="detail-grid">

        {{-- ── Left: Photo + quick facts ── --}}
        <div>
          <div class="card" style="text-align:center;overflow:hidden;padding:0;">
            <div style="background:linear-gradient(135deg,#7C3AED,#A78BFA);padding:28px 16px 40px;position:relative;">
              @if($driver->photo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($driver->photo_path) }}"
                     style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:4px solid rgba(255,255,255,.5);">
              @else
                <div style="width:96px;height:96px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                  <i class="fas fa-user" style="font-size:36px;color:#fff;"></i>
                </div>
              @endif
              <div style="font-size:18px;font-weight:800;color:#fff;margin-top:12px;">{{ $driver->name }}</div>
              <span class="badge {{ $driver->status=='Active' ? 'badge-G' : 'badge-ERR' }}" style="margin-top:6px;">{{ $driver->status }}</span>
            </div>

            <div style="padding:18px 20px;text-align:left;">
              <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Phone</span>
                <span style="font-size:12px;font-weight:600;">{{ $driver->phone }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Email</span>
                <span style="font-size:12px;font-weight:600;">{{ $driver->email ?? $driver->user->email ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">License #</span>
                <span style="font-size:12px;font-weight:600;">{{ $driver->license }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">CNIC #</span>
                <span style="font-size:12px;font-weight:600;">{{ $driver->cnic }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Date of Birth</span>
                <span style="font-size:12px;font-weight:600;">{{ $driver->dob?->format('d M Y') ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Address</span>
                <span style="font-size:12px;font-weight:600;text-align:right;max-width:60%;">{{ $driver->address ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Experience</span>
                <span style="font-size:12px;font-weight:600;">{{ $driver->experience ?? '—' }}</span>
              </div>
            </div>

            @if($driver->user)
              <div style="border-top:1px solid rgba(30,27,46,.08);padding:14px 20px;">
                <a href="{{ route('messages.thread', $driver->user_id) }}" class="btn btn-S" style="width:100%;justify-content:center;font-size:12px;">
                  <i class="fas fa-comment"></i> Message Driver
                </a>
              </div>
            @endif
          </div>
        </div>

        {{-- ── Right: Documents, assignment, reference, complaints ── --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

          {{-- CNIC Documents --}}
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-id-card" style="color:var(--color-primary);margin-right:6px;"></i>CNIC Documents</h3></div>
            <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
              <div>
                <div style="font-size:11px;color:rgba(30,27,46,.6);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Front</div>
                @if($driver->cnic_front_path)
                  <img src="{{ route('drivers.cnic', [$driver, 'front']) }}" style="width:100%;border-radius:10px;border:1px solid rgba(30,27,46,.1);">
                @else
                  <div style="border:1px dashed rgba(30,27,46,.2);border-radius:10px;padding:24px;text-align:center;color:rgba(30,27,46,.4);font-size:12px;">Not uploaded</div>
                @endif
              </div>
              <div>
                <div style="font-size:11px;color:rgba(30,27,46,.6);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">Back</div>
                @if($driver->cnic_back_path)
                  <img src="{{ route('drivers.cnic', [$driver, 'back']) }}" style="width:100%;border-radius:10px;border:1px solid rgba(30,27,46,.1);">
                @else
                  <div style="border:1px dashed rgba(30,27,46,.2);border-radius:10px;padding:24px;text-align:center;color:rgba(30,27,46,.4);font-size:12px;">Not uploaded</div>
                @endif
              </div>
            </div>
          </div>

          {{-- Vehicle / Route assignment --}}
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-bus" style="color:var(--color-warning);margin-right:6px;"></i>Assignment</h3></div>
            <div class="card-body">
              <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                <div class="tile">
                  <div class="tile-lbl">Vehicle</div>
                  <div class="tile-val">{{ $driver->vehicle->number ?? '— Unassigned —' }}</div>
                </div>
                <div class="tile">
                  <div class="tile-lbl">Route</div>
                  <div class="tile-val">{{ $driver->vehicle->route->name ?? '—' }}</div>
                </div>
              </div>
              @if($driver->vehicle && $driver->vehicle->route && $driver->vehicle->route->Stops->count())
                <div style="margin-top:14px;">
                  <div style="font-size:12px;color:rgba(30,27,46,.65);margin-bottom:6px;">Route Stops</div>
                  <div style="font-size:12px;font-weight:600;">
                    {{ $driver->vehicle->route->Stops->pluck('name')->implode(' → ') }}
                  </div>
                </div>
              @endif
            </div>
          </div>

          {{-- Emergency reference --}}
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-address-card" style="color:var(--color-info);margin-right:6px;"></i>Reference / Emergency Contact</h3></div>
            <div class="card-body">
              <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                <div class="tile"><div class="tile-lbl">Name</div><div class="tile-val">{{ $driver->reference_name ?? '—' }}</div></div>
                <div class="tile"><div class="tile-lbl">Phone</div><div class="tile-val">{{ $driver->reference_phone ?? '—' }}</div></div>
                <div class="tile"><div class="tile-lbl">Relation</div><div class="tile-val">{{ $driver->reference_relation ?? '—' }}</div></div>
                <div class="tile"><div class="tile-lbl">Address</div><div class="tile-val">{{ $driver->reference_address ?? '—' }}</div></div>
              </div>
            </div>
          </div>

          {{-- Complaints against this driver (Admin only) --}}
          @if(Auth::user()->role === 'admin')
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-triangle-exclamation" style="color:var(--color-danger);margin-right:6px;"></i>Complaints Against Driver</h3></div>
            <div class="card-body">
              <table class="tbl">
                <thead><tr><th>From</th><th>Subject</th><th>Status</th></tr></thead>
                <tbody>
                  @forelse($driver->complaintsAgainst as $c)
                  <tr>
                    <td>{{ $c->passenger->name ?? '—' }}</td>
                    <td>{{ $c->subject }}</td>
                    <td>
                      @if($c->status === 'resolved') <span class="badge badge-G">Resolved</span>
                      @elseif($c->status === 'reviewed') <span class="badge badge-B">Reviewed</span>
                      @else <span class="badge badge-W">Open</span> @endif
                    </td>
                  </tr>
                  @empty
                  <tr class="empty-row"><td colspan="3">No complaints filed against this driver.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          @endif

        </div>
      </div>
    </div>
  </div>
</div>
@endsection

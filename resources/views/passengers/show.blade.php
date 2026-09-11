@extends('layout.app')
@section('title', 'Passenger Card — {{ $passenger->name }}')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Passenger Card">
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

      <div class="detail-grid">

        {{-- ── Left: QR Card ── --}}
        <div>
          <div class="card" style="text-align:center;padding:0;overflow:hidden;">
            {{-- Card header strip --}}
            <div style="background:linear-gradient(135deg,#7C3AED,#A78BFA);padding:20px 16px 14px;position:relative;">
              <div style="font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:6px;">Transport Pass</div>
              <div style="font-size:20px;font-weight:800;color:#fff;">{{ $passenger->name }}</div>
              <div style="font-size:13px;color:rgba(255,255,255,.7);margin-top:2px;">{{ $passenger->roll }}</div>
              <div style="position:absolute;top:12px;right:14px;">
                <span class="badge {{ $passenger->status=='Active' ? 'badge-G' : 'badge-ERR' }}" style="font-size:10px;">{{ $passenger->status }}</span>
              </div>
            </div>

            {{-- QR Code — only rendered once approved AND the transport fee is paid & approved --}}
            <div style="padding:24px 20px 16px;">
              @if($passenger->qrIsActive())
                <div style="background:#fff;border-radius:14px;padding:14px;display:inline-block;box-shadow:0 4px 20px rgba(0,0,0,.3);">
                  <div id="qrcode"></div>
                </div>
                <div style="font-size:11px;color:rgba(30,27,46,.6);margin-top:10px;letter-spacing:.08em;">
                  TOKEN: <span style="color:rgba(30,27,46,.6);font-family:monospace;letter-spacing:.12em;">{{ $passenger->qr_token }}</span>
                </div>
                <div style="font-size:11px;color:rgba(30,27,46,.55);margin-top:4px;">Scan to mark attendance</div>
              @else
                <div style="background:rgba(255,255,255,.12);border:1px dashed rgba(255,255,255,.4);border-radius:14px;padding:32px 14px;display:flex;flex-direction:column;align-items:center;gap:8px;">
                  <i class="fas fa-lock" style="font-size:22px;color:#fff;"></i>
                  <div style="font-size:12px;color:#fff;font-weight:600;text-align:center;">
                    QR locked until {{ $passenger->isApproved() ? 'transport fee is paid & approved' : 'application is approved' }}
                  </div>
                </div>
              @endif
            </div>

            {{-- Info rows --}}
            <div style="border-top:1px solid rgba(30,27,46,.08);padding:14px 20px;text-align:left;">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Department</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->department }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Route</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->route->name ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Stop</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->routeStop?->name ?? $passenger->stop ?? '—' }}</span>
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
              @if($passenger->route && $passenger->route->routeStops->count())
              <div style="margin-top:10px;">
                <span style="font-size:12px;color:rgba(30,27,46,.65);">Route Stops</span>
                <div style="font-size:12px;color:var(--color-text);font-weight:600;margin-top:4px;">
                  {{ $passenger->route->routeStops->pluck('name')->implode(' → ') }}
                </div>
              </div>
              @endif
            </div>

            {{-- Fingerprint badge --}}
            <div style="border-top:1px solid rgba(30,27,46,.08);padding:12px 20px;display:flex;align-items:center;gap:8px;justify-content:center;">
              @if($passenger->fingerprint_enrolled)
                <i class="fas fa-fingerprint" style="color:#16A34A;font-size:16px;"></i>
                <span style="font-size:12px;color:#16A34A;font-weight:600;">Security PIN Set</span>
              @else
                <i class="fas fa-fingerprint" style="color:rgba(30,27,46,.25);font-size:16px;"></i>
                <span style="font-size:12px;color:rgba(30,27,46,.55);">No PIN Set</span>
              @endif
            </div>

            {{-- Actions --}}
            <div style="border-top:1px solid rgba(30,27,46,.08);padding:14px 20px;display:flex;gap:10px;">
              @if($passenger->qrIsActive())
                <a href="{{ route('passengers.qr', $passenger) }}" class="btn btn-P" style="flex:1;justify-content:center;font-size:12px;">
                  <i class="fas fa-download"></i> Download QR
                </a>
              @endif
              @if(in_array(Auth::user()->role, ['admin','incharge']))
                <a href="{{ route('passengers.edit', $passenger) }}" class="btn btn-S" style="flex:1;justify-content:center;font-size:12px;">
                  <i class="fas fa-edit"></i> Edit
                </a>
              @endif
            </div>

            @if(in_array(Auth::user()->role, ['admin','incharge']))
            <div style="border-top:1px solid rgba(30,27,46,.08);padding:14px 20px;">
              <form method="POST" action="{{ route('passengers.regenerateQr', $passenger) }}"
                    onsubmit="return confirm('This invalidates {{ $passenger->name }}\'s current QR card immediately — the old card will stop working. A new card must be printed and issued. Continue?');">
                @csrf
                <button type="submit" class="btn btn-S" style="width:100%;justify-content:center;font-size:12px;color:#DC2626;">
                  <i class="fas fa-rotate"></i> Card Lost / Compromised — Regenerate QR
                </button>
              </form>
            </div>
            @endif
          </div>
        </div>

        {{-- ── Right: Stats + Attendance History ── --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

          {{-- Stats --}}
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div class="stat-card">
              <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-calendar-check"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $presentDays }}</div><div class="stat-lbl">Days Present</div></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon" style="background:rgba(220,38,38,.15);color:#DC2626;"><i class="fas fa-calendar-xmark"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $totalDays - $presentDays }}</div><div class="stat-lbl">Days Absent</div></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon" style="background:rgba(124,58,237,.15);color:#7C3AED;"><i class="fas fa-percent"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $attendancePct }}%</div><div class="stat-lbl">Attendance Rate</div></div>
            </div>
          </div>

          {{-- Transport Fee status --}}
          @php $feeActive = $passenger->activeFeePayment(); $feePending = $passenger->pendingFeePayment(); @endphp
          <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
              <h3><i class="fas fa-receipt" style="color:var(--color-warning);margin-right:6px;"></i>Transport Fee</h3>
              @if($feeActive)
                <span class="badge badge-G">Active until {{ $feeActive->qr_expires_at->format('d M Y') }}</span>
              @elseif($feePending)
                <span class="badge badge-W">Pending Review ({{ $feePending->monthLabel() }})</span>
              @else
                <span class="badge badge-ERR">No Active Pass</span>
              @endif
            </div>
            @if(in_array(Auth::user()->role, ['admin', 'incharge']))
              <div class="card-body">
                <a href="{{ route('fee-payments.index') }}" class="btn btn-S btn-sm">
                  <i class="fas fa-money-bill-wave"></i> Manage Transport Fee
                </a>
              </div>
            @endif
          </div>

          {{-- Attendance history --}}
          <div class="card">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
              <h3>Attendance History</h3>
              <span style="font-size:12px;color:rgba(30,27,46,.6);">Last 30 records</span>
            </div>
            <div class="card-body">
              <table class="tbl">
                <thead>
                  <tr><th>Date</th><th>Time</th><th>Method</th><th>Status</th></tr>
                </thead>
                <tbody>
                  @forelse($passenger->attendances()->latest('date')->take(30)->get() as $a)
                  <tr>
                    <td>{{ $a->date->format('d M Y') }}</td>
                    <td>{{ $a->time ? \Carbon\Carbon::parse($a->time)->format('h:i A') : '—' }}</td>
                    <td>
                      @if($a->method === 'qr')
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#7C3AED;"><i class="fas fa-qrcode"></i> QR Scan</span>
                      @elseif($a->method === 'fingerprint')
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#16A34A;"><i class="fas fa-fingerprint"></i> Security PIN</span>
                      @else
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:rgba(30,27,46,.65);"><i class="fas fa-keyboard"></i> Manual</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge {{ $a->status=='Present' ? 'badge-G' : 'badge-ERR' }}">{{ $a->status }}</span>
                    </td>
                  </tr>
                  @empty
                  <tr><td colspan="4" style="text-align:center;color:rgba(30,27,46,.55);">No attendance records yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@if($passenger->qrIsActive())
{{-- QR Code generation using qrcode.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  new QRCode(document.getElementById("qrcode"), {
    text: "{{ route('attendance.scan', ['token' => $passenger->qr_token]) }}",
    width: 180,
    height: 180,
    colorDark: "#1e1b4b",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
  });
</script>
@endif
@endsection

@extends('layout.app')
@section('title', 'My Transport Dashboard — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">

  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="My Transport Dashboard" />

    <div class="content">

      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert-err">{{ session('error') }}</div>
      @endif

      @if(isset($announcements) && $announcements->count())
        <div class="card mb-3">
          <div class="card-header"><h3><i class="fas fa-bullhorn" style="color:var(--color-primary);margin-right:6px;"></i>Announcements</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
            @foreach($announcements as $an)
              <div style="border-left:3px solid var(--color-primary);padding:6px 12px;">
                <div style="font-weight:600;font-size:13px;">{{ $an->title }}</div>
                <div style="font-size:12px;color:var(--color-text-muted);">{{ $an->body }}</div>
                <div style="font-size:10px;color:var(--color-text-faint);margin-top:2px;">{{ $an->created_at->diffForHumans() }}</div>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      @if(!$passenger)
        {{-- No passenger record linked --}}
        <div class="card" style="text-align:center;padding:48px;">
          <i class="fas fa-circle-exclamation" style="font-size:40px;color:var(--color-warning);margin-bottom:16px;"></i>
          <h3 style="margin-bottom:8px;">No Application Found</h3>
          <p style="color:var(--color-text-muted);">Your account is not linked to a transport application. Please contact the admin.</p>
        </div>

      @elseif($passenger->isPending())
        {{-- ── PENDING STATE ── --}}
        <div class="card" style="margin-bottom:20px;">
          <div style="padding:32px;text-align:center;">
            <div style="width:72px;height:72px;background:var(--color-warning-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;border:2px solid rgba(217,119,6,.3);">
              <i class="fas fa-hourglass-half" style="font-size:28px;color:var(--color-warning);"></i>
            </div>
            <h2 style="margin-bottom:8px;">Application Under Review</h2>
            <p style="color:var(--color-text-muted);max-width:440px;margin:0 auto 24px;line-height:1.6;">
              Your transport application has been submitted successfully. The admin will review your request shortly. You will have full access to your pass, QR code, and fee details once approved.
            </p>
            <span style="display:inline-block;background:var(--color-warning-soft);color:var(--color-warning);border:1px solid rgba(217,119,6,.3);border-radius:20px;padding:6px 20px;font-size:13px;font-weight:600;">
              <i class="fas fa-clock"></i> Pending Approval
            </span>
          </div>
        </div>

        {{-- Show submitted details only --}}
        <div class="card">
          <div class="card-header"><h3>Your Submitted Details</h3></div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
              <div class="tile">
                <div class="tile-lbl">Full Name</div>
                <div class="tile-val">{{ $passenger->name }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Registration #</div>
                <div class="tile-val" style="font-family:monospace;">{{ $passenger->roll }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Department</div>
                <div class="tile-val">{{ $passenger->department }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Preferred Stop</div>
                <div class="tile-val">{{ $passenger->stop ?? '—' }}</div>
              </div>
            </div>
          </div>
        </div>

      @elseif($passenger->isRejected())
        {{-- ── REJECTED STATE ── --}}
        <div class="card" style="padding:32px;text-align:center;">
          <div style="width:72px;height:72px;background:var(--color-danger-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;border:2px solid rgba(220,38,38,.3);">
            <i class="fas fa-circle-xmark" style="font-size:28px;color:var(--color-danger);"></i>
          </div>
          <h2 style="margin-bottom:8px;">Application Rejected</h2>
          <p style="color:var(--color-text-muted);max-width:440px;margin:0 auto 16px;line-height:1.6;">
            Unfortunately your transport application was not approved. Please contact the transport office for further information.
          </p>
          <span style="display:inline-block;background:var(--color-danger-soft);color:var(--color-danger);border:1px solid rgba(220,38,38,.3);border-radius:20px;padding:6px 20px;font-size:13px;font-weight:600;">
            <i class="fas fa-ban"></i> Application Rejected
          </span>
        </div>

      @else
        {{-- ── APPROVED STATE — full dashboard ── --}}

        {{-- Welcome banner --}}
        <div style="background:var(--gradient-brand);border-radius:16px;padding:24px 28px;margin-bottom:20px;display:flex;align-items:center;gap:20px;box-shadow:var(--shadow-brand);">
          <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-id-card" style="font-size:24px;color:#fff;"></i>
          </div>
          <div>
            <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;">Transport Pass Active</div>
            <div style="font-size:22px;font-weight:800;color:#fff;">{{ $passenger->name }}</div>
            <div style="font-size:13px;color:rgba(255,255,255,.85);">{{ $passenger->roll }} &nbsp;·&nbsp; {{ $passenger->department }}</div>
          </div>
          <div style="margin-left:auto;">
            <span class="badge" style="font-size:12px;padding:6px 14px;background:rgba(255,255,255,.22);color:#fff;"><i class="fas fa-circle-check"></i> Approved</span>
          </div>
        </div>

        {{-- Stats --}}
        <div class="stats-grid" style="margin-bottom:20px;">
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--color-success-soft);color:var(--color-success);"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-info"><div class="stat-val">{{ $presentDays }}</div><div class="stat-lbl">Days Present</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--color-danger-soft);color:var(--color-danger);"><i class="fas fa-calendar-xmark"></i></div>
            <div class="stat-info"><div class="stat-val">{{ $totalDays - $presentDays }}</div><div class="stat-lbl">Days Absent</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--color-primary-soft);color:var(--color-primary);"><i class="fas fa-percent"></i></div>
            <div class="stat-info"><div class="stat-val">{{ $attendancePct }}%</div><div class="stat-lbl">Attendance Rate</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--color-warning-soft);color:var(--color-warning);"><i class="fas fa-route"></i></div>
            <div class="stat-info"><div class="stat-val">{{ $passenger->route->name ?? '—' }}</div><div class="stat-lbl">My Route</div></div>
          </div>
        </div>

        {{-- Today's Assignment — day-specific driver/vehicle/timing, if the Incharge has set one --}}
        <div class="card mb-3">
          <div class="card-header"><h3><i class="fas fa-calendar-day" style="color:var(--color-warning);margin-right:6px;"></i>Today's Trip</h3></div>
          <div class="card-body">
            @if($todaysAssignment)
              <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:10px;">
                <div class="tile" style="flex:1;min-width:140px;">
                  <div class="tile-lbl">Driver Today</div>
                  <div class="tile-val">{{ $todaysAssignment->driver?->name ?? $passenger->route->vehicle?->driver?->name ?? '—' }}</div>
                </div>
                <div class="tile" style="flex:1;min-width:140px;">
                  <div class="tile-lbl">Vehicle Today</div>
                  <div class="tile-val">{{ $todaysAssignment->vehicle?->number ?? $passenger->route->vehicle?->number ?? '—' }}</div>
                </div>
                <div class="tile" style="flex:1;min-width:140px;">
                  <div class="tile-lbl">Departure</div>
                  <div class="tile-val">{{ $todaysAssignment->estimated_departure_time ?? '—' }}</div>
                </div>
              </div>
              @php
                $myStopRow = $todaysAssignment->stopsWithTimes()->first(fn($row) => $row['stop']->name === $passenger->stop);
                $myStopTimeToday = $myStopRow['time'] ?? null;
              @endphp
              @if($myStopTimeToday)
                <div class="tile" style="margin-bottom:10px;">
                  <div class="tile-lbl">ETA at My Stop Today</div>
                  <div class="tile-val"><i class="fas fa-clock" style="color:var(--color-warning);"></i> {{ $myStopTimeToday }}</div>
                </div>
              @endif
              @if($todaysAssignment->route->routeStops->count())
                <div class="tile" style="margin-bottom:10px;">
                  <div class="tile-lbl">All Stop Timings Today</div>
                  <div class="tile-val" style="font-size:13px;display:flex;flex-direction:column;gap:4px;margin-top:4px;">
                    @foreach($todaysAssignment->stopsWithTimes() as $row)
                      <div style="display:flex;justify-content:space-between;gap:10px;max-width:420px;">
                        <span><i class="fas fa-map-pin" style="color:var(--color-primary);font-size:11px;margin-right:4px;"></i>{{ $row['stop']->name }}</span>
                        <span style="color:var(--color-text-muted);font-size:12px;">{{ $row['time'] ?? '—' }}</span>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif
              @if($todaysAssignment->notes)
                <div class="tile" style="margin-bottom:10px;">
                  <div class="tile-lbl">Notes from Transport Office</div>
                  <div class="tile-val" style="font-size:13px;">{{ $todaysAssignment->notes }}</div>
                </div>
              @endif
              <span class="badge {{ $todaysAssignment->status=='Scheduled' ? 'badge-G' : ($todaysAssignment->status=='Completed' ? 'badge-B' : 'badge-ERR') }}">{{ $todaysAssignment->status }}</span>
            @else
              <p style="font-size:12px;color:var(--color-text-faint);">No special update for today — your route is running its usual schedule.</p>
            @endif
          </div>
        </div>

        <div class="detail-grid--narrow">

          {{-- QR Card --}}
          <div class="card" style="text-align:center;padding:0;overflow:hidden;">
            <div style="background:var(--gradient-brand);padding:16px 16px 12px;">
              <div style="font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;">My Transport QR</div>
              <div style="font-size:16px;font-weight:700;color:#fff;">{{ $passenger->name }}</div>
              <div style="font-size:12px;color:rgba(255,255,255,.85);">{{ $passenger->roll }}</div>
            </div>
            @if($passenger->qrIsActive())
              <div style="padding:20px 16px 12px;">
                <div style="background:#fff;border-radius:12px;padding:12px;display:inline-block;box-shadow:var(--shadow-md);border:1px solid var(--color-border);">
                  <div id="qrcode"></div>
                </div>
                <div style="font-size:10px;color:var(--color-text-faint);margin-top:8px;font-family:monospace;">{{ $passenger->qr_token }}</div>
                <div style="font-size:11px;color:var(--color-text-faint);margin-top:2px;">Scan to mark attendance</div>
              </div>
            @else
              <div style="padding:32px 16px;">
                <i class="fas fa-lock" style="font-size:24px;color:var(--color-text-faint);"></i>
                <div style="font-size:12px;color:var(--color-text-muted);margin-top:10px;max-width:220px;margin-inline:auto;">
                  Your QR unlocks once your transport fee is paid and approved.
                </div>
              </div>
            @endif
            <div style="border-top:1px solid var(--color-border);padding:12px 16px;text-align:left;">
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:12px;color:var(--color-text-muted);">Route</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->route->name ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:12px;color:var(--color-text-muted);">Stop</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->stop ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span style="font-size:12px;color:var(--color-text-muted);">Vehicle</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->route->vehicle->number ?? '—' }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:12px;color:var(--color-text-muted);">Driver</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;">{{ $passenger->route->vehicle->driver->name ?? '—' }}</span>
              </div>
              @php $myStopEta = $passenger->route?->routeStops->firstWhere('name', $passenger->stop)?->eta; @endphp
              @if($myStopEta)
              <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:12px;color:var(--color-text-muted);">ETA at My Stop</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;"><i class="fas fa-clock" style="color:var(--color-warning);"></i> {{ $myStopEta }}</span>
              </div>
              @endif
              @if($passenger->route && $passenger->route->routeStops->count())
              <div style="margin-top:8px;">
                <span style="font-size:12px;color:var(--color-text-muted);">Route Stops</span>
                <div style="font-size:12px;color:var(--color-text);font-weight:600;margin-top:4px;display:flex;flex-direction:column;gap:3px;">
                  @foreach($passenger->route->routeStops as $stop)
                    <div style="display:flex;justify-content:space-between;gap:10px;">
                      <span>{{ $stop->name }}</span>
                      <span style="color:var(--color-text-muted);font-weight:400;">{{ $stop->eta ?? '—' }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
              @endif
            </div>
            @if($passenger->qrIsActive())
              <div style="border-top:1px solid var(--color-border);padding:12px 16px;">
                <a href="{{ route('passengers.qr', $passenger) }}" class="btn btn-P" style="width:100%;justify-content:center;font-size:12px;" target="_blank">
                  <i class="fas fa-download"></i> Download QR Pass
                </a>
              </div>
            @endif
          </div>

          {{-- Right: Details + Fees + Attendance --}}
          <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- My Details --}}
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-id-card" style="color:var(--color-primary);margin-right:6px;"></i>My Details</h3></div>
              <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                  <div class="tile">
                    <div class="tile-lbl">Name</div>
                    <div class="tile-val">{{ $passenger->name }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Roll / Reg #</div>
                    <div class="tile-val" style="font-family:monospace;">{{ $passenger->roll }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Department</div>
                    <div class="tile-val">{{ $passenger->department }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Status</div>
                    <span class="badge {{ $passenger->status=='Active' ? 'badge-G' : 'badge-ERR' }}">{{ $passenger->status }}</span>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Stop</div>
                    <div class="tile-val">{{ $passenger->stop ?? '—' }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Security PIN</div>
                    @if($passenger->fingerprint_enrolled)
                      <span style="color:var(--color-success);font-size:13px;font-weight:600;"><i class="fas fa-fingerprint"></i> Enrolled</span>
                    @else
                      <span style="color:var(--color-text-faint);font-size:13px;"><i class="fas fa-fingerprint"></i> Not enrolled</span>
                    @endif
                  </div>
                </div>
              </div>
            </div>

            {{-- Fees Section --}}
            @php $activeFee = $passenger->activeFeePayment(); $pendingFee = $passenger->pendingFeePayment(); @endphp
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-receipt" style="color:var(--color-warning);margin-right:6px;"></i>Fee Details</h3></div>
              <div class="card-body">
                @if($activeFee)
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;color:var(--color-text-muted);">Transport fee for {{ $activeFee->monthLabel() }}</span>
                    <span class="badge badge-G"><i class="fas fa-circle-check"></i> Active until {{ $activeFee->qr_expires_at->format('d M') }}</span>
                  </div>
                @elseif($pendingFee)
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;color:var(--color-text-muted);">Payment for {{ $pendingFee->monthLabel() }}</span>
                    <span class="badge badge-W"><i class="fas fa-hourglass-half"></i> Pending Review</span>
                  </div>
                @else
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-size:13px;color:var(--color-text-muted);">No active pass</span>
                    <span class="badge badge-ERR"><i class="fas fa-triangle-exclamation"></i> Fee Due</span>
                  </div>
                @endif
                <a href="{{ route('fee.my') }}" class="btn btn-P" style="width:100%;justify-content:center;font-size:12px;">
                  <i class="fas fa-money-bill-wave"></i> {{ $activeFee ? 'View My Fee Pass' : 'Pay Transport Fee' }}
                </a>
              </div>
            </div>

            {{-- Attendance History --}}
            <div class="card">
              <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                <h3><i class="fas fa-calendar" style="color:var(--color-success);margin-right:6px;"></i>Attendance History</h3>
                <span style="font-size:12px;color:var(--color-text-faint);">Last 30 records</span>
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
                          <span style="color:var(--color-primary);font-size:11px;"><i class="fas fa-qrcode"></i> QR Scan</span>
                        @elseif($a->method === 'fingerprint')
                          <span style="color:var(--color-success);font-size:11px;"><i class="fas fa-fingerprint"></i> Security PIN</span>
                        @else
                          <span style="color:var(--color-text-muted);font-size:11px;"><i class="fas fa-keyboard"></i> Manual</span>
                        @endif
                      </td>
                      <td>
                        <span class="badge {{ $a->status=='Present' ? 'badge-G' : 'badge-ERR' }}">{{ $a->status }}</span>
                      </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--color-text-faint);">No attendance records yet.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            {{-- Complaints & Feedback quick link --}}
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-comment-dots" style="color:var(--color-info);margin-right:6px;"></i>Complaints &amp; Feedback</h3></div>
              <div class="card-body">
                <p style="font-size:12px;color:var(--color-text-muted);margin-bottom:12px;">Report an issue with a driver, another passenger, or the service in general — or just leave feedback.</p>
                <a href="{{ route('complaints.create') }}" class="btn btn-S" style="width:100%;justify-content:center;font-size:12px;">
                  <i class="fas fa-paper-plane"></i> Submit Complaint / Feedback
                </a>
              </div>
            </div>

            {{-- Cancel Transport Subscription --}}
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-ban" style="color:var(--color-danger);margin-right:6px;"></i>Transport Subscription</h3></div>
              <div class="card-body">
                @if($passenger->cancellation_status === 'requested')
                  <span class="badge badge-W" style="margin-bottom:8px;display:inline-block;"><i class="fas fa-hourglass-half"></i> Cancellation request pending admin review</span>
                @elseif($passenger->cancellation_status === 'approved')
                  <span class="badge badge-ERR" style="margin-bottom:8px;display:inline-block;"><i class="fas fa-ban"></i> Subscription cancelled</span>
                @else
                  @if($passenger->cancellation_status === 'rejected')
                    <p style="font-size:12px;color:var(--color-text-muted);margin-bottom:10px;">Your previous cancellation request was declined. You can submit a new one below.</p>
                  @endif
                  <form method="POST" action="{{ route('passengers.cancellation.request', $passenger) }}" onsubmit="return confirm('Request cancellation of your transport subscription?');">
                    @csrf
                    <textarea name="cancellation_reason" class="lf-input" rows="2" placeholder="Reason for cancelling…" required style="height:auto;padding:8px;font-size:12px;margin-bottom:10px;"></textarea>
                    <button type="submit" class="btn btn-ERR" style="width:100%;justify-content:center;font-size:12px;">
                      <i class="fas fa-ban"></i> Request Cancellation
                    </button>
                  </form>
                @endif
              </div>
            </div>

          </div>
        </div>

        @if($passenger->qrIsActive())
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
          new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('attendance.scan', ['token' => $passenger->qr_token]) }}",
            width: 160,
            height: 160,
            colorDark: "#1E1B2E",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
          });
        </script>
        @endif
      @endif

    </div>
  </div>
</div>
@endsection

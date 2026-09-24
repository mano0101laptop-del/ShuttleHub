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
      @if($errors->any())
        <div class="alert-err mb-3">
          @foreach($errors->all() as $error)
            <div><i class="fas fa-circle-exclamation"></i> {{ $error }}</div>
          @endforeach
        </div>
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
              Your transport application has been submitted successfully. The admin will review your request shortly. You will have full access to your pass and fee details once approved.
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
                <div class="tile-lbl">College ID</div>
                <div class="tile-val" style="font-family:monospace;">{{ $passenger->roll }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Passenger Type</div>
                <div class="tile-val">{{ $passenger->passenger_type ?? '—' }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Contact Number</div>
                <div class="tile-val">{{ $passenger->contact_number ?? '—' }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Emergency Contact</div>
                <div class="tile-val">{{ $passenger->emergency_contact ?? '—' }}</div>
              </div>
              <div class="tile">
                <div class="tile-lbl">Address</div>
                <div class="tile-val">{{ $passenger->address ?? '—' }}</div>
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
          <form id="passenger-photo-form" method="POST" action="{{ route('passengers.updateOwnPhoto') }}" enctype="multipart/form-data" style="margin:0;flex-shrink:0;">
            @csrf
            <label for="passenger-photo-input" title="Change profile photo" style="width:66px;height:66px;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.5);border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;cursor:pointer;box-shadow:0 4px 16px rgba(24,10,50,.15);">
              @if($passenger->photo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($passenger->photo_path) }}" alt="{{ $passenger->name }}" style="width:100%;height:100%;object-fit:cover;">
              @else
                <span style="font-size:22px;font-weight:800;color:#fff;">{{ strtoupper(substr($passenger->name,0,1)) }}</span>
              @endif
              <span style="position:absolute;right:1px;bottom:1px;width:20px;height:20px;border-radius:50%;background:#fff;color:var(--color-primary);display:flex;align-items:center;justify-content:center;font-size:9px;box-shadow:0 2px 6px rgba(0,0,0,.2);"><i class="fas fa-camera"></i></span>
            </label>
            <input id="passenger-photo-input" type="file" name="photo" accept="image/png,image/jpeg,image/webp" style="display:none;" onchange="document.getElementById('passenger-photo-form').submit()">
          </form>
          <div>
            <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;">Transport Pass Active</div>
            <div style="font-size:22px;font-weight:800;color:#fff;">{{ $passenger->name }}</div>
            <div style="font-size:13px;color:rgba(255,255,255,.85);">{{ $passenger->roll }} &nbsp;·&nbsp; {{ $passenger->passenger_type ?? $passenger->department ?? '—' }}</div>
            <div style="font-size:10px;color:rgba(255,255,255,.7);margin-top:3px;"><i class="fas fa-camera"></i> Click your photo to replace it</div>
          </div>
          <div style="margin-left:auto;">
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
              <a href="{{ route('passengers.transport-card', $passenger) }}" class="btn" style="font-size:11px;padding:7px 11px;background:#fff;color:var(--color-primary);border:0;box-shadow:none;"><i class="fas fa-download"></i> Download Card</a>
              <span class="badge" style="font-size:12px;padding:6px 14px;background:rgba(255,255,255,.22);color:#fff;"><i class="fas fa-circle-check"></i> Approved</span>
            </div>
          </div>
        </div>

        {{-- Stats --}}
        @php $dashActiveFee = $passenger->activeFeePayment(); @endphp
        <div class="stats-grid" style="margin-bottom:20px;">
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--color-warning-soft);color:var(--color-warning);"><i class="fas fa-route"></i></div>
            <div class="stat-info"><div class="stat-val">{{ $passenger->route->name ?? '—' }}</div><div class="stat-lbl">My Route</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--color-primary-soft);color:var(--color-primary);"><i class="fas fa-map-pin"></i></div>
            <div class="stat-info"><div class="stat-val">{{ $passenger->stop ?? '—' }}</div><div class="stat-lbl">My Stop</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:{{ $dashActiveFee ? 'var(--color-success-soft)' : 'var(--color-danger-soft)' }};color:{{ $dashActiveFee ? 'var(--color-success)' : 'var(--color-danger)' }};"><i class="fas fa-receipt"></i></div>
            <div class="stat-info"><div class="stat-val" style="font-size:15px;">{{ $dashActiveFee ? 'Active' : 'Due' }}</div><div class="stat-lbl">Fee Status</div></div>
          </div>
        </div>

        {{-- Persistent Schedule — remains active until Admin/Incharge updates it --}}
        <div class="card mb-3">
          <div class="card-header"><h3><i class="fas fa-calendar-day" style="color:var(--color-warning);margin-right:6px;"></i>My Schedule</h3></div>
          <div class="card-body">
            @if($schedule)
              <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:10px;">
                <div class="tile" style="flex:1;min-width:140px;">
                  <div class="tile-lbl">Scheduled Driver</div>
                  <div class="tile-val">{{ $schedule->driver?->name ?? $passenger->route->vehicle?->driver?->name ?? '—' }}</div>
                </div>
                <div class="tile" style="flex:1;min-width:140px;">
                  <div class="tile-lbl">Scheduled Vehicle</div>
                  <div class="tile-val">{{ $schedule->vehicle?->number ?? $passenger->route->vehicle?->number ?? '—' }}</div>
                </div>
                <div class="tile" style="flex:1;min-width:140px;">
                  <div class="tile-lbl">Departure</div>
                  <div class="tile-val">{{ $schedule->estimated_departure_time ?? '—' }}</div>
                </div>
              </div>
              @php
                $myStopRow = $schedule->stopsWithTimes()->first(fn($row) => $row['stop']->name === $passenger->stop);
                $myStopTime = $myStopRow['time'] ?? null;
              @endphp
              @if($myStopTime)
                <div class="tile" style="margin-bottom:10px;">
                  <div class="tile-lbl">ETA at My Stop</div>
                  <div class="tile-val"><i class="fas fa-clock" style="color:var(--color-warning);"></i> {{ $myStopTime }}</div>
                </div>
              @endif
              @if($schedule->route->Stops->count())
                <div class="tile" style="margin-bottom:10px;">
                  <div class="tile-lbl">All Stop Timings</div>
                  <div class="tile-val" style="font-size:13px;display:flex;flex-direction:column;gap:4px;margin-top:4px;">
                    @foreach($schedule->stopsWithTimes() as $row)
                      <div style="display:flex;justify-content:space-between;gap:10px;max-width:420px;">
                        <span><i class="fas fa-map-pin" style="color:var(--color-primary);font-size:11px;margin-right:4px;"></i>{{ $row['stop']->name }}</span>
                        <span style="color:var(--color-text-muted);font-size:12px;">{{ $row['time'] ?? '—' }}</span>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif
              @if($schedule->notes)
                <div class="tile" style="margin-bottom:10px;">
                  <div class="tile-lbl">Notes from Transport Office</div>
                  <div class="tile-val" style="font-size:13px;">{{ $schedule->notes }}</div>
                </div>
              @endif
              <span class="badge {{ $schedule->status=='Scheduled' ? 'badge-G' : ($schedule->status=='Completed' ? 'badge-B' : 'badge-ERR') }}">{{ $schedule->status }}</span>
            @else
              <p style="font-size:12px;color:var(--color-text-faint);">No custom Schedule is set for your route — your normal route details remain in effect.</p>
            @endif
          </div>
        </div>

        <div class="detail-grid--narrow">

          {{-- My Transport --}}
          <div class="card" style="text-align:center;padding:0;overflow:hidden;">
            <div style="background:var(--gradient-brand);padding:16px 16px 12px;">
              <div style="font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;">My Transport</div>
              <div style="font-size:16px;font-weight:700;color:#fff;">{{ $passenger->name }}</div>
              <div style="font-size:12px;color:rgba(255,255,255,.85);">{{ $passenger->roll }}</div>
            </div>
            <div style="border-top:1px solid var(--color-border);padding:16px 16px 12px;text-align:left;">
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
              @php $myStopEta = $passenger->route?->Stops->firstWhere('name', $passenger->stop)?->eta; @endphp
              @if($myStopEta)
              <div style="display:flex;justify-content:space-between;margin-top:6px;">
                <span style="font-size:12px;color:var(--color-text-muted);">ETA at My Stop</span>
                <span style="font-size:12px;color:var(--color-text);font-weight:600;"><i class="fas fa-clock" style="color:var(--color-warning);"></i> {{ $myStopEta }}</span>
              </div>
              @endif
              @if($passenger->route && $passenger->route->Stops->count())
              <div style="margin-top:8px;">
                <span style="font-size:12px;color:var(--color-text-muted);">Route Stops</span>
                <div style="font-size:12px;color:var(--color-text);font-weight:600;margin-top:4px;display:flex;flex-direction:column;gap:3px;">
                  @foreach($passenger->route->Stops as $stop)
                    <div style="display:flex;justify-content:space-between;gap:10px;">
                      <span>{{ $stop->name }}</span>
                      <span style="color:var(--color-text-muted);font-weight:400;">{{ $stop->eta ?? '—' }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
              @endif
            </div>
            <div style="border-top:1px solid var(--color-border);padding:12px 16px;display:flex;gap:8px;">
              <a href="{{ route('passengers.show', $passenger) }}" class="btn btn-S" style="flex:1;justify-content:center;font-size:11px;"><i class="fas fa-user"></i> My Profile</a>
              <a href="{{ route('passengers.transport-card', $passenger) }}" class="btn btn-P" style="flex:1;justify-content:center;font-size:11px;"><i class="fas fa-id-card"></i> Digital Card</a>
            </div>
          </div>

          {{-- Right: Details + Fees --}}
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
                    <div class="tile-lbl">College ID</div>
                    <div class="tile-val" style="font-family:monospace;">{{ $passenger->roll }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Passenger Type</div>
                    <div class="tile-val">{{ $passenger->passenger_type ?? '—' }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Contact Number</div>
                    <div class="tile-val">{{ $passenger->contact_number ?? '—' }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Emergency Contact</div>
                    <div class="tile-val">{{ $passenger->emergency_contact ?? '—' }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Address</div>
                    <div class="tile-val">{{ $passenger->address ?? '—' }}</div>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Status</div>
                    <span class="badge {{ $passenger->status=='Active' ? 'badge-G' : 'badge-ERR' }}">{{ $passenger->status }}</span>
                  </div>
                  <div class="tile">
                    <div class="tile-lbl">Stop</div>
                    <div class="tile-val">{{ $passenger->stop ?? '—' }}</div>
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
                    <span class="badge badge-G"><i class="fas fa-circle-check"></i> Active until {{ $activeFee->valid_until->format('d M') }}</span>
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

      @endif

    </div>
  </div>
</div>
@endsection

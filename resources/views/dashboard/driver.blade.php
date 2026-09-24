@extends('layout.app')
@section('title', 'Driver Dashboard — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Driver Dashboard" />
    <div class="content">
      @if(session('success')) <div class="alert-success">{{ session('success') }}</div> @endif
      @if($errors->any())
        <div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>
      @endif

      @if(!$driver)
        <div class="card" style="text-align:center;padding:48px;">
          <i class="fas fa-user-tie" style="font-size:40px;color:var(--color-warning);margin-bottom:16px;"></i>
          <h3 style="margin-bottom:8px;">No Driver Profile Found</h3>
          <p style="color:var(--color-text-muted);">Your login is not linked to a driver profile. Please contact the transport office.</p>
        </div>
      @else
        <div style="background:var(--gradient-brand);border-radius:16px;padding:24px 28px;margin-bottom:20px;display:flex;align-items:center;gap:20px;box-shadow:var(--shadow-brand);flex-wrap:wrap;">
          <form id="photo-form" action="{{ route('drivers.updateOwnPhoto') }}" method="POST" enctype="multipart/form-data" style="margin:0;">@csrf
            <label for="photo-input" title="Change profile picture" style="width:68px;height:68px;background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.35);border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;cursor:pointer;">
              @if($driver->photo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($driver->photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
              @else
                <i class="fas fa-user-tie" style="font-size:28px;color:#fff;"></i>
              @endif
              <span style="position:absolute;inset:auto 0 0;background:rgba(0,0,0,.45);padding:3px;text-align:center;color:#fff;font-size:10px;"><i class="fas fa-camera"></i></span>
            </label>
            <input type="file" id="photo-input" name="photo" accept="image/png,image/jpeg,image/webp" style="display:none;" onchange="document.getElementById('photo-form').submit()">
          </form>
          <div>
            <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.72);margin-bottom:4px;">Driver Profile</div>
            <div style="font-size:23px;font-weight:800;color:#fff;">{{ $driver->name }}</div>
            <div style="font-size:13px;color:rgba(255,255,255,.85);">License {{ $driver->license }} · {{ $driver->phone }}</div>
          </div>
          <div style="margin-left:auto;display:flex;gap:10px;flex-wrap:wrap;">
            <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;padding:7px 12px;"><i class="fas fa-bus"></i> My Bus: {{ $schedule?->vehicle?->number ?? $driver->vehicle?->number ?? 'Not assigned' }}</span>
            <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;padding:7px 12px;"><i class="fas fa-circle-check"></i> {{ $driver->status }}</span>
          </div>
        </div>

        @if($announcements->count())
          <div class="card mb-3">
            <div class="card-header"><h3><i class="fas fa-bullhorn" style="color:var(--color-primary);margin-right:6px;"></i>Announcements</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
              @foreach($announcements as $an)
                <div style="border-left:3px solid var(--color-primary);padding:6px 12px;"><div style="font-weight:700;font-size:13px;">{{ $an->title }}</div><div style="font-size:12px;color:var(--color-text-muted);">{{ $an->body }}</div></div>
              @endforeach
            </div>
          </div>
        @endif

        <div class="card mb-3">
          <div class="card-header" style="display:flex;align-items:center;gap:10px;">
            <h3><i class="fas fa-calendar-day" style="color:var(--color-primary);margin-right:6px;"></i>Active Schedule</h3>
            @if($schedule)<span class="badge {{ $schedule->status === 'Completed' ? 'badge-B' : ($schedule->status === 'Cancelled' ? 'badge-ERR' : 'badge-G') }}" style="margin-left:auto;">{{ $schedule->status }}</span>@endif
          </div>
          <div class="card-body">
            @if($schedule)
              <div style="display:grid;grid-template-columns:repeat(4,minmax(130px,1fr));gap:10px;margin-bottom:16px;">
                <div class="tile"><div class="tile-lbl">Route</div><div class="tile-val"><i class="fas fa-route" style="color:var(--color-primary);margin-right:5px;"></i>{{ $schedule->route?->name ?? '—' }}</div></div>
                <div class="tile"><div class="tile-lbl">My Bus</div><div class="tile-val"><i class="fas fa-bus" style="color:var(--color-primary);margin-right:5px;"></i>{{ $schedule->vehicle?->number ?? '—' }}</div></div>
                <div class="tile"><div class="tile-lbl">Passengers</div><div class="tile-val">{{ $schedule->passengerAssignments->count() }}</div></div>
                <div class="tile"><div class="tile-lbl">Departure</div><div class="tile-val">{{ $schedule->estimated_departure_time ?? '—' }}</div></div>
              </div>

              <div style="display:grid;grid-template-columns:minmax(260px,.9fr) minmax(360px,1.4fr);gap:16px;">
                <div style="border:1px solid var(--color-border);border-radius:12px;padding:14px;">
                  <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-muted);margin-bottom:8px;">Stops & Timings</div>
                  @forelse($schedule->stops->sortBy(fn($s) => $s->Stop?->sequence ?? 999) as $row)
                    <div style="display:flex;justify-content:space-between;gap:12px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--color-border);' : '' }}">
                      <span style="font-size:13px;font-weight:600;"><i class="fas fa-location-dot" style="color:var(--color-primary);margin-right:5px;"></i>{{ $row->Stop?->name ?? 'Stop' }}</span>
                      <span style="font-size:11px;color:var(--color-text-muted);text-align:right;">Pickup {{ $row->pickup_time ?? $row->estimated_time ?? '—' }}<br>Drop {{ $row->dropoff_time ?? '—' }}</span>
                    </div>
                  @empty
                    <div style="font-size:12px;color:var(--color-text-faint);">No stops are selected for this Schedule.</div>
                  @endforelse
                </div>

                <div style="border:1px solid var(--color-border);border-radius:12px;padding:14px;overflow:auto;">
                  <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--color-text-muted);margin-bottom:8px;">Assigned Passengers</div>
                  <table class="tbl" style="min-width:560px;margin:0;">
                    <thead><tr><th>Passenger</th><th>Stop</th><th>Pickup</th><th>Drop-off</th></tr></thead>
                    <tbody>
                      @forelse($schedule->passengerAssignments as $pa)
                        <tr>
                          <td><strong>{{ $pa->passenger?->name ?? 'Removed passenger' }}</strong><div style="font-size:10px;color:var(--color-text-faint);">{{ $pa->passenger?->roll }}</div></td>
                          <td>{{ $pa->Stop?->name ?? $pa->passenger?->stop ?? '—' }}</td>
                          <td>{{ $pa->pickup_time ?? '—' }}</td>
                          <td>{{ $pa->dropoff_time ?? '—' }}</td>
                        </tr>
                      @empty
                        <tr class="empty-row"><td colspan="4">No passengers are assigned to this Schedule.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
              @if($schedule->notes)<div style="margin-top:12px;padding:10px 12px;background:rgba(124,58,237,.05);border-radius:10px;font-size:12px;color:var(--color-text-muted);"><i class="fas fa-note-sticky" style="color:var(--color-primary);margin-right:5px;"></i>{{ $schedule->notes }}</div>@endif
            @else
              <div style="text-align:center;padding:24px 10px;">
                <i class="fas fa-calendar-xmark" style="font-size:30px;color:var(--color-text-faint);margin-bottom:10px;"></i>
                <h3 style="margin-bottom:5px;">No Schedule assigned</h3>
                <p style="font-size:12px;color:var(--color-text-muted);">Your normal bus / route details are shown below, but no persistent Schedule is currently assigned to you.</p>
              </div>
            @endif
          </div>
        </div>

        <div class="detail-grid--narrow">
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-user-tie" style="color:var(--color-primary);margin-right:6px;"></i>My Driver Details</h3></div>
            <div class="card-body">
              <div class="tile" style="margin-bottom:10px;"><div class="tile-lbl">Phone</div><div class="tile-val">{{ $driver->phone }}</div></div>
              <div class="tile" style="margin-bottom:10px;"><div class="tile-lbl">Experience</div><div class="tile-val">{{ $driver->experience ?? '—' }}</div></div>
              <div class="tile" style="margin-bottom:10px;"><div class="tile-lbl">My Bus</div><div class="tile-val">{{ $driver->vehicle?->number ?? '—' }} {{ $driver->vehicle?->type ? '· '.$driver->vehicle->type : '' }}</div></div>
              <div class="tile"><div class="tile-lbl">Normal Route</div><div class="tile-val">{{ $driver->vehicle?->route?->name ?? '—' }}</div></div>
            </div>
          </div>

          <div class="card">
            <div class="card-header"><h3><i class="fas fa-key" style="color:var(--color-primary);margin-right:6px;"></i>Change Password</h3></div>
            <div class="card-body">
              <form method="POST" action="{{ route('drivers.updateOwnPassword') }}">@csrf
                <div class="lf-group"><label class="lf-label">Current Password</label><div class="lf-input-wrap"><i class="fas fa-lock"></i><input class="lf-input" type="password" name="current_password" required></div></div>
                <div class="lf-group"><label class="lf-label">New Password</label><div class="lf-input-wrap"><i class="fas fa-key"></i><input class="lf-input" type="password" name="password" minlength="8" required></div></div>
                <div class="lf-group"><label class="lf-label">Confirm New Password</label><div class="lf-input-wrap"><i class="fas fa-key"></i><input class="lf-input" type="password" name="password_confirmation" minlength="8" required></div></div>
                <button class="btn btn-P" type="submit" style="width:100%;justify-content:center;"><i class="fas fa-shield-halved"></i> Update Password</button>
              </form>
            </div>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;"><h3><i class="fas fa-comments" style="color:var(--color-success);margin-right:6px;"></i>Messages with Transport Office</h3>@if($unreadMessages>0)<span class="badge badge-W">{{ $unreadMessages }} new</span>@endif</div>
          <div class="card-body">
            @forelse($recentMessages as $m)
              <div style="padding:8px 0;border-bottom:1px solid var(--color-border);font-size:12px;"><strong>{{ $m->sender_id === $driver->user_id ? 'You' : 'Office' }}:</strong> {{ \Illuminate\Support\Str::limit($m->body, 90) }}<div style="font-size:10px;color:var(--color-text-faint);">{{ $m->created_at->diffForHumans() }}</div></div>
            @empty
              <p style="font-size:12px;color:var(--color-text-faint);">No messages yet.</p>
            @endforelse
            <a href="{{ route('messages.thread', $driver->user_id) }}" class="btn btn-P" style="margin-top:12px;"><i class="fas fa-comments"></i> Open Conversation</a>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

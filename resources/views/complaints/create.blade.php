@extends('layout.app')
@section('title', 'Complaints & Feedback — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Complaints & Feedback">
      <a href="{{ route('dashboard') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Dashboard</a>
    </x-topbar>
    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
      @endif

      <div class="detail-grid--narrow">
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-comment-dots" style="color:var(--color-primary);margin-right:6px;"></i>Submit Complaint / Feedback</h3></div>
          <div class="card-body">
            <form method="POST" action="{{ route('complaints.store') }}">
              @csrf
              <div class="lf-group">
                <label class="lf-label">Type *</label>
                <div class="lf-input-wrap"><i class="fas fa-tag"></i>
                  <select class="lf-input" name="type" required>
                    <option value="complaint">Complaint</option>
                    <option value="feedback">Feedback</option>
                  </select>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Regarding *</label>
                <div class="lf-input-wrap"><i class="fas fa-bullseye"></i>
                  <select class="lf-input" name="against_type" id="against_type" required onchange="document.getElementById('driver-pick').style.display=this.value==='driver'?'block':'none';document.getElementById('passenger-pick').style.display=this.value==='passenger'?'block':'none';">
                    <option value="general">General / Service</option>
                    <option value="driver">A Driver</option>
                    <option value="passenger">Another Passenger</option>
                  </select>
                </div>
              </div>
              <div class="lf-group" id="driver-pick" style="display:none;">
                <label class="lf-label">Select Driver</label>
                <div class="lf-input-wrap"><i class="fas fa-user-tie"></i>
                  <select class="lf-input" name="against_driver_id">
                    <option value="">— Select —</option>
                    @foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                  </select>
                </div>
              </div>
              <div class="lf-group" id="passenger-pick" style="display:none;">
                <label class="lf-label">Select Passenger</label>
                <div class="lf-input-wrap"><i class="fas fa-user"></i>
                  <select class="lf-input" name="against_passenger_id">
                    <option value="">— Select —</option>
                    @foreach($passengers as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->roll }})</option>@endforeach
                  </select>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Subject *</label>
                <div class="lf-input-wrap"><i class="fas fa-heading"></i>
                  <input class="lf-input" type="text" name="subject" value="{{ old('subject') }}" required>
                </div>
              </div>
              <div class="lf-group">
                <label class="lf-label">Message *</label>
                <div class="lf-input-wrap"><i class="fas fa-align-left"></i>
                  <textarea class="lf-input" name="message" rows="4" required style="height:auto;padding-top:10px;">{{ old('message') }}</textarea>
                </div>
              </div>
              <button type="submit" class="btn btn-P" style="width:100%;justify-content:center;"><i class="fas fa-paper-plane"></i> Submit</button>
            </form>
          </div>
        </div>

        @if(Auth::user()->role === 'passenger')
        <div class="card">
          <div class="card-header"><h3>My Submissions</h3></div>
          <div class="card-body">
            <table class="tbl">
              <thead><tr><th>Type</th><th>Subject</th><th>Status</th><th>Response</th></tr></thead>
              <tbody>
                @forelse($myComplaints as $c)
                <tr>
                  <td><span class="badge {{ $c->type=='complaint' ? 'badge-ERR' : 'badge-G' }}">{{ ucfirst($c->type) }}</span></td>
                  <td>{{ $c->subject }}</td>
                  <td>
                    @if($c->status === 'resolved') <span class="badge badge-G">Resolved</span>
                    @elseif($c->status === 'reviewed') <span class="badge badge-B">Reviewed</span>
                    @else <span class="badge badge-W">Open</span> @endif
                  </td>
                  <td style="font-size:12px;color:var(--color-text-muted);max-width:220px;">{{ $c->admin_response ?? '—' }}</td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="4">You haven't submitted anything yet.</td></tr>
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
@endsection

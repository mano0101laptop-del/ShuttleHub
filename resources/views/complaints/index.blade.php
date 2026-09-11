@extends('layout.app')
@section('title', 'Complaints & Feedback — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Complaints & Feedback" />
    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      <div class="card">
        <div class="card-header"><h3>Submissions</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead><tr><th>From</th><th>Type</th><th>Regarding</th><th>Subject / Message</th><th>Status</th><th>Response</th></tr></thead>
            <tbody>
              @forelse($complaints as $c)
              <tr>
                <td style="font-weight:600;">{{ $c->passenger->name ?? '—' }}</td>
                <td><span class="badge {{ $c->type=='complaint' ? 'badge-ERR' : 'badge-G' }}">{{ ucfirst($c->type) }}</span></td>
                <td style="font-size:12px;">
                  @if($c->against_type === 'driver')
                    Driver: {{ $c->againstDriver->name ?? '—' }}
                  @elseif($c->against_type === 'passenger')
                    Passenger: {{ $c->againstPassenger->name ?? '—' }}
                  @else
                    General
                  @endif
                </td>
                <td style="font-size:12px;max-width:280px;">
                  <div style="font-weight:600;">{{ $c->subject }}</div>
                  <div style="color:var(--color-text-muted);">{{ \Illuminate\Support\Str::limit($c->message, 90) }}</div>
                </td>
                <td>
                  @if($c->status === 'resolved') <span class="badge badge-G">Resolved</span>
                  @elseif($c->status === 'reviewed') <span class="badge badge-B">Reviewed</span>
                  @else <span class="badge badge-W">Open</span> @endif
                </td>
                <td style="min-width:220px;">
                  <form method="POST" action="{{ route('complaints.respond', $c) }}" style="display:flex;flex-direction:column;gap:6px;">
                    @csrf
                    <textarea name="admin_response" class="lf-input" rows="2" placeholder="Write a response…" style="height:auto;padding:8px;font-size:12px;" required>{{ $c->admin_response }}</textarea>
                    <div style="display:flex;gap:6px;">
                      <select name="status" class="lf-input" style="height:32px;font-size:12px;flex:1;">
                        <option value="open" {{ $c->status=='open'?'selected':'' }}>Open</option>
                        <option value="reviewed" {{ $c->status=='reviewed'?'selected':'' }}>Reviewed</option>
                        <option value="resolved" {{ $c->status=='resolved'?'selected':'' }}>Resolved</option>
                      </select>
                      <button type="submit" class="btn btn-P btn-sm"><i class="fas fa-check"></i></button>
                    </div>
                  </form>
                </td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="6">No complaints or feedback submitted yet.</td></tr>
              @endforelse
            </tbody>
          </table>
          {{ $complaints->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

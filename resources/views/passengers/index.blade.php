@extends('layout.app')
@section('title', 'Passengers — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Passengers">
      @if(Auth::user()->role === 'admin')
        <a href="{{ route('passengers.create') }}" class="btn btn-P"><i class="fas fa-user-plus"></i> Register Passenger</a>
      @endif
    </x-topbar>

    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      {{-- ── Pending Transport Requests (Admin only) ── --}}
      @if(Auth::user()->role === 'admin' && $pendingPassengers->count())
      <div class="card mb-3" style="border-color:rgba(217,119,6,.3);">
        <div class="card-header" style="background:rgba(217,119,6,.08);">
          <h3 style="color:var(--color-warning);"><i class="fas fa-hourglass-half" style="margin-right:6px;"></i>Pending Transport Applications</h3>
          <span class="badge badge-W">{{ $pendingPassengers->count() }} pending</span>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Name</th><th>College ID</th><th>Type</th><th>Stop</th><th>Applied</th><th>Actions</th></tr>
            </thead>
            <tbody>
              @foreach($pendingPassengers as $p)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight:600;color:var(--color-text);">{{ $p->name }}</td>
                <td style="font-family:var(--font-mono);font-size:11px;">{{ $p->roll }}</td>
                <td>{{ $p->passenger_type ?? '—' }}</td>
                <td>{{ $p->stop ?? '—' }}</td>
                <td style="font-size:12px;color:var(--color-text-faint);">{{ $p->created_at->format('d M Y') }}</td>
                <td style="white-space:nowrap;">
                  <form method="POST" action="{{ route('passengers.approve', $p) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-G btn-sm" title="Approve">
                      <i class="fas fa-check"></i> Approve
                    </button>
                  </form>
                  <form method="POST" action="{{ route('passengers.reject', $p) }}" style="display:inline;" onsubmit="return confirm('Reject {{ $p->name }}\'s application?')">
                    @csrf
                    <button type="submit" class="btn btn-ERR btn-sm" title="Reject">
                      <i class="fas fa-times"></i> Reject
                    </button>
                  </form>
                  <a href="{{ route('passengers.show', $p) }}" class="btn btn-S btn-sm" title="View"><i class="fas fa-eye"></i></a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      {{-- ── Cancellation Requests (Admin only) ── --}}
      @if(Auth::user()->role === 'admin' && $cancellationRequests->count())
      <div class="card mb-3" style="border-color:rgba(220,38,38,.3);">
        <div class="card-header" style="background:rgba(220,38,38,.06);">
          <h3 style="color:var(--color-danger);"><i class="fas fa-ban" style="margin-right:6px;"></i>Cancellation Requests</h3>
          <span class="badge badge-ERR">{{ $cancellationRequests->count() }} pending</span>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead><tr><th>#</th><th>Name</th><th>College ID</th><th>Reason</th><th>Requested</th><th>Actions</th></tr></thead>
            <tbody>
              @foreach($cancellationRequests as $p)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight:600;">{{ $p->name }}</td>
                <td style="font-family:var(--font-mono);font-size:11px;">{{ $p->roll }}</td>
                <td style="font-size:12px;max-width:280px;color:var(--color-text-muted);">{{ $p->cancellation_reason ?? '—' }}</td>
                <td style="font-size:12px;color:var(--color-text-faint);">{{ optional($p->cancellation_requested_at)->format('d M Y') }}</td>
                <td style="white-space:nowrap;">
                  <form method="POST" action="{{ route('passengers.cancellation.approve', $p) }}" style="display:inline;" onsubmit="return confirm('Approve cancellation for {{ $p->name }}? Their subscription will be deactivated.')">
                    @csrf
                    <button type="submit" class="btn btn-G btn-sm"><i class="fas fa-check"></i> Approve</button>
                  </form>
                  <form method="POST" action="{{ route('passengers.cancellation.reject', $p) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-ERR btn-sm"><i class="fas fa-times"></i> Reject</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      {{-- ── Approved / Rejected Passengers ── --}}
      <div class="card">
        <div class="card-header">
          <h3>Passengers</h3>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Name</th><th>College ID</th><th>Type</th><th>Route</th><th>Stop</th><th>Driver</th><th>Bus</th><th>Pickup / Drop</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              @forelse($passengers as $p)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <a href="{{ route('passengers.show', $p) }}" style="color:var(--color-primary);font-weight:600;">
                    {{ $p->name }}
                  </a>
                </td>
                <td style="font-family:var(--font-mono);font-size:11px;">{{ $p->roll }}</td>
                <td>{{ $p->passenger_type ?? '—' }}</td>
                <td>{{ $p->route->name ?? '—' }}</td>
                <td>{{ $p->Stop?->name ?? $p->stop ?? '—' }}</td>
                <td>{{ $p->assignedDriver?->name ?? '—' }}</td>
                <td>{{ $p->assignedVehicle?->number ?? '—' }}</td>
                <td style="font-size:11px;white-space:nowrap;">{{ $p->pickup_time ?? '—' }} / {{ $p->dropoff_time ?? '—' }}</td>
                <td>
                  @if($p->approval_status === 'rejected')
                    <span class="badge badge-ERR">Rejected</span>
                  @else
                    <span class="badge {{ $p->status=='Active' ? 'badge-G' : 'badge-ERR' }}">{{ $p->status }}</span>
                  @endif
                  @if($p->cancellation_status === 'requested')
                    <span class="badge badge-W" title="Cancellation requested" style="margin-left:4px;">Cancel Req.</span>
                  @endif
                </td>
                <td style="white-space:nowrap;">
                  <a href="{{ route('passengers.show', $p) }}" class="btn btn-S btn-sm" title="View Profile"><i class="fas fa-id-card"></i></a>
                  @if($p->isApproved())
                    <a href="{{ route('passengers.transport-card', $p) }}" class="btn btn-P btn-sm" title="Download Transport Card"><i class="fas fa-download"></i></a>
                  @endif
                  @if(Auth::user()->role === 'admin')
                    <a href="{{ route('passengers.edit', $p) }}" class="btn btn-S btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{ route('passengers.destroy', $p) }}" style="display:inline" onsubmit="return confirm('Delete {{ $p->name }}?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-ERR btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                  @endif
                </td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="12">No approved passengers yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $passengers->links() }}
      </div>

    </div>
  </div>
</div>
@endsection

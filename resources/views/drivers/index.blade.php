@extends('layout.app')
@section('title', 'Drivers — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Drivers">
      @if(Auth::user()->role === 'admin')
        <a href="{{ route('drivers.create') }}" class="btn btn-P"><i class="fas fa-user-plus"></i> Add Driver</a>
      @endif
    </x-topbar>

    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif

      @if(session('new_driver_credentials'))
        @php $creds = session('new_driver_credentials'); @endphp
        <div class="alert-warning mb-3" style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
          <div style="font-weight:700;"><i class="fas fa-key"></i> Driver Portal Login — {{ $creds['name'] }} (shown once, save it now)</div>
          <div>Username: <strong style="font-family:monospace;">{{ $creds['email'] }}</strong></div>
          <div>Password: <strong style="font-family:monospace;">{{ $creds['password'] }}</strong></div>
        </div>
      @endif

      <div class="card">
        <div class="card-header"><h3>Driver List</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Photo</th><th>Name</th><th>Phone</th><th>License</th><th>Vehicle</th><th>Experience</th><th>Status</th>
                @if(Auth::user()->role === 'admin')
                  <th>Actions</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($drivers as $d)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  @if($d->photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($d->photo_path) }}" style="width:32px;height:32px;object-fit:cover;border-radius:50%;">
                  @else
                    <i class="fas fa-user-circle" style="font-size:26px;color:var(--color-text-faint);"></i>
                  @endif
                </td>
                <td>{{ $d->name }}</td>
                <td>{{ $d->phone }}</td>
                <td>{{ $d->license }}</td>
                <td>{{ $d->vehicle->number ?? '—' }}</td>
                <td>{{ $d->experience ?? '—' }}</td>
                <td>
                  <span class="badge {{ $d->status=='Active' ? 'badge-G' : 'badge-ERR' }}">{{ $d->status }}</span>
                </td>
                @if(Auth::user()->role === 'admin')
                <td style="white-space:nowrap;">
                  <a href="{{ route('drivers.show', $d) }}" class="btn btn-S btn-sm" title="View profile"><i class="fas fa-eye"></i></a>
                  @if($d->user)
                    <a href="{{ route('messages.thread', $d->user_id) }}" class="btn btn-S btn-sm" title="Message driver"><i class="fas fa-comment"></i></a>
                  @endif
                    <a href="{{ route('drivers.edit', $d) }}" class="btn btn-S btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                    @if($d->user)
                      <form method="POST" action="{{ route('drivers.reset-password', $d) }}" style="display:inline" onsubmit="return confirm('Reset {{ $d->name }}\'s portal password?')">
                        @csrf
                        <button type="submit" class="btn btn-S btn-sm" title="Reset Password"><i class="fas fa-key"></i></button>
                      </form>
                    @endif
                    <form method="POST" action="{{ route('drivers.destroy', $d) }}" style="display:inline" onsubmit="return confirm('Delete this driver?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-ERR btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
                @endif
              </tr>
              @empty
              <tr class="empty-row"><td colspan="{{ Auth::user()->role === 'admin' ? 9 : 8 }}">No drivers found.@if(Auth::user()->role === 'admin') <a href="{{ route('drivers.create') }}" style="color:var(--color-primary);">Add one →</a>@endif</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $drivers->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

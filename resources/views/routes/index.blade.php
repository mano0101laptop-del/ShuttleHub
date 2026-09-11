@extends('layout.app')
@section('title', 'Routes — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Routes">
      <a href="{{ route('tmsroutes.create') }}" class="btn btn-P"><i class="fas fa-plus"></i> Add Route</a>
    </x-topbar>

    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      <div class="card">
        <div class="card-header"><h3>Route List</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Name</th><th>From</th><th>To</th><th>Stops</th><th>Vehicle</th><th>Driver</th><th>Passengers</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              @forelse($routes as $r)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $r->name }}</td>
                <td>{{ $r->from }}</td>
                <td>{{ $r->to }}</td>
                <td title="{{ $r->routeStops->pluck('name')->implode(' → ') }}">
                  {{ $r->routeStops->count() }}
                  @if($r->routeStops->count())
                    <span style="font-size:11px;color:var(--color-text-faint);display:block;">{{ $r->routeStops->pluck('name')->implode(' → ') }}</span>
                  @endif
                </td>
                <td>{{ $r->vehicle->number ?? '—' }}</td>
                <td>{{ $r->vehicle->driver->name ?? '—' }}</td>
                <td>{{ $r->passengers->count() }}</td>
                <td>
                  <span class="badge {{ $r->status=='Active' ? 'badge-G' : 'badge-ERR' }}">{{ $r->status }}</span>
                </td>
                <td>
                  <a href="{{ route('tmsroutes.edit', $r) }}" class="btn btn-S btn-sm"><i class="fas fa-edit"></i></a>
                  <form method="POST" action="{{ route('tmsroutes.destroy', $r) }}" style="display:inline" onsubmit="return confirm('Delete this route?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ERR btn-sm"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="10">No routes found. <a href="{{ route('tmsroutes.create') }}" style="color:var(--color-primary);">Add one →</a></td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $routes->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

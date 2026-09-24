@extends('layout.app')
@section('title', 'Vehicles — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Vehicles">
      @if(Auth::user()->role === 'admin')
        <a href="{{ route('vehicles.create') }}" class="btn btn-P"><i class="fas fa-plus"></i> Add Vehicle</a>
      @endif
    </x-topbar>

    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      <div class="card">
        <div class="card-header"><h3>Bus / Vehicle List</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Vehicle No</th><th>Type</th><th>Capacity</th><th>Driver</th><th>Route</th><th>Status</th>
                @if(Auth::user()->role === 'admin')
                  <th>Actions</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($vehicles as $v)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $v->number }}</td>
                <td>{{ $v->type }}</td>
                <td>{{ $v->capacity }}</td>
                <td>{{ $v->driver->name ?? '—' }}</td>
                <td>{{ $v->route->name ?? '—' }}</td>
                <td>
                  <span class="badge {{ $v->status=='Active' ? 'badge-G' : ($v->status=='Breakdown' ? 'badge-W' : 'badge-ERR') }}">
                    {{ $v->status }}
                  </span>
                </td>
                @if(Auth::user()->role === 'admin')
                <td>
                    <a href="{{ route('vehicles.edit', $v) }}" class="btn btn-S btn-sm"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{ route('vehicles.destroy', $v) }}" style="display:inline" onsubmit="return confirm('Delete this vehicle?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="btn btn-ERR btn-sm"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
                @endif
              </tr>
              @empty
              <tr class="empty-row"><td colspan="{{ Auth::user()->role === 'admin' ? 8 : 7 }}">No vehicles found.@if(Auth::user()->role === 'admin') <a href="{{ route('vehicles.create') }}" style="color:var(--color-primary);">Add one →</a>@endif</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $vehicles->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

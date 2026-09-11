@extends('layout.app')
@section('title', 'Transport In-Charges — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Transport In-Charges">
      <a href="{{ route('staff.create') }}" class="btn btn-P"><i class="fas fa-user-plus"></i> Add In-Charge</a>
    </x-topbar>
    <div class="content">
      @if(session('success')) <div class="alert-success">{{ session('success') }}</div> @endif
      <div class="card">
        <div class="card-header"><h3><i class="fas fa-user-tie" style="color:var(--color-primary);margin-right:6px;"></i>Transport In-Charge Accounts</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
              @forelse($incharges as $u)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><strong>{{ $u->name }}</strong></td>
                  <td>{{ $u->email }}</td>
                  <td>{{ $u->created_at->format('d M Y') }}</td>
                  <td style="white-space:nowrap;">
                    <a href="{{ route('staff.show', $u) }}" class="btn btn-S btn-sm" title="View"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('staff.edit', $u) }}" class="btn btn-S btn-sm" title="Edit"><i class="fas fa-pen"></i></a>
                    <form method="POST" action="{{ route('staff.destroy', $u) }}" style="display:inline" onsubmit="return confirm('Remove {{ $u->name }}?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-ERR btn-sm" type="submit"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr class="empty-row"><td colspan="5">No Transport In-Charges yet. <a href="{{ route('staff.create') }}" style="color:var(--color-primary);">Add one →</a></td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        {{ $incharges->links() }}
      </div>
    </div>
  </div>
</div>
@endsection

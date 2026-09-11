@extends('layout.app')
@section('title', 'Driver Messages — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Driver Messages" />
    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      <div class="card">
        <div class="card-header"><h3>Conversations</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>Driver</th><th>Last Message</th><th></th></tr>
            </thead>
            <tbody>
              @forelse($driverUsers as $du)
              <tr>
                <td style="font-weight:600;">
                  {{ $du->name }}
                  @if($du->unread_count > 0)
                    <span class="badge badge-W" style="margin-left:6px;">{{ $du->unread_count }} new</span>
                  @endif
                  <div style="font-size:11px;color:var(--color-text-faint);font-weight:400;">{{ optional($du->driver)->phone ?? $du->email }}</div>
                </td>
                <td style="font-size:12px;color:var(--color-text-muted);max-width:360px;">
                  @if($du->driverMessages->first())
                    {{ \Illuminate\Support\Str::limit($du->driverMessages->first()->body, 80) }}
                  @else
                    <span style="color:var(--color-text-faint);">No messages yet</span>
                  @endif
                </td>
                <td><a href="{{ route('messages.thread', $du->id) }}" class="btn btn-S btn-sm"><i class="fas fa-comments"></i> Open</a></td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="3">No drivers with a portal login yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@extends('layout.app')
@section('title', 'Messages — ' . $driverUser->name)
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Messages — {{ $driverUser->name }}">
      @if(Auth::user()->role !== 'driver')
        <a href="{{ route('messages.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> All Conversations</a>
      @endif
    </x-topbar>
    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
      @endif

      <div class="card" style="max-width:700px;">
        <div class="card-header"><h3><i class="fas fa-user-tie" style="color:var(--color-primary);margin-right:6px;"></i>{{ $driverUser->name }}</h3></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px;max-height:480px;overflow-y:auto;">
          @forelse($messages as $m)
            @php $mine = $m->sender_id === Auth::id(); @endphp
            <div style="align-self:{{ $mine ? 'flex-end' : 'flex-start' }};max-width:75%;">
              <div style="background:{{ $mine ? 'var(--gradient-brand)' : 'var(--color-surface-2)' }};color:{{ $mine ? '#fff' : 'var(--color-text)' }};padding:10px 14px;border-radius:14px;font-size:13px;line-height:1.5;">
                {{ $m->body }}
              </div>
              <div style="font-size:10px;color:var(--color-text-faint);margin-top:4px;text-align:{{ $mine ? 'right' : 'left' }};">
                {{ $mine ? 'You' : $m->sender->name }} · {{ $m->created_at->format('d M, h:i A') }}
              </div>
            </div>
          @empty
            <div style="text-align:center;color:var(--color-text-faint);padding:24px;">No messages yet — say hello!</div>
          @endforelse
        </div>
        <div style="border-top:1px solid var(--color-border);padding:14px 20px;">
          <form method="POST" action="{{ route('messages.store', $driverUser->id) }}" style="display:flex;gap:10px;">
            @csrf
            <input class="lf-input" type="text" name="body" placeholder="Type a message…" required style="flex:1;">
            <button type="submit" class="btn btn-P"><i class="fas fa-paper-plane"></i> Send</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

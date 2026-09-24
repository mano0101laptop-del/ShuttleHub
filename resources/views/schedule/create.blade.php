@extends('layout.app')
@section('title', 'New Schedule — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="New Schedule">
      <a href="{{ route('schedule.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:1100px;margin:0 auto 18px;">
        <div class="card-header"><h3><i class="fas fa-route" style="color:var(--color-primary);margin-right:7px;"></i>1. Select Route</h3></div>
        <div class="card-body">
          <form method="GET" action="{{ route('schedule.create') }}">
            <div class="lf-group" style="margin:0;">
              <label class="lf-label">Route *</label>
              <div class="lf-input-wrap"><i class="fas fa-route"></i>
                <select class="lf-input" name="route_id" required onchange="this.form.submit()">
                  <option value="">— Choose route —</option>
                  @foreach($routes as $route)
                    <option value="{{ $route->id }}" {{ optional($selectedRoute)->id == $route->id ? 'selected' : '' }}>{{ $route->name }} · {{ $route->from }} → {{ $route->to }} · {{ $route->Stops->count() }} stops</option>
                  @endforeach
                </select>
              </div>
            </div>
          </form>
          @if($routes->isEmpty())
            <div style="font-size:12px;color:var(--color-text-muted);margin-top:10px;">Every active route already has a Schedule. Edit an existing Schedule to make changes.</div>
          @endif
        </div>
      </div>

      @if($selectedRoute)
        <div class="card" style="max-width:1100px;margin:0 auto;">
          <div class="card-header"><h3><i class="fas fa-calendar-check" style="color:var(--color-success);margin-right:7px;"></i>2. Driver, Bus, Stops, Passengers & Timings</h3></div>
          <div class="card-body">
            @php $route = $selectedRoute; $schedule = null; @endphp
            @include('schedule.partials.assignment-form')
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

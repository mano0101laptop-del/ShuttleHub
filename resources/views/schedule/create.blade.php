@extends('layout.app')
@section('title', 'New Schedule — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="New Schedule">
      <a href="{{ route('schedule.index', ['date' => $date->toDateString()]) }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:1100px;margin:0 auto 18px;">
        <div class="card-header"><h3><i class="fas fa-route" style="color:var(--color-primary);margin-right:7px;"></i>1. Select Date & Route</h3></div>
        <div class="card-body">
          <form method="GET" action="{{ route('schedule.create') }}" style="display:grid;grid-template-columns:minmax(180px,.7fr) minmax(260px,1.7fr);gap:14px;align-items:end;">
            <div class="lf-group" style="margin:0;">
              <label class="lf-label">Date *</label>
              <div class="lf-input-wrap"><i class="fas fa-calendar-day"></i>
                <input class="lf-input" type="date" name="date" value="{{ $date->toDateString() }}" required>
              </div>
            </div>
            <div class="lf-group" style="margin:0;">
              <label class="lf-label">Route *</label>
              <div class="lf-input-wrap"><i class="fas fa-route"></i>
                <select class="lf-input" name="route_id" required onchange="this.form.submit()">
                  <option value="">— Choose route —</option>
                  @foreach($routes as $r)
                    <option value="{{ $r->id }}" {{ optional($selectedRoute)->id == $r->id ? 'selected' : '' }}>{{ $r->name }} · {{ $r->from }} → {{ $r->to }} · {{ $r->routeStops->count() }} stops</option>
                  @endforeach
                </select>
              </div>
            </div>
          </form>
        </div>
      </div>

      @if($selectedRoute)
        <div class="card" style="max-width:1100px;margin:0 auto;">
          <div class="card-header"><h3><i class="fas fa-calendar-check" style="color:var(--color-success);margin-right:7px;"></i>2. Driver, Bus, Stops, Passengers & Timings</h3></div>
          <div class="card-body">
            @php $route = $selectedRoute; $assignment = null; @endphp
            @include('schedule.partials.assignment-form')
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection

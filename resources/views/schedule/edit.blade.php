@extends('layout.app')
@section('title', 'Edit Schedule — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Edit Schedule">
      <a href="{{ route('schedule.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:1100px;margin:0 auto 16px;">
        <div class="card-header"><h3><i class="fas fa-route" style="color:var(--color-primary);margin-right:7px;"></i>Change Route (optional)</h3></div>
        <div class="card-body">
          <form method="GET" action="{{ route('schedule.edit', $schedule) }}">
            <div class="lf-group" style="margin:0;">
              <label class="lf-label">Route for this Schedule</label>
              <div class="lf-input-wrap"><i class="fas fa-route"></i>
                <select class="lf-input" name="route_id" onchange="this.form.submit()">
                  @foreach($routes as $availableRoute)
                    <option value="{{ $availableRoute->id }}" {{ (int)$route->id === (int)$availableRoute->id ? 'selected' : '' }}>{{ $availableRoute->name }} · {{ $availableRoute->from }} → {{ $availableRoute->to }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card" style="max-width:1100px;margin:0 auto;">
        <div class="card-header">
          <h3><i class="fas fa-pen-to-square" style="color:var(--color-primary);margin-right:7px;"></i>{{ $route->name }} Schedule</h3>
        </div>
        <div class="card-body">
          @include('schedule.partials.assignment-form')
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

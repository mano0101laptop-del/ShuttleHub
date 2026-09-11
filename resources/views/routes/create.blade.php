@extends('layout.app')
@section('title', 'Add Route — TM Service')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Add Route">
      <a href="{{ route('tmsroutes.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      <div class="card" style="max-width:600px;">
        <div class="card-header"><h3>Route Details</h3></div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>
          @endif
          <form method="POST" action="{{ route('tmsroutes.store') }}">
            @csrf
            <div class="lf-group">
              <label class="lf-label">Route Name *</label>
              <div class="lf-input-wrap"><i class="fas fa-route"></i>
                <input class="lf-input" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Route A" required>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">From *</label>
              <div class="lf-input-wrap"><i class="fas fa-location-dot"></i>
                <input class="lf-input" type="text" name="from" value="{{ old('from') }}" placeholder="Starting point" required>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">To *</label>
              <div class="lf-input-wrap"><i class="fas fa-flag-checkered"></i>
                <input class="lf-input" type="text" name="to" value="{{ old('to') }}" placeholder="Destination" required>
              </div>
            </div>
            @include('routes.partials.stops-repeater', ['stops' => collect(old('stop_name', ['']))->map(fn($n, $i) => ['name' => $n, 'eta' => old('stop_eta.'.$i)])])
            <div class="lf-group">
              <label class="lf-label">Assign Vehicle</label>
              <div class="lf-input-wrap"><i class="fas fa-bus"></i>
                <select class="lf-input" name="vehicle_id">
                  <option value="">— None —</option>
                  @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ old('vehicle_id')==$v->id ? 'selected':'' }}>{{ $v->number }} ({{ $v->type }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Status *</label>
              <div class="lf-input-wrap"><i class="fas fa-circle-dot"></i>
                <select class="lf-input" name="status" required>
                  <option value="Active"   {{ old('status')=='Active'   ? 'selected':'' }}>Active</option>
                  <option value="Inactive" {{ old('status')=='Inactive' ? 'selected':'' }}>Inactive</option>
                </select>
              </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
              <button type="submit" class="btn btn-P" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Save Route</button>
              <a href="{{ route('tmsroutes.index') }}" class="btn btn-G" style="flex:1;justify-content:center;">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

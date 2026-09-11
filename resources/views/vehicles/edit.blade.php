@extends('layout.app')
@section('title', 'Edit Vehicle — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Edit Vehicle">
      <a href="{{ route('vehicles.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>

    <div class="content">
      <div class="card" style="max-width:600px;">
        <div class="card-header"><h3>Edit: {{ $vehicle->number }}</h3></div>
        <div class="card-body">
          @if($errors->any())
            <div class="alert-err mb-3">
              @foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach
            </div>
          @endif
          <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
            @csrf @method('PUT')
            <div class="lf-group">
              <label class="lf-label">Vehicle Number *</label>
              <div class="lf-input-wrap">
                <i class="fas fa-bus"></i>
                <input class="lf-input" type="text" name="number" value="{{ old('number', $vehicle->number) }}" required>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Type *</label>
              <div class="lf-input-wrap">
                <i class="fas fa-truck-bus"></i>
                <input class="lf-input" type="text" name="type" value="{{ old('type', $vehicle->type) }}" required>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Capacity *</label>
              <div class="lf-input-wrap">
                <i class="fas fa-users"></i>
                <input class="lf-input" type="number" name="capacity" value="{{ old('capacity', $vehicle->capacity) }}" min="1" required>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Status *</label>
              <div class="lf-input-wrap">
                <i class="fas fa-circle-dot"></i>
                <select class="lf-input" name="status" required>
                  <option value="Active"    {{ old('status',$vehicle->status)=='Active'    ? 'selected':'' }}>Active</option>
                  <option value="Inactive"  {{ old('status',$vehicle->status)=='Inactive'  ? 'selected':'' }}>Inactive</option>
                  <option value="Breakdown" {{ old('status',$vehicle->status)=='Breakdown' ? 'selected':'' }}>Breakdown</option>
                </select>
              </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
              <button type="submit" class="btn btn-P" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Update</button>
              <a href="{{ route('vehicles.index') }}" class="btn btn-G" style="flex:1;justify-content:center;">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

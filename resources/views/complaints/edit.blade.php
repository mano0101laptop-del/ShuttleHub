@extends('layout.app')
@section('title', 'Edit Complaint / Feedback — Shuttle Hub')
@section('content')
<div class="screen active" id="screen-app">
  @include('partials.sidebar')
  <div class="main">
    <x-topbar title="Edit Complaint / Feedback">
      <a href="{{ route('complaints.index') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>
    <div class="content">
      @if($errors->any())
        <div class="alert-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
      @endif
      <div class="card" style="max-width:780px;">
        <div class="card-header"><h3><i class="fas fa-pen" style="color:var(--color-primary);margin-right:6px;"></i>Edit Record</h3></div>
        <div class="card-body">
          <form method="POST" action="{{ route('complaints.update', $complaint) }}">
            @csrf @method('PUT')
            <div class="lf-group">
              <label class="lf-label">Submitted By Passenger *</label>
              <div class="lf-input-wrap"><i class="fas fa-user"></i>
                <select class="lf-input" name="passenger_id" required>
                  @foreach($passengers as $p)
                    <option value="{{ $p->id }}" {{ (string) old('passenger_id', $complaint->passenger_id) === (string) $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->roll }})</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Type *</label>
              <div class="lf-input-wrap"><i class="fas fa-tag"></i>
                <select class="lf-input" name="type" required>
                  <option value="complaint" {{ old('type', $complaint->type) === 'complaint' ? 'selected' : '' }}>Complaint</option>
                  <option value="feedback" {{ old('type', $complaint->type) === 'feedback' ? 'selected' : '' }}>Feedback</option>
                </select>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Regarding *</label>
              <div class="lf-input-wrap"><i class="fas fa-bullseye"></i>
                <select class="lf-input" name="against_type" id="against_type" required onchange="toggleTargets()">
                  <option value="general" {{ old('against_type', $complaint->against_type) === 'general' ? 'selected' : '' }}>General / Service</option>
                  <option value="driver" {{ old('against_type', $complaint->against_type) === 'driver' ? 'selected' : '' }}>A Driver</option>
                  <option value="passenger" {{ old('against_type', $complaint->against_type) === 'passenger' ? 'selected' : '' }}>Another Passenger</option>
                </select>
              </div>
            </div>
            <div class="lf-group" id="driver-pick">
              <label class="lf-label">Select Driver</label>
              <div class="lf-input-wrap"><i class="fas fa-user-tie"></i>
                <select class="lf-input" name="against_driver_id">
                  <option value="">— Select —</option>
                  @foreach($drivers as $d)<option value="{{ $d->id }}" {{ (string) old('against_driver_id', $complaint->against_driver_id) === (string) $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
                </select>
              </div>
            </div>
            <div class="lf-group" id="passenger-pick">
              <label class="lf-label">Select Passenger</label>
              <div class="lf-input-wrap"><i class="fas fa-user"></i>
                <select class="lf-input" name="against_passenger_id">
                  <option value="">— Select —</option>
                  @foreach($passengers as $p)<option value="{{ $p->id }}" {{ (string) old('against_passenger_id', $complaint->against_passenger_id) === (string) $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->roll }})</option>@endforeach
                </select>
              </div>
            </div>
            <div class="lf-group"><label class="lf-label">Subject *</label><div class="lf-input-wrap"><i class="fas fa-heading"></i><input class="lf-input" type="text" name="subject" value="{{ old('subject', $complaint->subject) }}" required></div></div>
            <div class="lf-group"><label class="lf-label">Message *</label><div class="lf-input-wrap"><i class="fas fa-align-left"></i><textarea class="lf-input" name="message" rows="5" required style="height:auto;padding-top:10px;">{{ old('message', $complaint->message) }}</textarea></div></div>
            <button type="submit" class="btn btn-P"><i class="fas fa-save"></i> Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function toggleTargets(){
  const type=document.getElementById('against_type').value;
  document.getElementById('driver-pick').style.display=type==='driver'?'block':'none';
  document.getElementById('passenger-pick').style.display=type==='passenger'?'block':'none';
}
toggleTargets();
</script>
@endsection

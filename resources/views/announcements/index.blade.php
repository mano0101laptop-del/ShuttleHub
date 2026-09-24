@extends('layout.app')
@section('title', 'Announcements — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Announcements" />
    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
      @endif

      @if($canManage)
      <div class="card mb-3">
        <div class="card-header"><h3><i class="fas fa-bullhorn" style="color:var(--color-primary);margin-right:6px;"></i>Publish New Announcement</h3></div>
        <div class="card-body">
          <form method="POST" action="{{ route('announcements.store') }}">
            @csrf
            <div class="lf-group">
              <label class="lf-label">Title *</label>
              <div class="lf-input-wrap"><i class="fas fa-heading"></i>
                <input class="lf-input" type="text" name="title" value="{{ old('title') }}" required>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Message *</label>
              <div class="lf-input-wrap"><i class="fas fa-align-left"></i>
                <textarea class="lf-input" name="body" rows="3" required style="height:auto;padding-top:10px;">{{ old('body') }}</textarea>
              </div>
            </div>
            <div class="lf-group">
              <label class="lf-label">Audience *</label>
              <div class="lf-input-wrap"><i class="fas fa-users"></i>
                <select class="lf-input" name="audience" required>
                  <option value="passengers" {{ old('audience') === 'passengers' ? 'selected' : '' }}>Passengers</option>
                  <option value="drivers" {{ old('audience') === 'drivers' ? 'selected' : '' }}>Drivers</option>
                  <option value="all" {{ old('audience') === 'all' ? 'selected' : '' }}>Everyone</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-P"><i class="fas fa-paper-plane"></i> Publish</button>
          </form>
        </div>
      </div>
      @else
      <div class="alert-note mb-3" style="background:rgba(124,58,237,.06);border:1px solid rgba(124,58,237,.18);border-radius:10px;padding:12px 16px;font-size:12.5px;color:rgba(30,27,46,.65);">
        <i class="fas fa-circle-info" style="color:#7C3AED;margin-right:6px;"></i> View-only — publishing, editing and removing announcements is handled by Admin.
      </div>
      @endif

      <div class="card">
        <div class="card-header"><h3>Published Announcements</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead><tr><th>Title</th><th>Message</th><th>Audience</th><th>Published</th>@if($canManage)<th>Actions</th>@endif</tr></thead>
            <tbody>
              @forelse($announcements as $announcement)
              <tr>
                <td style="font-weight:600;">{{ $announcement->title }}</td>
                <td style="font-size:12px;color:var(--color-text-muted);max-width:320px;">{{ \Illuminate\Support\Str::limit($announcement->body, 100) }}</td>
                <td><span class="badge badge-B">{{ ucfirst($announcement->audience) }}</span></td>
                <td style="font-size:12px;color:var(--color-text-faint);">{{ $announcement->created_at->format('d M Y') }}</td>
                @if($canManage)
                <td style="white-space:nowrap;">
                  <button type="button" class="btn btn-S btn-sm" onclick="var row=document.getElementById('announcement-edit-{{ $announcement->id }}'); row.style.display = row.style.display === 'none' ? 'table-row' : 'none';" title="Edit"><i class="fas fa-pen"></i></button>
                  <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" style="display:inline;" onsubmit="return confirm('Remove this announcement?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ERR btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
                @endif
              </tr>
              @if($canManage)
              <tr id="announcement-edit-{{ $announcement->id }}" style="display:none;">
                <td colspan="5" style="background:rgba(124,58,237,.03);">
                  <form method="POST" action="{{ route('announcements.update', $announcement) }}" style="display:grid;grid-template-columns:1fr 1.7fr .8fr auto;gap:10px;align-items:end;">
                    @csrf @method('PUT')
                    <div class="lf-group" style="margin:0;"><label class="lf-label">Title</label><input class="lf-input" type="text" name="title" value="{{ $announcement->title }}" required></div>
                    <div class="lf-group" style="margin:0;"><label class="lf-label">Message</label><textarea class="lf-input" name="body" rows="2" style="height:auto;padding:8px;" required>{{ $announcement->body }}</textarea></div>
                    <div class="lf-group" style="margin:0;"><label class="lf-label">Audience</label><select class="lf-input" name="audience" required><option value="passengers" {{ $announcement->audience === 'passengers' ? 'selected' : '' }}>Passengers</option><option value="drivers" {{ $announcement->audience === 'drivers' ? 'selected' : '' }}>Drivers</option><option value="all" {{ $announcement->audience === 'all' ? 'selected' : '' }}>Everyone</option></select></div>
                    <button type="submit" class="btn btn-P btn-sm"><i class="fas fa-save"></i> Save</button>
                  </form>
                </td>
              </tr>
              @endif
              @empty
              <tr class="empty-row"><td colspan="{{ $canManage ? 5 : 4 }}">No announcements yet.</td></tr>
              @endforelse
            </tbody>
          </table>
          {{ $announcements->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

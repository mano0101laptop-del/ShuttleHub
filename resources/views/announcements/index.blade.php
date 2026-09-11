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
        <div class="alert-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
      @endif

      @unless(Auth::user()->isAdmin())
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
                  <option value="passengers">Passengers</option>
                  <option value="drivers">Drivers</option>
                  <option value="all">Everyone</option>
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-P"><i class="fas fa-paper-plane"></i> Publish</button>
          </form>
        </div>
      </div>
      @endunless

      <div class="card">
        <div class="card-header"><h3>Published Announcements</h3></div>
        <div class="card-body">
          <table class="tbl">
            <thead><tr><th>Title</th><th>Message</th><th>Audience</th><th>Published</th><th></th></tr></thead>
            <tbody>
              @forelse($announcements as $a)
              <tr>
                <td style="font-weight:600;">{{ $a->title }}</td>
                <td style="font-size:12px;color:var(--color-text-muted);max-width:320px;">{{ \Illuminate\Support\Str::limit($a->body, 100) }}</td>
                <td><span class="badge badge-B">{{ ucfirst($a->audience) }}</span></td>
                <td style="font-size:12px;color:var(--color-text-faint);">{{ $a->created_at->format('d M Y') }}</td>
                <td>
                  @unless(Auth::user()->isAdmin())
                  <form method="POST" action="{{ route('announcements.destroy', $a) }}" onsubmit="return confirm('Remove this announcement?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ERR btn-sm"><i class="fas fa-trash"></i></button>
                  </form>
                  @endunless
                </td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="5">No announcements yet.</td></tr>
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

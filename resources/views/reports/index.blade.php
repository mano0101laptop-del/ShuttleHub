@extends('layout.app')
@section('title', 'Attendance Report — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Attendance Report" />

    <div class="content">

      @if(Auth::user()->isAdmin())
        <div class="alert-info" style="margin-bottom:16px;">
          <i class="fas fa-circle-info"></i>
          <div>This is a read-only report. Marking attendance is handled by the Transport Incharge, drivers, and the QR scanner terminal.</div>
        </div>
      @endif

      {{-- Stats --}}
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(124,58,237,.15);color:#7C3AED;"><i class="fas fa-users"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['total_passengers'] }}</div><div class="stat-lbl">Active Passengers</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-percent"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['avg_attendance'] }}</div><div class="stat-lbl">All-Time Attendance</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(217,119,6,.15);color:#D97706;"><i class="fas fa-clipboard-check"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['total_records'] }}</div><div class="stat-lbl">Attendance Records Logged</div></div>
        </div>
      </div>

      {{-- Period Tabs --}}
      <div style="display:flex;gap:8px;margin:20px 0 16px;flex-wrap:wrap;">
        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $key => $label)
          <a href="{{ route('reports.attendance', ['period' => $key]) }}"
             class="btn {{ $period === $key ? 'btn-P' : 'btn-S' }}" style="font-size:12px;">
            {{ $label }}
          </a>
        @endforeach
      </div>

      <div class="card">
        <div class="card-header">
          <h3>
            @switch($period)
              @case('daily') Last 14 Days @break
              @case('weekly') Last 8 Weeks @break
              @case('monthly') Last 12 Months @break
              @case('yearly') Last 5 Years @break
            @endswitch
          </h3>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr>
                <th>
                  @switch($period)
                    @case('daily') Date @break
                    @case('weekly') Week @break
                    @case('monthly') Month @break
                    @case('yearly') Year @break
                  @endswitch
                </th>
                <th>Total Records</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Attendance %</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $r)
              <tr>
                <td>{{ $r['label'] }}</td>
                <td>{{ $r['total'] }}</td>
                <td><span class="badge badge-G">{{ $r['present'] }}</span></td>
                <td><span class="badge badge-ERR">{{ $r['absent'] }}</span></td>
                <td>{{ $r['attendance'] }}</td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="5">No attendance records for this period yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

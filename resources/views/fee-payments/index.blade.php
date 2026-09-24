@extends('layout.app')
@section('title', 'Transport Fee — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Transport Fee" />

    <div class="content">

      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert-err">{{ session('error') }}</div>
      @endif

      {{-- ── Fee Settings ── --}}
      <div class="card mb-3">
        <div class="card-header"><h3><i class="fas fa-sliders" style="color:var(--color-primary);margin-right:6px;"></i>Fee Settings</h3></div>
        <div class="card-body">
          @if(Auth::user()->isAdmin())
          <form method="POST" action="{{ route('fee-payments.settings') }}" style="display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap;">
            @csrf @method('PUT')
            <div class="lf-group" style="margin-bottom:0;min-width:220px;">
              <label class="lf-label">Monthly Transport Fee (Rs.) *</label>
              <div class="lf-input-wrap">
                <i class="fas fa-money-bill"></i>
                <input class="lf-input" type="number" step="0.01" min="0" name="monthly_fee" value="{{ old('monthly_fee', $setting->monthly_fee) }}" required>
              </div>
            </div>
            <div class="lf-group" style="margin-bottom:0;min-width:220px;">
              <label class="lf-label">JazzCash Number</label>
              <div class="lf-input-wrap">
                <i class="fas fa-mobile-screen"></i>
                <input class="lf-input" type="text" name="jazzcash_number" value="{{ old('jazzcash_number', $setting->jazzcash_number) }}" placeholder="e.g. 03XXXXXXXXX">
              </div>
            </div>
            <div class="lf-group" style="margin-bottom:0;min-width:220px;">
              <label class="lf-label">Easypaisa Number</label>
              <div class="lf-input-wrap">
                <i class="fas fa-mobile-screen-button"></i>
                <input class="lf-input" type="text" name="easypaisa_number" value="{{ old('easypaisa_number', $setting->easypaisa_number) }}" placeholder="e.g. 03XXXXXXXXX">
              </div>
            </div>
            <button type="submit" class="btn btn-P"><i class="fas fa-save"></i> Update Payment Details</button>
          </form>
          <div style="font-size:12px;color:var(--color-text-faint);margin-top:10px;">
            Current fee: <strong>Rs. {{ number_format($setting->monthly_fee, 2) }}</strong>
            @if($setting->updated_by) &middot; last updated by {{ optional($setting->updatedBy)->name ?? '—' }} @endif
          </div>
          @else
            <div style="font-size:13px;color:var(--color-text-muted);">
              Current fee: <strong>Rs. {{ number_format($setting->monthly_fee, 2) }}</strong>
              &middot; Payment settings are managed by Admin.
            </div>
          @endif
        </div>
      </div>

      {{-- ── Pending Payment Review Queue ── --}}
      @if($pendingPayments->count())
      <div class="card mb-3" style="border-color:rgba(217,119,6,.3);">
        <div class="card-header" style="background:rgba(217,119,6,.08);">
          <h3 style="color:var(--color-warning);"><i class="fas fa-hourglass-half" style="margin-right:6px;"></i>Payments Awaiting Review</h3>
          <span class="badge badge-W">{{ $pendingPayments->count() }} pending</span>
        </div>
        <div class="card-body">
          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Passenger</th><th>Month</th><th>Amount</th><th>TID</th><th>Screenshot</th><th>Submitted</th>
                @if(Auth::user()->isIncharge())
                  <th>Actions</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @foreach($pendingPayments as $fp)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight:600;color:var(--color-text);">
                  {{ $fp->passenger->name }}
                  <div style="font-size:11px;color:var(--color-text-faint);font-weight:400;">{{ $fp->passenger->roll }}</div>
                </td>
                <td>{{ $fp->monthLabel() }}</td>
                <td>Rs. {{ number_format($fp->amount, 2) }}</td>
                <td style="font-family:var(--font-mono);font-size:11px;">
                  @if($fp->payment_method === 'stripe')
                    <span class="badge badge-B" style="font-family:inherit;">Card (Stripe)</span>
                  @elseif($fp->payment_method === 'jazzcash')
                    <span class="badge badge-B" style="font-family:inherit;">JazzCash</span><br>{{ $fp->tid }}
                  @elseif($fp->payment_method === 'easypaisa')
                    <span class="badge badge-G" style="font-family:inherit;">Easypaisa</span><br>{{ $fp->tid }}
                  @else
                    {{ $fp->tid }}
                  @endif
                </td>
                <td>
                  @if($fp->payment_method === 'stripe')
                    <span style="color:var(--color-text-faint);font-size:11px;">— paid via Stripe —</span>
                  @else
                  <a href="{{ route('fee-payments.screenshot', $fp) }}" target="_blank" class="btn btn-S btn-sm" title="View Screenshot">
                    <i class="fas fa-image"></i> View
                  </a>
                  @endif
                </td>
                <td style="font-size:12px;color:var(--color-text-faint);">{{ $fp->created_at->format('d M Y, h:i A') }}</td>
                @if(Auth::user()->isIncharge())
                <td style="white-space:nowrap;">
                  <form method="POST" action="{{ route('fee-payments.approve', $fp) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-G btn-sm" title="Approve Payment">
                      <i class="fas fa-check"></i> Approve
                    </button>
                  </form>
                  <button type="button" class="btn btn-ERR btn-sm" title="Reject" onclick="var f=document.getElementById('reject-{{ $fp->id }}'); f.style.display = f.style.display==='none' ? 'flex' : 'none';">
                    <i class="fas fa-times"></i> Reject
                  </button>
                  <form id="reject-{{ $fp->id }}" method="POST" action="{{ route('fee-payments.reject', $fp) }}" style="display:none;margin-top:8px;gap:6px;">
                    @csrf
                    <input type="text" name="rejection_reason" placeholder="Reason (optional)" class="lf-input" style="height:32px;font-size:12px;">
                    <button type="submit" class="btn btn-ERR btn-sm">Confirm</button>
                  </form>
                </td>
                @endif
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      {{-- ── Monthly Fee Records ── --}}
      <div class="card">
        <div class="card-header" style="flex-wrap:wrap;gap:12px;">
          <h3><i class="fas fa-calendar-days" style="color:var(--color-primary);margin-right:6px;"></i>Fee Records — {{ $monthLabel }}</h3>
          <form method="GET" action="{{ route('fee-payments.index') }}" style="display:flex;align-items:center;gap:8px;">
            <input type="month" name="month" value="{{ $month }}" class="lf-input" style="height:34px;">
            <button type="submit" class="btn btn-S btn-sm">View</button>
          </form>
        </div>
        <div class="card-body">
          <div class="stats-grid" style="margin-bottom:16px;">
            <div class="stat-card">
              <div class="stat-icon" style="background:var(--color-success-soft);color:var(--color-success);"><i class="fas fa-circle-check"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $counts['active'] }}</div><div class="stat-lbl">Active</div></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon" style="background:var(--color-danger-soft);color:var(--color-danger);"><i class="fas fa-triangle-exclamation"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $counts['due'] }}</div><div class="stat-lbl">Due</div></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon" style="background:var(--color-primary-soft);color:var(--color-primary);"><i class="fas fa-calendar-plus"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $counts['upcoming'] }}</div><div class="stat-lbl">Upcoming</div></div>
            </div>
            <div class="stat-card">
              <div class="stat-icon" style="background:var(--color-warning-soft);color:var(--color-warning);"><i class="fas fa-hourglass-half"></i></div>
              <div class="stat-info"><div class="stat-val">{{ $counts['pending'] }}</div><div class="stat-lbl">Pending Review</div></div>
            </div>
          </div>

          <table class="tbl">
            <thead>
              <tr><th>#</th><th>Passenger</th><th>Roll No</th><th>Status</th><th>Amount</th><th>Valid Until</th><th>Actions</th></tr>
            </thead>
            <tbody>
              @forelse($records as $r)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight:600;color:var(--color-text);">{{ $r['passenger']->name }}</td>
                <td style="font-family:var(--font-mono);font-size:11px;">{{ $r['passenger']->roll }}</td>
                <td>
                  @switch($r['status'])
                    @case('active')
                      <span class="badge badge-G">Active</span> @break
                    @case('due')
                      <span class="badge badge-ERR">Due</span> @break
                    @case('upcoming')
                      <span class="badge badge-B">Upcoming</span> @break
                    @case('pending')
                      <span class="badge badge-W">Pending Review</span> @break
                    @default
                      <span class="badge badge-ERR">Expired</span>
                  @endswitch
                </td>
                <td>{{ $r['payment'] ? 'Rs. ' . number_format($r['payment']->amount, 2) : '—' }}</td>
                <td style="font-size:12px;color:var(--color-text-faint);">
                  {{ $r['payment'] && $r['payment']->valid_until ? $r['payment']->valid_until->format('d M Y') : '—' }}
                </td>
                <td>
                  <a href="{{ route('passengers.show', $r['passenger']) }}" class="btn btn-S btn-sm" title="View Passenger"><i class="fas fa-id-card"></i></a>
                </td>
              </tr>
              @empty
              <tr class="empty-row"><td colspan="7">No approved passengers yet.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

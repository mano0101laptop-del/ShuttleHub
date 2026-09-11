@extends('layout.app')
@section('title', 'My Transport Fee — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="My Transport Fee">
      <a href="{{ route('dashboard') }}" class="btn btn-S"><i class="fas fa-arrow-left"></i> Back</a>
    </x-topbar>

    <div class="content">

      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert-err">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert-err">
          @foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach
        </div>
      @endif

      <div class="detail-grid--narrow">

        {{-- ── Left: Fee status (no separate QR — the one transport QR lives on your dashboard) ── --}}
        <div class="card" style="text-align:center;padding:0;overflow:hidden;">
          @if($active)
            <div style="background:var(--gradient-brand);padding:16px 16px 12px;">
              <div style="font-size:11px;letter-spacing:.15em;text-transform:uppercase;color:rgba(255,255,255,.75);margin-bottom:4px;">Transport Fee</div>
              <div style="font-size:16px;font-weight:700;color:#fff;">{{ $active->monthLabel() }}</div>
              <div style="font-size:12px;color:rgba(255,255,255,.85);">Paid — valid until {{ $active->qr_expires_at->format('d M Y') }}</div>
            </div>
            <div style="padding:28px 20px;">
              <div style="width:64px;height:64px;background:var(--color-success-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;border:2px solid rgba(22,163,74,.3);">
                <i class="fas fa-circle-check" style="font-size:26px;color:var(--color-success);"></i>
              </div>
              <h3 style="margin-bottom:6px;">Fee Paid — Pass Unlocked</h3>
              <p style="color:var(--color-text-muted);font-size:13px;max-width:280px;margin:0 auto 16px;">
                Your transport QR code is now active. Show it from your dashboard when boarding or marking attendance.
              </p>
              <a href="{{ route('dashboard') }}" class="btn btn-P" style="justify-content:center;font-size:12px;">
                <i class="fas fa-qrcode"></i> View My QR Pass
              </a>
            </div>
          @elseif($pending)
            <div style="padding:40px 20px;">
              <div style="width:64px;height:64px;background:var(--color-warning-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;border:2px solid rgba(217,119,6,.3);">
                <i class="fas fa-hourglass-half" style="font-size:24px;color:var(--color-warning);"></i>
              </div>
              <h3 style="margin-bottom:6px;">Payment Under Review</h3>
              <p style="color:var(--color-text-muted);font-size:13px;max-width:280px;margin:0 auto;">
                Your payment of Rs. {{ number_format($pending->amount, 2) }} for {{ $pending->monthLabel() }} (TID: {{ $pending->tid }}) is awaiting admin approval. Your QR pass will unlock on your dashboard once approved.
              </p>
            </div>
          @else
            <div style="padding:40px 20px;">
              <div style="width:64px;height:64px;background:var(--color-danger-soft);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;border:2px solid rgba(220,38,38,.3);">
                <i class="fas fa-circle-exclamation" style="font-size:24px;color:var(--color-danger);"></i>
              </div>
              <h3 style="margin-bottom:6px;">No Active Pass</h3>
              <p style="color:var(--color-text-muted);font-size:13px;max-width:280px;margin:0 auto;">
                You don't have an active transport pass. Pay for {{ \Carbon\Carbon::createFromFormat('Y-m', $targetMonth)->format('F Y') }} to unlock your QR pass.
              </p>
            </div>
          @endif
        </div>

        {{-- ── Right: Pay form + history ── --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

          @if($canPay)
          <div class="card">
            <div class="card-header"><h3><i class="fas fa-money-bill-wave" style="color:var(--color-success);margin-right:6px;"></i>Pay Transport Fee</h3></div>
            <div class="card-body">
              <div class="alert-info" style="margin-bottom:16px;">
                <i class="fas fa-circle-info"></i>
                <div>
                  Amount due for <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $targetMonth)->format('F Y') }}</strong>:
                  <strong>Rs. {{ number_format($amount, 2) }}</strong>.
                </div>
              </div>

              @if($stripeEnabled)
              <form method="POST" action="{{ route('fee.stripe.checkout') }}" style="margin-bottom:16px;">
                @csrf
                <button type="submit" class="btn btn-P" style="width:100%;justify-content:center;">
                  <i class="fas fa-credit-card"></i> Pay Rs. {{ number_format($amount, 2) }} by Card (Stripe{{ app()->environment('production') ? '' : ' — Test Mode' }})
                </button>
                <div style="font-size:11px;color:var(--color-text-faint);text-align:center;margin-top:6px;">
                  Charged in {{ $stripeCurrency }} via Stripe's secure checkout. Instant — no manual review needed.
                </div>
              </form>
              <div style="display:flex;align-items:center;gap:10px;margin:14px 0;color:var(--color-text-faint);font-size:11px;">
                <div style="flex:1;height:1px;background:var(--color-border);"></div>
                OR PAY VIA BANK TRANSFER
                <div style="flex:1;height:1px;background:var(--color-border);"></div>
              </div>
              @endif

              <p style="font-size:12px;color:var(--color-text-muted);margin-bottom:10px;">
                Pay via your usual bank/transfer method, then submit the transaction ID and a screenshot below.
              </p>
              <form method="POST" action="{{ route('fee.pay') }}" enctype="multipart/form-data">
                @csrf
                <div class="lf-group">
                  <label class="lf-label">Transaction ID (TID) *</label>
                  <div class="lf-input-wrap">
                    <i class="fas fa-hashtag"></i>
                    <input class="lf-input" type="text" name="tid" value="{{ old('tid') }}" placeholder="e.g. TXN123456789" required>
                  </div>
                </div>
                <div class="lf-group">
                  <label class="lf-label">Payment Screenshot *</label>
                  <div class="lf-input-wrap">
                    <i class="fas fa-image"></i>
                    <input class="lf-input" type="file" name="screenshot" accept="image/*" required>
                  </div>
                </div>
                <button type="submit" class="btn btn-S" style="width:100%;justify-content:center;margin-top:8px;">
                  <i class="fas fa-paper-plane"></i> Submit for Review
                </button>
              </form>
            </div>
          </div>
          @endif

          <div class="card">
            <div class="card-header"><h3><i class="fas fa-clock-rotate-left" style="color:var(--color-primary);margin-right:6px;"></i>Payment History</h3></div>
            <div class="card-body">
              <table class="tbl">
                <thead>
                  <tr><th>Month</th><th>Amount</th><th>TID</th><th>Status</th><th>Valid Until</th></tr>
                </thead>
                <tbody>
                  @forelse($history as $h)
                  <tr>
                    <td>{{ $h->monthLabel() }}</td>
                    <td>Rs. {{ number_format($h->amount, 2) }}</td>
                    <td style="font-family:var(--font-mono);font-size:11px;">
                      @if($h->payment_method === 'stripe')
                        <span class="badge badge-B" style="font-family:inherit;">Card</span>
                      @else
                        {{ $h->tid }}
                      @endif
                    </td>
                    <td>
                      @if($h->status === 'approved')
                        <span class="badge {{ $h->isActive() ? 'badge-G' : 'badge-ERR' }}">{{ $h->isActive() ? 'Active' : 'Expired' }}</span>
                      @elseif($h->status === 'pending')
                        <span class="badge badge-W">Pending</span>
                      @else
                        <span class="badge badge-ERR">Rejected</span>
                      @endif
                    </td>
                    <td style="font-size:12px;color:var(--color-text-faint);">{{ $h->qr_expires_at ? $h->qr_expires_at->format('d M Y') : '—' }}</td>
                  </tr>
                  @empty
                  <tr><td colspan="5" style="text-align:center;color:var(--color-text-faint);">No payments submitted yet.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
@endsection

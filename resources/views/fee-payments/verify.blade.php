<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Fee Pass Verification — Shuttle Hub</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin:0;padding:0;box-sizing:border-box; }
    body { background:linear-gradient(135deg,#7C3AED,#C026D3);min-height:100vh;
           display:flex;align-items:center;justify-content:center;font-family:'Nunito Sans',sans-serif;padding:20px; }
    .card { background:#fff;border:1px solid rgba(30,27,46,.08);border-radius:20px;
            padding:36px 32px;max-width:400px;width:100%;text-align:center;box-shadow:0 20px 50px rgba(30,27,46,.25); }
    .icon-wrap { width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;
                 font-size:32px;margin:0 auto 20px; }
    .icon-success { background:rgba(22,163,74,.12);border:2px solid rgba(22,163,74,.35);color:#16A34A; }
    .icon-err     { background:rgba(220,38,38,.12);border:2px solid rgba(220,38,38,.35);color:#DC2626; }
    h2 { color:#1E1B2E;font-size:22px;font-weight:800;margin-bottom:6px; }
    .sub { color:rgba(30,27,46,.5);font-size:14px;margin-bottom:24px; }
    .info-box { background:#F8F7FC;border:1px solid rgba(30,27,46,.06);border-radius:12px;padding:16px;text-align:left;margin-bottom:20px; }
    .info-row { display:flex;justify-content:space-between;margin-bottom:8px; }
    .info-row:last-child { margin-bottom:0; }
    .info-lbl { font-size:12px;color:rgba(30,27,46,.55); }
    .info-val { font-size:13px;color:#1E1B2E;font-weight:700; }
    .btn { display:inline-block;padding:12px 28px;border-radius:10px;font-weight:700;font-size:14px;
           text-decoration:none;cursor:pointer;border:none; }
    .btn-home { background:linear-gradient(135deg,#7C3AED,#C026D3);color:#fff;width:100%;box-shadow:0 10px 26px rgba(124,58,237,.3); }
  </style>
</head>
<body>
  <div class="card">
    @if($valid)
      <div class="icon-wrap icon-success"><i>✓</i></div>
      <h2>Pass Valid</h2>
      <p class="sub">Transport fee is paid and active for {{ $feePayment->monthLabel() }}</p>
    @else
      <div class="icon-wrap icon-err"><i>✗</i></div>
      <h2>{{ $feePayment->isPending() ? 'Payment Pending' : ($feePayment->isRejected() ? 'Payment Rejected' : 'Pass Expired') }}</h2>
      <p class="sub">This passenger does not have a valid fee pass right now</p>
    @endif

    <div class="info-box">
      <div class="info-row">
        <span class="info-lbl">Name</span>
        <span class="info-val">{{ $feePayment->passenger->name }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Roll No</span>
        <span class="info-val">{{ $feePayment->passenger->roll }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Fee Month</span>
        <span class="info-val">{{ $feePayment->monthLabel() }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Status</span>
        <span class="info-val" style="color:{{ $valid ? '#16A34A' : '#DC2626' }};">{{ ucfirst($feePayment->status) }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">QR Expires</span>
        <span class="info-val">{{ $feePayment->qr_expires_at ? $feePayment->qr_expires_at->format('d M Y') : '—' }}</span>
      </div>
    </div>

    <a href="/" class="btn btn-home">← Back to Home</a>
  </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Attendance Result — Shuttle Hub</title>
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
    .icon-already { background:rgba(217,119,6,.12);border:2px solid rgba(217,119,6,.35);color:#D97706; }
    .icon-err     { background:rgba(220,38,38,.12);border:2px solid rgba(220,38,38,.35);color:#DC2626; }
    h2 { color:#1E1B2E;font-size:22px;font-weight:800;margin-bottom:6px; }
    .sub { color:rgba(30,27,46,.5);font-size:14px;margin-bottom:24px; }
    .info-box { background:#F8F7FC;border:1px solid rgba(30,27,46,.06);border-radius:12px;padding:16px;text-align:left;margin-bottom:20px; }
    .info-row { display:flex;justify-content:space-between;margin-bottom:8px; }
    .info-row:last-child { margin-bottom:0; }
    .info-lbl { font-size:12px;color:rgba(30,27,46,.55); }
    .info-val { font-size:13px;color:#1E1B2E;font-weight:700; }
    .method-badge { display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;margin-bottom:20px; }
    .method-qr   { background:rgba(124,58,237,.12);color:#7C3AED;border:1px solid rgba(124,58,237,.3); }
    .btn { display:inline-block;padding:12px 28px;border-radius:10px;font-weight:700;font-size:14px;
           text-decoration:none;cursor:pointer;border:none; }
    .btn-home { background:linear-gradient(135deg,#7C3AED,#C026D3);color:#fff;width:100%;box-shadow:0 10px 26px rgba(124,58,237,.3); }
    .pulse { animation:pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1}50%{opacity:.7} }
  </style>
</head>
<body>
  <div class="card">
    @if($status === 'success')
      <div class="icon-wrap icon-success pulse"><i>✓</i></div>
      <h2>Attendance Marked!</h2>
      <p class="sub">Successfully recorded via QR scan</p>
      <div class="method-badge method-qr">📱 QR Code Scan</div>

    @elseif($status === 'already')
      <div class="icon-wrap icon-already">⚠</div>
      <h2>Already Marked</h2>
      <p class="sub">Attendance was already recorded today</p>
      <div class="method-badge method-qr">📱 QR Code Scan</div>

    @elseif($status === 'inactive')
      <div class="icon-wrap icon-err">✗</div>
      <h2>Pass Not Active</h2>
      <p class="sub">Application not yet approved, or transport fee is unpaid</p>

    @else
      <div class="icon-wrap icon-err">✗</div>
      <h2>Error</h2>
      <p class="sub">Could not process QR code</p>
    @endif

    <div class="info-box">
      <div class="info-row">
        <span class="info-lbl">Name</span>
        <span class="info-val">{{ $passenger->name }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Roll No</span>
        <span class="info-val">{{ $passenger->roll }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Department</span>
        <span class="info-val">{{ $passenger->department }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Route</span>
        <span class="info-val">{{ $passenger->route->name ?? '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Date</span>
        <span class="info-val">{{ now()->format('d M Y') }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Time</span>
        <span class="info-val">{{ $attendance && $attendance->time ? \Carbon\Carbon::parse($attendance->time)->format('h:i A') : now()->format('h:i A') }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Status</span>
        <span class="info-val" style="color:{{ $attendance ? '#16A34A' : '#DC2626' }};">{{ $attendance->status ?? 'Not Marked' }}</span>
      </div>
    </div>

    <a href="/" class="btn btn-home">← Back to Home</a>
  </div>
</body>
</html>

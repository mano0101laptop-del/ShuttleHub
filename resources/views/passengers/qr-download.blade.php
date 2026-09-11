<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QR Pass — {{ $passenger->name }}</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <style>
    * { margin:0;padding:0;box-sizing:border-box; }
    body { background:#F4F5FA;display:flex;justify-content:center;align-items:center;min-height:100vh;font-family:'Nunito Sans',sans-serif; }
    .pass { width:340px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.15); }
    .pass-header { background:linear-gradient(135deg,#6D28D9,#7c3aed);padding:22px 20px 18px;color:#fff;text-align:center; }
    .pass-header .org { font-size:11px;letter-spacing:.15em;text-transform:uppercase;opacity:.7;margin-bottom:4px; }
    .pass-header .name { font-size:22px;font-weight:800; }
    .pass-header .roll { font-size:13px;opacity:.75;margin-top:2px; }
    .qr-wrap { padding:22px;text-align:center;background:#fff; }
    .qr-wrap canvas, .qr-wrap img { border-radius:8px; }
    .token { font-family:monospace;font-size:12px;color:#7C3AED;letter-spacing:.12em;margin-top:10px;display:block; }
    .info { border-top:1px solid #eee;padding:14px 20px; }
    .info-row { display:flex;justify-content:space-between;margin-bottom:8px; }
    .info-row:last-child { margin-bottom:0; }
    .info-lbl { font-size:11px;color:#9ca3af; }
    .info-val { font-size:12px;color:#1f2937;font-weight:600; }
    .fp-badge { border-top:1px solid #eee;padding:12px 20px;display:flex;align-items:center;justify-content:center;gap:8px;font-size:12px;color:#16A34A;font-weight:600; }
    .pass-footer { background:#6D28D9;padding:10px;text-align:center;font-size:10px;color:rgba(255,255,255,.75);letter-spacing:.08em;text-transform:uppercase; }
    .print-btn { position:fixed;bottom:24px;right:24px;padding:12px 24px;background:#6D28D9;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(79,70,229,.4); }
    @media print { .print-btn { display:none; } body { background:#fff; } }
  </style>
</head>
<body>
  <div class="pass">
    <div class="pass-header">
      <div class="org">Shuttle Hub — Transport Pass</div>
      <div class="name">{{ $passenger->name }}</div>
      <div class="roll">{{ $passenger->roll }}</div>
    </div>

    <div class="qr-wrap">
      <div id="qrcode" style="display:inline-block;"></div>
      <span class="token">{{ $passenger->qr_token }}</span>
      <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Scan to mark attendance</div>
    </div>

    <div class="info">
      <div class="info-row">
        <span class="info-lbl">Department</span>
        <span class="info-val">{{ $passenger->department }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Route</span>
        <span class="info-val">{{ $passenger->route->name ?? '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Stop</span>
        <span class="info-val">{{ $passenger->stop ?? '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-lbl">Issued</span>
        <span class="info-val">{{ now()->format('d M Y') }}</span>
      </div>
    </div>

    @if($passenger->fingerprint_enrolled)
    <div class="fp-badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1C7 1 3 5 3 10v4a9 9 0 0 0 18 0v-4c0-5-4-9-9-9z"/><path d="M9 10a3 3 0 0 1 6 0"/><path d="M12 10v6"/></svg>
      Security PIN Set
    </div>
    @endif

    <div class="pass-footer">Shuttle Hub &nbsp;·&nbsp; College Transport System</div>
  </div>

  <button class="print-btn" onclick="window.print()">🖨 Print / Download</button>

  <script>
    new QRCode(document.getElementById("qrcode"), {
      text: "{{ route('attendance.scan', ['token' => $passenger->qr_token]) }}",
      width: 200, height: 200,
      colorDark: "#1e1b4b", colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H
    });
  </script>
</body>
</html>

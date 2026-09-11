<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Fee Status Check</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      background:#F4F5FA; display:flex; justify-content:center; align-items:center;
      min-height:100vh; font-family:'Nunito Sans',sans-serif; padding:20px;
    }
    .card {
      width:100%; max-width:360px; background:#fff; border-radius:16px;
      overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,.15); text-align:center;
    }
    .header { background:#6D28D9; padding:16px; color:#fff; font-size:12px;
      letter-spacing:.15em; text-transform:uppercase; font-weight:700; }
    .body { padding:28px 22px; }
    .name { font-size:18px; font-weight:800; color:#1f2937; margin-bottom:2px; }
    .roll { font-size:13px; color:#9ca3af; margin-bottom:18px; }
    .month { font-size:12px; color:#9ca3af; margin-bottom:14px; text-transform:uppercase; letter-spacing:.05em; }
    .badge { font-size:22px; font-weight:800; padding:14px; border-radius:10px; margin-top:6px; }
    .paid       { color:#16A34A; background:#F0FDF4; }
    .due        { color:#DC2626; background:#FEF2F2; }
    .pending    { color:#D97706; background:#FFFBEB; }
    .rejected   { color:#DC2626; background:#FEF2F2; }
    .invalid    { color:#6B7280; background:#F3F4F6; }
  </style>
</head>
<body>
  <div class="card">
    <div class="header">Shuttle Hub — Fee Verification</div>
    <div class="body">
      @if($status === 'invalid')
        <div class="badge invalid">QR Pass Not Recognized</div>

      @elseif($status === 'not_approved')
        <div class="name">{{ $passenger->name }}</div>
        <div class="roll">{{ $passenger->roll }}</div>
        <div class="badge due">Transport Application Not Approved</div>

      @else
        <div class="name">{{ $passenger->name }}</div>
        <div class="roll">{{ $passenger->roll }}</div>
        <div class="month">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</div>

        @if($status === 'paid')
          <div class="badge paid">✅ Fee Paid</div>
        @elseif($status === 'pending')
          <div class="badge pending">⏳ Payment Under Review</div>
        @elseif($status === 'rejected')
          <div class="badge rejected">❌ Payment Rejected</div>
        @else
          <div class="badge due">❌ Fee Due</div>
        @endif
      @endif
    </div>
  </div>
</body>
</html>

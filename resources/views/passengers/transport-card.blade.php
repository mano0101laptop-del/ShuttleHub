<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Transport Card — {{ $passenger->name }}</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      background: #f1edf7;
      font-family: Arial, Helvetica, sans-serif;
      color: #17151f;
    }
    .toolbar {
      position: sticky;
      top: 0;
      z-index: 10;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
      padding: 14px 18px;
      background: rgba(255,255,255,.96);
      border-bottom: 1px solid #e8e1ef;
      box-shadow: 0 4px 18px rgba(36,25,52,.06);
    }
    .toolbar .title { font-weight: 800; margin-right: auto; }
    .btn {
      border: 0;
      border-radius: 9px;
      padding: 10px 14px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #fff;
      background: #7c3aed;
    }
    .btn.secondary { background: #2d2d2f; }
    .btn.light { color: #43384d; background: #eee8f5; }
    .page {
      padding: 34px 18px 46px;
      display: flex;
      justify-content: center;
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(2, 324px);
      gap: 34px;
      align-items: start;
    }
    .card-shell {
      width: 324px;
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 14px 34px rgba(37,26,51,.18);
      background: #fff;
    }
    .card-label {
      text-align: center;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .16em;
      color: #6d6178;
      font-weight: 800;
      margin-bottom: 9px;
    }
    svg { display: block; width: 324px; height: 516px; }
    .note {
      max-width: 680px;
      margin: 22px auto 0;
      padding: 12px 16px;
      border-radius: 10px;
      background: #fff;
      color: #675c71;
      font-size: 12px;
      line-height: 1.5;
      text-align: center;
      box-shadow: 0 4px 16px rgba(37,26,51,.06);
    }
    @media (max-width: 760px) {
      .cards { grid-template-columns: 1fr; }
      .page { padding-top: 24px; }
      .toolbar .title { width: 100%; margin-right: 0; }
    }
    @media print {
      @page { size: A4 portrait; margin: 12mm; }
      body { background: #fff; }
      .toolbar, .card-label, .note { display: none !important; }
      .page { padding: 0; }
      .cards {
        grid-template-columns: 54mm 54mm;
        gap: 10mm;
      }
      .card-shell {
        width: 54mm;
        height: 86mm;
        box-shadow: none;
        page-break-inside: avoid;
      }
      svg { width: 54mm; height: 86mm; }
    }
  </style>
</head>
<body>
@php
  $route = $passenger->route;
  $routeStops = $route?->Stops?->sortBy('sequence')->values() ?? collect();
  $pickupStop = $passenger->Stop?->name ?? $passenger->stop ?? 'Not assigned';
  $vehicle = $passenger->assignedVehicle?->number ?? $route?->vehicle?->number ?? '—';
  $driver = $passenger->assignedDriver?->name ?? $route?->vehicle?->driver?->name ?? '—';
  $routeName = $route?->name ?? 'Route not assigned';
  $routeDirection = $route ? trim(($route->from ?? '') . (($route->from || $route->to) ? ' → ' : '') . ($route->to ?? '')) : '—';
  $pickupTime = $passenger->pickup_time ?: ($passenger->Stop?->eta ?? '—');
  $dropoffTime = $passenger->dropoff_time ?: '—';
  $feeLabel = $passenger->status !== 'Active'
    ? 'TRANSPORT INACTIVE'
    : ($feePayment ? 'VALID TO ' . $feePayment->valid_until->format('d M Y') : 'FEE STATUS: DUE');
  $nameParts = preg_split('/\s+/', trim($passenger->name));
  $initials = strtoupper(substr($nameParts[0] ?? 'P', 0, 1) . substr(end($nameParts) ?: '', 0, 1));
  $displayStops = $routeStops->take(5);
@endphp

<div class="toolbar">
  <div class="title">{{ $passenger->name }} · Digital Transport Card</div>
  <a class="btn light" href="{{ route('passengers.show', $passenger) }}">← Back to Profile</a>
  <button class="btn" type="button" onclick="downloadPng('card-front','{{ $cardId }}-front.png')">Download Front PNG</button>
  <button class="btn" type="button" onclick="downloadPng('card-back','{{ $cardId }}-back.png')">Download Back PNG</button>
  <button class="btn secondary" type="button" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="page">
  <div>
    <div class="cards">
      <div>
        <div class="card-label">Front</div>
        <div class="card-shell">
          <svg id="card-front" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 324 516" width="324" height="516" role="img" aria-label="Front of {{ $passenger->name }} transport card">
            <defs>
              <clipPath id="front-photo-clip"><rect x="110" y="94" width="104" height="124" rx="2"/></clipPath>
            </defs>
            <rect width="324" height="516" fill="#ffffff"/>
            <rect width="324" height="176" fill="#2d2d2f"/>

            <polygon points="0,78 54,176 0,176" fill="#7638f4"/>
            <polygon points="0,113 80,246 0,246" fill="#d9a6f2"/>
            <polygon points="244,0 324,0 324,126" fill="#6d28d9"/>
            <polygon points="282,0 324,0 324,67" fill="#8b5cf6" opacity=".7"/>
            <polygon points="260,176 324,176 324,260" fill="#7432f5"/>
            <polygon points="0,356 54,444 0,444" fill="#7c3aed"/>
            <polygon points="0,407 30,456 0,456" fill="#dca8f3"/>
            <polygon points="271,516 324,516 324,430" fill="#7c3aed"/>
            <polygon points="294,516 324,516 324,468" fill="#dca8f3"/>

            <g transform="translate(87 33)">
              <polygon points="0,20 12,0 24,20" fill="#7c3aed"/>
              <polygon points="15,20 27,2 39,20" fill="#dca8f3"/>
              <text x="50" y="11" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="800" fill="#ffffff">SHUTTLE</text>
              <text x="50" y="27" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="800" fill="#ffffff">HUB</text>
            </g>

            <rect x="106" y="90" width="112" height="132" fill="#7c3aed" rx="2"/>
            <rect x="110" y="94" width="104" height="124" fill="#eee8f5" rx="2"/>
            @if($photoDataUri)
              <image href="{{ $photoDataUri }}" x="110" y="94" width="104" height="124" preserveAspectRatio="xMidYMid slice" clip-path="url(#front-photo-clip)"/>
            @else
              <circle cx="162" cy="144" r="28" fill="#ffffff"/>
              <text x="162" y="153" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="25" font-weight="800" fill="#7c3aed">{{ $initials }}</text>
              <text x="162" y="191" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="700" fill="#7b6f86">ADD PROFILE PHOTO</text>
            @endif

            <text x="162" y="260" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-style="italic" font-weight="800" fill="#7c3aed">TRANSPORT ID CARD</text>

            <text x="162" y="296" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#6b6471">NAME</text>
            <text x="162" y="316" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="17" font-weight="800" fill="#1c1920">{{ \Illuminate\Support\Str::limit($passenger->name, 26) }}</text>

            <text x="162" y="345" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#6b6471">PASSENGER ID</text>
            <text x="162" y="363" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="700" fill="#1c1920">{{ \Illuminate\Support\Str::limit($passenger->roll, 28) }}</text>

            <text x="162" y="393" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#6b6471">TYPE / DEPARTMENT</text>
            <text x="162" y="411" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="700" fill="#1c1920">{{ \Illuminate\Support\Str::limit(($passenger->passenger_type ?? 'Passenger') . ($passenger->department ? ' · ' . $passenger->department : ''), 38) }}</text>

            <rect x="71" y="438" width="182" height="34" rx="17" fill="#f0e9ff"/>
            <text x="162" y="459" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="800" letter-spacing=".7" fill="#6d28d9">{{ $feeLabel }}</text>

            <text x="162" y="495" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="700" fill="#918799">CARD {{ $cardId }}</text>
          </svg>
        </div>
      </div>

      <div>
        <div class="card-label">Back</div>
        <div class="card-shell">
          <svg id="card-back" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 324 516" width="324" height="516" role="img" aria-label="Back of {{ $passenger->name }} transport card">
            <rect width="324" height="516" fill="#ffffff"/>
            <rect width="324" height="78" fill="#2d2d2f"/>
            <polygon points="0,0 55,0 0,86" fill="#7c3aed"/>
            <polygon points="39,0 84,0 0,132 0,84" fill="#dca8f3"/>
            <polygon points="270,0 324,0 324,84" fill="#7c3aed"/>
            <polygon points="294,0 324,0 324,47" fill="#dca8f3"/>
            <polygon points="0,432 49,516 0,516" fill="#e3e3f5"/>
            <polygon points="0,395 70,516 34,516 0,458" fill="#7c3aed"/>
            <polygon points="278,516 324,516 324,440" fill="#7c3aed"/>
            <polygon points="301,516 324,516 324,478" fill="#dca8f3"/>

            <text x="162" y="31" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="700" letter-spacing="1.4" fill="#c9c2d0">SHUTTLE HUB</text>
            <text x="162" y="52" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="800" fill="#ffffff">ROUTE &amp; TRAVEL DETAILS</text>

            <text x="25" y="106" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="800" fill="#8d8397">ROUTE</text>
            <text x="25" y="126" font-family="Arial, Helvetica, sans-serif" font-size="14" font-weight="800" fill="#1c1920">{{ \Illuminate\Support\Str::limit($routeName, 34) }}</text>
            <text x="25" y="144" font-family="Arial, Helvetica, sans-serif" font-size="10" fill="#675e70">{{ \Illuminate\Support\Str::limit($routeDirection, 44) }}</text>

            <rect x="20" y="160" width="284" height="51" rx="8" fill="#f2edff"/>
            <circle cx="41" cy="185" r="8" fill="#7c3aed"/>
            <circle cx="41" cy="185" r="3" fill="#ffffff"/>
            <text x="58" y="178" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="800" fill="#7c3aed">MY PICKUP STOP</text>
            <text x="58" y="197" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="800" fill="#1c1920">{{ \Illuminate\Support\Str::limit($pickupStop, 34) }}</text>

            <text x="25" y="235" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="800" fill="#8d8397">ROUTE STOPS</text>
            @forelse($displayStops as $index => $stop)
              @php $y = 258 + ($index * 24); @endphp
              <circle cx="31" cy="{{ $y - 4 }}" r="4" fill="{{ ($stop->id ?? null) === $passenger->stop_id ? '#7c3aed' : '#c5b7dd' }}"/>
              <text x="44" y="{{ $y }}" font-family="Arial, Helvetica, sans-serif" font-size="10.5" font-weight="{{ ($stop->id ?? null) === $passenger->stop_id ? '800' : '600' }}" fill="#2a2630">{{ \Illuminate\Support\Str::limit($stop->name, 29) }}</text>
              <text x="291" y="{{ $y }}" text-anchor="end" font-family="Arial, Helvetica, sans-serif" font-size="9.5" fill="#887e91">{{ $stop->eta ?? '—' }}</text>
            @empty
              <text x="25" y="258" font-family="Arial, Helvetica, sans-serif" font-size="10.5" fill="#887e91">No route stops configured.</text>
            @endforelse
            @if($routeStops->count() > 5)
              <text x="44" y="{{ 258 + (5 * 24) }}" font-family="Arial, Helvetica, sans-serif" font-size="9" font-style="italic" fill="#7c3aed">+ {{ $routeStops->count() - 5 }} more stop(s)</text>
            @endif

            <line x1="20" x2="304" y1="386" y2="386" stroke="#e8e1ef" stroke-width="1"/>

            <text x="25" y="409" font-family="Arial, Helvetica, sans-serif" font-size="8.5" font-weight="800" fill="#8d8397">VEHICLE</text>
            <text x="25" y="425" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="800" fill="#1c1920">{{ \Illuminate\Support\Str::limit($vehicle, 18) }}</text>
            <text x="112" y="409" font-family="Arial, Helvetica, sans-serif" font-size="8.5" font-weight="800" fill="#8d8397">DRIVER</text>
            <text x="112" y="425" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="800" fill="#1c1920">{{ \Illuminate\Support\Str::limit($driver, 18) }}</text>
            <text x="241" y="409" font-family="Arial, Helvetica, sans-serif" font-size="8.5" font-weight="800" fill="#8d8397">PICKUP</text>
            <text x="241" y="425" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="800" fill="#1c1920">{{ $pickupTime }}</text>

            <text x="82" y="454" font-family="Arial, Helvetica, sans-serif" font-size="8.5" font-weight="800" fill="#8d8397">EMERGENCY CONTACT</text>
            <text x="82" y="470" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="700" fill="#1c1920">{{ $passenger->emergency_contact ?? '—' }}</text>
            <text x="218" y="454" font-family="Arial, Helvetica, sans-serif" font-size="8.5" font-weight="800" fill="#8d8397">DROP-OFF</text>
            <text x="218" y="470" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="700" fill="#1c1920">{{ $dropoffTime }}</text>

            <rect x="60" y="484" width="204" height="22" rx="11" fill="#ffffff" opacity=".94"/>
            <text x="162" y="498" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="8" font-weight="700" fill="#81768a">AUTHORIZED TRANSPORT PASS · {{ $cardId }}</text>
          </svg>
        </div>
      </div>
    </div>

    <div class="note">
      Use <strong>Download Front/Back PNG</strong> for a digital copy, or <strong>Print / Save PDF</strong> for a two-sided printable card. Passenger profile photos are automatically embedded in the downloaded card.
    </div>
  </div>
</div>

<script>
function downloadPng(svgId, filename) {
  const svg = document.getElementById(svgId);
  if (!svg) return;

  const clone = svg.cloneNode(true);
  clone.setAttribute('width', '648');
  clone.setAttribute('height', '1032');

  const serializer = new XMLSerializer();
  const source = serializer.serializeToString(clone);
  const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const img = new Image();

  img.onload = function () {
    const canvas = document.createElement('canvas');
    canvas.width = 648;
    canvas.height = 1032;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    URL.revokeObjectURL(url);

    canvas.toBlob(function (pngBlob) {
      if (!pngBlob) return;
      const downloadUrl = URL.createObjectURL(pngBlob);
      const link = document.createElement('a');
      link.href = downloadUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
    }, 'image/png');
  };

  img.onerror = function () {
    URL.revokeObjectURL(url);
    alert('Could not prepare the PNG. Please use Print / Save PDF instead.');
  };

  img.src = url;
}
</script>
</body>
</html>

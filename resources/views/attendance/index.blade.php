@extends('layout.app')
@section('title', 'Attendance — TM Service')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Attendance" />

    <div class="content">
      @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert-err mb-3">@foreach($errors->all() as $e)<div><i class="fas fa-circle-exclamation"></i> {{ $e }}</div>@endforeach</div>
      @endif

      {{-- ── Stats ── --}}
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(22,163,74,.15);color:#16A34A;"><i class="fas fa-user-check"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['present'] }}</div><div class="stat-lbl">Present Today</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(220,38,38,.15);color:#DC2626;"><i class="fas fa-user-xmark"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['absent'] }}</div><div class="stat-lbl">Absent Today</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(124,58,237,.15);color:#7C3AED;"><i class="fas fa-qrcode"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['qr_scans'] }}</div><div class="stat-lbl">QR Scans</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background:rgba(217,119,6,.15);color:#D97706;"><i class="fas fa-fingerprint"></i></div>
          <div class="stat-info"><div class="stat-val">{{ $stats['fingerprints'] }}</div><div class="stat-lbl">Security PIN</div></div>
        </div>
      </div>

      {{-- ── Tabs ── --}}
      <div style="display:flex;gap:8px;margin:20px 0 16px;flex-wrap:wrap;">
        <button class="att-tab active" data-tab="scanner" onclick="switchTab('scanner',this)">
          <i class="fas fa-camera"></i> Scanner
        </button>
        <button class="att-tab" data-tab="manual" onclick="switchTab('manual',this)">
          <i class="fas fa-keyboard"></i> Manual
        </button>
        <button class="att-tab" data-tab="fingerprint" onclick="switchTab('fingerprint',this)">
          <i class="fas fa-fingerprint"></i> Security PIN
        </button>
        <button class="att-tab" data-tab="qrgen" onclick="switchTab('qrgen',this)">
          <i class="fas fa-qrcode"></i> QR Generator
        </button>
      </div>

      <div class="detail-grid--wide">

        {{-- ── Left Panel (tabs) ── --}}
        <div>

          {{-- Scanner Tab --}}
          <div id="tab-scanner" class="att-panel">
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-camera"></i> Live QR Scanner</h3></div>
              <div class="card-body">
                <div id="scan-feedback" style="display:none;border-radius:10px;padding:14px 16px;margin-bottom:14px;font-size:13px;font-weight:600;"></div>
                <div id="scan-passenger-card" class="scan-result-card" style="display:none;"></div>

                <div id="qr-reader" style="width:100%;border-radius:12px;overflow:hidden;background:#111;min-height:0;"></div>

                <div style="display:flex;gap:8px;margin-top:14px;">
                  <button type="button" id="scan-start-btn" class="btn btn-P" style="flex:1;justify-content:center;" onclick="startScanner()">
                    <i class="fas fa-camera"></i> Start Camera
                  </button>
                  <button type="button" id="scan-stop-btn" class="btn" style="flex:1;justify-content:center;display:none;" onclick="stopScanner()">
                    <i class="fas fa-stop"></i> Stop Camera
                  </button>
                </div>
              </div>
            </div>
          </div>

          {{-- Manual Tab --}}
          <div id="tab-manual" class="att-panel" style="display:none;">
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-keyboard"></i> Manual Entry</h3></div>
              <div class="card-body">
                <form method="POST" action="{{ route('attendance.store') }}">
                  @csrf
                  <input type="hidden" name="method" value="manual">
                  <div class="lf-group">
                    <label class="lf-label">Passenger</label>
                    <div class="lf-input-wrap"><i class="fas fa-user"></i>
                      <select class="lf-input" name="passenger_id" required>
                        <option value="">— Select Passenger —</option>
                        @foreach($passengers as $p)
                          <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->roll }})</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="lf-group">
                    <label class="lf-label">Date</label>
                    <div class="lf-input-wrap"><i class="fas fa-calendar"></i>
                      <input class="lf-input" type="date" name="date" value="{{ now()->toDateString() }}" required>
                    </div>
                  </div>
                  <div class="lf-group">
                    <label class="lf-label">Status</label>
                    <div class="lf-input-wrap"><i class="fas fa-circle-dot"></i>
                      <select class="lf-input" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                      </select>
                    </div>
                  </div>
                  <div class="lf-group">
                    <label class="lf-label">Time</label>
                    <div class="lf-input-wrap"><i class="fas fa-clock"></i>
                      <input class="lf-input" type="time" name="time" value="{{ now()->format('H:i') }}">
                    </div>
                  </div>
                  <button type="submit" class="btn btn-P" style="width:100%;justify-content:center;">
                    <i class="fas fa-check"></i> Record Attendance
                  </button>
                </form>
              </div>
            </div>
          </div>

          {{-- Security PIN Tab --}}
          <div id="tab-fingerprint" class="att-panel" style="display:none;">
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-shield-halved"></i> Security PIN Verify</h3></div>
              <div class="card-body">
                <div id="fp-feedback" style="display:none;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:13px;font-weight:600;"></div>
                <div id="fp-passenger-card" class="scan-result-card" style="display:none;"></div>

                <div class="lf-group" style="margin-bottom:6px;">
                  <label class="lf-label">Search Passenger — by Name or ID</label>
                  <div class="lf-input-wrap"><i class="fas fa-magnifying-glass"></i>
                    <input class="lf-input" type="text" id="fp-search" placeholder="Type a name or roll no…" autocomplete="off" oninput="fpSearchInput()">
                  </div>
                </div>
                <div id="fp-search-results" style="display:none;max-height:220px;overflow-y:auto;border:1px solid rgba(30,27,46,.1);border-radius:10px;background:var(--color-surface);"></div>

                <div id="fp-selected" style="display:none;align-items:center;justify-content:space-between;gap:10px;background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.25);border-radius:10px;padding:10px 14px;margin-top:12px;">
                  <div>
                    <div id="fp-selected-name" style="font-weight:700;color:var(--color-text);font-size:14px;"></div>
                    <div id="fp-selected-roll" style="font-size:11px;color:rgba(30,27,46,.55);font-family:monospace;letter-spacing:.05em;"></div>
                  </div>
                  <button type="button" class="btn btn-S" style="font-size:12px;" onclick="fpClearSelection()"><i class="fas fa-arrow-rotate-left"></i> Change</button>
                </div>

                <div id="fp-pin-wrap" style="display:none;margin-top:16px;">
                  <div class="lf-group">
                    <label class="lf-label">Enter Security PIN</label>
                    <div class="lf-input-wrap"><i class="fas fa-shield-halved"></i>
                      <input class="lf-input" type="password" id="fp-pin" placeholder="Enter enrolled PIN" maxlength="20">
                    </div>
                  </div>
                  <button class="btn btn-P" style="width:100%;justify-content:center;margin-top:8px;" onclick="verifyFingerprint()">
                    <i class="fas fa-shield-halved"></i> Verify & Mark Present
                  </button>
                </div>

                <div id="fp-not-enrolled" style="display:none;text-align:center;padding:20px 0;color:rgba(30,27,46,.6);font-size:13px;">
                  <i class="fas fa-shield-halved" style="font-size:36px;display:block;margin-bottom:10px;"></i>
                  This passenger has no Security PIN enrolled.
                </div>
              </div>
            </div>
          </div>

          {{-- QR Generator Tab --}}
          <div id="tab-qrgen" class="att-panel" style="display:none;">
            <div class="card">
              <div class="card-header"><h3><i class="fas fa-qrcode"></i> Generate QR Pass</h3></div>
              <div class="card-body">
                <p style="font-size:13px;color:rgba(30,27,46,.45);margin-bottom:16px;">
                  Select a passenger to view and print their QR attendance pass.
                </p>
                <div class="lf-group">
                  <label class="lf-label">Select Passenger</label>
                  <div class="lf-input-wrap"><i class="fas fa-user"></i>
                    <select class="lf-input" id="qr-passenger-select" onchange="showQrPreview(this)">
                      <option value="">— Select Passenger —</option>
                      @foreach($passengers as $p)
                        <option value="{{ $p->id }}"
                                data-token="{{ $p->qr_token }}"
                                data-name="{{ $p->name }}"
                                data-roll="{{ $p->roll }}"
                                data-url="{{ route('attendance.scan', ['token' => $p->qr_token]) }}"
                                data-dl="{{ route('passengers.qr', $p) }}">
                          {{ $p->name }} ({{ $p->roll }})
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div id="qr-preview" style="display:none;text-align:center;margin-top:16px;">
                  <div style="background:#fff;border-radius:14px;padding:16px;display:inline-block;box-shadow:0 4px 20px rgba(0,0,0,.3);">
                    <div id="att-qrcode"></div>
                  </div>
                  <div id="qr-passenger-name" style="color:var(--color-text);font-weight:700;margin-top:10px;"></div>
                  <div id="qr-passenger-roll" style="color:rgba(30,27,46,.65);font-size:12px;margin-top:2px;font-family:monospace;letter-spacing:.1em;"></div>
                  <div style="display:flex;gap:10px;margin-top:14px;justify-content:center;">
                    <a id="qr-dl-btn" href="#" target="_blank" class="btn btn-P" style="font-size:12px;">
                      <i class="fas fa-download"></i> Print / Download
                    </a>
                    <a id="qr-view-btn" href="#" target="_blank" class="btn btn-S" style="font-size:12px;">
                      <i class="fas fa-eye"></i> View Pass
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>{{-- end left --}}

        {{-- ── Right: Today's Attendance ── --}}
        <div class="card">
          <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3>Today's Records</h3>
            <span style="font-size:12px;color:rgba(30,27,46,.55);">{{ now()->format('d M Y') }}</span>
          </div>
          <div class="card-body">
            <table class="tbl">
              <thead>
                <tr><th>#</th><th>Name</th><th>Roll</th><th>Route</th><th>Time</th><th>Method</th><th>Status</th></tr>
              </thead>
              <tbody>
                @forelse($attendance as $a)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $a->passenger->name ?? '—' }}</td>
                  <td style="font-family:monospace;font-size:11px;">{{ $a->passenger->roll ?? '—' }}</td>
                  <td>{{ $a->passenger->route->name ?? '—' }}</td>
                  <td>{{ $a->time ? \Carbon\Carbon::parse($a->time)->format('h:i A') : '—' }}</td>
                  <td>
                    @if($a->method === 'qr')
                      <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#7C3AED;font-weight:600;"><i class="fas fa-qrcode"></i> QR</span>
                    @elseif($a->method === 'fingerprint')
                      <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#16A34A;font-weight:600;"><i class="fas fa-shield-halved"></i> Security PIN</span>
                    @else
                      <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:rgba(30,27,46,.6);"><i class="fas fa-keyboard"></i> Manual</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge {{ $a->status=='Present' ? 'badge-G' : 'badge-ERR' }}">{{ $a->status }}</span>
                  </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;color:rgba(30,27,46,.55);">No records for today yet.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>{{-- end grid --}}
    </div>
  </div>
</div>

{{-- QRCode.js (generator) + html5-qrcode (camera scanner) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.att-tab {
  padding:9px 18px;border-radius:8px;border:1px solid rgba(30,27,46,.10);
  background:var(--color-surface-2);color:rgba(30,27,46,.5);font-size:13px;
  font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;transition:.2s;
}
.att-tab:hover {
  background:rgba(124,58,237,.1);border-color:rgba(124,58,237,.3);color:#7C3AED;
}
.att-tab.active {
  background:var(--gradient-brand);border-color:transparent;color:#fff;
  box-shadow:0 6px 16px rgba(124,58,237,.25);
}
.fp-result-row:hover { background:rgba(124,58,237,.08); }
.fp-result-row:last-child { border-bottom:none; }

.scan-result-card {
  border:1px solid rgba(30,27,46,.10);border-radius:12px;background:var(--color-surface);
  padding:14px 16px;margin-bottom:14px;font-size:13px;
}
.scan-result-card .src-name { font-weight:700;font-size:15px;color:var(--color-text);margin-bottom:2px; }
.scan-result-card .src-roll { font-size:11px;color:rgba(30,27,46,.55);font-family:monospace;letter-spacing:.05em;margin-bottom:10px; }
.scan-result-card .src-grid { display:grid;grid-template-columns:1fr 1fr;gap:8px 14px; }
.scan-result-card .src-lbl { font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:rgba(30,27,46,.45);margin-bottom:2px; }
.scan-result-card .src-val { font-weight:600;color:var(--color-text); }
.scan-pill { display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700; }
.scan-pill-ok   { background:rgba(22,163,74,.12);color:#16A34A; }
.scan-pill-warn { background:rgba(217,119,6,.12);color:#D97706; }
.scan-pill-bad  { background:rgba(220,38,38,.12);color:#DC2626; }
</style>

<script>
// ── Tab switching ──
function switchTab(tab, btn) {
  document.querySelectorAll('.att-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.att-tab.active').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).style.display = 'block';
  if (btn && btn.classList.contains('att-tab')) btn.classList.add('active');

  // Stop the camera whenever we navigate away from the Scanner tab
  if (tab !== 'scanner') stopScanner();
}

// ── Camera QR Scanner ──
let html5QrScanner = null;
let scannerBusy = false; // debounce: pause reading while we're verifying/awaiting a scan result

function pillClass(kind) {
  return kind === 'ok' ? 'scan-pill scan-pill-ok' : (kind === 'warn' ? 'scan-pill scan-pill-warn' : 'scan-pill scan-pill-bad');
}

// Renders the passenger info / attendance / fee / pass status card returned by
// the scan-camera and Security PIN endpoints after every scan attempt.
function renderPassengerCard(containerId, passenger) {
  const el = document.getElementById(containerId);
  if (!passenger) { el.style.display = 'none'; el.innerHTML = ''; return; }

  const attPill = pillClass(passenger.attendance_status === 'Present' ? 'ok' : 'warn');
  const feePill = pillClass(passenger.fee_status === 'Paid' ? 'ok' : 'bad');
  const passPill = pillClass(passenger.pass_status === 'Active' ? 'ok' : 'bad');

  el.style.display = 'block';
  el.innerHTML = `
    <div class="src-name">${passenger.name}</div>
    <div class="src-roll">${passenger.roll}${passenger.department ? ' · ' + passenger.department : ''}</div>
    <div class="src-grid">
      <div><div class="src-lbl">Route / Stop</div><div class="src-val">${passenger.route ?? '—'}${passenger.stop ? ' — ' + passenger.stop : ''}</div></div>
      <div><div class="src-lbl">Attendance</div><span class="${attPill}">${passenger.attendance_status}${passenger.attendance_time ? ' · ' + passenger.attendance_time : ''}</span></div>
      <div><div class="src-lbl">Fee Status</div><span class="${feePill}">${passenger.fee_status}</span></div>
      <div><div class="src-lbl">Pass Status</div><span class="${passPill}">${passenger.pass_status}</span></div>
    </div>`;
}

function scanFeedback(kind, html) {
  const fb = document.getElementById('scan-feedback');
  const styles = {
    info:    { bg: 'var(--color-surface-2)', fg: 'rgba(30,27,46,.5)', bd: 'rgba(30,27,46,.08)' },
    success: { bg: 'rgba(22,163,74,.1)',     fg: '#16A34A',           bd: 'rgba(22,163,74,.3)' },
    warn:    { bg: 'rgba(217,119,6,.1)',     fg: '#D97706',           bd: 'rgba(217,119,6,.3)' },
    error:   { bg: 'rgba(220,38,38,.1)',     fg: '#DC2626',           bd: 'rgba(220,38,38,.3)' },
  }[kind];
  fb.style.display = 'block';
  fb.style.background = styles.bg;
  fb.style.color = styles.fg;
  fb.style.border = '1px solid ' + styles.bd;
  fb.innerHTML = html;
}

function startScanner() {
  if (html5QrScanner) return;

  document.getElementById('scan-start-btn').style.display = 'none';
  document.getElementById('scan-stop-btn').style.display = 'block';

  html5QrScanner = new Html5Qrcode('qr-reader');
  html5QrScanner.start(
    { facingMode: 'environment' },
    { fps: 10, qrbox: { width: 240, height: 240 } },
    onQrDecoded,
    () => {} // per-frame "no QR found" — ignore, fires constantly
  ).catch(err => {
    scanFeedback('error', '<i class="fas fa-circle-exclamation"></i> Could not access camera: ' + err + '. Check browser camera permissions.');
    document.getElementById('scan-start-btn').style.display = 'block';
    document.getElementById('scan-stop-btn').style.display = 'none';
    html5QrScanner = null;
  });
}

function stopScanner() {
  if (!html5QrScanner) return;
  const s = html5QrScanner;
  html5QrScanner = null; // clear immediately so a second call can't double-stop
  s.stop().then(() => s.clear()).catch(() => {});
  document.getElementById('scan-start-btn').style.display = 'block';
  document.getElementById('scan-stop-btn').style.display = 'none';
}

async function onQrDecoded(decodedText) {
  if (scannerBusy) return;

  // The QR encodes the full scan URL (…/scan/{token}) — pull the token off the end.
  const token = decodedText.split('/').filter(Boolean).pop();
  if (!token) return;

  scannerBusy = true;
  scanFeedback('info', '<i class="fas fa-spinner fa-spin"></i> Verifying...');

  try {
    const res = await fetch('{{ route("attendance.scanCamera") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ token }),
    });
    const data = await res.json();
    renderPassengerCard('scan-passenger-card', data.passenger);

    if (data.status === 'success') {
      // Attendance marked successfully
      scanFeedback('success', `<i class="fas fa-circle-check"></i> <strong>${data.status_label}.</strong> ${data.message}`);
    } else if (data.status === 'already') {
      scanFeedback('warn', `<i class="fas fa-triangle-exclamation"></i> <strong>${data.status_label}.</strong> ${data.message}`);
    } else {
      // Covers fee_pending, not_active, invalid, and not_found — status_label names which one.
      scanFeedback('error', `<i class="fas fa-circle-xmark"></i> <strong>${data.status_label}.</strong> ${data.message}`);
    }
  } catch (err) {
    scanFeedback('error', '<i class="fas fa-circle-xmark"></i> <strong>Attendance Failed.</strong> Network error. Please try again.');
  }

  // Brief pause so the same badge isn't scanned twice in a row, then keep scanning.
  setTimeout(() => { scannerBusy = false; }, 2500);
}

// ── Security PIN tab: search-by-name-or-ID ──
const fpPassengers = [
  @foreach($passengers as $p)
  { id: {{ $p->id }}, name: @json($p->name), roll: @json($p->roll), enrolled: {{ $p->fingerprint_enrolled ? 'true' : 'false' }} },
  @endforeach
];
let fpSelectedId = null;

function fpSearchInput() {
  const q = document.getElementById('fp-search').value.trim().toLowerCase();
  const results = document.getElementById('fp-search-results');

  if (!q) { results.style.display = 'none'; results.innerHTML = ''; return; }

  const matches = fpPassengers.filter(p =>
    p.name.toLowerCase().includes(q) || p.roll.toLowerCase().includes(q)
  ).slice(0, 8);

  results.style.display = 'block';

  if (!matches.length) {
    results.innerHTML = '<div style="padding:12px 14px;font-size:12px;color:rgba(30,27,46,.5);">No passenger found.</div>';
    return;
  }

  results.innerHTML = matches.map(p => `
    <div class="fp-result-row" style="padding:10px 14px;cursor:pointer;border-bottom:1px solid rgba(30,27,46,.06);" onclick="fpSelectPassenger(${p.id})">
      <div style="font-weight:600;font-size:13px;color:var(--color-text);">${p.name}</div>
      <div style="font-size:11px;color:rgba(30,27,46,.55);font-family:monospace;letter-spacing:.05em;">${p.roll}</div>
    </div>`).join('');
}

function fpSelectPassenger(id) {
  const p = fpPassengers.find(x => x.id === id);
  if (!p) return;

  fpSelectedId = id;
  document.getElementById('fp-search').value = '';
  document.getElementById('fp-search-results').style.display = 'none';
  document.getElementById('fp-search-results').innerHTML = '';

  document.getElementById('fp-selected').style.display = 'flex';
  document.getElementById('fp-selected-name').textContent = p.name;
  document.getElementById('fp-selected-roll').textContent = p.roll;

  document.getElementById('fp-pin-wrap').style.display     = p.enrolled ? 'block' : 'none';
  document.getElementById('fp-not-enrolled').style.display = p.enrolled ? 'none'  : 'block';
  document.getElementById('fp-feedback').style.display = 'none';
}

function fpClearSelection() {
  fpSelectedId = null;
  document.getElementById('fp-selected').style.display = 'none';
  document.getElementById('fp-pin-wrap').style.display = 'none';
  document.getElementById('fp-not-enrolled').style.display = 'none';
  document.getElementById('fp-pin').value = '';
  document.getElementById('fp-search').focus();
}

async function verifyFingerprint() {
  const passengerId = fpSelectedId;
  const pin         = document.getElementById('fp-pin').value;
  const fb          = document.getElementById('fp-feedback');

  if (!passengerId || !pin) {
    fb.style.display = 'block';
    fb.style.background = 'rgba(220,38,38,.1)';
    fb.style.color = '#DC2626';
    fb.style.border = '1px solid rgba(220,38,38,.3)';
    fb.innerHTML = '<i class="fas fa-circle-xmark"></i> Please select a passenger and enter the PIN.';
    return;
  }

  fb.style.display = 'block';
  fb.style.background = 'var(--color-surface-2)';
  fb.style.color = 'rgba(30,27,46,.5)';
  fb.style.border = '1px solid rgba(30,27,46,.08)';
  fb.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying Security PIN...';

  try {
    const res = await fetch('{{ route("attendance.fingerprint") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ passenger_id: passengerId, fingerprint_data: pin }),
    });

    const data = await res.json();
    renderPassengerCard('fp-passenger-card', data.passenger);

    if (data.success) {
      fb.style.background = 'rgba(22,163,74,.1)';
      fb.style.color = '#16A34A';
      fb.style.border = '1px solid rgba(22,163,74,.3)';
      fb.innerHTML = `<i class="fas fa-circle-check"></i> <strong>${data.status_label}.</strong> ${data.message}`;
      fpClearSelection();
    } else {
      fb.style.background = 'rgba(220,38,38,.1)';
      fb.style.color = '#DC2626';
      fb.style.border = '1px solid rgba(220,38,38,.3)';
      fb.innerHTML = `<i class="fas fa-circle-xmark"></i> <strong>${data.status_label}.</strong> ${data.message}`;
    }
  } catch (err) {
    fb.innerHTML = '<i class="fas fa-circle-xmark"></i> <strong>Attendance Failed.</strong> Network error. Please try again.';
    fb.style.color = '#DC2626';
  }
}

// ── QR Generator tab ──
let qrInstance = null;

function showQrPreview(sel) {
  const opt = sel.options[sel.selectedIndex];
  const wrap = document.getElementById('qr-preview');

  if (!sel.value) { wrap.style.display = 'none'; return; }

  wrap.style.display = 'block';
  document.getElementById('qr-passenger-name').textContent = opt.dataset.name;
  document.getElementById('qr-passenger-roll').textContent = 'TOKEN: ' + opt.dataset.token;
  document.getElementById('qr-dl-btn').href   = opt.dataset.dl;
  document.getElementById('qr-view-btn').href = opt.dataset.url;

  // Destroy previous QR
  const container = document.getElementById('att-qrcode');
  container.innerHTML = '';
  qrInstance = new QRCode(container, {
    text: opt.dataset.url,
    width: 160, height: 160,
    colorDark: '#1e1b4b', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
  });
}
</script>
@endsection

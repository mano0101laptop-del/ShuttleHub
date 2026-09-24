@extends('layout.app')
@section('title', 'Scanner Terminal — Shuttle Hub')
@section('content')

<div class="screen active" id="screen-app">
  @include('partials.sidebar')

  <div class="main">
    <x-topbar title="Scanner Terminal">
      <div id="scanner-top-context" class="scanner-top-context" style="display:none;">
        <i class="fas fa-bus"></i>
        <span id="scanner-top-bus">—</span>
        <span class="scanner-top-dot"></span>
        <i class="fas fa-route"></i>
        <span id="scanner-top-route">—</span>
      </div>
    </x-topbar>

    <div class="content scanner-content">
      <div class="scanner-shell">

        <div class="scanner-progress" aria-label="Scanner setup progress">
          <div class="scanner-progress-item active" id="progress-bus">
            <span>1</span>
            <div><strong>Select Bus</strong><small>Load today's trip</small></div>
          </div>
          <div class="scanner-progress-line"></div>
          <div class="scanner-progress-item" id="progress-scan">
            <span>2</span>
            <div><strong>Mark Attendance</strong><small>QR code or Security PIN</small></div>
          </div>
        </div>

        {{-- STEP 1: BUS ENTRY --}}
        <section id="scanner-entry-view" class="scanner-view active">
          <div class="scanner-entry-grid">
            <div class="scanner-entry-main">
              <div class="scanner-eyebrow"><i class="fas fa-bus-simple"></i> Start attendance session</div>
              <h1>Enter Bus Number</h1>
              <p class="scanner-lead">Enter the bus number first. Shuttle Hub will automatically fetch the assigned driver, route and ordered stops before opening the scanner.</p>

              <form id="bus-lookup-form" class="scanner-bus-form" autocomplete="off">
                <label for="scanner-bus-number">Bus Number</label>
                <div class="scanner-input-wrap">
                  <i class="fas fa-bus"></i>
                  <input id="scanner-bus-number" name="bus_number" type="text" placeholder="e.g. GWR-2201" maxlength="50" required autofocus>
                </div>
                <button type="submit" id="bus-lookup-btn" class="btn btn-P scanner-primary-btn">
                  <i class="fas fa-arrow-right"></i>
                  <span>Get Bus Details</span>
                </button>
              </form>

              <div id="bus-lookup-error" class="scanner-inline-alert error" style="display:none;"></div>

              <div class="scanner-helper">
                <i class="fas fa-wand-magic-sparkles"></i>
                <span>Driver, today's route and stop list are loaded automatically from your Shuttle Hub records.</span>
              </div>
            </div>

            <aside class="scanner-bus-panel" id="entry-bus-panel">
              <div id="bus-panel-placeholder" class="scanner-panel-placeholder">
                <div class="scanner-panel-placeholder-icon"><i class="fas fa-route"></i></div>
                <h3>Bus details appear here</h3>
                <p>Type a valid active bus number to verify the trip before attendance scanning starts.</p>
              </div>

              <div id="bus-panel-content" style="display:none;">
                <div class="scanner-found-label"><i class="fas fa-circle-check"></i> Bus Found</div>
                <div class="scanner-bus-heading">
                  <div class="scanner-bus-icon"><i class="fas fa-bus"></i></div>
                  <div>
                    <small>Selected Bus</small>
                    <h2 id="entry-bus-number">—</h2>
                  </div>
                </div>

                <div class="scanner-detail-row">
                  <div class="scanner-detail-icon"><i class="fas fa-user-tie"></i></div>
                  <div><small>Driver</small><strong id="entry-driver">—</strong></div>
                </div>
                <div class="scanner-detail-row">
                  <div class="scanner-detail-icon"><i class="fas fa-location-dot"></i></div>
                  <div><small>Route</small><strong id="entry-route">—</strong><span id="entry-route-path"></span></div>
                </div>
                <div class="scanner-detail-row scanner-stops-row">
                  <div class="scanner-detail-icon"><i class="fas fa-map"></i></div>
                  <div class="scanner-detail-grow">
                    <small>Stops</small>
                    <div id="entry-stops" class="scanner-stop-list"></div>
                  </div>
                </div>

                <button type="button" id="continue-scanner-btn" class="btn btn-P scanner-primary-btn" onclick="openScannerView()">
                  <i class="fas fa-clipboard-check"></i> Continue to Attendance
                </button>
              </div>
            </aside>
          </div>
        </section>

        {{-- STEP 2: SCANNER --}}
        <section id="scanner-camera-view" class="scanner-view">
          <div class="scanner-workspace-head">
            <div>
              <button type="button" class="scanner-back-btn" onclick="changeBus()"><i class="fas fa-arrow-left"></i> Change Bus</button>
              <h1>Mark Passenger Attendance</h1>
              <p>Use the QR code printed on the passenger card, or verify the passenger with the Security PIN already registered in Shuttle Hub.</p>
            </div>
            <div class="scanner-live-count">
              <span>{{ $stats['present'] }}</span>
              <small>present today</small>
            </div>
          </div>

          <div class="scanner-workspace-grid">
            <div class="scanner-camera-column">
              <div class="scanner-method-switch" role="tablist" aria-label="Attendance method">
                <button type="button" id="mode-qr-btn" class="scanner-method-btn active" role="tab" aria-selected="true" onclick="switchAttendanceMode('qr')">
                  <span class="scanner-method-icon"><i class="fas fa-qrcode"></i></span>
                  <span><strong>QR Code</strong><small>Scan code on passenger card</small></span>
                </button>
                <button type="button" id="mode-pin-btn" class="scanner-method-btn" role="tab" aria-selected="false" onclick="switchAttendanceMode('pin')">
                  <span class="scanner-method-icon"><i class="fas fa-shield-halved"></i></span>
                  <span><strong>Security PIN</strong><small>Use registered passenger PIN</small></span>
                </button>
              </div>

              <div id="scan-feedback" class="scanner-inline-alert" style="display:none;"></div>
              <div id="scan-passenger-card" class="scanner-passenger-card" style="display:none;"></div>

              <div id="scanner-qr-panel" class="scanner-method-panel active">
                <div class="scanner-camera-card">
                  <div id="qr-reader" class="scanner-reader"></div>
                  <div id="scanner-camera-placeholder" class="scanner-camera-placeholder">
                    <div class="scanner-qr-guide" aria-hidden="true">
                      <div class="scanner-frame-corner tl"></div><div class="scanner-frame-corner tr"></div>
                      <div class="scanner-frame-corner bl"></div><div class="scanner-frame-corner br"></div>
                      <div class="scanner-qr-icon"><i class="fas fa-qrcode"></i></div>
                    </div>
                    <strong>Place the QR code inside the square</strong>
                    <span>Scan only the QR code printed on the passenger card — the full card does not need to fit in the frame.</span>
                  </div>
                </div>

                <div class="scanner-camera-actions">
                  <button type="button" id="scan-start-btn" class="btn btn-P scanner-primary-btn" onclick="startScanner()">
                    <i class="fas fa-camera"></i> Start Camera
                  </button>
                  <button type="button" id="scan-stop-btn" class="btn btn-S scanner-primary-btn" style="display:none;" onclick="stopScanner()">
                    <i class="fas fa-stop"></i> Stop Camera
                  </button>
                </div>

                <div id="scanner-ready-pill" class="scanner-ready-pill">
                  <i class="fas fa-circle"></i> QR scanner ready
                </div>
              </div>

              <div id="scanner-pin-panel" class="scanner-method-panel">
                <div class="scanner-pin-card">
                  <div class="scanner-pin-head">
                    <div class="scanner-pin-head-icon"><i class="fas fa-shield-halved"></i></div>
                    <div>
                      <h3>Security PIN Attendance</h3>
                      <p>Enter the passenger's ID / roll number and the Security PIN already registered for that passenger.</p>
                    </div>
                  </div>

                  <form id="security-pin-form" class="scanner-pin-form" autocomplete="off">
                    <label for="scanner-passenger-roll">Passenger ID / Roll No</label>
                    <div class="scanner-pin-field">
                      <i class="fas fa-id-card"></i>
                      <input id="scanner-passenger-roll" type="text" maxlength="100" placeholder="e.g. 2026-CS-014" required>
                    </div>

                    <label for="scanner-security-pin">Security PIN</label>
                    <div class="scanner-pin-field scanner-pin-password">
                      <i class="fas fa-key"></i>
                      <input id="scanner-security-pin" type="password" minlength="6" maxlength="20" placeholder="Enter registered Security PIN" required autocomplete="off">
                      <button type="button" class="scanner-pin-toggle" id="scanner-pin-toggle" onclick="togglePinVisibility()" aria-label="Show Security PIN" title="Show Security PIN">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>

                    <button type="submit" id="security-pin-submit" class="btn btn-P scanner-primary-btn">
                      <i class="fas fa-user-check"></i><span>Verify PIN &amp; Mark Present</span>
                    </button>
                  </form>

                  <div class="scanner-pin-note">
                    <i class="fas fa-circle-info"></i>
                    <span>The PIN is checked against the passenger's existing encrypted PIN. The passenger must belong to the route of the selected bus.</span>
                  </div>
                </div>
              </div>
            </div>

            <aside class="scanner-current-card">
              <div class="scanner-current-head">
                <div>
                  <small>Current Bus Details</small>
                  <h2 id="scan-bus-number">—</h2>
                </div>
                <div class="scanner-mini-bus"><i class="fas fa-bus"></i></div>
              </div>

              <div class="scanner-detail-row">
                <div class="scanner-driver-avatar" id="scan-driver-avatar">D</div>
                <div><small>Driver</small><strong id="scan-driver">—</strong></div>
              </div>
              <div class="scanner-detail-row">
                <div class="scanner-detail-icon"><i class="fas fa-location-dot"></i></div>
                <div><small>Route</small><strong id="scan-route">—</strong><span id="scan-route-path"></span></div>
              </div>
              <div class="scanner-detail-row scanner-stops-row">
                <div class="scanner-detail-icon"><i class="fas fa-map"></i></div>
                <div class="scanner-detail-grow">
                  <small>Stops</small>
                  <div id="scan-stops" class="scanner-stop-list"></div>
                </div>
              </div>
              <div id="scan-assignment-note" class="scanner-assignment-note"></div>
            </aside>
          </div>
        </section>

      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
const scannerRoutes = {
  busDetails: @json(route('scanner.busDetails')),
  scanCamera: @json(route('attendance.scanCamera')),
  pinVerify: @json(route('attendance.fingerprint')),
};
const csrfToken = @json(csrf_token());
let selectedBus = null;
let html5QrScanner = null;
let scannerBusy = false;
let pinBusy = false;
let attendanceMode = 'qr';

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
}

function setLookupError(message = '') {
  const box = document.getElementById('bus-lookup-error');
  if (!message) { box.style.display = 'none'; box.innerHTML = ''; return; }
  box.innerHTML = `<i class="fas fa-circle-exclamation"></i><span>${escapeHtml(message)}</span>`;
  box.style.display = 'flex';
}

function stopListHtml(stops) {
  if (!Array.isArray(stops) || stops.length === 0) {
    return '<div class="scanner-stop-empty">No stops added to this route.</div>';
  }
  return stops.map((stop, index) => `
    <div class="scanner-stop-item ${index === 0 ? 'active' : ''}">
      <span class="scanner-stop-dot"></span>
      <div><strong>${escapeHtml(stop.name)}</strong>${stop.time ? `<small>${escapeHtml(stop.time)}</small>` : ''}</div>
    </div>`).join('');
}

function renderBusDetails(data) {
  selectedBus = data;
  document.getElementById('bus-panel-placeholder').style.display = 'none';
  document.getElementById('bus-panel-content').style.display = 'block';

  const driver = data.driver?.name || 'Driver not assigned';
  const path = [data.route?.from, data.route?.to].filter(Boolean).join(' → ');

  document.getElementById('entry-bus-number').textContent = data.bus.number;
  document.getElementById('entry-driver').textContent = driver;
  document.getElementById('entry-route').textContent = data.route.name;
  document.getElementById('entry-route-path').textContent = path;
  document.getElementById('entry-stops').innerHTML = stopListHtml(data.stops);

  document.getElementById('scan-bus-number').textContent = data.bus.number;
  document.getElementById('scan-driver').textContent = driver;
  document.getElementById('scan-driver-avatar').textContent = driver.trim().charAt(0).toUpperCase() || 'D';
  document.getElementById('scan-route').textContent = data.route.name;
  document.getElementById('scan-route-path').textContent = path;
  document.getElementById('scan-stops').innerHTML = stopListHtml(data.stops);

  const assignmentText = data.assignment?.schedule_specific
    ? `Active Schedule${data.assignment.departure_time ? ' · Departure ' + data.assignment.departure_time : ''}`
    : 'Using the bus\'s default route assignment';
  document.getElementById('scan-assignment-note').innerHTML = `<i class="fas fa-calendar-check"></i> ${escapeHtml(assignmentText)}`;
}

document.getElementById('bus-lookup-form').addEventListener('submit', async (event) => {
  event.preventDefault();
  const input = document.getElementById('scanner-bus-number');
  const button = document.getElementById('bus-lookup-btn');
  const number = input.value.trim();
  if (!number) return;

  setLookupError();
  button.disabled = true;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Finding bus…</span>';

  try {
    const url = new URL(scannerRoutes.busDetails, window.location.origin);
    url.searchParams.set('bus_number', number);
    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await response.json();

    if (!response.ok || !data.success) throw new Error(data.message || 'Bus could not be loaded.');
    renderBusDetails(data);
  } catch (error) {
    selectedBus = null;
    document.getElementById('bus-panel-placeholder').style.display = 'flex';
    document.getElementById('bus-panel-content').style.display = 'none';
    setLookupError(error.message || 'Unable to load bus details.');
  } finally {
    button.disabled = false;
    button.innerHTML = '<i class="fas fa-arrow-right"></i><span>Get Bus Details</span>';
  }
});

function setProgress(scannerActive) {
  document.getElementById('progress-bus').classList.toggle('done', scannerActive);
  document.getElementById('progress-bus').classList.toggle('active', !scannerActive);
  document.getElementById('progress-scan').classList.toggle('active', scannerActive);
}

function openScannerView() {
  if (!selectedBus) return;
  document.getElementById('scanner-entry-view').classList.remove('active');
  document.getElementById('scanner-camera-view').classList.add('active');
  document.getElementById('scanner-top-context').style.display = 'flex';
  document.getElementById('scanner-top-bus').textContent = selectedBus.bus.number;
  document.getElementById('scanner-top-route').textContent = selectedBus.route.name;
  setProgress(true);
  setAttendanceModeUi('qr');

  // The Continue button is a user gesture, so try opening the QR camera immediately.
  // Security PIN remains available as a separate attendance option.
  setTimeout(startScanner, 100);
}

function changeBus() {
  stopScanner();
  scannerBusy = false;
  pinBusy = false;
  document.getElementById('scanner-camera-view').classList.remove('active');
  document.getElementById('scanner-entry-view').classList.add('active');
  document.getElementById('scanner-top-context').style.display = 'none';
  setProgress(false);
  scanFeedback('', '');
  renderPassengerCard(null);
  document.getElementById('scanner-passenger-roll').value = '';
  document.getElementById('scanner-security-pin').value = '';
  setAttendanceModeUi('qr');
  document.getElementById('scanner-bus-number').focus();
}

function setAttendanceModeUi(mode) {
  attendanceMode = mode === 'pin' ? 'pin' : 'qr';
  const qrActive = attendanceMode === 'qr';
  document.getElementById('mode-qr-btn').classList.toggle('active', qrActive);
  document.getElementById('mode-pin-btn').classList.toggle('active', !qrActive);
  document.getElementById('mode-qr-btn').setAttribute('aria-selected', qrActive ? 'true' : 'false');
  document.getElementById('mode-pin-btn').setAttribute('aria-selected', qrActive ? 'false' : 'true');
  document.getElementById('scanner-qr-panel').classList.toggle('active', qrActive);
  document.getElementById('scanner-pin-panel').classList.toggle('active', !qrActive);
}

async function switchAttendanceMode(mode) {
  const nextMode = mode === 'pin' ? 'pin' : 'qr';
  if (nextMode === attendanceMode) return;

  if (nextMode === 'pin') {
    await stopScanner();
  }

  scannerBusy = false;
  scanFeedback('', '');
  renderPassengerCard(null);
  setAttendanceModeUi(nextMode);

  if (nextMode === 'qr') {
    startScanner();
  } else {
    setTimeout(() => document.getElementById('scanner-passenger-roll').focus(), 50);
  }
}

function togglePinVisibility() {
  const input = document.getElementById('scanner-security-pin');
  const button = document.getElementById('scanner-pin-toggle');
  const showing = input.type === 'text';
  input.type = showing ? 'password' : 'text';
  button.innerHTML = showing ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
  button.setAttribute('aria-label', showing ? 'Show Security PIN' : 'Hide Security PIN');
  button.title = showing ? 'Show Security PIN' : 'Hide Security PIN';
  input.focus();
}

function scanFeedback(kind, message) {
  const box = document.getElementById('scan-feedback');
  if (!kind || !message) { box.style.display = 'none'; box.innerHTML = ''; box.className = 'scanner-inline-alert'; return; }
  box.className = `scanner-inline-alert ${kind}`;
  box.innerHTML = message;
  box.style.display = 'flex';
}

function renderPassengerCard(passenger) {
  const card = document.getElementById('scan-passenger-card');
  if (!passenger) { card.style.display = 'none'; return; }

  const attendanceClass = passenger.attendance_status === 'Present' ? 'success' : 'warning';
  const passClass = passenger.pass_status === 'Active' ? 'success' : 'danger';
  const feeClass = passenger.fee_status === 'Paid' ? 'success' : 'danger';
  card.innerHTML = `
    <div class="scanner-passenger-icon"><i class="fas fa-user-check"></i></div>
    <div class="scanner-passenger-main">
      <small>Passenger detected</small>
      <h3>${escapeHtml(passenger.name)}</h3>
      <p>${escapeHtml(passenger.roll)}${passenger.department ? ' · ' + escapeHtml(passenger.department) : ''}</p>
      <div class="scanner-passenger-pills">
        <span class="${attendanceClass}">${escapeHtml(passenger.attendance_status)}${passenger.attendance_time ? ' · ' + escapeHtml(passenger.attendance_time) : ''}</span>
        <span class="${feeClass}">Fee ${escapeHtml(passenger.fee_status)}</span>
        <span class="${passClass}">Pass ${escapeHtml(passenger.pass_status)}</span>
      </div>
    </div>`;
  card.style.display = 'flex';
}

function showAttendanceResult(data) {
  renderPassengerCard(data.passenger);

  if (data.status === 'success') {
    scanFeedback('success', `<i class="fas fa-circle-check"></i><span><strong>${escapeHtml(data.status_label)}.</strong> ${escapeHtml(data.message)}</span>`);
  } else if (data.status === 'already') {
    scanFeedback('warning', `<i class="fas fa-triangle-exclamation"></i><span><strong>${escapeHtml(data.status_label)}.</strong> ${escapeHtml(data.message)}</span>`);
  } else {
    scanFeedback('error', `<i class="fas fa-circle-xmark"></i><span><strong>${escapeHtml(data.status_label || 'Attendance Failed')}.</strong> ${escapeHtml(data.message || 'Please try again.')}</span>`);
  }
}

async function startScanner() {
  if (attendanceMode !== 'qr' || html5QrScanner || !document.getElementById('scanner-camera-view').classList.contains('active')) return;
  if (typeof Html5Qrcode === 'undefined') {
    scanFeedback('error', '<i class="fas fa-circle-exclamation"></i><span>Scanner library could not load. Check the network and refresh.</span>');
    return;
  }

  document.getElementById('scan-start-btn').style.display = 'none';
  document.getElementById('scan-stop-btn').style.display = 'inline-flex';
  document.getElementById('scanner-ready-pill').classList.add('active');

  html5QrScanner = new Html5Qrcode('qr-reader');
  try {
    await html5QrScanner.start(
      { facingMode: 'environment' },
      { fps: 10, qrbox: { width: 230, height: 230 }, aspectRatio: 1.55 },
      onQrDecoded,
      () => {}
    );
    document.getElementById('scanner-camera-placeholder').classList.add('camera-on');
    scanFeedback('info', '<i class="fas fa-qrcode"></i><span>Camera active. Place the QR code printed on the passenger card inside the square.</span>');
  } catch (error) {
    html5QrScanner = null;
    document.getElementById('scan-start-btn').style.display = 'inline-flex';
    document.getElementById('scan-stop-btn').style.display = 'none';
    document.getElementById('scanner-ready-pill').classList.remove('active');
    scanFeedback('error', '<i class="fas fa-circle-exclamation"></i><span>Could not access the camera. Allow camera permission, then press Start Camera.</span>');
  }
}

async function stopScanner() {
  if (!html5QrScanner) {
    document.getElementById('scan-start-btn').style.display = 'inline-flex';
    document.getElementById('scan-stop-btn').style.display = 'none';
    return;
  }
  const scanner = html5QrScanner;
  html5QrScanner = null;
  try { await scanner.stop(); await scanner.clear(); } catch (_) {}
  document.getElementById('scanner-camera-placeholder').classList.remove('camera-on');
  document.getElementById('scan-start-btn').style.display = 'inline-flex';
  document.getElementById('scan-stop-btn').style.display = 'none';
  document.getElementById('scanner-ready-pill').classList.remove('active');
}

async function onQrDecoded(decodedText) {
  if (scannerBusy) return;
  const token = String(decodedText || '').split('/').filter(Boolean).pop();
  if (!token) return;

  scannerBusy = true;
  scanFeedback('info', '<i class="fas fa-spinner fa-spin"></i><span>Verifying passenger pass…</span>');

  try {
    const response = await fetch(scannerRoutes.scanCamera, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ token, route_id: selectedBus?.route?.id || null }),
    });
    const data = await response.json();
    showAttendanceResult(data);
  } catch (_) {
    scanFeedback('error', '<i class="fas fa-circle-xmark"></i><span><strong>Attendance Failed.</strong> Network error. Please try again.</span>');
  }

  setTimeout(() => { scannerBusy = false; }, 2500);
}

document.getElementById('security-pin-form').addEventListener('submit', async (event) => {
  event.preventDefault();
  if (pinBusy || !selectedBus) return;

  const rollInput = document.getElementById('scanner-passenger-roll');
  const pinInput = document.getElementById('scanner-security-pin');
  const submit = document.getElementById('security-pin-submit');
  const roll = rollInput.value.trim();
  const pin = pinInput.value;

  if (!roll) {
    scanFeedback('error', '<i class="fas fa-circle-xmark"></i><span>Enter the Passenger ID / Roll No first.</span>');
    rollInput.focus();
    return;
  }
  if (pin.length < 6) {
    scanFeedback('error', '<i class="fas fa-circle-xmark"></i><span>Security PIN must be at least 6 characters.</span>');
    pinInput.focus();
    return;
  }

  pinBusy = true;
  submit.disabled = true;
  submit.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Verifying PIN…</span>';
  scanFeedback('info', '<i class="fas fa-shield-halved"></i><span>Verifying passenger and Security PIN…</span>');

  try {
    const response = await fetch(scannerRoutes.pinVerify, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        roll,
        fingerprint_data: pin,
        route_id: selectedBus?.route?.id || null,
      }),
    });
    const data = await response.json();
    showAttendanceResult(data);

    // Never keep a passenger's PIN in the field after a verification attempt.
    pinInput.value = '';
    if (data.status === 'success' || data.status === 'already') {
      rollInput.value = '';
      rollInput.focus();
    } else {
      pinInput.focus();
    }
  } catch (_) {
    pinInput.value = '';
    scanFeedback('error', '<i class="fas fa-circle-xmark"></i><span><strong>Attendance Failed.</strong> Network error. Please try again.</span>');
    pinInput.focus();
  } finally {
    pinBusy = false;
    submit.disabled = false;
    submit.innerHTML = '<i class="fas fa-user-check"></i><span>Verify PIN &amp; Mark Present</span>';
  }
});

window.addEventListener('beforeunload', () => { if (html5QrScanner) html5QrScanner.stop().catch(() => {}); });
</script>
@endsection

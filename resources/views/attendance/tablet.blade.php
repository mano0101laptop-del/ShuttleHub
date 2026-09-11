<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Attendance Hardware Terminal</title>
    <style>
        body { background: #0b0f19; font-family: sans-serif; text-align: center; color: white; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .container { max-width: 500px; width: 100%; background: #111827; padding: 30px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); border: 1px solid #1f2937; position: relative; }
        
        .tab-navigation { display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid #1f2937; padding-bottom: 15px; justify-content: center; }
        .tab-btn { background: #1f2937; color: #9ca3af; border: 1px solid #374151; font-size: 16px; padding: 12px 24px; border-radius: 12px; cursor: pointer; font-weight: bold; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
        .tab-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; }
        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .keypad { display: grid; grid-template-columns: repeat(3, 75px); gap: 12px; justify-content: center; margin-top: 20px; }
        .keypad button { background: #1f2937; color: white; border: 1px solid #374151; font-size: 22px; padding: 18px; border-radius: 12px; cursor: pointer; transition: 0.1s; font-weight: bold; }
        .keypad button:active { background: #4f46e5; transform: scale(0.95); }
        
        #pin-display { font-size: 26px; text-align: center; width: 250px; padding: 12px; border-radius: 10px; margin-top: 15px; background: #1f2937; color: white; border: 1px solid #374151; letter-spacing: 4px; box-sizing: border-box; }
        #reader { width: 100%; max-width: 350px; margin: auto; background: #1f2937; border-radius: 16px; overflow: hidden; border: 1px solid #374151; }
        #reader button { font-size: 16px; padding: 10px; background: #4f46e5; border: none; margin: 10px 0; border-radius: 8px; color: white; cursor: pointer; }
        
        .alert-box { padding: 15px; border-radius: 10px; margin-bottom: 20px; display: none; font-weight: bold; font-size: 16px; }
        .alert-success { background: #10b981; color: white; border: 1px solid #059669; }
        .alert-error { background: #ef4444; color: white; border: 1px solid #dc2626; }
        .alert-already { background: #f59e0b; color: white; border: 1px solid #d97706; }
        select { width: 250px; padding: 12px; margin-top: 15px; background: #1f2937; color: white; border: 1px solid #374151; border-radius: 10px; font-size: 16px; outline: none; }
    </style>
</head>
<body>

<div class="container">
    <h2 style="margin-top: 0; color: #4f46e5; margin-bottom: 20px;">🚌 Bus Hardware Terminal</h2>
    
    <div id="status-message" class="alert-box"></div>

    <div class="tab-navigation">
        <button class="tab-btn active" onclick="switchMode('scanner')">Live Scanner</button>
        <button class="tab-btn" onclick="switchMode('pin-pad')">Security PIN</button>
    </div>

    <!-- 1. LIVE CAMERA SCANNER -->
    <div id="panel-scanner" class="tab-panel active">
        <div id="reader"></div>
        <p style="color: #9ca3af; font-size: 13px; margin-top: 15px;">Apna QR Pass camera ke samne dikhayein.</p>
    </div>

    <!-- 2. PIN INPUT KEYPAD -->
    <div id="panel-pin-pad" class="tab-panel">
        <select id="passenger_id">
            <option value="">-- Select Passenger --</option>
            @foreach($passengers as $p)
                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->roll }})</option>
            @endforeach
        </select>
        <br>
        <input type="password" id="pin-display" readonly placeholder="••••••">
        
        <div class="keypad">
            <button type="button" onclick="pressKey('1')">1</button>
            <button type="button" onclick="pressKey('2')">2</button>
            <button type="button" onclick="pressKey('3')">3</button>
            <button type="button" onclick="pressKey('4')">4</button>
            <button type="button" onclick="pressKey('5')">5</button>
            <button type="button" onclick="pressKey('6')">6</button>
            <button type="button" onclick="pressKey('7')">7</button>
            <button type="button" onclick="pressKey('8')">8</button>
            <button type="button" onclick="pressKey('9')">9</button>
            <button type="button" onclick="clearPIN()" style="background:#ef4444; border-color: #ef4444;">C</button>
            <button type="button" onclick="pressKey('0')">0</button>
            <button type="button" onclick="submitPIN()" style="background:#10b981; border-color: #10b981;">Go</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com"></script>
<script>
    let display = document.getElementById('pin-display');
    let isProcessing = false;

    function switchMode(mode) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + mode).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function pressKey(num) { if(display.value.length < 6) display.value += num; }
    function clearPIN() { display.value = ''; }

    function showMessage(status, text) {
        let msgBox = document.getElementById('status-message');
        msgBox.className = 'alert-box alert-' + (status === 'already' ? 'already' : (status === 'success' ? 'success' : 'error'));
        msgBox.innerText = text; msgBox.style.display = 'block';
        setTimeout(() => { msgBox.style.display = 'none'; isProcessing = false; }, 4000);
    }

    function onScanSuccess(decodedText) {
        if (isProcessing) return; isProcessing = true;
        fetch("{{ route('attendance.scanCamera') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ token: decodedText })
        })
        .then(res => res.json()).then(data => showMessage(data.status, data.message))
        .catch(err => showMessage('error', 'Scanning network failed.'));
    }

    function submitPIN() {
        let passengerId = document.getElementById('passenger_id').value;
        let pinData = display.value;
        if(!passengerId || !pinData) { alert('Select passenger & enter PIN!'); return; }

        fetch("{{ route('attendance.fingerprint') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ passenger_id: passengerId, fingerprint_data: pinData })
        })
        .then(res => res.json()).then(data => {
            showMessage(data.success ? 'success' : 'error', data.message);
            clearPIN(); document.getElementById('passenger_id').value = '';
        }).catch(err => showMessage('error', 'PIN processing timeout error.'));
    }

    let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 15, qrbox: 220 });
    html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>

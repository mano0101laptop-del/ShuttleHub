<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Result</title>
    <style>
        body { background: #0b0f19; font-family: sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; color: white; }
        .result-card { background: #111827; border-radius: 24px; padding: 30px; text-align: center; border: 1px solid #1f2937; box-shadow: 0 20px 50px rgba(0,0,0,0.5); max-width: 400px; width: 100%; }
        .status-icon { font-size: 60px; margin-bottom: 15px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .duplicate { color: #f59e0b; }
        .passenger-info { background: #1f2937; border-radius: 12px; padding: 15px; margin: 20px 0; text-align: left; font-size: 14px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 4px; }
        .info-label { color: #9ca3af; }
        .info-value { font-weight: 600; }
        .btn-back { display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: bold; font-size: 14px; transition: 0.2s; margin-top: 10px; }
        .btn-back:hover { background: #4338ca; }
    </style>
</head>
<body>

    <div class="result-card">
        @if($status === 'success')
            <div class="status-icon success">✓</div>
            <h2 class="success">SUCCESSFUL</h2>
        @elseif($status === 'duplicate')
            <div class="status-icon duplicate">⚠</div>
            <h2 class="duplicate">ALREADY MARKED</h2>
        @else
            <div class="status-icon error">✗</div>
            <h2 class="error">FAILED</h2>
        @endif

        <p style="color: #9ca3af; margin-bottom: 20px;">{{ $message }}</p>

        @if($passenger)
            <div class="passenger-info">
                <div class="info-row"><span class="info-label">Name:</span><span class="info-value">{{ $passenger['name'] }}</span></div>
                <div class="info-row"><span class="info-label">Roll No:</span><span class="info-value">{{ $passenger['roll'] }}</span></div>
                <div class="info-row"><span class="info-label">Dept:</span><span class="info-value">{{ $passenger['department'] }}</span></div>
                <div class="info-row"><span class="info-label">Route:</span><span class="info-value">{{ $passenger['route'] ?? '—' }}</span></div>
            </div>
        @endif

        <!-- Bacha card status dekhne ke baad automatically wapis tablet scanning page par jaye -->
        <a href="{{ route('scanner.index') }}" class="btn-back">Scan Next Passenger</a>
    </div>

</body>
</html>

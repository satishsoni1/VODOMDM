<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Sticker Sheet — {{ count($devices) }} Devices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f7f6; font-family: 'Segoe UI', system-ui, sans-serif; }
        .toolbar { max-width: 1000px; margin: 0 auto; padding: 1rem; }
        .sheet-wrap { max-width: 1000px; margin: 0 auto; padding: 0 1rem 2rem; }
        .sheet {
            background: #fff;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3mm;
            padding: 8mm;
            border: 1px solid #d4eeeb;
        }
        .label {
            border: 1px dashed #aaa;
            border-radius: 4px;
            padding: 3mm;
            text-align: center;
            break-inside: avoid;
        }
        .label img { width: 30mm; height: 30mm; }
        .label .asset-tag { font-family: 'Courier New', monospace; font-weight: 700; font-size: 3.2mm; margin-top: 1mm; }
        .label .serial { font-size: 2.4mm; color: #666; }
        .label .brand { font-size: 2.2mm; color: #1a8a7c; letter-spacing: .3px; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet-wrap { padding: 0; max-width: none; }
            .sheet { border: none; padding: 0; }
            @page { size: A4; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="toolbar no-print d-flex justify-content-between align-items-center">
    <div>
        <h6 class="fw-bold mb-0">QR Sticker Sheet</h6>
        <small class="text-muted">{{ count($devices) }} label{{ count($devices) === 1 ? '' : 's' }} — print on sticker paper, cut, and paste on each device.</small>
    </div>
    <div>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">Close</button>
    </div>
</div>

<div class="sheet-wrap">
    @if(count($devices) === 0)
        <div class="alert alert-warning no-print">No devices were selected. Go back and select at least one device.</div>
    @else
        <div class="sheet">
            @foreach($devices as $device)
                <div class="label">
                    <div class="brand">{{ $device->model?->brand?->name }}</div>
                    <img src="{{ route('devices.qr', $device) }}" alt="QR — {{ $device->asset_tag }}">
                    <div class="asset-tag">{{ $device->asset_tag }}</div>
                    <div class="serial">{{ $device->serial_number }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>

</body>
</html>

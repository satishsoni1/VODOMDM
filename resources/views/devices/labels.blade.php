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
        .toolbar .form-select { width: auto; display: inline-block; }
        .sheet-wrap { max-width: 1000px; margin: 0 auto; padding: 0 1rem 2rem; }
        .sheet {
            background: #fff;
            display: grid;
            gap: 3mm;
            padding: 8mm;
            border: 1px solid #d4eeeb;
            margin-bottom: 1rem;
            width: 210mm;
            min-height: 297mm;
            box-sizing: border-box;
        }
        .label {
            border: 1px dashed #aaa;
            border-radius: 4px;
            padding: 2mm;
            text-align: center;
            break-inside: avoid;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .label.empty { border-style: none; }
        .label img { width: 60%; max-width: 30mm; height: auto; aspect-ratio: 1/1; }
        .label .asset-tag { font-family: 'Courier New', monospace; font-weight: 700; font-size: 3.2mm; margin-top: 1mm; }
        .label .serial { font-size: 2.6mm; color: #666; }
        .label .imei { font-size: 2.6mm; color: #666; }
        .label .brand { font-size: 2.2mm; color: #1a8a7c; letter-spacing: .3px; }
        .label .employee { font-size: 2.6mm; color: #333; margin-top: .5mm; }
        .label .employee .code { font-family: 'Courier New', monospace; color: #666; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet-wrap { padding: 0; max-width: none; }
            .sheet { border: none; margin: 0; width: auto; min-height: 0; page-break-after: always; }
            .sheet:last-child { page-break-after: auto; }
            @page { size: A4; margin: 8mm; }
        }
    </style>
</head>
<body>

<div class="toolbar no-print d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h6 class="fw-bold mb-0">QR Sticker Sheet</h6>
        <small class="text-muted">{{ count($devices) }} label{{ count($devices) === 1 ? '' : 's' }} — choose how many fit per A4 sheet, then print on sticker paper, cut, and paste on each device.</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <label for="layoutSelect" class="form-label mb-0 small text-muted">Labels per A4 sheet</label>
        <select id="layoutSelect" class="form-select form-select-sm">
            <option value="2x4">8 per sheet (2 × 4) — large</option>
            <option value="3x4">12 per sheet (3 × 4)</option>
            <option value="3x5">15 per sheet (3 × 5)</option>
            <option value="3x7">21 per sheet (3 × 7)</option>
            <option value="3x8" selected>24 per sheet (3 × 8)</option>
            <option value="4x9">36 per sheet (4 × 9)</option>
            <option value="4x10">40 per sheet (4 × 10)</option>
            <option value="5x13">65 per sheet (5 × 13) — small</option>
        </select>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">Close</button>
    </div>
</div>

<div class="sheet-wrap" id="sheetWrap">
    @if(count($devices) === 0)
        <div class="alert alert-warning no-print">No devices were selected. Go back and select at least one device.</div>
    @endif
</div>

@if(count($devices) > 0)
@php
    $devicesJson = json_encode($devices->map(function ($d) {
        $brand = $d->model ? $d->model->brand : null;
        $emp   = $d->currentEmployee;
        return [
            'qr'        => route('devices.qr', $d),
            'brand'     => $brand ? $brand->name : null,
            'asset_tag' => $d->asset_tag,
            'serial'    => $d->serial_number,
            'imei'      => $d->imei1,
            'emp_name'  => $emp ? $emp->name : null,
            'emp_code'  => $emp ? $emp->employee_code : null,
        ];
    }));
@endphp
<script type="application/json" id="devices-data">{!! $devicesJson !!}</script>
<script>
const DEVICES = JSON.parse(document.getElementById('devices-data').textContent);

function labelHtml(d) {
    if (!d) return '<div class="label empty"></div>';
    return `
        <div class="label">
            <div class="brand">${d.brand ?? ''}</div>
            <img src="${d.qr}" alt="QR — ${d.asset_tag}">
            <div class="asset-tag">${d.asset_tag}</div>
            <div class="serial">${d.serial ?? ''}</div>
            ${d.imei ? `<div class="imei">IMEI: ${d.imei}</div>` : ''}
            ${d.emp_name ? `<div class="employee">${d.emp_name} <span class="code">(${d.emp_code ?? ''})</span></div>` : ''}
        </div>`;
}

function renderSheets() {
    const [cols, rows] = document.getElementById('layoutSelect').value.split('x').map(Number);
    const perSheet = cols * rows;
    const wrap = document.getElementById('sheetWrap');
    wrap.innerHTML = '';

    for (let i = 0; i < DEVICES.length; i += perSheet) {
        const chunk = DEVICES.slice(i, i + perSheet);
        const sheet = document.createElement('div');
        sheet.className = 'sheet';
        sheet.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        sheet.style.gridTemplateRows = `repeat(${rows}, 1fr)`;
        let html = '';
        for (let s = 0; s < perSheet; s++) html += labelHtml(chunk[s]);
        sheet.innerHTML = html;
        wrap.appendChild(sheet);
    }
}

document.getElementById('layoutSelect').addEventListener('change', renderSheets);
renderSheets();
</script>
@endif

</body>
</html>

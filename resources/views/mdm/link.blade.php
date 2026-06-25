@extends('layouts.main')
@section('title','Link Devices to Employees')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mdm.index') }}">MDM</a></li>
    <li class="breadcrumb-item active">Link Employee</li>
@endsection

@push('styles')
<style>
    .emp-card  { cursor:pointer; border:2px solid transparent; border-radius:8px; transition:.15s; padding:.5rem .75rem; }
    .emp-card:hover   { border-color:#0d6efd55; background:#f4f8ff; }
    .emp-card.selected{ border-color:#0d6efd; background:#e8f1ff; }
    .emp-list  { max-height:380px; overflow-y:auto; scrollbar-width:thin; }
    .grp-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.08em; color:#6c757d;
                 padding:.4rem .5rem .2rem; font-weight:600; background:#f8f9fa;
                 position:sticky; top:0; z-index:1; border-bottom:1px solid #dee2e6; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success border-0 alert-dismissible mb-4">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ── Stats ────────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card border-0 shadow-sm p-3 text-center">
            <div class="fs-3 fw-bold">{{ $stats['total'] }}</div>
            <div class="text-muted small">Total Devices</div>
        </div>
    </div>
    <div class="col-4">
        <a href="{{ request()->fullUrlWithQuery(['filter'=>'linked','page'=>null]) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm p-3 text-center h-100">
                <div class="fs-3 fw-bold text-success">{{ $stats['linked'] }}</div>
                <div class="text-muted small">Linked</div>
            </div>
        </a>
    </div>
    <div class="col-4">
        <a href="{{ request()->fullUrlWithQuery(['filter'=>'unlinked','page'=>null]) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm p-3 text-center h-100">
                <div class="fs-3 fw-bold text-warning">{{ $stats['unlinked'] }}</div>
                <div class="text-muted small">Unlinked</div>
            </div>
        </a>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            {{-- Tab buttons --}}
            <div class="col-12 mb-1">
                <div class="btn-group btn-group-sm">
                    <a href="{{ request()->fullUrlWithQuery(['filter'=>'all','page'=>null]) }}"
                       class="btn {{ $filter==='all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        All
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['filter'=>'unlinked','page'=>null]) }}"
                       class="btn {{ $filter==='unlinked' ? 'btn-warning' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-x me-1"></i>Unlinked
                        @if($stats['unlinked'] > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $stats['unlinked'] }}</span>
                        @endif
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['filter'=>'linked','page'=>null]) }}"
                       class="btn {{ $filter==='linked' ? 'btn-success' : 'btn-outline-secondary' }}">
                        <i class="bi bi-person-check me-1"></i>Linked
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Search device #, IMEI, serial, model…"
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <select name="group" class="form-select form-select-sm">
                    <option value="">All Groups</option>
                    @foreach($groups as $g)
                    <option value="{{ $g }}" {{ request('group')===$g ? 'selected':'' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="on"  {{ request('status')==='on'  ? 'selected':'' }}>Online</option>
                    <option value="off" {{ request('status')==='off' ? 'selected':'' }}>Offline</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="client" class="form-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client')==$c->id ? 'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('mdm.link') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

{{-- ── Device Table ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <div class="small text-muted">
            @if($devices->total())
            {{ $devices->firstItem() }}–{{ $devices->lastItem() }} of {{ $devices->total() }} devices
            @else
            No devices found
            @endif
        </div>
        <a href="{{ route('mdm.devices') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-phone me-1"></i>Device List
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small align-middle">
            <thead class="table-light">
                <tr>
                    <th>Device #</th>
                    <th>Model</th>
                    <th>IMEI / Serial</th>
                    <th class="text-center">Status</th>
                    <th>Current Employee</th>
                    <th>Client</th>
                    <th>Group</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $d)
                <tr>
                    <td>
                        <a href="{{ route('mdm.show', $d) }}" class="fw-semibold font-monospace text-decoration-none text-dark">
                            {{ $d->mdm_number }}
                        </a>
                    </td>
                    <td class="text-muted">{{ $d->model ?? '—' }}</td>
                    <td class="font-monospace text-muted" style="font-size:.7rem">
                        @if($d->imei)<div>{{ $d->imei }}</div>@endif
                        @if($d->serial_number)<div>{{ $d->serial_number }}</div>@endif
                        @if(!$d->imei && !$d->serial_number)—@endif
                    </td>
                    <td class="text-center">
                        @if($d->isOnline())
                            <span class="badge bg-success-subtle text-success border" style="font-size:.65rem">Online</span>
                        @elseif($d->device_status==='off')
                            <span class="badge bg-danger-subtle text-danger border" style="font-size:.65rem">Offline</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem">—</span>
                        @endif
                    </td>
                    <td>
                        @if($d->employee)
                            <div class="fw-semibold">{{ $d->employee->name }}</div>
                            <div class="text-muted" style="font-size:.7rem">
                                {{ $d->employee->employee_code }} &middot; {{ $d->employee->designation }}
                            </div>
                        @else
                            <span class="badge bg-warning-subtle text-warning border">
                                <i class="bi bi-person-x me-1"></i>Not Linked
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $d->employee?->client?->name ?? '—' }}</td>
                    <td class="text-muted small">{{ $d->mdm_group ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button"
                                    class="btn btn-sm btn-primary py-0 px-2"
                                    style="font-size:.72rem"
                                    onclick="openLinkModal({{ $d->id }}, '{{ addslashes($d->mdm_number) }}', '{{ addslashes($d->model ?? '') }}', {{ $d->employee_id ?? 'null' }})">
                                <i class="bi bi-link-45deg me-1"></i>
                                {{ $d->employee ? 'Change' : 'Link' }}
                            </button>
                            @if($d->employee)
                            <form method="POST" action="{{ route('mdm.link.save', $d) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="employee_id" value="">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:.72rem"
                                        onclick="return confirm('Remove link from {{ addslashes($d->mdm_number) }}?')">
                                    <i class="bi bi-x"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-link-slash fs-3 d-block mb-2 opacity-20"></i>
                        No devices found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($devices->hasPages())
    <div class="card-footer bg-white">{{ $devices->links() }}</div>
    @endif
</div>

{{-- ── Employee Link Modal ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="linkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-link-45deg me-2"></i>
                    Link <span id="modalDevNum" class="font-monospace text-primary"></span> to Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-muted small mb-3" id="modalDevInfo"></div>

                {{-- Search box --}}
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="empSearchInput" class="form-control"
                           placeholder="Search by name, code, designation, city…"
                           oninput="filterModalEmployees(this.value)">
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('empSearchInput').value='';filterModalEmployees('')">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                {{-- Employee list --}}
                <div class="emp-list border rounded" id="empListContainer">
                    @foreach($employees as $clientName => $empGroup)
                    <div class="grp-label" data-group-label>{{ $clientName }}</div>
                    @foreach($empGroup as $emp)
                    <div class="emp-card"
                         data-id="{{ $emp->id }}"
                         data-name="{{ $emp->name }}"
                         data-sub="{{ $emp->employee_code }} · {{ $emp->designation }}"
                         data-search="{{ strtolower($emp->name.' '.$emp->employee_code.' '.($emp->designation??'').' '.($emp->city??'').' '.$clientName) }}"
                         onclick="selectModalEmployee(this)">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold small">{{ $emp->name }}</div>
                                <div class="text-muted" style="font-size:.72rem">
                                    {{ $emp->employee_code }} &middot; {{ $emp->designation }}
                                    @if($emp->city) &middot; {{ $emp->city }} @endif
                                </div>
                            </div>
                            <div class="text-muted small">{{ $clientName }}</div>
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>

                {{-- Selected preview --}}
                <div id="selectedEmpPreview" class="mt-3 p-3 rounded border-start border-primary border-3 bg-primary-subtle d-none">
                    <div class="small text-primary fw-semibold mb-1">Selected:</div>
                    <div class="fw-bold" id="selEmpName"></div>
                    <div class="text-muted small" id="selEmpSub"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="linkModalForm">
                    @csrf
                    <input type="hidden" name="employee_id" id="linkEmpIdInput">
                    <button type="submit" class="btn btn-primary" id="linkSaveBtn" disabled>
                        <i class="bi bi-save me-1"></i>Save Link
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const linkModal    = new bootstrap.Modal(document.getElementById('linkModal'));
const modalDevNum  = document.getElementById('modalDevNum');
const modalDevInfo = document.getElementById('modalDevInfo');
const linkModalForm= document.getElementById('linkModalForm');
const linkEmpId    = document.getElementById('linkEmpIdInput');
const linkSaveBtn  = document.getElementById('linkSaveBtn');
const selPreview   = document.getElementById('selectedEmpPreview');
const selName      = document.getElementById('selEmpName');
const selSub       = document.getElementById('selEmpSub');
let currentEmpId   = null;

function openLinkModal(deviceId, deviceNum, deviceModel, currentEmpIdVal) {
    modalDevNum.textContent  = deviceNum;
    modalDevInfo.textContent = deviceModel || '';
    linkModalForm.action     = `/mdm/link/${deviceId}`;
    currentEmpId             = currentEmpIdVal;

    // Reset state
    linkEmpId.value = '';
    linkSaveBtn.disabled = true;
    selPreview.classList.add('d-none');
    document.getElementById('empSearchInput').value = '';
    filterModalEmployees('');

    // Mark current employee
    document.querySelectorAll('.emp-card').forEach(c => {
        c.classList.toggle('selected', c.dataset.id == currentEmpIdVal);
    });
    if (currentEmpIdVal) {
        const cur = document.querySelector(`.emp-card[data-id="${currentEmpIdVal}"]`);
        if (cur) {
            selName.textContent    = cur.dataset.name;
            selSub.textContent     = cur.dataset.sub;
            selPreview.classList.remove('d-none');
            linkEmpId.value        = currentEmpIdVal;
            linkSaveBtn.disabled   = false;
        }
    }

    linkModal.show();
}

function selectModalEmployee(card) {
    document.querySelectorAll('.emp-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    linkEmpId.value      = card.dataset.id;
    selName.textContent  = card.dataset.name;
    selSub.textContent   = card.dataset.sub;
    selPreview.classList.remove('d-none');
    linkSaveBtn.disabled = false;
}

function filterModalEmployees(q) {
    q = q.toLowerCase().trim();
    let shown = 0;
    document.querySelectorAll('#empListContainer .emp-card').forEach(card => {
        const match = !q || card.dataset.search.includes(q);
        card.style.display = match ? '' : 'none';
        if (match) shown++;
    });
    // Hide group labels if all members hidden
    document.querySelectorAll('#empListContainer .grp-label').forEach(lbl => {
        let sib = lbl.nextElementSibling;
        let any = false;
        while (sib && !sib.classList.contains('grp-label')) {
            if (sib.style.display !== 'none') any = true;
            sib = sib.nextElementSibling;
        }
        lbl.style.display = any ? '' : 'none';
    });
}
</script>
@endpush

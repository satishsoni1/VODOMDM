@extends('layouts.main')
@section('title','Employee — '.$employee->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $employee->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-person me-2"></i>{{ $employee->name }}
        <span class="badge bg-secondary ms-2">{{ $employee->employee_code }}</span>
        @if($employee->username)<span class="badge bg-light text-dark border ms-1 fw-normal">{{ $employee->username }}</span>@endif
        <span class="badge bg-{{ match($employee->status){ 'active'=>'success','resigned'=>'warning text-dark','terminated'=>'danger','on_leave'=>'info text-dark',default=>'secondary' } }} ms-1">
            {{ ucwords(str_replace('_',' ',$employee->status)) }}
        </span>
        @if($employee->is_manager)<span class="badge bg-purple ms-1" style="background:#6f42c1">Manager</span>@endif
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('employees.edit',$employee) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
        <a href="{{ route('recovery.create', ['employee_id'=>$employee->id]) }}" class="btn btn-sm btn-danger"><i class="bi bi-arrow-return-left"></i> Initiate Recovery</a>
    </div>
</div>

{{-- Tab Nav --}}
<ul class="nav nav-tabs mb-3 flex-wrap">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#sv-basic">Overview</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-personal">Personal</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-contact">Contact & Address</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-edu">Education</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-org">Org & Employment</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-hr">HR & Work Setup</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-bank">Banking & PF</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-emergency">Emergency & Nominee</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#sv-devices">Devices & Activity</a></li>
</ul>

<div class="tab-content">

{{-- ═══ OVERVIEW ═══ --}}
<div class="tab-pane fade show active" id="sv-basic">
<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100"><div class="card-header fw-semibold">Basic Info</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">Employee Code</td><td class="font-monospace fw-bold">{{ $employee->employee_code }}</td></tr>
            <tr><td class="text-muted ps-3">Username</td><td>{{ $employee->username ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">First Name</td><td>{{ $employee->first_name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Last Name</td><td>{{ $employee->last_name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Full Name</td><td>{{ $employee->name }}</td></tr>
            <tr><td class="text-muted ps-3">Designation</td><td>{{ $employee->designation ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Plant Location</td><td>{{ $employee->plant_location ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Business Area</td><td>{{ $employee->business_area ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Is Manager</td><td>{{ $employee->is_manager ? 'Yes' : 'No' }}</td></tr>
            <tr><td class="text-muted ps-3">Default Shift</td><td>{{ $employee->default_shift ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Grade / Level</td><td>{{ implode(' / ', array_filter([$employee->grade, $employee->level])) ?: '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Position ID</td><td class="font-monospace">{{ $employee->position_id ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card h-100"><div class="card-header fw-semibold">Reporting & Manager</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">Manager</td><td>{{ $employee->manager_name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Mgr Username</td><td>{{ $employee->manager_username ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Mgr Employee ID</td><td class="font-monospace">{{ $employee->manager_emp_id ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Mgr Phone</td><td class="font-monospace">{{ $employee->manager_phone ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Mgr Email</td><td>{{ $employee->manager_email ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Dotted Line Mgr</td><td>{{ $employee->dotted_line_manager ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Client</td><td>{{ $employee->client?->name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Project</td><td>{{ $employee->project?->name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Location</td><td>{{ $employee->location?->name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Region / HQ / ABM</td><td>{{ implode(' / ', array_filter([$employee->region, $employee->hq, $employee->abm])) ?: '—' }}</td></tr>
        </table>
        </div></div>
    </div>
</div>
</div>

{{-- ═══ PERSONAL ═══ --}}
<div class="tab-pane fade" id="sv-personal">
<div class="card"><div class="card-body p-0">
<table class="table table-sm table-borderless mb-0">
    <tr><td class="text-muted ps-3 w-30">Gender</td><td>{{ ucfirst($employee->gender ?? '—') }}</td><td class="text-muted">Blood Group</td><td>{{ $employee->blood_group ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Date of Birth</td><td>{{ $employee->date_of_birth?->format('d M Y') ?? '—' }}</td><td class="text-muted">Place of Birth</td><td>{{ $employee->place_of_birth ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Marital Status</td><td>{{ ucfirst($employee->marital_status ?? '—') }}</td><td class="text-muted">Date of Marriage</td><td>{{ $employee->date_of_marriage?->format('d M Y') ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Father's Name</td><td>{{ $employee->fathers_name ?? '—' }}</td><td class="text-muted">Mother Tongue</td><td>{{ $employee->mother_tongue ?? '—' }}</td></tr>
</table>
@if($employee->about_me)
<div class="px-3 pb-2"><strong class="small text-muted text-uppercase">About Me</strong><p class="mt-1">{{ $employee->about_me }}</p></div>
@endif
@if($employee->views_on_organization)
<div class="px-3 pb-2"><strong class="small text-muted text-uppercase">Views on Organization</strong><p class="mt-1">{{ $employee->views_on_organization }}</p></div>
@endif
</div></div>

<div class="card mt-3"><div class="card-header fw-semibold">Identity Documents</div>
<div class="card-body p-0">
<table class="table table-sm table-borderless mb-0">
    <tr><td class="text-muted ps-3 w-30">Aadhar Number</td><td class="font-monospace">{{ $employee->aadhar_number ?? '—' }}</td><td class="text-muted">PAN Number</td><td class="font-monospace">{{ $employee->pan_number ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Passport Number</td><td class="font-monospace">{{ $employee->passport_number ?? '—' }}</td><td class="text-muted">Driving Licence</td><td class="font-monospace">{{ $employee->driving_licence_number ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Biometric ID</td><td class="font-monospace">{{ $employee->biometric_id ?? '—' }}</td><td></td><td></td></tr>
</table>
</div></div>

@php $docs = ['doc_aadhar_card'=>'Aadhar Card','doc_pan_card'=>'PAN Card','doc_dob_proof'=>'DOB Proof','doc_graduation'=>'Graduation Cert','doc_post_graduation'=>'PG Cert','doc_relieving_letter'=>'Relieving Letter','doc_experience_letter'=>'Experience Letter','doc_other'=>'Other Docs']; @endphp
@if(collect($docs)->keys()->filter(fn($k)=>$employee->$k)->isNotEmpty())
<div class="card mt-3"><div class="card-header fw-semibold">Document References</div>
<div class="card-body p-0">
<table class="table table-sm table-borderless mb-0">
    @foreach($docs as $field=>$label)
    @if($employee->$field)
    <tr><td class="text-muted ps-3 w-30">{{ $label }}</td><td class="font-monospace small">{{ $employee->$field }}</td></tr>
    @endif
    @endforeach
</table>
</div></div>
@endif
</div>

{{-- ═══ CONTACT & ADDRESS ═══ --}}
<div class="tab-pane fade" id="sv-contact">
<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Official Contact</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-40">Official Email</td><td>{{ $employee->email ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Official Mobile</td><td class="font-monospace">{{ $employee->phone ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Alternate Phone</td><td class="font-monospace">{{ $employee->alternate_phone ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Personal Contact</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-40">Personal Email</td><td>{{ $employee->personal_email ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Personal Mobile</td><td class="font-monospace">{{ $employee->personal_mobile ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-12">
        <div class="card"><div class="card-header fw-semibold">Address</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-20">Current</td><td colspan="3">{{ $employee->address ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Permanent</td><td colspan="3">{{ $employee->permanent_address ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">City / Town</td><td>{{ $employee->city ?? '—' }}</td><td class="text-muted">State</td><td>{{ $employee->state ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Country</td><td>{{ $employee->country ?? '—' }}</td><td class="text-muted">Pin Code</td><td class="font-monospace">{{ $employee->pin_code ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
</div>
</div>

{{-- ═══ EDUCATION ═══ --}}
<div class="tab-pane fade" id="sv-edu">
<div class="card"><div class="card-body p-0">
<table class="table table-sm table-borderless mb-0">
    <tr><td class="text-muted ps-3 w-30">Graduation</td><td>{{ $employee->graduation ?? '—' }}</td><td class="text-muted">Year</td><td>{{ $employee->year_of_passing_grad ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Post Graduation</td><td>{{ $employee->post_graduation ?? '—' }}</td><td class="text-muted">Year</td><td>{{ $employee->year_of_passing_post_grad ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">Other Qualification</td><td>{{ $employee->other_qualification ?? '—' }}</td><td class="text-muted">Year</td><td>{{ $employee->year_of_passing_other ?? '—' }}</td></tr>
</table>
@if($employee->certifications)
<div class="px-3 pb-2"><strong class="small text-muted text-uppercase">Certifications</strong><p class="mt-1">{{ $employee->certifications }}</p></div>
@endif
@if($employee->co_curricular_activities)
<div class="px-3 pb-2"><strong class="small text-muted text-uppercase">Co-Curricular Activities / Hobbies</strong><p class="mt-1">{{ $employee->co_curricular_activities }}</p></div>
@endif
</div></div>
</div>

{{-- ═══ ORG & EMPLOYMENT ═══ --}}
<div class="tab-pane fade" id="sv-org">
<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Employment Dates</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">Date of Joining</td><td>{{ $employee->joining_date?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Date of Group Joining</td><td>{{ $employee->date_of_group_joining?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Confirmation Date</td><td>{{ $employee->confirmation_date?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Last Working Date</td><td class="{{ $employee->exit_date?'text-danger':'' }}">{{ $employee->exit_date?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Previous Experience</td><td>{{ $employee->previous_experience ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Company & Position</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">Company</td><td>{{ $employee->company_name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Company State</td><td>{{ $employee->company_state ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Field Work Location</td><td>{{ $employee->field_work_location ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Function / SBU</td><td>{{ $employee->function_sbu ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Vertical</td><td>{{ $employee->vertical ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Division</td><td>{{ $employee->division ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Department</td><td>{{ $employee->department ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Sub Department</td><td>{{ $employee->sub_department ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Posting Type</td><td>{{ $employee->posting_type ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-12">
        <div class="card"><div class="card-header fw-semibold">Employment Classification</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td class="text-muted ps-3 w-20">Category</td><td>{{ $employee->employee_category ?? '—' }}</td>
                <td class="text-muted">Group</td><td>{{ $employee->employee_group ?? '—' }}</td>
                <td class="text-muted">Payroll Group</td><td>{{ $employee->employee_payroll_group ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-muted ps-3">Empl. Status</td><td>{{ $employee->employment_status ?? '—' }}</td>
                <td class="text-muted">Notice Period</td><td>{{ $employee->notice_period ?? '—' }}</td>
                <td class="text-muted">TAG</td><td>{{ $employee->tag ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-muted ps-3">Cost Centre</td><td>{{ $employee->cost_centre ?? '—' }}</td>
                <td class="text-muted">Cost Centre Name</td><td>{{ $employee->cost_centre_name ?? '—' }}</td>
                <td class="text-muted">Business Head SBU</td><td>{{ $employee->business_head_sbu ?? '—' }}</td>
            </tr>
            <tr>
                <td class="text-muted ps-3">Direct Reports Flag</td><td>{{ $employee->direct_reports_flag ? 'Yes' : 'No' }}</td>
                <td></td><td></td><td></td><td></td>
            </tr>
        </table>
        </div></div>
    </div>
</div>
</div>

{{-- ═══ HR & WORK SETUP ═══ --}}
<div class="tab-pane fade" id="sv-hr">
<div class="card"><div class="card-body p-0">
<table class="table table-sm table-borderless mb-0">
    <tr><td class="text-muted ps-3 w-20">HRBP</td><td>{{ $employee->hrbp ?? '—' }}</td><td class="text-muted">Payroll Area</td><td>{{ $employee->payroll_area ?? '—' }}</td></tr>
    <tr><td class="text-muted ps-3">HRSS</td><td>{{ $employee->hrss ?? '—' }}</td><td class="text-muted">HOD</td><td>{{ $employee->hod ?? '—' }}</td></tr>
</table>
@if($employee->notes)
<div class="px-3 pb-2 border-top mt-2"><strong class="small text-muted text-uppercase">Notes</strong><p class="mt-1">{{ $employee->notes }}</p></div>
@endif
</div></div>
</div>

{{-- ═══ BANKING & PF ═══ --}}
<div class="tab-pane fade" id="sv-bank">
<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Bank Details</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">Bank Name</td><td>{{ $employee->bank_name ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Account Owner</td><td>{{ $employee->bank_account_owner ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Account No</td><td class="font-monospace">{{ $employee->bank_account_no ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Bank ID</td><td class="font-monospace">{{ $employee->bank_id ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">IFSC Code</td><td class="font-monospace">{{ $employee->ifsc_code ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Payment Method</td><td>{{ $employee->payment_method ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Currency</td><td>{{ $employee->currency ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">PF & Statutory</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">Prev PF Account</td><td class="font-monospace">{{ $employee->previous_pf_account ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">PF Account No</td><td class="font-monospace">{{ $employee->pf_account_number ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">UAN Number</td><td class="font-monospace">{{ $employee->uan_number ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">PF Category</td><td>{{ $employee->pf_category ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">ESIC Account No</td><td class="font-monospace">{{ $employee->esic_account_number ?? '—' }}</td></tr>
        </table>
        </div></div>
        <div class="card mt-3"><div class="card-header fw-semibold">Insurance / Superannuation</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-45">SA Policy No</td><td class="font-monospace">{{ $employee->sa_policy_number ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">SA Policy ID</td><td class="font-monospace">{{ $employee->sa_policy_id ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">SA Annuity No</td><td class="font-monospace">{{ $employee->sa_annuity_number ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">GR Policy No</td><td class="font-monospace">{{ $employee->gr_policy_number ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">GR Policy ID</td><td class="font-monospace">{{ $employee->gr_policy_id ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
</div>
</div>

{{-- ═══ EMERGENCY & NOMINEE ═══ --}}
<div class="tab-pane fade" id="sv-emergency">
<div class="row g-3">
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Emergency Contact</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-40">Contact Person</td><td>{{ $employee->emergency_contact_person ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Relationship</td><td>{{ $employee->emergency_contact_relationship ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Mobile No</td><td class="font-monospace">{{ $employee->emergency_contact_mobile ?? '—' }}</td></tr>
        </table>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-header fw-semibold">Nominee (PF / Insurance)</div>
        <div class="card-body p-0">
        <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted ps-3 w-40">Relation</td><td>{{ $employee->nominee_relation ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Date of Birth</td><td>{{ $employee->nominee_birth_date?->format('d M Y') ?? '—' }}</td></tr>
            <tr><td class="text-muted ps-3">Gender</td><td>{{ ucfirst($employee->nominee_gender ?? '—') }}</td></tr>
        </table>
        </div></div>
    </div>
</div>
</div>

{{-- ═══ DEVICES & ACTIVITY ═══ --}}
<div class="tab-pane fade" id="sv-devices">
<div class="card mb-3">
    <div class="card-header"><strong>Assigned Devices ({{ $employee->currentDevices->count() }})</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Asset Tag</th><th>Model</th><th>IMEI 1</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @forelse($employee->currentDevices as $dev)
            <tr>
                <td><a href="{{ route('devices.show',$dev) }}" class="font-monospace fw-bold">{{ $dev->asset_tag }}</a></td>
                <td>{{ $dev->model?->brand?->name }} {{ $dev->model?->model_name }}</td>
                <td class="font-monospace small">{{ $dev->imei1 ?? '—' }}</td>
                <td><span class="badge bg-primary">{{ str_replace('_',' ',$dev->lifecycle_status) }}</span></td>
                <td><a href="{{ route('devices.show',$dev) }}" class="btn btn-xs btn-outline-primary btn-sm py-0 px-1"><i class="bi bi-eye"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-2">No devices currently assigned.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($employee->recoveryCases->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><strong>Recovery Cases</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Case #</th><th>Device</th><th>Trigger</th><th>Status</th><th>Due Date</th></tr></thead>
        <tbody>
            @foreach($employee->recoveryCases as $rc)
            <tr>
                <td><a href="{{ route('recovery.show',$rc) }}" class="fw-bold">{{ $rc->case_number }}</a></td>
                <td class="font-monospace small">{{ $rc->device?->asset_tag }}</td>
                <td class="small">{{ ucfirst($rc->trigger_reason) }}</td>
                <td><span class="badge bg-{{ $rc->status==='recovered'?'success':($rc->status==='escalated'?'danger':'warning text-dark') }}">{{ ucfirst($rc->status) }}</span></td>
                <td class="{{ $rc->recovery_due_date?->isPast() ? 'text-danger fw-bold' : '' }}">{{ $rc->recovery_due_date?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($employee->callLogs->isNotEmpty())
<div class="card">
    <div class="card-header"><strong>Recent Calls</strong></div>
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>Date & Time</th><th>Called By</th><th>Outcome</th><th>Next Follow-up</th></tr></thead>
        <tbody>
            @foreach($employee->callLogs as $call)
            <tr>
                <td class="small">{{ $call->call_datetime->format('d M Y H:i') }}</td>
                <td class="small">{{ $call->calledBy?->name }}</td>
                <td><span class="badge bg-{{ $call->outcome==='agreed_to_return'?'success':($call->outcome==='refused'?'danger':'secondary') }}">{{ ucwords(str_replace('_',' ',$call->outcome)) }}</span></td>
                <td class="small">{{ $call->next_follow_up_date?->format('d M Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
</div>

</div>{{-- end tab-content --}}
@endsection

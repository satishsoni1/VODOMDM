@extends('layouts.main')
@section('title','Add Employee')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Add Employee</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>New Employee</h5>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" action="{{ route('employees.store') }}" id="empForm">
@csrf

{{-- Tab Nav --}}
<ul class="nav nav-tabs mb-3 flex-wrap" id="empTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-basic">Basic Info</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-personal">Personal</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-identity">Identity</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-contact">Contact & Address</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-education">Education</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-org">Org & Employment</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-hr">HR & Work Setup</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-bank">Banking & PF</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-emergency">Emergency & Nominee</a></li>
</ul>

<div class="tab-content">

{{-- ═══ BASIC INFO ═══ --}}
<div class="tab-pane fade show active" id="tab-basic">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Employee Code *</label>
        <input class="form-control @error('employee_code') is-invalid @enderror" name="employee_code" value="{{ old('employee_code') }}" required>
        @error('employee_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" value="{{ old('username') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">First Name</label>
        <input class="form-control" name="first_name" value="{{ old('first_name') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Last Name</label>
        <input class="form-control" name="last_name" value="{{ old('last_name') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Full Name</label>
        <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Designation</label>
        <input class="form-control" name="designation" value="{{ old('designation') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status *</label>
        <select class="form-select @error('status') is-invalid @enderror" name="status" required>
            @foreach(['active'=>'Active','inactive'=>'Inactive','resigned'=>'Resigned','terminated'=>'Terminated','on_leave'=>'On Leave'] as $val=>$label)
            <option value="{{ $val }}" {{ old('status','active')===$val?'selected':'' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Plant Location</label>
        <input class="form-control" name="plant_location" value="{{ old('plant_location') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Business Area</label>
        <input class="form-control" name="business_area" value="{{ old('business_area') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Position ID</label>
        <input class="form-control" name="position_id" value="{{ old('position_id') }}">
    </div>
    <div class="col-md-2 d-flex align-items-end pb-1">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_manager" value="1" id="isManager" {{ old('is_manager') ? 'checked' : '' }}>
            <label class="form-check-label" for="isManager">Is Manager</label>
        </div>
    </div>
    <div class="col-md-3">
        <label class="form-label">Default Shift</label>
        <input class="form-control" name="default_shift" value="{{ old('default_shift') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Grade</label>
        <input class="form-control" name="grade" value="{{ old('grade') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Level</label>
        <input class="form-control" name="level" value="{{ old('level') }}">
    </div>
</div>

<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Client & Project</h6>
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Client</label>
        <select class="form-select" name="client_id" id="clientSelect" onchange="loadProjects(this.value)">
            <option value="">— Select Client —</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" {{ (old('client_id') ?? request('client_id'))==$c->id?'selected':'' }}>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Project</label>
        <select class="form-select" name="client_project_id" id="projectSelect">
            <option value="">— Select Project —</option>
            @foreach($clients as $c)
                @foreach($c->projects as $proj)
                <option value="{{ $proj->id }}" data-client="{{ $c->id }}" {{ old('client_project_id')==$proj->id?'selected':'' }}>{{ $proj->name }}</option>
                @endforeach
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Location</label>
        <select class="form-select" name="location_id">
            <option value="">— Select —</option>
            @foreach($locations as $loc)
            <option value="{{ $loc->id }}" {{ old('location_id')==$loc->id?'selected':'' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Manager & Reporting</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Manager Name</label><input class="form-control" name="manager_name" value="{{ old('manager_name') }}"></div>
    <div class="col-md-4"><label class="form-label">Manager Username</label><input class="form-control" name="manager_username" value="{{ old('manager_username') }}"></div>
    <div class="col-md-4"><label class="form-label">Manager Employee ID</label><input class="form-control" name="manager_emp_id" value="{{ old('manager_emp_id') }}"></div>
    <div class="col-md-4"><label class="form-label">Manager Phone</label><input class="form-control" name="manager_phone" value="{{ old('manager_phone') }}"></div>
    <div class="col-md-4"><label class="form-label">Manager Email</label><input class="form-control" type="email" name="manager_email" value="{{ old('manager_email') }}"></div>
    <div class="col-md-4"><label class="form-label">Dotted Line Reporting Manager</label><input class="form-control" name="dotted_line_manager" value="{{ old('dotted_line_manager') }}"></div>
    <div class="col-md-3"><label class="form-label">Region</label><input class="form-control" name="region" value="{{ old('region') }}"></div>
    <div class="col-md-3"><label class="form-label">HQ</label><input class="form-control" name="hq" value="{{ old('hq') }}"></div>
    <div class="col-md-3"><label class="form-label">ABM</label><input class="form-control" name="abm" value="{{ old('abm') }}"></div>
</div>
</div></div>
</div>

{{-- ═══ PERSONAL ═══ --}}
<div class="tab-pane fade" id="tab-personal">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select class="form-select" name="gender">
            <option value="">— Select —</option>
            @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer Not to Say'] as $v=>$l)
            <option value="{{ $v }}" {{ old('gender')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Blood Group</label>
        <select class="form-select" name="blood_group">
            <option value="">— Select —</option>
            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
            <option value="{{ $bg }}" {{ old('blood_group')===$bg?'selected':'' }}>{{ $bg }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Place of Birth</label>
        <input class="form-control" name="place_of_birth" value="{{ old('place_of_birth') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Marital Status</label>
        <select class="form-select" name="marital_status">
            <option value="">— Select —</option>
            @foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed','separated'=>'Separated'] as $v=>$l)
            <option value="{{ $v }}" {{ old('marital_status')===$v?'selected':'' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Date of Marriage</label>
        <input type="date" class="form-control" name="date_of_marriage" value="{{ old('date_of_marriage') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Father's Name</label>
        <input class="form-control" name="fathers_name" value="{{ old('fathers_name') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Mother Tongue</label>
        <input class="form-control" name="mother_tongue" value="{{ old('mother_tongue') }}">
    </div>
    <div class="col-12">
        <label class="form-label">About Me</label>
        <textarea class="form-control" name="about_me" rows="3">{{ old('about_me') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Your Views On Our Organization</label>
        <textarea class="form-control" name="views_on_organization" rows="3">{{ old('views_on_organization') }}</textarea>
    </div>
</div>
</div></div>
</div>

{{-- ═══ IDENTITY ═══ --}}
<div class="tab-pane fade" id="tab-identity">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Aadhar Number</label><input class="form-control" name="aadhar_number" maxlength="20" value="{{ old('aadhar_number') }}"></div>
    <div class="col-md-4"><label class="form-label">PAN Number</label><input class="form-control" name="pan_number" maxlength="20" value="{{ old('pan_number') }}"></div>
    <div class="col-md-4"><label class="form-label">Passport Number</label><input class="form-control" name="passport_number" maxlength="30" value="{{ old('passport_number') }}"></div>
    <div class="col-md-4"><label class="form-label">Driving Licence Number</label><input class="form-control" name="driving_licence_number" maxlength="30" value="{{ old('driving_licence_number') }}"></div>
    <div class="col-md-4"><label class="form-label">Biometric / Punch Card ID</label><input class="form-control" name="biometric_id" value="{{ old('biometric_id') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Document References</h6>
<p class="text-muted small">Enter document reference numbers or file paths.</p>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Aadhar Card Ref</label><input class="form-control" name="doc_aadhar_card" value="{{ old('doc_aadhar_card') }}"></div>
    <div class="col-md-4"><label class="form-label">PAN Card Ref</label><input class="form-control" name="doc_pan_card" value="{{ old('doc_pan_card') }}"></div>
    <div class="col-md-4"><label class="form-label">Date of Birth Proof Ref</label><input class="form-control" name="doc_dob_proof" value="{{ old('doc_dob_proof') }}"></div>
    <div class="col-md-4"><label class="form-label">Graduation Certificate Ref</label><input class="form-control" name="doc_graduation" value="{{ old('doc_graduation') }}"></div>
    <div class="col-md-4"><label class="form-label">Post Graduation Certificate Ref</label><input class="form-control" name="doc_post_graduation" value="{{ old('doc_post_graduation') }}"></div>
    <div class="col-md-4"><label class="form-label">Relieving Letter (Previous Co.) Ref</label><input class="form-control" name="doc_relieving_letter" value="{{ old('doc_relieving_letter') }}"></div>
    <div class="col-md-4"><label class="form-label">Experience Letter (Previous Co.) Ref</label><input class="form-control" name="doc_experience_letter" value="{{ old('doc_experience_letter') }}"></div>
    <div class="col-md-4"><label class="form-label">Other Documents Ref</label><input class="form-control" name="doc_other" value="{{ old('doc_other') }}"></div>
</div>
</div></div>
</div>

{{-- ═══ CONTACT & ADDRESS ═══ --}}
<div class="tab-pane fade" id="tab-contact">
<div class="card"><div class="card-body">
<h6 class="text-muted fw-bold text-uppercase small mb-3">Official Contact</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Official Email</label><input class="form-control" type="email" name="email" value="{{ old('email') }}"></div>
    <div class="col-md-4"><label class="form-label">Official Mobile No</label><input class="form-control" name="phone" value="{{ old('phone') }}"></div>
    <div class="col-md-4"><label class="form-label">Alternate Phone</label><input class="form-control" name="alternate_phone" value="{{ old('alternate_phone') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Personal Contact</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Personal Email</label><input class="form-control" type="email" name="personal_email" value="{{ old('personal_email') }}"></div>
    <div class="col-md-4"><label class="form-label">Personal Mobile No</label><input class="form-control" name="personal_mobile" value="{{ old('personal_mobile') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Current Address</h6>
<div class="row g-3">
    <div class="col-12"><label class="form-label">Current Address</label><textarea class="form-control" name="address" rows="2">{{ old('address') }}</textarea></div>
    <div class="col-md-3"><label class="form-label">City / Town</label><input class="form-control" name="city" value="{{ old('city') }}"></div>
    <div class="col-md-3"><label class="form-label">State</label><input class="form-control" name="state" value="{{ old('state') }}"></div>
    <div class="col-md-3"><label class="form-label">Country</label><input class="form-control" name="country" value="{{ old('country') }}"></div>
    <div class="col-md-3"><label class="form-label">Pin Code</label><input class="form-control" name="pin_code" value="{{ old('pin_code') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Permanent Address</h6>
<div class="row g-3">
    <div class="col-12"><label class="form-label">Permanent Address</label><textarea class="form-control" name="permanent_address" rows="2">{{ old('permanent_address') }}</textarea></div>
</div>
</div></div>
</div>

{{-- ═══ EDUCATION ═══ --}}
<div class="tab-pane fade" id="tab-education">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Graduation</label><input class="form-control" name="graduation" value="{{ old('graduation') }}"></div>
    <div class="col-md-3"><label class="form-label">Year of Passing</label><input class="form-control" type="number" name="year_of_passing_grad" min="1950" max="2099" value="{{ old('year_of_passing_grad') }}"></div>
    <div class="col-md-6"><label class="form-label">Post Graduation</label><input class="form-control" name="post_graduation" value="{{ old('post_graduation') }}"></div>
    <div class="col-md-3"><label class="form-label">Year of Passing</label><input class="form-control" type="number" name="year_of_passing_post_grad" min="1950" max="2099" value="{{ old('year_of_passing_post_grad') }}"></div>
    <div class="col-md-6"><label class="form-label">Other Qualification</label><input class="form-control" name="other_qualification" value="{{ old('other_qualification') }}"></div>
    <div class="col-md-3"><label class="form-label">Year of Passing</label><input class="form-control" type="number" name="year_of_passing_other" min="1950" max="2099" value="{{ old('year_of_passing_other') }}"></div>
    <div class="col-12"><label class="form-label">Certifications</label><textarea class="form-control" name="certifications" rows="3">{{ old('certifications') }}</textarea></div>
    <div class="col-12"><label class="form-label">Co-Curricular Activities / Hobbies</label><textarea class="form-control" name="co_curricular_activities" rows="3">{{ old('co_curricular_activities') }}</textarea></div>
</div>
</div></div>
</div>

{{-- ═══ ORG & EMPLOYMENT ═══ --}}
<div class="tab-pane fade" id="tab-org">
<div class="card"><div class="card-body">
<h6 class="text-muted fw-bold text-uppercase small mb-3">Employment Dates</h6>
<div class="row g-3">
    <div class="col-md-3"><label class="form-label">Date of Joining</label><input type="date" class="form-control" name="joining_date" value="{{ old('joining_date') }}"></div>
    <div class="col-md-3"><label class="form-label">Date of Group Joining</label><input type="date" class="form-control" name="date_of_group_joining" value="{{ old('date_of_group_joining') }}"></div>
    <div class="col-md-3"><label class="form-label">Confirmation Date</label><input type="date" class="form-control" name="confirmation_date" value="{{ old('confirmation_date') }}"></div>
    <div class="col-md-3"><label class="form-label">Last Working Date</label><input type="date" class="form-control" name="exit_date" value="{{ old('exit_date') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Company & Position</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Company Name</label><input class="form-control" name="company_name" value="{{ old('company_name') }}"></div>
    <div class="col-md-4"><label class="form-label">Company State</label><input class="form-control" name="company_state" value="{{ old('company_state') }}"></div>
    <div class="col-md-4"><label class="form-label">Field Work Location</label><input class="form-control" name="field_work_location" value="{{ old('field_work_location') }}"></div>
    <div class="col-md-4"><label class="form-label">Function / SBU</label><input class="form-control" name="function_sbu" value="{{ old('function_sbu') }}"></div>
    <div class="col-md-4"><label class="form-label">Vertical</label><input class="form-control" name="vertical" value="{{ old('vertical') }}"></div>
    <div class="col-md-4"><label class="form-label">Division</label><input class="form-control" name="division" value="{{ old('division') }}"></div>
    <div class="col-md-4"><label class="form-label">Department</label><input class="form-control" name="department" value="{{ old('department') }}"></div>
    <div class="col-md-4"><label class="form-label">Sub Department</label><input class="form-control" name="sub_department" value="{{ old('sub_department') }}"></div>
    <div class="col-md-4"><label class="form-label">Posting Type</label>
        <select class="form-select" name="posting_type">
            <option value="">— Select —</option>
            @foreach(['Corporate','Plant','Field Staff'] as $pt)
            <option value="{{ $pt }}" {{ old('posting_type')===$pt?'selected':'' }}>{{ $pt }}</option>
            @endforeach
        </select>
    </div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Employment Classification</h6>
<div class="row g-3">
    <div class="col-md-3"><label class="form-label">Employee Category</label><input class="form-control" name="employee_category" value="{{ old('employee_category') }}"></div>
    <div class="col-md-3"><label class="form-label">Employee Group</label><input class="form-control" name="employee_group" value="{{ old('employee_group') }}"></div>
    <div class="col-md-3"><label class="form-label">Employee Payroll Group</label><input class="form-control" name="employee_payroll_group" value="{{ old('employee_payroll_group') }}"></div>
    <div class="col-md-3"><label class="form-label">Employment Status</label><input class="form-control" name="employment_status" value="{{ old('employment_status') }}"></div>
    <div class="col-md-3"><label class="form-label">Notice Period</label><input class="form-control" name="notice_period" value="{{ old('notice_period') }}"></div>
    <div class="col-md-3"><label class="form-label">Cost Centre</label><input class="form-control" name="cost_centre" value="{{ old('cost_centre') }}"></div>
    <div class="col-md-3"><label class="form-label">Cost Centre Name</label><input class="form-control" name="cost_centre_name" value="{{ old('cost_centre_name') }}"></div>
    <div class="col-md-3"><label class="form-label">Business Head / SBU</label><input class="form-control" name="business_head_sbu" value="{{ old('business_head_sbu') }}"></div>
    <div class="col-md-3"><label class="form-label">TAG</label><input class="form-control" name="tag" value="{{ old('tag') }}"></div>
    <div class="col-md-3"><label class="form-label">Previous Experience</label><input class="form-control" name="previous_experience" value="{{ old('previous_experience') }}"></div>
    <div class="col-md-3 d-flex align-items-end pb-1">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="direct_reports_flag" value="1" id="drFlag" {{ old('direct_reports_flag') ? 'checked' : '' }}>
            <label class="form-check-label" for="drFlag">Direct Reports Location Flag</label>
        </div>
    </div>
</div>
</div></div>
</div>

{{-- ═══ HR & WORK SETUP ═══ --}}
<div class="tab-pane fade" id="tab-hr">
<div class="card"><div class="card-body">
<div class="row g-3">
    <div class="col-md-3"><label class="form-label">HRBP</label><input class="form-control" name="hrbp" value="{{ old('hrbp') }}"></div>
    <div class="col-md-3"><label class="form-label">Payroll Area</label><input class="form-control" name="payroll_area" value="{{ old('payroll_area') }}"></div>
    <div class="col-md-3"><label class="form-label">HRSS</label><input class="form-control" name="hrss" value="{{ old('hrss') }}"></div>
    <div class="col-md-3"><label class="form-label">HOD</label><input class="form-control" name="hod" value="{{ old('hod') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Notes</h6>
<div class="row g-3">
    <div class="col-12"><textarea class="form-control" name="notes" rows="3" placeholder="Internal notes...">{{ old('notes') }}</textarea></div>
</div>
</div></div>
</div>

{{-- ═══ BANKING & PF ═══ --}}
<div class="tab-pane fade" id="tab-bank">
<div class="card"><div class="card-body">
<h6 class="text-muted fw-bold text-uppercase small mb-3">Bank Details</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Bank Name</label><input class="form-control" name="bank_name" value="{{ old('bank_name') }}"></div>
    <div class="col-md-4"><label class="form-label">Account Owner Name</label><input class="form-control" name="bank_account_owner" value="{{ old('bank_account_owner') }}"></div>
    <div class="col-md-4"><label class="form-label">Account Number</label><input class="form-control" name="bank_account_no" value="{{ old('bank_account_no') }}"></div>
    <div class="col-md-4"><label class="form-label">Bank ID</label><input class="form-control" name="bank_id" value="{{ old('bank_id') }}"></div>
    <div class="col-md-4"><label class="form-label">IFSC Code</label><input class="form-control" name="ifsc_code" value="{{ old('ifsc_code') }}"></div>
    <div class="col-md-2"><label class="form-label">Payment Method</label><input class="form-control" name="payment_method" value="{{ old('payment_method') }}"></div>
    <div class="col-md-2"><label class="form-label">Currency</label><input class="form-control" name="currency" value="{{ old('currency','INR') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">PF & Statutory</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Previous PF Account Number</label><input class="form-control" name="previous_pf_account" value="{{ old('previous_pf_account') }}"></div>
    <div class="col-md-4"><label class="form-label">PF Account Number</label><input class="form-control" name="pf_account_number" value="{{ old('pf_account_number') }}"></div>
    <div class="col-md-4"><label class="form-label">UAN Number</label><input class="form-control" name="uan_number" value="{{ old('uan_number') }}"></div>
    <div class="col-md-4"><label class="form-label">PF Category</label><input class="form-control" name="pf_category" value="{{ old('pf_category') }}"></div>
    <div class="col-md-4"><label class="form-label">ESIC Account Number</label><input class="form-control" name="esic_account_number" value="{{ old('esic_account_number') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Insurance / Superannuation</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">SA Policy Number</label><input class="form-control" name="sa_policy_number" value="{{ old('sa_policy_number') }}"></div>
    <div class="col-md-4"><label class="form-label">SA Policy ID</label><input class="form-control" name="sa_policy_id" value="{{ old('sa_policy_id') }}"></div>
    <div class="col-md-4"><label class="form-label">SA Annuity Number</label><input class="form-control" name="sa_annuity_number" value="{{ old('sa_annuity_number') }}"></div>
    <div class="col-md-4"><label class="form-label">GR Policy Number</label><input class="form-control" name="gr_policy_number" value="{{ old('gr_policy_number') }}"></div>
    <div class="col-md-4"><label class="form-label">GR Policy ID</label><input class="form-control" name="gr_policy_id" value="{{ old('gr_policy_id') }}"></div>
</div>
</div></div>
</div>

{{-- ═══ EMERGENCY & NOMINEE ═══ --}}
<div class="tab-pane fade" id="tab-emergency">
<div class="card"><div class="card-body">
<h6 class="text-muted fw-bold text-uppercase small mb-3">Emergency Contact</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Contact Person</label><input class="form-control" name="emergency_contact_person" value="{{ old('emergency_contact_person') }}"></div>
    <div class="col-md-4"><label class="form-label">Relationship</label><input class="form-control" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}"></div>
    <div class="col-md-4"><label class="form-label">Mobile No</label><input class="form-control" name="emergency_contact_mobile" value="{{ old('emergency_contact_mobile') }}"></div>
</div>
<h6 class="text-muted fw-bold text-uppercase small mt-4 mb-3">Nominee (PF / Insurance)</h6>
<div class="row g-3">
    <div class="col-md-4"><label class="form-label">Relation</label><input class="form-control" name="nominee_relation" value="{{ old('nominee_relation') }}"></div>
    <div class="col-md-4"><label class="form-label">Nominee Date of Birth</label><input type="date" class="form-control" name="nominee_birth_date" value="{{ old('nominee_birth_date') }}"></div>
    <div class="col-md-4"><label class="form-label">Nominee Gender</label><input class="form-control" name="nominee_gender" value="{{ old('nominee_gender') }}"></div>
</div>
</div></div>
</div>

</div>{{-- end tab-content --}}

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Employee</button>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
</form>
@endsection

@push('scripts')
<script>
function loadProjects(clientId) {
    const sel = document.getElementById('projectSelect');
    Array.from(sel.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!clientId || opt.dataset.client === clientId) ? '' : 'none';
    });
    sel.value = '';
}
document.addEventListener('DOMContentLoaded', () => {
    const v = document.getElementById('clientSelect').value;
    if (v) loadProjects(v);
});
</script>
@endpush

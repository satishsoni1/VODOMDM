<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Core
        'employee_code', 'username', 'first_name', 'last_name', 'name',
        'email', 'phone', 'alternate_phone',
        'designation', 'plant_location', 'business_area', 'status',
        'is_manager', 'last_login_date',

        // Org / Manager
        'client_id', 'client_project_id', 'location_id',
        'manager_name', 'manager_phone', 'manager_email',
        'manager_username', 'manager_emp_id',
        'region', 'hq', 'abm',

        // Dates
        'joining_date', 'date_of_group_joining', 'confirmation_date',
        'exit_date', 'default_shift',

        // Personal
        'about_me', 'views_on_organization', 'gender', 'blood_group',
        'date_of_birth', 'place_of_birth', 'marital_status', 'date_of_marriage',
        'fathers_name', 'mother_tongue',

        // Identity
        'aadhar_number', 'passport_number', 'pan_number', 'driving_licence_number',

        // Contact
        'personal_email', 'personal_mobile',

        // Address
        'address', 'permanent_address', 'city', 'state', 'country', 'pin_code',

        // Education
        'graduation', 'year_of_passing_grad', 'post_graduation', 'year_of_passing_post_grad',
        'other_qualification', 'year_of_passing_other', 'certifications', 'co_curricular_activities',

        // Position & Company
        'position_id', 'company_name', 'company_state', 'field_work_location',
        'function_sbu', 'vertical', 'division', 'department', 'sub_department',

        // Employment classification
        'employee_category', 'employee_group', 'employee_payroll_group',
        'employment_status', 'notice_period', 'cost_centre', 'cost_centre_name',
        'business_head_sbu', 'tag', 'previous_experience',

        // Work setup
        'dotted_line_manager', 'biometric_id', 'posting_type', 'direct_reports_flag',
        'grade', 'level',

        // HR
        'hrbp', 'payroll_area', 'hrss', 'hod',

        // Banking
        'bank_name', 'bank_account_owner', 'bank_account_no', 'bank_id',
        'ifsc_code', 'payment_method', 'currency',

        // PF & Statutory
        'previous_pf_account', 'pf_account_number', 'uan_number', 'pf_category',
        'esic_account_number',

        // Insurance / Superannuation
        'sa_policy_number', 'sa_policy_id', 'sa_annuity_number',
        'gr_policy_number', 'gr_policy_id',

        // Nominee
        'nominee_relation', 'nominee_birth_date', 'nominee_gender',

        // Emergency contact
        'emergency_contact_person', 'emergency_contact_relationship', 'emergency_contact_mobile',

        // Documents
        'doc_aadhar_card', 'doc_pan_card', 'doc_graduation', 'doc_post_graduation',
        'doc_relieving_letter', 'doc_experience_letter', 'doc_other', 'doc_dob_proof',

        // Misc
        'notes',
    ];

    protected $casts = [
        'joining_date'         => 'date',
        'date_of_group_joining' => 'date',
        'confirmation_date'    => 'date',
        'exit_date'            => 'date',
        'date_of_birth'        => 'date',
        'date_of_marriage'     => 'date',
        'nominee_birth_date'   => 'date',
        'last_login_date'      => 'datetime',
        'is_manager'           => 'boolean',
        'direct_reports_flag'  => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(DeviceHandover::class);
    }

    public function currentDevices(): HasMany
    {
        return $this->hasMany(Device::class, 'current_employee_id');
    }

    public function recoveryCases(): HasMany
    {
        return $this->hasMany(RecoveryCase::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    public function mdmDevice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MdmPortalDevice::class);
    }
}

<?php

namespace App\Http\Controllers;

use App\Imports\BulkCompanyAssignImport;
use App\Imports\BulkDeviceAssignImport;
use App\Imports\EmployeesImport;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
set_time_limit(0);
class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['client', 'project'])->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('employee_code', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('username', 'like', "%$q%");
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('region')) {
            $query->where('region', 'like', '%' . $request->region . '%');
        }

        $employees = $query->paginate(25)->withQueryString();
        $clients   = Client::where('status', 'active')->orderBy('name')->get();

        return view('employees.index', compact('employees', 'clients'));
    }

    public function create()
    {
        $clients   = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('employees.create', compact('clients', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        if (empty($validated['name']) && (!empty($validated['first_name']) || !empty($validated['last_name']))) {
            $validated['name'] = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));
        }

        $employee = Employee::create($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'client', 'project', 'location',
            'currentDevices.model.brand',
            'handovers.device',
            'recoveryCases',
            'callLogs' => fn ($q) => $q->latest()->limit(10),
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $clients   = Client::with('projects')->where('status', 'active')->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('employees.edit', compact('employee', 'clients', 'locations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $rules = $this->rules($employee->id);
        $validated = $request->validate($rules);

        if (empty($validated['name']) && (!empty($validated['first_name']) || !empty($validated['last_name']))) {
            $validated['name'] = trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));
        }

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }

    public function importForm()
    {
        return view('employees.import');
    }

    public function import(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new EmployeesImport();
        Excel::import($import, $request->file('file'));

        $summary = [
            'imported' => $import->importedCount,
            'updated'  => $import->updatedCount,
            'skipped'  => $import->skippedCount,
            'errors'   => $import->errors,
        ];

        // XHR: persist summary in session so the JS-driven redirect still shows the banner
        if ($request->ajax()) {
            session(['import_summary' => $summary]);
            return response()->json(['redirect' => route('employees.index')]);
        }

        return redirect()->route('employees.index')->with('import_summary', $summary);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ];

        $columns = [
            'company_code', 'employee_code', 'username', 'first_name', 'last_name', 'name',
            'email', 'phone', 'alternate_phone', 'designation', 'plant_location',
            'business_area', 'status', 'is_manager', 'default_shift',
            'manager_name', 'manager_username', 'manager_emp_id', 'manager_phone', 'manager_email',
            'date_of_joining', 'date_of_group_joining', 'confirmation_date', 'last_working_date',
            'gender', 'blood_group', 'date_of_birth', 'place_of_birth',
            'marital_status', 'date_of_marriage', 'fathers_name', 'mother_tongue',
            'about_me', 'your_views_on_our_organization',
            'aadhar_number', 'passport_number', 'pan_number', 'driving_licence_number',
            'official_mail_id', 'official_mobile_no', 'personal_mail_id', 'personal_mobile_no',
            'current_address', 'permanent_address', 'state', 'city_town', 'country', 'pin_code',
            'graduation', 'year_of_passing_grad', 'post_graduation', 'year_of_passing_post_grad',
            'other_qualification', 'year_of_passing_other_qualification',
            'certifications', 'co_curricular_activities_hobbies',
            'position_id', 'company_name', 'company_state', 'field_work_location',
            'function_sbu', 'vertical', 'division', 'department', 'sub_department',
            'employee_category', 'employee_group', 'employee_payroll_group',
            'employment_status', 'notice_period', 'cost_centre', 'cost_centre_name',
            'business_head_sbu', 'tag', 'previous_experience',
            'dotted_line_reporting_manager', 'punch_card_bio_metric_id',
            'posting_on_corporate_plant_field_staff', 'direct_reports_location_flag',
            'grade', 'level', 'hrbp', 'payroll_area', 'hrss', 'hod',
            'bank_name', 'bank_account_owner', 'bank_account_no', 'bank_id', 'ifsc', 'payment_method', 'currency',
            'previous_pf_account_number', 'pf_account_number', 'uan_number', 'pf_category', 'esic_account_number',
            'sa_policy_number', 'sa_policy_id', 'sa_annuity_number', 'gr_policy_number', 'gr_policy_id',
            'relation', 'birth_date', 'sex',
            'emergency_contact_person', 'relationship', 'emergency_contact_s_mobile_no',
            'region', 'hq', 'abm', 'notes',
        ];

        $sample = [
            'CLIENT001', 'EMP001', 'jdoe', 'John', 'Doe', 'John Doe',
            'john.doe@company.com', '9876543210', '', 'Software Engineer', 'Plant A',
            'IT', 'active', 'No', 'General',
            'Jane Smith', 'jsmith', 'EMP002', '9876543211', 'jane.smith@company.com',
            '2023-01-15', '2023-01-15', '2023-07-15', '',
            'Male', 'O+', '1990-05-20', 'Mumbai',
            'Single', '', 'Ramesh Doe', 'Hindi',
            '', '',
            '123456789012', 'P1234567', 'ABCDE1234F', 'DL-123456',
            'john.doe@company.com', '9876543210', 'john.doe@gmail.com', '9876543210',
            '123 Main St', '456 Home St', 'Maharashtra', 'Mumbai', 'India', '400001',
            'B.Tech', '2012', 'M.Tech', '2014',
            '', '',
            '', '',
            'POS001', 'Acme Corp', 'Maharashtra', 'Mumbai',
            'Engineering', 'Technology', 'Software', 'Engineering', 'Backend',
            'Permanent', 'Regular', 'Monthly',
            'Active', '30 days', 'CC001', 'IT Cost Centre',
            'IT Head', '', '5 years',
            'Jane Smith', 'BIO001',
            'Corporate', 'No',
            'L3', 'Senior', 'HR Name', 'Area A', 'HR Support', 'Dept Head',
            'HDFC Bank', 'John Doe', '1234567890', 'HDFC0001', 'HDFC0001234', 'NEFT', 'INR',
            '', 'PF001', 'UAN001', 'Category A', 'ESIC001',
            '', '', '', '', '',
            '', '', '',
            'Emergency Contact', 'Spouse', '9876543212',
            'West', 'Mumbai', '', '',
        ];

        $callback = function () use ($columns, $sample) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            fputcsv($handle, $sample);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Bulk Assignment ────────────────────────────────────────────────────────

    public function bulkAssignForm()
    {
        return view('employees.bulk_assign');
    }

    public function bulkAssignCompany(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new BulkCompanyAssignImport();
        Excel::import($import, $request->file('file'));

        $summary = [
            'assigned' => $import->assignedCount,
            'skipped'  => $import->skippedCount,
            'errors'   => $import->errors,
        ];

        if ($request->ajax()) {
            session(['bulk_assign_summary' => $summary]);
            return response()->json(['redirect' => route('employees.index')]);
        }

        return redirect()->route('employees.index')->with('bulk_assign_summary', $summary);
    }

    public function bulkCompanyTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bulk_company_assign_template.csv"',
        ];

        $columns = ['company_code', 'employee_code'];
        $sample  = ['CLIENT001', 'EMP001'];

        return response()->stream(function () use ($columns, $sample) {
            $h = fopen('php://output', 'w');
            fputcsv($h, $columns);
            fputcsv($h, $sample);
            fclose($h);
        }, 200, $headers);
    }

    public function bulkAssignDevice(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        $import = new BulkDeviceAssignImport(auth()->id());
        Excel::import($import, $request->file('file'));

        $summary = [
            'assigned' => $import->assignedCount,
            'skipped'  => $import->skippedCount,
            'errors'   => $import->errors,
        ];

        if ($request->ajax()) {
            session(['bulk_device_summary' => $summary]);
            return response()->json(['redirect' => route('handovers.index')]);
        }

        return redirect()->route('handovers.index')->with('bulk_device_summary', $summary);
    }

    public function bulkDeviceTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="bulk_device_assign_template.csv"',
        ];

        $columns = [
            'company_code', 'employee_code', 'asset_tag', 'serial_number', 'imei', 'group',
            'handover_date', 'handover_location', 'handover_city',
            'condition', 'accessories', 'remarks',
        ];
        $sample = [
            'CLIENT001', 'EMP001', 'AST-00123', '', '', 'Sales Team',
            '2024-06-01', 'Head Office', 'Mumbai',
            'new', 'Charger, Box', 'Initial issue',
        ];

        return response()->stream(function () use ($columns, $sample) {
            $h = fopen('php://output', 'w');
            fputcsv($h, $columns);
            fputcsv($h, $sample);
            fclose($h);
        }, 200, $headers);
    }

    private function rules(?int $ignoreId = null): array
    {
        $unique = $ignoreId
            ? "required|string|max:50|unique:employees,employee_code,{$ignoreId}"
            : 'required|string|max:50|unique:employees';

        return [
            'employee_code'              => $unique,
            'username'                   => 'nullable|string|max:100',
            'first_name'                 => 'nullable|string|max:100',
            'last_name'                  => 'nullable|string|max:100',
            'name'                       => 'nullable|string|max:255',
            'email'                      => 'nullable|email|max:255',
            'phone'                      => 'nullable|string|max:20',
            'alternate_phone'            => 'nullable|string|max:20',
            'designation'                => 'nullable|string|max:150',
            'plant_location'             => 'nullable|string|max:150',
            'business_area'              => 'nullable|string|max:150',
            'status'                     => 'required|in:active,inactive,resigned,terminated,on_leave',
            'is_manager'                 => 'boolean',
            'default_shift'              => 'nullable|string|max:100',
            'client_id'                  => 'nullable|exists:clients,id',
            'client_project_id'          => 'nullable|exists:client_projects,id',
            'location_id'                => 'nullable|exists:locations,id',
            'manager_name'               => 'nullable|string|max:255',
            'manager_username'           => 'nullable|string|max:100',
            'manager_emp_id'             => 'nullable|string|max:50',
            'manager_phone'              => 'nullable|string|max:20',
            'manager_email'              => 'nullable|email|max:255',
            'region'                     => 'nullable|string|max:100',
            'hq'                         => 'nullable|string|max:100',
            'abm'                        => 'nullable|string|max:100',
            'joining_date'               => 'nullable|date',
            'date_of_group_joining'      => 'nullable|date',
            'confirmation_date'          => 'nullable|date',
            'exit_date'                  => 'nullable|date',
            'about_me'                   => 'nullable|string',
            'views_on_organization'      => 'nullable|string',
            'gender'                     => 'nullable|in:male,female,other,prefer_not_to_say',
            'blood_group'                => 'nullable|string|max:10',
            'date_of_birth'              => 'nullable|date',
            'place_of_birth'             => 'nullable|string|max:150',
            'marital_status'             => 'nullable|in:single,married,divorced,widowed,separated',
            'date_of_marriage'           => 'nullable|date',
            'fathers_name'               => 'nullable|string|max:255',
            'mother_tongue'              => 'nullable|string|max:100',
            'aadhar_number'              => 'nullable|string|max:20',
            'passport_number'            => 'nullable|string|max:30',
            'pan_number'                 => 'nullable|string|max:20',
            'driving_licence_number'     => 'nullable|string|max:30',
            'personal_email'             => 'nullable|email|max:255',
            'personal_mobile'            => 'nullable|string|max:20',
            'address'                    => 'nullable|string',
            'permanent_address'          => 'nullable|string',
            'city'                       => 'nullable|string|max:100',
            'state'                      => 'nullable|string|max:100',
            'country'                    => 'nullable|string|max:100',
            'pin_code'                   => 'nullable|string|max:10',
            'graduation'                 => 'nullable|string|max:255',
            'year_of_passing_grad'       => 'nullable|integer|min:1950|max:2099',
            'post_graduation'            => 'nullable|string|max:255',
            'year_of_passing_post_grad'  => 'nullable|integer|min:1950|max:2099',
            'other_qualification'        => 'nullable|string|max:255',
            'year_of_passing_other'      => 'nullable|integer|min:1950|max:2099',
            'certifications'             => 'nullable|string',
            'co_curricular_activities'   => 'nullable|string',
            'position_id'                => 'nullable|string|max:100',
            'company_name'               => 'nullable|string|max:255',
            'company_state'              => 'nullable|string|max:100',
            'field_work_location'        => 'nullable|string|max:255',
            'function_sbu'               => 'nullable|string|max:150',
            'vertical'                   => 'nullable|string|max:150',
            'division'                   => 'nullable|string|max:150',
            'department'                 => 'nullable|string|max:150',
            'sub_department'             => 'nullable|string|max:150',
            'employee_category'          => 'nullable|string|max:100',
            'employee_group'             => 'nullable|string|max:100',
            'employee_payroll_group'     => 'nullable|string|max:100',
            'employment_status'          => 'nullable|string|max:100',
            'notice_period'              => 'nullable|string|max:100',
            'cost_centre'                => 'nullable|string|max:100',
            'cost_centre_name'           => 'nullable|string|max:255',
            'business_head_sbu'          => 'nullable|string|max:255',
            'tag'                        => 'nullable|string|max:100',
            'previous_experience'        => 'nullable|string|max:255',
            'dotted_line_manager'        => 'nullable|string|max:255',
            'biometric_id'               => 'nullable|string|max:100',
            'posting_type'               => 'nullable|string|max:100',
            'direct_reports_flag'        => 'boolean',
            'grade'                      => 'nullable|string|max:50',
            'level'                      => 'nullable|string|max:50',
            'hrbp'                       => 'nullable|string|max:255',
            'payroll_area'               => 'nullable|string|max:100',
            'hrss'                       => 'nullable|string|max:255',
            'hod'                        => 'nullable|string|max:255',
            'bank_name'                  => 'nullable|string|max:255',
            'bank_account_owner'         => 'nullable|string|max:255',
            'bank_account_no'            => 'nullable|string|max:50',
            'bank_id'                    => 'nullable|string|max:50',
            'ifsc_code'                  => 'nullable|string|max:20',
            'payment_method'             => 'nullable|string|max:50',
            'currency'                   => 'nullable|string|max:10',
            'previous_pf_account'        => 'nullable|string|max:100',
            'pf_account_number'          => 'nullable|string|max:100',
            'uan_number'                 => 'nullable|string|max:50',
            'pf_category'                => 'nullable|string|max:50',
            'esic_account_number'        => 'nullable|string|max:50',
            'sa_policy_number'           => 'nullable|string|max:50',
            'sa_policy_id'               => 'nullable|string|max:50',
            'sa_annuity_number'          => 'nullable|string|max:50',
            'gr_policy_number'           => 'nullable|string|max:50',
            'gr_policy_id'               => 'nullable|string|max:50',
            'nominee_relation'           => 'nullable|string|max:100',
            'nominee_birth_date'         => 'nullable|date',
            'nominee_gender'             => 'nullable|string|max:20',
            'emergency_contact_person'   => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_mobile'   => 'nullable|string|max:20',
            'notes'                      => 'nullable|string',
        ];
    }
}

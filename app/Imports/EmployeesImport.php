<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class EmployeesImport implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public int $importedCount = 0;
    public int $updatedCount  = 0;
    public int $skippedCount  = 0;
    public array $errors      = [];

    /** In-memory cache: company_code → client_id (avoids N DB queries per chunk) */
    private array $clientCache = [];

    public function chunkSize(): int
    {
        return 100;
    }

    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data     = $row->toCollection();

        $code = trim($data['employee_code'] ?? '');
        if ($code === '') {
            $this->errors[]   = "Row {$rowIndex}: Employee Code is required — row skipped.";
            $this->skippedCount++;
            return;
        }

        // Resolve company → client_id
        $clientId = $this->resolveClient($data);

        // A company_code was supplied but could not be matched — block the row
        $companyProvided = trim($data['company_code'] ?? '') !== '' ||
                           trim($data['company_name'] ?? '') !== '';
        if ($companyProvided && $clientId === null) {
            $this->errors[]   = "Row {$rowIndex} ({$code}): Company not found — row skipped.";
            $this->skippedCount++;
            return;
        }

        try {
            $mapped = $this->mapRow($data, $clientId);

            // Match by company + employee_code when company is known; else by code alone
            $query = Employee::where('employee_code', $code);
            if ($clientId !== null) {
                $query->where('client_id', $clientId);
            }
            $existing = $query->first();

            if ($existing) {
                $existing->update($mapped);
                $this->updatedCount++;
            } else {
                Employee::create($mapped);
                $this->importedCount++;
            }
        } catch (\Throwable $e) {
            $this->errors[] = "Row {$rowIndex} ({$code}): " . $e->getMessage();
            $this->skippedCount++;
        }
    }

    /**
     * Resolve company_code or company_name → client_id.
     * Returns null if nothing was supplied (not an error — just no company specified).
     */
    private function resolveClient(\Illuminate\Support\Collection $data): ?int
    {
        $companyCode = trim($data['company_code'] ?? '');
        $companyName = trim($data['company_name'] ?? '');

        if ($companyCode !== '') {
            $cacheKey = 'code:' . strtolower($companyCode);
            if (!array_key_exists($cacheKey, $this->clientCache)) {
                $client = Client::where('code', $companyCode)->first();
                $this->clientCache[$cacheKey] = $client?->id;
            }
            return $this->clientCache[$cacheKey];
        }

        if ($companyName !== '') {
            $cacheKey = 'name:' . strtolower($companyName);
            if (!array_key_exists($cacheKey, $this->clientCache)) {
                $client = Client::whereRaw('LOWER(name) = ?', [strtolower($companyName)])->first();
                $this->clientCache[$cacheKey] = $client?->id;
            }
            return $this->clientCache[$cacheKey];
        }

        return null;
    }

    private function mapRow(\Illuminate\Support\Collection $row, ?int $clientId): array
    {
        $firstName = trim($row['first_name'] ?? '');
        $lastName  = trim($row['last_name']  ?? '');
        $name      = trim($row['name'] ?? $row['full_name'] ?? '');
        if ($name === '' && ($firstName || $lastName)) {
            $name = trim("{$firstName} {$lastName}");
        }

        $data = [
            'employee_code'                  => trim($row['employee_code'] ?? ''),
            'client_id'                      => $clientId,
            'username'                       => $row['username']                        ?? null,
            'first_name'                     => $firstName ?: null,
            'last_name'                      => $lastName  ?: null,
            'name'                           => $name      ?: null,
            'email'                          => $row['email'] ?? $row['official_mail_id'] ?? null,
            'phone'                          => $row['phone'] ?? $row['official_mobile_no'] ?? null,
            'alternate_phone'                => $row['alternate_phone']                  ?? null,
            'designation'                    => $row['designation']                      ?? null,
            'plant_location'                 => $row['plant_location']                   ?? null,
            'business_area'                  => $row['business_area']                    ?? null,
            'status'                         => $this->mapStatus($row['status']          ?? null),
            'is_manager'                     => $this->parseBool($row['is_manager']      ?? null),
            'manager_name'                   => $row['manager'] ?? $row['manager_name']  ?? null,
            'manager_username'               => $row['manager_username']                 ?? null,
            'manager_emp_id'                 => $row['manager_id'] ?? $row['manager_emp_id'] ?? null,
            'manager_phone'                  => $row['manager_phone']                    ?? null,
            'manager_email'                  => $row['manager_email']                    ?? null,
            'default_shift'                  => $row['default_shift']                    ?? null,
            'about_me'                       => $row['about_me']                         ?? null,
            'views_on_organization'          => $row['your_views_on_our_organization'] ?? $row['views_on_organization'] ?? null,
            'gender'                         => $this->mapGender($row['gender']          ?? null),
            'blood_group'                    => $row['blood_group']                      ?? null,
            'date_of_birth'                  => $this->parseDate($row['date_of_birth']   ?? null),
            'place_of_birth'                 => $row['place_of_birth']                   ?? null,
            'marital_status'                 => $this->mapMaritalStatus($row['marital_status'] ?? null),
            'date_of_marriage'               => $this->parseDate($row['date_of_marriage'] ?? null),
            'fathers_name'                   => $row['father_s_name'] ?? $row['fathers_name'] ?? null,
            'mother_tongue'                  => $row['mother_tongue']                    ?? null,
            'aadhar_number'                  => $row['aadhar_number']                    ?? null,
            'passport_number'                => $row['passport_number']                  ?? null,
            'pan_number'                     => $row['pan_number']                       ?? null,
            'driving_licence_number'         => $row['driving_licence_number']           ?? null,
            'personal_email'                 => $row['personal_mail_id'] ?? $row['personal_email'] ?? null,
            'personal_mobile'                => $row['personal_mobile_no'] ?? $row['personal_mobile'] ?? null,
            'address'                        => $row['current_address'] ?? $row['address'] ?? null,
            'permanent_address'              => $row['permanent_address']                ?? null,
            'city'                           => $row['city_town'] ?? $row['city']        ?? null,
            'state'                          => $row['state']                            ?? null,
            'country'                        => $row['country']                          ?? null,
            'pin_code'                       => $row['pin_code']                         ?? null,
            'graduation'                     => $row['graduation']                       ?? null,
            'year_of_passing_grad'           => $row['year_of_passing_grad']             ?? null,
            'post_graduation'                => $row['post_graduation']                  ?? null,
            'year_of_passing_post_grad'      => $row['year_of_passing_post_grad']        ?? null,
            'other_qualification'            => $row['other_qualification']              ?? null,
            'year_of_passing_other'          => $row['year_of_passing_other_qualification'] ?? $row['year_of_passing_other'] ?? null,
            'certifications'                 => $row['certifications']                   ?? null,
            'co_curricular_activities'       => $row['co_curricular_activities_hobbies'] ?? $row['co_curricular_activities'] ?? null,
            'position_id'                    => $row['position_id']                      ?? null,
            'company_name'                   => $row['company_name']                     ?? null,
            'company_state'                  => $row['company_state']                    ?? null,
            'field_work_location'            => $row['field_work_location']              ?? null,
            'function_sbu'                   => $row['function_sbu']                     ?? null,
            'vertical'                       => $row['vertical']                         ?? null,
            'division'                       => $row['division']                         ?? null,
            'department'                     => $row['department']                       ?? null,
            'sub_department'                 => $row['sub_department']                   ?? null,
            'employee_category'              => $row['employee_category']                ?? null,
            'employee_group'                 => $row['employee_group']                   ?? null,
            'employee_payroll_group'         => $row['employee_payroll_group']           ?? null,
            'confirmation_date'              => $this->parseDate($row['confirmation_date'] ?? null),
            'employment_status'              => $row['employment_status']                ?? null,
            'notice_period'                  => $row['notice_period']                    ?? null,
            'cost_centre'                    => $row['cost_centre']                      ?? null,
            'cost_centre_name'               => $row['cost_centre_name']                 ?? null,
            'business_head_sbu'              => $row['business_head_sbu']                ?? null,
            'exit_date'                      => $this->parseDate($row['last_working_date'] ?? $row['exit_date'] ?? null),
            'tag'                            => $row['tag']                              ?? null,
            'dotted_line_manager'            => $row['dotted_line_reporting_manager'] ?? $row['dotted_line_manager'] ?? null,
            'biometric_id'                   => $row['punch_card_bio_metric_id'] ?? $row['biometric_id'] ?? null,
            'posting_type'                   => $row['posting_on_corporate_plant_field_staff'] ?? $row['posting_type'] ?? null,
            'direct_reports_flag'            => $this->parseBool($row['direct_reports_location_flag'] ?? null),
            'grade'                          => $row['grade']                            ?? null,
            'level'                          => $row['level']                            ?? null,
            'hrbp'                           => $row['hrbp']                             ?? null,
            'payroll_area'                   => $row['payroll_area']                     ?? null,
            'hrss'                           => $row['hrss']                             ?? null,
            'hod'                            => $row['hod']                              ?? null,
            'bank_name'                      => $row['bank_name']                        ?? null,
            'bank_account_owner'             => $row['bank_account_owner']               ?? null,
            'bank_account_no'                => $row['bank_account_no']                  ?? null,
            'bank_id'                        => $row['bank_id']                          ?? null,
            'ifsc_code'                      => $row['ifsc']  ?? $row['ifsc_code']       ?? null,
            'payment_method'                 => $row['payment_method']                   ?? null,
            'currency'                       => $row['currency']                         ?? null,
            'previous_pf_account'            => $row['previous_pf_account_number'] ?? $row['previous_pf_account'] ?? null,
            'pf_account_number'              => $row['pf_account_number']                ?? null,
            'uan_number'                     => $row['uan_number']                       ?? null,
            'pf_category'                    => $row['pf_category']                      ?? null,
            'esic_account_number'            => $row['esic_account_number']              ?? null,
            'sa_policy_number'               => $row['sa_policy_number']                 ?? null,
            'sa_policy_id'                   => $row['sa_policy_id']                     ?? null,
            'sa_annuity_number'              => $row['sa_annuity_number']                ?? null,
            'gr_policy_number'               => $row['gr_policy_number']                 ?? null,
            'gr_policy_id'                   => $row['gr_policy_id']                     ?? null,
            'emergency_contact_person'       => $row['emergency_contact_person']         ?? null,
            'emergency_contact_relationship' => $row['relationship'] ?? $row['emergency_contact_relationship'] ?? null,
            'emergency_contact_mobile'       => $row['emergency_contact_s_mobile_no'] ?? $row['emergency_contact_mobile'] ?? null,
            'previous_experience'            => $row['previous_experience']              ?? null,
            'nominee_relation'               => $row['relation']   ?? $row['nominee_relation'] ?? null,
            'nominee_birth_date'             => $this->parseDate($row['birth_date']      ?? $row['nominee_birth_date'] ?? null),
            'nominee_gender'                 => $row['sex'] ?? $row['nominee_gender']    ?? null,
            'joining_date'                   => $this->parseDate($row['date_of_joining'] ?? $row['joining_date'] ?? null),
            'date_of_group_joining'          => $this->parseDate($row['date_of_group_joining'] ?? null),
            'region'                         => $row['region']                           ?? null,
            'hq'                             => $row['hq']                               ?? null,
            'abm'                            => $row['abm']                              ?? null,
            'notes'                          => $row['notes']                            ?? null,
        ];

        return array_filter($data, fn($v) => $v !== null && $v !== '');
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;
        if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d');
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseBool(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        return in_array(strtolower((string) $value), ['1', 'yes', 'true', 'y'], true);
    }

    private function mapStatus(?string $value): string
    {
        $map = [
            'active'     => 'active',
            'inactive'   => 'inactive',
            'resigned'   => 'resigned',
            'terminated' => 'terminated',
            'on_leave'   => 'on_leave',
            'on leave'   => 'on_leave',
        ];
        return $map[strtolower(trim((string) $value))] ?? 'active';
    }

    private function mapGender(?string $value): ?string
    {
        if (empty($value)) return null;
        $map = [
            'male'   => 'male',   'm' => 'male',
            'female' => 'female', 'f' => 'female',
            'other'  => 'other',
        ];
        return $map[strtolower(trim($value))] ?? null;
    }

    private function mapMaritalStatus(?string $value): ?string
    {
        if (empty($value)) return null;
        $map = [
            'single'    => 'single',   'unmarried' => 'single',
            'married'   => 'married',
            'divorced'  => 'divorced',
            'widowed'   => 'widowed',
            'separated' => 'separated',
        ];
        return $map[strtolower(trim($value))] ?? null;
    }
}

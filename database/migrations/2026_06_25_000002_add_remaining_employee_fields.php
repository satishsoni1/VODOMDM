<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure DYNAMIC row format (allows TEXT off-page storage)
        \DB::statement('ALTER TABLE `employees` ROW_FORMAT=DYNAMIC');

        // Shorten some existing varchar(255) columns that were added in migration 1.
        // This frees ~14,000 bytes so the remaining new columns can fit within the 65535 limit.
        $shorten = [
            'employment_status'      => 100,
            'notice_period'          => 50,
            'employee_category'      => 100,
            'employee_group'         => 100,
            'employee_payroll_group' => 100,
            'grade'                  => 50,
            'level'                  => 50,
            'payroll_area'           => 100,
            'posting_type'           => 50,
            'tag'                    => 100,
            'biometric_id'           => 100,
            'bank_id'                => 50,
            'bank_account_no'        => 50,
            'ifsc_code'              => 20,
            'payment_method'         => 50,
            'bank_name'              => 150,
            'bank_account_owner'     => 150,
            'hq'                     => 100,
            'abm'                    => 100,
            'company_state'          => 100,
        ];

        foreach ($shorten as $column => $length) {
            if (Schema::hasColumn('employees', $column)) {
                \DB::statement("ALTER TABLE `employees` MODIFY `{$column}` VARCHAR({$length}) NULL");
            }
        }

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'currency')) {
                $table->string('currency', 10)->nullable();
            }
            if (!Schema::hasColumn('employees', 'previous_pf_account')) {
                $table->string('previous_pf_account', 100)->nullable();
            }
            if (!Schema::hasColumn('employees', 'pf_account_number')) {
                $table->string('pf_account_number', 100)->nullable();
            }
            if (!Schema::hasColumn('employees', 'uan_number')) {
                $table->string('uan_number', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'pf_category')) {
                $table->string('pf_category', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'esic_account_number')) {
                $table->string('esic_account_number', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'sa_policy_number')) {
                $table->string('sa_policy_number', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'sa_policy_id')) {
                $table->string('sa_policy_id', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'sa_annuity_number')) {
                $table->string('sa_annuity_number', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'gr_policy_number')) {
                $table->string('gr_policy_number', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'gr_policy_id')) {
                $table->string('gr_policy_id', 50)->nullable();
            }
            if (!Schema::hasColumn('employees', 'nominee_relation')) {
                $table->string('nominee_relation', 100)->nullable();
            }
            if (!Schema::hasColumn('employees', 'nominee_birth_date')) {
                $table->date('nominee_birth_date')->nullable();
            }
            if (!Schema::hasColumn('employees', 'nominee_gender')) {
                $table->string('nominee_gender', 20)->nullable();
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_person')) {
                $table->string('emergency_contact_person', 150)->nullable();
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship', 100)->nullable();
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_mobile')) {
                $table->string('emergency_contact_mobile', 20)->nullable();
            }
            // Document reference columns stored as TEXT (off-page, no row-size overhead)
            if (!Schema::hasColumn('employees', 'doc_aadhar_card')) {
                $table->text('doc_aadhar_card')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_pan_card')) {
                $table->text('doc_pan_card')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_graduation')) {
                $table->text('doc_graduation')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_post_graduation')) {
                $table->text('doc_post_graduation')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_relieving_letter')) {
                $table->text('doc_relieving_letter')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_experience_letter')) {
                $table->text('doc_experience_letter')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_other')) {
                $table->text('doc_other')->nullable();
            }
            if (!Schema::hasColumn('employees', 'doc_dob_proof')) {
                $table->text('doc_dob_proof')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $cols = [
                'currency', 'previous_pf_account', 'pf_account_number', 'uan_number',
                'pf_category', 'esic_account_number', 'sa_policy_number', 'sa_policy_id',
                'sa_annuity_number', 'gr_policy_number', 'gr_policy_id', 'nominee_relation',
                'nominee_birth_date', 'nominee_gender', 'emergency_contact_person',
                'emergency_contact_relationship', 'emergency_contact_mobile', 'doc_aadhar_card',
                'doc_pan_card', 'doc_graduation', 'doc_post_graduation', 'doc_relieving_letter',
                'doc_experience_letter', 'doc_other', 'doc_dob_proof',
            ];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('employees', $c));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};

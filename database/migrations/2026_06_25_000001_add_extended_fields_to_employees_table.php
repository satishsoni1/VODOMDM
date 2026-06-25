<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Switch to DYNAMIC row format so many varchar columns can overflow to off-page storage
        \DB::statement('ALTER TABLE `employees` ROW_FORMAT=DYNAMIC');

        Schema::table('employees', function (Blueprint $table) {
            // Basic / Identity
            $table->string('username', 100)->nullable()->after('employee_code');
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('plant_location', 150)->nullable()->after('designation');
            $table->string('business_area', 150)->nullable()->after('plant_location');
            $table->boolean('is_manager')->default(false)->after('business_area');
            $table->string('manager_username', 100)->nullable()->after('manager_email');
            $table->string('manager_emp_id', 50)->nullable()->after('manager_username');
            $table->timestamp('last_login_date')->nullable();

            // Employment dates
            $table->date('date_of_group_joining')->nullable()->after('joining_date');
            $table->date('confirmation_date')->nullable()->after('date_of_group_joining');
            $table->string('default_shift')->nullable();

            // Personal
            $table->text('about_me')->nullable();
            $table->text('views_on_organization')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'separated'])->nullable();
            $table->date('date_of_marriage')->nullable();
            $table->string('fathers_name')->nullable();
            $table->string('mother_tongue')->nullable();

            // Identity documents
            $table->string('aadhar_number', 20)->nullable();
            $table->string('passport_number', 30)->nullable();
            $table->string('pan_number', 20)->nullable();
            $table->string('driving_licence_number', 30)->nullable();

            // Contact
            $table->string('personal_email')->nullable();
            $table->string('personal_mobile', 20)->nullable();

            // Address extension
            $table->text('permanent_address')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('pin_code', 10)->nullable();

            // Education
            $table->string('graduation')->nullable();
            $table->smallInteger('year_of_passing_grad')->nullable();
            $table->string('post_graduation')->nullable();
            $table->smallInteger('year_of_passing_post_grad')->nullable();
            $table->string('other_qualification')->nullable();
            $table->smallInteger('year_of_passing_other')->nullable();
            $table->text('certifications')->nullable();
            $table->text('co_curricular_activities')->nullable();

            // Position & Company
            $table->string('position_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_state')->nullable();
            $table->string('field_work_location')->nullable();
            $table->string('function_sbu')->nullable();
            $table->string('vertical')->nullable();
            $table->string('division')->nullable();
            $table->string('sub_department')->nullable();

            // Employment classification
            $table->string('employee_category')->nullable();
            $table->string('employee_group')->nullable();
            $table->string('employee_payroll_group')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('notice_period')->nullable();
            $table->string('cost_centre')->nullable();
            $table->string('cost_centre_name')->nullable();
            $table->string('business_head_sbu')->nullable();
            $table->string('tag')->nullable();
            $table->string('previous_experience')->nullable();

            // Work setup
            $table->string('dotted_line_manager')->nullable();
            $table->string('biometric_id')->nullable();
            $table->string('posting_type')->nullable();
            $table->boolean('direct_reports_flag')->default(false);
            $table->string('grade')->nullable();
            $table->string('level')->nullable();

            // HR
            $table->string('hrbp')->nullable();
            $table->string('payroll_area')->nullable();
            $table->string('hrss')->nullable();
            $table->string('hod')->nullable();

            // Banking
            $table->string('bank_name')->nullable();
            $table->string('bank_account_owner')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_id')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('currency', 10)->nullable();

            // PF & Statutory
            $table->string('previous_pf_account')->nullable();
            $table->string('pf_account_number')->nullable();
            $table->string('uan_number')->nullable();
            $table->string('pf_category')->nullable();
            $table->string('esic_account_number')->nullable();

            // Insurance / Superannuation
            $table->string('sa_policy_number')->nullable();
            $table->string('sa_policy_id')->nullable();
            $table->string('sa_annuity_number')->nullable();
            $table->string('gr_policy_number')->nullable();
            $table->string('gr_policy_id')->nullable();

            // Nominee
            $table->string('nominee_relation')->nullable();
            $table->date('nominee_birth_date')->nullable();
            $table->string('nominee_gender')->nullable();

            // Emergency contact
            $table->string('emergency_contact_person')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_mobile', 20)->nullable();

            // Document paths / references
            $table->string('doc_aadhar_card')->nullable();
            $table->string('doc_pan_card')->nullable();
            $table->string('doc_graduation')->nullable();
            $table->string('doc_post_graduation')->nullable();
            $table->string('doc_relieving_letter')->nullable();
            $table->string('doc_experience_letter')->nullable();
            $table->string('doc_other')->nullable();
            $table->string('doc_dob_proof')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'first_name', 'last_name', 'plant_location', 'business_area',
                'is_manager', 'manager_username', 'manager_emp_id', 'last_login_date',
                'date_of_group_joining', 'confirmation_date', 'default_shift',
                'about_me', 'views_on_organization', 'gender', 'blood_group',
                'date_of_birth', 'place_of_birth', 'marital_status', 'date_of_marriage',
                'fathers_name', 'mother_tongue', 'aadhar_number', 'passport_number',
                'pan_number', 'driving_licence_number', 'personal_email', 'personal_mobile',
                'permanent_address', 'country', 'pin_code',
                'graduation', 'year_of_passing_grad', 'post_graduation', 'year_of_passing_post_grad',
                'other_qualification', 'year_of_passing_other', 'certifications', 'co_curricular_activities',
                'position_id', 'company_name', 'company_state', 'field_work_location',
                'function_sbu', 'vertical', 'division', 'sub_department',
                'employee_category', 'employee_group', 'employee_payroll_group',
                'employment_status', 'notice_period', 'cost_centre', 'cost_centre_name',
                'business_head_sbu', 'tag', 'previous_experience',
                'dotted_line_manager', 'biometric_id', 'posting_type', 'direct_reports_flag',
                'grade', 'level', 'hrbp', 'payroll_area', 'hrss', 'hod',
                'bank_name', 'bank_account_owner', 'bank_account_no', 'bank_id',
                'ifsc_code', 'payment_method', 'currency',
                'previous_pf_account', 'pf_account_number', 'uan_number', 'pf_category',
                'esic_account_number', 'sa_policy_number', 'sa_policy_id',
                'sa_annuity_number', 'gr_policy_number', 'gr_policy_id',
                'nominee_relation', 'nominee_birth_date', 'nominee_gender',
                'emergency_contact_person', 'emergency_contact_relationship', 'emergency_contact_mobile',
                'doc_aadhar_card', 'doc_pan_card', 'doc_graduation', 'doc_post_graduation',
                'doc_relieving_letter', 'doc_experience_letter', 'doc_other', 'doc_dob_proof',
            ]);
        });
    }
};

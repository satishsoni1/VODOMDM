<?php

namespace Database\Seeders;

use App\Models\CallLog;
use App\Models\Client;
use App\Models\ClientProject;
use App\Models\CourierPartner;
use App\Models\DemandRequest;
use App\Models\Device;
use App\Models\DeviceBrand;
use App\Models\DeviceHandover;
use App\Models\DeviceInsurance;
use App\Models\DeviceModel;
use App\Models\DispatchBatch;
use App\Models\DispatchItem;
use App\Models\Employee;
use App\Models\Grn;
use App\Models\InsuranceClaim;
use App\Models\InsurancePolicy;
use App\Models\InsuranceProvider;
use App\Models\Location;
use App\Models\OwnershipHistory;
use App\Models\PoInvoice;
use App\Models\PurchaseOrder;
use App\Models\RecoveryCase;
use App\Models\RepairOrder;
use App\Models\Rfq;
use App\Models\RfqVendor;
use App\Models\Role;
use App\Models\ServiceCenter;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketComment;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorContact;
use App\Models\VendorQuotation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── USERS ──────────────────────────────────────────────────────────
        $roles = Role::pluck('id', 'slug');
        $admin = User::where('email', 'admin@assettrack.in')->first();

        $staffDefs = [
            ['name' => 'Amit Sharma',  'email' => 'procurement@assettrack.in', 'role' => 'procurement',    'dept' => 'Procurement'],
            ['name' => 'Priya Nair',   'email' => 'warehouse@assettrack.in',   'role' => 'warehouse',      'dept' => 'Warehouse'],
            ['name' => 'Ravi Mehta',   'email' => 'ops@assettrack.in',         'role' => 'operations',     'dept' => 'Operations'],
            ['name' => 'Sneha Kapoor', 'email' => 'servicedesk@assettrack.in', 'role' => 'service_desk',   'dept' => 'IT Support'],
            ['name' => 'Vikram Singh', 'email' => 'recovery@assettrack.in',    'role' => 'recovery_agent', 'dept' => 'Recovery'],
            ['name' => 'Deepa Iyer',   'email' => 'finance@assettrack.in',     'role' => 'finance',        'dept' => 'Finance'],
        ];

        $createdUsers = [];
        foreach ($staffDefs as $u) {
            $user = User::firstOrCreate(['email' => $u['email']], [
                'name'       => $u['name'],
                'password'   => Hash::make('Demo@1234'),
                'role_id'    => $roles[$u['role']] ?? null,
                'department' => $u['dept'],
                'is_active'  => true,
            ]);
            $createdUsers[$u['role']] = $user;
        }

        $procUser      = $createdUsers['procurement'];
        $warehouseUser = $createdUsers['warehouse'];
        $opsUser       = $createdUsers['operations'];
        $sdUser        = $createdUsers['service_desk'];
        $recUser       = $createdUsers['recovery_agent'];

        // ─── VENDORS ────────────────────────────────────────────────────────
        $vendor1 = Vendor::firstOrCreate(['code' => 'VND-001'], [
            'name' => 'TechDistributors Pvt Ltd', 'contact_person' => 'Rajesh Kumar',
            'email' => 'rajesh@techdist.in', 'phone' => '9999000001',
            'city' => 'Mumbai', 'state' => 'Maharashtra', 'status' => 'active',
        ]);
        $vendor2 = Vendor::firstOrCreate(['code' => 'VND-002'], [
            'name' => 'DigiSupply Solutions', 'contact_person' => 'Meena Patel',
            'email' => 'meena@digisupply.in', 'phone' => '9999000002',
            'gstin' => '27AAAPD1234A1Z5',
            'city' => 'Pune', 'state' => 'Maharashtra', 'status' => 'active',
        ]);
        $vendor3 = Vendor::firstOrCreate(['code' => 'VND-003'], [
            'name' => 'MobileMart India', 'contact_person' => 'Sunil Joshi',
            'email' => 'sunil@mobilemart.in', 'phone' => '9999000003',
            'city' => 'Delhi', 'state' => 'Delhi', 'status' => 'active',
        ]);

        VendorContact::firstOrCreate(['email' => 'sales@techdist.in'], [
            'vendor_id' => $vendor1->id, 'name' => 'Ravi Desai',
            'designation' => 'Sales Manager', 'phone' => '9111000001',
        ]);
        VendorContact::firstOrCreate(['email' => 'accounts@techdist.in'], [
            'vendor_id' => $vendor1->id, 'name' => 'Anita Shah',
            'designation' => 'Accounts Manager', 'phone' => '9111000002',
        ]);

        // ─── CLIENTS & PROJECTS ──────────────────────────────────────────────
        $client1 = Client::firstOrCreate(['code' => 'CLI-001'], [
            'name' => 'Acme Pharma Ltd', 'contact_person' => 'Suresh Sharma',
            'email' => 'suresh@acmepharma.in', 'phone' => '9888000001',
            'city' => 'Mumbai', 'state' => 'Maharashtra', 'status' => 'active',
        ]);
        $client2 = Client::firstOrCreate(['code' => 'CLI-002'], [
            'name' => 'Tata Consumer Products', 'contact_person' => 'Nilesh Bhatia',
            'email' => 'nilesh@tataconsumer.in', 'phone' => '9888000002',
            'gstin' => '27AAACT1234A1Z5',
            'city' => 'Pune', 'state' => 'Maharashtra', 'status' => 'active',
        ]);
        $client3 = Client::firstOrCreate(['code' => 'CLI-003'], [
            'name' => 'HUL Distribution', 'contact_person' => 'Kavita Rao',
            'email' => 'kavita@hul.in', 'phone' => '9888000003',
            'city' => 'Bangalore', 'state' => 'Karnataka', 'status' => 'active',
        ]);

        $proj1 = ClientProject::firstOrCreate(['code' => 'ACME-PRJ-001'], [
            'client_id' => $client1->id, 'name' => 'Field Force Mobility',
            'region' => 'All India', 'status' => 'active',
        ]);
        $proj2 = ClientProject::firstOrCreate(['code' => 'TATA-PRJ-001'], [
            'client_id' => $client2->id, 'name' => 'Sales Force Mobility',
            'region' => 'West India', 'status' => 'active',
        ]);
        $proj3 = ClientProject::firstOrCreate(['code' => 'HUL-PRJ-001'], [
            'client_id' => $client3->id, 'name' => 'Distribution Team Devices',
            'region' => 'South India', 'status' => 'active',
        ]);

        // ─── DEVICE MODELS ───────────────────────────────────────────────────
        $samsung  = DeviceBrand::where('name', 'Samsung')->first();
        $motorola = DeviceBrand::where('name', 'Motorola')->first();
        $nokia    = DeviceBrand::where('name', 'Nokia')->first();
        $smartCat = \App\Models\DeviceCategory::where('slug', 'smartphone')->first();

        $model1 = DeviceModel::firstOrCreate(['model_number' => 'SM-A145F'], [
            'brand_id' => $samsung->id, 'category_id' => $smartCat->id,
            'model_name' => 'Galaxy A14', 'os' => 'Android', 'standard_cost' => 11999,
        ]);
        $model2 = DeviceModel::firstOrCreate(['model_number' => 'SM-A245F'], [
            'brand_id' => $samsung->id, 'category_id' => $smartCat->id,
            'model_name' => 'Galaxy A24', 'os' => 'Android', 'standard_cost' => 17999,
        ]);
        DeviceModel::firstOrCreate(['model_number' => 'XT2343-4'], [
            'brand_id' => $motorola->id, 'category_id' => $smartCat->id,
            'model_name' => 'Moto G73 5G', 'os' => 'Android', 'standard_cost' => 15999,
        ]);
        DeviceModel::firstOrCreate(['model_number' => 'TA-1420'], [
            'brand_id' => $nokia->id, 'category_id' => $smartCat->id,
            'model_name' => 'Nokia G42 5G', 'os' => 'Android', 'standard_cost' => 13999,
        ]);

        // ─── LOCATION ───────────────────────────────────────────────────────
        $locDelhi = Location::where('code', 'WH-DEL')->first()
            ?? Location::where('city', 'Delhi')->first()
            ?? Location::first();

        // ─── EMPLOYEES ───────────────────────────────────────────────────────
        $empDefs = [
            ['code' => 'EMP-ACME-001', 'name' => 'Rahul Gupta',    'client' => $client1, 'proj' => $proj1, 'phone' => '9700000001', 'desig' => 'Medical Rep',     'dept' => 'Sales', 'city' => 'Mumbai',     'state' => 'Maharashtra', 'region' => 'West'],
            ['code' => 'EMP-ACME-002', 'name' => 'Suman Das',      'client' => $client1, 'proj' => $proj1, 'phone' => '9700000002', 'desig' => 'Area Manager',    'dept' => 'Sales', 'city' => 'Pune',       'state' => 'Maharashtra', 'region' => 'West'],
            ['code' => 'EMP-ACME-003', 'name' => 'Anita Verma',    'client' => $client1, 'proj' => $proj1, 'phone' => '9700000003', 'desig' => 'Medical Rep',     'dept' => 'Sales', 'city' => 'Nashik',     'state' => 'Maharashtra', 'region' => 'West'],
            ['code' => 'EMP-ACME-004', 'name' => 'Kiran Patil',    'client' => $client1, 'proj' => $proj1, 'phone' => '9700000004', 'desig' => 'Zonal Manager',   'dept' => 'Sales', 'city' => 'Delhi',      'state' => 'Delhi',       'region' => 'North'],
            ['code' => 'EMP-TATA-001', 'name' => 'Devendra Yadav', 'client' => $client2, 'proj' => $proj2, 'phone' => '9700000005', 'desig' => 'Sales Executive', 'dept' => 'Sales', 'city' => 'Pune',       'state' => 'Maharashtra', 'region' => 'West'],
            ['code' => 'EMP-TATA-002', 'name' => 'Meghana Joshi',  'client' => $client2, 'proj' => $proj2, 'phone' => '9700000006', 'desig' => 'Sales Executive', 'dept' => 'Sales', 'city' => 'Nagpur',     'state' => 'Maharashtra', 'region' => 'Central'],
            ['code' => 'EMP-TATA-003', 'name' => 'Rajiv Kulkarni', 'client' => $client2, 'proj' => $proj2, 'phone' => '9700000007', 'desig' => 'Territory Head',  'dept' => 'Sales', 'city' => 'Aurangabad', 'state' => 'Maharashtra', 'region' => 'Central'],
            ['code' => 'EMP-HUL-001',  'name' => 'Lakshmi Reddy',  'client' => $client3, 'proj' => $proj3, 'phone' => '9700000008', 'desig' => 'Distributor MR',  'dept' => 'Sales', 'city' => 'Bangalore',  'state' => 'Karnataka',   'region' => 'South'],
            ['code' => 'EMP-HUL-002',  'name' => 'Suresh Babu',    'client' => $client3, 'proj' => $proj3, 'phone' => '9700000009', 'desig' => 'Distributor MR',  'dept' => 'Sales', 'city' => 'Chennai',    'state' => 'Tamil Nadu',  'region' => 'South'],
            ['code' => 'EMP-HUL-003',  'name' => 'Divya Kumar',    'client' => $client3, 'proj' => $proj3, 'phone' => '9700000010', 'desig' => 'Area Manager',    'dept' => 'Sales', 'city' => 'Hyderabad',  'state' => 'Telangana',   'region' => 'South'],
        ];

        $employees = [];
        foreach ($empDefs as $ed) {
            $employees[] = Employee::firstOrCreate(['employee_code' => $ed['code']], [
                'name'              => $ed['name'],
                'client_id'         => $ed['client']->id,
                'client_project_id' => $ed['proj']->id,
                'phone'             => $ed['phone'],
                'designation'       => $ed['desig'],
                'department'        => $ed['dept'],
                'city'              => $ed['city'],
                'state'             => $ed['state'],
                'region'            => $ed['region'],
                'status'            => 'active',
                'joining_date'      => now()->subMonths(rand(6, 24)),
            ]);
        }

        // ─── PROCUREMENT ─────────────────────────────────────────────────────
        $dr1 = DemandRequest::firstOrCreate(['request_number' => 'DR-DEMO-0001'], [
            'client_id'            => $client1->id,
            'client_project_id'    => $proj1->id,
            'device_model_id'      => $model1->id,
            'device_specification' => 'Samsung Galaxy A14 4GB/64GB Black — Field Sales',
            'quantity'             => 10,
            'budget_amount'        => 125000,
            'required_date'        => now()->subDays(60)->toDateString(),
            'division'             => 'West Zone',
            'region'               => 'Maharashtra',
            'justification'        => 'Field medical reps need smartphones for detailing app and order tracking.',
            'requested_by'         => $procUser->id,
            'status'               => 'converted_to_po',
            'approved_by'          => $admin->id,
            'approved_at'          => now()->subDays(58),
        ]);

        $dr2 = DemandRequest::firstOrCreate(['request_number' => 'DR-DEMO-0002'], [
            'client_id'            => $client2->id,
            'client_project_id'    => $proj2->id,
            'device_model_id'      => $model2->id,
            'device_specification' => 'Samsung Galaxy A24 — Sales Team West',
            'quantity'             => 7,
            'budget_amount'        => 130000,
            'required_date'        => now()->subDays(30)->toDateString(),
            'division'             => 'West',
            'region'               => 'Maharashtra',
            'justification'        => 'New sales hires require devices for DMS and SFA.',
            'requested_by'         => $procUser->id,
            'status'               => 'approved',
            'approved_by'          => $admin->id,
            'approved_at'          => now()->subDays(28),
        ]);

        DemandRequest::firstOrCreate(['request_number' => 'DR-DEMO-0003'], [
            'client_id'            => $client3->id,
            'client_project_id'    => $proj3->id,
            'device_model_id'      => $model1->id,
            'device_specification' => 'Samsung Galaxy A14 — South Distribution Team',
            'quantity'             => 5,
            'budget_amount'        => 62000,
            'required_date'        => now()->addDays(15)->toDateString(),
            'region'               => 'Karnataka',
            'justification'        => 'Replacement for damaged devices in South region.',
            'requested_by'         => $procUser->id,
            'status'               => 'submitted',
        ]);

        // RFQ 1 (closed) — rfqs table has no sent_at column
        $rfq1 = Rfq::firstOrCreate(['rfq_number' => 'RFQ-DEMO-0001'], [
            'demand_request_id'    => $dr1->id,
            'created_by'           => $procUser->id,
            'device_specification' => $dr1->device_specification,
            'quantity'             => 10,
            'response_deadline'    => now()->subDays(52)->toDateString(),
            'terms'                => 'Delivery within 7 days of PO. GST extra.',
            'status'               => 'closed',
        ]);

        // rfq_vendors table DOES have sent_at
        RfqVendor::firstOrCreate(['rfq_id' => $rfq1->id, 'vendor_id' => $vendor1->id], [
            'sent_at' => now()->subDays(56), 'status' => 'responded',
        ]);
        RfqVendor::firstOrCreate(['rfq_id' => $rfq1->id, 'vendor_id' => $vendor2->id], [
            'sent_at' => now()->subDays(56), 'status' => 'responded',
        ]);

        $quot1 = VendorQuotation::firstOrCreate(['rfq_id' => $rfq1->id, 'vendor_id' => $vendor1->id], [
            'quotation_number' => 'QT-TD-2026-001',
            'quotation_date'   => now()->subDays(53)->toDateString(),
            'valid_until'      => now()->subDays(23)->toDateString(),
            'quantity'         => 10,
            'unit_price'       => 11500,
            'total_amount'     => 115000,
            'delivery_days'    => 5,
            'warranty_months'  => '12',
            'is_selected'      => true,
        ]);

        VendorQuotation::firstOrCreate(['rfq_id' => $rfq1->id, 'vendor_id' => $vendor2->id], [
            'quotation_number' => 'QT-DS-2026-001',
            'quotation_date'   => now()->subDays(52)->toDateString(),
            'valid_until'      => now()->subDays(22)->toDateString(),
            'quantity'         => 10,
            'unit_price'       => 11800,
            'total_amount'     => 118000,
            'delivery_days'    => 7,
        ]);

        $rfq2 = Rfq::firstOrCreate(['rfq_number' => 'RFQ-DEMO-0002'], [
            'demand_request_id'    => $dr2->id,
            'created_by'           => $procUser->id,
            'device_specification' => $dr2->device_specification,
            'quantity'             => 7,
            'response_deadline'    => now()->addDays(5)->toDateString(),
            'terms'                => 'Delivery within 10 days of PO.',
            'status'               => 'sent',
        ]);

        RfqVendor::firstOrCreate(['rfq_id' => $rfq2->id, 'vendor_id' => $vendor1->id], [
            'sent_at' => now()->subDays(5), 'status' => 'pending',
        ]);
        RfqVendor::firstOrCreate(['rfq_id' => $rfq2->id, 'vendor_id' => $vendor3->id], [
            'sent_at' => now()->subDays(5), 'status' => 'pending',
        ]);

        $po1 = PurchaseOrder::firstOrCreate(['po_number' => 'PO-DEMO-0001'], [
            'vendor_id'              => $vendor1->id,
            'demand_request_id'      => $dr1->id,
            'rfq_id'                 => $rfq1->id,
            'vendor_quotation_id'    => $quot1->id,
            'created_by'             => $procUser->id,
            'approved_by'            => $admin->id,
            'approved_at'            => now()->subDays(50),
            'po_date'                => now()->subDays(50)->toDateString(),
            'expected_delivery_date' => now()->subDays(43)->toDateString(),
            'quantity'               => 10,
            'unit_price'             => 11500,
            'total_amount'           => 115000,
            'tax_amount'             => 20700,
            'grand_total'            => 135700,
            'payment_terms'          => '30 days from invoice date',
            'delivery_address'       => 'Warehouse Delhi, Plot 12, Sector 5, Noida, UP',
            'warranty_months'        => '12',
            'status'                 => 'completed',
        ]);

        PoInvoice::firstOrCreate(['invoice_number' => 'INV-TD-2026-001'], [
            'purchase_order_id' => $po1->id,
            'invoice_date'      => now()->subDays(44)->toDateString(),
            'invoice_amount'    => 135700,
            'due_date'          => now()->subDays(14)->toDateString(),
            'payment_status'    => 'paid',
        ]);

        $po2 = PurchaseOrder::firstOrCreate(['po_number' => 'PO-DEMO-0002'], [
            'vendor_id'              => $vendor2->id,
            'demand_request_id'      => $dr2->id,
            'created_by'             => $procUser->id,
            'approved_by'            => $admin->id,
            'approved_at'            => now()->subDays(15),
            'po_date'                => now()->subDays(15)->toDateString(),
            'expected_delivery_date' => now()->subDays(8)->toDateString(),
            'quantity'               => 5,
            'unit_price'             => 17500,
            'total_amount'           => 87500,
            'tax_amount'             => 15750,
            'grand_total'            => 103250,
            'payment_terms'          => '30 days from invoice date',
            'warranty_months'        => '12',
            'status'                 => 'approved',
        ]);

        // ─── GRN 1 + DEVICES ─────────────────────────────────────────────────
        $grn1 = Grn::firstOrCreate(['grn_number' => 'GRN-DEMO-0001'], [
            'purchase_order_id'       => $po1->id,
            'vendor_id'               => $vendor1->id,
            'location_id'             => $locDelhi->id,
            'received_by'             => $warehouseUser->id,
            'received_date'           => now()->subDays(43)->toDateString(),
            'quantity_ordered'        => 10,
            'quantity_received'       => 10,
            'quantity_accepted'       => 10,
            'quantity_rejected'       => 0,
            'invoice_number'          => 'INV-TD-2026-001',
            'delivery_challan_number' => 'DC-TD-2026-0588',
            'remarks'                 => 'All 10 units received in sealed boxes. IMEI verified.',
            'status'                  => 'accepted',
        ]);

        $devicePool = [];
        $a14Defs = [
            ['AT-2026-0001','SN00A14001','358000001111111','358000001111112'],
            ['AT-2026-0002','SN00A14002','358000002222221','358000002222222'],
            ['AT-2026-0003','SN00A14003','358000003333331','358000003333332'],
            ['AT-2026-0004','SN00A14004','358000004444441','358000004444442'],
            ['AT-2026-0005','SN00A14005','358000005555551','358000005555552'],
            ['AT-2026-0006','SN00A14006','358000006666661','358000006666662'],
            ['AT-2026-0007','SN00A14007','358000007777771','358000007777772'],
            ['AT-2026-0008','SN00A14008','358000008888881','358000008888882'],
            ['AT-2026-0009','SN00A14009','358000009999991','358000009999992'],
            ['AT-2026-0010','SN00A14010','358000010101011','358000010101012'],
        ];
        foreach ($a14Defs as $dd) {
            $devicePool[] = Device::firstOrCreate(['asset_tag' => $dd[0]], [
                'serial_number'       => $dd[1],
                'imei1'               => $dd[2],
                'imei2'               => $dd[3],
                'device_model_id'     => $model1->id,
                'grn_id'              => $grn1->id,
                'purchase_order_id'   => $po1->id,
                'vendor_id'           => $vendor1->id,
                'color'               => 'Black',
                'box_number'          => 'BOX-' . substr($dd[0], -4),
                'purchase_date'       => now()->subDays(43)->toDateString(),
                'purchase_price'      => 11500,
                'warranty_months'     => 12,
                'warranty_expiry'     => now()->subDays(43)->addYear()->toDateString(),
                'lifecycle_status'    => 'in_stock',
                'condition'           => 'new',
                'current_location_id' => $locDelhi->id,
            ]);
        }

        // GRN 2 + 4 A24 devices
        $grn2 = Grn::firstOrCreate(['grn_number' => 'GRN-DEMO-0002'], [
            'purchase_order_id'       => $po2->id,
            'vendor_id'               => $vendor2->id,
            'location_id'             => $locDelhi->id,
            'received_by'             => $warehouseUser->id,
            'received_date'           => now()->subDays(7)->toDateString(),
            'quantity_ordered'        => 5,
            'quantity_received'       => 5,
            'quantity_accepted'       => 4,
            'quantity_rejected'       => 1,
            'delivery_challan_number' => 'DC-DS-2026-0122',
            'remarks'                 => '1 unit rejected (cracked screen). 4 units accepted.',
            'status'                  => 'partially_accepted',
        ]);

        $a24Defs = [
            ['AT-2026-0011','SN00A24011','359000011111111'],
            ['AT-2026-0012','SN00A24012','359000012222221'],
            ['AT-2026-0013','SN00A24013','359000013333331'],
            ['AT-2026-0014','SN00A24014','359000014444441'],
        ];
        foreach ($a24Defs as $dd) {
            $devicePool[] = Device::firstOrCreate(['asset_tag' => $dd[0]], [
                'serial_number'       => $dd[1],
                'imei1'               => $dd[2],
                'device_model_id'     => $model2->id,
                'grn_id'              => $grn2->id,
                'purchase_order_id'   => $po2->id,
                'vendor_id'           => $vendor2->id,
                'color'               => 'Silver',
                'purchase_date'       => now()->subDays(7)->toDateString(),
                'purchase_price'      => 17500,
                'warranty_months'     => 12,
                'warranty_expiry'     => now()->subDays(7)->addYear()->toDateString(),
                'lifecycle_status'    => 'in_stock',
                'condition'           => 'new',
                'current_location_id' => $locDelhi->id,
            ]);
        }

        // ─── DISPATCH ────────────────────────────────────────────────────────
        $courier = CourierPartner::where('code', 'BD')->first()
            ?? CourierPartner::first();

        $dispatch1 = DispatchBatch::firstOrCreate(['dispatch_number' => 'DISP-DEMO-0001'], [
            'client_id'              => $client1->id,
            'client_project_id'      => $proj1->id,
            'courier_partner_id'     => $courier?->id,
            'from_location_id'       => $locDelhi->id,
            'dispatched_by'          => $opsUser->id,
            'awb_number'             => 'BD-2026-99001',
            'tracking_number'        => 'BD99001',
            'dispatch_date'          => now()->subDays(38)->toDateString(),
            'expected_delivery_date' => now()->subDays(35)->toDateString(),
            'actual_delivery_date'   => now()->subDays(36)->toDateString(),
            'destination_address'    => 'Acme Pharma Head Office, Worli, Mumbai',
            'destination_city'       => 'Mumbai',
            'destination_state'      => 'Maharashtra',
            'receiver_name'          => 'Suresh Sharma',
            'receiver_phone'         => '9888000001',
            'freight_cost'           => 850,
            'status'                 => 'delivered',
        ]);

        $dispatchDevices = array_slice($devicePool, 0, 4);
        foreach ($dispatchDevices as $dev) {
            DispatchItem::firstOrCreate(
                ['dispatch_batch_id' => $dispatch1->id, 'device_id' => $dev->id],
                ['status' => 'delivered']
            );
            $dev->update(['lifecycle_status' => 'delivered', 'client_id' => $client1->id]);
        }
        // Remaining A14 devices stay in_stock (devices 4–9 are not dispatched)

        // ─── HANDOVERS ───────────────────────────────────────────────────────
        $acmeEmployees = array_slice($employees, 0, 4);
        $hoDates = [now()->subDays(35), now()->subDays(34), now()->subDays(33), now()->subDays(32)];

        for ($i = 0; $i < 4; $i++) {
            DeviceHandover::firstOrCreate(['handover_number' => 'HO-DEMO-000' . ($i + 1)], [
                'device_id'               => $dispatchDevices[$i]->id,
                'employee_id'             => $acmeEmployees[$i]->id,
                'client_id'               => $client1->id,
                'client_project_id'       => $proj1->id,
                'dispatch_batch_id'       => $dispatch1->id,
                'handed_over_by'          => $opsUser->id,
                'handover_date'           => $hoDates[$i]->toDateString(),
                'handover_location'       => 'Acme Pharma Head Office',
                'handover_city'           => 'Mumbai',
                'condition_at_handover'   => 'new',
                'accessories_handed'      => 'Charger, USB Cable, Box',
                'acknowledgement_received'=> true,
                'acknowledged_at'         => $hoDates[$i]->copy()->addHours(2),
                'status'                  => 'activated',
            ]);

            $dispatchDevices[$i]->update([
                'lifecycle_status'    => 'activated',
                'client_id'           => $client1->id,
                'current_employee_id' => $acmeEmployees[$i]->id,
            ]);

            OwnershipHistory::firstOrCreate(
                [
                    'device_id'   => $dispatchDevices[$i]->id,
                    'employee_id' => $acmeEmployees[$i]->id,
                    'from_date'   => $hoDates[$i]->toDateString(),
                ],
                [
                    'client_id'       => $client1->id,
                    'ownership_type'  => 'employee',
                    'transfer_reason' => 'Initial device handover',
                    'transferred_by'  => $opsUser->id,
                ]
            );
        }

        // ─── TICKETS ─────────────────────────────────────────────────────────
        $cats = TicketCategory::pluck('id', 'slug');

        $ticketDefs = [
            ['number' => 'TKT-DEMO-0001', 'di' => 0, 'ei' => 0, 'cat' => 'broken_screen',     'priority' => 'high',     'status' => 'in_progress',   'days' => 10, 'sla' => 24, 'rsp' => true,  'res' => false, 'subject' => 'Screen cracked after accidental drop',     'desc' => 'Employee Rahul Gupta reported the screen cracked after the device fell from his pocket. Display has a large crack affecting visibility.'],
            ['number' => 'TKT-DEMO-0002', 'di' => 1, 'ei' => 1, 'cat' => 'app_issue',         'priority' => 'medium',   'status' => 'resolved',      'days' => 20, 'sla' => 24, 'rsp' => true,  'res' => true,  'subject' => 'CRM App crashing on launch',               'desc' => 'The Salesforce mobile app crashes immediately after login. Issue started after latest Android update. Reinstalling did not help.'],
            ['number' => 'TKT-DEMO-0003', 'di' => 2, 'ei' => 2, 'cat' => 'battery_issue',     'priority' => 'medium',   'status' => 'open',          'days' => 3,  'sla' => 24, 'rsp' => false, 'res' => false, 'subject' => 'Battery drains in 3-4 hours',              'desc' => 'Device battery drains extremely fast. Full charge at 8 AM, dead by noon with minimal usage. Affecting full-day field work.'],
            ['number' => 'TKT-DEMO-0004', 'di' => 3, 'ei' => 3, 'cat' => 'lost_device',       'priority' => 'critical', 'status' => 'assigned',      'days' => 5,  'sla' => 2,  'rsp' => true,  'res' => false, 'subject' => 'Device reported stolen — Delhi',           'desc' => 'Device stolen from employee vehicle in Delhi. FIR filed at Connaught Place PS (CR-2026-4421). IMEI block requested.'],
            ['number' => 'TKT-DEMO-0005', 'di' => 4, 'ei' => 0, 'cat' => 'sim_issue',         'priority' => 'high',     'status' => 'pending_user',  'days' => 7,  'sla' => 8,  'rsp' => true,  'res' => false, 'subject' => 'SIM card not detected',                   'desc' => 'Device showing "SIM not inserted" error. SIM tray cleaned and reseated but issue persists. Employee suspects slot damage.'],
            ['number' => 'TKT-DEMO-0006', 'di' => 5, 'ei' => 1, 'cat' => 'device_not_working','priority' => 'high',     'status' => 'closed',        'days' => 25, 'sla' => 4,  'rsp' => true,  'res' => true,  'subject' => 'Device not switching on',                  'desc' => 'Device completely unresponsive. Does not boot after 30s power hold. Charging LED does not light up. Hardware failure suspected.'],
        ];

        $tickets = [];
        foreach ($ticketDefs as $td) {
            $createdAt = now()->subDays($td['days']);
            $ticket = Ticket::firstOrCreate(['ticket_number' => $td['number']], [
                'device_id'          => $devicePool[$td['di']]->id,
                'employee_id'        => $employees[$td['ei']]->id,
                'client_id'          => $client1->id,
                'ticket_category_id' => $cats[$td['cat']] ?? null,
                'raised_by'          => $sdUser->id,
                'assigned_to'        => in_array($td['status'], ['assigned','in_progress','pending_user','resolved','closed']) ? $sdUser->id : null,
                'subject'            => $td['subject'],
                'description'        => $td['desc'],
                'priority'           => $td['priority'],
                'status'             => $td['status'],
                'sla_due_at'         => $createdAt->copy()->addHours($td['sla']),
                'first_response_at'  => $td['rsp'] ? $createdAt->copy()->addHour() : null,
                'resolved_at'        => $td['res'] ? $createdAt->copy()->addDays(2) : null,
                'closed_at'          => $td['status'] === 'closed' ? $createdAt->copy()->addDays(3) : null,
                'resolution_hours'   => $td['res'] ? 48 : null,
                'resolution_notes'   => $td['res'] ? 'Issue resolved after factory reset and app reinstall. Device tested and working normally.' : null,
                'created_at'         => $createdAt,
            ]);

            if ($td['rsp']) {
                TicketComment::firstOrCreate(
                    ['ticket_id' => $ticket->id, 'user_id' => $sdUser->id, 'is_internal' => false],
                    ['comment' => 'Ticket acknowledged. Contacting employee for detailed diagnosis.', 'created_at' => $createdAt->copy()->addHour()]
                );
            }
            $tickets[] = $ticket;
        }

        // ─── REPAIR ORDERS ───────────────────────────────────────────────────
        $sc = ServiceCenter::where('code', 'ASC-DEL-SAM')->first()
            ?? ServiceCenter::first();

        RepairOrder::firstOrCreate(['rma_number' => 'RMA-DEMO-0001'], [
            'device_id'             => $devicePool[0]->id,
            'service_center_id'     => $sc?->id,
            'ticket_id'             => $tickets[0]->id,
            'created_by'            => $sdUser->id,
            'fault_description'     => 'Cracked screen — display replacement required',
            'detailed_notes'        => 'Upper-left crack. Touch works but display bleeding. Warranty applicable.',
            'sent_date'             => now()->subDays(8)->toDateString(),
            'estimated_return_date' => now()->subDays(1)->toDateString(),
            'estimated_cost'        => 3500,
            'repair_type'           => 'warranty',
            'under_warranty'        => true,
            'status'                => 'under_repair',
        ]);

        RepairOrder::firstOrCreate(['rma_number' => 'RMA-DEMO-0002'], [
            'device_id'             => $devicePool[5]->id,
            'service_center_id'     => $sc?->id,
            'ticket_id'             => $tickets[5]->id,
            'created_by'            => $sdUser->id,
            'fault_description'     => 'Device dead — motherboard failure',
            'detailed_notes'        => 'Completely unresponsive. Service center confirmed motherboard failure.',
            'sent_date'             => now()->subDays(22)->toDateString(),
            'estimated_return_date' => now()->subDays(14)->toDateString(),
            'actual_return_date'    => now()->subDays(15)->toDateString(),
            'estimated_cost'        => 7500,
            'actual_cost'           => 0,
            'repair_type'           => 'warranty',
            'under_warranty'        => true,
            'status'                => 'returned',
            'outcome'               => 'replaced',
            'repair_notes'          => 'Device replaced under warranty. New unit issued.',
        ]);

        // ─── RECOVERY CASES ───────────────────────────────────────────────────
        $rc1 = RecoveryCase::firstOrCreate(['case_number' => 'RCV-DEMO-0001'], [
            'device_id'           => $devicePool[1]->id,
            'employee_id'         => $employees[1]->id,
            'client_id'           => $client1->id,
            'assigned_to'         => $recUser->id,
            'created_by'          => $opsUser->id,
            'trigger_reason'      => 'resignation',
            'exit_date'           => now()->subDays(5)->toDateString(),
            'recovery_due_date'   => now()->addDays(7)->toDateString(),
            'pickup_address'      => '12/B, Shivaji Nagar, Pune - 411005',
            'status'              => 'contacted',
            'follow_up_count'     => 2,
            'last_follow_up_at'   => now()->subDay(),
            'next_follow_up_date' => now()->addDays(2)->toDateString(),
            'remarks'             => 'Employee agreed to handover. Pickup scheduled for Friday.',
        ]);

        CallLog::firstOrCreate(
            ['recovery_case_id' => $rc1->id, 'phone_number' => $employees[1]->phone, 'outcome' => 'connected'],
            [
                'device_id'           => $devicePool[1]->id,
                'employee_id'         => $employees[1]->id,
                'called_by'           => $recUser->id,
                'call_datetime'       => now()->subDays(4),
                'duration_seconds'    => 145,
                'promise_date'        => now()->addDays(3)->toDateString(),
                'next_follow_up_date' => now()->addDays(2)->toDateString(),
                'remarks'             => 'Employee confirmed resignation and will hand over device.',
            ]
        );

        CallLog::firstOrCreate(
            ['recovery_case_id' => $rc1->id, 'phone_number' => $employees[1]->phone, 'outcome' => 'promised'],
            [
                'device_id'           => $devicePool[1]->id,
                'employee_id'         => $employees[1]->id,
                'called_by'           => $recUser->id,
                'call_datetime'       => now()->subDay(),
                'duration_seconds'    => 67,
                'promise_date'        => now()->addDays(3)->toDateString(),
                'next_follow_up_date' => now()->addDays(2)->toDateString(),
                'remarks'             => 'Confirmed Friday afternoon pickup.',
            ]
        );

        RecoveryCase::firstOrCreate(['case_number' => 'RCV-DEMO-0002'], [
            'device_id'          => $devicePool[3]->id,
            'employee_id'        => $employees[3]->id,
            'client_id'          => $client1->id,
            'assigned_to'        => $recUser->id,
            'created_by'         => $opsUser->id,
            'trigger_reason'     => 'other',
            'recovery_due_date'  => now()->subDays(2)->toDateString(),
            'status'             => 'escalated',
            'follow_up_count'    => 3,
            'last_follow_up_at'  => now()->subDays(3),
            'remarks'            => 'Device stolen. FIR filed. Escalated to management for insurance claim.',
        ]);

        // ─── INSURANCE ───────────────────────────────────────────────────────
        $insurer = InsuranceProvider::where('code', 'NIA')->first()
            ?? InsuranceProvider::first();

        $policy1 = InsurancePolicy::firstOrCreate(['policy_number' => 'POL-DEMO-0001'], [
            'insurance_provider_id' => $insurer?->id,
            'client_id'             => $client1->id,
            'coverage_type'         => 'Mobile Device Comprehensive Insurance',
            'coverage_details'      => "Covers: Physical damage, Theft, Liquid damage, Screen damage\nExclusions: Intentional damage, War\nDeductible: 10% per claim",
            'premium_amount'        => 18000,
            'sum_insured'           => 500000,
            'start_date'            => now()->subMonths(3)->toDateString(),
            'expiry_date'           => now()->addMonths(9)->toDateString(),
            'terms'                 => '30-day claim filing window. FIR mandatory for theft claims.',
            'status'                => 'active',
        ]);

        foreach (array_slice($devicePool, 0, 4) as $dev) {
            DeviceInsurance::firstOrCreate(
                ['device_id' => $dev->id, 'insurance_policy_id' => $policy1->id],
                [
                    'insured_value'  => 11500,
                    'effective_date' => now()->subMonths(3)->toDateString(),
                    'expiry_date'    => now()->addMonths(9)->toDateString(),
                    'status'         => 'active',
                ]
            );
        }

        InsuranceClaim::firstOrCreate(['claim_number' => 'CLM-DEMO-0001'], [
            'device_id'            => $devicePool[3]->id,
            'insurance_policy_id'  => $policy1->id,
            'raised_by'            => $opsUser->id,
            'incident_date'        => now()->subDays(5)->toDateString(),
            'incident_type'        => 'Theft',
            'incident_description' => 'Device stolen from employee vehicle in Delhi. FIR filed at CP PS (CR-2026-4421). IMEI block requested.',
            'claimed_amount'       => 11500,
            'claim_date'           => now()->subDays(4)->toDateString(),
            'status'               => 'under_review',
            'remarks'              => 'FIR copy submitted. Awaiting insurer adjuster.',
        ]);

        InsuranceClaim::firstOrCreate(['claim_number' => 'CLM-DEMO-0002'], [
            'device_id'            => $devicePool[0]->id,
            'insurance_policy_id'  => $policy1->id,
            'raised_by'            => $opsUser->id,
            'incident_date'        => now()->subDays(10)->toDateString(),
            'incident_type'        => 'Accidental Damage — Screen',
            'incident_description' => 'Screen cracked due to accidental drop. Device still functional but screen needs replacement.',
            'claimed_amount'       => 3500,
            'approved_amount'      => 3150,
            'claim_date'           => now()->subDays(9)->toDateString(),
            'status'               => 'approved',
            'remarks'              => 'Approved 90% of claim. 10% deductible applied.',
        ]);

        $this->command->info('');
        $this->command->info('✓ Demo data seeded successfully!');
        $this->command->table(
            ['Module', 'Total Records'],
            [
                ['Users',              User::count()],
                ['Vendors',            Vendor::count()],
                ['Clients',            Client::count()],
                ['Client Projects',    ClientProject::count()],
                ['Employees',          Employee::count()],
                ['Device Models',      DeviceModel::count()],
                ['Demand Requests',    DemandRequest::count()],
                ['RFQs',               Rfq::count()],
                ['Purchase Orders',    PurchaseOrder::count()],
                ['GRNs',               Grn::count()],
                ['Devices',            Device::count()],
                ['Dispatch Batches',   DispatchBatch::count()],
                ['Handovers',          DeviceHandover::count()],
                ['Tickets',            Ticket::count()],
                ['Repair Orders',      RepairOrder::count()],
                ['Recovery Cases',     RecoveryCase::count()],
                ['Insurance Policies', InsurancePolicy::count()],
                ['Insurance Claims',   InsuranceClaim::count()],
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientProject;
use App\Models\CourierPartner;
use App\Models\DeviceBrand;
use App\Models\DeviceCategory;
use App\Models\DeviceModel;
use App\Models\InsuranceProvider;
use App\Models\Location;
use App\Models\Role;
use App\Models\ServiceCenter;
use App\Models\TicketCategory;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = [
            ['name' => 'Super Admin',    'slug' => 'super_admin'],
            ['name' => 'Admin',          'slug' => 'admin'],
            ['name' => 'Procurement',    'slug' => 'procurement'],
            ['name' => 'Warehouse',      'slug' => 'warehouse'],
            ['name' => 'Operations',     'slug' => 'operations'],
            ['name' => 'Service Desk',   'slug' => 'service_desk'],
            ['name' => 'Recovery Agent', 'slug' => 'recovery_agent'],
            ['name' => 'Finance',        'slug' => 'finance'],
            ['name' => 'Viewer',         'slug' => 'viewer'],
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        // Default admin user
        $adminRole = Role::where('slug', 'super_admin')->first();
        User::firstOrCreate(['email' => 'admin@assettrack.in'], [
            'name' => 'System Admin',
            'password' => Hash::make('Admin@1234'),
            'role_id' => $adminRole?->id,
            'is_active' => true,
        ]);

        // Locations
        $locations = [
            ['name' => 'Head Office - Mumbai',  'code' => 'HO-MUM',  'type' => 'office',     'city' => 'Mumbai',     'state' => 'Maharashtra'],
            ['name' => 'Warehouse - Delhi',     'code' => 'WH-DEL',  'type' => 'warehouse',  'city' => 'Delhi',      'state' => 'Delhi'],
            ['name' => 'Warehouse - Bangalore', 'code' => 'WH-BLR',  'type' => 'warehouse',  'city' => 'Bangalore',  'state' => 'Karnataka'],
            ['name' => 'Warehouse - Chennai',   'code' => 'WH-CHN',  'type' => 'warehouse',  'city' => 'Chennai',    'state' => 'Tamil Nadu'],
            ['name' => 'Warehouse - Hyderabad', 'code' => 'WH-HYD',  'type' => 'warehouse',  'city' => 'Hyderabad',  'state' => 'Telangana'],
        ];
        foreach ($locations as $loc) {
            Location::firstOrCreate(['code' => $loc['code']], $loc);
        }

        // Device Brands
        $brands = ['Samsung', 'Apple', 'Motorola', 'Nokia', 'Realme', 'Xiaomi', 'OnePlus', 'Vivo', 'Oppo'];
        foreach ($brands as $brand) {
            DeviceBrand::firstOrCreate(['name' => $brand]);
        }

        // Device Categories
        $categories = [
            ['name' => 'Smartphone',  'slug' => 'smartphone'],
            ['name' => 'Tablet',      'slug' => 'tablet'],
            ['name' => 'Laptop',      'slug' => 'laptop'],
            ['name' => 'Feature Phone', 'slug' => 'feature_phone'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
        ];
        foreach ($categories as $cat) {
            DeviceCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Device Models (Samsung examples)
        $samsung = DeviceBrand::where('name', 'Samsung')->first();
        $smartphone = DeviceCategory::where('slug', 'smartphone')->first();
        if ($samsung && $smartphone) {
            $models = [
                ['model_name' => 'Galaxy A14', 'model_number' => 'SM-A145F', 'os' => 'Android', 'ram' => '4GB', 'storage' => '64GB', 'standard_cost' => 11999],
                ['model_name' => 'Galaxy A24', 'model_number' => 'SM-A245F', 'os' => 'Android', 'ram' => '8GB', 'storage' => '128GB', 'standard_cost' => 17999],
                ['model_name' => 'Galaxy A54', 'model_number' => 'SM-A546E', 'os' => 'Android', 'ram' => '8GB', 'storage' => '256GB', 'standard_cost' => 38999],
                ['model_name' => 'Galaxy M14', 'model_number' => 'SM-M146B', 'os' => 'Android', 'ram' => '4GB', 'storage' => '128GB', 'standard_cost' => 13999],
            ];
            foreach ($models as $model) {
                DeviceModel::firstOrCreate(
                    ['model_number' => $model['model_number']],
                    array_merge($model, ['brand_id' => $samsung->id, 'category_id' => $smartphone->id])
                );
            }
        }

        // Ticket Categories
        $ticketCats = [
            ['name' => 'Device Not Working',  'slug' => 'device_not_working',  'priority' => 'high',     'sla_hours' => 4],
            ['name' => 'Broken Screen',       'slug' => 'broken_screen',        'priority' => 'medium',   'sla_hours' => 24],
            ['name' => 'Lost Device',         'slug' => 'lost_device',          'priority' => 'critical', 'sla_hours' => 2],
            ['name' => 'MDM Issue',           'slug' => 'mdm_issue',            'priority' => 'high',     'sla_hours' => 8],
            ['name' => 'App Issue',           'slug' => 'app_issue',            'priority' => 'medium',   'sla_hours' => 24],
            ['name' => 'SIM Issue',           'slug' => 'sim_issue',            'priority' => 'high',     'sla_hours' => 8],
            ['name' => 'Accessory Request',   'slug' => 'accessory_request',    'priority' => 'low',      'sla_hours' => 48],
            ['name' => 'Battery Issue',       'slug' => 'battery_issue',        'priority' => 'medium',   'sla_hours' => 24],
            ['name' => 'Software Issue',      'slug' => 'software_issue',       'priority' => 'medium',   'sla_hours' => 24],
        ];
        foreach ($ticketCats as $cat) {
            TicketCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Courier Partners
        $couriers = [
            ['name' => 'BlueDart',       'code' => 'BD',  'tracking_url' => 'https://www.bluedart.com/tracking'],
            ['name' => 'Delhivery',      'code' => 'DLV', 'tracking_url' => 'https://www.delhivery.com/track'],
            ['name' => 'DTDC',           'code' => 'DTDC','tracking_url' => 'https://www.dtdc.in/track'],
            ['name' => 'FedEx',          'code' => 'FDX', 'tracking_url' => 'https://www.fedex.com/tracking'],
            ['name' => 'Ecom Express',   'code' => 'ECE', 'tracking_url' => 'https://ecomexpress.in/tracking'],
        ];
        foreach ($couriers as $courier) {
            CourierPartner::firstOrCreate(['code' => $courier['code']], $courier);
        }

        // Service Centers
        $serviceCenters = [
            ['name' => 'Samsung ASC - Mumbai',    'code' => 'ASC-MUM-SAM', 'type' => 'authorized', 'city' => 'Mumbai',    'state' => 'Maharashtra'],
            ['name' => 'Samsung ASC - Delhi',     'code' => 'ASC-DEL-SAM', 'type' => 'authorized', 'city' => 'Delhi',     'state' => 'Delhi'],
            ['name' => 'Samsung ASC - Bangalore', 'code' => 'ASC-BLR-SAM', 'type' => 'authorized', 'city' => 'Bangalore', 'state' => 'Karnataka'],
        ];
        foreach ($serviceCenters as $sc) {
            ServiceCenter::firstOrCreate(['code' => $sc['code']], $sc);
        }

        // Insurance Providers
        $insurers = [
            ['name' => 'New India Assurance',   'code' => 'NIA'],
            ['name' => 'HDFC Ergo',             'code' => 'HDFC-E'],
            ['name' => 'ICICI Lombard',         'code' => 'ICICI-L'],
            ['name' => 'Bajaj Allianz',         'code' => 'BAJAJ-A'],
        ];
        foreach ($insurers as $ins) {
            InsuranceProvider::firstOrCreate(['code' => $ins['code']], $ins);
        }

        // Sample Vendor
        Vendor::firstOrCreate(['code' => 'VND-001'], [
            'name' => 'TechDistributors Pvt Ltd',
            'contact_person' => 'Rajesh Kumar',
            'email' => 'rajesh@techdist.in',
            'phone' => '9999000001',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'status' => 'active',
        ]);

        // Sample Client
        $client = Client::firstOrCreate(['code' => 'CLI-001'], [
            'name' => 'Acme Pharma Ltd',
            'contact_person' => 'Suresh Sharma',
            'email' => 'suresh@acmepharma.in',
            'phone' => '9888000001',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'status' => 'active',
        ]);

        ClientProject::firstOrCreate(['code' => 'ACME-PRJ-001'], [
            'client_id' => $client->id,
            'name' => 'Field Force Mobility',
            'description' => 'Mobile device deployment for field sales team',
            'region' => 'All India',
            'status' => 'active',
        ]);
    }
}

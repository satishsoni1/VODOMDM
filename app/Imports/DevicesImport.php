<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Device;
use App\Models\DeviceBrand;
use App\Models\DeviceCategory;
use App\Models\DeviceEvent;
use App\Models\DeviceModel;
use App\Models\Location;
use App\Models\MdmDevice;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class DevicesImport implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public int $importedCount   = 0;
    public int $updatedCount    = 0;
    public int $skippedCount    = 0;
    public int $mdmMatchedCount = 0;
    public array $errors        = [];

    private array $brandCache    = [];
    private array $categoryCache = [];
    private array $modelCache    = [];
    private array $locationCache = [];
    private array $vendorCache   = [];
    private array $clientCache   = [];

    public function chunkSize(): int
    {
        return 100;
    }

    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data     = $row->toCollection();

        $assetTag     = trim($data['asset_tag']     ?? '');
        $serialNumber = trim($data['serial_number'] ?? '');
        $imei1        = trim($data['imei1']         ?? '');
        $imei2        = trim($data['imei2']         ?? '');

        if ($assetTag === '' && $serialNumber === '' && $imei1 === '' && $imei2 === '') {
            $this->errors[] = "Row {$rowIndex}: at least one of asset_tag, serial_number, imei1, imei2 is required — skipped.";
            $this->skippedCount++;
            return;
        }

        try {
            $device = $this->resolveDevice($assetTag, $serialNumber, $imei1, $imei2);
            $isNew  = $device === null;

            $modelId    = $this->resolveDeviceModel($data);
            $locationId = $this->resolveWarehouse($data);
            $vendorId   = $this->resolveVendor($data);
            $clientId   = $this->resolveClient($data);

            $clientCode = trim($data['client_code'] ?? $data['company_code'] ?? '');
            if ($clientCode !== '' && $clientId === null) {
                $identifier = $assetTag ?: $serialNumber ?: $imei1 ?: $imei2;
                $this->errors[] = "Row {$rowIndex} ({$identifier}): client_code '{$clientCode}' not found — device saved without a client link.";
            }

            $attrs = array_filter([
                'asset_tag'           => $assetTag ?: null,
                'serial_number'       => $serialNumber ?: null,
                'imei1'               => $imei1 ?: null,
                'imei2'               => $imei2 ?: null,
                'device_model_id'     => $modelId,
                'vendor_id'           => $vendorId,
                'client_id'           => $clientId,
                'box_number'          => trim($data['box_number'] ?? '') ?: null,
                'color'               => trim($data['color'] ?? '') ?: null,
                'purchase_date'       => $this->parseDate($data['purchase_date'] ?? null),
                'purchase_price'      => is_numeric($data['purchase_price'] ?? null) ? $data['purchase_price'] : null,
                'warranty_months'     => is_numeric($data['warranty_months'] ?? null) ? $data['warranty_months'] : null,
                'condition'           => $this->mapCondition($data['condition'] ?? null),
                'notes'               => trim($data['notes'] ?? '') ?: null,
            ], fn ($v) => $v !== null);

            if ($locationId !== null) {
                $attrs['current_location_id'] = $locationId;
            }

            if ($isNew) {
                $attrs['lifecycle_status'] = $attrs['lifecycle_status'] ?? 'in_stock';
                $attrs['condition']        = $attrs['condition'] ?? 'good';
                $device = Device::create($attrs);

                DeviceEvent::create([
                    'device_id'   => $device->id,
                    'event_type'  => 'imported',
                    'to_status'   => $device->lifecycle_status,
                    'description' => 'Device created via bulk import',
                    'event_at'    => now(),
                ]);

                $this->importedCount++;
            } else {
                $device->update($attrs);
                $this->updatedCount++;
            }

            if ($this->linkMdmDevice($device)) {
                $this->mdmMatchedCount++;
            }
        } catch (\Throwable $e) {
            $identifier = $assetTag ?: $serialNumber ?: $imei1 ?: $imei2;
            $this->errors[] = "Row {$rowIndex} ({$identifier}): " . $e->getMessage();
            $this->skippedCount++;
        }
    }

    private function resolveDevice(string $assetTag, string $serialNumber, string $imei1, string $imei2): ?Device
    {
        $query = Device::query();
        $query->where(function ($q) use ($assetTag, $serialNumber, $imei1, $imei2) {
            $q->whereRaw('1 = 0');
            if ($assetTag !== '')     $q->orWhere('asset_tag', $assetTag);
            if ($serialNumber !== '') $q->orWhere('serial_number', $serialNumber);
            if ($imei1 !== '')        $q->orWhere('imei1', $imei1)->orWhere('imei2', $imei1);
            if ($imei2 !== '')        $q->orWhere('imei1', $imei2)->orWhere('imei2', $imei2);
        });

        return $query->first();
    }

    private function resolveDeviceModel(Collection $data): ?int
    {
        $modelName = trim($data['model_name'] ?? '');
        if ($modelName === '') {
            return null;
        }

        $brandName = trim($data['brand'] ?? '') ?: 'Unbranded';
        $brandKey  = strtolower($brandName);
        if (!array_key_exists($brandKey, $this->brandCache)) {
            $brand = DeviceBrand::firstOrCreate(['name' => $brandName], ['is_active' => true]);
            $this->brandCache[$brandKey] = $brand->id;
        }
        $brandId = $this->brandCache[$brandKey];

        $categoryName = trim($data['category'] ?? '') ?: 'Uncategorized';
        $categoryKey  = strtolower($categoryName);
        if (!array_key_exists($categoryKey, $this->categoryCache)) {
            $category = DeviceCategory::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName), 'is_active' => true]
            );
            $this->categoryCache[$categoryKey] = $category->id;
        }
        $categoryId = $this->categoryCache[$categoryKey];

        $modelKey = $brandId . '|' . strtolower($modelName);
        if (!array_key_exists($modelKey, $this->modelCache)) {
            $model = DeviceModel::firstOrCreate(
                ['brand_id' => $brandId, 'model_name' => $modelName],
                [
                    'category_id'   => $categoryId,
                    'model_number'  => trim($data['model_number'] ?? '') ?: null,
                    'is_active'     => true,
                ]
            );
            $this->modelCache[$modelKey] = $model->id;
        }

        return $this->modelCache[$modelKey];
    }

    private function resolveWarehouse(Collection $data): ?int
    {
        $code = trim($data['warehouse_code'] ?? '');
        $name = trim($data['warehouse_name'] ?? '');
        if ($code === '' && $name === '') {
            return null;
        }

        $cacheKey = strtolower($code ?: $name);
        if (array_key_exists($cacheKey, $this->locationCache)) {
            return $this->locationCache[$cacheKey];
        }

        $location = null;
        if ($code !== '') {
            $location = Location::where('code', $code)->first();
        }
        if (!$location && $name !== '') {
            $location = Location::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        }

        if (!$location) {
            $location = Location::create([
                'name' => $name ?: $code,
                'code' => $code ?: strtoupper(Str::slug($name, '_')),
                'type' => 'warehouse',
                'is_active' => true,
            ]);
        }

        $this->locationCache[$cacheKey] = $location->id;

        return $location->id;
    }

    private function resolveVendor(Collection $data): ?int
    {
        $code = trim($data['vendor_code'] ?? '');
        $name = trim($data['vendor_name'] ?? '');
        if ($code === '' && $name === '') {
            return null;
        }

        $cacheKey = strtolower($code ?: $name);
        if (array_key_exists($cacheKey, $this->vendorCache)) {
            return $this->vendorCache[$cacheKey];
        }

        $vendor = null;
        if ($code !== '') {
            $vendor = Vendor::where('code', $code)->first();
        }
        if (!$vendor && $name !== '') {
            $vendor = Vendor::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        }

        if (!$vendor) {
            $vendor = Vendor::create([
                'name'   => $name ?: $code,
                'code'   => $code ?: null,
                'status' => 'active',
            ]);
        }

        $this->vendorCache[$cacheKey] = $vendor->id;

        return $vendor->id;
    }

    private function resolveClient(Collection $data): ?int
    {
        $code = trim($data['client_code'] ?? $data['company_code'] ?? '');
        if ($code === '') {
            return null;
        }

        $cacheKey = strtolower($code);
        if (!array_key_exists($cacheKey, $this->clientCache)) {
            $client = Client::where('code', $code)->first();
            $this->clientCache[$cacheKey] = $client?->id;
        }

        return $this->clientCache[$cacheKey];
    }

    private function linkMdmDevice(Device $device): bool
    {
        $query = MdmDevice::whereNull('local_device_id');
        $query->where(function ($q) use ($device) {
            $q->whereRaw('1 = 0');
            if ($device->imei1)         $q->orWhere('imei', $device->imei1);
            if ($device->imei2)         $q->orWhere('imei', $device->imei2);
            if ($device->serial_number) $q->orWhere('serial_number', $device->serial_number);
        });

        $mdmDevice = $query->first();
        if (!$mdmDevice) {
            return false;
        }

        $mdmDevice->update([
            'local_device_id'   => $device->id,
            'local_employee_id' => $device->current_employee_id ?: $mdmDevice->local_employee_id,
        ]);

        return true;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;
        if ($value instanceof \DateTimeInterface) return $value->format('Y-m-d');
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapCondition(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['new', 'good', 'fair', 'poor', 'damaged'], true) ? $value : null;
    }
}

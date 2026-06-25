<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Device;
use App\Models\DeviceHandover;
use App\Models\Employee;
use App\Models\OwnershipHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkDeviceAssignImport implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public int $assignedCount = 0;
    public int $skippedCount  = 0;
    public array $errors      = [];

    private array $clientCache   = [];
    private ?int  $handedOverBy;

    public function __construct(?int $handedOverBy = null)
    {
        $this->handedOverBy = $handedOverBy;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function onRow(Row $row): void
    {
        $rowIndex     = $row->getIndex();
        $data         = $row->toCollection();

        $assetTag     = trim($data['asset_tag']     ?? '');
        $serialNumber = trim($data['serial_number'] ?? '');
        $empCode      = trim($data['employee_code'] ?? '');
        $companyCode  = trim($data['company_code']  ?? '');

        if ($empCode === '') {
            $this->errors[] = "Row {$rowIndex}: employee_code is required — skipped.";
            $this->skippedCount++;
            return;
        }

        if ($assetTag === '' && $serialNumber === '') {
            $this->errors[] = "Row {$rowIndex} ({$empCode}): asset_tag or serial_number is required — skipped.";
            $this->skippedCount++;
            return;
        }

        // Resolve device
        $device = null;
        if ($assetTag !== '') {
            $device = Device::where('asset_tag', $assetTag)->first();
        }
        if (!$device && $serialNumber !== '') {
            $device = Device::where('serial_number', $serialNumber)->first();
        }
        if (!$device) {
            $identifier = $assetTag ?: $serialNumber;
            $this->errors[] = "Row {$rowIndex}: Device '{$identifier}' not found — skipped.";
            $this->skippedCount++;
            return;
        }

        // Resolve client
        $clientId = null;
        if ($companyCode !== '') {
            $cacheKey = strtolower($companyCode);
            if (!array_key_exists($cacheKey, $this->clientCache)) {
                $client = Client::where('code', $companyCode)->first();
                $this->clientCache[$cacheKey] = $client?->id;
            }
            $clientId = $this->clientCache[$cacheKey];
            if ($clientId === null) {
                $this->errors[] = "Row {$rowIndex} ({$empCode}): company_code '{$companyCode}' not found — skipped.";
                $this->skippedCount++;
                return;
            }
        }

        // Resolve employee
        $query = Employee::where('employee_code', $empCode);
        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }
        $employee = $query->first();
        if (!$employee) {
            $this->errors[] = "Row {$rowIndex}: Employee '{$empCode}'" .
                ($companyCode ? " (company: {$companyCode})" : '') . " not found — skipped.";
            $this->skippedCount++;
            return;
        }

        // Use employee's client_id if not supplied
        if ($clientId === null) {
            $clientId = $employee->client_id;
        }

        $handoverDate = $this->parseDate($data['handover_date'] ?? null) ?? now()->toDateString();
        $condition    = in_array(trim($data['condition'] ?? ''), ['new', 'good', 'fair', 'poor'])
                            ? trim($data['condition'])
                            : 'good';

        try {
            DeviceHandover::create([
                'handover_number'       => 'HO-' . strtoupper(Str::random(8)),
                'device_id'             => $device->id,
                'employee_id'           => $employee->id,
                'client_id'             => $clientId,
                'handed_over_by'        => $this->handedOverBy,
                'handover_date'         => $handoverDate,
                'handover_location'     => trim($data['handover_location'] ?? '') ?: null,
                'handover_city'         => trim($data['handover_city']     ?? '') ?: null,
                'condition_at_handover' => $condition,
                'accessories_handed'    => trim($data['accessories']       ?? '') ?: null,
                'remarks'               => trim($data['remarks']           ?? '') ?: null,
                'status'                => 'assigned',
            ]);

            $device->update([
                'lifecycle_status'    => 'assigned',
                'current_employee_id' => $employee->id,
                'client_id'           => $clientId ?? $device->client_id,
            ]);

            OwnershipHistory::create([
                'device_id'       => $device->id,
                'employee_id'     => $employee->id,
                'client_id'       => $clientId,
                'ownership_type'  => 'employee',
                'from_date'       => now(),
                'transfer_reason' => 'Bulk device assignment',
                'transferred_by'  => $this->handedOverBy,
            ]);

            $this->assignedCount++;
        } catch (\Throwable $e) {
            $this->errors[] = "Row {$rowIndex} ({$empCode}): " . $e->getMessage();
            $this->skippedCount++;
        }
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
}

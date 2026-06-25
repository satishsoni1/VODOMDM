<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkCompanyAssignImport implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public int $assignedCount = 0;
    public int $skippedCount  = 0;
    public array $errors      = [];

    private array $clientCache = [];

    public function chunkSize(): int
    {
        return 100;
    }

    public function onRow(Row $row): void
    {
        $rowIndex    = $row->getIndex();
        $data        = $row->toCollection();

        $empCode     = trim($data['employee_code'] ?? '');
        $companyCode = trim($data['company_code']  ?? '');

        if ($empCode === '') {
            $this->errors[] = "Row {$rowIndex}: employee_code is required — skipped.";
            $this->skippedCount++;
            return;
        }

        if ($companyCode === '') {
            $this->errors[] = "Row {$rowIndex} ({$empCode}): company_code is required — skipped.";
            $this->skippedCount++;
            return;
        }

        // Resolve client with cache
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

        $employee = Employee::where('employee_code', $empCode)->first();
        if (!$employee) {
            $this->errors[] = "Row {$rowIndex}: Employee '{$empCode}' not found — skipped.";
            $this->skippedCount++;
            return;
        }

        try {
            $employee->update(['client_id' => $clientId]);
            $this->assignedCount++;
        } catch (\Throwable $e) {
            $this->errors[] = "Row {$rowIndex} ({$empCode}): " . $e->getMessage();
            $this->skippedCount++;
        }
    }
}

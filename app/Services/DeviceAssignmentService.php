<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceHandover;
use App\Models\Employee;
use App\Models\OwnershipHistory;
use Illuminate\Support\Str;

class DeviceAssignmentService
{
    /**
     * Assign a device to an employee: creates the handover record, updates the
     * device's current owner/status/group, and logs ownership history.
     */
    public static function assign(Device $device, Employee $employee, array $options = []): DeviceHandover
    {
        $clientId = $options['client_id'] ?? $employee->client_id;
        $group    = $options['group'] ?? null;

        $handover = DeviceHandover::create([
            'handover_number'       => 'HO-' . strtoupper(Str::random(8)),
            'device_id'             => $device->id,
            'employee_id'           => $employee->id,
            'client_id'             => $clientId,
            'handed_over_by'        => $options['handed_over_by'] ?? null,
            'handover_date'         => $options['handover_date'] ?? now()->toDateString(),
            'handover_location'     => $options['handover_location'] ?? null,
            'handover_city'         => $options['handover_city'] ?? null,
            'condition_at_handover' => $options['condition'] ?? 'good',
            'accessories_handed'    => $options['accessories'] ?? null,
            'remarks'               => $options['remarks'] ?? null,
            'assignment_group'      => $group,
            'status'                => 'assigned',
        ]);

        $device->update([
            'lifecycle_status'    => 'assigned',
            'current_employee_id' => $employee->id,
            'client_id'           => $clientId ?? $device->client_id,
            'current_group'       => $group ?: $device->current_group,
        ]);

        OwnershipHistory::create([
            'device_id'       => $device->id,
            'employee_id'     => $employee->id,
            'client_id'       => $clientId,
            'ownership_type'  => 'employee',
            'from_date'       => now(),
            'transfer_reason' => $options['transfer_reason'] ?? 'Device assignment',
            'transferred_by'  => $options['handed_over_by'] ?? null,
        ]);

        return $handover;
    }
}

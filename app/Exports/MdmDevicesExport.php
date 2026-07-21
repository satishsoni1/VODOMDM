<?php

namespace App\Exports;

use App\Models\MdmDevice;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MdmDevicesExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param Builder $query Pre-filtered MdmDevice query (list-page filters already applied).
     * @param bool $useResolvedEmployee When true, falls back to IMEI/serial-matched employee
     *        (MdmDevice::resolvedEmployee()) instead of only the stored local_employee_id link —
     *        matches how the client portal displays the Employee column.
     */
    public function __construct(
        private Builder $query,
        private bool $useResolvedEmployee = false,
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Device #', 'IMEI', 'Serial Number', 'Model', 'Status',
            'Group', 'Configuration', 'Employee', 'Employee Code', 'Client',
            'Android Version', 'Last Sync', 'Latitude', 'Longitude', 'Permission Status',
        ];
    }

    public function map($d): array
    {
        /** @var MdmDevice $d */
        $employee = $this->useResolvedEmployee ? $d->resolvedEmployee() : $d->employee;
        $lat = $d->locationLatest?->latitude ?? $d->latitude;
        $lng = $d->locationLatest?->longitude ?? $d->longitude;

        return [
            $d->pg_number,
            $d->imei,
            $d->serial_number,
            $d->model,
            $d->isOnline() ? 'Online' : 'Offline',
            $d->mdm_group,
            $d->configuration,
            $employee?->name,
            $employee?->employee_code,
            $employee?->client?->name,
            $d->android_version,
            $d->sync_time?->format('d M Y H:i'),
            $lat,
            $lng,
            $d->permission_status,
        ];
    }
}

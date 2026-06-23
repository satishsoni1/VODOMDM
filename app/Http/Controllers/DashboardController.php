<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Employee;
use App\Models\InsuranceClaim;
use App\Models\InsurancePolicy;
use App\Models\PurchaseOrder;
use App\Models\RecoveryCase;
use App\Models\RepairOrder;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'devices' => [
                'total'        => Device::count(),
                'in_stock'     => Device::where('lifecycle_status', 'in_stock')->count(),
                'assigned'     => Device::where('lifecycle_status', 'assigned')->count(),
                'in_transit'   => Device::where('lifecycle_status', 'in_transit')->count(),
                'under_repair' => Device::where('lifecycle_status', 'under_repair')->count(),
                'lost'         => Device::where('lifecycle_status', 'lost')->count(),
            ],
            'procurement' => [
                'pending_pos'    => PurchaseOrder::whereNotIn('status', ['completed', 'cancelled'])->count(),
                'total_po_value' => PurchaseOrder::whereNotIn('status', ['cancelled'])->sum('grand_total'),
            ],
            'operations' => [
                'active_employees' => Employee::where('status', 'active')->count(),
                'open_tickets'     => Ticket::whereNotIn('status', ['closed', 'cancelled'])->count(),
                'sla_breached'     => Ticket::whereNotIn('status', ['resolved', 'closed'])
                                        ->whereNotNull('sla_due_at')
                                        ->where('sla_due_at', '<', now())->count(),
            ],
            'recovery' => [
                'open_cases' => RecoveryCase::where('status', 'open')->count(),
                'pending'    => RecoveryCase::whereNotIn('status', ['recovered', 'closed', 'written_off'])->count(),
                'overdue'    => RecoveryCase::whereNotIn('status', ['recovered', 'closed', 'written_off'])
                                    ->where('recovery_due_date', '<', now())->count(),
            ],
            'insurance' => [
                'active_policies' => InsurancePolicy::where('status', 'active')->count(),
                'expiring_soon'   => InsurancePolicy::where('status', 'active')
                                        ->whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
                'open_claims'     => InsuranceClaim::whereNotIn('status', ['settled', 'closed', 'rejected'])->count(),
            ],
            'service' => [
                'under_repair' => RepairOrder::whereNotIn('status', ['returned'])->count(),
            ],
        ];

        $devicesByStatus = Device::select('lifecycle_status', DB::raw('count(*) as count'))
            ->groupBy('lifecycle_status')
            ->pluck('count', 'lifecycle_status');

        $recentTickets = Ticket::with(['device', 'employee', 'category', 'raisedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingRecoveries = RecoveryCase::with(['device', 'employee', 'client'])
            ->whereNotIn('status', ['recovered', 'closed'])
            ->orderBy('recovery_due_date')
            ->limit(10)
            ->get();

        return view('dashboard', compact('stats', 'devicesByStatus', 'recentTickets', 'pendingRecoveries'));
    }
}

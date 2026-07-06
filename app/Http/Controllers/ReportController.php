<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DemandRequest;
use App\Models\DispatchBatch;
use App\Models\Employee;
use App\Models\Grn;
use App\Models\InsuranceClaim;
use App\Models\InsurancePolicy;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\RecoveryCase;
use App\Models\RepairOrder;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $summary = [
            'total_devices'    => Device::count(),
            'active_devices'   => Device::where('lifecycle_status', 'active')->count(),
            'open_tickets'     => Ticket::whereNotIn('status', ['resolved', 'closed', 'cancelled'])->count(),
            'open_recovery'    => RecoveryCase::whereIn('status', ['open', 'contacted', 'pickup_scheduled'])->count(),
            'active_insurance' => InsurancePolicy::where('status', 'active')->count(),
            'active_repairs'   => RepairOrder::whereNotIn('status', ['returned', 'unrepairable'])->count(),
            'pending_po'       => PurchaseOrder::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_po_value'   => PurchaseOrder::sum('grand_total'),
        ];

        return view('reports.index', compact('summary'));
    }

    public function inventory(Request $request)
    {
        $byStatus = Device::select('lifecycle_status', DB::raw('count(*) as count'))
            ->groupBy('lifecycle_status')->orderByDesc('count')->get();

        $byLocation = Device::with('currentLocation')
            ->select('current_location_id', DB::raw('count(*) as count'))
            ->groupBy('current_location_id')->orderByDesc('count')->get();

        $byClient = Device::with('client')
            ->select('client_id', DB::raw('count(*) as count'))
            ->whereNotNull('client_id')
            ->groupBy('client_id')->orderByDesc('count')->get();

        $recentGrns = Grn::with(['purchaseOrder.vendor', 'location'])->latest('received_date')->limit(10)->get();

        return view('reports.inventory', compact('byStatus', 'byLocation', 'byClient', 'recentGrns'));
    }

    public function procurement(Request $request)
    {
        $poByStatus = PurchaseOrder::select('status', DB::raw('count(*) as count'), DB::raw('sum(grand_total) as total'))
            ->groupBy('status')->get();

        $poByVendor = PurchaseOrder::with('vendor')
            ->select('vendor_id', DB::raw('count(*) as count'), DB::raw('sum(grand_total) as total'))
            ->groupBy('vendor_id')->orderByDesc('total')->limit(10)->get();

        $recentPOs = PurchaseOrder::with(['vendor', 'createdBy'])->latest()->limit(10)->get();

        $demandStats = [
            'draft'          => DemandRequest::where('status', 'draft')->count(),
            'submitted'      => DemandRequest::where('status', 'submitted')->count(),
            'approved'       => DemandRequest::where('status', 'approved')->count(),
            'converted_to_po'=> DemandRequest::where('status', 'converted_to_po')->count(),
        ];

        return view('reports.procurement', compact('poByStatus', 'poByVendor', 'recentPOs', 'demandStats'));
    }

    public function recovery(Request $request)
    {
        $byStatus = RecoveryCase::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->get();

        $byReason = RecoveryCase::select('trigger_reason', DB::raw('count(*) as count'))
            ->groupBy('trigger_reason')->orderByDesc('count')->get();

        $overdue = RecoveryCase::with(['device.model.brand', 'employee', 'client'])
            ->whereIn('status', ['open', 'contacted', 'pickup_scheduled'])
            ->where('recovery_due_date', '<', today())
            ->latest()->get();

        $recentCases = RecoveryCase::with(['device.model.brand', 'employee', 'client'])
            ->latest()->limit(10)->get();

        return view('reports.recovery', compact('byStatus', 'byReason', 'overdue', 'recentCases'));
    }

    public function insurance(Request $request)
    {
        $byStatus = InsurancePolicy::select('status', DB::raw('count(*) as count'), DB::raw('sum(sum_insured) as total_sum'))
            ->groupBy('status')->get();

        $claimsByStatus = InsuranceClaim::select('status', DB::raw('count(*) as count'), DB::raw('sum(claimed_amount) as total_claimed'))
            ->groupBy('status')->get();

        $expiringSoon = InsurancePolicy::with(['provider', 'client'])
            ->where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays(60))
            ->orderBy('expiry_date')->get();

        $recentClaims = InsuranceClaim::with(['device.model.brand', 'policy.provider'])->latest()->limit(10)->get();

        return view('reports.insurance', compact('byStatus', 'claimsByStatus', 'expiringSoon', 'recentClaims'));
    }

    public function financial(Request $request)
    {
        $poSummary = [
            'total_orders'   => PurchaseOrder::count(),
            'total_value'    => PurchaseOrder::sum('grand_total'),
            'paid_value'     => PurchaseOrder::where('status', 'completed')->sum('grand_total'),
            'pending_value'  => PurchaseOrder::whereNotIn('status', ['completed', 'cancelled'])->sum('grand_total'),
        ];

        $repairCosts = RepairOrder::select(
            DB::raw('sum(estimated_cost) as estimated'),
            DB::raw('sum(actual_cost) as actual'),
            DB::raw('count(*) as count')
        )->first();

        $insuranceSummary = [
            'total_premiums' => InsurancePolicy::sum('premium_amount'),
            'total_claimed'  => InsuranceClaim::sum('claimed_amount'),
            'total_settled'  => InsuranceClaim::sum('settled_amount'),
        ];

        $poByMonth = PurchaseOrder::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('count(*) as count'),
            DB::raw('sum(grand_total) as total')
        )->groupBy('month')->orderBy('month')->limit(12)->get();

        return view('reports.financial', compact('poSummary', 'repairCosts', 'insuranceSummary', 'poByMonth'));
    }

    public function deviceLifecycle(Request $request)
    {
        $lifecycleCount = Device::select('lifecycle_status', DB::raw('count(*) as count'))
            ->groupBy('lifecycle_status')->orderByDesc('count')->get();

        $conditionCount = Device::select('condition', DB::raw('count(*) as count'))
            ->groupBy('condition')->orderByDesc('count')->get();

        $ageGroups = Device::selectRaw("
            CASE
                WHEN purchase_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 'Under 1 year'
                WHEN purchase_date >= DATE_SUB(NOW(), INTERVAL 2 YEAR) THEN '1-2 years'
                WHEN purchase_date >= DATE_SUB(NOW(), INTERVAL 3 YEAR) THEN '2-3 years'
                ELSE 'Over 3 years'
            END as age_group,
            count(*) as count
        ")->whereNotNull('purchase_date')->groupBy('age_group')->get();

        $warrantyExpiring = Device::with('model.brand')
            ->whereNotNull('warranty_expiry')
            ->where('warranty_expiry', '<=', now()->addDays(90))
            ->where('warranty_expiry', '>=', today())
            ->orderBy('warranty_expiry')->get();

        return view('reports.device_lifecycle', compact('lifecycleCount', 'conditionCount', 'ageGroups', 'warrantyExpiring'));
    }

    public function deviceTracking(Request $request)
    {
        $query = Device::with(['model.brand', 'currentEmployee', 'currentLocation', 'mdmDevice', 'latestHandover'])
            ->orderBy('updated_at', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('current_employee_id', $request->employee_id);
        }

        if ($request->filled('location_id')) {
            $query->where('current_location_id', $request->location_id);
        }

        if ($request->filled('group')) {
            $query->where('current_group', 'like', '%' . $request->group . '%');
        }

        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->status);
        }

        if ($request->filled('mdm')) {
            if ($request->mdm === 'installed') {
                $query->whereHas('mdmDevice');
            } elseif ($request->mdm === 'not_installed') {
                $query->whereDoesntHave('mdmDevice');
            }
        }

        $devices   = $query->paginate(30)->withQueryString();
        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_code']);
        $locations = Location::where('type', 'warehouse')->orderBy('name')->get(['id', 'name']);

        return view('reports.device_tracking', compact('devices', 'employees', 'locations'));
    }
}

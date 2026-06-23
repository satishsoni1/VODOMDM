<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Employee;
use App\Models\InsuranceClaim;
use App\Models\PurchaseOrder;
use App\Models\RecoveryCase;
use App\Models\Ticket;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q       = $request->get('q', '');
        $results = [];

        if (strlen($q) >= 2) {
            $like = "%$q%";

            $results['devices'] = Device::with(['model.brand', 'currentEmployee', 'client'])
                ->where(function ($query) use ($like) {
                    $query->where('serial_number', 'like', $like)
                          ->orWhere('asset_tag', 'like', $like)
                          ->orWhere('imei1', 'like', $like)
                          ->orWhere('imei2', 'like', $like);
                })->limit(10)->get();

            $results['employees'] = Employee::with('client')
                ->where(function ($query) use ($like) {
                    $query->where('employee_code', 'like', $like)
                          ->orWhere('name', 'like', $like)
                          ->orWhere('phone', 'like', $like);
                })->limit(10)->get();

            $results['tickets'] = Ticket::with(['device', 'employee'])
                ->where('ticket_number', 'like', $like)
                ->orWhere('subject', 'like', $like)
                ->limit(10)->get();

            $results['purchase_orders'] = PurchaseOrder::with('vendor')
                ->where('po_number', 'like', $like)
                ->limit(5)->get();

            $results['claims'] = InsuranceClaim::with('device')
                ->where('claim_number', 'like', $like)
                ->limit(5)->get();

            $results['recovery'] = RecoveryCase::with(['device', 'employee'])
                ->where('case_number', 'like', $like)
                ->limit(5)->get();
        }

        return view('search.results', compact('q', 'results'));
    }
}

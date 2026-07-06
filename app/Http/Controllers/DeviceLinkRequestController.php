<?php

namespace App\Http\Controllers;

use App\Models\DeviceLinkRequest;
use App\Services\DeviceAssignmentService;
use Illuminate\Http\Request;

class DeviceLinkRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $linkRequests = DeviceLinkRequest::with(['device.model.brand', 'employee', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('link_requests.index', compact('linkRequests', 'status'));
    }

    public function approve(DeviceLinkRequest $linkRequest)
    {
        if ($linkRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        if ($linkRequest->device->current_employee_id) {
            return back()->with('error', 'This device is already assigned to someone else.');
        }

        DeviceAssignmentService::assign($linkRequest->device, $linkRequest->employee, [
            'handed_over_by' => auth()->id(),
            'transfer_reason' => 'Linked via QR scan self-link request',
        ]);

        $linkRequest->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Link request approved — device has been assigned.');
    }

    public function reject(Request $request, DeviceLinkRequest $linkRequest)
    {
        if ($linkRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $linkRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Link request rejected.');
    }
}

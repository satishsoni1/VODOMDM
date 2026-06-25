<?php

namespace App\Http\Controllers;

use App\Models\ApiLog;
use Illuminate\Http\Request;

class ApiLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ApiLog::with('triggeredBy')
            ->whereNull('parent_log_id')   // top-level runs only
            ->orderByDesc('created_at');

        if ($request->filled('type'))   $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);

        $logs = $query->paginate(30)->withQueryString();

        $stats = [
            'total'   => ApiLog::whereNull('parent_log_id')->count(),
            'success' => ApiLog::whereNull('parent_log_id')->where('status', 'success')->count(),
            'failed'  => ApiLog::whereNull('parent_log_id')->where('status', 'failed')->count(),
            'running' => ApiLog::whereNull('parent_log_id')->where('status', 'running')->count(),
        ];

        return view('api-logs.index', compact('logs', 'stats'));
    }

    public function show(ApiLog $apiLog)
    {
        $steps = ApiLog::where('parent_log_id', $apiLog->id)
            ->orderBy('created_at')
            ->get();
        return view('api-logs.show', compact('apiLog', 'steps'));
    }
}

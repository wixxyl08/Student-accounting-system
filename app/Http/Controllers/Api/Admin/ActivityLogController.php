<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
{
    $query = ActivityLog::query()->with('user:id,full_name,role');

    if ($entityType = $request->input('entity_type')) {
        $query->where('entity_type', 'like', "%{$entityType}%");
    }

    if ($userId = $request->input('user_id')) {
        $query->where('user_id', $userId);
    }

    if ($action = $request->input('action')) {
        $query->where('action', $action);
    }

    $perPage = min((int) $request->input('per_page', 20), 100);
    $logs = $query->orderBy('id', 'desc')->paginate($perPage);

    return response()->json($logs);
}
}
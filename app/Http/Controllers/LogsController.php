<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogsController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user')->orderByDesc('created_at');

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->get('subject_type'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->get('action'));
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('logs.index', [
            'logs' => $logs,
            'subjectTypeLabels' => ActivityLog::subjectTypeLabels(),
            'actionLabels' => ActivityLog::actionLabels(),
        ]);
    }
}

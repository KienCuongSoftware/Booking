<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('actor:id,name,email')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->string('action')->value().'%');
        }

        if ($request->filled('subject')) {
            $query->where('subject_type', 'like', '%'.$request->string('subject')->value().'%');
        }

        $logs = $query->paginate(40)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}

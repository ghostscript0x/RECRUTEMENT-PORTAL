<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user:id,name,email')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'actions' => AuditLog::select('action')->distinct()->orderBy('action')->pluck('action'),
            'current' => $request->input('action', ''),
        ]);
    }
}

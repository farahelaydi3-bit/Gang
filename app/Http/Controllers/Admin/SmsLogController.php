<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use App\Support\DashboardList;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = DashboardList::search($request);
        $statuses = DashboardList::labels(['pending', 'sent', 'failed', 'delivered']);
        $status = DashboardList::choice($request, 'status', array_keys($statuses));

        $query = SmsLog::with(['user', 'waitingList.groupGame', 'waitingList.court'])
            ->where('admin_id', auth('admin')->id())
            ->when($search, fn ($query) => $query->where(function ($matching) use ($search) {
                $matching->whereAny(['phone', 'message'], 'like', $search)
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $search));
            }))
            ->when($status, fn ($query) => $query->where('status', $status));

        $logs = DashboardList::dates($query, $request, 'created_at')
            ->latest('id')->paginate(DashboardList::PER_PAGE)->withQueryString();

        return view('admins.sms-logs.index', compact('logs', 'statuses'));
    }
}

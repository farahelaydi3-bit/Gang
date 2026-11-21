<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsLog;
use Illuminate\View\View;

class SmsLogController extends Controller
{
    public function index(): View
    {
        $logs = SmsLog::with(['user', 'waitingList.groupGame', 'waitingList.court'])
            ->where('admin_id', auth('admin')->id())
            ->latest()->paginate(25);

        return view('admins.sms-logs.index', compact('logs'));
    }
}

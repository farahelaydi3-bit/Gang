@extends('admins.layout.app')

@section('title', 'إدارة الاشتراكات')
@section('page-title', 'إدارة الاشتراكات')

@section('content')
<div dir="rtl" style="padding:24px;background:white;border-radius:16px">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px">
        <div>
            <h2 style="margin:0 0 8px">اشتراكات ملاعبي</h2>
            <p style="color:#65746b;margin:0">المواعيد الثابتة تظهر أدناه، والطلبات بانتظار الدفع تحجز الفترات مؤقتًا.</p>
        </div>
        @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.create'))
            <a href="{{ route('admin.subscriptions.create') }}" class="admin-button" style="text-decoration:none">+ إضافة اشتراك</a>
        @endif
    </div>

    @if (session('success'))
        <div role="status" style="padding:12px;background:#e8f6ee;color:#17613c;border-radius:8px;margin-bottom:12px">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" style="padding:12px;background:#fff0ef;color:#9e2929;border-radius:8px;margin-bottom:12px">{{ session('error') }}</div>
    @endif

    @if (isset($subscriptionStats) && $subscriptionStats)
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:20px">
            @foreach (['total' => 'جميع الاشتراكات', 'active' => 'نشطة', 'pending' => 'بانتظار الدفع', 'expired' => 'منتهية', 'cancelled' => 'ملغاة'] as $key => $label)
                <div style="border:1px solid #e3eae6;border-radius:12px;padding:14px">
                    <div style="color:#65746b">{{ $label }}</div>
                    <strong style="font-size:24px">{{ $subscriptionStats->{$key} ?? 0 }}</strong>
                </div>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('admin.subscriptions.index') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:end;margin-bottom:20px">
        <label style="display:flex;flex-direction:column;gap:6px;flex:2;min-width:200px">
            <span>البحث عن اشتراك</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="اسم الباقة أو المشترك أو البريد الإلكتروني" style="padding:10px;border:1px solid #ccd7d0;border-radius:8px;width:100%">
        </label>
        <label style="display:flex;flex-direction:column;gap:6px;flex:1;min-width:160px">
            <span>الحالة</span>
            <select name="status" style="padding:10px;border:1px solid #ccd7d0;border-radius:8px;width:100%">
                <option value="">جميع الحالات</option>
                @foreach (['pending' => 'بانتظار الدفع', 'active' => 'نشط', 'expired' => 'منتهي', 'cancelled' => 'ملغى'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="admin-button" style="padding:10px 18px">بحث وتصفية</button>
        <a href="{{ route('admin.subscriptions.index') }}" style="padding:10px">إعادة ضبط</a>
    </form>

    <div style="overflow-x:auto">
        <table style="width:100%;min-width:1100px;border-collapse:collapse;text-align:right">
            <thead>
                <tr>
                    @foreach (['المشترك', 'الملعب', 'الباقة', 'المدة', 'الساعات', 'المواعيد', 'المبلغ', 'الحالة', 'الإجراءات'] as $header)
                        <th scope="col" style="padding:12px;border-bottom:1px solid #e3eae6">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            <strong>{{ $subscription->user?->name ?? 'غير معروف' }}</strong>
                            @if ($subscription->user?->email)
                                <div style="font-size:12px;color:#65746b" dir="ltr">{{ $subscription->user->email }}</div>
                            @endif
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->court?->name ?? 'غير معروف' }}</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->package_name ?? '—' }}</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->duration_days }} يوم</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            @if ($subscription->is_fixed_schedule)
                                {{ number_format((float) $subscription->total_hours, 1) }} محجوزة
                            @else
                                {{ number_format((float) $subscription->remaining_hours, 1) }} متبقية
                            @endif
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            @if ($subscription->is_fixed_schedule)
                                <div dir="ltr">{{ substr((string) $subscription->scheduled_start_time, 0, 5) }}–{{ substr((string) $subscription->scheduled_end_time, 0, 5) }}</div>
                                <details>
                                    <summary>{{ $subscription->bookings->count() }} مواعيد</summary>
                                    <ul style="padding-right:17px;max-height:160px;overflow:auto">
                                        @foreach ($subscription->bookings as $booking)
                                            <li>{{ $booking->booking_date?->format('Y-m-d') ?? $booking->booking_date }} ({{ ['pending' => 'معلّق', 'confirmed' => 'مؤكد', 'completed' => 'مكتمل', 'cancelled' => 'ملغى'][$booking->booking_status] ?? $booking->booking_status }})</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                مرن
                            @endif
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ number_format((float) $subscription->amount, 2) }} د.أ</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            {{ ['pending' => 'بانتظار الدفع', 'active' => 'نشط', 'expired' => 'منتهي', 'cancelled' => 'ملغى'][$subscription->status] ?? $subscription->status }}
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                                @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.show'))
                                    <a href="{{ route('admin.subscriptions.show', $subscription->id) }}">عرض</a>
                                @endif
                                @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.edit'))
                                    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}">تعديل</a>
                                @endif
                                @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.destroy'))
                                    <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الاشتراك؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="border:0;background:none;color:#b42318;cursor:pointer">حذف</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="padding:25px;text-align:center">لا توجد اشتراكات مطابقة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (method_exists($subscriptions, 'hasPages') && $subscriptions->hasPages())
        <div style="margin-top:20px">{{ $subscriptions->links() }}</div>
    @endif
</div>
@endsection

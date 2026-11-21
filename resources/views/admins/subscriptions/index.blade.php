@extends('admins.layout.app')

@section('title', 'إدارة الاشتراكات')
@section('page-title', 'إدارة الاشتراكات')

@section('content')
<div style="padding:24px;background:white;border-radius:16px;direction:rtl">
    <h2>اشتراكات ملاعبي</h2>
    <p style="color:#65746b">المواعيد الثابتة تظهر أدناه؛ الطلبات بانتظار الدفع تحجز الفترات مؤقتًا.</p>
    <div style="overflow-x:auto">
        <table style="width:100%;min-width:820px;border-collapse:collapse;text-align:right">
            <thead>
                <tr>
                    @foreach (['المشترك', 'الملعب', 'المدة', 'الساعات', 'المواعيد', 'المبلغ', 'الحالة'] as $header)
                        <th style="padding:12px;border-bottom:1px solid #e3eae6">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->user?->name ?? 'غير معروف' }}</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->court?->name ?? 'غير معروف' }}</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->duration_days }} يوم</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            @if ($subscription->is_fixed_schedule)
                                {{ number_format((float)$subscription->total_hours,1) }} محجوزة
                            @else
                                {{ number_format($subscription->remaining_hours,1) }} متبقية
                            @endif
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee">
                            @if ($subscription->is_fixed_schedule)
                                <div>{{ substr((string)$subscription->scheduled_start_time,0,5) }}–{{ substr((string)$subscription->scheduled_end_time,0,5) }}</div>
                                <details>
                                    <summary>{{ $subscription->bookings->count() }} مواعيد</summary>
                                    <ul style="padding-right:17px;max-height:160px;overflow:auto">
                                        @foreach ($subscription->bookings as $booking)
                                            <li>{{ $booking->booking_date->format('Y-m-d') }} ({{ $booking->booking_status }})</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                مرن
                            @endif
                        </td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ number_format((float)$subscription->amount,2) }} د.أ</td>
                        <td style="padding:12px;border-bottom:1px solid #eee">{{ $subscription->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:25px;text-align:center">لا توجد اشتراكات حتى الآن.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

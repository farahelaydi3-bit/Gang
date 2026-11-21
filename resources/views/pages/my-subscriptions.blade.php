@extends('layouts.demo')

@section('title', 'اشتراكاتي')
@section('page', 'my-subscriptions')

@push('styles')
<style>
    .my-rec-page { padding-bottom: 60px; direction: rtl; }
    .my-rec-table-wrap { overflow-x: auto; border: 1px solid #dae6df; border-radius: 16px; background: white; }
    .my-rec-table { width: 100%; border-collapse: collapse; min-width: 840px; text-align: right; }
    .my-rec-table th, .my-rec-table td { padding: 14px; border-bottom: 1px solid #eef1ef; vertical-align: top; }
    .my-rec-table th { color: #1a4633; background: #f4faf6; }
    .my-rec-badge { display: inline-block; border-radius: 100px; padding: 5px 10px; background: #eaf4ed; }
    .my-rec-dates { max-height: 220px; overflow-y: auto; margin: 8px 0 0; padding-right: 18px; }
    .my-rec-alert { background: #e3f6e9; color: #125a31; padding: 14px 18px; border-radius: 12px; margin: 15px 0; }
    .my-rec-cancel { cursor: pointer; border: 1px solid #d99595; color: #a22e2e; background: white;
        padding: 8px 12px; border-radius: 10px; font: inherit; }
    .my-rec-link { display: inline-block; margin-top: 22px; }
</style>
@endpush

@section('content')
<div class="wrap page my-rec-page">
    @include('pages.heading', [
        'eyebrow' => 'Gang',
        'heading' => 'اشتراكاتي',
        'description' => 'تفاصيل الباقات والمواعيد الثابتة وحالة الدفع.',
    ])

    @if (session('success'))
        <div class="my-rec-alert" role="status">{{ session('success') }}</div>
    @endif

    <div class="my-rec-table-wrap">
        <table class="my-rec-table">
            <thead>
                <tr><th>الملعب</th><th>المدة</th><th>المواعيد والساعات</th><th>المبلغ</th><th>الانتهاء</th><th>الحالة</th><th>الإجراء</th></tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td>{{ $subscription->court?->name ?? 'غير معروف' }}</td>
                        <td>{{ $subscription->duration_days }} يوم</td>
                        <td>
                            @if ($subscription->is_fixed_schedule)
                                <strong>{{ number_format((float) $subscription->total_hours, 1) }} ساعة محجوزة</strong>
                                <div>{{ substr((string)$subscription->scheduled_start_time, 0, 5) }}–{{ substr((string)$subscription->scheduled_end_time, 0, 5) }}</div>
                                <details>
                                    <summary>عرض {{ $subscription->bookings->count() }} مواعيد</summary>
                                    <ul class="my-rec-dates">
                                        @foreach($subscription->bookings as $booking)
                                            <li>{{ $booking->booking_date->format('Y-m-d') }} — {{ substr((string)$booking->start_time, 0, 5) }}–{{ substr((string)$booking->end_time, 0, 5) }} ({{ $booking->booking_status }})</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                {{ number_format($subscription->remaining_hours, 1) }} ساعة متبقية من {{ number_format((float)$subscription->total_hours, 1) }}
                            @endif
                        </td>
                        <td>{{ number_format((float) $subscription->amount, 2) }} د.أ</td>
                        <td>{{ $subscription->end_date?->format('Y-m-d') }}</td>
                        <td><span class="my-rec-badge">{{ [
                            'pending' => 'بانتظار الدفع',
                            'active' => 'نشط',
                            'expired' => 'منتهي',
                            'cancelled' => 'ملغى',
                        ][$subscription->status] ?? $subscription->status }}</span></td>
                        <td>
                            @if ($subscription->status === 'pending' && $subscription->paid_at === null)
                                <form action="{{ route('subscriptions.destroy', $subscription) }}" method="POST"
                                    onsubmit="return confirm('هل تريد إلغاء الطلب وتحرير المواعيد؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="my-rec-cancel">إلغاء الطلب</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:24px;text-align:center">لا توجد اشتراكات حتى الآن.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <a class="my-rec-link" href="{{ route('courtbook.courts') }}">استعرض الملاعب للاشتراك من صفحة الملعب</a>
</div>
@endsection

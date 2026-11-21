@extends('admins.layout.app')

@section('title', 'إدارة الاشتراكات')
@section('page-title', 'إدارة الاشتراكات')

@section('content')
<div lang="ar" dir="rtl">
    <x-super-admin.page-header title="اشتراكات ملاعبي" description="راجع الاشتراكات وحالات الدفع والمواعيد الثابتة.">
        <x-slot:actions>
            <a href="{{ route('admin.subscriptions.create') }}" class="admin-button">+ إضافة اشتراك</a>
        </x-slot:actions>
    </x-super-admin.page-header>
    <x-super-admin.form-errors />

    @if (isset($subscriptionStats) && $subscriptionStats)
        <div class="stats-grid">
            @foreach (['total' => 'جميع الاشتراكات', 'active' => 'نشطة', 'pending' => 'بانتظار الدفع', 'expired' => 'منتهية', 'cancelled' => 'ملغاة'] as $key => $label)
                <div class="stat-card"><div class="stat-info">
                    <span>{{ $label }}</span>
                    <strong>{{ $subscriptionStats->{$key} ?? 0 }}</strong></div>
                </div>
            @endforeach
        </div>
    @endif

    <section class="admin-card super-admin-filter" aria-label="تصفية الاشتراكات">
    <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="super-admin-filter-form">
        <label class="super-admin-filter-field super-admin-filter-field--search">
            <span class="form-label">البحث عن اشتراك</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="اسم الباقة أو المشترك أو البريد الإلكتروني" class="form-control">
        </label>
        <label class="super-admin-filter-field">
            <span class="form-label">الحالة</span>
            <select name="status" class="form-control">
                <option value="">جميع الحالات</option>
                @foreach (['pending' => 'بانتظار الدفع', 'active' => 'نشط', 'expired' => 'منتهي', 'cancelled' => 'ملغى'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="admin-button">بحث وتصفية</button>
        <a href="{{ route('admin.subscriptions.index') }}" class="admin-button admin-button-outline">إعادة ضبط</a>
    </form>

    </section>
    <section class="admin-card super-admin-table-card">
    <div class="admin-table-wrap">
        <table class="admin-table owner-subscriptions-table">
            <thead>
                <tr>
                    @foreach (['المشترك', 'الملعب', 'الباقة', 'المدة', 'الساعات', 'المواعيد', 'المبلغ', 'الحالة', 'الإجراءات'] as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td>
                            <strong>{{ $subscription->user?->name ?? 'غير معروف' }}</strong>
                            @if ($subscription->user?->email)
                                <div class="super-admin-muted" dir="ltr">{{ $subscription->user->email }}</div>
                            @endif
                        </td>
                        <td>{{ $subscription->court?->name ?? 'غير معروف' }}</td>
                        <td>{{ $subscription->package_name ?? '—' }}</td>
                        <td>{{ $subscription->duration_days }} يوم</td>
                        <td>
                            @if ($subscription->is_fixed_schedule)
                                {{ number_format((float) $subscription->total_hours, 1) }} محجوزة
                            @else
                                {{ number_format((float) $subscription->remaining_hours, 1) }} متبقية
                            @endif
                        </td>
                        <td>
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
                        <td>{{ number_format((float) $subscription->amount, 2) }} د.أ</td>
                        <td>
                            <x-dashboard-status :state="$subscription->status"
                                :label="['pending' => 'بانتظار الدفع', 'active' => 'نشط', 'expired' => 'منتهي', 'cancelled' => 'ملغى'][$subscription->status] ?? $subscription->status" />
                        </td>
                        <td>
                            <div class="super-admin-inline-actions">
                                @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.show'))
                                    <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" class="admin-button admin-button-outline">عرض</a>
                                @endif
                                @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.edit'))
                                    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="admin-button admin-button-outline">تعديل</a>
                                @endif
                                @if (\Illuminate\Support\Facades\Route::has('admin.subscriptions.destroy'))
                                    <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الاشتراك؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-button admin-button-danger">حذف</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="super-admin-empty">لا توجد اشتراكات مطابقة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscriptions->withQueryString()->links('components.super-admin.pagination', ['arabic' => true]) }}
    </section>
</div>
@endsection

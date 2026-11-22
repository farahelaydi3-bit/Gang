@props(['action', 'placeholder' => null, 'selects' => [], 'dates' => false, 'context' => []])

<section class="admin-card super-admin-filter" aria-label="{{ __('List filters') }}">
    <form method="GET" action="{{ $action }}" class="super-admin-filter-form dashboard-list-filters">
        @foreach ($context as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @if ($placeholder)
            <label class="super-admin-filter-field super-admin-filter-field--search">
                <span class="form-label">{{ __('Search') }}</span>
                <input type="search" name="search" class="form-control" maxlength="120" dir="auto"
                    placeholder="{{ __($placeholder) }}" value="{{ \App\Support\DashboardList::value(request(), 'search') }}">
            </label>
        @endif

        @foreach ($selects as $name => [$label, $empty, $options])
            <label class="super-admin-filter-field">
                <span class="form-label">{{ __($label) }}</span>
                <select name="{{ $name }}" class="form-control">
                    <option value="">{{ __($empty) }}</option>
                    @foreach ($options as $value => $option)
                        <option value="{{ $value }}" @selected(\App\Support\DashboardList::value(request(), $name) === (string) $value)>{{ __((string) $option) }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach

        @if ($dates)
            @foreach (['from' => 'From date', 'to' => 'To date'] as $name => $label)
                <label class="super-admin-filter-field dashboard-date-filter">
                    <span class="form-label">{{ __($label) }}</span>
                    <input type="date" name="{{ $name }}" class="form-control"
                        value="{{ \App\Support\DashboardList::value(request(), $name) }}">
                </label>
            @endforeach
        @endif

        <div class="dashboard-filter-actions">
            <button type="submit" class="admin-button">{{ __('Filter') }}</button>
            <a href="{{ $context ? $action.'?'.\Illuminate\Support\Arr::query($context) : $action }}" class="admin-button admin-button-outline">{{ __('Clear filters') }}</a>
        </div>
    </form>
</section>
